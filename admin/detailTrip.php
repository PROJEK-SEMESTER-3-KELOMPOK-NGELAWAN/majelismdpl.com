<?php
require_once 'auth_check.php';
require_once '../backend/koneksi.php';

$id = $_GET['id'] ?? null;

// Ambil data tabel paket_trips
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

// Ambil data detail_trips
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
      max-width: 1100px;
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

    /* Header opsi pertama */

    .trip-header {
      display: flex;
      align-items: center;
      gap: 25px;
      background: rgba(255, 255, 255, 0.85);
      padding: 15px 20px;
      border-radius: 14px;
      margin-bottom: 30px;
      box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
    }

    .trip-image {
      width: 140px;
      height: 140px;
      border-radius: 12px;
      object-fit: cover;
      box-shadow: 0 4px 14px rgba(169, 124, 80, 0.4);
    }

    .trip-info {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .trip-info h1 {
      font-weight: 800;
      font-size: 1.9rem;
      margin-bottom: 6px;
      color: #a97c50;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .badge-status {
      font-size: 0.9rem;
      padding: 6px 16px;
      border-radius: 14px;
      background-color: #63c494;
      color: white;
      font-weight: 700;
      width: auto;
      max-width: max-content;
      white-space: nowrap;
      display: inline-block;
      margin-bottom: 12px;
      text-transform: uppercase;
    }

    .badge-status.sold {
      background-color: #d48d9a;
    }

    .trip-meta {
      font-size: 0.95rem;
      color: #695a3a;
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
      font-weight: 600;
    }

    .trip-meta span {
      display: flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
    }

    /* Harga dan rating */
    .trip-extra {
      display: flex;
      align-items: center;
      gap: 40px;
      margin-top: 12px;
      font-weight: 700;
      color: #a97c50;
    }

    .trip-extra .price {
      font-size: 1.4rem;
      font-weight: 900;
      color: #2ea564;
    }

    .trip-extra .rating {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #a97c50;
      font-size: 1rem;
    }

    .trip-extra .rating i {
      color: #ffd700;
      font-size: 1.2rem;
    }

    .info-box {
      background: white;
      border-radius: 18px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      padding: 25px 25px 18px 25px;
      margin-bottom: 30px;
      color: black;
      border: none;
    }

    .section-title {
      color: #a97c50;
      margin-bottom: 18px;
      font-size: 1.7em;
    }

    /* Icon di judul section */
    .section-title i {
      margin-right: 8px;
      font-size: 1em;
      vertical-align: middle;
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

    /* Tombol kembali */
    .btn-back {
      background-color: #d9d9d9;
      color: #444;
      border-radius: 8px;
      font-weight: 600;
      padding: 10px 24px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
      transition: background-color 0.3s ease;
    }

    .btn-back:hover {
      background-color: #a97c50;
      color: white;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div class="container-detail">

    <div class="trip-header">
      <img id="tripGambar" src="<?= $trip['gambar'] ? '../' . htmlspecialchars($trip['gambar']) : 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80' ?>" alt="Foto Gunung" class="trip-image" />
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
        <div class="trip-extra">
          <div class="price">Rp <?= isset($trip['harga']) && is_numeric($trip['harga']) ? number_format($trip['harga'], 0, ',', '.') : '0' ?></div>
          <div class="rating" title="Rating Trip">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-half"></i>
            <i class="bi bi-star"></i>
            <span>(4.5)</span>
          </div>
        </div>
      </div>
    </div>

    <button class="btn-add-detail" id="btnTambahDetailTrip"><i class="bi bi-plus-circle"></i> Tambah/Edit Detail Trip</button>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-geo-alt-fill"></i> Meeting Point</h2>
      <div class="section-content">
        <p><strong>Lokasi :</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
        <p><strong>Alamat :</strong> <?= nl2br(htmlspecialchars($detail['alamat'])) ?></p>
        <p><strong>Waktu Kumpul :</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
      </div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-check2-square"></i> Include</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['include'])) ?></div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-x-square"></i> Exclude</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['exclude'])) ?></div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-file-text"></i> Syarat & Ketentuan</h2>
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['syaratKetentuan'])) ?></div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-map"></i> Lokasi Meeting Point di Google Map</h2>
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

    <div class="d-flex justify-content-start mb-4">
      <a href="trip.php" class="btn-back">
        <i class="bi bi-arrow-left-circle"></i> Kembali ke Menu Trip
      </a>
    </div>

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
