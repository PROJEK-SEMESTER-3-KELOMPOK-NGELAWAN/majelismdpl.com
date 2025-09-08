<?php
session_start();

$file = __DIR__ . "/trips.json";

if (file_exists($file)) {
  $trips = json_decode(file_get_contents($file), true);
} else {
  $trips = [];
}

// Button tambah
if (isset($_POST['tambah'])) {
  $id = count($trips) + 1;
  $nama = $_POST['nama_gunung'];
  $via = $_POST['via_gunung'];
  $jenis = $_POST['jenis_trip']; 
  $durasi = ($jenis == "Camp") ? $_POST['durasi'] : "-";
  $harga = $_POST['harga'];
  $tanggal = $_POST['tanggal'];
  $slot = $_POST['slot'];
  $status = $_POST['status'];
  $gambar = "default.jpg";

  // Upload gambar
  if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
    $targetDir = "../img/";
    $fileName = time() . "_" . basename($_FILES['gambar']['name']);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
      $gambar = $fileName;
    }
  }

  $trips[] = [
    "id" => $id,
    "nama_gunung" => $nama,
    "via_gunung" => $via,
    "jenis_trip" => $jenis,
    "durasi" => $durasi,
    "harga" => $harga,
    "tanggal" => $tanggal,
    "slot" => $slot,
    "status" => $status,
    "gambar" => $gambar
  ];

  file_put_contents($file, json_encode($trips, JSON_PRETTY_PRINT));
  header("Location: trip.php");
  exit();
}

// Button hapus
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  foreach ($trips as $key => $trip) {
    if ($trip['id'] == $id) {
      unset($trips[$key]);
    }
  }
  $trips = array_values($trips);

  file_put_contents($file, json_encode($trips, JSON_PRETTY_PRINT));
  header("Location: trip.php");
  exit();
}

// Button edit
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  foreach ($trips as &$trip) {
    if ($trip['id'] == $id) {
      $trip['nama_gunung'] = $_POST['nama_gunung'];
      $trip['via_gunung'] = $_POST['via_gunung'];
      $trip['jenis_trip'] = $_POST['jenis_trip'];
      $trip['durasi'] = ($_POST['jenis_trip'] == "Camp") ? $_POST['durasi'] : "-";
      $trip['harga'] = $_POST['harga'];
      $trip['tanggal'] = $_POST['tanggal'];
      $trip['slot'] = $_POST['slot'];
      $trip['status'] = $_POST['status']; 

      // Cek upload gambar baru
      if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $targetDir = "../img/";
        $fileName = time() . "_" . basename($_FILES['gambar']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
          $trip['gambar'] = $fileName;
        }
      }
      break;
    }
  }
  file_put_contents($file, json_encode($trips, JSON_PRETTY_PRINT));
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
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i><span> Dashboard</span></a></li>
      <li class="nav-item"><a href="trip.php" class="nav-link active"><i class="bi bi-map-fill"></i><span> Trip</span></a></li>
      <li class="nav-item"><a href="peserta.php" class="nav-link"><i class="bi bi-people-fill"></i><span> Peserta</span></a></li>
      <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i><span> Pembayaran</span></a></li>
      <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i><span> Galeri</span></a></li>
      <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span> Logout</span></a></li>
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

<!-- Modal Edit Trip -->
<?php foreach ($trips as $trip) : ?>
<div class="modal fade" id="editModal<?= $trip['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $trip['id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-decoration-underline" id="editModalLabel<?= $trip['id'] ?>"> Edit Trip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $trip['id'] ?>">

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Nama Gunung</label>
          <input type="text" name="nama_gunung" value="<?= $trip['nama_gunung'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-signpost-split"></i> Jenis Trip</label>
          <select name="jenis_trip" class="form-control jenisTripEdit" data-id="<?= $trip['id'] ?>" required>
            <option value="Camp" <?= $trip['jenis_trip']=="Camp"?"selected":"" ?>>Camp</option>
            <option value="Tektok" <?= $trip['jenis_trip']=="Tektok"?"selected":"" ?>>Tektok</option>
          </select>
        </div>
        <div class="mb-3 durasiGroupEdit<?= $trip['id'] ?>" style="<?= $trip['jenis_trip']=="Camp"?"":"display:none;" ?>">
          <label class="form-label"><i class="bi bi-clock-history"></i> Durasi Camp</label>
          <input type="text" name="durasi" value="<?= $trip['durasi'] ?>" class="form-control" placeholder="cth: 2 hari 1 malam">
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-cash-coin"></i> Harga Trip / pax</label>
          <input type="text" name="harga" value="<?= $trip['harga'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-calendar-event"></i> Tanggal</label>
          <input type="date" name="tanggal" value="<?= $trip['tanggal'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-people-fill"></i> Slot</label>
          <input type="number" name="slot" value="<?= $trip['slot'] ?>" class="form-control" required>
        </div>
       <div class="mb-3">
          <label class="form-label"><i class="bi bi-check-circle-fill"></i> Status</label>
          <select name="status" class="form-control" required>
            <option value="available" <?= isset($trip) && $trip['status']=="available"?"selected":"" ?>>
              ✅ Available
            </option>
            <option value="sold" <?= isset($trip) && $trip['status']=="sold"?"selected":"" ?>>
              ❌ Sold
            </option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-signpost"></i> Via Gunung</label>
          <input type="text" name="via_gunung" value="<?= $trip['via_gunung'] ?? '' ?>" class="form-control" placeholder="cth: Via Cemoro Kandang">
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-image"></i> Upload Gambar</label>
          <input type="file" name="gambar" class="form-control" accept="image/*">
          <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<!-- Tambah Trip -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="tambahModalLabel">Tambah Trip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Nama Gunung</label>
          <input type="text" name="nama_gunung" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-signpost-2"></i> Via Gunung</label>
          <input type="text" name="via_gunung" class="form-control" placeholder="cth: Via Cemoro Kandang" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-signpost-split"></i> Jenis Trip</label>
          <select name="jenis_trip" class="form-control" id="jenisTrip" required>
            <option value="Camp">Camp</option>
            <option value="Tektok">Tektok</option>
          </select>
        </div>
        <div class="mb-3" id="durasiGroup">
          <label class="form-label"><i class="bi bi-clock-history"></i> Durasi Camp</label>
          <input type="text" name="durasi" class="form-control" placeholder="cth: 2 hari 1 malam">
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-cash-coin"></i> Harga Trip / pax</label>
          <input type="text" name="harga" class="form-control" placeholder="Rp 0" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-calendar-event"></i> Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-people-fill"></i> Slot</label>
          <input type="number" name="slot" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-check-circle-fill"></i> Status</label>
          <select name="status" class="form-control" required>
            <option value="available">✅ Available</option>
            <option value="sold">❌ Sold</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-image"></i> Upload Gambar</label>
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

  document.getElementById("jenisTrip").addEventListener("change", function() {
    const durasiGroup = document.getElementById("durasiGroup");
    if (this.value === "Camp") {
      durasiGroup.style.display = "block";
    } else {
      durasiGroup.style.display = "none";
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>