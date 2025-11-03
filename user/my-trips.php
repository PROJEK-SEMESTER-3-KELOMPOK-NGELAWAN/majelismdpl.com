<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}

$navbarPath = '../';
require_once '../backend/koneksi.php';
$id_user = $_SESSION['id_user'];

/*
 Ambil data booking + status trip aktual:
 - status_booking: status proses booking user (pending/paid/cancelled/finished/confirmed)
 - status_pembayaran: paid/unpaid (payments)
 - status_trip: status paket dari admin (available/sold/done)
*/
$query = "SELECT 
            b.id_booking,
            b.id_trip,
            b.jumlah_orang,
            b.total_harga,
            b.tanggal_booking,
            b.status AS status_booking,
            t.nama_gunung,
            t.jenis_trip,
            t.tanggal AS tanggal_trip,
            t.durasi,
            t.via_gunung,
            t.gambar,
            t.status AS status_trip,
            d.nama_lokasi,
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
          ORDER BY b.tanggal_booking DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

$myTrips = [];
while ($row = $result->fetch_assoc()) {
    $statusBookingRaw = strtolower(trim($row['status_booking'] ?? ''));
    $statusPembayaran = strtolower(trim($row['status_pembayaran'] ?? ''));
    $statusTripRaw    = strtolower(trim($row['status_trip'] ?? ''));

    // Status Booking (tidak dipaksa jadi selesai)
    if ($statusBookingRaw === 'finished') {
        $finalBookingStatus = 'finished';
    } elseif ($statusBookingRaw === 'cancelled') {
        $finalBookingStatus = 'cancelled';
    } elseif ($statusPembayaran === 'paid') {
        $finalBookingStatus = 'paid';
    } elseif ($statusBookingRaw === 'confirmed') {
        $finalBookingStatus = 'confirmed';
    } else {
        $finalBookingStatus = 'pending';
    }

    // Status Trip (hanya 'done' yang ditandai di UI)
    $finalTripStatus = in_array($statusTripRaw, ['available', 'sold', 'done'], true) ? $statusTripRaw : 'available';

    // Gambar
    $imagePath = $navbarPath . 'img/default-mountain.jpg';
    if (!empty($row['gambar'])) {
        $imagePath = (strpos($row['gambar'], 'img/') === 0)
            ? $navbarPath . $row['gambar']
            : $navbarPath . 'img/' . $row['gambar'];
    }
    
    // Tentukan status pembayaran untuk diparse di JavaScript
    $paymentStatusForJS = $row['status_pembayaran'] ?? ($finalBookingStatus === 'paid' ? 'settlement' : ($finalBookingStatus === 'cancelled' ? 'cancel' : 'pending'));


    $myTrips[] = [
        'id_booking'        => $row['id_booking'],
        'id_trip'           => $row['id_trip'],
        'nama_gunung'       => $row['nama_gunung'],
        'jenis_trip'        => $row['jenis_trip'],
        'tanggal_trip'      => $row['tanggal_trip'],
        'durasi'            => $row['durasi'] ?? '1 hari',
        'via_gunung'        => $row['via_gunung'] ?? 'Via Utama',
        'nama_lokasi'       => $row['nama_lokasi'] ?? 'Lokasi belum ditentukan',
        'tanggal_booking'   => $row['tanggal_booking'],
        'jumlah_orang'      => $row['jumlah_orang'],
        'total_harga'       => $row['total_harga'],
        'status_booking'    => $finalBookingStatus,
        'status_trip'       => $finalTripStatus,
        'gambar'            => $imagePath,
        'include'           => $row['include'] ?? 'Informasi akan diperbarui',
        'exclude'           => $row['exclude'] ?? 'Informasi akan diperbarui',
        'syaratKetentuan'   => $row['syaratKetentuan'] ?? 'Informasi akan diperbarui',
        'waktu_kumpul'      => $row['waktu_kumpul'] ?? '00:00',
        'link_map'          => $row['link_map'] ?? '#',
        'status_pembayaran' => $paymentStatusForJS // Kirim status pembayaran yang relevan
    ];
}
$stmt->close();

// Statistik header:
$totalTrips     = count($myTrips);
$pendingCount   = count(array_filter($myTrips, fn($t) => strtolower($t['status_booking']) === 'pending'));
$paidCount      = count(array_filter($myTrips, fn($t) => strtolower($t['status_booking']) === 'paid' || strtolower($t['status_booking']) === 'confirmed'));
$finishedCount  = count(array_filter($myTrips, fn($t) => strtolower($t['status_trip']) === 'done'));

/**
 * FUNGSI INI HANYA UNTUK MERENDER STATUS PEMBAYARAN DI DALAM MODAL
 * (Karena kita ingin tampilan modal sama dengan payment-status.php)
 */
function format_status_detail($status)
{
    $s = strtolower($status ?? '');
    
    $status_data = [
        'text' => 'DIBATALKAN',
        'color' => '#c62828',
        'icon' => '<i class="fa-solid fa-times-circle"></i>'
    ];

    if ($s === 'paid' || $s === 'settlement' || $s === 'confirmed') {
        $status_data['text'] = 'PEMBAYARAN DITERIMA'; 
        $status_data['color'] = '#2e7d32';
        $status_data['icon'] = '<i class="fa-solid fa-check-circle"></i>';
    } elseif ($s === 'pending') {
        $status_data['text'] = 'MENUNGGU PEMBAYARAN'; 
        $status_data['color'] = '#e65100';
        $status_data['icon'] = '<i class="fa-solid fa-hourglass-half"></i>';
    } elseif ($s === 'expire') {
        $status_data['text'] = 'SUDAH KEDALUWARSA'; 
        $status_data['color'] = '#ad1457';
        $status_data['icon'] = '<i class="fa-solid fa-clock-rotate-left"></i>';
    } elseif ($s === 'cancelled' || $s === 'cancel' || $s === 'failed') {
        $status_data['text'] = 'DIBATALKAN'; 
        $status_data['color'] = '#dc3545'; 
        $status_data['icon'] = '<i class="fa-solid fa-ban"></i>';
    }
    
    return "<span style='color:{$status_data['color']};font-weight:700;'>{$status_data['icon']} {$status_data['text']}</span>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Saya - Majelis MDPL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8dcc4 100%);
            min-height: 100vh;
            padding-top: 80px
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 15px
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(20px);
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid rgba(169, 124, 80, .2);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08)
        }

        .title {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3D2F21 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 12px
        }

        .title i {
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.8rem
        }

        .subtitle {
            font-size: .85rem;
            color: #6B5847;
            margin-bottom: 20px;
            font-weight: 500
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px
        }

        .stat {
            background: linear-gradient(135deg, rgba(255, 255, 255, .8) 0%, rgba(255, 255, 255, .95) 100%);
            border-radius: 12px;
            padding: 16px 12px;
            text-align: center;
            border: 2px solid rgba(169, 124, 80, .25);
            transition: all .4s cubic-bezier(.34, 1.56, .64, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06)
        }

        .stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .4), transparent);
            transition: left .5s
        }

        .stat:hover::before {
            left: 100%
        }

        .stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(169, 124, 80, .2);
            border-color: rgba(169, 124, 80, .4)
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 10px;
            transition: all .3s ease
        }

        .stat:hover .stat-icon {
            transform: scale(1.1) rotate(-5deg)
        }

        .stat.total .stat-icon {
            background: linear-gradient(135deg, rgba(169, 124, 80, .15) 0%, rgba(212, 165, 116, .2) 100%);
            color: #a97c50
        }

        .stat.pending .stat-icon {
            background: linear-gradient(135deg, rgba(255, 193, 7, .15) 0%, rgba(255, 193, 7, .2) 100%);
            color: #ffc107
        }

        .stat.paid .stat-icon {
            background: linear-gradient(135deg, rgba(40, 167, 69, .15) 0%, rgba(40, 167, 69, .2) 100%);
            color: #28a745
        }

        .stat.finished .stat-icon {
            background: linear-gradient(135deg, rgba(108, 117, 125, .15) 0%, rgba(108, 117, 125, .2) 100%);
            color: #6c757d
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #3D2F21;
            line-height: 1;
            margin-bottom: 4px
        }

        .stat-label {
            font-size: .7rem;
            color: #6B5847;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        /* Grid kartu */
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #3D2F21;
            margin: 25px 0 15px;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, rgba(169, 124, 80, .3), transparent)
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px
        }

        .card {
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(169, 124, 80, .15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            transition: all .4s cubic-bezier(.34, 1.56, .64, 1);
            position: relative
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .15);
            border-color: rgba(169, 124, 80, .3)
        }

        .card-img {
            height: 170px;
            background-size: cover;
            background-position: center;
            position: relative
        }

        .card-img::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, .7) 100%)
        }

        /* Badge status booking (atas kanan) */
        .badge {
            position: absolute;
            top: 10px;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 700;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .3);
            z-index: 2;
            /* Kembali ke tampilan asal: flex-row */
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .badge-status {
            right: 10px;
        }
        
        .badge-status i {
            font-size: 0.8rem;
        }
        /* Akhir Kembali ke tampilan asal */

        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            color: #333
        }

        .status-paid, .status-confirmed {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff
        }

        .status-cancelled {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #fff
        }

        .status-finished {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: #fff
        }

        /* Overlay stempel DONE (hanya saat status_trip=done) */
        .done-stamp {
            position: absolute;
            z-index: 3;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            width: min(72%, 340px);
            opacity: .9;
            pointer-events: none;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .25));
        }

        .card-body {
            padding: 16px
        }
        
        /* Tata Letak Judul & Tanggal */
        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .card-title {
            font-size: 1.08rem;
            font-weight: 800;
            color: #3D2F21;
            line-height: 1.28;
            margin: 0;
            flex-grow: 1;
        }
        
        .card-date {
            font-size: .8rem;
            font-weight: 600;
            color: #3D2F21;
            white-space: nowrap;
            padding-left: 10px;
            text-align: right;
        }
        
        .card-date i {
            margin-right: 4px;
            color: #d4a574;
        }
        
        .card-via {
            color: #6B5847;
            font-size: .8rem;
            margin-bottom: 12px;
            font-weight: 500
        }

        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
            padding: 12px;
            background: linear-gradient(135deg, rgba(169, 124, 80, .05) 0%, rgba(212, 165, 116, .08) 100%);
            border-radius: 12px;
            border: 1px solid rgba(169, 124, 80, .1)
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: #6B5847
        }

        .meta-item i {
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(169, 124, 80, .12);
            color: #a97c50;
            font-size: .72rem
        }

        .meta-item strong {
            color: #3D2F21;
            font-weight: 700
        }
        
        .meta-item.type-badge strong {
            font-weight: 700;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .actions {
            display: flex;
            gap: 8px
        }

        .btn {
            flex: 1;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 800;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: .5px;
            transition: all .3s ease
        }

        .btn-detail {
            background: linear-gradient(135deg, #4a4a4a 0%, #2d2d2d 100%);
            color: #fff;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .2)
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 18px rgba(0, 0, 0, .3)
        }

        .btn-payment {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            box-shadow: 0 3px 12px rgba(169, 124, 80, .3)
        }

        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 18px rgba(169, 124, 80, .5)
        }

        /* ----------------------------------------------------- */
        /* CSS KHUSUS UNTUK TAMPILAN MODAL DETAIL (dari payment-status.php) */
        
        .swal2-popup {
            font-size: 0.85rem !important;
            padding: 0 !important;
            max-width: 700px !important;
            width: 90% !important;
            border-radius: 15px !important;
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

        .info-group-modal {
            margin-bottom: 15px;
            border: 1px solid rgba(169, 124, 80, 0.12);
            border-radius: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.4);
        }

        .info-group-modal h4 {
            color: #3D2F21;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 1rem;
            border-bottom: 2px solid rgba(169, 124, 80, 0.12);
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row-modal {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.8rem;
            border-bottom: 1px dashed rgba(169, 124, 80, 0.08);
        }

        .info-row-modal:last-child {
            border-bottom: none;
        }

        .info-row-modal span {
            color: #6B5847;
        }

        .info-row-modal strong {
            color: #3D2F21;
            font-weight: 600;
        }

        .price-total-modal {
            font-size: 1.2rem;
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            font-weight: 800;
        }
        
        .btn-map-modal {
            background: linear-gradient(135deg, #000 0%, #4a4a4a 100%);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .2);
            text-transform: uppercase;
        }
        /* ----------------------------------------------------- */

        /* Responsif */
        @media (max-width: 768px) {
            body {
                padding-top: 70px
            }

            .container {
                padding: 15px 10px
            }

            .stats {
                grid-template-columns: repeat(2, 1fr)
            }

            .grid {
                grid-template-columns: 1fr
            }

            .card-img {
                height: 180px
            }

            .done-stamp {
                width: min(78%, 320px);
                top: 54%
            }
        }

        @media (min-width: 769px) and (max-width: 1200px) {
            .container {
                max-width: 1000px
            }

            .grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .done-stamp {
                width: min(74%, 330px)
            }
        }

        @media (min-width: 1201px) {
            .grid {
                grid-template-columns: repeat(3, 1fr)
            }
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div class="container">
        <div class="header">
            <h1 class="title"><i class="fa-solid fa-mountain-sun"></i><span>Trip Saya</span></h1>
            <p class="subtitle">Kelola pemesanan petualangan pendakian Anda</p>
            <div class="stats">
                <div class="stat total">
                    <div class="stat-icon"><i class="fa-solid fa-mountain"></i></div>
                    <div class="stat-value"><?= $totalTrips ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat pending">
                    <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="stat-value"><?= $pendingCount ?></div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat paid">
                    <div class="stat-icon"><i class="fa-solid fa-credit-card"></i></div>
                    <div class="stat-value"><?= $paidCount ?></div>
                    <div class="stat-label">Dibayar</div>
                </div>
                <div class="stat finished">
                    <div class="stat-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="stat-value"><?= $finishedCount ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>
        </div>

        <?php if (empty($myTrips)): ?>
            <div class="empty">
                <i class="fa-solid fa-mountain"></i>
                <h2>Belum Ada Trip</h2>
                <p>Mulai petualangan gunung Anda hari ini!</p>
                <a href="<?= $navbarPath; ?>index.php#paketTrips" class="btn-explore">
                    <i class="fa-solid fa-compass"></i> Jelajahi Trip
                </a>
            </div>
        <?php else: ?>
            <h2 class="section-title"><i class="fa-solid fa-list"></i> Daftar Trip Anda</h2>
            <div class="grid">
                <?php foreach ($myTrips as $trip): ?>
                    <div class="card">
                        <div class="card-img" style="background-image: url('<?= htmlspecialchars($trip['gambar']); ?>');">
                            <span class="badge badge-status status-<?= strtolower($trip['status_booking']); ?>" style="top:10px;right:10px">
                                <?php
                                $sb = strtolower($trip['status_booking']);
                                if ($sb === 'pending')      echo '<i class="fa-solid fa-hourglass-half"></i> Menunggu';
                                elseif ($sb === 'paid' || $sb === 'confirmed') echo '<i class="fa-solid fa-credit-card"></i> Dibayar';
                                elseif ($sb === 'finished')  echo '<i class="fa-solid fa-flag-checkered"></i> Selesai';
                                elseif ($sb === 'cancelled') echo '<i class="fa-solid fa-times-circle"></i> Dibatalkan';
                                else echo '<i class="fa-solid fa-info-circle"></i> ' . htmlspecialchars(ucfirst($trip['status_booking']));
                                ?>
                            </span>

                            <?php if (strtolower($trip['status_trip']) === 'done'): ?>
                                <img src="<?= $navbarPath ?>assets/completed-stamp.png" alt="Completed Stamp" class="done-stamp" />
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="card-header-flex">
                                <h3 class="card-title"><?= htmlspecialchars($trip['nama_gunung']); ?></h3>
                                <p class="card-date"><i class="fa-solid fa-calendar-alt"></i> <strong><?= date('d M Y', strtotime($trip['tanggal_trip'])); ?></strong></p>
                            </div>
                            <p class="card-via"><i class="fa-solid fa-route"></i> <?= htmlspecialchars($trip['via_gunung']); ?></p>
                            <div class="card-meta">
                                <div class="meta-item type-badge"><i class="fa-solid fa-tag"></i><span>Tipe: <strong><?= htmlspecialchars($trip['jenis_trip']); ?></strong></span></div>
                                <div class="meta-item"><i class="fa-solid fa-clock"></i><span><?= htmlspecialchars($trip['durasi']); ?></span></div>
                                <div class="meta-item"><i class="fa-solid fa-users"></i><span><?= $trip['jumlah_orang']; ?> Orang</span></div>
                                <div class="meta-item"><i class="fa-solid fa-tag"></i><span>Total Harga: <strong>Rp <?= number_format($trip['total_harga'], 0, ',', '.'); ?></strong></span></div>
                            </div>
                            <div class="actions">
                                <button class="btn btn-detail" onclick='openDetail(<?= htmlspecialchars(json_encode($trip), ENT_QUOTES); ?>)'>
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                                <?php if (strtolower($trip['status_booking']) === 'pending'): ?>
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
    <script>
        // FUNGSI JS untuk memformat status di modal (agar konsisten dengan payment-status.php)
        const formatStatusDetailJs = (s) => {
            let status_text_detail = 'DIBATALKAN';
            let status_color = '#dc3545';
            let status_icon = '<i class="fa-solid fa-ban"></i>';

            if (s === 'paid' || s === 'settlement' || s === 'confirmed') {
                status_text_detail = 'PEMBAYARAN DITERIMA'; 
                status_color = '#2e7d32';
                status_icon = '<i class="fa-solid fa-check-circle"></i>';
            } else if (s === 'pending') {
                status_text_detail = 'MENUNGGU PEMBAYARAN'; 
                status_color = '#e65100';
                status_icon = '<i class="fa-solid fa-hourglass-half"></i>';
            } else if (s === 'expire') {
                status_text_detail = 'SUDAH KEDALUWARSA'; 
                status_color = '#ad1457';
                status_icon = '<i class="fa-solid fa-clock-rotate-left"></i>';
            } else if (s === 'cancelled' || s === 'cancel' || s === 'failed') {
                status_text_detail = 'DIBATALKAN'; 
                status_color = '#dc3545'; 
                status_icon = '<i class="fa-solid fa-ban"></i>';
            }
            
            return `<span style="color:${status_color};font-weight:700;">${status_icon} ${status_text_detail}</span>`;
        };

        // FUNGSI openDetail YANG MENGGUNAKAN TAMPILAN MODAL YANG KONSISTEN
        function openDetail(d) {
            // Menggunakan status pembayaran dari data trip
            const statusLabelHTML = formatStatusDetailJs(d.status_pembayaran);

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
            
            const mapsButton = d.link_map && d.link_map !== '#' ?
                `<div style="text-align:center;padding-top:10px;border-top:1px solid rgba(169, 124, 80, 0.08);">
                    <a href="${d.link_map}" target="_blank" class="btn-map-modal">
                        <i class="fa-solid fa-map-location-dot"></i> Lihat Lokasi Kumpul
                    </a>
                </div>` : '';

            Swal.fire({
                title: `<i class="fa-solid fa-route"></i> Detail Trip Anda`,
                html: `<div style="text-align:left">
                    <div class="info-group-modal">
                        <h4><i class="fa-solid fa-receipt"></i> Ringkasan Pemesanan</h4>
                        <div class="info-row-modal"><span>Nama Trip:</span><strong>${d.nama_gunung} (${d.jenis_trip})</strong></div>
                        <div class="info-row-modal"><span>ID Pemesanan:</span><strong>#${d.id_booking}</strong></div>
                        <div class="info-row-modal"><span>Tanggal Pesan:</span><strong>${tBook}</strong></div>
                        <div class="info-row-modal"><span>Status Pembayaran:</span>${statusLabelHTML}</div>
                        <div class="info-row-modal"><span>Peserta:</span><strong>${d.jumlah_orang} Orang</strong></div>
                        <div class="info-row-modal"><span>Total Harga:</span><strong class="price-total-modal">Rp ${parseInt(d.total_harga).toLocaleString('id-ID')}</strong></div>
                    </div>
                    
                    <div class="info-group-modal">
                        <h4><i class="fa-solid fa-calendar-check"></i> Detail Pelaksanaan Trip</h4>
                        <div class="info-row-modal"><span>Tanggal Trip:</span><strong>${tTrip}</strong></div>
                        <div class="info-row-modal"><span>Via/Jalur:</span><strong>${d.via_gunung}</strong></div>
                        <div class="info-row-modal"><span>Durasi:</span><strong>${d.durasi}</strong></div>
                        <div class="info-row-modal"><span>Waktu Kumpul:</span><strong>${(d.waktu_kumpul || '00:00').substring(0,5)} WIB</strong></div>
                        <div class="info-row-modal"><span>Lokasi Kumpul:</span><strong>${d.nama_lokasi}</strong></div>
                    </div>
                    
                    ${mapsButton}

                    <div class="info-group-modal">
                        <h4><i class="fa-solid fa-list-check"></i> Informasi Biaya & Ketentuan</h4>
                        <div style="padding:6px 0"><strong>Include:</strong><br><small style="display:block;margin-top:5px;color:#6B5847;line-height:1.5">${d.include}</small></div>
                        <div style="padding:6px 0"><strong>Exclude:</strong><br><small style="display:block;margin-top:5px;color:#6B5847;line-height:1.5">${d.exclude}</small></div>
                        <div style="padding:6px 0; border-top: 1px dashed rgba(169, 124, 80, 0.08); margin-top: 10px;"><strong>Syarat & Ketentuan:</strong><br><small style="display:block;margin-top:5px;color:#6B5847;line-height:1.5">${d.syaratKetentuan}</small></div>
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