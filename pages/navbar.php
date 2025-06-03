<header class="custom-navbar" id="navbar">
                <div class="navbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <div class="search-container">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" class="search-input" placeholder="Sistem Informasi Booking Ruangan">
                    </div>
                </div>

                <div class="navbar-right">
                    <button class="notifications">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6.5A2.5 2.5 0 014 16.5v-7A2.5 2.5 0 016.5 7H9V6a3 3 0 016 0v1h2.5A2.5 2.5 0 0120 9.5v1"/>
                        </svg>
                    </button>

                    <div class="user-profile">
                        <div class="user-avatar">SB</div>
                        <span class="user-name"><?= $_SESSION['nama_lengkap']; ?></span>
                    </div>
                </div>
            </header>