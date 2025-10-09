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

    .gallery-cardstyle {
      max-width: 1100px;
      margin: 60px auto;
      padding: 0 20px;
      color: #222;
      text-align: center;
    }

    .gallery-cardstyle h2 {
      font-weight: 700;
      font-size: 2.4rem;
      margin-bottom: 24px;
      color: #8b5e2e;
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 28px;
    }

    .card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 18px rgba(180, 140, 65, 0.15);
      overflow: hidden;
      cursor: pointer;
      transition: box-shadow 0.35s ease, transform 0.3s ease;
    }

    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      display: block;
      border-bottom: 1px solid #ddd;
    }

    .caption {
      padding: 8px 12px;
      font-weight: 600;
      color: #6e5a2b;
      font-size: 1.1rem;
      text-align: center;
      background-color: #fff8ec;
    }

    .card:hover {
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
      transform: translateY(-8px);
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-logo">
      <!-- Ganti src logo sesuai file logo kamu, contoh PNG transparan: -->
      <img src="img/majelis.png" alt="Logo Majelis MDPL" class="logo-img" />
    </div>
    <ul class="navbar-menu">
      <li><a href="#" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
      <li><a href="#"><i class="fa-solid fa-user"></i> Profile</a></li>
      <li><a href="#"><i class="fa-solid fa-calendar-days"></i> Jadwal Pendakian</a></li>
      <li><a href="#"><i class="fa-solid fa-image"></i> Galeri</a></li>
      <li><a href="#"><i class="fa-solid fa-comment-dots"></i> Testimoni</a></li>
    </ul>
    </div>
    <div class="nav-btns">
      <a href="#" id="open-signup" class="btn">Sign Up</a>
      <a href="#" id="open-login" class="btn">Login</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero-home">
    <img src="img/Herooo.jpg" alt="Gunung Bromo" class="hero-bg">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <span class="hero-offer">BEST OFFERS</span>
      <h1 class="hero-title">GUNUNG BROMO</h1>
      <div class="hero-days"><span class="highlight">1 HARI</span></div>
      <p class="hero-desc">Rasakan keindahan golden sunrise Gunung Bromo yang menyegarkan</p>
      <button class="hero-btn">DETAIL</button>
      <div class="hero-carousel">
        <img src="img/gambar1.jpg" alt="Jellyfish" class="carousel-item">
        <img src="img/gambar2.jpg" alt="Sunrise" class="carousel-item">
        <img src="img/gambar3.jpg" alt="Forest" class="carousel-item">
      </div>
    </div>
  </section>

  <!-- profile -->
  <section class="why-explorer">
    <div class="gallery-collage">
      <img src="img/gambar1.jpg" alt="Trip Foto 1" class="item item1">
      <img src="img/gambar2.jpg" alt="Trip Foto 2" class="item item2">
      <img src="img/gambar3.jpg" alt="Trip Foto 3" class="item item3">
      <img src="img/gambar1.jpg" alt="Trip Foto 4" class="item item4">
      <img src="img/gambar2.jpg" alt="Trip Foto 5" class="item item5">
    </div>
    <div class="content">
      <h1>Kenapa Pilih Majelis Mdpl?</h1>
      <div class="feature">
        <i class="fas fa-map-marked-alt icon"></i>
        <div>
          <h3>Banyak Pilihan Destinasi</h3>
          <p>Mau liburan ke Bandung, Lembang, Yogyakarta, Semarang, Surabaya, Gunung ataupun Laut semuanya ada di
            Explorer.ID.</p>
        </div>
      </div>
      <div class="feature">
        <i class="fas fa-credit-card icon"></i>
        <div>
          <h3>Banyak Metode Pembayaran</h3>
          <p>Gak usah pusing, Majelis Mdpl banyak metode pembayaran kekinian yang bakal bikin kamu lebih nyaman.</p>
        </div>
      </div>
      <div class="feature">
        <i class="fas fa-lock icon"></i>
        <div>
          <h3>Transaksi Aman</h3>
          <p>Keamanan dan privasi transaksi online Anda menjadi prioritas kami.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Profil -->
  <section class="profil-section" id="profil">
    <div class="profil-header" aria-label="Judul Bagian">
      <h1>MAJELIS MDPL</h1>
    </div>

    <div class="profil-container">
      <div class="profil-text">
        <h2>Tentang Kami</h2>
        <p>Kami adalah komunitas petualang yang menyediakan pengalaman open trip seru dan aman ke berbagai destinasi alam terbaik. Bergabunglah dan rasakan petualangan yang sesungguhnya bersama kami!</p>
        <p class="logo-description">Logo <strong>Majelis MDPL</strong> merepresentasikan semangat petualangan dan kecintaan terhadap alam. Visual utama berupa pegunungan, matahari, deretan pohon, dan tenda di tengahnya menggambarkan kegiatan eksplorasi, pendakian, dan camping yang menjadi identitas komunitas ini.</p>
        <p class="logo-description">Warna maroon yang dominan melambangkan keberanian dan kekompakan, sementara warna kuning emas pada tulisan “MAJELIS” memberi kesan hangat dan bersahabat. Elemen “MDPL” menguatkan identitas komunitas pecinta alam dengan merujuk pada satuan ketinggian yang umum digunakan dalam dunia pendakian.</p>
        <p class="logo-description">Tulisan “Since 2025” menunjukkan bahwa Majelis MDPL telah berdiri sejak tahun 2025, menandakan komitmen dan eksistensi dalam mengajak masyarakat untuk lebih dekat dengan alam.</p>
        <p class="logo-description">Secara keseluruhan, logo ini mencerminkan semangat menjelajah, kebersamaan, dan kepedulian terhadap alam dalam satu simbol yang kuat dan bermakna.</p>
      </div>

      <div class="profil-logo">
        <img src="img/majelismdpl.png" alt="Logo Profil">
      </div>
    </div>
  </section>

  <div class="destination-carousel">
    <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>

    <div class="carousel-track">
      <!-- Loading spinner sementara -->
      <div style="display: flex; justify-content: center; align-items: center; width: 100%; height: 200px;">
        <div class="spinner-border text-primary" role="status">
          <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">Loading...</span>
        </div>
      </div>
    </div>

    <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
  </div>

  <section class="gallery-cardstyle">
    <h2>Galeri Foto</h2>
    <div class="card-grid">
      <div class="card">
        <img src="img/foto1.png" alt="Foto 1" />
        <div class="caption">Gunung Bromo Sunrise</div>
      </div>
      <div class="card">
        <img src="img/foto3.png" alt="Foto 2" />
        <div class="caption">Camping Savana</div>
      </div>
      <div class="card">
        <img src="img/foto2.png" alt="Foto 3" />
        <div class="caption">Trip Seru Bersama</div>
      </div>
      <!-- Tambah kartu lainnya serupa -->
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <h2><span class="title-large">Apa Kata Mereka?</span></h2>
      <div class="testimonial-grid">
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Trip ke Rinjani sangat terorganisir. Guide-nya asik dan perhatian. Saya yang
            pemula merasa aman banget!</p>
          <div class="testimonial-author">
            <div class="author-image"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Rudi"></div>
            <div class="author-details">
              <h4>Rudi Saputra</h4>
              <p>Jakarta</p>
            </div>
          </div>
        </div>
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Baru pertama kali ikut open trip, tapi langsung jatuh cinta. Banyak teman baru dan
            pengalaman tak terlupakan.</p>
          <div class="testimonial-author">
            <div class="author-image"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dewi"></div>
            <div class="author-details">
              <h4>Dewi Lestari</h4>
              <p>Bandung</p>
            </div>
          </div>
        </div>
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Sunrise di Bromo, camping di savana, semua sempurna. Majelis MDPL benar-benar
            profesional!</p>
          <div class="testimonial-author">
            <div class="author-image"><img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Andi"></div>
            <div class="author-details">
              <h4>Andi Pratama</h4>
              <p>Surabaya</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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
            <a href="lupa-password.php" style="color: #a97c50; text-decoration: underline; font-size:14px;">
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
              <input type="text" name="username" placeholder="Username" autocomplete="username" required />
            </div>
            <div class="input-group password-group">
              <input type="password" name="password" id="signupPassword" placeholder="Password"
                autocomplete="new-password" required />
              <button type="button" class="password-toggle" id="toggleSignupPassword">
                <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>

            <!-- Row 2 -->
            <div class="input-group password-group">
              <input type="password" name="confirm_password" id="confirmPassword" placeholder="Konfirmasi Password"
                autocomplete="new-password" required />
              <button type="button" class="password-toggle" id="toggleConfirmPassword">
                <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <div class="input-group">
              <input type="email" name="email" placeholder="Email" autocomplete="email" required />
            </div>

            <!-- Row 3 -->
            <div class="input-group">
              <input type="tel" name="no_wa" placeholder="No HP" inputmode="tel" autocomplete="tel" required />
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

  <!-- footer -->
  <footer class="footer">
    <div class="container">
      <!-- Kolom Kiri -->
      <div class="footer-col">
        <h3 class="brand">Pendakian Majelis MDPL</h3>
        <p>
          ✨ Nikmati pengalaman tak terlupakan bersama Majelis MDPL Open Trip. <br>
          Ikuti serunya pendakian tektok maupun camping, rasakan panorama puncak
          yang menakjubkan, dan ciptakan kenangan berharga di setiap perjalanan. 🌲🏔
        </p>
        <div class="social-links">
          <a href="#"><i class="fa-brands fa-facebook"></i></a>
          <a href="#"><i class="fa-brands fa-tiktok"></i></a>
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <!-- Kolom Tengah -->
      <div class="footer-col">
        <h3>Kontak <span>Kami</span></h3>
        <p><strong>Alamat Kami</strong><br>Jl. aseleole, Kaliwates, Jember 55582</p>
        <p><strong>Whatsapp</strong><br>08562898933</p>
        <p><strong>Email</strong><br>majelismdpl@gmail.com</p>
      </div>
      <!-- Kolom Kanan -->
      <div class="footer-col">
        <h3>Quick <span>Link</span></h3>
        <ul>
          <li><a href="#">Profile</a></li>
          <li><a href="#">Paket Open Trip</a></li>
          <li><a href="#">Kontak</a></li>
        </ul>
      </div>
    </div>
    <div class="copyright">
      <p>Copyright © 2025 Majelis Mdpl. All rights reserved. Developed with ❤ by Dimasdw15</p>
    </div>
  </footer>

  <!-- Tombol WhatsApp -->
  <div class="whatsapp-container">
    <button class="whatsapp-button" onclick="bukaWhatsapp()">
      <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
    </button>
  </div>

  <!-- Load SweetAlert first -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Global functions that might be needed -->
  <script> 
    // Buka WhatsApp helper
    function bukaWhatsapp() {
      const nomor = "6283853493130";
      const url = "https://wa.me/" + nomor;
      window.open(url, "_blank");
    }

    // Global function untuk Google OAuth dan Google Login
    function handleGoogleOAuth() {
      window.location.href = window.location.origin + "/majelismdpl.com/backend/google-oauth.php";
    }

    function handleGoogleLogin() {
      window.location.href = window.location.origin + "/majelismdpl.com/backend/google-oauth.php?type=login";
    }

    // Modal animation helpers
    const OPEN = "open";
    const CLOSING = "closing";
    const DURATION = 300;

    function openModal(el) {
      el.classList.remove(CLOSING);
      el.style.display = "flex";
      void el.offsetWidth;
      el.classList.add(OPEN);
    }

    function closeModal(el) {
      el.classList.remove(OPEN);
      el.classList.add(CLOSING);
      setTimeout(() => {
        el.classList.remove(CLOSING);
        el.style.display = "none";
      }, DURATION);
    }

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
  </script>

  <!-- Load external JavaScript files -->
  <script src="frontend/registrasi.js"></script>
  <script src="frontend/login.js"></script>
  <script src="frontend/trip-user.js"></script>

</body>

</html>