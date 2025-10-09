<?php
// navbar.php
?>

<!-- Load Google Fonts Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

<!-- Load Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

<!-- Load FontAwesome (optional, jika Anda ingin menggunakan ikon FontAwesome) -->
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

  <div class="nav-btns">
    <a href="#" id="open-signup" class="btn">Sign Up</a>
    <a href="#" id="open-login" class="btn">Login</a>
  </div>
</nav>

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

  /* Logo */
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

  /* Menu */
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

  /* Icon Animations */
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
    0% {
      transform: scale(1.07) rotate(-8deg) translateY(0);
    }

    28% {
      transform: scale(1.28) rotate(-13deg) translateY(-10px);
    }

    49% {
      transform: scale(1.24) rotate(-9deg) translateY(2px);
    }

    70% {
      transform: scale(1.2) rotate(-11deg) translateY(-3px);
    }

    100% {
      transform: scale(1.25) rotate(-13deg) translateY(-5px);
    }
  }

  /* Buttons SignUp/Login */
  .nav-btns .btn {
    padding: 9px 28px;
    border-radius: 22px;
    font-size: 1em;
    font-weight: 600;
    border: none;
    color: #fff;
    background: #b49666;
    text-decoration: none;
    margin-left: 7px;
    transition: background 0.18s, color 0.17s;
    display: inline-block;
    box-shadow: 0 2px 8px rgba(168, 100, 48, 0.06);
    outline: none;
  }

  #open-signup.btn:hover,
  #open-login.btn:hover,
  #open-signup.btn:active,
  #open-login.btn:active {
    background: #8b5e3c;
    color: #fff;
  }

  /* Hamburger */
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

  /* Responsive */
  @media (max-width: 900px) {
    .navbar {
      padding: 10px 15px;
    }

    .navbar-menu,
    .nav-btns {
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

    .navbar-menu.show,
    .nav-btns.show {
      display: flex;
    }

    .navbar-menu a,
    .nav-btns .btn {
      margin-left: 24px;
      width: auto;
      font-size: 1.09rem;
      padding: 12px 16px;
    }

    .hamburger {
      display: none;
    }
  }

  .modal-container,
  .signup-modal {
    background: rgba(34, 36, 58, 0.26);
    border-radius: 16px;
    box-shadow: 0 8px 22px #0006;
    max-width: 350px;
    min-width: 220px;
    width: 100%;
    padding: 0;
    overflow: hidden;
    backdrop-filter: blur(9px);
    -webkit-backdrop-filter: blur(9px);
    border: 2px solid rgba(255, 255, 255, 0.14);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  .signup-modal {
    max-width: 410px;
  }

  .modal-logo,
  .signup-modal .modal-logo {
    width: 44px;
    height: 44px;
    margin: 13px auto 6px auto;
    background: rgba(255, 255, 255, 0.13);
    border-radius: 50%;
    box-shadow: 0 1px 6px #e2c7fd33;
    object-fit: contain;
    display: block;
  }

  .modal-left,
  .signup-modal .modal-left {
    width: 100%;
    background: none;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
  }

  .modal-right,
  .signup-modal .modal-right {
    width: 100%;
    background: none;
    padding: 12px 14px 10px 14px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
  }

  .modal-container h2,
  .signup-modal h2 {
    font-size: 1.08rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #fff;
    text-align: center;
    letter-spacing: .45px;
  }

  .signup-modal .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 12px;
    margin-bottom: 10px;
  }

  .signup-modal .form-grid .field-full {
    grid-column: 1 / -1;
  }

  @media (max-width: 540px) {

    .signup-modal,
    .modal-container {
      max-width: 99vw;
    }

    .signup-modal .form-grid {
      grid-template-columns: 1fr;
      gap: 8px 0;
    }

    .signup-modal .form-grid .field-full {
      grid-column: auto;
    }
  }

  .modal-container .input-group input,
  .signup-modal .input-group input {
    background: rgba(255, 255, 255, 0.12) !important;
    border: 1.2px solid rgba(255, 255, 255, 0.15);
    border-radius: 7px;
    font-weight: 500;
    font-size: 13px;
    color: #fff !important;
    padding: 8px 10px;
    margin-bottom: 3px;
    min-width: 0;
  }

  .modal-container .input-group input:focus,
  .signup-modal .input-group input:focus {
    background: rgba(255, 255, 255, 0.19) !important;
    border-color: #b089f4;
    color: #fff !important;
  }

  ::placeholder {
    color: #d0d0d0;
    opacity: 0.93;
  }

  .modal-container .divider,
  .signup-modal .divider {
    margin: 9px 0 6px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    gap: 0.35em;
    text-align: center;
  }

  .modal-container .divider::before,
  .signup-modal .divider::before,
  .modal-container .divider::after,
  .signup-modal .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.22) 100%);
    border-radius: 1.3px;
  }

  .modal-container .divider span,
  .signup-modal .divider span {
    display: inline-block;
    background: rgba(34, 36, 58, 0.78);
    font-weight: 800;
    font-size: .98rem;
    color: #fff;
    text-align: center;
    padding: 3px 10px;
    border-radius: 7px;
    letter-spacing: .5px;
    box-shadow: 0 1px 3px #0001;
  }

  .modal-container .btn-google,
  .signup-modal .btn-google {
    background: rgba(255, 255, 255, 0.14);
    color: #fff !important;
    border-radius: 7px;
    border: 1.8px solid #e1e1e1;
    font-size: 13px;
    padding: 8px 10px;
    margin-bottom: 5px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-container .btn-google:hover,
  .signup-modal .btn-google:hover {
    background: #44337060;
    border-color: #b089f4;
  }

  .modal-container .btn-login,
  .signup-modal .btn-login {
    background: linear-gradient(90deg, #a97c50 65%, #b089f4 100%);
    color: #fff !important;
    width: 100%;
    border: 0;
    border-radius: 8px;
    padding: 9px 0;
    font-weight: 700;
    font-size: 13.5px;
    letter-spacing: .4px;
    margin-top: 8px;
    margin-bottom: 3px;
    transition: background .3s;
    box-shadow: 0 2px 9px #b089f433;
  }

  .modal-container .btn-login:hover,
  .signup-modal .btn-login:hover {
    background: linear-gradient(90deg, #b089f4 32%, #a97c50 100%);
  }

  /* Password toggle small */
  .modal-container .password-group,
  .signup-modal .password-group {
    position: relative;
  }

  .modal-container .password-group input,
  .signup-modal .password-group input {
    padding-right: 42px !important;
    box-sizing: border-box;
  }

  .modal-container .password-toggle,
  .signup-modal .password-toggle {
    position: absolute;
    top: 0;
    bottom: 0;
    right: 12px;
    /* JANGAN 8px, ini agar benar-benar dalam */
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 28px;
    background: none;
    border: none;
    color: #eee;
    cursor: pointer;
    z-index: 11;
    margin: 0;
    padding: 0;
    transition: color .18s;
  }

  .modal-container .password-toggle:hover,
  .signup-modal .password-toggle:hover {
    color: #b089f4;
  }

  .modal-container .eye-icon,
  .signup-modal .eye-icon {
    width: 18px;
    height: 18px;
    display: block;
    stroke: currentColor;
    pointer-events: none;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const navbarMenu = document.getElementById('navbarMenu');
    const navBtns = document.querySelector('.nav-btns');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navbarMenu.classList.toggle('show');
      navBtns.classList.toggle('show');

      // Update aria-expanded for accessibility
      const expanded = hamburger.getAttribute('aria-expanded') === 'true' || false;
      hamburger.setAttribute('aria-expanded', !expanded);
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !navbarMenu.contains(e.target) && !navBtns.contains(e.target)) {
        hamburger.classList.remove('active');
        navbarMenu.classList.remove('show');
        navBtns.classList.remove('show');
        hamburger.setAttribute('aria-expanded', false);
      }
    });
  });
</script>