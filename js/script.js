// Menu toggle functionality
const menuToggle = document.getElementById("menuToggle");
const sidebar = document.getElementById("sidebar");
const mainContent = document.getElementById("mainContent");
const navbar = document.getElementById("navbar");
const content = document.getElementById("content");
const sidebarOverlay = document.getElementById("sidebarOverlay");

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
      sidebar.classList.add("collapsed");
      sidebarOverlay.classList.remove("active");
    } else {
      sidebar.classList.remove("collapsed");
      sidebarOverlay.classList.add("active");
    }
  } else {
    // Desktop behavior
    if (sidebarCollapsed) {
      sidebar.classList.add("collapsed");
      mainContent.classList.add("expanded");
      navbar.classList.add("full-width");
      content.classList.add("full-width");
    } else {
      sidebar.classList.remove("collapsed");
      mainContent.classList.remove("expanded");
      navbar.classList.remove("full-width");
      content.classList.remove("full-width");
    }
  }
}

menuToggle.addEventListener("click", toggleSidebar);

// Close sidebar when overlay is clicked (mobile)
sidebarOverlay.addEventListener("click", () => {
  if (isMobile() && !sidebarCollapsed) {
    toggleSidebar();
  }
});

// Navigation active state
const navItems = document.querySelectorAll(".nav-item");
navItems.forEach(item => {
  item.addEventListener("click", e => {
    e.preventDefault();
    navItems.forEach(nav => nav.classList.remove("active"));
    item.classList.add("active");

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
    sidebarOverlay.classList.remove("active");

    // If switching from mobile to desktop, show sidebar by default
    if (wasMobile) {
      sidebarCollapsed = false;
    }

    // Apply desktop layout based on current state
    if (sidebarCollapsed) {
      sidebar.classList.add("collapsed");
      mainContent.classList.add("expanded");
      navbar.classList.add("full-width");
      content.classList.add("full-width");
    } else {
      sidebar.classList.remove("collapsed");
      mainContent.classList.remove("expanded");
      navbar.classList.remove("full-width");
      content.classList.remove("full-width");
    }
  } else {
    // Mobile: Always use mobile layout
    mainContent.classList.add("expanded");
    navbar.classList.add("full-width");
    content.classList.add("full-width");

    // If switching from desktop to mobile, collapse sidebar
    if (!wasMobile && !sidebarCollapsed) {
      sidebarCollapsed = true;
    }

    if (sidebarCollapsed) {
      sidebar.classList.add("collapsed");
      sidebarOverlay.classList.remove("active");
    } else {
      sidebar.classList.remove("collapsed");
      sidebarOverlay.classList.add("active");
    }
  }
}

// Initial setup
function initializeLayout() {
  sidebarCollapsed = isMobile();
  handleResize();
}

initializeLayout();
window.addEventListener("resize", handleResize);

// Prevent Bootstrap from interfering with custom interactions
document.addEventListener("DOMContentLoaded", function () {
  // Ensure our custom event handlers take precedence
  const bootstrapElements = document.querySelectorAll(
    "[data-bs-toggle], [data-bs-target]"
  );
  bootstrapElements.forEach(element => {
    element.removeAttribute("data-bs-toggle");
    element.removeAttribute("data-bs-target");
  });
});
