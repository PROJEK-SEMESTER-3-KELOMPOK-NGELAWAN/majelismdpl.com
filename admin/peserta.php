<?php
session_start();

$peserta = []; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Peserta - Admin | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
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
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i><span> Dashboard</span></a></li>
      <li class="nav-item"><a href="trip.php" class="nav-link"><i class="bi bi-map-fill"></i><span> Trip</span></a></li>
      <li class="nav-item"><a href="peserta.php" class="nav-link active"><i class="bi bi-people-fill"></i><span> Peserta</span></a></li>
      <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i><span> Pembayaran</span></a></li>
      <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i><span> Galeri</span></a></li>
      <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span> Logout</span></a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1 p-4" id="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Daftar Peserta</h1>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No WA</th>
            <th>Alamat</th>
            <th>Status Pembayaran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($peserta)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">Belum ada peserta</td>
            </tr>
          <?php else: ?>
            <?php foreach ($peserta as $p): ?>
              <tr>
                <td class="text-center"><?= $p['id_participant'] ?></td>
                <td><?= $p['nama'] ?></td>
                <td><?= $p['email'] ?></td>
                <td><?= $p['no_wa'] ?></td>
                <td><?= $p['alamat'] ?></td>
                <td>
                  <?php if ($p['status_bayar'] === "Sudah"): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Sudah Bayar</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Belum Bayar</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit Status
                  </button>
                </td>
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
