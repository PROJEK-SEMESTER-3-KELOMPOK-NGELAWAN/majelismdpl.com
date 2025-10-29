<?php
// ✅ START SESSION
session_start();

// ✅ Redirect jika belum login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}

// ✅ SET NAVBAR PATH
$navbarPath = '../';

// ✅ KONEKSI DATABASE
require_once '../backend/koneksi.php';

// ✅ AMBIL DATA USER YANG LOGIN
$id_user = $_SESSION['id_user'];

// ✅ QUERY UNTUK MENGAMBIL SEMUA TRIP USER
$query = "
    SELECT 
        b.id_booking,
        b.id_trip,
        b.jumlah_orang,
        b.total_harga,
        b.tanggal_booking,
        b.status as status_booking,
        t.nama_gunung,
        t.jenis_trip,
        t.tanggal as tanggal_trip,
        t.durasi,
        t.via_gunung,
        t.harga,
        t.gambar,
        d.nama_lokasi,
        d.alamat,
        d.waktu_kumpul,
        d.link_map,
        d.include,
        d.exclude,
        d.syaratKetentuan,
        p.status_pembayaran
    FROM bookings b
    JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
    LEFT JOIN payments p ON b.id_booking = p.id_booking
    WHERE b.id_user = ?
    ORDER BY b.tanggal_booking DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

// ✅ SIMPAN DATA KE ARRAY
$myTrips = [];
while ($row = $result->fetch_assoc()) {
    // Determine final status based on booking status and payment status
    $finalStatus = $row['status_booking'];

    // Jika status_pembayaran = 'paid', override status booking
    if ($row['status_pembayaran'] === 'paid') {
        $finalStatus = 'paid';
    } elseif ($row['status_booking'] === 'confirmed') {
        $finalStatus = 'paid';
    } elseif ($row['status_booking'] === 'cancelled') {
        $finalStatus = 'cancelled';
    } elseif ($row['status_booking'] === 'finished') {
        $finalStatus = 'finished';
    } elseif ($row['status_booking'] === 'pending' && $row['status_pembayaran'] !== 'paid') {
        $finalStatus = 'pending';
    }

    // Format gambar path
    $imagePath = $navbarPath . 'img/default-mountain.jpg';
    if (!empty($row['gambar'])) {
        if (strpos($row['gambar'], 'img/') === 0) {
            $imagePath = $navbarPath . $row['gambar'];
        } else {
            $imagePath = $navbarPath . 'img/' . $row['gambar'];
        }
    }

    $myTrips[] = [
        'id_booking' => $row['id_booking'],
        'id_trip' => $row['id_trip'],
        'nama_gunung' => $row['nama_gunung'],
        'jenis_trip' => $row['jenis_trip'],
        'tanggal_trip' => $row['tanggal_trip'],
        'durasi' => $row['durasi'] ?? '1 hari',
        'via_gunung' => $row['via_gunung'] ?? 'Via Utama',
        'nama_lokasi' => $row['nama_lokasi'] ?? 'Lokasi kumpul belum ditentukan',
        'alamat' => $row['alamat'] ?? 'Alamat belum ditentukan',
        'tanggal_booking' => $row['tanggal_booking'],
        'jumlah_orang' => $row['jumlah_orang'],
        'total_harga' => $row['total_harga'],
        'status_booking' => $finalStatus,
        'gambar' => $imagePath,
        'include' => $row['include'] ?? 'Informasi akan diupdate',
        'exclude' => $row['exclude'] ?? 'Informasi akan diupdate',
        'syaratKetentuan' => $row['syaratKetentuan'] ?? 'Informasi akan diupdate',
        'waktu_kumpul' => $row['waktu_kumpul'] ?? '00:00:00',
        'link_map' => $row['link_map'] ?? '#',
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Trip Saya - Majelis MDPL</title>

    <!-- ✅ LOAD FONT AWESOME & BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8dcc4 100%);
            min-height: 100vh;
        }

        .page-container {
            padding-top: 100px;
            min-height: 100vh;
        }

        .my-trips-section {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-content {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .page-title i {
            color: #a97c50;
            margin-right: 10px;
        }

        .trips-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .list-trip-card {
            display: flex;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
            align-items: center;
        }

        .list-trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .card-image-box {
            width: 250px;
            min-width: 200px;
            height: 180px;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 10px;
        }

        .trip-type-badge {
            background: rgba(169, 124, 80, 0.9);
            color: #fff;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85em;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .card-info {
            flex-grow: 1;
            padding: 15px 25px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8em;
            color: #fff;
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #ffc107;
            color: #333;
        }

        .status-paid {
            background-color: #28a745;
        }

        .status-cancelled {
            background-color: #dc3545;
        }

        .status-finished {
            background-color: #6c757d;
        }

        .card-info h3 {
            font-size: 1.4rem;
            color: #222;
            margin-bottom: 15px;
        }

        .trip-meta {
            font-size: 0.95em;
            color: #555;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .trip-meta i {
            color: #a97c50;
            width: 20px;
            text-align: center;
        }

        .card-actions-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px;
            min-width: 180px;
            border-left: 1px solid #f0f0f0;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-detail-list {
            background: #4a4a4a;
            color: #fff;
        }

        .btn-detail-list:hover {
            background: #333;
        }

        .btn-payment-list {
            background: #a97c50;
            color: #fff;
        }

        .btn-payment-list:hover {
            background: #8b5e3c;
        }

        /* ========== MODAL STYLING - EXTRA LARGE ========== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 30px;
            /* ✅ PERBESAR PADDING */
        }

        .modal-overlay.show {
            display: flex;
            opacity: 1;
        }

        .modal-container {
            background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
            border-radius: 25px;
            max-width: 1100px;
            /* ✅ EXTRA LARGE dari 900px */
            width: 95%;
            /* ✅ PERBESAR */
            position: relative;
            transform: scale(0.9) translateY(30px);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-height: 92vh;
            /* ✅ PERBESAR */
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
        }

        .modal-overlay.show .modal-container {
            transform: scale(1) translateY(0);
        }

        /* Modal Header */
        .modal-header {
            background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
            padding: 40px 50px;
            /* ✅ EXTRA LARGE */
            display: flex;
            align-items: center;
            gap: 20px;
            color: #fff;
            position: relative;
        }

        .modal-header i {
            font-size: 2.8em;
            /* ✅ EXTRA LARGE */
            color: #ffd44a;
        }

        .modal-header h2 {
            font-size: 2.2rem;
            /* ✅ EXTRA LARGE */
            margin: 0;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        /* Modal Close Button */
        .modal-close-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            width: 52px;
            /* ✅ EXTRA LARGE */
            height: 52px;
            border-radius: 50%;
            font-size: 1.8rem;
            /* ✅ EXTRA LARGE */
            color: #fff;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .modal-close-btn:hover {
            background: rgba(220, 53, 69, 0.95);
            border-color: #dc3545;
            transform: rotate(90deg) scale(1.15);
        }

        /* Modal Content */
        .modal-content {
            padding: 50px;
            /* ✅ EXTRA LARGE */
            overflow-y: auto;
            flex: 1;
        }

        .modal-content::-webkit-scrollbar {
            width: 12px;
            /* ✅ PERBESAR */
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #a97c50;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #8b5e3c;
        }

        /* Info Group */
        .info-group {
            background: #fff;
            border: 2px solid #e8e8e8;
            /* ✅ PERBESAR BORDER */
            border-radius: 18px;
            /* ✅ PERBESAR */
            margin-bottom: 30px;
            /* ✅ PERBESAR */
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .info-group:hover {
            box-shadow: 0 6px 25px rgba(169, 124, 80, 0.2);
            transform: translateY(-3px);
        }

        .info-group:last-child {
            margin-bottom: 0;
        }

        /* Info Header */
        .info-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f1f2 100%);
            padding: 22px 30px;
            /* ✅ EXTRA LARGE */
            display: flex;
            align-items: center;
            gap: 18px;
            border-bottom: 4px solid #a97c50;
            /* ✅ PERBESAR */
        }

        .info-header i {
            font-size: 2em;
            /* ✅ EXTRA LARGE */
            color: #a97c50;
        }

        .info-header h3 {
            font-size: 1.5rem;
            /* ✅ EXTRA LARGE */
            color: #333;
            margin: 0;
            font-weight: 600;
        }

        /* Info Body */
        .info-body {
            padding: 30px;
            /* ✅ EXTRA LARGE */
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px 0;
            /* ✅ PERBESAR */
            border-bottom: 1px dashed #e0e0e0;
            gap: 25px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            font-size: 1.15em;
            /* ✅ EXTRA LARGE */
            flex-shrink: 0;
        }

        .info-value {
            color: #222;
            font-weight: 500;
            text-align: right;
            font-size: 1.15em;
            /* ✅ EXTRA LARGE */
            line-height: 1.5;
        }

        .info-body p {
            font-size: 1.15em;
            /* ✅ EXTRA LARGE */
            color: #555;
            line-height: 1.9;
            margin: 0;
        }

        /* Button Map */
        .btn-map {
            margin-top: 20px;
            background: linear-gradient(135deg, #333 0%, #000 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 15px 30px;
            /* ✅ EXTRA LARGE */
            border-radius: 12px;
            font-size: 1.15em;
            /* ✅ EXTRA LARGE */
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-map:hover {
            background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(169, 124, 80, 0.4);
        }

        .btn-map i {
            font-size: 1.3em;
        }

        /* Empty State */
        .no-trips {
            text-align: center;
            padding: 60px 20px;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            border-radius: 15px;
            margin-top: 20px;
        }

        .no-trips i {
            font-size: 3em;
            color: #ccc;
            margin-bottom: 15px;
        }

        .explore-btn {
            margin-top: 20px;
            display: inline-block;
            background: #a97c50;
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
        }

        .explore-btn:hover {
            background: #8b5e3c;
        }

        .swal2-popup {
            font-size: clamp(0.85rem, 2vw, 1rem) !important;
            padding: clamp(15px, 3vw, 25px) !important;
            max-width: 1100px !important;
            width: 95% !important;
            border-radius: 20px !important;
        }

        .swal2-title {
            color: #a97c50 !important;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px !important;
            margin-bottom: 15px !important;
            font-size: clamp(1.1rem, 4vw, 1.5rem) !important;
        }

        .swal2-html-container {
            max-height: 70vh !important;
            overflow-y: auto !important;
            margin: 0 !important;
        }

        /* Info Box Group - SAMA SEPERTI PAYMENT-STATUS.PHP */
        .info-box-group {
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: clamp(12px, 2.5vw, 15px);
            background: #fcfcfc;
        }

        .info-box-group h4 {
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: clamp(0.95rem, 2.5vw, 1.1rem);
            border-bottom: 1px dashed #ddd;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box-group h4 i {
            color: #a97c50;
        }

        .info-row-detail {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            flex-wrap: wrap;
            gap: 5px;
        }

        .info-row-detail strong {
            color: #222;
        }

        .info-row-detail span {
            color: #555;
        }

        .total-price-detail {
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            color: #a97c50;
            font-weight: 700;
            margin-top: 8px;
        }

        .btn-map-detail {
            margin-top: 15px;
            background: #333;
            color: #fff;
            padding: clamp(8px, 2vw, 10px) clamp(15px, 3vw, 20px);
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            transition: all 0.3s ease;
        }

        .btn-map-detail:hover {
            background: #000;
        }


        /* ========== RESPONSIVE - MOBILE ========== */
        @media (max-width: 768px) {
            .list-trip-card {
                flex-direction: column;
                align-items: stretch;
            }

            .card-image-box {
                width: 100%;
                height: 150px;
                min-width: 100%;
            }

            .card-info {
                padding: 15px;
            }

            .card-actions-list {
                flex-direction: row;
                border-left: none;
                border-top: 1px solid #f0f0f0;
                padding: 15px;
                min-width: auto;
            }

            .btn-detail-list,
            .btn-payment-list {
                flex: 1;
            }

            /* Modal Responsive */
            .modal-overlay {
                padding: 15px;
            }

            .modal-container {
                max-width: 100%;
                width: 95%;
                max-height: 93vh;
                border-radius: 18px;
            }

            .modal-header {
                padding: 28px 22px;
            }

            .modal-header h2 {
                font-size: 1.5rem;
            }

            .modal-header i {
                font-size: 2em;
            }

            .modal-close-btn {
                top: 22px;
                right: 22px;
                width: 44px;
                height: 44px;
                font-size: 1.5rem;
            }

            .modal-content {
                padding: 28px 22px;
            }

            .info-header {
                padding: 18px 20px;
            }

            .info-header h3 {
                font-size: 1.2rem;
            }

            .info-header i {
                font-size: 1.6em;
            }

            .info-body {
                padding: 22px;
            }

            .info-row {
                flex-direction: column;
                gap: 8px;
                padding: 12px 0;
            }

            .info-value {
                text-align: left;
            }

            .info-label,
            .info-value {
                font-size: 1em;
            }

            .info-body p {
                font-size: 1em;
            }

            .btn-map {
                padding: 12px 24px;
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .modal-header {
                padding: 22px 18px;
            }

            .modal-header h2 {
                font-size: 1.3rem;
            }

            .modal-content {
                padding: 22px 18px;
            }

            .info-header {
                padding: 15px 18px;
            }

            .info-body {
                padding: 18px;
            }
        }
    </style>

</head>

<body>
    <!-- ✅ INCLUDE NAVBAR -->
    <?php include '../navbar.php'; ?>

    <!-- ✅ INCLUDE AUTH MODALS -->
    <?php include '../auth-modals.php'; ?>

    <div class="page-container">
        <main class="my-trips-section">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fa-solid fa-mountain-sun"></i> Paket Trip Saya
                </h1>
                <p class="subtitle">Daftar reservasi dan info penting untuk pendakian Anda.</p>
            </div>

            <div class="trips-list">
                <?php if (empty($myTrips)): ?>
                    <div class="no-trips">
                        <i class="fa-solid fa-box-open"></i>
                        <h2>Oops! Belum Ada Trip</h2>
                        <p>Anda belum memesan paket trip apapun. Yuk, jelajahi penawaran kami!</p>
                        <a href="<?php echo $navbarPath; ?>#paketTrips" class="btn explore-btn">Lihat Paket Trip</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($myTrips as $trip): ?>
                        <div class="list-trip-card" data-status="<?php echo strtolower($trip['status_booking']); ?>">
                            <div class="card-image-box" style="background-image: url('<?php echo htmlspecialchars($trip['gambar']); ?>');">
                                <span class="trip-type-badge"><?php echo htmlspecialchars(ucfirst($trip['jenis_trip'])); ?></span>
                            </div>
                            <div class="card-info">
                                <span class="status-badge status-<?php echo strtolower($trip['status_booking']); ?>">
                                    <?php
                                    $status = strtolower($trip['status_booking']);
                                    if ($status === 'pending') echo '<i class="fa-solid fa-hourglass-half"></i> Menunggu Pembayaran';
                                    else if ($status === 'paid') echo '<i class="fa-solid fa-check-circle"></i> Pembayaran Selesai';
                                    else if ($status === 'cancelled') echo '<i class="fa-solid fa-times-circle"></i> Dibatalkan';
                                    else if ($status === 'finished') echo '<i class="fa-solid fa-flag-checkered"></i> Selesai';
                                    else echo ucwords($trip['status_booking']);
                                    ?>
                                </span>

                                <h3><?php echo htmlspecialchars($trip['nama_gunung']); ?> (Via <?php echo htmlspecialchars($trip['via_gunung']); ?>)</h3>

                                <div class="trip-meta">
                                    <p><i class="fa-solid fa-calendar-alt"></i> Trip: <strong><?php echo date('d M Y', strtotime($trip['tanggal_trip'])); ?></strong> (<?php echo htmlspecialchars($trip['durasi']); ?>)</p>
                                    <p><i class="fa-solid fa-users"></i> Peserta: <strong><?php echo htmlspecialchars($trip['jumlah_orang']); ?> Orang</strong></p>
                                    <p><i class="fa-solid fa-tag"></i> Total: <strong>Rp <?php echo number_format($trip['total_harga'], 0, ',', '.'); ?></strong></p>
                                </div>
                            </div>
                            <div class="card-actions-list">
                                <button class="btn btn-detail-list" onclick='openDetailModal(<?php echo htmlspecialchars(json_encode($trip), ENT_QUOTES, 'UTF-8'); ?>)' title="Lihat Detail & Perlengkapan">
                                    <i class="fa-solid fa-eye"></i> Detail Trip
                                </button>
                                <?php if (strtolower($trip['status_booking']) == 'pending'): ?>
                                    <a href="<?php echo $navbarPath; ?>user/payment-status.php?booking_id=<?php echo $trip['id_booking']; ?>" class="btn btn-payment-list" title="Lanjutkan Proses Pembayaran">
                                        <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Detail Modal -->
    <!-- diload menggunakan js dibawah -->


    <!-- ✅ LOAD JAVASCRIPT FILES -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        // ✅ Modal Functions - MENGGUNAKAN SWEETALERT2 SEPERTI PAYMENT-STATUS.PHP
        function openDetailModal(tripData) {
            // Format status pembayaran
            function formatStatus(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'pending') return '<span style="color: #ffc107;">⏳ Menunggu Pembayaran</span>';
                if (statusLower === 'paid' || statusLower === 'settlement') return '<span style="color: #28a745;">✅ Pembayaran Selesai</span>';
                if (statusLower === 'expired') return '<span style="color: #dc3545;">❌ Kadaluarsa</span>';
                if (statusLower === 'cancelled') return '<span style="color: #dc3545;">❌ Dibatalkan</span>';
                return status;
            }

            // Format tanggal
            const tanggalBooking = new Date(tripData.tanggal_booking).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            const tanggalTrip = new Date(tripData.tanggal_trip).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Generate Google Maps button
            const mapsButton = tripData.link_map && tripData.link_map !== '#' ?
                `<a href="${tripData.link_map}" target="_blank" class="btn-map-detail">
                <i class="fa-solid fa-map-location-dot"></i> Lihat Lokasi Kumpul
            </a>` : '';

            // Build modal content - PERSIS SEPERTI PAYMENT-STATUS.PHP
            const modalContent = `
            <div class="transaction-detail-content" style="text-align: left;">
                <div class="info-box-group">
                    <h4><i class="fa-solid fa-receipt"></i> Ringkasan Transaksi</h4>
                    <div class="info-row-detail"><span>ID Booking:</span> <strong>#${tripData.id_booking}</strong></div>
                    <div class="info-row-detail"><span>Tanggal Pesan:</span> <strong>${tanggalBooking}</strong></div>
                    <div class="info-row-detail"><span>Status Pembayaran:</span> <strong>${formatStatus(tripData.status_booking)}</strong></div>
                    <div class="info-row-detail"><span>Jumlah Peserta:</span> <strong>${tripData.jumlah_orang} Orang</strong></div>
                    <div class="info-row-detail"><span>Total Tagihan:</span> <strong class="total-price-detail">Rp ${parseInt(tripData.total_harga).toLocaleString('id-ID')}</strong></div>
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-mountain"></i> Detail Trip</h4>
                    <div class="info-row-detail"><span>Nama Trip:</span> <strong>${tripData.nama_gunung}</strong></div>
                    <div class="info-row-detail"><span>Via:</span> <strong>${tripData.via_gunung || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Jenis Trip:</span> <strong>${tripData.jenis_trip || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Tanggal Trip:</span> <strong>${tanggalTrip}</strong></div>
                    <div class="info-row-detail"><span>Durasi:</span> <strong>${tripData.durasi || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Waktu Kumpul:</span> <strong>${tripData.waktu_kumpul ? tripData.waktu_kumpul.substring(0, 5) : 'N/A'} WIB</strong></div>
                    <div class="info-row-detail"><span>Lokasi Kumpul:</span> <strong>${tripData.nama_lokasi || 'N/A'}</strong></div>
                    ${mapsButton}
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-clipboard-list"></i> Info Penting</h4>
                    <div style="padding: 5px 0;"><span><strong>Include:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${tripData.include || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Exclude:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${tripData.exclude || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Syarat & Ketentuan:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${tripData.syaratKetentuan || 'N/A'}</small></div>
                </div>
            </div>
        `;

            // Show SweetAlert2 modal
            Swal.fire({
                title: `<i class="fa-solid fa-mountain"></i> Detail Trip ${tripData.nama_gunung}`,
                html: modalContent,
                width: '1100px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    htmlContainer: 'swal2-html-container'
                }
            });
        }
    </script>

</body>

</html>