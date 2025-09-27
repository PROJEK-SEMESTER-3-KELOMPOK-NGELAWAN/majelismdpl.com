<?php
require_once 'auth_check.php';
require_once '../backend/koneksi.php';



$id = $_GET['id'] ?? null;

// Ambil data tabel
$trip = [];
if ($id) {
  $stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
  $stmtTrip->bind_param("i", $id);
  $stmtTrip->execute();
  $resultTrip = $stmtTrip->get_result();
  $trip = $resultTrip->fetch_assoc();
}
if (!$trip) {
  $trip = [
    'nama_gunung' => '',
    'tanggal' => '',
    'slot' => '',
    'durasi' => '',
    'jenis_trip' => '',
    'harga' => '',
    'via_gunung' => '',
    'status' => '',
    'gambar' => ''
  ];
}

$detail = [];
if ($id) {
  $stmtDetail = $conn->prepare("SELECT * FROM detail_trips WHERE id_trip = ?");
  $stmtDetail->bind_param("i", $id);
  $stmtDetail->execute();
  $resultDetail = $stmtDetail->get_result();
  $detail = $resultDetail->fetch_assoc();
}
if (!$detail) {
  $detail = [
    'nama_lokasi' => '',
    'alamat' => '',
    'waktu_kumpul' => '',
    'link_map' => '',
    'include' => '',
    'exclude' => '',
    'syaratKetentuan' => ''
  ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Trip | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      background: #f6f0e8;
      font-family: "Poppins", Arial, sans-serif;
      color: #232323;
      min-height: 100vh;
      padding: 20px;
    }

    .container-detail {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
      padding: 30px 40px;
    }

    h1,
    h2 {
      color: #a97c50;
      font-weight: 700;
      letter-spacing: 1.2px;
    }

    .trip-header {
      display: flex;
      gap: 25px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    .trip-image {
      max-width: 320px;
      flex-shrink: 0;
      border-radius: 14px;
      object-fit: cover;
      height: 220px;
      width: 200px;
      box-shadow: 0 4px 16px rgba(169, 124, 80, 0.3);
    }

    .trip-info h1 {
      margin-bottom: 12px;
    }

    .badge-status {
      text-transform: uppercase;
      font-weight: 700;
      padding: 6px 14px;
      border-radius: 14px;
      background-color: #63c494;
      color: white;
      font-size: 0.85em;
      display: inline-block;
      margin-bottom: 18px;
      width: max-content;
    }

    .badge-status.sold {
      background-color: #d48d9a;
    }

    .trip-meta {
      font-size: 1em;
      color: #444;
      margin-bottom: 30px;
      display: flex;
      flex-wrap: wrap;
      gap: 14px 28px;
    }

    .trip-meta span {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      color: #695a3a;
    }

    .trip-price {
      font-size: 1.6em;
      font-weight: 700;
      color: #2ea564;
      margin-bottom: 40px;
    }

    .info-box {
      background: white;
      border-radius: 18px;
      box-shadow: 0 2px 12px rgba(30, 30, 50, 0.05);
      padding: 25px 25px 18px 25px;
      margin-bottom: 30px;
      color: #ebebeb;
      border: 2px solid #d9b680;
    }

    .section-title {
      color: #a97c50;
      margin-bottom: 18px;
      font-size: 1.7em;
    }

    .section-content p {
      padding-bottom: 5px;
      margin: 0;
    }

    .section-content {
      font-family: 'Poppins', Arial, sans-serif;
      white-space: pre-line;
      line-height: 1;
      color: black;
      font-weight: 300;
      padding-bottom: 10px;
    }

    iframe {
      border-radius: 12px;
      width: 100%;
      height: 300px;
      border: 1.5 solid #d9b680;
      box-shadow: 0 6px 20px rgb(169 124 80 / 0.35);
    }

    .btn-add-detail {
      background-color: #a97c50;
      color: white;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-bottom: 25px;
    }

    .btn-add-detail:hover {
      background-color: #7a5f34;
    }
  </style>
</head>

<body>
  <div class="container-detail">

    <div class="trip-header">
      <img id="tripGambar" src="<?= $trip['gambar'] ? '../' . htmlspecialchars($trip['gambar']) : 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80' ?>" alt="" class="trip-image" />
      <div class="trip-info">
        <h1 id="tripJudul"><?= htmlspecialchars($trip['nama_gunung']) ?></h1>
        <div id="tripStatus" class="badge-status <?= $trip['status'] !== 'available' ? 'sold' : '' ?>">
          <?= htmlspecialchars($trip['status']) ?>
        </div>
        <div class="trip-meta">
          <span><i class="bi bi-calendar-event"></i> <span id="tripTanggal"><?= htmlspecialchars($trip['tanggal']) ?></span></span>
          <span><i class="bi bi-clock"></i> <span id="tripDurasi"><?= htmlspecialchars($trip['durasi']) ?></span></span>
          <span><i class="bi bi-people-fill"></i> Slot: <span id="tripSlot"><?= htmlspecialchars($trip['slot']) ?></span></span>
          <span><i class="bi bi-flag"></i> <span id="tripJenis"><?= htmlspecialchars($trip['jenis_trip']) ?></span></span>
          <span><i class="bi bi-signpost-2"></i> Via <span id="tripVia"><?= htmlspecialchars($trip['via_gunung']) ?></span></span>
        </div>
        <div class="trip-price" id="tripHarga">
          Rp <?= isset($trip['harga']) && is_numeric($trip['harga']) ? number_format($trip['harga']) : '0' ?>
        </div>
      </div>
    </div>

    <button class="btn-add-detail" id="btnTambahDetailTrip"><i class="bi bi-plus-circle"></i> Tambah/Edit Detail Trip</button>

    <section class="info-box">
      <h2 class="section-title">Meeting Point</h2>
      <div class="section-content">
        <p><strong>Lokasi :</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
        <p><strong>Alamat :</strong> <?= nl2br(htmlspecialchars($detail['alamat'])) ?></p>
        <p><strong>Waktu Kumpul :</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
      </div>
    </section>
    <section class="info-box">
      <h2 class="section-title">Include</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['include'])) ?></div>
    </section>
    <section class="info-box">
      <h2 class="section-title">Exclude</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['exclude'])) ?></div>
    </section>
    <section class="info-box">
      <h2 class="section-title">Syarat & Ketentuan</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['syaratKetentuan'])) ?></div>
    </section>

    <section class="info-box">
      <h2 class="section-title">Lokasi Meeting Point di Google Map</h2>
      <?php
      $linkMap = trim($detail['link_map']);

      if (!$linkMap) {
        echo '<p><em>Belum ada link Google Map Meeting Point</em></p>';
      }
      // Embed langsung jika sudah embed link
      elseif (strpos($linkMap, '/maps/embed?') !== false) {
        echo '<iframe src="' . htmlspecialchars($linkMap) . '" allowfullscreen loading="lazy"></iframe>';
      }
      // Auto-convert share url ke embed
      elseif (preg_match('#^https:\/\/(www\.)?google\.(com|co\.id)\/maps/#', $linkMap)) {
        $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
        echo '<iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
      }
      // Jika shortlink Google Maps
      elseif (preg_match('#^https:\/\/maps\.app\.goo\.gl\/[A-Za-z0-9]+#', $linkMap)) {
        echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" style="color:#3779e1;font-weight:600;">Buka peta di Google Maps</a></p>';
        echo '<small style="color:#888;">Preview otomatis hanya untuk link <b>maps.google.com</b> atau <b>maps.google.co.id</b>.</small>';
      }
      // Format lain (fallback: hanya tampil link)
      else {
        echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" style="color:#3779e1;font-weight:600;">Buka peta di Google Maps</a></p>';
        echo '<small style="color:#888;">Preview embed otomatis hanya untuk link <b>maps.google.com</b> atau <b>maps.google.co.id</b>.</small>';
      }
      ?>
    </section>

  </div>

  <!-- Modal Form Tambah/Edit Detail Trip -->
  <div class="modal fade" id="detailTripFormModal" tabindex="-1" aria-labelledby="detailTripFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formDetailTrip" method="POST" action="#">
        <div class="modal-header">
          <h5 class="modal-title" id="detailTripFormModalLabel">Tambah/Edit Detail Trip</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_trip" value="<?= htmlspecialchars($id) ?>" />
          <div class="mb-3">
            <label for="nama_lokasi" class="form-label">Nama Meeting Point</label>
            <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" required value="<?= htmlspecialchars($detail['nama_lokasi']) ?>" />
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat Meeting Point</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($detail['alamat']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="waktu_kumpul" class="form-label">Waktu Kumpul</label>
            <input
              type="time"
              class="form-control"
              id="waktu_kumpul"
              name="waktu_kumpul"
              required
              value="<?= htmlspecialchars($detail['waktu_kumpul']) ?>" />
          </div>

          <div class="mb-3">
            <label for="link_map" class="form-label">Link Google Map Meeting Point</label>
            <input type="text" class="form-control" id="link_map" name="link_map" value="<?= htmlspecialchars($detail['link_map']) ?>" placeholder="https://maps.google.com/..." />
          </div>

          <div class="mb-3">
            <label for="include" class="form-label">Include</label>
            <textarea class="form-control" id="include" name="include" rows="3" required><?= htmlspecialchars($detail['include']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="exclude" class="form-label">Exclude</label>
            <textarea class="form-control" id="exclude" name="exclude" rows="3" required><?= htmlspecialchars($detail['exclude']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="syaratKetentuan" class="form-label">Syarat & Ketentuan</label>
            <textarea class="form-control" id="syaratKetentuan" name="syaratKetentuan" rows="4" required><?= htmlspecialchars($detail['syaratKetentuan']) ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan Detail</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('btnTambahDetailTrip').addEventListener('click', function() {
      const modal = new bootstrap.Modal(document.getElementById('detailTripFormModal'));
      modal.show();
    });
  </script>
  <script src="../frontend/trip-detail.js"></script>
  <script>
    const idTrip = '<?= htmlspecialchars($id) ?>';
    window.onload = function() {
      loadTripDetail(idTrip);
    };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>