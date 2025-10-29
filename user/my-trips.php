<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}

$navbarPath = '../';
require_once '../backend/koneksi.php';
$id_user = $_SESSION['id_user'];

$query = "SELECT b.id_booking, b.id_trip, b.jumlah_orang, b.total_harga, b.tanggal_booking, b.status as status_booking,
    t.nama_gunung, t.jenis_trip, t.tanggal as tanggal_trip, t.durasi, t.via_gunung, t.gambar,
    d.nama_lokasi, d.waktu_kumpul, d.link_map, d.include, d.exclude, d.syaratKetentuan,
    p.status_pembayaran
    FROM bookings b JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
    LEFT JOIN payments p ON b.id_booking = p.id_booking
    WHERE b.id_user = ? ORDER BY b.tanggal_booking DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

$myTrips = [];
while ($row = $result->fetch_assoc()) {
    $finalStatus = $row['status_booking'];
    if ($row['status_pembayaran'] === 'paid') $finalStatus = 'paid';
    elseif ($row['status_booking'] === 'confirmed') $finalStatus = 'paid';
    elseif ($row['status_booking'] === 'cancelled') $finalStatus = 'cancelled';
    elseif ($row['status_booking'] === 'finished') $finalStatus = 'finished';

    $imagePath = $navbarPath . 'img/default-mountain.jpg';
    if (!empty($row['gambar'])) {
        $imagePath = (strpos($row['gambar'], 'img/') === 0) ? $navbarPath . $row['gambar'] : $navbarPath . 'img/' . $row['gambar'];
    }

    $myTrips[] = [
        'id_booking' => $row['id_booking'],
        'id_trip' => $row['id_trip'],
        'nama_gunung' => $row['nama_gunung'],
        'jenis_trip' => $row['jenis_trip'],
        'tanggal_trip' => $row['tanggal_trip'],
        'durasi' => $row['durasi'] ?? '1 hari',
        'via_gunung' => $row['via_gunung'] ?? 'Via Utama',
        'nama_lokasi' => $row['nama_lokasi'] ?? 'Lokasi belum ditentukan',
        'tanggal_booking' => $row['tanggal_booking'],
        'jumlah_orang' => $row['jumlah_orang'],
        'total_harga' => $row['total_harga'],
        'status_booking' => $finalStatus,
        'gambar' => $imagePath,
        'include' => $row['include'] ?? 'Info akan diupdate',
        'exclude' => $row['exclude'] ?? 'Info akan diupdate',
        'syaratKetentuan' => $row['syaratKetentuan'] ?? 'Info akan diupdate',
        'waktu_kumpul' => $row['waktu_kumpul'] ?? '00:00',
        'link_map' => $row['link_map'] ?? '#',
    ];
}
$stmt->close();

$totalTrips = count($myTrips);
$pendingCount = count(array_filter($myTrips, fn($t) => $t['status_booking'] === 'pending'));
$paidCount = count(array_filter($myTrips, fn($t) => $t['status_booking'] === 'paid'));
$finishedCount = count(array_filter($myTrips, fn($t) => $t['status_booking'] === 'finished'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - Majelis MDPL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
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
            padding-top: 80px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        /* Header Compact */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(169, 124, 80, 0.15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .title {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3D2F21 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .title i {
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 0.8rem;
            color: #6B5847;
            margin-bottom: 15px;
        }

        /* Stats Mini */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .stat {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.8) 100%);
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid rgba(169, 124, 80, 0.1);
            transition: all 0.3s ease;
        }

        .stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(169, 124, 80, 0.12);
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin: 0 auto 8px;
        }

        .stat.total .stat-icon {
            background: rgba(169, 124, 80, 0.1);
            color: #a97c50;
        }

        .stat.pending .stat-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .stat.paid .stat-icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stat.finished .stat-icon {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #3D2F21;
            line-height: 1;
            margin-bottom: 3px;
        }

        .stat-label {
            font-size: 0.65rem;
            color: #6B5847;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Trips Grid Mini */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3D2F21;
            margin: 20px 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, rgba(169, 124, 80, 0.2), transparent);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(169, 124, 80, 0.12);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .card-img {
            height: 140px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .card-img::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.6) 100%);
        }

        .badge {
            position: absolute;
            top: 8px;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            z-index: 1;
        }

        .badge-type {
            left: 8px;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
        }

        .badge-status {
            right: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            color: #333;
        }

        .status-paid {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #fff;
        }

        .status-finished {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: #fff;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #3D2F21;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .card-via {
            color: #6B5847;
            font-size: 0.75rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
            padding: 10px;
            background: rgba(169, 124, 80, 0.04);
            border-radius: 8px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #6B5847;
        }

        .meta-item i {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: rgba(169, 124, 80, 0.08);
            color: #a97c50;
            font-size: 0.7rem;
        }

        .meta-item strong {
            color: #3D2F21;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            flex: 1;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        .btn-detail {
            background: linear-gradient(135deg, #4a4a4a 0%, #2d2d2d 100%);
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        }

        .btn-payment {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            box-shadow: 0 2px 10px rgba(169, 124, 80, 0.25);
        }

        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(169, 124, 80, 0.4);
        }

        /* Empty State */
        .empty {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            border: 2px dashed rgba(169, 124, 80, 0.2);
        }

        .empty i {
            font-size: 3rem;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .empty h2 {
            font-size: 1.4rem;
            color: #3D2F21;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .empty p {
            color: #6B5847;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .btn-explore {
            padding: 10px 28px;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(169, 124, 80, 0.25);
            text-transform: uppercase;
        }

        .btn-explore:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(169, 124, 80, 0.4);
        }

        /* Modal Compact */
        .swal2-popup {
            font-size: 0.85rem !important;
            padding: 0 !important;
            max-width: 700px !important;
            width: 90% !important;
            border-radius: 15px !important;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2) !important;
        }

        .swal2-title {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff !important;
            padding: 18px 25px !important;
            margin: 0 !important;
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            border-radius: 15px 15px 0 0 !important;
        }

        .swal2-html-container {
            max-height: 60vh !important;
            overflow-y: auto !important;
            margin: 0 !important;
            padding: 20px 25px !important;
        }

        .swal2-close {
            font-size: 1.8rem !important;
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .info-group {
            margin-bottom: 15px;
            border: 1px solid rgba(169, 124, 80, 0.12);
            border-radius: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.4);
        }

        .info-group h4 {
            color: #3D2F21;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border-bottom: 2px solid rgba(169, 124, 80, 0.12);
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.8rem;
            border-bottom: 1px dashed rgba(169, 124, 80, 0.08);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row span {
            color: #6B5847;
        }

        .info-row strong {
            color: #3D2F21;
            font-weight: 600;
        }

        .price-total {
            font-size: 1.2rem;
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .btn-map {
            margin-top: 12px;
            background: linear-gradient(135deg, #333 0%, #000 100%);
            color: #fff;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
            text-transform: uppercase;
        }

        .btn-map:hover {
            background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }

            .container {
                padding: 15px 12px;
            }

            .header {
                padding: 15px;
                border-radius: 12px;
            }

            .title {
                font-size: 1.3rem;
                flex-wrap: wrap;
                justify-content: center;
                text-align: center;
            }

            .subtitle {
                text-align: center;
                font-size: 0.75rem;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .info-row {
                flex-direction: column;
                gap: 3px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div class="container">
        <div class="header">
            <h1 class="title"><i class="fa-solid fa-mountain-sun"></i><span>My Trips</span></h1>
            <p class="subtitle">Kelola reservasi pendakian Anda</p>
            <div class="stats">
                <div class="stat total">
                    <div class="stat-icon"><i class="fa-solid fa-mountain"></i></div>
                    <div class="stat-value"><?= $totalTrips ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat pending">
                    <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="stat-value"><?= $pendingCount ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat paid">
                    <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
                    <div class="stat-value"><?= $paidCount ?></div>
                    <div class="stat-label">Paid</div>
                </div>
                <div class="stat finished">
                    <div class="stat-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="stat-value"><?= $finishedCount ?></div>
                    <div class="stat-label">Done</div>
                </div>
            </div>
        </div>

        <?php if (empty($myTrips)): ?>
            <div class="empty">
                <i class="fa-solid fa-mountain"></i>
                <h2>Belum Ada Trip</h2>
                <p>Mulai petualangan Anda!</p>
                <a href="<?= $navbarPath; ?>index.php#paketTrips" class="btn-explore">
                    <i class="fa-solid fa-compass"></i> Jelajahi
                </a>
            </div>
        <?php else: ?>
            <h2 class="section-title"><i class="fa-solid fa-list"></i> Trip Anda</h2>
            <div class="grid">
                <?php foreach ($myTrips as $trip): ?>
                    <div class="card">
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars($trip['gambar']); ?>');">
                            <span class="badge badge-type"><?= htmlspecialchars($trip['jenis_trip']); ?></span>
                            <span class="badge badge-status status-<?= strtolower($trip['status_booking']); ?>">
                                <?php
                                $s = strtolower($trip['status_booking']);
                                if ($s === 'pending') echo '<i class="fa-solid fa-hourglass-half"></i> Pending';
                                elseif ($s === 'paid') echo '<i class="fa-solid fa-check-circle"></i> Paid';
                                elseif ($s === 'cancelled') echo '<i class="fa-solid fa-times-circle"></i> Cancelled';
                                elseif ($s === 'finished') echo '<i class="fa-solid fa-flag-checkered"></i> Done';
                                ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($trip['nama_gunung']); ?></h3>
                            <p class="card-via"><i class="fa-solid fa-route"></i> <?= htmlspecialchars($trip['via_gunung']); ?></p>
                            <div class="card-meta">
                                <div class="meta-item"><i class="fa-solid fa-calendar-alt"></i><span><strong><?= date('d M Y', strtotime($trip['tanggal_trip'])); ?></strong></span></div>
                                <div class="meta-item"><i class="fa-solid fa-clock"></i><span><?= htmlspecialchars($trip['durasi']); ?></span></div>
                                <div class="meta-item"><i class="fa-solid fa-users"></i><span><?= $trip['jumlah_orang']; ?> Orang</span></div>
                                <div class="meta-item"><i class="fa-solid fa-tag"></i><span><strong>Rp <?= number_format($trip['total_harga'], 0, ',', '.'); ?></strong></span></div>
                            </div>
                            <div class="actions">
                                <button class="btn btn-detail" onclick='openDetail(<?= htmlspecialchars(json_encode($trip), ENT_QUOTES); ?>)'>
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                                <?php if ($trip['status_booking'] == 'pending'): ?>
                                    <a href="<?= $navbarPath; ?>user/payment-status.php?booking_id=<?= $trip['id_booking']; ?>" class="btn btn-payment">
                                        <i class="fa-solid fa-credit-card"></i> Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>
    <script>
        function openDetail(d) {
            const fmt = s => {
                if (s === 'pending') return '<span style="color:#ffc107;font-weight:700">⏳ Pending</span>';
                if (s === 'paid') return '<span style="color:#28a745;font-weight:700">✅ Lunas</span>';
                return s;
            };
            const tBook = new Date(d.tanggal_booking).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            const tTrip = new Date(d.tanggal_trip).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            const maps = d.link_map && d.link_map !== '#' ? `<a href="${d.link_map}" target="_blank" class="btn-map"><i class="fa-solid fa-map-location-dot"></i> Maps</a>` : '';

            Swal.fire({
                title: `<i class="fa-solid fa-info-circle"></i> ${d.nama_gunung}`,
                html: `<div style="text-align:left">
                    <div class="info-group">
                        <h4><i class="fa-solid fa-receipt"></i> Ringkasan</h4>
                        <div class="info-row"><span>ID:</span><strong>#${d.id_booking}</strong></div>
                        <div class="info-row"><span>Tanggal:</span><strong>${tBook}</strong></div>
                        <div class="info-row"><span>Status:</span>${fmt(d.status_booking)}</div>
                        <div class="info-row"><span>Peserta:</span><strong>${d.jumlah_orang} Orang</strong></div>
                        <div class="info-row"><span>Total:</span><strong class="price-total">Rp ${parseInt(d.total_harga).toLocaleString('id-ID')}</strong></div>
                    </div>
                    <div class="info-group">
                        <h4><i class="fa-solid fa-mountain"></i> Detail Trip</h4>
                        <div class="info-row"><span>Gunung:</span><strong>${d.nama_gunung}</strong></div>
                        <div class="info-row"><span>Via:</span><strong>${d.via_gunung}</strong></div>
                        <div class="info-row"><span>Tanggal:</span><strong>${tTrip}</strong></div>
                        <div class="info-row"><span>Durasi:</span><strong>${d.durasi}</strong></div>
                        <div class="info-row"><span>Kumpul:</span><strong>${d.waktu_kumpul.substring(0,5)} WIB</strong></div>
                        <div class="info-row"><span>Lokasi:</span><strong>${d.nama_lokasi}</strong></div>
                        ${maps}
                    </div>
                    <div class="info-group">
                        <h4><i class="fa-solid fa-clipboard-list"></i> Info</h4>
                        <div style="padding:6px 0"><strong>Include:</strong><br><small style="margin-left:10px;display:block;margin-top:4px;color:#6B5847">${d.include}</small></div>
                        <div style="padding:6px 0"><strong>Exclude:</strong><br><small style="margin-left:10px;display:block;margin-top:4px;color:#6B5847">${d.exclude}</small></div>
                        <div style="padding:6px 0"><strong>S&K:</strong><br><small style="margin-left:10px;display:block;margin-top:4px;color:#6B5847">${d.syaratKetentuan}</small></div>
                    </div>
                </div>`,
                width: '700px',
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    </script>
</body>

</html>