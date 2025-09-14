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
      <a href="#" id="open-signup" class="btn">Sign Up</a> <!-- Added Sign Up button -->
      <a href="#" id="open-login" class="btn">Login</a>
    </div>
  </div>
</header>


  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="overlay"></div>
    <div class="hero-text">
      <h1>Pendakian Seru bersama <span>Majelis MDPL</span></h1>
      <p>Ikuti open trip gunung bareng komunitas pendaki seru, aman, dan penuh pengalaman berkesan.</p>
      <a href="#trip" class="btn-hero">Lihat Paket Trip</a>
    </div>
  </section>
    <section class="section" id="profile">
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
                  <?= $trip['status']=="sold" ? "bg-danger" : "bg-success" ?>">
                  <i class="bi <?= $trip['status']=="sold" ? "bi-x-circle-fill" : "bi-check-circle-fill" ?>"></i>
                  <?= $trip['status']=="sold" ? "Sold" : "Available" ?>
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
                <!-- Detail -->
                <a href="trip_detail.php?id=<?= $trip['id'] ?>" class="btn btn-info btn-sm">
                  <i class="bi bi-eye"></i> Detail
                </a>  
                  <!-- Edit -->
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $trip['id'] ?>">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <!-- Hapus -->
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
  </div>
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

  <!-- Popup Login -->
  <div id="loginModal" class="modal">
    <div class="login-box">
      <a href="#" class="close-btn" id="close-login">&times;</a>
      <h2>Login</h2>

      <form action="login.php" method="POST">
        <div class="input-group">
          <input type="text" id="username" name="username" placeholder="Username" required />
        </div>
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder="Password" required />
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
      <p>
        Copyright © 2025 Majelis Mdpl. All rights reserved. 
        Developed with ❤️ by Dimasdw15
      </p>
    </div>
  </footer>

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