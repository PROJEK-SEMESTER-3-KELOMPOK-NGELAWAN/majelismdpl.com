<?php
session_start();

$file = __DIR__ . "/trips.json";

if (!file_exists($file)) {
  die("Data trip tidak ditemukan.");
}

$trips = json_decode(file_get_contents($file), true);

$id = $_GET['id'] ?? null;
$trip = null;
foreach ($trips as $t) {
  if ($t['id'] == $id) {
    $trip = $t;
    break;
  }
}

if (!$trip) {
  die("Trip tidak ditemukan.");
}

if (isset($_POST['simpan'])) {
  $include = trim($_POST['include']);
  $exclude = trim($_POST['exclude']);
  $snk     = trim($_POST['snk']);

  if ($include === "" || $snk === "") {
    echo "<script>alert('Include dan S&K wajib diisi!'); window.history.back();</script>";
    exit();
  }

  foreach ($trips as &$t) {
    if ($t['id'] == $id) {
      $t['include'] = $include;
      $t['exclude'] = $exclude;
      $t['snk']     = $snk; 
      break;
    }
  }
  file_put_contents($file, json_encode($trips, JSON_PRETTY_PRINT));
  header("Location: trip_detail.php?id=$id");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Trip - Admin | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light p-4">

<div class="container">
  <h2 class="mb-4 text-center fw-bold">Detail Trip: <?= $trip['nama_gunung'] ?></h2>

  <!-- Card Detail Trip -->
  <div class="card shadow-lg mb-4">
    <div class="row g-0">
      <!-- Gambar -->
      <div class="col-md-5 text-center p-3">
        <img src="../img/<?= $trip['gambar'] ?>" class="img-fluid rounded" style="max-height:300px; object-fit:cover;">
      </div>
      <!-- Info Trip -->
      <div class="col-md-7">
        <div class="card-body">
          <h4 class="card-title"><?= $trip['nama_gunung'] ?></h4>
          <p><i class="bi bi-calendar-check"></i> <b>Tanggal:</b> <?= date("d M Y", strtotime($trip['tanggal'])) ?></p>
          <p><i class="bi bi-people-fill"></i> <b>Slot:</b> <?= $trip['slot'] ?></p>
          <p><i class="bi bi-flag-fill"></i> <b>Jenis Trip:</b> <?= $trip['jenis_trip'] ?></p>
          <p><i class="bi bi-check-circle-fill"></i> <b>Status:</b> 
            <?php if ($trip['status'] == "Available"): ?>
              <span class="badge bg-success"><i class="bi bi-check-circle"></i> Available</span>
            <?php else: ?>
              <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Sold</span>
            <?php endif; ?>
          </p>
          <p><i class="bi bi-cash-stack"></i> <b>Harga:</b> Rp <?= number_format($trip['harga'], 0, ',', '.') ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Card Detail Admin -->
  <div class="card shadow-lg mb-4">
    <div class="card-body">
      <h4 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-list-check"></i> Detail Admin</h4>
      <p><b>Include:</b><br><?= nl2br($trip['include'] ?? '-') ?></p>
      <p><b>Exclude:</b><br><?= nl2br($trip['exclude'] ?? '-') ?></p>
      <p><b>Syarat & Ketentuan:</b><br><?= nl2br($trip['snk'] ?? '-') ?></p>

      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
        <i class="bi bi-pencil-square"></i> Edit Detail
      </button>
      <a href="trip.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square"></i> Edit Detail Trip</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-check2-circle"></i> Include</label>
            <textarea name="include" class="form-control" rows="3" required><?= $trip['include'] ?? '' ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-x-circle"></i> Exclude</label>
            <textarea name="exclude" class="form-control" rows="3"><?= $trip['exclude'] ?? '' ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-file-earmark-text"></i> Syarat & Ketentuan</label>
            <textarea name="snk" class="form-control" rows="3" required><?= $trip['snk'] ?? '' ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="simpan" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
