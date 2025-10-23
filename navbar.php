<?php
// navbar.php
// Cek status login user (sesuaikan dengan sistem session dari login-api.php)
$isLoggedIn = isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
$userName = $isLoggedIn ? ($_SESSION['username'] ?? 'User') : '';
?>

<!-- Load Google Fonts Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

<!-- Load Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

<!-- Load FontAwesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

<nav class="navbar" role="navigation" aria-label="Main Navigation">
  <div class="navbar-logo">
    <img src="img/majelis.png" alt="Logo Majelis MDPL" class="logo-img" />
  </div>

  <button class="hamburger" id="hamburgerBtn" aria-label="Toggle Menu" aria-expanded="false" aria-controls="navbarMenu">
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
  </button>

  <ul class="navbar-menu" id="navbarMenu" role="menu">
    <li><a href="index.php" class="active" role="menuitem"><i class="fa-solid fa-house"></i> Home</a></li>
    <li><a href="#" role="menuitem"><i class="fa-solid fa-user"></i> Profile</a></li>
    <li><a href="#" role="menuitem"><i class="fa-solid fa-calendar-days"></i> Jadwal Pendakian</a></li>
    <li><a href="#" role="menuitem"><i class="fa-solid fa-image"></i> Galeri</a></li>
    <li><a href="#" role="menuitem"><i class="fa-solid fa-comment-dots"></i> Testimoni</a></li>
  </ul>

  <?php if (!$isLoggedIn): ?>
    <!-- Tampilkan tombol Sign Up dan Login jika belum login -->
    <div class="nav-btns">
      <a href="#" id="open-signup" class="btn">Sign Up</a>
      <a href="#" id="open-login" class="btn">Login</a>
    </div>
  <?php else: ?>
    <!-- Tampilkan User Menu jika sudah login -->
    <div class="user-menu-container">
      <button class="user-menu-toggle" id="userMenuToggle" aria-label="User Menu" aria-expanded="false">
        <i class="fa-solid fa-user-circle"></i>
        <span class="user-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
        <i class="fa-solid fa-chevron-down dropdown-icon"></i>
      </button>
      
      <div class="user-dropdown" id="userDropdown">
        <a href="profile.php" class="dropdown-item">
          <i class="fa-solid fa-user"></i> Profil
        </a>
        <a href="my-trips.php" class="dropdown-item">
          <i class="fa-solid fa-mountain"></i> Paket Trip Saya
        </a>
        <a href="payment-status.php" class="dropdown-item">
          <i class="fa-solid fa-credit-card"></i> Status Pembayaran
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" id="logout-btn" class="dropdown-item logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>
  <?php endif; ?>
</nav>

<!-- Form tersembunyi untuk logout -->
<form id="logout-form" method="POST" action="logout.php" style="display: none;">
  <input type="hidden" name="confirm_logout" value="1">
</form>

<!-- Custom Logout Modal -->
<div id="logout-modal" class="logout-modal-overlay">
  <div class="logout-modal-container">
    <div class="logout-modal-icon">
      <i class="fa-solid fa-exclamation"></i>
    </div>
    <h2 class="logout-modal-title">Konfirmasi Logout</h2>
    <p class="logout-modal-text">Apakah Anda Yakin Ingin Logout?</p>
    <div class="logout-modal-buttons">
      <button id="confirm-logout-btn" class="logout-btn-confirm">Ya, Logout</button>
      <button id="cancel-logout-btn" class="logout-btn-cancel">Batal</button>
    </div>
  </div>
</div>

<style>
  /* Navbar Glassmorphism */
  * {
    box-sizing: border-box;
  }

  .navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100vw;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(9px);
    box-shadow: none;
    padding: 10px 40px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .navbar.scrolled {
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 4px 18px rgba(87, 87, 244, 0.13);
    padding: 12px 40px;
  }

  .navbar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .logo-img {
    height: 50px;
    width: auto;
    object-fit: contain;
    display: inline-block;
    vertical-align: middle;
  }

  .navbar-menu {
    list-style: none;
    display: flex;
    gap: 28px;
    margin: 0;
    padding: 0;
  }

  .navbar-menu li {
    display: flex;
    align-items: center;
  }

  .navbar-menu a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #222;
    font-weight: 500;
    padding: 7px 19px;
    border-radius: 19px;
    font-size: 1.07em;
    transition: background 0.2s, color 0.2s;
    gap: 7px;
  }

  .navbar-menu a.active,
  .navbar-menu a:hover {
    background: #a97c50;
    color: #fff;
  }

  .navbar-menu a i {
    font-size: 1.22em;
    margin-right: 8px;
    color: #000000;
    transition: transform 0.23s cubic-bezier(0.54, 0.14, 0.23, 1.12), color 0.15s;
  }

  .navbar-menu a:hover i,
  .navbar-menu a.active i {
    transform: scale(1.25) rotate(-13deg) translateY(-5px);
    color: #ffffff;
    animation: navbarBounce 0.44s cubic-bezier(0.39, 1.6, 0.63, 1) 1;
  }

  @keyframes navbarBounce {
    0% { transform: scale(1.07) rotate(-8deg) translateY(0); }
    28% { transform: scale(1.28) rotate(-13deg) translateY(-10px); }
    49% { transform: scale(1.24) rotate(-9deg) translateY(2px); }
    70% { transform: scale(1.2) rotate(-11deg) translateY(-3px); }
    100% { transform: scale(1.25) rotate(-13deg) translateY(-5px); }
  }

  .nav-btns {
    display: flex;
    gap: 10px;
  }

  .nav-btns .btn {
    padding: 9px 28px;
    border-radius: 22px;
    font-size: 1em;
    font-weight: 600;
    border: none;
    color: #fff;
    background: #b49666;
    text-decoration: none;
    transition: background 0.18s, color 0.17s;
    display: inline-block;
    box-shadow: 0 2px 8px rgba(168, 100, 48, 0.06);
    outline: none;
    cursor: pointer;
  }

  #open-signup.btn:hover,
  #open-login.btn:hover {
    background: #8b5e3c;
    color: #fff;
  }

  .user-menu-container {
    position: relative;
    display: flex;
    align-items: center;
  }

  .user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(169, 124, 80, 0.3);
    border-radius: 25px;
    color: #333;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
  }

  .user-menu-toggle:hover {
    background: rgba(169, 124, 80, 0.2);
    border-color: #a97c50;
  }

  .user-menu-toggle i:first-child {
    font-size: 1.5em;
    color: #a97c50;
  }

  .user-menu-toggle .dropdown-icon {
    font-size: 0.8em;
    transition: transform 0.3s ease;
  }

  .user-menu-toggle.active .dropdown-icon {
    transform: rotate(180deg);
  }

  .user-name {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 240px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(169, 124, 80, 0.2);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1100;
    overflow: hidden;
  }

  .user-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    font-size: 0.95em;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .dropdown-item:last-child {
    border-bottom: none;
  }

  .dropdown-item i {
    font-size: 1.1em;
    color: #a97c50;
    width: 20px;
    text-align: center;
  }

  .dropdown-item:hover {
    background: rgba(169, 124, 80, 0.1);
    padding-left: 24px;
  }

  .dropdown-item.logout {
    color: #d9534f;
    cursor: pointer;
  }

  .dropdown-item.logout i {
    color: #d9534f;
  }

  .dropdown-item.logout:hover {
    background: rgba(217, 83, 79, 0.1);
  }

  .dropdown-divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.1);
    margin: 8px 0;
  }

  .hamburger {
    display: none;
    background: none;
    border: none;
    flex-direction: column;
    justify-content: space-between;
    width: 26px;
    height: 19px;
    cursor: pointer;
    padding: 0;
    z-index: 1201;
  }

  .hamburger-line {
    width: 100%;
    height: 3px;
    background-color: #5757f4;
    border-radius: 10px;
    transition: all 0.3s ease;
  }

  .hamburger.active .hamburger-line:nth-child(1) {
    transform: translateY(8px) rotate(45deg);
  }

  .hamburger.active .hamburger-line:nth-child(2) {
    opacity: 0;
  }

  .hamburger.active .hamburger-line:nth-child(3) {
    transform: translateY(-8px) rotate(-45deg);
  }

  /* Custom Logout Modal Styles */
  .logout-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease;
  }

  .logout-modal-overlay.show {
    display: flex;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .logout-modal-container {
    background: #fff;
    border-radius: 20px;
    padding: 40px 30px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .logout-modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #d4a574 0%, #b49666 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
  }

  .logout-modal-icon i {
    font-size: 2.5em;
    color: #fff;
  }

  .logout-modal-title {
    font-size: 1.5em;
    font-weight: 700;
    color: #b49666;
    margin-bottom: 10px;
    font-family: 'Poppins', sans-serif;
  }

  .logout-modal-text {
    font-size: 1em;
    color: #666;
    margin-bottom: 30px;
    font-family: 'Poppins', sans-serif;
  }

  .logout-modal-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
  }

  .logout-btn-confirm,
  .logout-btn-cancel {
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
  }

  .logout-btn-confirm {
    background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(180, 150, 102, 0.3);
  }

  .logout-btn-confirm:hover {
    background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(180, 150, 102, 0.4);
  }

  .logout-btn-cancel {
    background: #6c757d;
    color: #fff;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
  }

  .logout-btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
  }

  /* Responsive */
  @media (max-width: 900px) {
    .navbar {
      padding: 10px 15px;
    }

    .navbar-menu {
      display: none;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.98);
      position: absolute;
      top: 60px;
      left: 0;
      width: 100vw;
      padding: 16px 0;
      gap: 10px;
      box-shadow: 0 4px 16px rgba(123, 93, 254, 0.13);
      z-index: 1200;
    }

    .navbar-menu.show {
      display: flex;
    }

    .navbar-menu a {
      margin-left: 24px;
      width: auto;
      font-size: 1.09rem;
      padding: 12px 16px;
    }

    .nav-btns {
      display: none;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.98);
      position: absolute;
      top: 60px;
      left: 0;
      width: 100vw;
      padding: 16px 24px;
      gap: 10px;
      box-shadow: 0 4px 16px rgba(123, 93, 254, 0.13);
      z-index: 1200;
    }

    .nav-btns.show {
      display: flex;
    }

    .nav-btns .btn {
      width: 100%;
      text-align: center;
    }

    .hamburger {
      display: flex;
    }

    .user-menu-container {
      display: none;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.98);
      position: absolute;
      top: 60px;
      right: 0;
      width: 100vw;
      padding: 16px 24px;
      box-shadow: 0 4px 16px rgba(123, 93, 254, 0.13);
      z-index: 1200;
    }

    .user-menu-container.show {
      display: flex;
    }

    .user-menu-toggle {
      width: 100%;
      justify-content: center;
      margin-bottom: 10px;
    }

    .user-dropdown {
      position: static;
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      box-shadow: none;
      border: 1px solid rgba(169, 124, 80, 0.2);
    }

    .logout-modal-container {
      padding: 30px 20px;
    }

    .logout-modal-buttons {
      flex-direction: column;
    }

    .logout-btn-confirm,
    .logout-btn-cancel {
      width: 100%;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const navbarMenu = document.getElementById('navbarMenu');
    const navBtns = document.querySelector('.nav-btns');
    const userMenuContainer = document.querySelector('.user-menu-container');
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');
    const logoutBtn = document.getElementById('logout-btn');
    const logoutForm = document.getElementById('logout-form');
    const logoutModal = document.getElementById('logout-modal');
    const confirmLogoutBtn = document.getElementById('confirm-logout-btn');
    const cancelLogoutBtn = document.getElementById('cancel-logout-btn');

    // Hamburger menu toggle (untuk mobile)
    if (hamburger) {
      hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navbarMenu.classList.toggle('show');
        
        if (navBtns) {
          navBtns.classList.toggle('show');
        }
        
        if (userMenuContainer) {
          userMenuContainer.classList.toggle('show');
        }

        const expanded = hamburger.getAttribute('aria-expanded') === 'true' || false;
        hamburger.setAttribute('aria-expanded', !expanded);
      });
    }

    // User menu dropdown toggle (untuk desktop)
    if (userMenuToggle && userDropdown) {
      userMenuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenuToggle.classList.toggle('active');
        userDropdown.classList.toggle('show');
        
        const expanded = userMenuToggle.getAttribute('aria-expanded') === 'true' || false;
        userMenuToggle.setAttribute('aria-expanded', !expanded);
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
          userMenuToggle.classList.remove('active');
          userDropdown.classList.remove('show');
          userMenuToggle.setAttribute('aria-expanded', false);
        }
      });
    }

    // Logout dengan custom modal
    if (logoutBtn && logoutModal && logoutForm) {
      // Tampilkan modal saat klik logout
      logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        logoutModal.classList.add('show');
      });

      // Konfirmasi logout
      confirmLogoutBtn.addEventListener('click', () => {
        logoutModal.classList.remove('show');
        logoutForm.submit();
      });

      // Batal logout
      cancelLogoutBtn.addEventListener('click', () => {
        logoutModal.classList.remove('show');
      });

      // Close modal saat klik di luar modal
      logoutModal.addEventListener('click', (e) => {
        if (e.target === logoutModal) {
          logoutModal.classList.remove('show');
        }
      });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
      if (hamburger && !hamburger.contains(e.target) && 
          !navbarMenu.contains(e.target) && 
          (!navBtns || !navBtns.contains(e.target)) &&
          (!userMenuContainer || !userMenuContainer.contains(e.target))) {
        hamburger.classList.remove('active');
        navbarMenu.classList.remove('show');
        if (navBtns) navBtns.classList.remove('show');
        if (userMenuContainer) userMenuContainer.classList.remove('show');
        hamburger.setAttribute('aria-expanded', false);
      }
    });
  });
</script>
