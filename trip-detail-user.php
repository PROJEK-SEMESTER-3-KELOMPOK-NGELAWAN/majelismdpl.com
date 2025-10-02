<?php
// Debug untuk cek apakah file diakses
echo "<!-- File trip-detail-user.php berhasil diakses -->";

require_once 'backend/koneksi.php';

$id = $_GET['id'] ?? null;

// Debug ID
if (!$id) {
    echo "<!-- ID tidak ditemukan, redirect ke index -->";
    header("Location: index.php");
    exit();
}

echo "<!-- ID diterima: $id -->";

// Ambil data tabel paket_trips
$trip = [];
$stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) {
    echo "<!-- Trip tidak ditemukan di database -->";
    header("Location: index.php");
    exit();
}

echo "<!-- Trip ditemukan: " . $trip['nama_gunung'] . " -->";

// Ambil data detail_trips
$detail = [];
$stmtDetail = $conn->prepare("SELECT * FROM detail_trips WHERE id_trip = ?");
$stmtDetail->bind_param("i", $id);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();
$detail = $resultDetail->fetch_assoc();
$stmtDetail->close();

if (!$detail) {
  $detail = [
    'nama_lokasi' => 'Belum ditentukan',
    'alamat' => 'Belum ditentukan',
    'waktu_kumpul' => 'Belum ditentukan',
    'link_map' => '',
    'include' => 'Informasi akan diupdate segera',
    'exclude' => 'Informasi akan diupdate segera',
    'syaratKetentuan' => 'Informasi akan diupdate segera'
  ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($trip['nama_gunung']) ?> | Majelis MDPL</title>
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

    h1, h2 {
      color: #a97c50;
      font-weight: 700;
      letter-spacing: 1.2px;
    }

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
      line-height: 1.6;
      color: black;
      font-weight: 400;
      padding-bottom: 10px;
    }

    iframe {
      border-radius: 12px;
      width: 100%;
      height: 300px;
      border: 1.5px solid #d9b680;
      box-shadow: 0 6px 20px rgb(169 124 80 / 0.35);
    }

    .btn-back {
      background-color: #a97c50;
      color: white;
      border-radius: 8px;
      font-weight: 600;
      padding: 12px 24px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
      transition: background-color 0.3s ease;
    }

    .btn-back:hover {
      background-color: #7a5f34;
      color: white;
      text-decoration: none;
    }

    .btn-book {
      background-color: #2ea564;
      color: white;
      border-radius: 12px;
      font-weight: 600;
      padding: 15px 30px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 1.1rem;
      transition: background-color 0.3s ease;
      margin-left: 15px;
    }

    .btn-book:hover {
      background-color: #258a52;
      color: white;
      text-decoration: none;
    }

    .btn-book:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    .action-buttons {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .trip-header {
        flex-direction: column;
        text-align: center;
      }
      
      .trip-meta {
        justify-content: center;
      }
      
      .action-buttons {
        flex-direction: column;
        align-items: stretch;
      }
      
      .btn-book {
        margin-left: 0;
        justify-content: center;
      }
    }
  </style>
</head>

<body>
  <div class="container-detail">
    <div class="trip-header">
      <?php 
      $imagePath = 'img/default-mountain.jpg'; // Default image
      if (!empty($trip['gambar'])) {
          if (strpos($trip['gambar'], 'img/') === 0) {
              $imagePath = $trip['gambar'];
          } else {
              $imagePath = 'img/' . $trip['gambar'];
          }
      }
      ?>
      <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($trip['nama_gunung']) ?>" class="trip-image" />
      <div class="trip-info">
        <h1><?= htmlspecialchars($trip['nama_gunung']) ?></h1>
        <div class="badge-status <?= $trip['status'] !== 'available' ? 'sold' : '' ?>">
          <?= $trip['status'] === 'sold' ? 'SOLD OUT' : 'TERSEDIA' ?>
        </div>
        <div class="trip-meta">
          <span><i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($trip['tanggal'])) ?></span>
          <span><i class="bi bi-clock"></i> <?= htmlspecialchars($trip['durasi']) ?></span>
          <span><i class="bi bi-people-fill"></i> Slot: <?= htmlspecialchars($trip['slot']) ?></span>
          <span><i class="bi bi-flag"></i> <?= ucfirst(htmlspecialchars($trip['jenis_trip'])) ?></span>
          <span><i class="bi bi-signpost-2"></i> Via <?= htmlspecialchars($trip['via_gunung']) ?></span>
        </div>
        <div class="trip-extra">
          <div class="price">Rp <?= number_format($trip['harga'], 0, ',', '.') ?></div>
          <div class="rating">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-half"></i>
            <span>(4.8)</span>
          </div>
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <a href="index.php" class="btn-back">
        <i class="bi bi-arrow-left-circle"></i> Kembali
      </a>
      
      <?php if ($trip['status'] === 'available'): ?>
        <a href="#" class="btn-book" onclick="bookTrip(<?= $trip['id_trip'] ?>)">
          <i class="bi bi-calendar-check"></i> Booking Sekarang
        </a>
      <?php else: ?>
        <button class="btn-book" disabled>
          <i class="bi bi-x-circle"></i> Sold Out
        </button>
      <?php endif; ?>
    </div>

    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-geo-alt-fill"></i> Meeting Point</h2>
      <div class="section-content">
        <p><strong>Lokasi:</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
        <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($detail['alamat'])) ?></p>
        <p><strong>Waktu Kumpul:</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
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

    <?php if (!empty($detail['link_map'])): ?>
    <section class="info-box">
      <h2 class="section-title"><i class="bi bi-map"></i> Lokasi Meeting Point</h2>
      <?php
      $linkMap = trim($detail['link_map']);
      if (strpos($linkMap, '/maps/embed?') !== false) {
        echo '<iframe src="' . htmlspecialchars($linkMap) . '" allowfullscreen loading="lazy"></iframe>';
      } elseif (preg_match('#^https:\/\/(www\.)?google\.(com|co\.id)\/maps/#', $linkMap)) {
        $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
        echo '<iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
      } else {
        echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" class="text-primary fw-bold">Buka di Google Maps</a></p>';
      }
      ?>
    </section>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    function bookTrip(tripId) {
      Swal.fire({
        title: 'Booking Trip',
        text: 'Fitur booking akan segera tersedia!',
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#a97c50'
      });
    }
  </script>
</body>
</html>
