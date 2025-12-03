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

function createIconList($text, $iconClass, $variant = 'primary')
{
    $items = array_filter(array_map('trim', explode("\n", $text)));
    if (count($items) <= 1 && empty($items[0])) {
        return '<p class="empty-state">' . nl2br(htmlspecialchars($text)) . '</p>';
    }
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
        /* --- CORE VARIABLES --- */
        :root {
            --primary: #9C7E5C;
            --primary-dark: #7B5E3A;
            --secondary: #D4A373;

            --bg-body: #FAF8F5;
            --bg-card: #FFFFFF;
            --bg-icon-brown: #EFEBE9;

            --text-main: #37474F;
            --text-body-gray: #546E7A;
            --text-muted: #90A4AE;

            --danger: #D32F2F;
            --success: #388E3C;
            --warning: #FBC02D;

            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 8px;
            --container-width: 1100px;
            --shadow-brown: rgba(156, 126, 92, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            /* PADDING DESKTOP DEFAULT */
            padding-top: 80px;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--primary);
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
        }

        ul {
            list-style: none;
        }

        .text-success {
            color: var(--success) !important;
        }

        .text-danger {
            color: var(--danger) !important;
        }

        .text-warning {
            color: var(--warning) !important;
        }

        /* --- 1. HERO SECTION --- */
        .trip-hero {
            position: relative;
            height: 80vh;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 4rem;
            margin-bottom: 0;
            overflow: hidden;
            /* Pastikan tidak ada margin top */
            margin-top: 0; 
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
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6));
            z-index: 1;
        }

        .trip-hero-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(to bottom, transparent 0%, var(--bg-body) 100%);
            z-index: 2;
            pointer-events: none;
        }

        .trip-hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: white;
            width: 100%;
            max-width: var(--container-width);
            padding: 0 1.5rem;
            animation: slideUp 0.8s ease-out;
            margin-top: -50px;
        }

        .badge-trip {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #FFF;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .trip-title {
            font-size: clamp(2.5rem, 6vw, 5rem);
            line-height: 1.1;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            color: white;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* --- 2. STATS CONTAINER --- */
        .stats-container {
            max-width: var(--container-width);
            margin: -80px auto 4rem;
            padding: 0 1.5rem;
            position: relative;
            z-index: 10;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1.5rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-top: 1px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem 2rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.8rem;
            position: relative;
            transition: transform 0.3s ease;
            cursor: default;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-item::after {
            display: none;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.5);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .stat-item:hover .stat-icon {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 8px 20px rgba(156, 126, 92, 0.3);
        }

        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-body-gray);
            font-weight: 700;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
        }

        /* --- MAIN LAYOUT --- */
        .main-content {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2.5rem;
            align-items: stretch;
        }

        .left-column,
        .right-column {
            display: flex;
            flex-direction: column;
        }

        .content-card,
        .booking-card {
            margin-bottom: 2rem;
        }

        .left-column>:last-child,
        .right-column>:last-child {
            margin-bottom: 0;
        }

        /* === GRID UNTUK INCLUDE/EXCLUDE === */
        .details-grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .details-grid-container .content-card {
            margin-bottom: 0;
            height: 100%;
        }

        /* --- SIDEBAR KANAN: FINAL FIX --- */
        @media (min-width: 993px) {
            .sticky-right-sidebar {
                position: -webkit-sticky;
                position: sticky;
                top: 100px;
                z-index: 90;
                height: fit-content;
            }

            .booking-card {
                position: static !important;
            }
        }

        /* --- COMPONENTS --- */
        .content-card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(156, 126, 92, 0.05);
            border: 1px solid rgba(156, 126, 92, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(156, 126, 92, 0.12);
        }

        /* --- REVISI: CARD HEADER (PEMBUNGKUS JUDUL) --- */
        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(156, 126, 92, 0.08);
            margin: -2.5rem -2.5rem 1.5rem -2.5rem;
            padding: 1.5rem 2.5rem;
            border-bottom: 1px solid rgba(156, 126, 92, 0.1);
        }

        .card-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--primary);
        }

        .card-header i {
            font-size: 1.6rem;
            color: var(--primary);
        }

        .include-box .card-header i {
            color: var(--success);
        }

        .exclude-box .card-header i {
            color: var(--danger);
        }

        /* Booking Card */
        .booking-card {
            background: #fff;
            border: 2px solid var(--primary);
            padding: 0.7rem;
            border-radius: var(--radius-md);
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 40px var(--shadow-brown);
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: -25px;
            right: -25px;
            width: 90px;
            height: 90px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.08;
        }

        /* REVISI: JARAK HARGA */
        .price-tag-large {
            margin-bottom: 1.2rem;
        }

        .price-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            /* Ditambah agar tidak dempet */
            display: block;
        }

        .price-amount {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .btn-book-static {
            width: 95%;
            margin: 0 auto;
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(156, 126, 92, 0.25);
        }

        .btn-book-static:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-book-static:disabled {
            background: #BCAAA4;
            cursor: not-allowed;
            box-shadow: none;
        }

        .booking-card p {
            margin-top: 0.5rem !important;
            font-size: 0.60rem !important;
            margin-bottom: 0;
        }

        /* Help Card */
        .help-card {
            margin-top: 1.2rem;
            text-align: center;
            background: #fff;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            border: 1px solid rgba(156, 126, 92, 0.15);
            box-shadow: 0 4px 15px rgba(156, 126, 92, 0.05);
        }

        .help-card .bi-headset {
            font-size: 2rem !important;
            color: var(--primary);
            opacity: 0.7;
            display: block;
            margin-bottom: 0.5rem;
        }

        .help-card h4 {
            margin: 0 0 0.5rem;
            font-size: 1.05rem;
            color: var(--primary);
        }

        .help-card p {
            margin-bottom: 1rem !important;
            font-size: 0.85rem;
            line-height: 1.4;
            color: var(--text-body-gray);
        }

        .help-card a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.7rem;
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            gap: 6px;
            transition: 0.3s;
            background: white;
        }

        .help-card a:hover {
            background: var(--primary);
            color: white;
        }

        /* Detail & Map */
        .location-details p {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: var(--text-body-gray);
            font-size: 1rem;
        }

        .location-details p strong {
            color: var(--text-main);
            min-width: 100px;
        }

        .location-details i {
            font-size: 1.1rem;
            margin-top: 3px;
        }

        .location-details i.text-danger {
            color: var(--danger) !important;
        }

        .location-details i.text-warning {
            color: var(--warning) !important;
        }

        .location-details i.text-success {
            color: var(--success) !important;
        }

        /* --- CUSTOM MAPS DESIGN (WARM THEME) --- */

        .map-frame {
            margin-top: 2rem;
            width: 100%;
            height: 320px;

            /* Bentuk Container: Rounded Modern */
            border-radius: 30px;
            /* Hapus border putih tebal, ganti border tipis warna tema */
            border: 2px solid rgba(156, 126, 92, 0.2);

            overflow: hidden;
            position: relative;
            background: #F3F4F6;

            /* Shadow yang mengangkat peta */
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
        }

        /* INI RAHASIANYA: Mengubah warna peta dengan filter */
        .map-frame iframe {
            width: 100%;
            height: 100%;
            border: 0;

            /* Filter Kombinasi:
       sepia: bikin kecoklatan
       saturate: atur kecerahan warna
       hue-rotate: geser warna biru laut jadi agak teal/hijau
    */
            filter: sepia(0.4) saturate(1.4) contrast(1.1) hue-rotate(-15deg);

            /* Transisi halus jika dihover */
            transition: filter 0.5s ease;
        }

        /* Efek: Kalau mouse diarahkan ke peta, warnanya jadi normal (agar user jelas liat jalan) */
        .map-frame:hover iframe {
            filter: sepia(0) saturate(1) contrast(1) hue-rotate(0deg);
        }

        /* Tombol Buka Maps di bawahnya kita permak juga */
        .btn-open-map {
            display: flex;
            align-items: center;
            justify-content: center;
            width: auto;
            /* Tidak full width */
            display: inline-flex;
            /* Biar ngikutin lebar teks */
            margin-top: 1.2rem;
            padding: 0.6rem 1.5rem;
            background: rgba(156, 126, 92, 0.1);
            /* Background transparan tema */
            color: var(--primary);
            font-weight: 700;
            font-size: 0.9rem;
            border-radius: 50px;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        /* Posisikan tombol map di kanan agar rapi */
        .content-card .btn-open-map-wrapper {
            text-align: right;
        }

        .btn-open-map:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(156, 126, 92, 0.3);
            transform: translateY(-2px);
        }

        /* Lists */
        .custom-list {
            flex: 1;
        }

        .custom-list li {
            padding: 0.8rem 0;
            display: flex;
            align-items: flex-start;
            gap: 1.2rem;
            border-bottom: 1px solid rgba(156, 126, 92, 0.1);
        }

        .custom-list li:last-child {
            border-bottom: none;
        }

        .list-icon {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-top: 3px;
        }

        .list-icon.icon-primary {
            background: var(--bg-icon-brown);
            color: var(--primary);
        }

        .list-icon.icon-success {
            background: #E8F5E9;
            color: var(--success);
        }

        .list-icon.icon-danger {
            background: #FFEBEE;
            color: var(--danger);
        }

        .list-text {
            font-size: 1rem;
            color: var(--text-body-gray);
            font-weight: 500;
        }

        .pre-booking-header {
            background: var(--bg-body);
            padding: 1.5rem;
            border-bottom: 1px solid rgba(156, 126, 92, 0.1);
            text-align: center;
        }

        .pre-booking-content {
            padding: 2rem;
            overflow-y: auto;
        }

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

        .checkbox-container:hover {
            border-color: var(--primary);
            background: var(--bg-icon-brown);
        }

        .checkbox-container input {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
        }

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
        }

        .btn-main-next:disabled {
            background: #BCAAA4;
            cursor: not-allowed;
        }

        /* REVISI: TOMBOL CLOSE JELAS */
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #e0e0e0;
            /* Abu gelap dikit */
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #000;
            /* Hitam */
            font-weight: 800;
            /* Tebal */
            z-index: 10;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .close-btn:hover {
            background: transparent;
            color: #fff;
        }

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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: 0.3s;
        }

        .whatsapp-float:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.3);
        }

        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 992px) {
            body {
                padding-bottom: 100px;
                /* REVISI DI SINI: Mengurangi padding-top mobile agar tidak ada gap */
                /* Ubah angka 60px ini jika navbar Anda lebih kecil/besar, atau set 0 jika navbar tidak fixed */
                padding-top: 60px; 
            }

            .trip-hero {
                height: 50vh;
                min-height: 350px;
            }

            .trip-title {
                font-size: 2rem;
            }

            .stats-container {
                margin-top: -50px;
                padding: 0 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                padding: 1.5rem 1rem;
                gap: 0.75rem;
            }

            .stat-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
            }

            .stat-value {
                font-size: 1rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .main-content {
                grid-template-columns: 1fr;
                padding: 0 1rem 2rem;
            }

            .content-card {
                padding: 1.5rem;
            }

            /* Header Mobile */
            .card-header {
                margin: -1.5rem -1.5rem 1rem -1.5rem;
                padding: 1.2rem 1.5rem;
            }

            .booking-card {
                display: none;
            }

            .details-grid-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .mobile-bottom-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: #fff;
                padding: 1rem 1.5rem;
                padding-bottom: calc(1rem + env(safe-area-inset-bottom));
                box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
                z-index: 990;
                border-top: 1px solid #eee;
            }

            .mobile-price-info {
                display: flex;
                flex-direction: column;
            }

            .mobile-price-label {
                font-size: 0.75rem;
                color: var(--text-muted);
            }

            .mobile-price-amount {
                font-size: 1.1rem;
                font-weight: 800;
                color: var(--primary);
            }

            .btn-mobile-book {
                background: var(--primary);
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 50px;
                font-weight: 700;
                border: none;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.95rem;
                cursor: pointer;
            }

            .btn-mobile-book:disabled {
                background: #ccc;
            }

            .whatsapp-float {
                bottom: 100px;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* --- TARUH INI DI DALAM TAG <style> ANDA --- */

        /* Kustomisasi Container Popup SweetAlert */
        .swal2-popup.custom-theme-popup {
            border-radius: 30px !important;
            /* Sudut sangat membulat */
            padding: 2.5rem 2rem !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
            /* Shadow lembut */
        }

        /* Menghilangkan border default pada actions */
        .swal2-actions {
            margin-top: 1.5rem !important;
        }

        /* Kustomisasi Ikon */
        /* Kita akan membuat ikon custom menggunakan HTML, jadi kita style container-nya */
        .swal-custom-icon-container {
            width: 80px;
            height: 80px;
            background: var(--primary);
            /* Warna emas/coklat tema */
            border-radius: 50%;
            /* Lingkaran penuh */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            /* Posisi tengah */
            box-shadow: 0 10px 20px -5px rgba(156, 126, 92, 0.4);
            /* Shadow lembut di bawah ikon */
        }

        .swal-custom-icon-container i {
            color: white;
            font-size: 2.5rem;
        }

        /* Kustomisasi Judul dan Teks */
        .swal-custom-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 0.8rem;
        }

        .swal-custom-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-body-gray);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* Kustomisasi Tombol (PENTING) */
        /* Kita matikan styling default SweetAlert dan gunakan class kita sendiri */
        .btn-swal-custom {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            /* Sudut tombol agak membulat */
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            /* Transisi halus untuk hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 0 0.5rem;
            /* Jarak antar tombol */
        }

        /* Tombol Konfirmasi (Login) */
        .btn-swal-confirm {
            background-color: var(--primary) !important;
            color: white !important;
        }

        /* Efek Hover Tombol Konfirmasi */
        .btn-swal-confirm:hover {
            background-color: var(--primary-dark) !important;
            /* Warna lebih gelap saat hover */
            transform: translateY(-3px);
            /* Efek naik sedikit */
            box-shadow: 0 8px 15px rgba(156, 126, 92, 0.3);
        }

        /* Tombol Batal */
        .btn-swal-cancel {
            background-color: #6c757d !important;
            /* Warna abu-abu standard */
            color: white !important;
        }

        /* Efek Hover Tombol Batal */
        .btn-swal-cancel:hover {
            background-color: #5a6268 !important;
            /* Abu-abu lebih gelap */
            transform: translateY(-3px);
            /* Efek naik sedikit */
            box-shadow: 0 8px 15px rgba(108, 117, 125, 0.3);
        }

        /* MODALS */
        /* Pastikan ID ini berdiri sendiri sekarang */
        #preBookingModal {
            display: none;
            /* INI KUNCI UTAMANYA: Sembunyikan secara default */
            position: fixed;
            /* Agar melayang di atas konten lain */
            inset: 0;
            z-index: 10000;
            background: rgba(78, 52, 46, 0.85);
            /* Background gelap transparan */
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 1rem;
            width: 100%;
            height: 100%;
        }

        /* Class ini ditambahkan oleh Javascript saat tombol diklik */
        #preBookingModal.active {
            display: flex;
            /* Ubah jadi flex agar muncul */
            animation: fadeIn 0.3s ease;
        }

        .pre-booking-box {
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

        /* ============================================================ */
        /* 🔥 FIX RESPONSIVE SWEETALERT DI LAYAR HP & BUTTON BERDAMPINGAN 🔥 */
        /* ============================================================ */

        /* Kustomisasi Container Popup SweetAlert */
        .swal2-popup.custom-theme-popup {
            border-radius: 30px !important;
            padding: 2.5rem 2rem !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
            width: 90% !important;
            /* Lebar 90% untuk default */
            max-width: 500px !important;
            /* Maksimal lebar di desktop */
            box-sizing: border-box !important;
        }

        .swal2-actions {
            display: flex !important;
            flex-direction: row !important;
            gap: 8px !important;
            /* Mengurangi jarak antar tombol agar lebih berdempetan */
            width: 100% !important;
            justify-content: center !important;
            margin-top: 1.5rem !important;
            padding: 0 1rem !important;
            /* Memberi padding samping pada container tombol */
        }

        /* Kustomisasi Ikon */
        .swal-custom-icon-container {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 10px 20px -5px rgba(156, 126, 92, 0.4);
        }

        .swal-custom-icon-container i {
            color: white;
            font-size: 2.5rem;
        }

        /* Text Styling */
        .swal-custom-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 0.8rem;
        }

        .swal-custom-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-body-gray);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .btn-swal-custom {
            padding: 0.7rem 0.5rem !important;
            /* Mengurangi padding vertikal agar tombol sedikit lebih pendek */
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            /* Sedikit mengecilkan font agar muat */
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 0 !important;
            /* Hapus margin default */
            width: 45% !important;
            /* Lebar tombol agar berdempetan tapi tetap ada spasi */
            flex: 1;
            /* Memastikan kedua tombol membagi ruang secara adil */
        }

        .btn-swal-confirm {
            background-color: var(--primary) !important;
            color: white !important;
        }

        .btn-swal-confirm:hover {
            background-color: var(--primary-dark) !important;
            transform: translateY(-3px);
        }

        .btn-swal-cancel {
            background-color: #6c757d !important;
            color: white !important;
        }

        .btn-swal-cancel:hover {
            background-color: #5a6268 !important;
            transform: translateY(-3px);
        }

        /* --- MEDIA QUERY KHUSUS MOBILE UNTUK SWEETALERT --- */
        @media (max-width: 480px) {
            .swal2-popup.custom-theme-popup {
                padding: 1.5rem 1rem !important;
                width: 95% !important;
                /* Lebih lebar di HP kecil */
            }

            .swal-custom-icon-container {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }

            .swal-custom-icon-container i {
                font-size: 1.8rem;
            }

            .swal-custom-title {
                font-size: 1.4rem;
                margin-bottom: 0.5rem;
            }

            .swal-custom-text {
                font-size: 0.9rem;
            }

            .btn-swal-custom {
                font-size: 0.85rem;
                padding: 10px 0 !important;
            }

            /* Di HP Tetap Row (Berdampingan) */
            .swal2-actions {
                flex-direction: row !important;
            }
        }

        /* ... sisa CSS di bawahnya (header, content, footer) sudah benar ... */
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
        <div class="trip-hero-fade"></div>

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

            <div class="details-grid-container">
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

            <section class="content-card sop-box">
                <div class="card-header">
                    <i class="bi bi-shield-exclamation"></i>
                    <h3>Syarat & Ketentuan</h3>
                </div>
                <?= createIconList($detail['syaratKetentuan'], 'bi bi-check-lg', 'primary') ?>
            </section>

        </div>

        <div class="right-column">
            <div class="sticky-right-sidebar">

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

                <div class="help-card">
                    <div style="font-size: 2rem; color: var(--primary); opacity: 0.7; margin-bottom: 0.5rem;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4>Butuh Bantuan?</h4>
                    <p style="font-size: 0.85rem; color: var(--text-body-gray); margin-bottom: 1rem; line-height: 1.4;">
                        Bingung soal itinerary atau perlengkapan? Tim kami siap membantu 24/7.
                    </p>
                    <a href="https://wa.me/6285233463360" target="_blank">
                        <i class="bi bi-whatsapp"></i> Chat Admin
                    </a>
                </div>

            </div>
        </div>
    </main>

    <a href="https://wa.me/6285233463360?text=Halo,%20saya%20mau%20tanya%20paket%20trip%20<?= urlencode($trip['nama_gunung']) ?>" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>

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
            </div>
            <div class="pre-booking-footer">
                <label class="checkbox-container">
                    <input type="checkbox" id="agreementCheckbox" onclick="toggleNextButton()">
                    <span style="font-size: 0.9rem; color: var(--text-main);">Saya telah membaca, memahami, dan menyetujui SOP pendakian di atas.</span>
                </label>
                <button type="button" class="btn-main-next" id="nextStepBtn" disabled onclick="continueToRegistration()">
                    Lanjutkan Pendaftaran <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="mobile-bottom-nav">
        <div class="mobile-price-info">
            <span class="mobile-price-label">Harga per pax</span>
            <span class="mobile-price-amount">Rp <?= number_format($trip['harga'], 0, ',', '.') ?></span>
        </div>

        <?php if ($soldOut): ?>
            <button class="btn-mobile-book" disabled style="background: #999;">
                Sold Out <i class="bi bi-emoji-frown"></i>
            </button>
        <?php else: ?>
            <button class="btn-mobile-book" onclick="showPreBookingModal()">
                Daftar <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
        <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>
    <script>
        // GANTI BAGIAN SCRIPT LAMA DENGAN INI

        const tripData = <?= $tripDetailsJson ?>;
        const isUserLoggedIn = <?= $isLogin ? 'true' : 'false' ?>;
        const registrationPageUrl = '<?= getPageUrl('user/register-trip.php') ?>';

        // --- FUNGSI BARU MENGGUNAKAN SWEETALERT2 ---
        function showLoginWarning() {
            Swal.fire({
                // Kita gunakan HTML kustom untuk struktur konten agar mirip desain target
                html: `
            <div class="swal-custom-content">
                <div class="swal-custom-icon-container">
                    <i class="bi bi-lock-fill"></i> </div>
                <h2 class="swal-custom-title">Login Diperlukan</h2>
                <p class="swal-custom-text">Anda harus masuk ke akun Anda untuk mendaftar trip ini.</p>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Login Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true, // Tombol konfirmasi di kanan, batal di kiri (opsional)

                // PENTING: Set ke false agar kita bisa pakai CSS class kita sendiri untuk tombol
                buttonsStyling: false,

                // Terapkan class CSS kustom yang sudah kita buat di atas
                customClass: {
                    popup: 'custom-theme-popup',
                    confirmButton: 'btn-swal-custom btn-swal-confirm',
                    cancelButton: 'btn-swal-custom btn-swal-cancel'
                },
                // Backdrop gelap transparan
                backdrop: `rgba(0, 0, 0, 0.6)`
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aksi jika tombol "Login Sekarang" diklik
                    openLoginFromWarning();
                }
            });
        }

        // Fungsi closeLoginWarning tidak lagi diperlukan karena SweetAlert menanganinya otomatis, 
        // tapi jika ada pemanggilan lain, biarkan kosong atau hapus.
        function closeLoginWarning() {
            Swal.close();
        }

        function openLoginFromWarning() {
            // Tidak perlu memanggil closeLoginWarning() manual karena Swal otomatis close setelah klik confirm
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.style.display = 'flex';
                setTimeout(() => {
                    loginModal.classList.add('open');
                }, 10); // Sedikit delay agar transisi CSS berjalan halus
                document.body.style.overflow = 'hidden';
            }
        }

        // --- SISA SCRIPT LAIN TETAP SAMA ---
        function showPreBookingModal() {
            if (!isUserLoggedIn) {
                showLoginWarning();
                return;
            }
            // ... kode prebooking modal selanjutnya ...
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