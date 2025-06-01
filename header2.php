<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RieeTech Dashboard</title>
    
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: white;
            border-right: 1px solid #e2e8f0;
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .logo {
            padding: 0 1.5rem 2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .logo::before {
            content: '</>';
            color: #3b82f6;
            margin-right: 0.5rem;
            font-weight: bold;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section h3 {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0 1.5rem;
            margin-bottom: 0.75rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
            border-right: 3px solid transparent;
        }

        .nav-item:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .nav-item.active {
            background-color: #eff6ff;
            color: #3b82f6;
            border-right-color: #3b82f6;
        }

        .nav-item::before {
            content: '';
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            background-size: contain;
            opacity: 0.7;
        }

        .nav-item[data-icon="dashboard"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'/%3E%3C/svg%3E");
        }

        .nav-item[data-icon="book"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/%3E%3C/svg%3E");
        }

        .nav-item[data-icon="clipboard"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'/%3E%3C/svg%3E");
        }

        .nav-item[data-icon="star"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'/%3E%3C/svg%3E");
        }

        .nav-item[data-icon="login"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'/%3E%3C/svg%3E");
        }

        .nav-item[data-icon="register"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'/%3E%3C/svg%3E");
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Navbar */
        .navbar {
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 2rem 0 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: margin 0.3s ease, border-radius 0.3s ease;
        }

        .navbar.full-width {
            margin: 0;
            border-radius: 0;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .menu-toggle:hover {
            background-color: #f1f5f9;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            width: 300px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            color: #9ca3af;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notifications {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
            position: relative;
        }

        .notifications:hover {
            background-color: #f1f5f9;
        }

        .notifications::after {
            content: '';
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            width: 0.5rem;
            height: 0.5rem;
            background-color: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .user-profile:hover {
            background-color: #f1f5f9;
        }

        .user-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-name {
            font-weight: 500;
            color: #1e293b;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 2rem;
            margin: 0 2rem 2rem 2rem;
            transition: margin 0.3s ease;
        }

        .content.full-width {
            margin: 0;
        }

        .welcome-section {
            text-align: center;
            padding: 4rem 2rem;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .welcome-subtitle {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .welcome-button {
            background-color: #3b82f6;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .welcome-button:hover {
            background-color: #2563eb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                height: 100vh;
                height: 100dvh; /* Dynamic viewport height for mobile browsers */
                top: 0;
                left: 0;
                position: fixed;
                width: 260px;
            }

            .sidebar:not(.collapsed) {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .navbar {
                margin: 1rem;
            }

            .navbar.full-width {
                margin: 0;
            }

            .search-input {
                width: 200px;
            }

            .content {
                margin: 0 1rem 1rem 1rem;
            }

            .content.full-width {
                margin: 0;
            }

            /* Overlay for mobile sidebar */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                height: 100dvh; /* Dynamic viewport height */
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <h1>RieeTech.</h1>
            </div>
            
            <nav>
                <div class="nav-section">
                    <a href="#" class="nav-item active" data-icon="dashboard">Dashboard</a>
                </div>

                <div class="nav-section">
                    <h3>UI Components</h3>
                    <a href="#" class="nav-item" data-icon="book">Data Buku</a>
                    <a href="#" class="nav-item" data-icon="clipboard">Data Peminjaman</a>
                    <a href="#" class="nav-item" data-icon="star">Icons</a>
                </div>

                <div class="nav-section">
                    <h3>Pages</h3>
                    <a href="#" class="nav-item" data-icon="login">Login</a>
                    <a href="#" class="nav-item" data-icon="register">Register</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Navbar -->
            <header class="navbar" id="navbar">
                <div class="navbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <div>
                        <h3 class="mb-2">Sistem Informasi Booking Ruangan</h3>
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
                        <span class="user-name">Stebin Ben</span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content" id="content">
                <div class="welcome-section">
                    <h1 class="welcome-title">Hello World</h1>
                    <p class="welcome-subtitle">Selamat datang di dashboard RieeTech</p>
                    <button class="welcome-button">Get Started</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Menu toggle functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const navbar = document.getElementById('navbar');
        const content = document.getElementById('content');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Check if we're on mobile
        function isMobile() {
            return window.innerWidth <= 768;
        }

        // Set initial state based on screen size
        let sidebarCollapsed = isMobile(); // Collapsed on mobile, open on desktop

        function toggleSidebar() {
            sidebarCollapsed = !sidebarCollapsed;
            
            if (isMobile()) {
                // Mobile behavior
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    sidebarOverlay.classList.remove('active');
                } else {
                    sidebar.classList.remove('collapsed');
                    sidebarOverlay.classList.add('active');
                }
            } else {
                // Desktop behavior
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    navbar.classList.add('full-width');
                    content.classList.add('full-width');
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    navbar.classList.remove('full-width');
                    content.classList.remove('full-width');
                }
            }
        }

        menuToggle.addEventListener('click', toggleSidebar);

        // Close sidebar when overlay is clicked (mobile)
        sidebarOverlay.addEventListener('click', () => {
            if (isMobile() && !sidebarCollapsed) {
                toggleSidebar();
            }
        });

        // Navigation active state
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
                
                // Close sidebar on mobile after selecting nav item
                if (isMobile() && !sidebarCollapsed) {
                    toggleSidebar();
                }
            });
        });

        // Handle window resize
        function handleResize() {
            const wasMobile = sidebarCollapsed && !isMobile();
            
            if (!isMobile()) {
                // Desktop: Remove mobile-specific classes
                sidebarOverlay.classList.remove('active');
                
                // If switching from mobile to desktop, show sidebar by default
                if (wasMobile) {
                    sidebarCollapsed = false;
                }
                
                // Apply desktop layout based on current state
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    navbar.classList.add('full-width');
                    content.classList.add('full-width');
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    navbar.classList.remove('full-width');
                    content.classList.remove('full-width');
                }
            } else {
                // Mobile: Always use mobile layout
                mainContent.classList.add('expanded');
                navbar.classList.add('full-width');
                content.classList.add('full-width');
                
                // If switching from desktop to mobile, collapse sidebar
                if (!wasMobile && !sidebarCollapsed) {
                    sidebarCollapsed = true;
                }
                
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    sidebarOverlay.classList.remove('active');
                } else {
                    sidebar.classList.remove('collapsed');
                    sidebarOverlay.classList.add('active');
                }
            }
        }

        // Initial setup
        function initializeLayout() {
            sidebarCollapsed = isMobile();
            handleResize();
        }

        initializeLayout();
        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>