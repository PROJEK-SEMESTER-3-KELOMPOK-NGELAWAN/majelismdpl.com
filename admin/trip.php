<?php
session_start();

// Inisialisasi data trip kosong kalau belum ada
if (!isset($_SESSION['trips'])) {
  $_SESSION['trips'] = [];
}

// Tambah trip baru
if (isset($_POST['tambah'])) {
  $id = count($_SESSION['trips']) + 1;
  $nama = $_POST['nama_gunung'];
  $tanggal = $_POST['tanggal'];
  $slot = $_POST['slot'];
  $status = $_POST['status'];
  $gambar = "default.jpg";

  // Proses upload file
  if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
    $targetDir = "../img/";
    $fileName = time() . "_" . basename($_FILES['gambar']['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
      $gambar = $fileName;
    }
  }

  $_SESSION['trips'][] = [
    "id" => $id,
    "nama_gunung" => $nama,
    "tanggal" => $tanggal,
    "slot" => $slot,
    "status" => $status,
    "gambar" => $gambar
  ];

  header("Location: trip.php");
  exit();
}

// Hapus trip
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  foreach ($_SESSION['trips'] as $key => $trip) {
    if ($trip['id'] == $id) {
      unset($_SESSION['trips'][$key]);
    }
  }
  $_SESSION['trips'] = array_values($_SESSION['trips']);
  header("Location: trip.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Trip - Admin | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    /* Animasi fade-in */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-text {
      animation: fadeIn 1s ease-in-out;
    }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3 d-flex flex-column" id="sidebar">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="mb-0">Menu</h2>
      <button class="btn-toggle" id="toggleBtn"><i class="bi bi-list"></i></button>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
      <li class="nav-item"><a href="trip.php" class="nav-link active"><i class="bi bi-map-fill"></i> Trip</a></li>
      <li class="nav-item"><a href="peserta.php" class="nav-link"><i class="bi bi-people-fill"></i> Peserta</a></li>
      <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran</a></li>
      <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i> Galeri</a></li>
      <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1 p-4" id="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Daftar Trip</h1>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahModal">
        <i class="bi bi-plus-circle"></i> Tambah Trip
      </button>
    </div>

    <div class="row g-4">
        <?php if (empty($_SESSION['trips'])): ?>
    <div class="d-flex justify-content-center align-items-center" style="height:60vh;">
      <p class="text-muted fs-4 fade-text">🚫 Belum ada jadwal trip.</p>
    </div>
  <?php else: ?>
    <?php foreach ($_SESSION['trips'] as $trip) : ?>
      <div class="col-md-4 fade-text">
        <div class="card shadow-sm">
          <img src="../img/<?= $trip['gambar'] ?>" class="card-img-top" alt="<?= $trip['nama_gunung'] ?>">
          <div class="card-body">
            <h5 class="card-title"><?= $trip['nama_gunung'] ?></h5>
            <p class="card-text">
              📅 <?= date("d M Y", strtotime($trip['tanggal'])) ?><br>
              🎟️ Slot: <?= $trip['slot'] ?><br>
              <?= $trip['status'] ?>
            </p>
            <div class="d-flex justify-content-between">
              <a href="#" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
              <a href="trip.php?hapus=<?= $trip['id'] ?>" onclick="return confirm('Hapus trip ini?');" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Tambah Trip -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="tambahModalLabel">Tambah Trip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Gunung</label>
          <input type="text" name="nama_gunung" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Slot</label>
          <input type="number" name="slot" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="Aktif">Aktif</option>
            <option value="Penuh">Penuh</option>
            <option value="Ditutup">Ditutup</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Upload Gambar</label>
          <input type="file" name="gambar" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
      </div>
    </form>
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
