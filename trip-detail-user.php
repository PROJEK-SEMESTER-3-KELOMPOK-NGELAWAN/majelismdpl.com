<?php
// file: detail_trip.php
require_once 'backend/koneksi.php';

// Pastikan koneksi sukses
if (!isset($conn)) {
    die("Koneksi database gagal dimuat.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

// 1. Ambil data trip utama
$stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) {
    header("Location: index.php");
    exit();
}

// 2. Ambil data detail trip
$stmtDetail = $conn->prepare("SELECT * FROM detail_trips WHERE id_trip = ?");
$stmtDetail->bind_param("i", $id);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();
$detail = $resultDetail->fetch_assoc();
$stmtDetail->close();
// $conn->close(); 

// 3. Data default jika detail tidak ditemukan
if (!$detail) {
  $detail = [
    'nama_lokasi' => 'Belum ditentukan',
    'alamat' => 'Belum ditentukan',
    'waktu_kumpul' => 'Belum ditentukan',
    'link_map' => '',
    'include' => "Informasi akan diupdate segera",
    'exclude' => "Informasi akan diupdate segera",
    'syaratKetentuan' => "Informasi akan diupdate segera"
  ];
}

/**
 * Fungsi untuk mengkonversi teks per baris menjadi daftar berikon.
 * @param string $text Teks dari database.
 * @param string $iconClass Kelas ikon Bootstrap.
 * @return string HTML daftar (<ul>) atau paragraf jika hanya satu baris.
 */
function createIconList($text, $iconClass) {
    // Bersihkan spasi kosong yang berlebihan dan pecah berdasarkan baris baru
    $items = array_filter(array_map('trim', explode("\n", $text)));

    // Jika hanya ada satu baris atau teks kosong, kembalikan sebagai paragraf biasa
    if (count($items) <= 1 && empty($items[0])) {
        return '<p>' . nl2br(htmlspecialchars($text)) . '</p>';
    }
    
    // Jika ada banyak baris, buat daftar
    $html = '<ul class="icon-list">';
    foreach ($items as $item) {
        if (!empty($item)) {
            $html .= '<li><i class="' . htmlspecialchars($iconClass) . '"></i> ' . htmlspecialchars($item) . '</li>';
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
  <title><?=htmlspecialchars($trip['nama_gunung'])?> | Majelis MDPL</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,700;1,400&family=Merriweather:wght@700;900&family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    /* Global & Container */
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f9f7f1;
      color: #4b4439;
      overflow-x: hidden; /* Mencegah scroll horizontal */
    }
    .container {
      /* Hanya untuk konten di bawah hero */
      max-width: 1200px; 
      width: 90%; 
      margin: 60px auto; /* Margin setelah hero */
      padding: 0; 
    }
    
    /* === Hero Section (Full Viewport Height & Left Aligned) === */
    .hero {
      position: relative;
      height: 100vh; /* Tinggi penuh layar */
      width: 100vw; /* Lebar penuh layar */
      margin-bottom: 0; /* Hapus margin bawah */
      border-radius: 0; 
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
      display: flex; 
      align-items: center; /* Pusatkan teks secara vertikal */
      justify-content: flex-start; /* Teks di sebelah kiri */
      text-align: left;
    }
    .hero img {
      position: absolute; 
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.55); /* Lebih gelap agar teks putih kontras */
      z-index: 1;
    }
    .hero-overlay {
      position: absolute;
      inset: 0;
      /* Gradient dari kiri ke kanan agar teks kiri lebih menonjol */
      background: linear-gradient(90deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.1) 100%);
      z-index: 2;
    }
    .hero-content {
        position: relative;
        z-index: 3; 
        color: #fff8ec;
        max-width: 600px; /* Batasi lebar teks */
        padding-left: 3%; 
        margin-top: 25vh; /* Jarak dari atas layar */
    }
    .hero-subtitle {
        font-family: 'Lora', serif;
        font-style: italic;
        font-size: 1.4rem; /* Ukuran font lebih kecil */
        margin-bottom: 8px;
        text-shadow: 0 2px 5px rgba(0,0,0,0.8);
        line-height: 1.4;
    }
    .hero-text {
      position: static; 
      transform: none;
      font-size: 4rem; /* Ukuran font lebih kecil */
      font-weight: 900;
      letter-spacing: 0.05em;
      text-shadow: 0 3px 8px rgba(0,0,0,0.9);
      line-height: 1.1;
      font-family: 'Merriweather', serif;
      margin-bottom: 25px; /* Jarak dengan tombol */
    }
    /* Menghilangkan badge status dari hero section */
    .badge-status {
        display: none; 
    }

    /* === Tombol Daftar Sekarang (dipindah ke hero-content) === */
    .btn-hero-wrapper {
        margin-top: 30px; 
    }
    .btn-hero {
      background-color: #a97c50; 
      color: #fff8ec;
      padding: 10px 25px;
      font-weight: 700;
      font-size: 0.9rem;
      border-radius: 50px; /* Kembali ke gaya pil/rounded */
      cursor: pointer;
      transition: box-shadow 0.3s ease, background-color 0.3s ease;
      border: none;
      user-select: none;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      margin-top: 20vh;
      text-transform: uppercase;
    }
    .btn-hero:hover:not(:disabled) {
      background-color: #7a5f34;
    }
    .btn-hero:disabled {
      background-color: #bdbdbd;
      cursor: not-allowed;
      box-shadow: none;
      color: #7a7a7a;
    }
    .btn-hero i {
      font-size: 1.4rem;
    }
    
    /* Tombol Booking di bawah info bar (dihilangkan dari posisi sebelumnya) */
    .btn-book-wrapper {
      display: none; /* Menyembunyikan wrapper lama */
    }

    /* === Info bar === */
    .info-bar {
      background: #ffffffff; /* Menggunakan warna terang untuk info bar */
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-radius: 8px; /* Tambahkan sedikit radius */
      padding: 25px 30px;
      display: flex;
      justify-content: space-around; 
      color: #4b4439;
      margin-bottom: 45px;
      flex-wrap: wrap;
      gap: 20px 30px; 
      border-top: none; /* Hilangkan garis di info bar */
      border-bottom: none; 
    }
    .info-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.1rem;
      flex: 0 1 auto; 
      min-width: 180px; 
      flex-direction: column;
      text-align: center;
    }
    .info-item i {
      font-size: 2.2rem;
      color: #a97c50; 
      margin-bottom: 5px;
    }
    .info-item span:first-child {
        font-weight: 400; 
        font-size: 1rem;
        color: #6b5e3f; 
    }
    .info-item span:last-child {
        font-weight: 700; 
        color: #4b4439; 
    }

    /* === Detail sections & Icon List Styling === */
    .content-area {
        background: #fff;
        padding: 50px 70px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05); 
    }
    section.detail-section {
      background: #fff;
      padding: 40px 0;
      color: #423a29;
      line-height: 1.6;
      border-bottom: 1px solid #e0e0e0; 
    }
    section.detail-section:last-of-type {
        border-bottom: none; 
    }
    section.detail-section h2 {
      font-size: 2.3rem;
      font-weight: 700;
      margin-bottom: 30px;
      color: #4b4439; 
      border-left: 7px solid #a97c50; 
      padding-left: 18px;
      font-family: 'Merriweather', serif; 
    }
    section.detail-section p {
      font-size: 1.05rem;
      padding-left: 25px;
      /* Perbaikan: Hapus margin-bottom jika menggunakan UL */
      margin-bottom: 1.2rem; 
    }
    
    /* Styling untuk daftar berikon (UL) */
    .icon-list {
        list-style: none; /* Hilangkan bullet default */
        padding-left: 25px; /* Samakan indentasi dengan paragraf */
        margin-top: 0;
    }
    .icon-list li {
        margin-bottom: 10px;
        line-height: 1.5;
        font-size: 1.05rem;
        color: #423a29;
    }
    .icon-list li i {
        margin-right: 8px;
        font-size: 1.1rem;
    }
    
    /* Custom Icon Color/Style for List */
    .icon-list-include i {
        color: #28a745; /* Hijau untuk Include (Check) */
        font-size: 1.2rem;
    }
    .icon-list-exclude i {
        color: #dc3545; /* Merah untuk Exclude (X) */
        font-size: 1.2rem;
    }
    .icon-list-syarat i {
        color: #ffc107; /* Kuning/Orange untuk Syarat (Alert) */
        font-size: 1.2rem;
    }
    
    /* Lanjutkan CSS lainnya... */
    
    section.detail-section p strong {
      font-weight: 700;
      color: #4b4439; 
    }
    section.detail-section p a {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 15px;
        background-color: #a97c50;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    section.detail-section p a:hover {
        background-color: #7a5f34;
    }

    /* Map container */
    .map-container {
      margin-top: 40px;
      border-radius: 4px; 
      overflow: hidden;
      height: 350px; 
      box-shadow: 0 5px 15px rgb(0 0 0 / 0.15);
      border: 2px solid #d9b680; 
      border-radius:10px;
    }
    .map-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .hero-content {
            padding-left: 8%;
        }
        .hero-text {
            font-size: 3rem;
        }
        .hero-subtitle {
            font-size: 1.3rem;
        }
        .btn-hero {
            padding: 14px 40px;
            font-size: 1.1rem;
        }
        .content-area {
            padding: 40px 50px;
        }
    }
    @media (max-width: 780px) {
      .hero {
        height: 85vh; /* Sedikit dikecilkan agar terlihat ada konten di bawahnya */
        align-items: flex-end; /* Posisikan konten di bawah */
      }
      .hero-content {
        padding-left: 10%;
        padding-bottom: 10vh; /* Jarak dari bawah layar */
        max-width: 90%;
      }
      .hero-subtitle {
          font-size: 1.1rem;
          margin-bottom: 5px;
      }
      .hero-text {
        font-size: 2.2rem;
        margin-bottom: 15px;
      }
      .btn-hero {
        width: 100%;
        max-width: 300px;
        justify-content: center;
        padding: 12px 30px;
        font-size: 1rem;
      }
      
      .container {
        width: 100%;
        padding: 0 16px;
      }
      .info-bar {
        padding: 15px 0;
      }
      .content-area {
        padding: 20px;
      }
      
      .icon-list {
        padding-left: 20px;
      }
    }
  </style>
</head>
<body>
  <?php 
  include 'navbar.php'; 
  ?>

  <?php $heroSubtitle = "Jelajahi keindahan alam abadi di puncak tertinggi."; ?>
  
  <?php
    $imgPath = 'img/default-mountain.jpg';
    if (!empty($trip['gambar'])) {
      $imgPath = (strpos($trip['gambar'],'img/') === 0) ? $trip['gambar'] : 'img/'.$trip['gambar'];
    }
  ?>

  <section class="hero" aria-label="Hero Section Full Screen Trip <?=htmlspecialchars($trip['nama_gunung'])?>">
    <img src="<?=htmlspecialchars($imgPath)?>" alt="Foto Gunung <?=htmlspecialchars($trip['nama_gunung'])?>" />
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-subtitle"><?=htmlspecialchars($heroSubtitle)?></p>
        <div class="hero-text"><?=htmlspecialchars($trip['nama_gunung'])?></div>
        
        <div class="btn-hero-wrapper">
          <?php if($trip['status'] === 'available'): ?>
          <button class="btn-hero" type="button" onclick="bookTrip(<?=$trip['id_trip']?>)" aria-label="Daftar sekarang">
            <i class="bi bi-calendar-check"></i> Daftar Sekarang
          </button>
          <?php else: ?>
          <button class="btn-hero" type="button" disabled aria-disabled="true">
            <i class="bi bi-x-circle"></i> Sold Out
          </button>
          <?php endif; ?>
        </div>
    </div>
    </section>

  <div class="container" role="main" aria-label="Detail trip <?=htmlspecialchars($trip['nama_gunung'])?>">
    
    <nav class="info-bar" aria-label="Informasi ringkas trip">
      <div class="info-item" title="Tanggal Trip">
        <i class="bi bi-calendar-event"></i>
        <span>Tanggal</span>
        <span><?=date('d M Y', strtotime($trip['tanggal']))?></span>
      </div>
      <div class="info-item" title="Durasi Trip">
        <i class="bi bi-clock"></i>
        <span>Durasi</span>
        <span><?=htmlspecialchars($trip['durasi'])?></span>
      </div>
      <div class="info-item" title="Slot">
        <i class="bi bi-people-fill"></i>
        <span>Slot Tersisa</span>
        <span><?=htmlspecialchars($trip['slot'])?></span>
      </div>
      <div class="info-item" title="Harga">
        <i class="bi bi-currency-dollar"></i>
        <span>Harga Mulai</span>
        <span>Rp <?=number_format($trip['harga'], 0, ',', '.')?></span>
      </div>
    </nav>
    
    <div class="content-area">
        <section class="detail-section" aria-labelledby="meeting-title" style="scroll-margin-top:130px;">
          <h2 id="meeting-title">Meeting Point</h2>
          <p><strong>Nama Lokasi :</strong>  <?=htmlspecialchars($detail['nama_lokasi'])?></p>
          <p><strong>Alamat :</strong>  <?=nl2br(htmlspecialchars($detail['alamat']))?></p>
          <p><strong>Waktu Kumpul :</strong>  <?=htmlspecialchars($detail['waktu_kumpul'])?></p>
          <?php if(!empty($detail['link_map'])): ?>
          <div class="map-container" aria-label="Peta meeting point">
            <?php 
              $linkMap = trim($detail['link_map']);
              if(strpos($linkMap, '/maps/embed?') !== false) {
                echo '<iframe src="'.htmlspecialchars($linkMap).'" allowfullscreen loading="lazy"></iframe>';
              } elseif(preg_match('#^https?://(www\.)?google\.(com|co\.id)/maps/#', $linkMap)) {
                $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
                if(strpos($embedUrl, '?') === false) {
                     $embedUrl .= '?';
                }
                if(strpos($embedUrl, 'zoom=') === false) {
                     $embedUrl .= '&zoom=12'; 
                }
                echo '<iframe src="'.htmlspecialchars($embedUrl).'" allowfullscreen loading="lazy"></iframe>';
              } else {
                echo '<p style="padding: 21px 0 0 25px;"><a href="'.htmlspecialchars($linkMap).'" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a></p>';
              }
            ?>
          </div>
          <?php endif; ?>
        </section>

        <section class="detail-section" aria-labelledby="include-title" style="scroll-margin-top:130px;">
          <h2 id="include-title">Include</h2>
          <?= createIconList($detail['include'], 'bi bi-check-circle-fill icon-list-include') ?>
        </section>

        <section class="detail-section" aria-labelledby="exclude-title" style="scroll-margin-top:130px;">
          <h2 id="exclude-title">Exclude</h2>
          <?= createIconList($detail['exclude'], 'bi bi-x-octagon-fill icon-list-exclude') ?>
        </section>

        <section class="detail-section" aria-labelledby="syarat-title" style="scroll-margin-top:130px;">
          <h2 id="syarat-title">Syarat & Ketentuan</h2>
          <?= createIconList($detail['syaratKetentuan'], 'bi bi-exclamation-triangle-fill icon-list-syarat') ?>
        </section>
    </div>
    
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function bookTrip(tripId) {
      Swal.fire({
        title: 'Booking Trip',
        text: 'Fitur pendaftaran untuk trip ID ' + tripId + ' akan segera tersedia!',
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#a97c50'
      });
    }
  </script>
</body>
</html>