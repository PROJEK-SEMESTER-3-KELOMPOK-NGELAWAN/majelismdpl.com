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
    .google-signup-section {
      margin-bottom: 20px;
      text-align: center;
    }

    .btn-google {
      display: flex !important;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #dadce0;
      border-radius: 4px;
      background-color: #fff;
      color: #3c4043;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer !important;
      transition: background-color 0.2s;
      margin-bottom: 15px;
      position: relative;
      z-index: 1000;
      pointer-events: auto !important;
      box-sizing: border-box;
    }

    .btn-google:hover {
      background-color: #f8f9fa;
      border-color: #c6c6c6;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-google:active {
      background-color: #e8f0fe;
      transform: translateY(1px);
    }

    .divider {
      position: relative;
      margin: 15px 0;
      text-align: center;
      color: #666;
      font-size: 12px;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background-color: #dadce0;
      z-index: 1;
    }

    .divider span {
      background-color: white;
      padding: 0 15px;
      position: relative;
      z-index: 2;
    }

    /* UNTUK CSS POP UP LOGIN */
    .google-login-section {
      margin-bottom: 20px;
      text-align: center;
    }

    /* CSS untuk tombol Google sudah ada dari sebelumnya */
    .btn-google {
      display: flex !important;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #dadce0;
      border-radius: 4px;
      background-color: #fff;
      color: #3c4043;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer !important;
      transition: background-color 0.2s;
      margin-bottom: 15px;
      position: relative;
      z-index: 1000;
      pointer-events: auto !important;
      box-sizing: border-box;
    }

    .btn-google:hover {
      background-color: #f8f9fa;
      border-color: #c6c6c6;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-google:active {
      background-color: #e8f0fe;
      transform: translateY(1px);
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
    <div class="login-box">
      <a href="#" class="close-btn" id="close-login">&times;</a>
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


  <!-- POPUP SIGN UP (layout 2 kolom + alamat full width) -->
  <div id="signUpModal" class="modal">
    <div class="login-box">
      <a href="#" class="close-btn" id="close-signup">&times;</a>
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
      </form>

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