<?php
require_once 'auth_check.php';

$id = $_GET['id'] ?? null;

// Contoh ambil data trip dan detail dari database menggunakan $id
// Contoh data statis sebagai ilustrasi (ganti dengan query dari DB)
$trip = [
    'nama_gunung' => 'Gunung Merapi',
    'tanggal' => '2025-10-01',
    'slot' => 10,
    'durasi' => '2 Hari 1 Malam',
    'jenis_trip' => 'Camp',
    'harga' => 1500000,
    'via_gunung' => 'Selo',
    'status' => 'available',
    'gambar' => 'images/merapi.jpg'
];

// Data detail trip meeting point (ganti dengan query dari DB sesuai $id)
$detail = [
    'nama_lokasi_meeting_point' => 'Basecamp Selo',
    'alamat_meeting_point' => 'Jl. Raya Selo, Boyolali',
    'waktu_kumpul' => '07.00 WIB di Basecamp',
    'include' => "- Transportasi\n- Makan 3x\n- Guide Profesional",
    'exclude' => "- Perlengkapan pribadi\n- Asuransi\n- Biaya pribadi",
    'syarat_ketentuan' => "Tidak menerima peserta dengan penyakit kronis\nPatuhi instruksi guide",
    'link_gmap_meeting_point' => 'https://www.google.com/maps/place/Basecamp+Selo/@-7.541,110.591'
];
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
    h1, h2 {
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
    /* Kotak lembut tanpa garis untuk setiap section */
    .info-box {
      background: white;
      border-radius: 18px;
      box-shadow: 0 2px 12px rgba(30,30,50,0.05);
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
      <img src="<?= htmlspecialchars($trip['gambar']) ?>" alt="<?= htmlspecialchars($trip['nama_gunung']) ?>" class="trip-image" />
      <div class="trip-info">
        <h1><?= htmlspecialchars($trip['nama_gunung']) ?></h1>
        <div class="badge-status <?= $trip['status'] === 'available' ? 'available' : 'sold' ?>">
          <?= ucfirst($trip['status']) ?>
        </div>
        <div class="trip-meta">
          <span><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($trip['tanggal']) ?></span>
          <span><i class="bi bi-clock"></i> <?= htmlspecialchars($trip['durasi']) ?></span>
          <span><i class="bi bi-people-fill"></i> Slot: <?= intval($trip['slot']) ?></span>
          <span><i class="bi bi-flag"></i> <?= htmlspecialchars($trip['jenis_trip']) ?></span>
          <span><i class="bi bi-signpost-2"></i> Via <?= htmlspecialchars($trip['via_gunung']) ?></span>
        </div>
        <div class="trip-price">Rp <?= number_format($trip['harga'], 0, ',', '.') ?></div>
      </div>
    </div>

    <button class="btn-add-detail" id="btnTambahDetailTrip"><i class="bi bi-plus-circle"></i> Tambah/Edit Detail Trip</button>

    <section class="info-box">
      <h2 class="section-title">Meeting Point</h2>
      <div class="section-content">
        <p><strong>Lokasi :</strong> <?= htmlspecialchars($detail['nama_lokasi_meeting_point']) ?></p>
        <p><strong>Alamat :</strong> <?= nl2br(htmlspecialchars($detail['alamat_meeting_point'])) ?></p>
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
      <div class="section-content"><?= nl2br(htmlspecialchars($detail['syarat_ketentuan'])) ?></div>
    </section>

    <section class="info-box">
      <h2 class="section-title">Lokasi Meeting Point di Google Map</h2>
      <?php if (!empty($detail['link_gmap_meeting_point'])): ?>
        <iframe src="<?= str_replace('/maps/', '/maps/embed/', htmlspecialchars($detail['link_gmap_meeting_point'])) ?>" allowfullscreen loading="lazy"></iframe>
      <?php else: ?>
        <p><em>Belum ada link Google Map Meeting Point</em></p>
      <?php endif; ?>
    </section>
  </div>

  <!-- Modal Form Tambah/Edit Detail Trip -->
  <div class="modal fade" id="detailTripFormModal" tabindex="-1" aria-labelledby="detailTripFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formDetailTrip" method="POST" action="save_detailtrip.php">
        <div class="modal-header">
          <h5 class="modal-title" id="detailTripFormModalLabel">Tambah/Edit Detail Trip</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_trip" value="<?= htmlspecialchars($id) ?>" />
          <div class="mb-3">
            <label for="nama_lokasi_meeting_point" class="form-label">Nama Meeting Point</label>
            <input type="text" class="form-control" id="nama_lokasi_meeting_point" name="nama_lokasi_meeting_point" required value="<?= htmlspecialchars($detail['nama_lokasi_meeting_point']) ?>" />
          </div>
          <div class="mb-3">
            <label for="alamat_meeting_point" class="form-label">Alamat Meeting Point</label>
            <textarea class="form-control" id="alamat_meeting_point" name="alamat_meeting_point" rows="3" required><?= htmlspecialchars($detail['alamat_meeting_point']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="waktu_kumpul" class="form-label">Waktu Kumpul</label>
            <input type="text" class="form-control" id="waktu_kumpul" name="waktu_kumpul" required value="<?= htmlspecialchars($detail['waktu_kumpul']) ?>" />
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
            <label for="syarat_ketentuan" class="form-label">Syarat & Ketentuan</label>
            <textarea class="form-control" id="syarat_ketentuan" name="syarat_ketentuan" rows="4" required><?= htmlspecialchars($detail['syarat_ketentuan']) ?></textarea>
          </div>
          <div class="mb-3">
            <label for="link_gmap_meeting_point" class="form-label">Link Google Map Meeting Point</label>
            <input type="url" class="form-control" id="link_gmap_meeting_point" name="link_gmap_meeting_point" value="<?= htmlspecialchars($detail['link_gmap_meeting_point']) ?>" placeholder="https://maps.google.com/..." />
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
</body>
</html>
