<?php
// navbar.php
$isLoggedIn = isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
$userName = 'User';
$photoFileName = 'default.jpg';
$isCustomPhoto = false;
$initials = '';

$navbarPath = '';
$currentDir = dirname($_SERVER['PHP_SELF']);
if (strpos($currentDir, '/user') !== false || strpos($currentDir, '/admin') !== false) {
  $navbarPath = '../';
}

require_once $navbarPath . 'backend/koneksi.php';

if ($isLoggedIn) {
  $id_user = $_SESSION['id_user'];
  $stmt = $conn->prepare("SELECT username, foto_profil FROM users WHERE id_user=?");
  $stmt->bind_param("i", $id_user);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($result) {
    $userName = $result['username'];
    $photoFileName = $result['foto_profil'] ?? 'default.jpg';
    $initials = strtoupper(substr($userName, 0, 1));
  }
}

$escapedPhotoFileName = htmlspecialchars($photoFileName, ENT_QUOTES, 'UTF-8');
$projectDirName = '/majelismdpl.com';
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . $projectDirName;
$absoluteFilePath = $projectRoot . '/img/profile/' . $escapedPhotoFileName;
$isCustomPhoto = ($photoFileName !== 'default.jpg' && file_exists($absoluteFilePath));
$photoPathFinal = $navbarPath . 'img/profile/' . $escapedPhotoFileName;
$cacheBuster = '?' . time();
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

<nav class="navbar" role="navigation" aria-label="Main Navigation">
  <div class="navbar-logo">
    <img src="<?php echo $navbarPath; ?>img/majelis.png" alt="Logo Majelis MDPL" class="logo-img" />
  </div>

  <ul class="navbar-menu" id="navbarMenu" role="menu">
    <li><a href="<?php echo $navbarPath; ?>#home" role="menuitem"><i class="fa-solid fa-house"></i> Home</a></li>
    <li><a href="<?php echo $navbarPath; ?>#profile" role="menuitem"><i class="fa-solid fa-user"></i> Profile</a></li>
    <li><a href="<?php echo $navbarPath; ?>#paketTrips" role="menuitem"><i class="fa-solid fa-calendar-days"></i> Paket Trip</a></li>
    <li><a href="<?php echo $navbarPath; ?>#gallerys" role="menuitem"><i class="fa-solid fa-image"></i> Galeri</a></li>
    <li><a href="<?php echo $navbarPath; ?>#testimonials" role="menuitem"><i class="fa-solid fa-comment-dots"></i> Testimoni</a></li>
  </ul>

  <?php if (!$isLoggedIn): ?>
    <div class="nav-btns">
      <a href="#" id="open-signup" class="btn">Sign Up</a>
      <a href="#" id="open-login" class="btn">Login</a>
    </div>
  <?php else: ?>
    <div class="user-menu-container">
      <button class="user-menu-toggle" id="userMenuToggle" aria-label="User Menu" aria-expanded="false">
        <?php if ($isCustomPhoto): ?>
          <img src="<?php echo $photoPathFinal . $cacheBuster; ?>" alt="Foto Profil" class="profile-img-nav">
        <?php else: ?>
          <div class="profile-initials-nav">
            <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
        <span class="user-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
        <i class="fa-solid fa-chevron-down dropdown-icon"></i>
      </button>

      <div class="user-dropdown" id="userDropdown">
        <a href="<?php echo $navbarPath; ?>user/profile.php" class="dropdown-item">
          <i class="fa-solid fa-user"></i> Profil
        </a>
        <a href="<?php echo $navbarPath; ?>user/my-trips.php" class="dropdown-item">
          <i class="fa-solid fa-mountain"></i> Paket Trip Saya
        </a>
        <a href="<?php echo $navbarPath; ?>user/payment-status.php" class="dropdown-item">
          <i class="fa-solid fa-credit-card"></i> Status Pembayaran
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" id="logout-btn" class="dropdown-item logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>
  <?php endif; ?>

  <button class="hamburger" id="hamburgerBtn" aria-label="Toggle Menu" aria-expanded="false" aria-controls="navbarMenu">
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
  </button>
</nav>

<form id="logout-form" method="POST" action="<?php echo $navbarPath; ?>user/logout.php" style="display: none;">
  <input type="hidden" name="confirm_logout" value="1">
</form>

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
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  html {
    scroll-behavior: smooth;
  }

  body {
    font-family: "Poppins", Arial, sans-serif;
    background-color: #ffffff;
    line-height: 1.6;
    color: #333;
    overflow-x: hidden;
  }

  /* ========== NAVBAR ========== */
  .navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 80px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(50px) saturate(180%);
    -webkit-backdrop-filter: blur(50px) saturate(180%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 10px 40px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-bottom: 1px solid rgba(169, 124, 80, 0.15);
  }

  .navbar.scrolled {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(50px) saturate(180%);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
  }

  .navbar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1201;
  }

  .logo-img {
    height: 50px;
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
    filter: drop-shadow(0 2px 8px rgba(169, 124, 80, 0.2));
  }

  .logo-img:hover {
    transform: scale(1.08) rotate(-5deg);
  }

  .profile-initials-nav {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2px solid rgba(169, 124, 80, 0.4);
    box-shadow: 0 0 8px rgba(169, 124, 80, 0.15);
    transition: all 0.3s ease;
    background-color: #a97c50;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8em;
    font-weight: 700;
  }

  .user-menu-toggle:hover .profile-initials-nav {
    transform: scale(1.08);
    border-color: #ffd44a;
    box-shadow: 0 0 12px rgba(255, 212, 74, 0.35);
  }

  /* ========== MENU ITEMS ========== */
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
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 1.05em;
    transition: background 0.25s ease, color 0.25s ease;
    gap: 10px;
  }

  .navbar-menu a:hover {
    background: #a97c50;
    color: #fff;
  }

  .navbar-menu a.active {
    background: #8b5e3c;
    color: #fff;
  }

  .navbar-menu a i {
    font-size: 1.3em;
    color: #000000;
    transition: transform 0.25s cubic-bezier(0.54, 0.14, 0.23, 1.12), color 0.2s;
    display: inline-block;
  }

  /* ========== ICON ANIMATIONS ========== */
  .navbar-menu li:nth-child(1) a i {
    animation: jellyBounce 2.5s ease-in-out infinite;
  }

  @keyframes jellyBounce {
    0%, 100% { transform: translateY(0) scaleY(1); }
    30% { transform: translateY(-10px) scaleY(1.08); }
    40% { transform: translateY(-8px) scaleY(0.92); }
    50% { transform: translateY(0) scaleY(1.04); }
    60% { transform: translateY(0) scaleY(0.96); }
  }

  .navbar-menu li:nth-child(2) a i {
    animation: crazyWiggle 2s ease-in-out infinite;
    animation-delay: 0.4s;
  }

  @keyframes crazyWiggle {
    0%, 100% { transform: rotate(0deg) scale(1); }
    15% { transform: rotate(-18deg) scale(1.08); }
    30% { transform: rotate(18deg) scale(0.96); }
    45% { transform: rotate(-14deg) scale(1.04); }
    60% { transform: rotate(14deg) scale(0.98); }
    75% { transform: rotate(-8deg) scale(1.02); }
  }

  .navbar-menu li:nth-child(3) a i {
    animation: heartbeat 1.8s ease-in-out infinite;
    animation-delay: 0.6s;
  }

  @keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    10% { transform: scale(1.18); }
    20% { transform: scale(1); }
    30% { transform: scale(1.14); }
    40% { transform: scale(1); }
  }

  .navbar-menu li:nth-child(4) a i {
    animation: spinScale 3s ease-in-out infinite;
    animation-delay: 0.8s;
  }

  @keyframes spinScale {
    0%, 100% { transform: rotate(0deg) scale(1); }
    25% { transform: rotate(180deg) scale(1.18); }
    50% { transform: rotate(360deg) scale(1); }
    75% { transform: rotate(540deg) scale(1.12); }
  }

  .navbar-menu li:nth-child(5) a i {
    animation: waveFloat 2.2s ease-in-out infinite;
    animation-delay: 1s;
  }

  @keyframes waveFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    20% { transform: translateY(-7px) rotate(-10deg); }
    40% { transform: translateY(-3px) rotate(7deg); }
    60% { transform: translateY(-9px) rotate(-7deg); }
    80% { transform: translateY(-2px) rotate(9deg); }
  }

  .navbar-menu a:hover i {
    color: #ffffff;
    transform: scale(1.25) rotate(-13deg) translateY(-5px);
    animation: navbarBounce 0.44s cubic-bezier(0.39, 1.6, 0.63, 1) 1;
  }

  @keyframes navbarBounce {
    0% { transform: scale(1.07) rotate(-8deg) translateY(0); }
    28% { transform: scale(1.28) rotate(-13deg) translateY(-10px); }
    49% { transform: scale(1.24) rotate(-9deg) translateY(2px); }
    70% { transform: scale(1.2) rotate(-11deg) translateY(-3px); }
    100% { transform: scale(1.25) rotate(-13deg) translateY(-5px); }
  }

  .navbar-menu a.active i {
    color: #ffffff;
  }

  /* ========== BUTTONS ========== */
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
    transition: background 0.18s, color 0.17s, transform 0.2s;
    display: inline-block;
    box-shadow: 0 2px 8px rgba(168, 100, 48, 0.06);
    outline: none;
    cursor: pointer;
  }

  .nav-btns .btn:hover {
    background: #8b5e3c;
    color: #fff;
    transform: translateY(-2px);
  }

  /* ========== USER MENU ========== */
  .user-menu-container {
    position: relative;
    display: flex;
    align-items: center;
    z-index: 1100;
  }

  .user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0.2) 100%);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1.5px solid rgba(169, 124, 80, 0.25);
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(169, 124, 80, 0.1), inset 0 1px 2px rgba(255, 255, 255, 0.3);
    color: #333;
    font-weight: 600;
    font-size: 0.88em;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .user-menu-toggle::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -100%;
    width: 200%;
    height: 200%;
    background: linear-gradient(60deg, transparent 40%, rgba(255, 255, 255, 0.25) 50%, transparent 60%);
    animation: shimmerMove 3s ease-in-out infinite;
    pointer-events: none;
  }

  @keyframes shimmerMove {
    0% { left: -100%; }
    50% { left: 100%; }
    100% { left: 100%; }
  }

  .user-menu-toggle:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.45) 0%, rgba(255, 255, 255, 0.3) 100%);
    border-color: rgba(255, 212, 74, 0.4);
    box-shadow: 0 6px 18px rgba(169, 124, 80, 0.18), 0 0 15px rgba(255, 212, 74, 0.12), inset 0 1px 3px rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
  }

  .user-menu-toggle .dropdown-icon {
    font-size: 0.7em;
    transition: transform 0.3s ease;
    color: #a97c50;
    margin-left: 2px;
  }

  .user-menu-toggle.active .dropdown-icon {
    transform: rotate(180deg);
  }

  .user-name {
    max-width: 110px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    color: #5c3922;
  }

  .profile-img-nav {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(169, 124, 80, 0.4);
    box-shadow: 0 0 8px rgba(169, 124, 80, 0.15);
    transition: all 0.3s ease;
  }

  .user-menu-toggle:hover .profile-img-nav {
    transform: scale(1.08);
    border-color: #ffd44a;
    box-shadow: 0 0 12px rgba(255, 212, 74, 0.35);
  }

  /* ========== DROPDOWN ========== */
  .user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 230px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.94) 100%);
    backdrop-filter: blur(25px) saturate(180%);
    -webkit-backdrop-filter: blur(25px) saturate(180%);
    border-radius: 14px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 0 15px rgba(169, 124, 80, 0.08), inset 0 1px 2px rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(169, 124, 80, 0.18);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(0.96);
    transform-origin: top right;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1150;
    overflow: hidden;
  }

  .user-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
  }

  .dropdown-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 11px 16px;
    color: #333;
    text-decoration: none;
    font-size: 0.88em;
    font-weight: 500;
    transition: all 0.25s ease;
    border-bottom: 1px solid rgba(169, 124, 80, 0.05);
    position: relative;
  }

  .dropdown-item:last-child {
    border-bottom: none;
  }

  .dropdown-item::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(135deg, #ffd44a, #a97c50);
    transform: scaleY(0);
    transition: transform 0.25s ease;
  }

  .dropdown-item:hover::before {
    transform: scaleY(1);
  }

  .dropdown-item i {
    font-size: 1em;
    color: #a97c50;
    width: 17px;
    text-align: center;
    transition: all 0.25s ease;
  }

  .dropdown-item:hover {
    background: rgba(169, 124, 80, 0.07);
    padding-left: 20px;
    color: #5c3922;
  }

  .dropdown-item:hover i {
    transform: scale(1.12);
    color: #ffd44a;
  }

  .dropdown-item.logout {
    color: #d9534f;
    cursor: pointer;
  }

  .dropdown-item.logout i {
    color: #d9534f;
  }

  .dropdown-item.logout:hover {
    background: rgba(217, 83, 79, 0.07);
  }

  .dropdown-item.logout:hover i {
    color: #ff5459;
  }

  .dropdown-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(169, 124, 80, 0.12), transparent);
    margin: 5px 0;
  }

  /* ========== HAMBURGER ========== */
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
    background: #000000;
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

  /* ========== LOGOUT MODAL ========== */
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
    from { opacity: 0; }
    to { opacity: 1; }
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
  }

  .logout-modal-text {
    font-size: 1em;
    color: #666;
    margin-bottom: 30px;
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

  /* ========== RESPONSIVE - MOBILE ========== */
  @media (max-width: 900px) {
    .navbar {
      padding: 10px 15px;
      height: 70px;
    }

    .logo-img {
      height: 40px;
    }

    .navbar-logo {
      order: 1;
    }

    /* Auth buttons di tengah */
    .nav-btns {
      order: 2;
      margin-left: auto;
      margin-right: 10px;
      gap: 6px;
    }

    .nav-btns .btn {
      padding: 7px 16px;
      font-size: 0.85em;
      border-radius: 18px;
    }

    /* User menu di tengah (untuk logged in) */
    .user-menu-container {
      order: 2;
      margin-left: auto;
      margin-right: 10px;
    }

    .user-menu-toggle {
      padding: 5px 10px;
      gap: 5px;
      font-size: 0.78em;
    }

    .profile-img-nav,
    .profile-initials-nav {
      width: 22px;
      height: 22px;
      font-size: 0.75em;
    }

    .user-name {
      max-width: 70px;
      font-size: 0.85em;
    }

    /* Hamburger di paling kanan */
    .hamburger {
      display: flex !important;
      order: 3;
    }

    /* Navbar menu dropdown */
    .navbar-menu {
      display: none;
      flex-direction: column;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
      backdrop-filter: blur(25px) saturate(180%);
      -webkit-backdrop-filter: blur(25px) saturate(180%);
      position: absolute;
      top: 70px;
      left: 0;
      width: 100%;
      padding: 15px;
      gap: 8px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      z-index: 1050;
      border-top: 1px solid rgba(169, 124, 80, 0.2);
      max-height: calc(100vh - 70px);
      overflow-y: auto;
    }

    .navbar-menu.show {
      display: flex !important;
    }

    .navbar-menu li {
      width: 100%;
    }

    .navbar-menu a {
      width: 100%;
      font-size: 1rem;
      padding: 12px 18px;
      justify-content: flex-start;
      border-radius: 12px;
    }

    .user-dropdown {
      min-width: 200px;
    }

    .dropdown-item {
      padding: 10px 14px;
      font-size: 0.85em;
    }
  }

  @media (max-width: 600px) {
    .navbar {
      padding: 8px 12px;
      height: 65px;
    }

    .logo-img {
      height: 36px;
    }

    .navbar-menu {
      top: 65px;
    }

    .hamburger {
      width: 24px;
      height: 17px;
    }

    .nav-btns {
      margin-right: 8px;
    }

    .nav-btns .btn {
      padding: 6px 14px;
      font-size: 0.8em;
    }

    .user-menu-container {
      margin-right: 8px;
    }

    .user-menu-toggle {
      padding: 4px 8px;
      font-size: 0.72em;
    }

    .profile-img-nav,
    .profile-initials-nav {
      width: 20px;
      height: 20px;
    }

    .user-name {
      max-width: 60px;
    }
  }

  @media (max-width: 480px) {
    .nav-btns .btn {
      padding: 5px 12px;
      font-size: 0.75em;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const navbarMenu = document.getElementById('navbarMenu');
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');
    const logoutBtn = document.getElementById('logout-btn');
    const logoutForm = document.getElementById('logout-form');
    const logoutModal = document.getElementById('logout-modal');
    const confirmLogoutBtn = document.getElementById('confirm-logout-btn');
    const cancelLogoutBtn = document.getElementById('cancel-logout-btn');

    // Hamburger menu toggle
    if (hamburger) {
      hamburger.addEventListener('click', function(e) {
        e.stopPropagation();
        this.classList.toggle('active');
        
        if (navbarMenu) {
          navbarMenu.classList.toggle('show');
        }

        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !expanded);

        // Close user dropdown if open
        if (userDropdown && userDropdown.classList.contains('show')) {
          userDropdown.classList.remove('show');
          if (userMenuToggle) {
            userMenuToggle.classList.remove('active');
            userMenuToggle.setAttribute('aria-expanded', 'false');
          }
        }
      });
    }

    // User menu toggle
    if (userMenuToggle && userDropdown) {
      userMenuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
        this.classList.toggle('active');
        
        const isExpanded = userDropdown.classList.contains('show');
        this.setAttribute('aria-expanded', isExpanded);

        // Close hamburger menu on mobile
        if (window.innerWidth <= 900) {
          if (hamburger && hamburger.classList.contains('active')) {
            hamburger.classList.remove('active');
            if (navbarMenu) navbarMenu.classList.remove('show');
            hamburger.setAttribute('aria-expanded', 'false');
          }
        }
      });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      if (userDropdown && userMenuToggle) {
        if (!userDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
          userDropdown.classList.remove('show');
          userMenuToggle.classList.remove('active');
          userMenuToggle.setAttribute('aria-expanded', 'false');
        }
      }

      if (hamburger && navbarMenu) {
        if (!navbarMenu.contains(e.target) && !hamburger.contains(e.target)) {
          hamburger.classList.remove('active');
          navbarMenu.classList.remove('show');
          hamburger.setAttribute('aria-expanded', 'false');
        }
      }
    });

    // Logout modal
    if (logoutBtn && logoutModal && logoutForm) {
      logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        logoutModal.classList.add('show');
      });

      if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function() {
          logoutModal.classList.remove('show');
          logoutForm.submit();
        });
      }

      if (cancelLogoutBtn) {
        cancelLogoutBtn.addEventListener('click', function() {
          logoutModal.classList.remove('show');
        });
      }

      logoutModal.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
          logoutModal.classList.remove('show');
        }
      });
    }

    // Close menu when clicking menu links (mobile)
    const menuLinks = document.querySelectorAll('.navbar-menu a');
    menuLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 900) {
          if (hamburger) {
            hamburger.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
          }
          if (navbarMenu) navbarMenu.classList.remove('show');
        }
      });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 900) {
        if (hamburger) {
          hamburger.classList.remove('active');
          hamburger.setAttribute('aria-expanded', 'false');
        }
        if (navbarMenu) navbarMenu.classList.remove('show');
      }
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (navbar) {
        if (window.scrollY > 50) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
      }
    });
  });
</script>
