<aside class="sidebar" id="sidebar">
  <div class="logo">
      <h1>RieeTech.</h1>
  </div>
  
  <nav>
      
    <?php if(isAdmin()): ?>
      <div class="nav-section">
          <h3>Admin Menu</h3>
          <a href="?page=default" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'default') ? 'active' : ''; ?>" data-icon="dashboard">Dashboard</a>

          <a href="?page=dataRuangan" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'dataRuangan') ? 'active' : ''; ?>" data-icon="ruangan">Kelola Ruangan</a>

          <a href="?page=dataJadwal" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'dataJadwal') ? 'active' : ''; ?>" data-icon="jadwal">Kelola Jadwal</a>

          <a href="?page=kelolaBooking" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'kelolaBooking') ? 'active' : ''; ?>" data-icon="booking">Kelola Booking</a>

          <a href="?page=laporan" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'laporan') ? 'active' : ''; ?>" data-icon="laporan">Laporan</a>
      </div>
      
      <?php else: ?>
        <div class="nav-section">
          <h3>Mahasiswa Menu</h3>
          <a href="?page=default" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'default') ? 'active' : ''; ?>" data-icon="dashboard">Dashboard</a>

          <a href="?page=booking" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'booking') ? 'active' : ''; ?>" data-icon="ruangan">Booking Ruangan</a>

          <a href="?page=myBooking" class="nav-item <?= (isset($_GET['page']) && $_GET['page'] == 'myBooking') ? 'active' : ''; ?>" data-icon="riwayat">Riwayat Booking</a>
          
        </div>
    <?php endif; ?>
      <div class="nav-section">
          <h3>Account</h3>
          <a href="logout.php" class="nav-item" data-icon="login">Logout</a>
          
      </div>
  </nav>
</aside>