<?php
session_start();

$file = __DIR__ . "/trips.json";

if (file_exists($file)) {
    $trips = json_decode(file_get_contents($file), true);
} else {
    $trips = [];
}

// Fungsi upload multiple file gambar
function uploadMultipleFiles($files) {
    $uploadedImages = [];
    if (!empty($files) && isset($files['name']) && is_array($files['name'])) {
        $targetDir = "../img/";
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0) {
                $fileName = time() . '_' . uniqid() . '_' . basename($files['name'][$i]);
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                    $uploadedImages[] = $fileName;
                }
            }
        }
    }
    return $uploadedImages;
}

// Button tambah
if (isset($_POST['tambah'])) {
    $id = (count($trips) > 0) ? max(array_column($trips, 'id')) + 1 : 1;
    $nama = $_POST['nama_gunung'];
    $via = $_POST['via_gunung'] ?? '';
    $jenis = $_POST['jenis_trip'];
    $durasi = ($jenis == "Camp") ? ($_POST['durasi'] ?? '-') : "-";
    $harga = $_POST['harga'];
    $tanggal = $_POST['tanggal'];
    $slot = $_POST['slot'];
    $status = $_POST['status'];

    $uploadedImages = uploadMultipleFiles($_FILES['gambar']);
    $gambar = !empty($uploadedImages) ? $uploadedImages : ["default.jpg"];

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
            $trip['via_gunung'] = $_POST['via_gunung'] ?? '';
            $trip['jenis_trip'] = $_POST['jenis_trip'];
            $trip['durasi'] = ($_POST['jenis_trip'] == "Camp") ? ($_POST['durasi'] ?? '-') : "-";
            $trip['harga'] = $_POST['harga'];
            $trip['tanggal'] = $_POST['tanggal'];
            $trip['slot'] = $_POST['slot'];
            $trip['status'] = $_POST['status'];

            $uploadedImages = uploadMultipleFiles($_FILES['gambar']);
            if (!empty($uploadedImages)) {
                $trip['gambar'] = $uploadedImages;
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    .btn-detail {
      background-color: #5a5a5a; 
      color: white;
      border: none;
    }
    .btn-detail:hover {
      background-color: #4e4e4e;
    }
    .btn-edit {
      background-color: #f39c12; 
      color: white;
      border: none;
    }
    .btn-edit:hover {
      background-color: #e67e22;
    }
    .btn-delete {
      background-color: #dc3545; 
      color: white;
      border: none;
    }
    .btn-delete:hover {
      background-color: #c82333;
    }
    .card-img-top {
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .badge {
      font-size: 0.9rem;
      border-radius: 5px;
    }
    .card-body {
      padding: 1.5rem;
      text-align: center;
    }
    .badge-success {
      background-color: rgba(40, 167, 69, 0.7); 
      color: white;
    }
    .badge-danger {
      background-color: rgba(220, 53, 69, 0.7); 
      color: white;
    }
    .text-muted {
      font-size: 0.8rem;
    }
    .preview-img {
      width: 90px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      margin-right: 8px;
      margin-bottom: 8px;
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
          <p class="text-muted fs-4">🚫 Belum ada jadwal trip.</p>
        </div>
      <?php else: ?>
        <?php foreach ($trips as $trip) :
          $mainImage = "default.jpg";
          if (!empty($trip['gambar'])) {
              if (is_array($trip['gambar'])) {
                  $mainImage = $trip['gambar'][0];
              } else {
                  $mainImage = $trip['gambar'];
              }
          }
        ?>
          <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 text-center">
              <div class="position-relative">
                <span class="badge position-absolute top-0 start-0 m-2 px-3 py-2" 
                  style="
                    color: white;
                    font-size: 0.8rem;
                    background-color: <?= $trip['status'] == 'sold' ? 'rgba(220, 53, 69, 0.4)' : 'rgba(40, 167, 69, 0.4)' ?>;
                    border-radius: 20px;
                    padding: 5px 10px;">
                  <i class="bi <?= $trip['status'] == 'sold' ? 'bi-x-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                  <?= $trip['status'] == 'sold' ? 'Sold' : 'Available' ?>
                </span>
                <img src="../img/<?= htmlspecialchars($mainImage) ?>" class="card-img-top" alt="<?= htmlspecialchars($trip['nama_gunung']) ?>">
              </div>
              <div class="card-body text-center">
                <div class="d-flex justify-content-between small text-muted mb-2">
                  <span><i class="bi bi-calendar-event"></i> <?= date("d M Y", strtotime($trip['tanggal'])) ?></span>
                  <span><i class="bi bi-clock"></i> <?= $trip['jenis_trip'] == "Camp" ? $trip['durasi'] : "1 hari" ?></span>
                </div>
                <h5 class="card-title fw-bold"><?= $trip['nama_gunung'] ?></h5>
                <div class="mb-2">
                  <span class="badge bg-secondary">
                    <i class="bi bi-flag-fill"></i> <?= $trip['jenis_trip'] ?>
                  </span>
                </div>
                <div class="small text-muted mb-2">
                  <i class="bi bi-star-fill text-warning"></i> 5 (<?= rand(101, 300) ?>+ ulasan)
                </div>
                <div class="small text-muted mb-2">
                  <i class="bi bi-signpost-2"></i> Via <?= $trip['via_gunung'] ?? '-' ?>
                </div>
                <h5 class="fw-bold text-success mb-3">
                  Rp <?= number_format((int)str_replace(['.', ','], '', $trip['harga']), 0, ',', '.') ?>
                </h5>
                <div class="d-flex justify-content-between">
                  <a href="trip_detail.php?id=<?= $trip['id'] ?>" class="btn btn-detail btn-sm">
                    <i class="bi bi-arrow-right"></i> Detail
                  </a>
                  <button class="btn btn-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $trip['id'] ?>">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <a href="trip.php?hapus=<?= $trip['id'] ?>" onclick="return confirm('Hapus trip ini?');" class="btn btn-delete btn-sm">
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

<!-- Modal Tambah Trip -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Trip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Input fields -->
        <div class="mb-3">
          <label class="form-label">Nama Gunung</label>
          <input type="text" name="nama_gunung" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Jenis Trip</label>
          <select name="jenis_trip" class="form-control" required>
            <option value="Camp">Camp</option>
            <option value="Tektok">Tektok</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga Trip / pax</label>
          <input type="text" name="harga" class="form-control" required>
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
          <select name="status" class="form-control" required>
            <option value="available">Available</option>
            <option value="sold">Sold</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Via Gunung</label>
          <input type="text" name="via_gunung" class="form-control" placeholder="cth: Via Cemoro Kandang">
        </div>
        <div class="mb-3">
          <label class="form-label">Durasi (hanya untuk Camp)</label>
          <input type="text" name="durasi" class="form-control" placeholder="cth: 3 Hari">
        </div>
        <div class="mb-3">
          <label class="form-label">Upload Gambar (bisa lebih dari satu)</label>
          <input type="file" name="gambar[]" multiple class="form-control" accept="image/*" id="gambarInputTambah">
        </div>
        <div id="previewContainerTambah" class="d-flex flex-wrap gap-2 mb-3"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="tambah" class="btn btn-success">Simpan Trip</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Trip -->
<div class="modal fade" id="editModal<?= $trip['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $trip['id'] ?>" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel<?= $trip['id'] ?>">Edit Trip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $trip['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Nama Gunung</label>
          <input type="text" name="nama_gunung" value="<?= htmlspecialchars($trip['nama_gunung']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Jenis Trip</label>
          <select name="jenis_trip" class="form-control" required>
            <option value="Camp" <?= ($trip['jenis_trip'] == "Camp") ? "selected" : "" ?>>Camp</option>
            <option value="Tektok" <?= ($trip['jenis_trip'] == "Tektok") ? "selected" : "" ?>>Tektok</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga Trip / pax</label>
          <input type="text" name="harga" value="<?= htmlspecialchars($trip['harga']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" value="<?= htmlspecialchars($trip['tanggal']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Slot</label>
          <input type="number" name="slot" value="<?= htmlspecialchars($trip['slot']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-control" required>
            <option value="available" <?= ($trip['status'] == "available") ? "selected" : "" ?>>Available</option>
            <option value="sold" <?= ($trip['status'] == "sold") ? "selected" : "" ?>>Sold</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Via Gunung</label>
          <input type="text" name="via_gunung" value="<?= htmlspecialchars($trip['via_gunung'] ?? '') ?>" class="form-control" placeholder="cth: Via Cemoro Kandang">
        </div>
        <div class="mb-3">
          <label class="form-label">Durasi (hanya untuk Camp)</label>
          <input type="text" name="durasi" value="<?= htmlspecialchars($trip['durasi']) ?>" class="form-control" placeholder="cth: 3 Hari">
        </div>

        <!-- Gambar Saat Ini -->
        <label class="form-label">Gambar Saat Ini</label>
        <div id="currentImagesContainer<?= $trip['id'] ?>" class="d-flex flex-wrap gap-2 mb-3">
          <?php 
            $images = is_array($trip['gambar']) ? $trip['gambar'] : [$trip['gambar'] ?? 'default.jpg'];
            foreach ($images as $img): ?>
            <div class="position-relative" style="width:90px; height:70px;">
              <img src="../img/<?= htmlspecialchars($img) ?>" class="img-thumbnail" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
              <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 p-1 delete-btn" style="border-radius:50%; font-size:1rem; line-height:1;">&times;</button>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Upload Gambar Baru -->
        <div class="mb-3">
          <label class="form-label">Upload Gambar Baru (bisa lebih dari satu)</label>
          <input type="file" name="gambar[]" multiple class="form-control" accept="image/*" id="gambarInput<?= $trip['id'] ?>">
        </div>
        <div id="previewContainer<?= $trip['id'] ?>" class="d-flex flex-wrap gap-2 mb-3"></div>

      </div>
      <div class="modal-footer">
        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>

    // Sidebar toggle
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggleBtn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");
    });
    // Sidebar toggle end

  (function() {
    const inputId = 'gambarInput<?= $trip['id'] ?>';
    const previewId = 'previewContainer<?= $trip['id'] ?>';
    const currentImagesId = 'currentImagesContainer<?= $trip['id'] ?>';

    const inputElement = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewId);
    const currentImagesContainer = document.getElementById(currentImagesId);

    // Preview gambar baru sebelum upload
    inputElement.addEventListener('change', () => {
      previewContainer.innerHTML = '';
      const files = inputElement.files;
      if (!files.length) return;
      Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.width = '90px';
          img.style.height = '70px';
          img.style.objectFit = 'cover';
          img.style.borderRadius = '8px';
          img.style.boxShadow = '0 2px 6px rgba(0,0,0,0.15)';
          img.style.marginRight = '8px';
          img.style.marginBottom = '8px';
          previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });

    // Hapus gambar saat ini (demo frontend)
    currentImagesContainer.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const container = this.parentElement;
        if (container) container.remove();
        alert('Gambar akan dihapus (frontend only, belum tersambung backend)');
      });
    });
  })();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setupImagePreview(inputId, previewContainerId) {
    const inputElement = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewContainerId);

    inputElement.addEventListener('change', () => {
      previewContainer.innerHTML = '';
      const files = inputElement.files;

      if (!files.length) return;

      Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.classList.add('preview-img');
          previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });
  }

  // Setup preview untuk modal tambah
  setupImagePreview('gambarInputTambah', 'previewContainerTambah');

  // Setup preview untuk modal edit masing-masing trip
  <?php foreach($trips as $trip): ?>
    setupImagePreview('gambarInputEdit<?= $trip['id'] ?>', 'previewContainerEdit<?= $trip['id'] ?>');
  <?php endforeach; ?>
</script>
</body>
</html>
