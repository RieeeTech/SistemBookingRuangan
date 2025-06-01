<?php
// header.php - Header untuk semua halaman
session_start();
require_once 'config.php';

// Jika belum login, redirect ke halaman login
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-building me-2"></i>Sistem Booking Ruangan
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item me-3">
                            <span class="navbar-text">
                                <i class="fas fa-clock me-1"></i>
                                <span id="current-time"></span>
                            </span>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['nama_lengkap']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container-fluid main-content ">
        <div class="row">
            <?php if (isLoggedIn()): ?>
                <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="user-info">
                        <div class="avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="role"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    
                    <?php if (isAdmin()): ?>
                        <!-- Menu Admin -->
                        <div class="sidebar-heading">
                            <i class="fas fa-crown me-1"></i> Admin Menu
                        </div>
                        <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="ruangan.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ruangan.php') ? 'active' : ''; ?>">
                            <i class="fas fa-door-open"></i> Kelola Ruangan
                        </a>
                        <a href="jadwal.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'jadwal.php') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> Kelola Jadwal
                        </a>
                        <a href="booking_admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'booking_admin.php') ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i> Kelola Booking
                        </a>
                        <a href="users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Kelola Users
                        </a>
                        <a href="laporan.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    <?php else: ?>
                        <!-- Menu Mahasiswa -->
                        <div class="sidebar-heading">
                            <i class="fas fa-graduation-cap me-1"></i> Menu Mahasiswa
                        </div>
                        <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                        <a href="jadwal_mahasiswa.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'jadwal_mahasiswa.php') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> Jadwal Ruangan
                        </a>
                        <a href="booking.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'active' : ''; ?>">
                            <i class="fas fa-bookmark"></i> Booking Ruangan
                        </a>
                        <a href="riwayat_booking.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'riwayat_booking.php') ? 'active' : ''; ?>">
                            <i class="fas fa-history"></i> Riwayat Booking
                        </a>
                    <?php endif; ?>
                    
                    <!-- Menu Umum -->
                    <div class="sidebar-heading">
                        <i class="fas fa-cogs me-1"></i> Umum
                    </div>
                    <a href="bantuan.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'bantuan.php') ? 'active' : ''; ?>">
                        <i class="fas fa-question-circle"></i> Bantuan
                    </a>
                </div>
                
                <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    <?php
                    // Tampilkan pesan jika ada
                    $message = getMessage();
                    if ($message) {
                        echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">
                            <i class="fas fa-' . ($message['type'] == 'success' ? 'check-circle' : ($message['type'] == 'danger' ? 'exclamation-triangle' : 'info-circle')) . ' me-2"></i>
                            ' . $message['message'] . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                    }
                    ?>
            <?php else: ?>
                <div class="col-12">
            <?php endif; ?>


</body>
</html>