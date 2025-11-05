<?php
require_once 'auth_check.php';
require_once '../config.php';
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
    'nama_gunung' => 'Trip Tidak Ditemukan',
    'tanggal' => '-',
    'slot' => '-',
    'durasi' => '-',
    'harga' => '0',
    'jenis_trip' => '-',
    'via_gunung' => '-',
    'status' => 'error',
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
    'nama_lokasi' => 'Belum Diatur',
    'alamat' => 'Belum Diatur',
    'waktu_kumpul' => '00:00',
    'link_map' => '',
    'include' => 'Belum ada data Include. Klik "Tambah/Edit Detail Trip" untuk mengisi.',
    'exclude' => 'Belum ada data Exclude. Klik "Tambah/Edit Detail Trip" untuk mengisi.',
    'syaratKetentuan' => 'Belum ada Syarat & Ketentuan. Klik "Tambah/Edit Detail Trip" untuk mengisi.',
  ];
}

// Fungsi untuk memformat teks list dengan ikon
function formatListContent($text, $iconClass = 'bi-check-circle-fill')
{
  $items = explode("\n", trim($text));
  $html = '<ul class="list-unstyled list-styled">';

  if (count($items) == 1 && (strpos($text, 'Belum ada data') !== false || strpos($text, 'Belum ada Syarat') !== false)) {
    return '<p class="text-muted fst-italic mt-2">' . nl2br(htmlspecialchars($text)) . '</p>';
  }

  foreach ($items as $item) {
    $item = trim($item);
    if (!empty($item)) {
      $cleanedItem = ltrim($item, "•- \t");
      $html .= '<li><i class="bi ' . $iconClass . ' me-2"></i>' . htmlspecialchars($cleanedItem) . '</li>';
    }
  }
  $html .= '</ul>';
  return $html;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Trip: <?= htmlspecialchars($trip['nama_gunung']) ?> | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <style>
    :root {
      --primary-color: #a97c50;
      --secondary-color: #695a3a;
      --background-color: #f6f0e8;
      --success-color: #2ea564;
      --danger-color: #d48d9a;
      --light-brown-bg: #f6f0e8;
    }

    body {
      background: var(--light-brown-bg);
      font-family: "Poppins", Arial, sans-serif;
      color: #232323;
      min-height: 100vh;
      padding: 30px 20px;
    }

    .btn-primary,
    .btn-success {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b6332 100%) !important;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 500;
      transition: all 0.3s ease;
      color: white !important;
    }

    .btn-primary:hover,
    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
      background: linear-gradient(135deg, #8b6332 0%, var(--primary-color) 100%) !important;
    }

    .modal-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b6332 100%);
      color: white;
      border: none;
      border-radius: 0.7rem 0.7rem 0 0;
      padding: 20px 25px;
    }

    .modal-title {
      color: white !important;
      font-weight: 600;
    }

    .modal-body .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
      outline: none;
    }

    .container-detail {
      max-width: 1100px;
      margin: auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgb(0 0 0 / 0.1);
      padding: 40px 50px;
    }

    .trip-header {
      display: flex;
      align-items: flex-start;
      gap: 30px;
      padding: 25px;
      border-radius: 16px;
      margin-bottom: 40px;
      background: rgba(169, 124, 80, 0.05);
      border: 1px solid rgba(169, 124, 80, 0.1);
    }

    .trip-image {
      width: 300px;
      height: 250px;
      min-width: 160px;
      border-radius: 16px;
      object-fit: cover;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
      border: 4px solid #fff;
    }

    .trip-info h1 {
      font-weight: 800;
      font-size: 2.2rem;
      margin-bottom: 8px;
      color: var(--primary-color);
      line-height: 1.2;
    }

    .badge-status {
      font-size: 0.9rem;
      padding: 8px 18px;
      border-radius: 20px;
      background-color: var(--success-color);
      color: white;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 5px;
      display: inline-block;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .badge-status.sold {
      background-color: var(--danger-color);
    }

    .trip-meta {
      font-size: 1rem;
      color: var(--secondary-color);
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      margin-top: 15px;
      font-weight: 600;
    }

    .trip-meta i {
      color: var(--primary-color);
      font-size: 1.1rem;
    }

    .trip-extra .price {
      font-size: 1.6rem;
      font-weight: 900;
      color: var(--success-color);
      margin-top: 15px;
    }

    .btn-add-detail {
      font-weight: 600;
      font-size: 15px;
      padding: 14px 28px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 6px 15px rgba(169, 124, 80, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }

    .btn-back {
      background-color: #e0e0e0;
      color: #444;
      border-radius: 10px;
      font-weight: 600;
      padding: 12px 28px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-back:hover {
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
    }

    .info-box {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
      padding: 30px;
      margin-bottom: 35px;
      border-left: 5px solid var(--primary-color);
    }

    .section-title {
      color: var(--secondary-color);
      margin-bottom: 20px;
      font-size: 1.8em;
      font-weight: 700;
      padding-bottom: 10px;
      border-bottom: 2px dashed #f0f0f0;
    }

    .section-title i {
      margin-right: 10px;
      font-size: 1em;
      color: var(--primary-color);
    }

    .section-content {
      font-family: 'Poppins', Arial, sans-serif;
      line-height: 1.7;
      color: #494949;
      font-weight: 400;
    }

    .list-styled {
      padding-left: 0;
      margin-top: 15px;
    }

    .list-styled li {
      margin-bottom: 12px;
      font-size: 1.05rem;
      color: #494949;
    }

    .list-styled .bi-check-circle-fill {
      color: var(--success-color);
      font-size: 1.1rem;
    }

    .list-styled .bi-x-octagon-fill {
      color: var(--danger-color);
      font-size: 1.1rem;
    }

    .list-styled .bi-bookmark-check-fill {
      color: var(--primary-color);
      font-size: 1.1rem;
    }

    iframe {
      border-radius: 12px;
      width: 100%;
      height: 350px;
      border: 2px solid rgba(169, 124, 80, 0.3);
      box-shadow: 0 8px 25px rgb(169 124 80 / 0.2);
      margin-top: 15px;
    }
  </style>
</head>

<body>
  <div class="container-detail">
    <div class="trip-header">
      <img id="tripGambar"
        src="<?= $trip['gambar'] ? getAssetsUrl(ltrim($trip['gambar'], '/')) : 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80' ?>"
        alt="Foto Gunung" class="trip-image" />
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
          <div class="price">Harga: <strong>Rp <?= isset($trip['harga']) && is_numeric($trip['harga']) ? number_format($trip['harga'], 0, ',', '.') : '0' ?></strong></div>
        </div>
      </div>
    </div>

    <button class="btn btn-primary btn-add-detail" id="btnTambahDetailTrip"><i class="bi bi-pencil-square me-2"></i> <?= $detail['nama_lokasi'] !== 'Belum Diatur' ? 'Edit Detail Trip' : 'Tambah Detail Trip' ?></button>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-geo-alt-fill"></i> Meeting Point</h2>
      <div class="section-content">
        <p><strong>Lokasi :</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
        <p><strong>Alamat :</strong> <?= nl2br(htmlspecialchars($detail['alamat'])) ?></p>
        <p><strong>Waktu Kumpul :</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
      </div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-check2-circle"></i> Include (Sudah Termasuk)</h2>
      <div class="section-content">
        <?= formatListContent($detail['include'], 'bi-check-circle-fill') ?>
      </div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-x-octagon"></i> Exclude (Tidak Termasuk)</h2>
      <div class="section-content">
        <?= formatListContent($detail['exclude'], 'bi-x-octagon-fill') ?>
      </div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-file-earmark-check"></i> Syarat & Ketentuan</h2>
      <div class="section-content">
        <?= formatListContent($detail['syaratKetentuan'], 'bi-bookmark-check-fill') ?>
      </div>
    </section>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-map"></i> Lokasi Meeting Point di Google Map</h2>
      <?php
      $linkMap = trim($detail['link_map']);

      if (!$linkMap || $linkMap === 'Belum Diatur') {
        echo '<p class="text-muted fst-italic"><em>Link Google Map Meeting Point belum diatur.</em></p>';
      } elseif (strpos($linkMap, '/maps/embed?') !== false) {
        echo '<iframe src="' . htmlspecialchars($linkMap) . '" allowfullscreen loading="lazy"></iframe>';
      } elseif (preg_match('#^https:\/\/(www\.)?google\.(com|co\.id)\/maps/#', $linkMap)) {
        if (strpos($linkMap, 'maps.app.goo.gl') !== false) {
          echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" class="btn btn-outline-primary mt-3"><i class="bi bi-box-arrow-up-right"></i> Buka Peta di Google Maps</a></p>';
          echo '<small class="text-muted">Gunakan link embed untuk preview otomatis.</small>';
        } else {
          $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
          echo '<iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
        }
      } else {
        echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" class="btn btn-outline-primary mt-3"><i class="bi bi-box-arrow-up-right"></i> Buka Peta di Google Maps</a></p>';
        echo '<small class="text-muted">Gunakan link embed dari opsi "Share > Embed map" di Google Maps untuk menampilkan preview di sini.</small>';
      }
      ?>
    </section>

    <div class="d-flex justify-content-start mt-4">
      <a href="<?php echo getPageUrl('admin/trip.php'); ?>" class="btn-back">
        <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Trip
      </a>
    </div>

  </div>

  <div class="modal fade" id="detailTripFormModal" tabindex="-1" aria-labelledby="detailTripFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form class="modal-content" id="formDetailTrip">
        <div class="modal-header">
          <h5 class="modal-title" id="detailTripFormModalLabel">
            <i class="bi bi-gear me-2"></i>
            <span id="detailModalTitleText">Tambah/Edit Detail Trip</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_trip" value="<?= htmlspecialchars($id) ?>" />
          <input type="hidden" name="action" id="detailActionType" value="<?= $detail['nama_lokasi'] !== 'Belum Diatur' ? 'updateDetail' : 'createDetail' ?>" />

          <h6 class="text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i> Informasi Meeting Point</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="nama_lokasi" class="form-label">
                  <i class="bi bi-pin-map-fill"></i> Nama Meeting Point
                </label>
                <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" required
                  value="<?= $detail['nama_lokasi'] === 'Belum Diatur' ? '' : htmlspecialchars($detail['nama_lokasi']) ?>" placeholder="Cth: Stasiun Senen" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="waktu_kumpul" class="form-label">
                  <i class="bi bi-clock-fill"></i> Waktu Kumpul
                </label>
                <input type="time" class="form-control" id="waktu_kumpul" name="waktu_kumpul" required
                  value="<?= htmlspecialchars($detail['waktu_kumpul']) ?>" />
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="alamat" class="form-label">
              <i class="bi bi-house-door-fill"></i> Alamat Meeting Point
            </label>
            <textarea class="form-control" id="alamat" name="alamat" rows="2" required
              placeholder="Cth: Jl. Senen Raya No.3, Jakarta Pusat"><?= $detail['alamat'] === 'Belum Diatur' ? '' : htmlspecialchars($detail['alamat']) ?></textarea>
          </div>

          <div class="mb-4">
            <label for="link_map" class="form-label">
              <i class="bi bi-link-45deg"></i> Link Google Map Meeting Point
            </label>
            <input type="url" class="form-control" id="link_map" name="link_map"
              value="<?= htmlspecialchars($detail['link_map']) ?>"
              placeholder="Link Google Maps (Bisa Share Link atau Embed Link)" />
            <small class="form-text text-muted">Akan ditampilkan sebagai link atau embed map (gunakan link embed untuk preview).</small>
          </div>

          <hr class="my-4">

          <h6 class="text-secondary mb-3"><i class="bi bi-list-check me-1"></i> Detail Layanan & Ketentuan</h6>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="include" class="form-label">
                  <i class="bi bi-check-circle-fill"></i> Include (Pisahkan dengan baris baru)
                </label>
                <textarea class="form-control" id="include" name="include" rows="4" required
                  placeholder="Transportasi\nLogistik tenda dan alat masak\nPorter Tim"><?= $detail['include'] === 'Belum ada data Include. Klik "Tambah/Edit Detail Trip" untuk mengisi.' ? '' : htmlspecialchars($detail['include']) ?></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="exclude" class="form-label">
                  <i class="bi bi-x-octagon-fill"></i> Exclude (Pisahkan dengan baris baru)
                </label>
                <textarea class="form-control" id="exclude" name="exclude" rows="4" required
                  placeholder="Tiket pesawat/kereta\nLogistik pribadi (makanan/minuman)\nPengeluaran pribadi"><?= $detail['exclude'] === 'Belum ada data Exclude. Klik "Tambah/Edit Detail Trip" untuk mengisi.' ? '' : htmlspecialchars($detail['exclude']) ?></textarea>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="syaratKetentuan" class="form-label">
              <i class="bi bi-file-earmark-check"></i> Syarat & Ketentuan
            </label>
            <textarea class="form-control" id="syaratKetentuan" name="syaratKetentuan" rows="5" required
              placeholder="Tuliskan syarat dan ketentuan, pisahkan dengan baris baru."><?= $detail['syaratKetentuan'] === 'Belum ada Syarat & Ketentuan. Klik "Tambah/Edit Detail Trip" untuk mengisi.' ? '' : htmlspecialchars($detail['syaratKetentuan']) ?></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Tutup
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-1"></i> Simpan Detail
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>
  <script src="<?php echo getAssetsUrl('frontend/trip-detail.js'); ?>"></script>
  <script>
    const idTrip = '<?= htmlspecialchars($id) ?>';

    document.getElementById('btnTambahDetailTrip').addEventListener('click', function() {
      const actionType = document.getElementById('detailActionType').value;
      const modalTitle = document.getElementById('detailModalTitleText');
      modalTitle.textContent = actionType === 'updateDetail' ? 'Edit Detail Trip' : 'Tambah Detail Trip Baru';

      const modal = new bootstrap.Modal(document.getElementById('detailTripFormModal'));
      modal.show();
    });

    document.getElementById('formDetailTrip').addEventListener('submit', function(event) {
      event.preventDefault();
      if (typeof submitDetailTrip === 'function') {
        submitDetailTrip(event.target);
      } else {
        console.error('Fungsi submitDetailTrip belum didefinisikan di trip-detail.js');
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      const detailActionType = document.getElementById('detailActionType').value;
      const btnTambahDetailTrip = document.getElementById('btnTambahDetailTrip');
      if (detailActionType === 'updateDetail') {
        btnTambahDetailTrip.innerHTML = '<i class="bi bi-pencil-square me-2"></i> Edit Detail Trip';
      } else {
        btnTambahDetailTrip.innerHTML = '<i class="bi bi-plus-circle me-2"></i> Tambah Detail Trip';
      }
    });
  </script>

</body>

</html>