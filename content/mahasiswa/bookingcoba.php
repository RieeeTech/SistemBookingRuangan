<?php
// booking.php - Halaman untuk mahasiswa melakukan booking

$conn = connectDB();
$message = '';
$error = '';

// Fungsi untuk generate booking ID dengan format YYYYMMDD-XXX berdasarkan data hari ini
function generateBookingId($conn) {
    $today = date('Y-m-d');
    $date_prefix = date('Ymd');
    
    // Hitung jumlah booking hari ini
    $query = "SELECT COUNT(*) as total FROM tb_booking WHERE DATE(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $new_number = $row['total'] + 1;
    
    // Format: YYYYMMDD-XXX (misal: 20250601-001)
    return $date_prefix . '-' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk mendapatkan booking ID berdasarkan ID record
function getBookingIdFromRecord($conn, $booking_record_id) {
    $query = "SELECT DATE(created_at) as booking_date FROM tb_booking WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_record_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $booking_date = $row['booking_date'];
        $date_prefix = date('Ymd', strtotime($booking_date));
        
        // Hitung urutan booking pada tanggal tersebut sampai dengan ID ini
        $query_count = "SELECT COUNT(*) as urutan FROM tb_booking 
                        WHERE DATE(created_at) = ? AND id <= ? 
                        ORDER BY id";
        $stmt_count = $conn->prepare($query_count);
        $stmt_count->bind_param('si', $booking_date, $booking_record_id);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        
        return $date_prefix . '-' . str_pad($row_count['urutan'], 3, '0', STR_PAD_LEFT);
    }
    
    return null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jadwal = (int)$_POST['id_jadwal'];
    $keperluan = trim($_POST['keperluan']);
    $id_user = $_SESSION['user_id'];
    
    if (empty($keperluan)) {
        $error = "Keperluan harus diisi";
    } else {
        // Cek apakah jadwal masih tersedia
        $query_check = "SELECT tj.*, tr.nama
                        FROM tb_jadwal tj 
                        JOIN tb_ruangan tr ON tj.id_ruangan = tr.id 
                        WHERE tj.id = ? AND tj.status = 'Kosong'";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param('i', $id_jadwal);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $jadwal = $result_check->fetch_assoc();
            
            // Cek apakah user sudah pernah booking di jadwal yang sama
            $query_duplicate = "SELECT id FROM tb_booking WHERE id_user = ? AND id_jadwal = ? AND status IN ('pending', 'disetujui')";
            $stmt_duplicate = $conn->prepare($query_duplicate);
            $stmt_duplicate->bind_param('ii', $id_user, $id_jadwal);
            $stmt_duplicate->execute();
            $result_duplicate = $stmt_duplicate->get_result();
            
            if ($result_duplicate->num_rows > 0) {
                $error = "Anda sudah melakukan booking untuk jadwal ini";
            } else {
                // Insert booking baru  
                $query_insert = "INSERT INTO tb_booking (id_user, id_jadwal, keperluan, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param('iis', $id_user, $id_jadwal, $keperluan);
                
                if ($stmt_insert->execute()) {
                    // Dapatkan ID booking yang baru saja dimasukkan
                    $new_booking_id = $conn->insert_id;
                    
                    // Generate booking ID berdasarkan tanggal dan urutan
                    $booking_id = getBookingIdFromRecord($conn, $new_booking_id);
                    
                    // Update status jadwal menjadi tidak tersedia
                    $query_update = "UPDATE tb_jadwal SET status = 'booked' WHERE id = ?";
                    $stmt_update = $conn->prepare($query_update);
                    $stmt_update->bind_param('i', $id_jadwal);
                    $stmt_update->execute();
                    
                    $_SESSION['success'] = "Booking berhasil diajukan dengan ID: $booking_id. Menunggu persetujuan admin.";
                    redirect('my_booking.php');
                    exit();
                } else {
                    $error = "Gagal melakukan booking. Silakan coba lagi.";
                }
            }
        } else {
            $error = "Jadwal tidak tersedia";
        }
    }
}

// Ambil daftar tipe ruangan yang tersedia
$query_tipe = "SELECT DISTINCT tipe FROM tb_ruangan WHERE status = 'aktif' ORDER BY tipe";
$result_tipe = $conn->query($query_tipe);

// Ambil jadwal tersedia berdasarkan filter
$filter_tipe = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';
$filter_hari = isset($_GET['hari']) ? trim($_GET['hari']) : '';

$filter_clause = "";
$params = [];
$types = '';

if (!empty($filter_tipe)) {
    $filter_clause .= " AND tr.tipe = ?";
    $params[] = $filter_tipe;
    $types .= 's';
}

if (!empty($filter_hari)) {
    $filter_clause .= " AND tj.hari = ?";
    $params[] = $filter_hari;
    $types .= 's';
}

// Query untuk mengambil jadwal tersedia
$query_jadwal = "SELECT tj.*, tr.nama AS nama_ruangan, tr.fasilitas, tr.tipe, tr.lokasi
                 FROM tb_jadwal tj
                 JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
                 WHERE tj.status = 'Kosong'$filter_clause
                 ORDER BY tr.tipe, tr.nama, tj.jam_mulai";

$stmt_jadwal = $conn->prepare($query_jadwal);
if (!empty($types)) {
    $stmt_jadwal->bind_param($types, ...$params);
}
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();

// Reset result tipe untuk loop kedua
$result_tipe = $conn->query($query_tipe);
?>

<!-- Tampilan Booking untuk User -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Booking Ruangan</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter Jadwal</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tipe" class="form-label">Tipe Ruangan:</label>
                        <select class="form-select" id="tipe" name="tipe">
                            <option value="">Semua Tipe</option>
                            <?php 
                            $tipe_ruangan = ['lab', 'teori', 'aula'];
                            foreach ($tipe_ruangan as $tipe): 
                            ?>
                                <option value="<?php echo $tipe; ?>" <?php if ($filter_tipe == $tipe) echo 'selected'; ?>>
                                    <?php echo ucfirst($tipe); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hari" class="form-label">Hari:</label>
                        <select class="form-select" id="hari" name="hari">
                            <option value="">Pilih Hari</option>
                            <?php 
                            $hari_ruangan = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
                            foreach ($hari_ruangan as $hari): 
                            ?>
                                <option value="<?php echo $hari; ?>" <?php if ($filter_hari == $hari) echo 'selected'; ?>>
                                    <?php echo ucfirst($hari); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Cari
                        </button>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?><?php echo isset($_GET['page']) ? '?page=' . htmlspecialchars($_GET['page']) : ''; ?>" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Jadwal Tersedia -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Jadwal Tersedia</h6>
        </div>
        <div class="card-body">
            <?php if ($result_jadwal->num_rows > 0): ?>
                <div class="row">
                    <?php 
                    $current_tipe = '';
                    while ($jadwal = $result_jadwal->fetch_assoc()): 
                        // Tampilkan header tipe jika berbeda
                        if ($current_tipe != $jadwal['tipe'] && empty($filter_tipe)): 
                            if ($current_tipe != ''): ?>
                            </div>
                            <hr class="my-4">
                            <div class="row">
                            <?php endif; ?>
                            <div class="col-12 mb-3">
                                <h5 class="text-primary fw-bold">
                                    <i class="fas fa-<?php echo $jadwal['tipe'] == 'lab' ? 'flask' : ($jadwal['tipe'] == 'teori' ? 'chalkboard-teacher' : 'building'); ?> me-2"></i>
                                    Ruangan <?php echo ucfirst($jadwal['tipe']); ?>
                                </h5>
                            </div>
                            <?php $current_tipe = $jadwal['tipe']; 
                        endif; 
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-start border-primary border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title text-primary mb-0"><?php echo htmlspecialchars($jadwal['nama_ruangan']); ?></h5>
                                        <span class="badge bg-<?php echo $jadwal['tipe'] == 'lab' ? 'success' : ($jadwal['tipe'] == 'teori' ? 'info' : 'warning'); ?> ms-2">
                                            <?php echo ucfirst($jadwal['tipe']); ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-calendar text-muted me-2"></i>
                                        <strong><?php echo ucfirst($jadwal['hari']); ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-clock text-muted me-2"></i>
                                        <?php echo formatWaktu($jadwal['jam_mulai']) . ' - ' . formatWaktu($jadwal['jam_selesai']); ?>
                                    </div>
                                    
                                    <?php if (!empty($jadwal['fasilitas'])): ?>
                                    <div class="mb-3">
                                        <i class="bi bi-diagram-2 text-muted me-2"></i>
                                        <small class="text-muted"><?php echo htmlspecialchars($jadwal['fasilitas']); ?></small>
                                    </div>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#bookingModal<?php echo $jadwal['id']; ?>">
                                        <i class="fas fa-plus me-1"></i> Booking
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Booking -->
                        <div class="modal fade" id="bookingModal<?php echo $jadwal['id']; ?>" tabindex="-1" aria-labelledby="bookingModalLabel<?php echo $jadwal['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="bookingModalLabel<?php echo $jadwal['id']; ?>">Booking Ruangan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" id="bookingForm<?php echo $jadwal['id']; ?>">
                                        <div class="modal-body">
                                            <div class="row">
                                                <!-- Detail Ruangan -->
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-primary mb-3">Detail Ruangan</h6>
                                                    <div class="mb-2">
                                                        <strong>Nama Ruangan:</strong><br>
                                                        <?php echo htmlspecialchars($jadwal['nama_ruangan']); ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>Tipe:</strong><br>
                                                        <span class="badge bg-<?php echo $jadwal['tipe'] == 'lab' ? 'success' : ($jadwal['tipe'] == 'teori' ? 'info' : 'warning'); ?>">
                                                            <?php echo ucfirst($jadwal['tipe']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>Hari:</strong><br>
                                                        <?php echo ucfirst($jadwal['hari']); ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>Waktu:</strong><br>
                                                        <?php echo formatWaktu($jadwal['jam_mulai']) . ' - ' . formatWaktu($jadwal['jam_selesai']); ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>Lokasi:</strong><br>
                                                        <?php echo htmlspecialchars($jadwal['lokasi']); ?>
                                                    </div>
                                                    <?php if (!empty($jadwal['fasilitas'])): ?>
                                                    <div class="mb-2">
                                                        <strong>Fasilitas:</strong><br>
                                                        <?php echo htmlspecialchars($jadwal['fasilitas']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Form Booking -->
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-primary mb-3">Informasi Booking</h6>
                                                    <div class="mb-3">
                                                        <label for="keperluan<?php echo $jadwal['id']; ?>" class="form-label">
                                                            <strong>Keperluan/Tujuan Penggunaan: <span class="text-danger">*</span></strong>
                                                        </label>
                                                        <textarea class="form-control" id="keperluan<?php echo $jadwal['id']; ?>" name="keperluan" rows="5" placeholder="Jelaskan keperluan penggunaan ruangan..." required></textarea>
                                                        <div class="form-text">
                                                            Contoh: Rapat organisasi, diskusi kelompok, presentasi tugas akhir, dll.
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <strong>Catatan:</strong><br>
                                                        • Booking akan mendapat ID unik dengan format tanggal hari ini<br>
                                                        • Admin akan memverifikasi dalam 1x24 jam<br>
                                                        • Pastikan data yang diisi benar dan lengkap
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="id_jadwal" value="<?php echo $jadwal['id']; ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Batal
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i> Ajukan Booking
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal Tersedia</h5>
                    <p class="text-muted">
                        <?php if (!empty($filter_tipe) || !empty($filter_hari)): ?>
                            Tidak ada jadwal tersedia untuk filter yang dipilih.
                        <?php else: ?>
                            Silakan pilih hari atau tipe ruangan lain, atau coba lagi nanti.
                        <?php endif; ?>
                    </p>
                    <a href="my_booking.php" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> Lihat Booking Saya
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Informasi</div>
                            <div class="text-sm mb-0">
                                • Filter berdasarkan tipe ruangan: Lab, Teori, atau Aula<br>
                                • Filter berdasarkan hari: Senin sampai Sabtu<br>
                                • Booking harus diajukan sebelum penggunaan<br>
                                • Admin akan memverifikasi booking dalam 1x24 jam<br>
                                                                                        • ID booking akan berformat: YYYYMMDD-XXX berdasarkan tanggal dan urutan booking<br>
                                • Pastikan menggunakan ruangan sesuai kapasitas dan fasilitas yang tersedia
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Aturan Penggunaan</div>
                            <div class="text-sm mb-0">
                                • Menjaga kebersihan dan kerapihan ruangan<br>
                                • Tidak merusak fasilitas yang ada<br>
                                • Menggunakan ruangan sesuai waktu yang ditentukan<br>
                                • Melaporkan jika ada kerusakan fasilitas<br>
                                • Hadir tepat waktu sesuai jadwal yang disetujui
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk konfirmasi booking -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission dengan konfirmasi
    const forms = document.querySelectorAll('form[id^="bookingForm"]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const keperluan = form.querySelector('textarea[name="keperluan"]').value.trim();
            
            if (keperluan === '') {
                alert('Mohon isi keperluan penggunaan ruangan');
                return;
            }
            
            if (confirm('Apakah Anda yakin ingin mengajukan booking ini?')) {
                form.submit();
            }
        });
    });
});
</script>