<!-- Sidebar -->
<aside class="custom-sidebar" id="customSidebar">
    <!-- Hamburger Button di dalam sidebar -->
    <div class="sidebar-toggle-container">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
    </div>
    
    <div class="sidebar-header">
        <img src="../img/majelis.png" alt="Majelis MDPL" class="sidebar-logo" />
        <div class="sidebar-title">Majelis MDPL</div>
    </div>
    
    <nav class="custom-sidebar-nav">
        <a href="index.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" data-tooltip="Dashboard">
            <i class="bi bi-bar-chart"></i>
            <span class="link-text">Dashboard</span>
        </a>
        <a href="trip.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'trip.php' ? 'active' : '' ?>" data-tooltip="Trip">
            <i class="bi bi-signpost-split"></i>
            <span class="link-text">Trip</span>
        </a>
        <a href="peserta.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'peserta.php' ? 'active' : '' ?>" data-tooltip="Peserta">
            <i class="bi bi-people"></i>
            <span class="link-text">Peserta</span>
        </a>
        <a href="pembayaran.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>" data-tooltip="Pembayaran">
            <i class="bi bi-credit-card"></i>
            <span class="link-text">Pembayaran</span>
        </a>
        <a href="galeri.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'galeri.php' ? 'active' : '' ?>" data-tooltip="Galeri">
            <i class="bi bi-images"></i>
            <span class="link-text">Galeri</span>
        </a>
        <a href="#" class="sidebar-link logout-link" onclick="confirmLogout()" data-tooltip="Logout">
            <i class="bi bi-box-arrow-right"></i>
            <span class="link-text">Logout</span>
        </a>
    </nav>
</aside>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Toast Colors */
.colored-toast.swal2-icon-success {
    background-color: #a5dc86 !important;
}
.colored-toast.swal2-icon-error {
    background-color: #f27474 !important;
}
.colored-toast.swal2-icon-warning {
    background-color: #f8bb86 !important;
}
.colored-toast.swal2-icon-info {
    background-color: #3fc3ee !important;
}
.colored-toast .swal2-title {
    color: white;
    font-size: 16px;
}

/* Custom Sidebar - Default terbuka */
.custom-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #be8859ff; /* Warna yang diminta */
    z-index: 1055;
    transition: width 0.3s ease;
    overflow: visible;
    box-shadow: 2px 0 15px rgba(0,0,0,0.1);
}

/* Ketika sidebar collapsed - jadi mini sidebar */
.custom-sidebar.collapsed {
    width: 70px; /* Mini sidebar dengan icon saja */
}

/* Hamburger Button Container */
.sidebar-toggle-container {
    padding: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

/* Sidebar Toggle Button di dalam sidebar */
.sidebar-toggle {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-toggle:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}

/* Ketika collapsed, sembunyikan logo dan title */
.custom-sidebar.collapsed .sidebar-header {
    padding: 10px 5px;
}

.custom-sidebar.collapsed .sidebar-logo {
    width: 35px;
    height: 35px;
    margin-bottom: 0;
}

.custom-sidebar.collapsed .sidebar-title {
    display: none;
}

.sidebar-logo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 3px solid white;
    transition: all 0.3s ease;
}

.sidebar-title {
    color: white;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Sidebar Navigation */
.custom-sidebar-nav {
    padding: 15px 10px;
    overflow-y: auto;
    height: calc(100vh - 160px);
}

.sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    border-radius: 10px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
    position: relative;
}

.sidebar-link:hover {
    background: rgba(255,255,255,0.2);
    color: white;
    transform: translateX(3px);
    text-decoration: none;
}

.sidebar-link.active {
    background: rgba(255,255,255,0.25);
    color: white;
    font-weight: 600;
}

.sidebar-link i {
    font-size: 18px;
    width: 25px;
    text-align: center;
    margin-right: 15px;
    transition: all 0.3s ease;
}

.sidebar-link .link-text {
    transition: all 0.3s ease;
    white-space: nowrap;
}

/* Ketika sidebar collapsed */
.custom-sidebar.collapsed .sidebar-link {
    justify-content: center;
    padding: 12px 8px;
    margin: 5px;
}

.custom-sidebar.collapsed .sidebar-link i {
    margin: 0;
    font-size: 20px;
}

.custom-sidebar.collapsed .link-text {
    display: none;
}

/* Tooltip untuk mini sidebar */
.custom-sidebar.collapsed .sidebar-link:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.custom-sidebar.collapsed .sidebar-link:hover::before {
    content: '';
    position: absolute;
    left: 65px;
    top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-right-color: #333;
    z-index: 1000;
}

.sidebar-link.logout-link {
    margin-top: 20px;
    background: rgba(220, 53, 69, 0.3);
    border: 1px solid rgba(220, 53, 69, 0.4);
}

.sidebar-link.logout-link:hover {
    background: rgba(220, 53, 69, 0.4);
    color: white;
}

/* Main content adjustment */
.main-content,
.main,
.container-fluid,
.content {
    margin-left: 280px; /* Default dengan sidebar terbuka */
    transition: margin-left 0.3s ease;
    padding: 20px;
}

/* Ketika sidebar collapsed */
body.sidebar-collapsed .main-content,
body.sidebar-collapsed .main,
body.sidebar-collapsed .container-fluid,
body.sidebar-collapsed .content {
    margin-left: 70px; /* Adjust untuk mini sidebar */
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .custom-sidebar {
        left: -280px; /* Default tertutup di mobile */
        width: 280px;
    }
    
    .custom-sidebar.mobile-open {
        left: 0;
        width: 280px;
    }
    
    .main-content,
    .main,
    .container-fluid,
    .content {
        margin-left: 0 !important; /* Selalu full width di mobile */
    }
    
    /* Mobile overlay */
    .mobile-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .mobile-overlay.show {
        opacity: 1;
        visibility: visible;
    }
}
</style>

<script>
// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('customSidebar');
    
    // Check if mobile
    const isMobile = window.innerWidth <= 768;
    
    // Set initial state
    if (isMobile) {
        // Mobile: default tertutup
        sidebar.style.left = '-280px';
        document.body.classList.add('sidebar-collapsed');
        
        // Create mobile overlay
        const mobileOverlay = document.createElement('div');
        mobileOverlay.className = 'mobile-overlay';
        mobileOverlay.id = 'mobileOverlay';
        document.body.appendChild(mobileOverlay);
        
        // Mobile overlay click handler
        mobileOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    } else {
        // Desktop: default terbuka
        sidebar.classList.remove('collapsed');
        document.body.classList.remove('sidebar-collapsed');
    }
    
    // Toggle sidebar function
    function toggleSidebar() {
        if (isMobile) {
            // Mobile behavior - full show/hide
            const isOpen = sidebar.classList.contains('mobile-open');
            if (isOpen) {
                sidebar.classList.remove('mobile-open');
                sidebar.style.left = '-280px';
                const overlay = document.getElementById('mobileOverlay');
                if (overlay) overlay.classList.remove('show');
            } else {
                sidebar.classList.add('mobile-open');
                sidebar.style.left = '0';
                const overlay = document.getElementById('mobileOverlay');
                if (overlay) overlay.classList.add('show');
            }
        } else {
            // Desktop behavior - expand/collapse
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
        }
    }
    
    // Close sidebar function
    function closeSidebar() {
        if (isMobile) {
            sidebar.classList.remove('mobile-open');
            sidebar.style.left = '-280px';
            const overlay = document.getElementById('mobileOverlay');
            if (overlay) overlay.classList.remove('show');
        }
    }
    
    // Toggle button click handler
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Auto close on navigation (mobile only)
    const sidebarLinks = document.querySelectorAll('.sidebar-link:not(.logout-link)');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile) {
                setTimeout(closeSidebar, 150);
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const newIsMobile = window.innerWidth <= 768;
        if (newIsMobile !== isMobile) {
            location.reload(); // Reload untuk reset state
        }
    });
});

// Toast function
function showToast(type, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            popup: 'colored-toast'
        }
    });
}

// Logout confirmation
function confirmLogout() {
    Swal.fire({
        title: 'Konfirmasi Logout',
        text: 'Yakin ingin keluar dari sistem?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        width: '400px'
    }).then((result) => {
        if (result.isConfirmed) {
            showToast('success', 'Logging out...');
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'logout.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirm_logout';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            
            setTimeout(function() {
                form.submit();
            }, 1000);
        }
    });
}
</script>
