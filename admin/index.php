<?php
session_start();
if (!isset($_SESSION["admin"])) {
  header("Location: ../index.php");
  exit();
}

// Ngambil data trip dari JSON
$file = __DIR__ . "/trips.json";
if (file_exists($file)) {
  $trips = json_decode(file_get_contents($file), true);
} else {
  $trips = [];
}

// Total trip
$tripAktif = count(array_filter($trips, fn($t) => $t['status'] === "Aktif"));

// Total peserta
$totalPeserta = array_sum(array_column($trips, 'slot'));

// Jadwal terdekat
$today = date("Y-m-d");
$jadwalTerdekat = "Belum ada jadwal";
$upcoming = array_filter($trips, fn($t) => $t['tanggal'] >= $today);
if (!empty($upcoming)) {
  usort($upcoming, fn($a, $b) => strcmp($a['tanggal'], $b['tanggal']));
  $jadwalTerdekat = $upcoming[0]['nama_gunung'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  
  <div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3 d-flex flex-column" id="sidebar">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="mb-0">Menu</h2>
      <button class="btn-toggle" id="toggleBtn">
        <i class="bi bi-list"></i>
      </button>
    </div>

  <ul class="nav flex-column">
    <li class="nav-item"><a href="index.php" class="nav-link active"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a></li>
    <li class="nav-item"><a href="trip.php" class="nav-link"><i class="bi bi-map-fill"></i> <span>Trip</span></a></li>
    <li class="nav-item"><a href="peserta.php" class="nav-link"><i class="bi bi-people-fill"></i> <span>Peserta</span></a></li>
    <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> <span>Pembayaran</span></a></li>
    <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i> <span>Galeri</span></a></li>
    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Logout</span></a></li>
  </ul>
</div>

    <div class="content flex-grow-1 p-4" id="main">
      <h1>Dashboard</h1>

      <div class="row g-3 my-3">
        <div class="col-md-3">
          <div class="card p-3 text-center">
            <h5>Trip Aktif</h5>
            <p class="fs-4 fw-bold"><?= $tripAktif ?></p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3 text-center">
            <h5>Total Peserta</h5>
            <p class="fs-4 fw-bold"><?= $totalPeserta ?></p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3 text-center">
            <h5>Pembayaran Masuk</h5>
            <p class="fs-4 fw-bold">Rp 0</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3 text-center">
            <h5>Jadwal Terdekat</h5>
            <p class="fs-4 fw-bold"><?= $jadwalTerdekat ?></p>
          </div>
        </div>
      </div>

      <div class="card p-3">
        <h4>Daftar Trip</h4>
        <table class="table table-striped mt-2">
          <thead class="table-dark">
            <tr><th>Nama Gunung</th><th>Tanggal</th><th>Slot</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php if (empty($trips)): ?>
              <tr><td colspan="4" class="text-center text-muted">🚫 Belum ada jadwal trip.</td></tr>
            <?php else: ?>
              <?php foreach ($trips as $trip): ?>
              <tr>
                <td><?= $trip['nama_gunung'] ?></td>
                <td><?= date("d M Y", strtotime($trip['tanggal'])) ?></td>
                <td><?= $trip['slot'] ?></td>
                <td><?= $trip['status'] ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggleBtn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
