<!DOCTYPE html>
<html>
<head>
  <title>Profile - Majelis MDPL</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Navbar -->
  <header class="navbar">
    <div class="container nav-content">
      <div class="logo">
        <img src="img/majelis.png" alt="Majelis MDPL" />
        <span>Majelis MDPL</span>
      </div>
      <nav>
        <ul class="nav-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="profile.php" class="active">Profile</a></li>
          <li><a href="jadwalpendakian.php">Jadwal Pendakian</a></li>
          <li><a href="testimoni.php">Testimoni</a></li>
          <li><a href="galeri.php">Galeri</a></li>
        </ul>
      </nav>
      <div class="nav-btns">
        <a href="#" class="btn">Sign Up</a>
        <a href="#" class="btn">Login</a>
      </div>
    </div>
  </header>
  <main style="padding-top: 70px;">
    <!-- CARD -->
    <div class="row g-4">
      <?php if (empty($trips)): ?>
        <div class="d-flex justify-content-center align-items-center" style="height:60vh;">
          <p class="text-muted fs-4 fade-text">🚫 Belum ada jadwal trip.</p>
        </div>
      <?php else: ?>
        <?php foreach ($trips as $trip): ?>
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
                <img src="../img/<?= $trip['gambar'] ?>" class="card-img-top rounded-top-4"
                  alt="<?= $trip['nama_gunung'] ?>" style="height:200px; object-fit:cover;">
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
                  Rp <?= number_format((int) str_replace(['.', ','], '', $trip['harga']), 0, ',', '.') ?>
                </h5>
                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-between">
                  <a href="trip_detail.php?id=<?= $trip['id'] ?>" class="btn btn-info btn-sm">
                    <i class="bi bi-eye"></i> Detail
                  </a>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#editModal<?= $trip['id'] ?>">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <a href="trip.php?hapus=<?= $trip['id'] ?>" onclick="return confirm('Hapus trip ini?');"
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