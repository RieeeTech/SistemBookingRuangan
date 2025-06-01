<?php
// header.php - Header untuk semua halaman
session_start();
require_once 'configPage.php';
require_once 'config.php';


// Jika belum login, redirect ke halaman login

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RieeTech Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap 5 Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <?php include('pages/sidebar.php') ?>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Navbar -->
            <?php include('pages/navbar.php') ?>

            <!-- Content -->
            <div class="content" id="content">
              <?= eval($main) ?>
              <!-- Button trigger modal -->

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Modal functionality - only if modal exists
    const myModal = document.getElementById('myModal');
    const myInput = document.getElementById('myInput');
    
    if (myModal && myInput) {
        myModal.addEventListener('shown.bs.modal', () => {
            myInput.focus();
        });
    }
    
    // Menu toggle functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const navbar = document.getElementById('navbar');
    const content = document.getElementById('content');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Check if all required elements exist
    if (!menuToggle || !sidebar || !mainContent) {
        console.warn('Required elements for sidebar functionality not found');
        return;
    }

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
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            } else {
                sidebar.classList.remove('collapsed');
                if (sidebarOverlay) sidebarOverlay.classList.add('active');
            }
        } else {
            // Desktop behavior
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                if (navbar) navbar.classList.add('full-width');
                if (content) content.classList.add('full-width');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                if (navbar) navbar.classList.remove('full-width');
                if (content) content.classList.remove('full-width');
            }
        }
    }

    menuToggle.addEventListener('click', toggleSidebar);

    // Close sidebar when overlay is clicked (mobile)
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            if (isMobile() && !sidebarCollapsed) {
                toggleSidebar();
            }
        });
    }

    // Handle window resize
    function handleResize() {
        const wasMobile = sidebarCollapsed && !isMobile();
        
        if (!isMobile()) {
            // Desktop: Remove mobile-specific classes
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            
            // If switching from mobile to desktop, show sidebar by default
            if (wasMobile) {
                sidebarCollapsed = false;
            }
            
            // Apply desktop layout based on current state
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                if (navbar) navbar.classList.add('full-width');
                if (content) content.classList.add('full-width');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                if (navbar) navbar.classList.remove('full-width');
                if (content) content.classList.remove('full-width');
            }
        } else {
            // Mobile: Always use mobile layout
            mainContent.classList.add('expanded');
            if (navbar) navbar.classList.add('full-width');
            if (content) content.classList.add('full-width');
            
            // If switching from desktop to mobile, collapse sidebar
            if (!wasMobile && !sidebarCollapsed) {
                sidebarCollapsed = true;
            }
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            } else {
                sidebar.classList.remove('collapsed');
                if (sidebarOverlay) sidebarOverlay.classList.add('active');
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

    // Handle Bootstrap conflicts more carefully
    // Only remove Bootstrap attributes from non-modal elements
    setTimeout(() => {
        const bootstrapElements = document.querySelectorAll('[data-bs-toggle]:not([data-bs-toggle="modal"]), [data-bs-target]:not([data-bs-target*="modal"])');
        bootstrapElements.forEach(element => {
            // Only remove if it's not related to modal functionality
            if (!element.getAttribute('data-bs-target')?.includes('modal') && 
                element.getAttribute('data-bs-toggle') !== 'modal') {
                element.removeAttribute('data-bs-toggle');
                element.removeAttribute('data-bs-target');
            }
        });
    }, 100); // Small delay to ensure Bootstrap has initialized
});
    </script>
</body>
</html>