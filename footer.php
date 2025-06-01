<?php
// footer.php - Footer untuk semua halaman
?>

            <?php if (isLoggedIn()): ?>
                </div> <!-- Close col-md-9 -->
            <?php else: ?>
                </div> <!-- Close col-12 -->
            <?php endif; ?>
        </div> <!-- Close row -->
    </div> <!-- Close container-fluid main-content -->

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="footer-brand mb-2">
                        <i class="fas fa-building me-2"></i>Sistem Booking Ruangan
                    </div>
                    <p class="mb-0 small">
                        Sistem manajemen booking ruangan yang mudah dan efisien untuk kebutuhan akademik.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="ruangan.php" class="small"><i class="fas fa-door-open me-2"></i>Kelola Ruangan</a></li>
                                <li><a href="booking_admin.php" class="small"><i class="fas fa-clipboard-list me-2"></i>Kelola Booking</a></li>
                            <?php else: ?>
                                <li><a href="booking.php" class="small"><i class="fas fa-bookmark me-2"></i>Booking Ruangan</a></li>
                                <li><a href="jadwal_mahasiswa.php" class="small"><i class="fas fa-calendar-alt me-2"></i>Jadwal Ruangan</a></li>
                            <?php endif; ?>
                            <li><a href="bantuan.php" class="small"><i class="fas fa-question-circle me-2"></i>Bantuan</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">Informasi Kontak</h6>
                    <div class="small">
                        <p class="mb-1">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:admin@booking.com">admin@booking.com</a>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:+62123456789">+62 123 456 789</a>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Jl. Kampus No. 123, Kota
                        </p>
                    </div>
                </div>
            </div>
            <hr class="my-3" style="border-color: #495057;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 small">
                        &copy; <?php echo date('Y'); ?> Sistem Booking Ruangan. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="small">
                        <?php if (isLoggedIn()): ?>
                            <span class="me-3">
                                <i class="fas fa-user me-1"></i>
                                Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?>
                            </span>
                        <?php endif; ?>
                        <span id="footer-time"></span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update jam real-time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Update jam di navbar
            const currentTimeElement = document.getElementById('current-time');
            if (currentTimeElement) {
                currentTimeElement.textContent = timeString;
            }
            
            // Update jam di footer
            const footerTimeElement = document.getElementById('footer-time');
            if (footerTimeElement) {
                footerTimeElement.innerHTML = `<i class="fas fa-clock me-1"></i>${timeString}`;
            }
        }
        
        // Update setiap detik
        setInterval(updateTime, 1000);
        updateTime(); // Panggil sekali saat halaman dimuat
        
        // Auto-dismiss alerts setelah 5 detik
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Konfirmasi logout
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLink = document.querySelector('a[href="logout.php"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin logout?')) {
                        e.preventDefault();
                    }
                });
            }
        });
        
        // Tooltip activation
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    <?php
    // Include additional scripts jika ada
    if (isset($additional_scripts)) {
        foreach ($additional_scripts as $script) {
            echo '<script src="' . $script . '"></script>';
        }
    }
    
    // Include custom page scripts
    if (isset($page_scripts)) {
        echo '<script>' . $page_scripts . '</script>';
    }
    ?>