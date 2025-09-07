<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Majelis MDPL</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
   <meta charset="UTF-8">
    <title>WhatsApp Button</title>

    <!-- CSS Link -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome untuk ikon WhatsApp -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- JavaScript -->
    <script src="script.js" defer></script>
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
        <a href="#" id="open-login" class="btn">Login</a>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="overlay"></div>
    <div class="hero-text">
      <h1>Pendakian Seru bersama <span>Majelis MDPL</span></h1>
      <p>Ikuti open trip gunung bareng komunitas pendaki seru, aman, dan penuh pengalaman berkesan.</p>
      <a href="#trip" class="btn-hero">Lihat Paket Trip</a>
    </div>
  </section>
    <section class="section" id="profile">
    <h2>Profile</h2>
    <p>Majelis MDPL adalah komunitas yang bergerak dalam kegiatan pendakian gunung secara terorganisir dan aman.</p>
  </section>
  <!-- Deskripsi Aplikasi -->
  <section class="about">
    <div class="container">
      <h2>Apa Itu Majelis Open Trip?</h2>
      <p>Platform pendakian untuk menjelajahi gunung bersama tim profesional.</p>
      <img src="img/fitur-aplikasi.png" alt="Aplikasi" />
    </div>
  </section>

  <!-- Fasilitas Section -->
  <section class="fasilitas">
    <div class="judul-fasilitas">
      <p class="sub">FASILITAS</p>
      <h2><span class="highlight">Fasilitas</span> Kami</h2>
      <p class="deskripsi">
        Nikmati segala kemudahan yang kami sediakan untuk membuat perjalanan wisata Anda di kawasan Gunung Merapi menjadi pengalaman yang tak terlupakan.
      </p>
    </div>

    <div class="fasilitas-container">
      <div class="fasilitas-item">
        <i class="fas fa-music"></i>
        <p>Spotify Manual</p>
      </div>
      <div class="fasilitas-item">
        <i class="fas fa-first-aid"></i>
        <p>P3K</p>
      </div>
      <div class="fasilitas-item">
        <i class="fas fa-camera"></i>
        <p>Dokumentasi</p>
      </div>
      <div class="fasilitas-item">
        <i class="fas fa-book-open"></i>
        <p>Cerita Nabi-nabi</p>
      </div>
      <div class="fasilitas-item">
        <i class="fas fa-heart"></i>
        <p>Jodoh bila beruntung</p>
      </div>
    </div>
  </section>
  
<!-- Testimonials -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2><span class="title-large">Apa Kata Mereka?</span></h2>
            <div class="testimonial-grid">
                <div class="testimonial-card elevation-1">
                    <p class="testimonial-text">Trip ke Rinjani sangat terorganisir. Guide-nya asik dan perhatian. Saya yang pemula merasa aman banget!</p>
                    <div class="testimonial-author">
                        <div class="author-image"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Rudi"></div>
                        <div class="author-details"><h4>Rudi Saputra</h4><p>Jakarta</p></div>
                    </div>
                </div>
                <div class="testimonial-card elevation-1">
                    <p class="testimonial-text">Baru pertama kali ikut open trip, tapi langsung jatuh cinta. Banyak teman baru dan pengalaman tak terlupakan.</p>
                    <div class="testimonial-author">
                        <div class="author-image"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dewi"></div>
                        <div class="author-details"><h4>Dewi Lestari</h4><p>Bandung</p></div>
                    </div>
                </div>
                <div class="testimonial-card elevation-1">
                    <p class="testimonial-text">Sunrise di Bromo, camping di savana, semua sempurna. Majelis MDPL benar-benar profesional!</p>
                    <div class="testimonial-author">
                        <div class="author-image"><img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Andi"></div>
                        <div class="author-details"><h4>Andi Pratama</h4><p>Surabaya</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

  <!-- Kontak -->
  <section class="contact">
    <h2>Kontak Kami</h2>
    <form>
      <input type="text" placeholder="Nama" required />
      <textarea placeholder="Pesan" required></textarea>
      <button type="submit">Kirim</button>
    </form>
  </section>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Majelis Open Trip</p>
  </footer>
  

  <!-- Popup Login -->
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
        <button type="submit" class="btn-login">Masuk</button>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("loginModal");
    const openBtn = document.getElementById("open-login");
    const closeBtn = document.getElementById("close-login");

    openBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modal.style.display = "flex";
    });

    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });
  </script>

<!-- BUTTON WHATSAPP -->
<div class="whatsapp-container">
    <button class="whatsapp-button" onclick="bukaWhatsapp()">
        <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
    </button>
</div>

<script>
  function bukaWhatsapp() {
    const nomor = "6283853493130"; // Ganti dengan nomor kamu
    const url = "https://wa.me/" + nomor;
    window.open(url, '_blank');
  }
</script>
</div>
</body>
</html>
