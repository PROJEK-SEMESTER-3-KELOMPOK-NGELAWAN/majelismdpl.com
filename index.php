<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Majelis MDPL</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
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
</body>

</html>