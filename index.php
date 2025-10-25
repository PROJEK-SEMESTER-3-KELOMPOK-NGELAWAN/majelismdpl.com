<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Majelis MDPL</title>

  <!-- CSS utama -->
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <style>
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



</head>

<body>
  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

  <!-- Hero -->
  <section class="hero-home" id="home">
    <div class="hero-bg-container">
      <img src="img/profil_foto.jpeg" alt="Gunung Bromo" class="hero-bg" id="hero-bg">
      <div class="hero-overlay"></div>
    </div>

    <div class="hero-content">
      <span class="hero-offer" id="hero-offer">BEST OFFERS</span>
      <h1 class="hero-title" id="hero-title">GUNUNG BROMO</h1>
      <div class="hero-days hero-days-wrapper">
        <span class="highlight" id="hero-days">1 HARI</span>
      </div>
      <p class="hero-desc" id="hero-desc">Rasakan keindahan golden sunrise Gunung Bromo yang menyegarkan</p>
      <a href="#paketTrips" class="hero-btn" style="text-decoration: none; ">DETAIL</a>

      <div class="hero-carousel" id="hero-carousel">
        <img src="img/herooo.jpg" alt="Gunung Bromo" class="carousel-item active" data-index="0">
        <img src="img/ijen.jpg" alt="Sunrise" class="carousel-item" data-index="1">
        <img src="img/rinjani.jpg" alt="Forest" class="carousel-item" data-index="2">
      </div>
    </div>

  </section>



  <!-- profile -->
  <section class="why-explorer style-4" id="profile">
    <!-- Animated Gradient Background - Brown Theme -->
    <div class="gradient-bg"></div>
    <div class="floating-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <!-- Header -->
    <div class="profile-header" data-aos="fade-down" data-aos-duration="600" data-aos-once="true">
      <h2 class="profile-title">
        <span class="gradient-text">Kenapa Pilih</span>
        <span class="gradient-text">Majelis Mdpl?</span>
      </h2>
      <p class="profile-subtitle">
        Pengalaman petualangan yang tak terlupakan dimulai dari sini
      </p>
    </div>

    <!-- Glass Cards Container -->
    <div class="glass-cards-wrapper">
      <!-- Left: Large Feature Card -->
      <div class="main-feature-card" data-aos="fade-right" data-aos-delay="200" data-aos-once="true">
        <div class="feature-badge">
          <i class="fas fa-award"></i>
          <span>Trusted Partner</span>
        </div>
        <h3>5000+ Happy Travelers</h3>
        <p>Bergabunglah dengan ribuan traveler yang telah merasakan pengalaman tak terlupakan bersama kami</p>

        <div class="feature-stats">
          <div class="stat-mini">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-info">
              <div class="stat-num">4.9</div>
              <div class="stat-text">Rating</div>
            </div>
          </div>
          <div class="stat-mini">
            <div class="stat-icon"><i class="fas fa-mountain"></i></div>
            <div class="stat-info">
              <div class="stat-num">50+</div>
              <div class="stat-text">Destinations</div>
            </div>
          </div>
        </div>

        <!-- Image Preview (Not Video) -->
        <div class="image-preview">
          <img src="img/profil_foto.jpeg" alt="Adventure">
        </div>
      </div>

      <!-- Right: Grid of Small Cards (No Learn More Button) -->
      <div class="features-grid">
        <div class="glass-card" data-aos="zoom-in" data-aos-delay="300" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-route"></i>
          </div>
          <h4>Flexible Routes</h4>
          <p>Pilih destinasi sesuai keinginan dengan berbagai pilihan rute</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="400" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4>Safety First</h4>
          <p>Keamanan prioritas dengan guide bersertifikat</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="500" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <h4>Easy Payment</h4>
          <p>Berbagai metode pembayaran modern dan aman</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="600" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-headset"></i>
          </div>
          <h4>24/7 Support</h4>
          <p>Tim support siap membantu kapan saja</p>
        </div>
      </div>
    </div>
  </section>



  <!-- Paket Trip Section -->
  <section class="paket-trip-section" id="paketTrips">
    <!-- Simple Pattern Background -->
    <div class="bg-pattern-simple"></div>

    <!-- Section Header -->
    <div class="section-header" data-aos="fade-up" data-aos-duration="600" data-aos-once="true">
      <h2 class="section-title">
        <span data-aos="fade-right" data-aos-delay="100" data-aos-once="true">Paket</span>
        <span data-aos="fade-left" data-aos-delay="200" data-aos-once="true">Trip</span>
        <span data-aos="fade-right" data-aos-delay="300" data-aos-once="true">Kami</span>
      </h2>
      <p class="section-subtitle" data-aos="fade-up" data-aos-delay="400" data-aos-once="true">
        Pilihan destinasi petualangan terbaik untuk liburan Anda
      </p>
    </div>

    <!-- Destination Carousel -->
    <div class="destination-carousel" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300" data-aos-once="true">
      <!-- Carousel Track (Horizontal Scroll) -->
      <div class="carousel-track">
        <!-- Loading spinner -->
        <div class="loading-container">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>

      <!-- Scroll Indicators -->
      <div class="scroll-indicators">
        <button class="scroll-indicator prev" aria-label="Scroll Left">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="scroll-indicator next" aria-label="Scroll Right">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Mountain Wave Divider -->
    <div class="mountain-wave-divider">
      <svg viewBox="0 0 1440 200" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <defs>
          <linearGradient id="mountainGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#8b5e3c;stop-opacity:0.6" />
            <stop offset="100%" style="stop-color:#5c3922;stop-opacity:0.8" />
          </linearGradient>
        </defs>
        <path fill="url(#mountainGradient)" d="M0,150 L60,130 L120,145 L180,120 L240,135 L300,110 L360,125 L420,100 L480,115 L540,95 L600,110 L660,90 L720,105 L780,85 L840,100 L900,80 L960,95 L1020,75 L1080,90 L1140,70 L1200,85 L1260,65 L1320,80 L1380,60 L1440,75 L1440,200 L0,200 Z"></path>
        <path fill="#4b3625" opacity="0.5" d="M0,165 L80,155 L160,165 L240,150 L320,160 L400,145 L480,155 L560,140 L640,150 L720,135 L800,145 L880,130 L960,140 L1040,125 L1120,135 L1200,120 L1280,130 L1360,115 L1440,125 L1440,200 L0,200 Z"></path>
      </svg>
    </div>
  </section>



  <!-- Gallery Section -->
  <section class="gallery-cardstyle" id="gallerys">
    <!-- Particles.js Background (reduced) -->
    <div id="particles-js"></div>

    <!-- Gallery Header -->
    <div class="gallery-header">
      <h2 class="gallery-title">
        <span>Galeri</span>
        <span>Foto</span>
        <span>Kami</span>
      </h2>
      <p class="gallery-subtitle">
        Dokumentasi perjalanan petualangan yang tak terlupakan
      </p>
    </div>

    <!-- Card Grid -->
    <div class="card-grid">
      <div class="card">
        <div class="card-overlay"></div>
        <img src="img/gambar3.jpg" alt="Gunung Bromo" />
        <div class="caption">
          <i class="fas fa-mountain"></i>
          Gunung Bromo Sunrise
        </div>
      </div>

      <div class="card">
        <div class="card-overlay"></div>
        <img src="img/gambar2.jpg" alt="Camping" />
        <div class="caption">
          <i class="fas fa-campground"></i>
          Camping Savana
        </div>
      </div>

      <div class="card">
        <div class="card-overlay"></div>
        <img src="img/gambar1.jpg" alt="Trip Bersama" />
        <div class="caption">
          <i class="fas fa-users"></i>
          Trip Seru Bersama
        </div>
      </div>
    </div>
  </section>




  <!-- Testimonials -->
  <section id="testimonials" class="testimonials">
    <!-- Modern Background Pattern -->
    <div class="testimonial-bg-pattern"></div>

    <div class="container">
      <div class="testimonial-header" data-aos="fade-up" data-aos-once="true">
        <h2><span class="title-large">Apa Kata Mereka?</span></h2>
        <p class="testimonial-subtitle">Pengalaman nyata dari para traveler kami</p>
      </div>

      <!-- Row 1: Moving LEFT -->
      <div class="testimonial-row row-left">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Trip ke Rinjani sangat terorganisir. Guide-nya asik dan perhatian. Saya yang pemula merasa aman banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Rudi"></div>
              <div class="author-details">
                <h4>Rudi Saputra</h4>
                <p>Jakarta</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Sunrise di Bromo, camping di savana, semua sempurna. Majelis MDPL benar-benar profesional!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Andi"></div>
              <div class="author-details">
                <h4>Andi Pratama</h4>
                <p>Surabaya</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Fasilitas lengkap, harga terjangkau, pelayanan memuaskan. Recommended banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Sarah"></div>
              <div class="author-details">
                <h4>Sarah Wijaya</h4>
                <p>Yogyakarta</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 2: Moving RIGHT -->
      <div class="testimonial-row row-right">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Baru pertama kali ikut open trip, tapi langsung jatuh cinta. Banyak teman baru dan pengalaman tak terlupakan.</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dewi"></div>
              <div class="author-details">
                <h4>Dewi Lestari</h4>
                <p>Bandung</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Tim guide sangat berpengalaman dan ramah. Perjalanan jadi lebih seru dan aman. Pasti bakal ikut lagi!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/85.jpg" alt="Budi"></div>
              <div class="author-details">
                <h4>Budi Hermawan</h4>
                <p>Semarang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Dokumentasi keren, momen tak terlupakan. Customer service responsif banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/90.jpg" alt="Nina"></div>
              <div class="author-details">
                <h4>Nina Agustina</h4>
                <p>Malang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 3: Moving LEFT -->
      <div class="testimonial-row row-left">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Pengalaman hiking terbaik! View spektakuler, persiapan matang, semua worth it banget.</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Doni"></div>
              <div class="author-details">
                <h4>Doni Prasetyo</h4>
                <p>Bekasi</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Pelayanan ramah, harga bersahabat, destinasi keren. Majelis MDPL is the best!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/55.jpg" alt="Rina"></div>
              <div class="author-details">
                <h4>Rina Sari</h4>
                <p>Tangerang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Open trip paling seru yang pernah saya ikuti. Semua terorganisir dengan baik!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Eko"></div>
              <div class="author-details">
                <h4>Eko Prabowo</h4>
                <p>Solo</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <?php include 'footer.php'; ?>



  <!-- POPUP LOGIN -->
  <div id="loginModal" class="modal">
    <div class="modal-container">
      <a href="#" class="close-btn" id="close-login">&times;</a>

      <!-- Left section dengan logo -->
      <div class="modal-left">
        <div class="logo-container">
          <img src="assets/logo_majelis_noBg.png" alt="Majelis MDPL Logo" class="modal-logo">
        </div>
      </div>

      <!-- Right section dengan form -->
      <div class="modal-right">
        <h2>Login</h2>

        <form action="login.php" method="POST">
          <div class="input-group">
            <input type="text" name="username" placeholder="Username" required />
          </div>

          <!-- Input Password dengan toggle show/hide -->
          <div class="input-group password-group">
            <input type="password" name="password" id="loginPassword" placeholder="Password" required />
            <button type="button" class="password-toggle" id="toggleLoginPassword">
              <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
            </button>
          </div>

          <button type="submit" class="btn-login">Masuk</button>
          <div style="text-align:center;margin-top:13px;">
            <a href="user/lupa-password.php" style="color: #a97c50; text-decoration: underline; font-size:14px;">
              Lupa password?
            </a>
          </div>

          <!-- Tombol Login dengan Google -->
          <div class="divider">
            <span>atau</span>
          </div>

          <div class="google-login-section">
            <button type="button" id="googleLoginBtn" class="btn-google">
              <img src="assets/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
              Login with Google
            </button>
          </div>


        </form>
      </div>
    </div>
  </div>




  <!-- POPUP SIGN UP -->
  <div id="signUpModal" class="modal">
    <div class="modal-container signup-modal">
      <a href="#" class="close-btn" id="close-signup">&times;</a>

      <!-- Left section dengan logo -->
      <div class="modal-left">
        <div class="logo-container">
          <img src="assets/logo_majelis_noBg.png" alt="Majelis MDPL Logo" class="modal-logo">
        </div>
      </div>

      <!-- Right section dengan form -->
      <div class="modal-right">
        <h2>Sign Up</h2>

        <form method="POST" novalidate id="signupForm">
          <div class="form-grid">
            <!-- Row 1 -->
            <div class="input-group">
              <input type="text" name="username" placeholder="Username" autocomplete="username" required minlength="3" />
              <small class="error-message" style="color: #e74c3c; font-size: 12px; display: none;">Username minimal 3 karakter</small>
            </div>
            <div class="input-group password-group">
              <input type="password" name="password" id="signupPassword" placeholder="Password" autocomplete="new-password" required minlength="6" />
              <button type="button" class="password-toggle" id="toggleSignupPassword">
                <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
              <small class="error-message" style="color: #e74c3c; font-size: 12px; display: none;">Password minimal 6 karakter</small>
            </div>

            <!-- Row 2 -->
            <div class="input-group password-group">
              <input type="password" name="confirm_password" id="confirmPassword" placeholder="Konfirmasi Password" autocomplete="new-password" required />
              <button type="button" class="password-toggle" id="toggleConfirmPassword">
                <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
              <small class="error-message" style="color: #e74c3c; font-size: 12px; display: none;">Password tidak cocok</small>
            </div>
            <div class="input-group">
              <input type="email" name="email" placeholder="Email" autocomplete="email" required />
              <small class="error-message" style="color: #e74c3c; font-size: 12px; display: none;">Format email tidak valid</small>
            </div>

            <!-- Row 3 -->
            <div class="input-group">
              <input type="tel" name="no_wa" placeholder="No HP (contoh: 081234567890)" inputmode="tel" autocomplete="tel" required />
              <small class="error-message" style="color: #e74c3c; font-size: 12px; display: none;">Format nomor HP tidak valid</small>
            </div>
            <div class="input-group">
              <input type="text" name="alamat" placeholder="Alamat" autocomplete="street-address" required />
            </div>
          </div>

          <button type="submit" class="btn-login">Daftar</button>

          <!-- Tombol Sign Up dengan Google -->
          <div class="divider">
            <span>atau</span>
          </div>
          <div class="google-signup-section">
            <button type="button" id="googleSignUpBtn" class="btn-google">
              <img src="assets/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
              Sign up with Google
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>





  <!-- Tombol WhatsApp -->
  <div class="whatsapp-container" data-aos="zoom-in" data-aos-delay="500">
    <button class="whatsapp-button" id="whatsappBtn" onclick="bukaWhatsapp()">
      <div class="whatsapp-icon-wrapper">
        <i class="fab fa-whatsapp"></i>
        <span class="ping-dot"></span>
      </div>
      <span class="whatsapp-text">Chat WhatsApp</span>
    </button>
    <div class="whatsapp-tooltip">Ada yang bisa kami bantu? 💬</div>
  </div>



  <!-- AOS Library Script -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <!-- Load SweetAlert first -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Load Particles.js -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

  <!-- Global functions that might be needed -->
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      mirror: false,
      offset: 100
    });


    // Buka WhatsApp helper
    // ========== WHATSAPP BUTTON - CUTE & RESPONSIVE ==========
    (function() {
      const whatsappBtn = document.getElementById('whatsappBtn');

      if (whatsappBtn) {
        let expandTimeout;
        let isExpanded = false;

        // Handle click on WhatsApp button
        whatsappBtn.addEventListener('click', function(e) {
          // Mobile: First tap expands, second tap opens WhatsApp
          if (window.innerWidth <= 768) {
            if (!isExpanded) {
              // First tap - expand button
              e.preventDefault();
              e.stopPropagation();

              this.classList.add('expanded');
              isExpanded = true;

              // Auto-collapse after 3 seconds
              clearTimeout(expandTimeout);
              expandTimeout = setTimeout(() => {
                whatsappBtn.classList.remove('expanded');
                isExpanded = false;
              }, 3000);
            } else {
              // Second tap - open WhatsApp (bukaWhatsapp() will be called)
              clearTimeout(expandTimeout);
            }
          }
          // Desktop: Direct open WhatsApp
        });

        // Handle window resize
        window.addEventListener('resize', function() {
          if (window.innerWidth > 768) {
            whatsappBtn.classList.remove('expanded');
            isExpanded = false;
            clearTimeout(expandTimeout);
          }
        });

        // Close expanded state when clicking outside
        document.addEventListener('click', function(e) {
          if (window.innerWidth <= 768 && isExpanded) {
            if (!whatsappBtn.contains(e.target)) {
              whatsappBtn.classList.remove('expanded');
              isExpanded = false;
              clearTimeout(expandTimeout);
            }
          }
        });
      }
    })();

    // ========== BUKA WHATSAPP FUNCTION ==========
    function bukaWhatsapp() {
      const nomor = "6285233463360"; // Nomor WhatsApp Anda
      const pesan = encodeURIComponent("Halo! Saya ingin bertanya tentang paket trip Majelis MDPL.");
      const url = `https://wa.me/${nomor}?text=${pesan}`;

      // Open WhatsApp in new tab
      window.open(url, "_blank");

      // Add click feedback animation
      const whatsappBtn = document.getElementById('whatsappBtn');
      if (whatsappBtn) {
        whatsappBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
          whatsappBtn.style.transform = '';
        }, 150);
      }
    }

    // ========== GOOGLE OAUTH & LOGIN ==========
    function handleGoogleOAuth() {
      const baseURL = window.location.origin;
      window.location.href = `${baseURL}/majelismdpl.com/backend/google-oauth.php`;
    }

    function handleGoogleLogin() {
      const baseURL = window.location.origin;
      window.location.href = `${baseURL}/majelismdpl.com/backend/google-oauth.php?type=login`;
    }

    // ========== MODAL ANIMATION HELPERS ==========
    const OPEN = "open";
    const CLOSING = "closing";
    const DURATION = 300;

    function openModal(el) {
      if (!el) return;

      el.classList.remove(CLOSING);
      el.style.display = "flex";

      // Force reflow for smooth animation
      void el.offsetWidth;

      el.classList.add(OPEN);

      // Prevent body scroll when modal is open
      document.body.style.overflow = "hidden";
    }

    function closeModal(el) {
      if (!el) return;

      el.classList.remove(OPEN);
      el.classList.add(CLOSING);

      setTimeout(() => {
        el.classList.remove(CLOSING);
        el.style.display = "none";

        // Restore body scroll
        document.body.style.overflow = "";
      }, DURATION);
    }

    // ========== CLOSE MODAL ON ESCAPE KEY ==========
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.open');
        openModals.forEach(modal => closeModal(modal));
      }
    });

    // ========== CLOSE MODAL ON OUTSIDE CLICK ==========
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('modal')) {
        closeModal(e.target);
      }
    });



    // --------- When DOM loaded ----------
    document.addEventListener('DOMContentLoaded', function() {
      // ======= LOGIN MODAL
      const loginModal = document.getElementById("loginModal");
      const openLogin = document.getElementById("open-login");
      const closeLogin = document.getElementById("close-login");
      if (openLogin && loginModal && closeLogin) {
        openLogin.addEventListener("click", (e) => {
          e.preventDefault();
          openModal(loginModal);

          setTimeout(() => {
            const googleLoginBtn = document.getElementById("googleLoginBtn");
            if (googleLoginBtn && !googleLoginBtn.hasAttribute('data-main-login-listener')) {
              googleLoginBtn.setAttribute('data-main-login-listener', 'true');
              googleLoginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleGoogleLogin();
              });
            }
          }, 100);
        });

        closeLogin.addEventListener("click", (e) => {
          e.preventDefault();
          closeModal(loginModal);
        });

        loginModal.addEventListener("click", (e) => {
          if (e.target === loginModal) closeModal(loginModal);
        });
      }

      // ======= SIGNUP MODAL
      const signUpModal = document.getElementById("signUpModal");
      const openSignUp = document.getElementById("open-signup");
      const closeSignUp = document.getElementById("close-signup");
      if (openSignUp && signUpModal && closeSignUp) {
        openSignUp.addEventListener("click", (e) => {
          e.preventDefault();
          openModal(signUpModal);

          setTimeout(() => {
            const googleSignupBtn = document.getElementById("googleSignUpBtn");
            if (googleSignupBtn && !googleSignupBtn.hasAttribute('data-main-signup-listener')) {
              googleSignupBtn.setAttribute('data-main-signup-listener', 'true');
              googleSignupBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleGoogleOAuth();
              });
            }
          }, 100);
        });

        closeSignUp.addEventListener("click", (e) => {
          e.preventDefault();
          closeModal(signUpModal);
        });

        signUpModal.addEventListener("click", (e) => {
          if (e.target === signUpModal) closeModal(signUpModal);
        });
      }

      // ESC untuk menutup
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          if (signUpModal && signUpModal.style.display === "flex") closeModal(signUpModal);
          if (loginModal && loginModal.style.display === "flex") closeModal(loginModal);
        }
      });

      // --- TOGGLE PASSWORD LOGIN ---
      const toggleButton = document.getElementById('toggleLoginPassword');
      const passwordInput = document.getElementById('loginPassword');
      if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', function() {
          const showIcon = toggleButton.querySelector('.eye-icon.show');
          const hideIcon = toggleButton.querySelector('.eye-icon.hide');
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'block';
          } else {
            passwordInput.type = 'password';
            showIcon.style.display = 'block';
            hideIcon.style.display = 'none';
          }
        });
      }

      // --- TOGGLE PASSWORD SIGNUP ---
      const toggleSignupPassword = document.getElementById('toggleSignupPassword');
      const signupPasswordInput = document.getElementById('signupPassword');
      if (toggleSignupPassword && signupPasswordInput) {
        toggleSignupPassword.addEventListener('click', function() {
          togglePasswordVisibility(signupPasswordInput, toggleSignupPassword);
        });
      }

      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
      const confirmPasswordInput = document.getElementById('confirmPassword');
      if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
          togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
        });
      }

      // SUBMIT handler for signup form: only alert, no inline indicator
      const signupForm = document.getElementById('signupForm');
      if (signupForm && signupPasswordInput && confirmPasswordInput) {
        signupForm.addEventListener('submit', function(e) {
          const password = signupPasswordInput.value;
          const confirm = confirmPasswordInput.value;
          if (password !== confirm) {
            e.preventDefault();
            showCustomAlert(
              'Password Tidak Sama!',
              'Password dan Konfirmasi Password harus sama. Silakan periksa kembali input Anda.',
              'error'
            );
            return false;
          }
          showCustomAlert(
            'Berhasil!',
            'Password sudah sama. Form siap untuk disubmit.',
            'success'
          );
          // Lanjutkan dengan logic submit form atau AJAX di sini jika ingin.
        });
      }
    });

    // Utility for password show/hide
    function togglePasswordVisibility(passwordInput, toggleButton) {
      const showIcon = toggleButton.querySelector('.eye-icon.show');
      const hideIcon = toggleButton.querySelector('.eye-icon.hide');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        showIcon.style.display = 'none';
        hideIcon.style.display = 'block';
      } else {
        passwordInput.type = 'password';
        showIcon.style.display = 'block';
        hideIcon.style.display = 'none';
      }
    }

    // Custom alert popup
    function showCustomAlert(title, message, type) {
      const alertHTML = `
    <div class="custom-alert show" id="customAlert">
      <div class="alert-content">
        <div class="alert-icon ${type}">${type === 'error' ? '⚠' : '✅'}</div>
        <div class="alert-title">${title}</div>
        <div class="alert-message">${message}</div>
        <button class="alert-button" onclick="closeCustomAlert()">OK</button>
      </div>
    </div>
  `;
      const existingAlert = document.getElementById('customAlert');
      if (existingAlert) existingAlert.remove();
      document.body.insertAdjacentHTML('beforeend', alertHTML);
    }

    function closeCustomAlert() {
      const alert = document.getElementById('customAlert');
      if (alert) {
        alert.classList.remove('show');
        setTimeout(() => {
          alert.remove();
        }, 300);
      }
    }


    fetch('backend/galeri-api.php?action=get')
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          const galleryContainer = document.querySelector('.card-grid');
          galleryContainer.innerHTML = ''; // kosongkan dulu
          result.data.forEach(item => {
            const card = document.createElement('div');
            card.classList.add('card');
            card.innerHTML = `
          <img src="img/${item.gallery}" alt="Foto" />
          
        `;
            galleryContainer.appendChild(card);
          });
        } else {
          console.error(result.message);
        }
      })
      .catch(err => console.error(err));



    // UNTUK SECTION HERO HOME

    document.addEventListener('DOMContentLoaded', () => {
      const heroSlides = [{
          image: "img/herooo.jpg",
          title: "GUNUNG BROMO",
          offer: "BEST OFFERS",
          days: "1 HARI",
          desc: "Rasakan keindahan golden sunrise Gunung Bromo yang menyegarkan"
        },
        {
          image: "img/ijen.jpg",
          title: "GUNUNG IJEN",
          offer: "IJEN ADVENTURE",
          days: "2 HARI",
          desc: "Nikmati mendaki gunung berapi biru di Jawa Timur."
        },
        {
          image: "img/rinjani.jpg",
          title: "GUNUNG RINJANI",
          offer: "RINJANI EXPEDITION",
          days: "3 HARI",
          desc: "Rasakan petualangan mendaki puncak tertinggi di Nusa Tenggara Barat."
        }
      ];

      const heroBg = document.getElementById('hero-bg');
      const carouselItems = document.querySelectorAll('.carousel-item');
      const titleElem = document.getElementById('hero-title');
      const offerElem = document.getElementById('hero-offer');
      const daysElem = document.getElementById('hero-days');
      const descElem = document.getElementById('hero-desc');

      // Validasi elemen ada atau tidak
      if (!heroBg || !titleElem || !offerElem || !daysElem || !descElem) {
        console.error('Hero elements not found!');
        return;
      }

      const daysWrapper = daysElem.closest('.hero-days-wrapper') || daysElem.parentElement;

      let currentIndex = 0;
      let slideInterval;
      let isAnimating = false;
      const total = heroSlides.length;

      function setActiveIndex(index) {
        if (isAnimating) return;
        isAnimating = true;

        // Update active carousel item
        carouselItems.forEach((item, i) => {
          item.classList.toggle('active', i === index);
        });

        const textElements = [offerElem, titleElem, daysWrapper, descElem];

        // FASE 1: Fade out semua teks dulu (lebih cepat)
        textElements.forEach(elem => {
          if (elem) {
            elem.classList.remove('fade-in');
            elem.classList.add('fade-out');
          }
        });

        // FASE 2: Setelah 300ms, fade out background
        setTimeout(() => {
          heroBg.classList.remove('fade-in');
          heroBg.classList.add('fade-out');
        }, 300);

        // FASE 3: Setelah background fade out (900ms total), ganti gambar
        setTimeout(() => {
          heroBg.src = heroSlides[index].image;

          // Fade in background
          heroBg.classList.remove('fade-out');
          void heroBg.offsetWidth; // Force reflow
          heroBg.classList.add('fade-in');

        }, 900);

        // FASE 4: Tunggu background muncul penuh, baru ganti & munculkan teks
        setTimeout(() => {
          // Ganti konten teks
          titleElem.textContent = heroSlides[index].title;
          offerElem.textContent = heroSlides[index].offer;
          daysElem.textContent = heroSlides[index].days;
          descElem.textContent = heroSlides[index].desc;

          // Remove fade-out dari semua teks
          textElements.forEach(elem => {
            if (elem) elem.classList.remove('fade-out');
          });

          // Force reflow
          void offerElem.offsetWidth;

          // Fade in teks dengan stagger (muncul satu per satu)
          setTimeout(() => offerElem.classList.add('fade-in'), 100);
          setTimeout(() => titleElem.classList.add('fade-in'), 250);
          setTimeout(() => {
            if (daysWrapper) daysWrapper.classList.add('fade-in');
          }, 400);
          setTimeout(() => descElem.classList.add('fade-in'), 550);

          currentIndex = index;

          // Reset animation flag setelah semua selesai
          setTimeout(() => {
            isAnimating = false;
          }, 1000);

        }, 1400); // Tunggu background fade in selesai (900 + 500)
      }

      function startSlideShow() {
        slideInterval = setInterval(() => {
          let nextIndex = (currentIndex + 1) % total;
          setActiveIndex(nextIndex);
        }, 6000); // 6 detik per slide (lebih lama untuk nikmati animasi)
      }

      // Click handler untuk carousel items
      carouselItems.forEach(item => {
        item.addEventListener('click', () => {
          if (isAnimating) return;
          clearInterval(slideInterval);
          setActiveIndex(parseInt(item.dataset.index));
          setTimeout(startSlideShow, 2000);
        });
      });

      // Initialize
      setActiveIndex(0);
      startSlideShow();
    });


    // UNTUK SECTION GALERI
    // UNTUK SECTION PARTICLES GALERI
    // Optimized Particles.js - LIGHTWEIGHT
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
          particles: {
            number: {
              value: 40, // Reduced dari 50
              density: {
                enable: true,
                value_area: 1000
              }
            },
            color: {
              value: ['#ffffff', '#ffd44a']
            },
            shape: {
              type: 'circle' // Simple shape only
            },
            opacity: {
              value: 0.3,
              random: true,
              anim: {
                enable: true,
                speed: 0.5,
                opacity_min: 0.1,
                sync: false
              }
            },
            size: {
              value: 3,
              random: true,
              anim: {
                enable: false // Disable animation
              }
            },
            line_linked: {
              enable: true,
              distance: 150,
              color: '#ffd44a',
              opacity: 0.15,
              width: 1
            },
            move: {
              enable: true,
              speed: 1, // Slower
              direction: 'none',
              random: false,
              straight: false,
              out_mode: 'out',
              bounce: false
            }
          },
          interactivity: {
            detect_on: 'canvas',
            events: {
              onhover: {
                enable: false // Disable untuk performance
              },
              onclick: {
                enable: false // Disable untuk performance
              },
              resize: true
            }
          },
          retina_detect: true
        });
      }
    });



    // UNTUK SECTION PROFIL
    // Counter Animation
    document.addEventListener("DOMContentLoaded", function() {
      const counters = document.querySelectorAll('.stat-number');
      const speed = 200;

      const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const inc = target / speed;

        if (count < target) {
          counter.innerText = Math.ceil(count + inc);
          setTimeout(() => animateCounter(counter), 1);
        } else {
          counter.innerText = target;
        }
      };

      // Intersection Observer untuk trigger animasi
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            counters.forEach(counter => animateCounter(counter));
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.5
      });

      const statsContainer = document.querySelector('.stats-container');
      if (statsContainer) {
        observer.observe(statsContainer);
      }
    });


    // UNTUK SECTION TESTIMONIAL
    // Testimonial 3-Row Infinite Marquee
    document.addEventListener('DOMContentLoaded', function() {
      const rows = document.querySelectorAll('.testimonial-row');

      rows.forEach(row => {
        const track = row.querySelector('.testimonial-track');
        const cards = Array.from(track.children);

        // Duplicate cards 2 times for seamless loop
        cards.forEach(card => {
          const clone1 = card.cloneNode(true);
          const clone2 = card.cloneNode(true);
          track.appendChild(clone1);
          track.appendChild(clone2);
        });
      });
    });
  </script>

  <!-- Load external JavaScript files -->
  <script src="frontend/registrasi.js"></script>
  <script src="frontend/login.js"></script>
  <script src="frontend/trip-user.js"></script>

</body>

</html>