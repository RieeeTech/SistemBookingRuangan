<?php
// index.php - Halaman utama dashboard sistem booking ruangan

$conn = connectDB();

redirectIfNotLoggedIn();


// Statistik untuk admin
if (isAdmin()) {
    // Hitung total ruangan
    $query_ruangan = "SELECT COUNT(*) as total_ruangan FROM tb_ruangan WHERE status = 'aktif'";
    $result_ruangan = $conn->query($query_ruangan);
    $total_ruangan = $result_ruangan->fetch_assoc()['total_ruangan'];
    
    // Hitung total jadwal
    $query_jadwal = "SELECT COUNT(*) as total_jadwal FROM tb_jadwal";
    $result_jadwal = $conn->query($query_jadwal);
    $total_jadwal = $result_jadwal->fetch_assoc()['total_jadwal'];
    
    // Hitung total booking
    $query_booking = "SELECT COUNT(*) as total_booking FROM tb_booking";
    $result_booking = $conn->query($query_booking);
    $total_booking = $result_booking->fetch_assoc()['total_booking'];
    
    // Hitung total mahasiswa
    $query_mahasiswa = "SELECT COUNT(*) as total_mahasiswa FROM tb_user WHERE role = 'mahasiswa'";
    $result_mahasiswa = $conn->query($query_mahasiswa);
    $total_mahasiswa = $result_mahasiswa->fetch_assoc()['total_mahasiswa'];
    
    // Ambil data booking terbaru
    $query_recent_bookings = "SELECT tb.*, tm.nama AS nama_lengkap, tr.nama, tj.hari, tj.jam_mulai, tj.jam_selesai 
        FROM tb_booking tb
        JOIN tb_user tu ON tb.id_user = tu.id_user
        JOIN tb_mahasiswa tm ON tu.nim = tm.nim

        JOIN tb_jadwal tj ON tb.id_jadwal = tj.id
        JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
        ORDER BY tb.created_at DESC
        LIMIT 5";
    $result_recent_bookings = $conn->query($query_recent_bookings);


    
    // Statistik booking hari ini
    $query_today_bookings = "SELECT COUNT(*) as total_today FROM tb_booking tb 
                            JOIN tb_jadwal tj ON tb.id_jadwal = tj.id 
                            WHERE tj.tanggal = CURDATE()";
    $result_today = $conn->query($query_today_bookings);
    $today_bookings = $result_today->fetch_assoc()['total_today'];
    
    // Booking pending yang perlu diproses
    $query_pending_admin = "SELECT COUNT(*) as total_pending FROM tb_booking WHERE status = 'pending'";
    $result_pending_admin = $conn->query($query_pending_admin);
    $pending_admin = $result_pending_admin->fetch_assoc()['total_pending'];
}

// Untuk mahasiswa
if (isMahasiswa()) {
    // Ambil data booking milik mahasiswa
    $user_id = $_SESSION['user_id'];
    
    $query_my_bookings = "SELECT tb.*, tr.nama, tj.tanggal, tj.jam_mulai, tj.jam_selesai 
                         FROM tb_booking tb
                         JOIN tb_jadwal tj ON tb.id_jadwal = tj.id
                         JOIN tb_ruangan tr ON tj.id_ruangan = tr.id
                         WHERE tb.id_user = ?
                         ORDER BY tj.tanggal DESC, tj.jam_mulai DESC
                         LIMIT 5";
    $stmt = $conn->prepare($query_my_bookings);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_my_bookings = $stmt->get_result();
    
    // Hitung total booking milik mahasiswa
    $query_total_bookings = "SELECT COUNT(*) as total FROM tb_booking WHERE id_user = ?";
    $stmt = $conn->prepare($query_total_bookings);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_total = $stmt->get_result();
    $total_bookings = $result_total->fetch_assoc()['total'];
    
    // Hitung booking yang pending
    $query_pending = "SELECT COUNT(*) as total FROM tb_booking WHERE id_user = ? AND status = 'pending'";
    $stmt = $conn->prepare($query_pending);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_pending = $stmt->get_result();
    $pending_bookings = $result_pending->fetch_assoc()['total'];
    
    // Hitung booking yang disetujui
    $query_approved = "SELECT COUNT(*) as total FROM tb_booking WHERE id_user = ? AND status = 'disetujui'";
    $stmt = $conn->prepare($query_approved);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_approved = $stmt->get_result();
    $approved_bookings = $result_approved->fetch_assoc()['total'];
    
    // Hitung booking yang ditolak
    $query_rejected = "SELECT COUNT(*) as total FROM tb_booking WHERE id_user = ? AND status = 'ditolak'";
    $stmt = $conn->prepare($query_rejected);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_rejected = $stmt->get_result();
    $rejected_bookings = $result_rejected->fetch_assoc()['total'];
    
    // Booking hari ini untuk mahasiswa
    $query_today_user = "SELECT COUNT(*) as total_today FROM tb_booking tb 
                        JOIN tb_jadwal tj ON tb.id_jadwal = tj.id 
                        WHERE tb.id_user = ? AND tj.tanggal = CURDATE()";
    $stmt = $conn->prepare($query_today_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_today_user = $stmt->get_result();
    $today_user_bookings = $result_today_user->fetch_assoc()['total_today'];
}

// Function untuk format tanggal Indonesia
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
}

// Function untuk format waktu
if (!function_exists('formatWaktu')) {
    function formatWaktu($waktu) {
        return date('H:i', strtotime($waktu));
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div class="d-none d-lg-inline-block">
            <span class="text-muted">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?>!</span>
        </div>
    </div>

    <?php if (isAdmin()): ?>
        <!-- Dashboard Admin -->
        <div class="row">
            <!-- Total Ruangan Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Ruangan</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_ruangan; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-door-open fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Jadwal Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Jadwal</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_jadwal; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Booking Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Booking</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_booking; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Mahasiswa Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Mahasiswa</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_mahasiswa; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row kedua untuk statistik tambahan -->
        <div class="row">
            <!-- Booking Hari Ini -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Booking Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Pending -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Perlu Persetujuan</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_admin; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Terbaru -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Booking Terbaru</h6>
                <a href="booking_admin.php" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-arrow-right fa-sm text-white-50"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <?php if ($result_recent_bookings && $result_recent_bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Ruangan</th>
                                    
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        
                                        <td><?php echo formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($row['status']) {
                                                case 'pending':
                                                    $badge_class = 'badge bg-warning text-dark';
                                                    break;
                                                case 'disetujui':
                                                    $badge_class = 'badge bg-success';
                                                    break;
                                                case 'ditolak':
                                                    $badge_class = 'badge bg-danger';
                                                    break;
                                                case 'selesai':
                                                    $badge_class = 'badge bg-secondary';
                                                    break;
                                                default:
                                                    $badge_class = 'badge bg-light text-dark';
                                            }
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="detail_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="approve_booking.php?id=<?php echo $row['id']; ?>&action=approve" class="btn btn-sm btn-success" title="Setujui" onclick="return confirm('Setujui booking ini?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <a href="approve_booking.php?id=<?php echo $row['id']; ?>&action=reject" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak booking ini?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i>
                        <p class="text-muted">Belum ada data booking.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif (isMahasiswa()): ?>
        <!-- Dashboard Mahasiswa -->
        <div class="row">
            <!-- Total Booking Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Booking</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menunggu Persetujuan Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Menunggu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disetujui Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Disetujui</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $approved_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ditolak Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Ditolak</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rejected_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row kedua untuk statistik tambahan mahasiswa -->
        <div class="row">
            <div class="col-xl-12 col-md-12 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Booking Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_user_bookings; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Saya -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Booking Terbaru Saya</h6>
                <div>
                    <a href="booking.php" class="btn btn-sm btn-success shadow-sm me-2">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Booking Baru
                    </a>
                    <a href="riwayat_booking.php" class="btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-list fa-sm text-white-50"></i> Lihat Semua
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($result_my_bookings && $result_my_bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Keperluan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_my_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo formatTanggal($row['tanggal']); ?></td>
                                        <td><?php echo formatWaktu($row['jam_mulai']) . ' - ' . formatWaktu($row['jam_selesai']); ?></td>
                                        <td>
                                            <?php 
                                            $keperluan = htmlspecialchars($row['keperluan']);
                                            echo strlen($keperluan) > 50 ? substr($keperluan, 0, 50) . '...' : $keperluan;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($row['status']) {
                                                case 'pending':
                                                    $badge_class = 'badge bg-warning text-dark';
                                                    break;
                                                case 'disetujui':
                                                    $badge_class = 'badge bg-success';
                                                    break;
                                                case 'ditolak':
                                                    $badge_class = 'badge bg-danger';
                                                    break;
                                                case 'selesai':
                                                    $badge_class = 'badge bg-secondary';
                                                    break;
                                                default:
                                                    $badge_class = 'badge bg-light text-dark';
                                            }
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="detail_booking_mahasiswa.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="cancel_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Batalkan" onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i>
                        <p class="text-muted">Anda belum memiliki booking ruangan.</p>
                        <a href="booking.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Booking Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions untuk Mahasiswa -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="booking.php" class="btn btn-success btn-block">
                            <i class="fas fa-plus mb-2"></i><br>
                            Booking Ruangan Baru
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="jadwal_ruangan.php" class="btn btn-info btn-block">
                            <i class="fas fa-calendar-alt mb-2"></i><br>
                            Lihat Jadwal Ruangan
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="riwayat_booking.php" class="btn btn-primary btn-block">
                            <i class="fas fa-history mb-2"></i><br>
                            Riwayat Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Jika role tidak dikenali -->
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            Role pengguna tidak dikenali. Silakan hubungi administratotr.
        </div>
    <?php endif; ?>
</div>

<!-- Modal untuk notifikasi -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationMessage">
                <!-- Pesan notifikasi akan ditampilkan di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh dashboard setiap 5 menit
setTimeout(function() {
    location.reload();
}, 300000);

// Tampilkan notifikasi jika ada
<?php if (isset($_SESSION['success_message'])): ?>
    $('#notificationMessage').html('<div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>');
    $('#notificationModal').modal('show');
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    $('#notificationMessage').html('<div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>');
    $('#notificationModal').modal('show');
<?php endif; ?>
</script>


</body>
</html>