<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

$navbarPath = '../';

$isLogin = isset($_SESSION['id_user']);
$userLogin = null;
if ($isLogin) {
    $stmt = $conn->prepare("SELECT username, email, alamat, no_wa FROM users WHERE id_user=?");
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $userLogin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: " . getPageUrl('index.php'));
    exit();
}

$stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) {
    header("Location: " . getPageUrl('index.php'));
    exit();
}

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
        'include' => "Informasi akan diupdate segera",
        'exclude' => "Informasi akan diupdate segera",
        'syaratKetentuan' => "Informasi akan diupdate segera"
    ];
}

// Fungsi helper diupdate untuk menerima varian warna (primary, success, danger)
function createIconList($text, $iconClass, $variant = 'primary')
{
    $items = array_filter(array_map('trim', explode("\n", $text)));
    if (count($items) <= 1 && empty($items[0])) {
        return '<p class="empty-state">' . nl2br(htmlspecialchars($text)) . '</p>';
    }
    
    // Menentukan class berdasarkan varian
    $variantClass = 'icon-' . $variant;

    $html = '<ul class="custom-list">';
    foreach ($items as $item) {
        if (!empty($item)) {
            $html .= '<li><div class="list-icon ' . htmlspecialchars($variantClass) . '"><i class="' . htmlspecialchars($iconClass) . '"></i></div> <span class="list-text">' . htmlspecialchars($item) . '</span></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

// --- ATURAN PENDAKIAN MAJELIS MDPL ---
$climbingSOP = "
Wajib Membawa Perlengkapan Pribadi Lengkap (Jaket, Sepatu, Ransel, dll.).
Wajib dalam kondisi fisik dan mental yang prima.
Dilarang membawa dan mengonsumsi Narkoba, Miras, atau zat terlarang lainnya.
Mengikuti instruksi dan arahan dari Leader/Guide.
Menerapkan etika 'Leave No Trace' (Tidak meninggalkan sampah).
Mengisi dan menyerahkan Surat Pernyataan sebelum pendakian.
Setiap peserta bertanggung jawab penuh atas barang bawaan pribadi.
";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <title><?= htmlspecialchars($trip['nama_gunung']) ?> | Majelis MDPL</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* --- CORE VARIABLES & RESET --- */
        :root {
            /* Palette: Warm Brown & Natural */
            --primary: #9C7E5C; /* Coklat Emas Hangat (Untuk Title Section, Button) */
            --primary-dark: #7B5E3A;
            --secondary: #D4A373; /* Accent Gold */
            
            --bg-body: #FAF8F5; /* Krem Hangat */
            --bg-card: #FFFFFF;
            --bg-icon-brown: #EFEBE9;
            
            /* Definisi Ulang Warna Teks */
            --text-main: #37474F; /* Abu tua untuk teks utama */
            --text-body-gray: #546E7A; /* Abu-abu untuk isi list (Include/Exclude) */
            --text-muted: #90A4AE; /* Abu muda untuk label sekunder */
            
            /* Warna Standar (Dikembalikan) */
            --danger: #D32F2F; /* Merah Standar */
            --success: #388E3C; /* Hijau Standar */
            --warning: #FBC02D; /* Kuning Standar */
            
            /* Spacing & Radius */
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 8px;
            --container-width: 1100px;
            --shadow-brown: rgba(156, 126, 92, 0.15);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            padding-top: 80px; /* Space for Navbar */
        }

        /* Headings menggunakan warna Primary (Coklat) */
        h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; font-weight: 800; color: var(--primary); }
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; }
        
        /* Utility Classes untuk Warna */
        .text-success { color: var(--success) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-primary { color: var(--primary) !important; }

        /* --- HERO SECTION --- */
        .trip-hero {
            position: relative;
            height: 75vh;
            min-height: 500px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 4rem;
            margin-bottom: 2rem;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            overflow: hidden;
        }

        .trip-hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            filter: brightness(0.9) sepia(0.2);
            transform: scale(1.05);
        }

        .trip-hero-overlay {
            position: absolute;
            inset: 0;
            /* Gradient coklat hangat */
            background: linear-gradient(to top, rgba(78, 52, 46, 0.9) 0%, rgba(156, 126, 92, 0.4) 50%, transparent 100%);
            z-index: 1;
        }

        .trip-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            width: 100%;
            max-width: var(--container-width);
            padding: 0 1.5rem;
            animation: slideUp 0.8s ease-out;
        }

        .badge-trip {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            border: 1px solid rgba(255,255,255,0.4);
            color: #FFECB3;
        }

        /* REVISI: Judul Gunung kembali jadi Putih */
        .trip-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 12px rgba(0,0,0,0.3);
            color: white; /* Warna Putih */
        }

        /* --- STATS GRID --- */
        .stats-container {
            max-width: var(--container-width);
            margin: -60px auto 3rem; 
            padding: 0 1.5rem;
            position: relative;
            z-index: 10;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1.5rem;
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 40px var(--shadow-brown);
            border: 1px solid rgba(156, 126, 92, 0.1);
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.5rem;
            position: relative;
        }

        .stat-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -0.75rem;
            top: 10%;
            height: 80%;
            width: 1px;
            background: #D7CCC8;
            display: none; 
        }
        @media(min-width: 768px) { .stat-item:not(:last-child)::after { display: block; } }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--bg-icon-brown);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }

        /* --- MAIN LAYOUT (Tata Letak Rata Atas Bawah) --- */
        .main-content {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2.5rem;
            align-items: stretch; /* Tinggi kolom sama */
        }

        .left-column, .right-column {
            display: flex;
            flex-direction: column;
        }

        /* KUNCI TATA LETAK: Kartu terakhir di kolom kiri meregang */
        .left-column .content-card:last-child { flex: 1; }

        .content-card, .booking-card { margin-bottom: 2rem; }
        .left-column > :last-child, .right-column > :last-child { margin-bottom: 0; }

        @media (max-width: 992px) {
            .main-content { grid-template-columns: 1fr; }
            .left-column .content-card:last-child { flex: auto; }
            .content-card, .booking-card { margin-bottom: 2rem; }
        }

        /* --- CONTENT CARDS --- */
        .content-card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(156, 126, 92, 0.08);
            border: 1px solid rgba(156, 126, 92, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(156, 126, 92, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px dashed rgba(156, 126, 92, 0.15);
        }

        /* REVISI: Judul Section tetap Coklat */
        .card-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--primary);
        }
        
        .card-header i {
            font-size: 1.5rem;
            color: var(--primary);
        }
        
        /* Override khusus untuk header Include/Exclude agar ikonnya berwarna */
        .include-box .card-header i { color: var(--success); }
        .exclude-box .card-header i { color: var(--danger); }


        /* --- BOOKING CARD --- */
        .booking-card {
            background: #fff;
            border: 2px solid var(--primary); 
            padding: 2rem;
            border-radius: var(--radius-md);
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-brown);
        }
        
        .booking-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.1;
        }

        .price-tag-large {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
        }
        
        .price-label { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.2rem; }
        .price-amount { font-size: 2rem; font-weight: 800; color: var(--primary); }

        .btn-book-static {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(156, 126, 92, 0.3);
        }

        .btn-book-static:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(156, 126, 92, 0.4);
        }

        .btn-book-static:disabled {
            background: #BCAAA4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* --- MEETING POINT & MAP STYLING --- */
        .location-details p {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: var(--text-body-gray); /* Teks isi jadi abu-abu */
            font-size: 0.95rem;
        }
        
        .location-details p strong {
            color: var(--text-main);
            min-width: 120px;
        }

        /* REVISI: Warna ikon meeting point dikembalikan ke warna semantik */
        .location-details i.text-danger { color: var(--danger) !important; }
        .location-details i.text-warning { color: var(--warning) !important; }
        .location-details i.text-success { color: var(--success) !important; }
        
        .map-frame {
            margin-top: 1.5rem;
            width: 100%;
            height: 300px;
            border-radius: var(--radius-md);
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
            border: 4px solid white;
            background: #eee;
        }

        .map-frame iframe {
            width: 100%;
            height: 100%;
            border: 0;
            filter: saturate(0.8) sepia(0.2) contrast(1.1);
        }
        
        .btn-open-map {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 2px;
        }
        
        .btn-open-map:hover { color: var(--primary-dark); border-color: var(--primary-dark); }

        /* --- CUSTOM LISTS (Revised Colors) --- */
        .custom-list { flex: 1; }
        .custom-list li {
            position: relative;
            padding: 0.75rem 0;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 1px solid rgba(156, 126, 92, 0.1);
        }
        .custom-list li:last-child { border-bottom: none; }

        .list-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-top: 2px;
        }

        /* REVISI: Varian Warna Ikon List */
        .list-icon.icon-primary { background: var(--bg-icon-brown); color: var(--primary); }
        .list-icon.icon-success { background: #E8F5E9; color: var(--success); } /* Hijau Muda */
        .list-icon.icon-danger { background: #FFEBEE; color: var(--danger); } /* Merah Muda */
        
        /* REVISI: Teks isi list jadi Abu-abu */
        .list-text { font-size: 0.95rem; color: var(--text-body-gray); font-weight: 500; }

        /* --- MODAL RE-STYLING --- */
        #preBookingModal, #loginWarningModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(78, 52, 46, 0.85);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        #preBookingModal.active, #loginWarningModal.active { display: flex; animation: fadeIn 0.3s ease; }

        .pre-booking-box, .login-warning-container {
            background: #fff;
            width: 100%;
            max-width: 600px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(62, 39, 35, 0.3);
            display: flex;
            flex-direction: column;
            max-height: 85vh;
            position: relative;
        }

        .pre-booking-header {
            background: var(--bg-body);
            padding: 1.5rem;
            border-bottom: 1px solid rgba(156, 126, 92, 0.1);
            text-align: center;
        }
        
        .pre-booking-content { padding: 2rem; overflow-y: auto; }
        
        .pre-booking-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(156, 126, 92, 0.1);
            background: var(--bg-body);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            cursor: pointer;
            padding: 1rem;
            background: #fff;
            border: 1px solid #D7CCC8;
            border-radius: var(--radius-sm);
            transition: 0.2s;
        }
        
        .checkbox-container:hover { border-color: var(--primary); background: var(--bg-icon-brown); }
        .checkbox-container input { width: 20px; height: 20px; accent-color: var(--primary); }

        .btn-main-next {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(156, 126, 92, 0.25);
        }
        .btn-main-next:hover { background: var(--primary-dark); box-shadow: 0 8px 20px rgba(156, 126, 92, 0.35); }
        .btn-main-next:disabled { background: #BCAAA4; cursor: not-allowed; box-shadow: none; }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            z-index: 10;
        }
        .close-btn:hover { color: var(--primary); }

        /* --- WHATSAPP FLOATING --- */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #25D366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: 0.3s;
        }
        .whatsapp-float:hover { transform: scale(1.1); box-shadow: 0 6px 14px rgba(0,0,0,0.3); }

        /* Animations */
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .trip-title { font-size: 2.5rem; }
            .trip-hero { height: 60vh; }
            .stats-container { margin-top: -40px; }
            .content-card, .booking-card { padding: 1.5rem; }
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <?php
    $imgPath = '../img/default-mountain.jpg';
    if (!empty($trip['gambar'])) {
        $imgPath = (strpos($trip['gambar'], 'img/') === 0) ? '../' . $trip['gambar'] : '../img/' . $trip['gambar'];
    }
    $soldOut = ($trip['status'] !== 'available' || intval($trip['slot']) <= 0);
    
    // JSON Data
    $tripDetailsJson = json_encode([
        'id_trip' => $trip['id_trip'],
        'nama_gunung' => $trip['nama_gunung'],
        'tanggal' => date('d M Y', strtotime($trip['tanggal'])),
        'harga' => $trip['harga'],
        'slot' => intval($trip['slot'])
    ]);
    ?>

    <header class="trip-hero">
        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($trip['nama_gunung']) ?>" class="trip-hero-bg">
        <div class="trip-hero-overlay"></div>
        <div class="trip-hero-content">
            <span class="badge-trip">Open Trip Eksklusif</span>
            <h1 class="trip-title"><?= htmlspecialchars($trip['nama_gunung']) ?></h1>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem;">
                <i class="bi bi-geo-alt-fill" style="color: #FFECB3;"></i> Indonesia
            </p>
        </div>
    </header>

    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon"><i class="bi bi-calendar4-week"></i></div>
                <div class="stat-label">Tanggal</div>
                <div class="stat-value"><?= date('d M Y', strtotime($trip['tanggal'])) ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="bi bi-stopwatch"></i></div>
                <div class="stat-label">Durasi</div>
                <div class="stat-value"><?= htmlspecialchars($trip['durasi']) ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-label">Sisa Slot</div>
                <div class="stat-value"><?= htmlspecialchars($trip['slot']) ?> Seat</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="bi bi-tag"></i></div>
                <div class="stat-label">Status</div>
                <div class="stat-value" style="color: <?= $soldOut ? 'var(--danger)' : 'var(--success)' ?>;">
                    <?= $soldOut ? 'Full' : 'Available' ?>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content">
        
        <div class="left-column">
            
            <section class="content-card">
                <div class="card-header">
                    <i class="bi bi-map"></i>
                    <h3>Meeting Point & Lokasi</h3>
                </div>
                
                <div class="location-details">
                    <p><i class="bi bi-pin-map-fill text-danger"></i> <strong>Lokasi:</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
                    <p><i class="bi bi-alarm-fill text-warning"></i> <strong>Waktu:</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
                    <p><i class="bi bi-signpost-2 text-success"></i> <strong>Alamat:</strong> <span><?= nl2br(htmlspecialchars($detail['alamat'])) ?></span></p>
                </div>

                <?php if (!empty($detail['link_map'])): ?>
                    <div class="map-frame">
                        <?php
                        $linkMap = trim($detail['link_map']);
                        if (strpos($linkMap, '/maps/embed?') !== false) {
                            echo '<iframe src="' . htmlspecialchars($linkMap) . '" allowfullscreen loading="lazy"></iframe>';
                        } elseif (preg_match('#^https://(www\.)?google\.(com|co\.id)/maps/#', $linkMap)) {
                            $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
                            echo '<iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
                        }
                        ?>
                    </div>
                    <?php if (!strpos($linkMap, '/maps/embed?')): ?>
                        <div style="text-align: right;">
                            <a href="<?= htmlspecialchars($linkMap) ?>" target="_blank" class="btn-open-map">
                                Buka di Google Maps <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="content-card sop-box">
                <div class="card-header">
                    <i class="bi bi-shield-exclamation"></i>
                    <h3>Syarat & Ketentuan</h3>
                </div>
                <?= createIconList($detail['syaratKetentuan'], 'bi bi-check-lg', 'primary') ?>
            </section>
        </div>

        <div class="right-column">
            
            <div class="booking-card">
                <div class="price-tag-large">
                    <span class="price-label">Harga Per Pax</span>
                    <span class="price-amount">Rp <?= number_format($trip['harga'], 0, ',', '.') ?></span>
                </div>

                <?php if ($soldOut): ?>
                    <button class="btn-book-static" disabled>
                        <span>Sold Out</span>
                        <i class="bi bi-emoji-frown"></i>
                    </button>
                <?php else: ?>
                    <button class="btn-book-static" onclick="showPreBookingModal()">
                        <span>Daftar Sekarang</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                <?php endif; ?>
                
                <p style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                    <i class="bi bi-shield-check"></i> Transaksi Aman & Terpercaya
                </p>
            </div>

            <section class="content-card include-box">
                <div class="card-header">
                    <i class="bi bi-bag-check"></i>
                    <h3>Include</h3>
                </div>
                <?= createIconList($detail['include'], 'bi bi-check-circle-fill', 'success') ?>
            </section>

            <section class="content-card exclude-box">
                <div class="card-header">
                    <i class="bi bi-x-circle"></i>
                    <h3>Exclude</h3>
                </div>
                <?= createIconList($detail['exclude'], 'bi bi-x-circle-fill', 'danger') ?>
            </section>
        </div>

    </main>

    <a href="https://wa.me/6285233463360?text=Halo,%20saya%20mau%20tanya%20paket%20trip%20<?= urlencode($trip['nama_gunung']) ?>" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>

    <div id="loginWarningModal">
        <div class="login-warning-container" style="text-align: center; padding: 3rem 2rem;">
            <div style="font-size: 4rem; color: var(--primary); margin-bottom: 1rem;"><i class="bi bi-lock-fill"></i></div>
            <h2 style="margin-bottom: 1rem; color: var(--primary);">Login Diperlukan</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Anda harus masuk ke akun Anda untuk mendaftar trip ini.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button onclick="closeLoginWarning()" style="padding: 0.8rem 1.5rem; border: 1px solid #D7CCC8; background: white; color: var(--text-muted); border-radius: 8px; cursor: pointer; font-weight: 600;">Batal</button>
                <button onclick="openLoginFromWarning()" style="padding: 0.8rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 10px rgba(156, 126, 92, 0.2);">Login Sekarang</button>
            </div>
        </div>
    </div>

    <div id="preBookingModal">
        <div class="pre-booking-box">
            <button class="close-btn" onclick="closePreBookingModal()">&times;</button>
            
            <div class="pre-booking-header">
                <h3>Persetujuan & SOP</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Mohon baca dengan teliti sebelum melanjutkan.</p>
            </div>

            <div class="pre-booking-content">
                <h4 style="color: var(--primary); margin-bottom: 1rem; border-bottom: 2px solid var(--primary); display: inline-block;">SOP Pendakian</h4>
                <?= createIconList($climbingSOP, 'bi bi-check2-square', 'primary') ?>
                
                <br>
                
                <h4 style="color: var(--primary); margin-bottom: 1rem; border-bottom: 2px solid var(--primary); display: inline-block;">Ketentuan Trip</h4>
                <?= createIconList($detail['syaratKetentuan'], 'bi bi-info-circle', 'primary') ?>
            </div>

            <div class="pre-booking-footer">
                <label class="checkbox-container">
                    <input type="checkbox" id="agreementCheckbox" onclick="toggleNextButton()">
                    <span style="font-size: 0.9rem; color: var(--text-main);">Saya telah membaca, memahami, dan menyetujui seluruh aturan di atas.</span>
                </label>
                <button type="button" class="btn-main-next" id="nextStepBtn" disabled onclick="continueToRegistration()">
                    Lanjutkan Pendaftaran <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        const tripData = <?= $tripDetailsJson ?>;
        const isUserLoggedIn = <?= $isLogin ? 'true' : 'false' ?>;
        const registrationPageUrl = '<?= getPageUrl('user/register-trip.php') ?>';

        function showLoginWarning() {
            document.getElementById('loginWarningModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginWarning() {
            document.getElementById('loginWarningModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function openLoginFromWarning() {
            closeLoginWarning();
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.style.display = 'flex';
                loginModal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function showPreBookingModal() {
            if (!isUserLoggedIn) {
                showLoginWarning();
                return;
            }
            document.getElementById('agreementCheckbox').checked = false;
            document.getElementById('nextStepBtn').disabled = true;
            document.getElementById('preBookingModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePreBookingModal() {
            document.getElementById('preBookingModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleNextButton() {
            const checkbox = document.getElementById('agreementCheckbox');
            document.getElementById('nextStepBtn').disabled = !checkbox.checked;
            document.getElementById('nextStepBtn').style.opacity = checkbox.checked ? '1' : '0.5';
        }

        function continueToRegistration() {
            if (document.getElementById('agreementCheckbox').checked) {
                closePreBookingModal();
                window.location.href = registrationPageUrl + '?id=' + tripData.id_trip;
            }
        }
    </script>

</body>
</html>