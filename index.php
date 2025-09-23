<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Majelis MDPL</title>

  <!-- CSS utama -->
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    /* === LOGIN / SIGNUP MODAL === */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      justify-content: center;
      align-items: center;
      z-index: 2000;
      padding: 16px;
      background: rgba(0, 0, 0, 0.6);
      opacity: 0;
      transition: opacity .28s ease, background-color .28s ease;
    }

    .modal.open {
      display: flex;
      opacity: 1;
      background: rgba(0, 0, 0, 0.6);
    }

    .modal.open .modal-container {
      transform: translateY(0) scale(1);
      opacity: 1;
    }

    .modal.closing {
      opacity: 0;
      background: rgba(0, 0, 0, 0.0);
    }

    .modal.closing .modal-container {
      transform: translateY(8px) scale(.98);
      opacity: 0;
    }

    /* Modal Container - menggunakan flexbox untuk layout kiri-kanan */
    .modal-container {
      background: #E3EAC4;
      /* Background cream seperti pada gambar */
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 800px;
      min-height: 500px;
      position: relative;
      display: flex;
      overflow: hidden;
      transform: translateY(12px) scale(.98);
      opacity: 0;
      transition: transform .28s ease, opacity .28s ease;
    }

    /* Close Button */
    .close-btn {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 28px;
      text-decoration: none;
      color: #666;
      z-index: 10;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background-color 0.2s, color 0.2s;
    }

    .close-btn:hover {
      background-color: rgba(0, 0, 0, 0.1);
      color: #333;
    }

    /* Modal Container - menggunakan flexbox untuk layout kiri-kanan */
    .modal-container {
      background: linear-gradient(to right, #E3EAC4 0%, #E3EAC4 45%, #f8f8f8 55%, #ffffff 100%);
      /* Gradasi dari cream ke putih */
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 800px;
      min-height: 500px;
      position: relative;
      display: flex;
      overflow: hidden;
      transform: translateY(12px) scale(.98);
      opacity: 0;
      transition: transform .28s ease, opacity .28s ease;
    }

    /* Left Section - Logo */
    .modal-left {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      background: transparent;
      /* Hapus background solid agar gradasi terlihat */
    }

    .logo-container {
      text-align: center;
      width: 100%;
    }

    .modal-logo {
      max-width: 320px;
      /* Diperbesar dari 250px menjadi 320px */
      width: 100%;
      height: auto;
      object-fit: contain;
    }

    /* Right Section - Form */
    .modal-right {
      flex: 1;
      background: transparent;
      /* Hapus background solid agar gradasi terlihat */
      padding: 50px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .modal-right h2 {
      font-size: 32px;
      font-weight: 600;
      margin-bottom: 30px;
      color: #333;
      text-align: center;
    }


    /* Input Groups */
    .input-group {
      margin-bottom: 20px;
    }

    .input-group input {
      width: 100%;
      padding: 15px 18px;
      border: 2px solid #e1e1e1;
      border-radius: 12px;
      outline: none;
      font-size: 16px;
      transition: border-color 0.3s, box-shadow 0.3s;
      background: #fafafa;
    }

    .input-group input:focus {
      border-color: #AE8340;
      box-shadow: 0 0 0 3px rgba(174, 131, 64, 0.1);
      background: #fff;
    }

    /* Divider */
    .divider {
      position: relative;
      margin: 20px 0;
      text-align: center;
      color: #888;
      font-size: 14px;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background-color: #e1e1e1;
      z-index: 1;
    }

    .divider span {
      background-color: white;
      padding: 0 20px;
      position: relative;
      z-index: 2;
    }

    /* Google Login Button */
    .google-login-section {
      margin-bottom: 20px;
      text-align: center;
    }

    .btn-google {
      display: flex !important;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 15px 16px;
      border: 2px solid #e1e1e1;
      border-radius: 12px;
      background-color: #fff;
      color: #333;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer !important;
      transition: all 0.3s;
      margin-bottom: 20px;
    }

    .btn-google:hover {
      background-color: #f8f9fa;
      border-color: #AE8340;
      box-shadow: 0 2px 8px rgba(174, 131, 64, 0.15);
    }

    /* Login Button */
    .btn-login {
      width: 100%;
      background: #AE8340;
      border: none;
      padding: 15px;
      font-weight: 600;
      color: #ffffff;
      border-radius: 12px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s, transform 0.2s;
    }

    .btn-login:hover {
      background: #8b5e3c;
      transform: translateY(-1px);
    }

    /* Signup Modal Specific Styles */
    .signup-modal {
      max-width: 900px;
      /* Lebih lebar untuk menampung form grid */
      min-height: 550px;
    }

    .signup-modal .modal-right {
      padding: 40px 40px;
      /* Sedikit lebih kompak */
    }

    .signup-modal .modal-right h2 {
      font-size: 28px;
      /* Sedikit lebih kecil */
      margin-bottom: 25px;
    }

    /* Form Grid Layout untuk 2 kolom */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px 20px;
      /* vertical gap, horizontal gap */
      margin-bottom: 25px;
    }

    /* Full width field */
    .field-full {
      grid-column: 1 / -1;
      /* Span across all columns */
    }

    /* Input Groups di dalam grid */
    .form-grid .input-group {
      margin-bottom: 0;
      /* Reset margin karena sudah ada gap di grid */
    }

    .form-grid .input-group input {
      width: 100%;
      padding: 12px 15px;
      /* Sedikit lebih kompak */
      border: 2px solid #e1e1e1;
      border-radius: 10px;
      outline: none;
      font-size: 15px;
      transition: border-color 0.3s, box-shadow 0.3s;
      background: #fafafa;
    }

    .form-grid .input-group input:focus {
      border-color: #AE8340;
      box-shadow: 0 0 0 3px rgba(174, 131, 64, 0.1);
      background: #fff;
    }

    /* Google Signup Section */
    .google-signup-section {
      margin-top: 20px;
      text-align: center;
    }

    /* Responsive Design untuk Signup Modal */
    @media (max-width: 1000px) {
      .signup-modal {
        max-width: 500px;
        flex-direction: column;
        min-height: auto;
        max-height: 90vh;
        overflow-y: auto;
      }

      .signup-modal .modal-left {
        padding: 25px 20px 15px;
      }

      .signup-modal .modal-logo {
        max-width: 160px;
      }

      .signup-modal .modal-right {
        padding: 25px 30px 35px;
      }
    }

    @media (max-width: 750px) {
      .form-grid {
        grid-template-columns: 1fr;
        /* Single column pada mobile */
        gap: 15px;
      }

      .signup-modal {
        max-width: calc(100% - 40px);
        margin: 20px;
      }

      .signup-modal .modal-right {
        padding: 20px 25px 30px;
      }

      .form-grid .input-group input,
      .signup-modal .btn-google,
      .signup-modal .btn-login {
        padding: 12px 15px;
        font-size: 15px;
      }
    }

    @media (max-width: 480px) {
      .signup-modal .modal-right {
        padding: 20px 20px 25px;
      }

      .signup-modal .modal-right h2 {
        font-size: 24px;
        margin-bottom: 20px;
      }

      .form-grid {
        gap: 12px;
      }

      .form-grid .input-group input {
        padding: 11px 14px;
        font-size: 14px;
        border-radius: 8px;
      }
    }

    /* Divider dan button styles tetap sama seperti login modal */
    .signup-modal .divider {
      position: relative;
      margin: 20px 0 15px 0;
      text-align: center;
      color: #888;
      font-size: 14px;
    }

    .signup-modal .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background-color: #e1e1e1;
      z-index: 1;
    }

    .signup-modal .divider span {
      background-color: white;
      padding: 0 20px;
      position: relative;
      z-index: 2;
    }

    .signup-modal .btn-google {
      display: flex !important;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e1e1e1;
      border-radius: 10px;
      background-color: #fff;
      color: #333;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer !important;
      transition: all 0.3s;
      margin-top: 5px;
    }

    .signup-modal .btn-google:hover {
      background-color: #f8f9fa;
      border-color: #AE8340;
      box-shadow: 0 2px 8px rgba(174, 131, 64, 0.15);
    }

    .signup-modal .btn-login {
      width: 100%;
      background: #AE8340;
      border: none;
      padding: 13px;
      font-weight: 600;
      color: #ffffff;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s, transform 0.2s;
      margin-bottom: 15px;
    }

    .signup-modal .btn-login:hover {
      background: #8b5e3c;
      transform: translateY(-1px);
    }


    /* Responsive Design */
    @media (max-width: 900px) {
      .modal-container {
        flex-direction: column;
        max-width: 450px;
        min-height: auto;
        max-height: 90vh;
        overflow-y: auto;
      }

      .modal-left {
        padding: 30px 20px 20px;
      }

      .modal-logo {
        max-width: 220px;
        /* Diperbesar dari 180px menjadi 220px untuk mobile */
      }

      .modal-right {
        padding: 30px 30px 40px;
      }

      .modal-right h2 {
        font-size: 28px;
        margin-bottom: 25px;
      }
    }

    @media (max-width: 600px) {
      .modal-container {
        margin: 20px;
        max-width: calc(100% - 40px);
      }

      .modal-right {
        padding: 25px 20px 35px;
      }

      .modal-logo {
        max-width: 200px;
        /* Disesuaikan untuk layar yang sangat kecil */
      }

      .input-group input,
      .btn-google,
      .btn-login {
        padding: 12px 15px;
        font-size: 15px;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <header class="navbar">
    <div class="container nav-content">
      <div class="logo">
        <img src="img/majelis.png" alt="Majelis MDPL" />
        <span>Majelis MDPL</span>
      </div>
      <nav class="nav-links">
        <a href="#">Home</a>
        <a href="#">Profile</a>
        <a href="#">Jadwal Pendakian</a>
        <a href="#">Testimoni</a>
        <a href="#">Galeri</a>
      </nav>
      <div class="nav-btns">
        <a href="#" id="open-signup" class="btn">Sign Up</a>
        <a href="#" id="open-login" class="btn">Login</a>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <div class="hero-section">
    <!-- Background layer -->
    <div class="hero-bg-custom"></div>

    <img class="hero-bg" src="img/Herooo.jpg" alt="Healing" />
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Kamu Pusing?<br>Yuk Healing</h1>
      <p>Mau Explore berbagai pilihan trip buat nikmatin weekendmu? <br>
        Temukan semuanya di sini.</p>
      <button class="hero-btn">Lihat Semua <i class="fas fa-arrow-right"></i></button>
      <div class="destination-carousel">
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <div class="carousel-track">
          <!-- Card Destinasi -->
          <!-- trip ini diambil dari file frontend/trip-user.js -->
          <!-- Tambahkan lagi card di sini jika perlu -->

        </div>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
  <script src="carousel.js"></script>

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
          <p>Mau liburan ke Bandung, Lembang, Yogyakarta, Semarang, Surabaya, Gunung ataupun Laut semuanya ada di Explorer.ID.</p>
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

  <!-- CARD -->
  <div class="row g-4">
    <?php if (empty($trips)): ?>
      <div class="d-flex justify-content-center align-items-center" style="height:60vh;">
        <p class="text-muted fs-4 fade-text">🚫 Belum ada jadwal trip.</p>
      </div>
    <?php else: ?>
      <?php foreach ($trips as $trip) : ?>
        <div class="col-md-4 fade-text">
          <div class="card shadow-sm border-0 rounded-4 h-100 text-center">
            <div class="position-relative">
              <!-- Badge Status Trip -->
              <span class="badge position-absolute top-0 start-0 m-2 px-3 py-2 
                <?= $trip['status'] == "sold" ? "bg-danger" : "bg-success" ?>">
                <i class="bi <?= $trip['status'] == "sold" ? "bi-x-circle-fill" : "bi-check-circle-fill" ?>"></i>
                <?= $trip['status'] == "sold" ? "Sold" : "Available" ?>
              </span>
              <!-- Gambar -->
              <img src="../img/<?= $trip['gambar'] ?>"
                class="card-img-top rounded-top-4"
                alt="<?= $trip['nama_gunung'] ?>"
                style="height:200px; object-fit:cover;">
            </div>
            <div class="card-body text-center">
              <!-- Tanggal & Durasi -->
              <div class="d-flex justify-content-between small text-muted mb-2">
                <span><i class="bi bi-calendar-event"></i> <?= date("d M Y", strtotime($trip['tanggal'])) ?></span>
                <span><i class="bi bi-clock"></i> <?= $trip['jenis_trip'] == "Camp" ? $trip['durasi'] : "1 hari" ?></span>
              </div>
              <!-- Judul -->
              <h5 class="card-title fw-bold"><?= $trip['nama_gunung'] ?></h5>
              <div class="mb-2">
                <span class="badge bg-secondary">
                  <i class="bi bi-flag-fill"></i> <?= $trip['jenis_trip'] ?>
                </span>
              </div>
              <!-- Rating & Ulasan -->
              <div class="small text-muted mb-2">
                <i class="bi bi-star-fill text-warning"></i> 5 (<?= rand(101, 300) ?>+ ulasan)
              </div>
              <!-- Via Gunung -->
              <div class="small text-muted mb-2">
                <i class="bi bi-signpost-2"></i> Via <?= $trip['via_gunung'] ?? '-' ?>
              </div>
              <!-- Harga -->
              <h5 class="fw-bold text-success mb-3">
                Rp <?= number_format((int)str_replace(['.', ','], '', $trip['harga']), 0, ',', '.') ?>
              </h5>
              <!-- Tombol Aksi -->
              <div class="d-flex justify-content-between">
                <a href="trip_detail.php?id=<?= $trip['id'] ?>" class="btn btn-info btn-sm">
                  <i class="bi bi-eye"></i> Detail
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $trip['id'] ?>">
                  <i class="bi bi-pencil-square"></i> Edit
                </button>
                <a href="trip.php?hapus=<?= $trip['id'] ?>"
                  onclick="return confirm('Hapus trip ini?');"
                  class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i> Hapus
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Testimonials -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <h2><span class="title-large">Apa Kata Mereka?</span></h2>
      <div class="testimonial-grid">
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Trip ke Rinjani sangat terorganisir. Guide-nya asik dan perhatian. Saya yang pemula merasa aman banget!</p>
          <div class="testimonial-author">
            <div class="author-image"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Rudi"></div>
            <div class="author-details">
              <h4>Rudi Saputra</h4>
              <p>Jakarta</p>
            </div>
          </div>
        </div>
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Baru pertama kali ikut open trip, tapi langsung jatuh cinta. Banyak teman baru dan pengalaman tak terlupakan.</p>
          <div class="testimonial-author">
            <div class="author-image"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dewi"></div>
            <div class="author-details">
              <h4>Dewi Lestari</h4>
              <p>Bandung</p>
            </div>
          </div>
        </div>
        <div class="testimonial-card elevation-1">
          <p class="testimonial-text">Sunrise di Bromo, camping di savana, semua sempurna. Majelis MDPL benar-benar profesional!</p>
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
          <img src="assets/logo-majelis.png" alt="Majelis MDPL Logo" class="modal-logo">
        </div>
      </div>

      <!-- Right section dengan form -->
      <div class="modal-right">
        <h2>Login</h2>

        <form action="login.php" method="POST">
          <div class="input-group">
            <input type="text" name="username" placeholder="Username" required />
          </div>
          <div class="input-group">
            <input type="password" name="password" placeholder="Password" required />
          </div>

          <!-- Tombol Login dengan Google -->
          <div class="divider">
            <span>atau</span>
          </div>

          <div class="google-login-section">
            <button type="button" id="googleLoginBtn" class="btn-google">
              <img src="img/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
              Login with Google
            </button>
          </div>

          <button type="submit" class="btn-login">Masuk</button>

          <div style="text-align:center;margin-top:13px;">
            <a href="lupa-password.php" style="color: #a97c50; text-decoration: underline; font-size:14px;">
              Lupa password?
            </a>
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
          <img src="assets/logo-majelis.png" alt="Majelis MDPL Logo" class="modal-logo">
        </div>
      </div>

      <!-- Right section dengan form -->
      <div class="modal-right">
        <h2>Sign Up</h2>

        <form method="POST" novalidate>
          <div class="form-grid">
            <!-- Row 1 -->
            <div class="input-group">
              <input type="text" name="username" placeholder="Username" autocomplete="username" required />
            </div>
            <div class="input-group">
              <input type="password" name="password" placeholder="Password" autocomplete="new-password" required />
            </div>

            <!-- Row 2 -->
            <div class="input-group">
              <input type="email" name="email" placeholder="Email" autocomplete="email" required />
            </div>
            <div class="input-group">
              <input type="tel" name="no_wa" placeholder="No HP" inputmode="tel" autocomplete="tel" required />
            </div>

            <!-- Row 3: full width -->
            <div class="input-group field-full">
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
              <img src="img/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
              Sign up with Google
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <footer class="footer">
    <div class="container">
      <!-- Kolom Kiri -->
      <div class="footer-col">
        <h3 class="brand">Pendakian Majelis MDPL</h3>
        <p>
          ✨ Nikmati pengalaman tak terlupakan bersama Majelis MDPL Open Trip. <br>
          Ikuti serunya pendakian tektok maupun camping, rasakan panorama puncak
          yang menakjubkan, dan ciptakan kenangan berharga di setiap perjalanan. 🌲🏔️
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
      <p>Copyright © 2025 Majelis Mdpl. All rights reserved. Developed with ❤️ by Dimasdw15</p>
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
    function bukaWhatsapp() {
      const nomor = "6283853493130";
      const url = "https://wa.me/" + nomor;
      window.open(url, "_blank");
    }

    // Global function untuk Google OAuth (fallback)
    function handleGoogleOAuth() {
      console.log("Global handleGoogleOAuth called");
      window.location.href = window.location.origin + "/majelismdpl.com/backend/google-oauth.php";
    }

    // Global function untuk Google Login (fallback)
    function handleGoogleLogin() {
      console.log("Global handleGoogleLogin called");
      window.location.href = window.location.origin + "/majelismdpl.com/backend/google-oauth.php?type=login";
    }

    // ====== util animasi modal (sesuai CSS .open/.closing) ======
    const OPEN = "open";
    const CLOSING = "closing";
    const DURATION = 300;

    function openModal(el) {
      el.classList.remove(CLOSING);
      el.style.display = "flex";
      void el.offsetWidth; // force reflow
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

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Main script DOMContentLoaded executed');

      // ====== LOGIN MODAL ======
      const loginModal = document.getElementById("loginModal");
      const openLogin = document.getElementById("open-login");
      const closeLogin = document.getElementById("close-login");

      if (openLogin && loginModal && closeLogin) {
        openLogin.addEventListener("click", (e) => {
          e.preventDefault();
          openModal(loginModal);

          // After opening modal, try to attach Google login button event
          setTimeout(() => {
            const googleLoginBtn = document.getElementById("googleLoginBtn");
            if (googleLoginBtn && !googleLoginBtn.hasAttribute('data-main-login-listener')) {
              googleLoginBtn.setAttribute('data-main-login-listener', 'true');
              googleLoginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Google login button clicked from main script!');
                handleGoogleLogin();
              });
              console.log('Google login button listener attached from main script');
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

      // ====== SIGN UP MODAL ======
      const signUpModal = document.getElementById("signUpModal");
      const openSignUp = document.getElementById("open-signup");
      const closeSignUp = document.getElementById("close-signup");

      if (openSignUp && signUpModal && closeSignUp) {
        openSignUp.addEventListener("click", (e) => {
          e.preventDefault();
          openModal(signUpModal);

          // After opening modal, try to attach Google signup button event
          setTimeout(() => {
            const googleSignupBtn = document.getElementById("googleSignUpBtn");
            if (googleSignupBtn && !googleSignupBtn.hasAttribute('data-main-signup-listener')) {
              googleSignupBtn.setAttribute('data-main-signup-listener', 'true');
              googleSignupBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Google signup button clicked from main script!');
                handleGoogleOAuth();
              });
              console.log('Google signup button listener attached from main script');
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
    });
  </script>

  <!-- Load external JavaScript files -->
  <script src="frontend/registrasi.js"></script>
  <script src="frontend/login.js"></script>
  <script src="frontend/trip-user.js"></script>

</body>

</html>