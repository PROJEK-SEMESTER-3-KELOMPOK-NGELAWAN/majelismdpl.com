<?php
require_once 'auth_check.php';
?>

<!-- Sidebar -->
<aside class="custom-sidebar" id="customSidebar">
    <!-- Hamburger Button di dalam sidebar -->
    <div class="sidebar-toggle-container">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
    </div>
    
    <div class="sidebar-header">
        <img src="../assets/majelis.png" alt="Majelis MDPL" class="sidebar-logo" />
        <div class="sidebar-title">Majelis MDPL</div>
    </div>
    
    <nav class="custom-sidebar-nav">
        <a href="index.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" data-tooltip="Dashboard">
            <i class="bi bi-bar-chart"></i>
            <span class="link-text">Dashboard</span>
        </a>
        
        <?php if (RoleHelper::canAccessMasterAdmin($user_role)): ?>
        <a href="master-admin.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'master-admin.php' ? 'active' : '' ?>" data-tooltip="Master Admin">
            <i class="bi bi-person-gear"></i>
            <span class="link-text">Master Admin</span>
        </a>
        <?php endif; ?>
        
        <?php if (RoleHelper::canManageTrips($user_role)): ?>
        <a href="trip.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'trip.php' ? 'active' : '' ?>" data-tooltip="Trip">
            <i class="bi bi-signpost-split"></i>
            <span class="link-text">Trip</span>
        </a>
        <?php endif; ?>
        
        <?php if (RoleHelper::canManageParticipants($user_role)): ?>
        <a href="peserta.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'peserta.php' ? 'active' : '' ?>" data-tooltip="Peserta">
            <i class="bi bi-people"></i>
            <span class="link-text">Peserta</span>
        </a>
        <?php endif; ?>
        
        <?php if (RoleHelper::canManagePayments($user_role)): ?>
        <a href="pembayaran.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>" data-tooltip="Pembayaran">
            <i class="bi bi-credit-card"></i>
            <span class="link-text">Pembayaran</span>
        </a>
        <?php endif; ?>
        
        <?php if (RoleHelper::canManageGallery($user_role)): ?>
        <a href="galeri.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'galeri.php' ? 'active' : '' ?>" data-tooltip="Galeri">
            <i class="bi bi-images"></i>
            <span class="link-text">Galeri</span>
        </a>
        <?php endif; ?>
        
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

/* Custom Sidebar - Default terbuka dengan scroll */
.custom-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #a97c50;
    z-index: 1055;
    transition: width 0.3s ease;
    overflow: hidden;
    box-shadow: 2px 0 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

/* Ketika sidebar collapsed - jadi mini sidebar */
.custom-sidebar.collapsed {
    width: 70px;
}

/* Hamburger Button Container - Fixed position */
.sidebar-toggle-container {
    padding: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
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

/* Sidebar Header - Fixed position */
.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
    flex-shrink: 0;
}

/* User Info Styling */
.user-info {
    margin-top: 10px;
}

.user-info .badge {
    font-size: 0.75em;
    padding: 4px 8px;
}

/* Custom brown badge */
.bg-brown {
    background-color: #a97c50 !important;
    color: white;
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

.custom-sidebar.collapsed .sidebar-title,
.custom-sidebar.collapsed .user-info {
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

/* Sidebar Navigation - Scrollable area */
.custom-sidebar-nav {
    flex: 1;
    padding: 15px 10px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.3) transparent;
}

/* Custom scrollbar untuk webkit browsers */
.custom-sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.custom-sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.custom-sidebar-nav::-webkit-scrollbar-thumb {
    background-color: rgba(255,255,255,0.3);
    border-radius: 3px;
    transition: background-color 0.3s ease;
}

.custom-sidebar-nav::-webkit-scrollbar-thumb:hover {
    background-color: rgba(255,255,255,0.5);
}

.custom-sidebar.collapsed .custom-sidebar-nav::-webkit-scrollbar {
    width: 4px;
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
    flex-shrink: 0;
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
    flex-shrink: 0;
}

.sidebar-link .link-text {
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    pointer-events: none;
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
    pointer-events: none;
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
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    padding: 20px;
    min-height: 100vh;
}

/* Ketika sidebar collapsed */
body.sidebar-collapsed .main-content,
body.sidebar-collapsed .main,
body.sidebar-collapsed .container-fluid,
body.sidebar-collapsed .content {
    margin-left: 70px;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .custom-sidebar {
        left: -280px;
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
        margin-left: 0 !important;
    }
    
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

html {
    scroll-behavior: smooth;
}

body {
    overflow-x: hidden;
}
</style>

<script>
// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('customSidebar');
    
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        sidebar.style.left = '-280px';
        document.body.classList.add('sidebar-collapsed');
        
        const mobileOverlay = document.createElement('div');
        mobileOverlay.className = 'mobile-overlay';
        mobileOverlay.id = 'mobileOverlay';
        document.body.appendChild(mobileOverlay);
        
        mobileOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    } else {
        sidebar.classList.remove('collapsed');
        document.body.classList.remove('sidebar-collapsed');
    }
    
    function toggleSidebar() {
        if (isMobile) {
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
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
        }
    }
    
    function closeSidebar() {
        if (isMobile) {
            sidebar.classList.remove('mobile-open');
            sidebar.style.left = '-280px';
            const overlay = document.getElementById('mobileOverlay');
            if (overlay) overlay.classList.remove('show');
        }
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    const sidebarLinks = document.querySelectorAll('.sidebar-link:not(.logout-link)');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile) {
                setTimeout(closeSidebar, 150);
            }
        });
    });
    
    window.addEventListener('resize', function() {
        const newIsMobile = window.innerWidth <= 768;
        if (newIsMobile !== isMobile) {
            location.reload();
        }
    });
    
    const sidebarNav = document.querySelector('.custom-sidebar-nav');
    if (sidebarNav) {
        sidebarNav.style.webkitOverflowScrolling = 'touch';
    }
});

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
