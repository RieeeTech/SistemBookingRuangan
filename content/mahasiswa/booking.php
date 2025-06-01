<?php
// booking.php - Halaman untuk mahasiswa melakukan booking

$conn = connectDB();
$message = '';
$error = '';



function generateBookingId($conn) {
    $tanggal = date('dmy'); // Format: ddmmyy, contoh: 010624

    // Hitung berapa booking yang sudah ada hari ini
    $query = "SELECT COUNT(*) AS total FROM tb_booking WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $jumlahBookingHariIni = $row['total'] + 1;

    $nomorUrut = str_pad($jumlahBookingHariIni, 2, '0', STR_PAD_LEFT); // dua digit
    return $tanggal . $nomorUrut;
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jadwal = (int)$_POST['id_jadwal'];
    $keperluan = trim($_POST['keperluan']);
    $id_user = $_SESSION['user_id'];
    $kelas = $_POST['kelas'];
    $penanggung_jawab = $_POST['penanggung_jawab'];
    

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

                $id_booking = generateBookingId($conn);
                // Insert booking baru
                $query_insert = "INSERT INTO tb_booking (id, id_user, id_jadwal, keperluan, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param('siis', $id_booking, $id_user, $id_jadwal, $keperluan);
                
                $set_jadwal = $conn->prepare("UPDATE tb_jadwal SET kelas = ? WHERE id = ?");
                $set_jadwal->bind_param('ss', $kelas, $id_jadwal);



                if ($stmt_insert->execute()) {
                    // Update status jadwal menjadi tidak tersedia
                    
                    
                    $_SESSION['success'] = "Booking berhasil diajukan. Menunggu persetujuan admin.";
                    redirect('index.php?page=myBooking');
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
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Jadwal</h6>
        </div>
        <div class="card-body">
            <!-- Perbaikan form action -->
            <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <!-- Jika menggunakan parameter page, tambahkan hidden input -->
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tipe">Tipe Ruangan:</label>
                        <select class="form-control" id="tipe" name="tipe">
                            <option value="">Semua Tipe</option>
                            <?php 
                            // Array tipe ruangan yang tersedia
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
                        <label for="hari">Hari:</label>
                        <select class="form-control" id="hari" name="hari">
                            <option value="">Pilih Hari</option>
                            <?php 
                            // Array hari yang tersedia
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
                            <i class="bi bi-search"></i>
                        </button>
                        <!-- Perbaikan link reset -->
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?><?php echo isset($_GET['page']) ? '?page=' . htmlspecialchars($_GET['page']) : ''; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-repeat"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Jadwal Tersedia -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Jadwal Tersedia</h6>
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
                                <h5 class="text-primary font-weight-bold">
                                    <i class="fas fa-<?php echo $jadwal['tipe'] == 'lab' ? 'flask' : ($jadwal['tipe'] == 'teori' ? 'chalkboard-teacher' : 'building'); ?> me-2"></i>
                                    Ruangan <?php echo ucfirst($jadwal['tipe']); ?>
                                </h5>
                            </div>
                            <?php $current_tipe = $jadwal['tipe']; 
                        endif; 
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title text-primary mb-0"><?php echo htmlspecialchars($jadwal['nama_ruangan']); ?></h5>
                                        <span class="badge badge-<?php echo $jadwal['tipe'] == 'lab' ? 'success' : ($jadwal['tipe'] == 'teori' ? 'info' : 'warning'); ?> ml-2">
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
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#bookingModal1<?php echo $jadwal['id']; ?>">
                                        <i class="fas fa-plus me-1"></i> Booking
                                    </button>
                                </div>
                            </div>
                        </div>



                        <!-- Modal Booking -->
                        <div class="modal fade" id="bookingModal1<?php echo $jadwal['id']; ?>" tabindex="-1" aria-labelledby="roomDetailModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="roomDetailModalLabel">Detail Ruangan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                    <!-- Gambar ruangan -->
                                    <div class="col-md-6">
                                        <img src="upload/jeruk.jpg" class="img-fluid rounded shadow-sm" alt="Foto Ruangan">
                                    </div>
                                    <!-- Detail ruangan -->
                                    <div class="col-md-6">
                                        <h4><?= $jadwal['nama_ruangan']?></h4>
                                        <p><strong>Lokasi: </strong> <?= $jadwal['lokasi']?></p>
                                        <p><strong>Hari: </strong> <?= $jadwal['hari']?></p>
                                        <p><strong>Jam: </strong> <?= date("H:i", strtotime($jadwal['jam_mulai'])) . ' - ' . date("H:i", strtotime($jadwal['jam_selesai'])) ?></p>
                                        <p><strong>Fasilitas:</strong></p>
                                            <ul>
                                                <?php 
                                                $fasilitas = explode(',', $jadwal['fasilitas']); // ubah string jadi array
                                                foreach ($fasilitas as $item): 
                                                ?>
                                                    <li><?= htmlspecialchars(trim($item)) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        
                                    </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="button" class="btn btn-primary" data-bs-target="#exampleModalToggle2" data-bs-toggle="modal">
                                        <i class="fas fa-paper-plane me-1"></i> Isi Data
                                    </button>
                                </div>
                                </div>
                            </div>
                            </div>


                        <!-- Modal Booking 2 -->
                        <div class="modal fade" id="exampleModalToggle2" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel<?php echo $jadwal['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="bookingModalLabel<?php echo $jadwal['id']; ?>">Isi Data Ini</h5>
                                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                                            
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Keperluan/Tujuan Penggunaan</label>
                                                <select name="keperluan" id="" class="form-control" required>
                                                    <option value="">-- Pilih Satu --</option>
                                                    <option value="Kelas Ganti">Kelas Ganti</option>
                                                    <option value="Praktek">Praktek</option>
                                                    <option value="Rapat">Rapat</option>
                                                    <option value="Presentasi">Presentasi</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                                
                                            </div>

                                            <div class="mb-3">
                                                <label for="input1" class="form-label">Kelas</label>
                                                <input type="text" name="kelas" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label for="input1" class="form-label">Penanggung Jawab (Dosen)</label>
                                                <input type="text" name="penanggung_jawab" class="form-control">
                                            </div>

                                            <input type="hidden" name="id_jadwal" value="<?php echo $jadwal['id']; ?>">
                                            
                                            
                                            
                                            
                                            
                                            
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary" data-bs-target="#exampleModalToggle2" data-bs-toggle="modal">
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Informasi</div>
                            <div class="text-xs mb-0">
                                • Filter berdasarkan tipe ruangan: Lab, Teori, atau Aula<br>
                                • Filter berdasarkan hari: Senin sampai Sabtu<br>
                                • Booking harus diajukan sebelum penggunaan<br>
                                • Admin akan memverifikasi booking dalam 1x24 jam<br>
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Aturan Penggunaan</div>
                            <div class="text-xs mb-0">
                                • Menjaga kebersihan dan kerapihan ruangan<br>
                                • Tidak merusak fasilitas yang ada<br>
                                • Menggunakan ruangan sesuai waktu yang ditentukan<br>
                                • Melaporkan jika ada kerusakan fasilitas
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




<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomDetailModal">
  Lihat Detail Ruangan
</button>

<!-- Modal -->
<div class="modal fade" id="roomDetailModal" tabindex="-1" aria-labelledby="roomDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roomDetailModalLabel">Detail Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Gambar ruangan -->
          <div class="col-md-6">
            <img src="https://via.placeholder.com/500x300?text=Foto+Ruangan" class="img-fluid rounded shadow-sm" alt="Foto Ruangan">
          </div>
          <!-- Detail ruangan -->
          <div class="col-md-6">
            <h4>Ruang Rapat 1</h4>
            <p><strong>Lokasi:</strong> Gedung A, Lantai 2</p>
            <p><strong>Kapasitas:</strong> 20 orang</p>
            <p><strong>Fasilitas:</strong></p>
            <ul>
              <li>Proyektor</li>
              <li>AC</li>
              <li>Whiteboard</li>
              <li>Wi-Fi</li>
            </ul>
            <p><strong>Keterangan Tambahan:</strong> Cocok untuk rapat formal dan presentasi.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
