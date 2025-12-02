<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: ' . getPageUrl('index.php'));
    exit;
}

$navbarPath = '../';
require_once '../backend/koneksi.php';
$id_user = $_SESSION['id_user'];

// Query data booking
$query = "SELECT 
            b.id_booking, b.id_trip, b.jumlah_orang, b.total_harga, b.tanggal_booking, b.status AS status_booking,
            t.nama_gunung, t.jenis_trip, t.tanggal AS tanggal_trip, t.durasi, t.via_gunung, t.gambar, t.status AS status_trip,
            d.nama_lokasi, d.waktu_kumpul, d.link_map, d.include, d.exclude, d.syaratKetentuan,
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

    $finalTripStatus = in_array($statusTripRaw, ['available', 'sold', 'done'], true) ? $statusTripRaw : 'available';

    $imagePath = $navbarPath . 'img/default-mountain.jpg';
    if (!empty($row['gambar'])) {
        $imagePath = (strpos($row['gambar'], 'img/') === 0) ? $navbarPath . $row['gambar'] : $navbarPath . 'img/' . $row['gambar'];
    }

    $myTrips[] = array_merge($row, [
        'status_booking' => $finalBookingStatus,
        'status_trip' => $finalTripStatus,
        'gambar' => $imagePath
    ]);
}
$stmt->close();

// Statistik
$totalTrips     = count($myTrips);
$pendingCount   = count(array_filter($myTrips, fn($t) => strtolower($t['status_booking']) === 'pending'));
$paidCount      = count(array_filter($myTrips, fn($t) => in_array(strtolower($t['status_booking']), ['paid', 'confirmed'])));
$finishedCount  = count(array_filter($myTrips, fn($t) => strtolower($t['status_trip']) === 'done'));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Saya | Majelis MDPL</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #9C7E5C;
            --primary-dark: #7B5E3A;
            --primary-soft: #EFEBE9;
            --bg-page: #FDFBF9;
            --surface: #FFFFFF;
            --text-main: #374151;
            --text-muted: #9CA3AF;
            --radius-lg: 20px;
            --radius-md: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: var(--bg-page);
            color: var(--text-main);
            padding-top: 120px;
            min-height: 100vh;
        }

        .navbar {
            font-family: 'Poppins', sans-serif !important;
        }

        .fa,
        .fas,
        .fa-solid,
        .fa-regular,
        .fa-brands {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }

        .page-decor::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 0;
            width: 100%;
            height: 500px;
            background: linear-gradient(180deg, #F3ECE7 0%, rgba(253, 251, 249, 0) 100%);
            z-index: -1;
            pointer-events: none;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px 60px;
            position: relative;
            z-index: 2;
        }

        /* --- STATS --- */
        .stats-section {
            margin-bottom: 40px;
        }

        .stats-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .stat-box {
            background: var(--surface);
            padding: 20px 15px;
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(156, 126, 92, 0.06);
            border: 1px solid rgba(156, 126, 92, 0.1);
            text-align: center;
            transition: transform 0.2s;
        }

        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(156, 126, 92, 0.1);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 1.1rem;
        }

        .stat-box.total .stat-icon {
            background: #EFEBE9;
            color: #8D6E63;
        }

        .stat-box.pending .stat-icon {
            background: #FFF8E1;
            color: #FFA000;
        }

        .stat-box.paid .stat-icon {
            background: #E8F5E9;
            color: #43A047;
        }

        .stat-box.done .stat-icon {
            background: #ECEFF1;
            color: #546E7A;
        }

        .stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* --- CARD STYLE (SHADOW KOTAK) --- */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 20px;
            padding-left: 12px;
            border-left: 4px solid var(--primary);
        }

        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .trip-card {
            background: var(--surface);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            /* Shadow Tegas */
            border: 1px solid rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(156, 126, 92, 0.15);
        }

        .trip-img-box {
            height: 180px;
            position: relative;
            overflow: hidden;
        }

        .trip-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .trip-card:hover .trip-img-box img {
            transform: scale(1.05);
        }

        .status-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .status-badge.pending {
            color: #F59E0B;
        }

        .status-badge.paid {
            color: #10B981;
        }

        .status-badge.cancelled {
            color: #EF4444;
        }

        .status-badge.finished {
            color: #6B7280;
        }

        .trip-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .trip-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .trip-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .trip-date i {
            color: var(--primary);
        }

        .trip-meta {
            display: flex;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #EEE;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-lbl {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #9CA3AF;
            font-weight: 600;
        }

        .meta-val {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .trip-footer {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }

        .btn-card {
            flex: 1;
            padding: 10px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary-soft);
        }

        .btn-outline:hover {
            background: var(--primary-soft);
            color: var(--primary-dark);
        }

        .btn-fill {
            background: var(--primary);
            color: white;
        }

        .btn-fill:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(156, 126, 92, 0.25);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 2px dashed #E0E0E0;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--primary);
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .btn-explore {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-explore:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }

            .trips-grid {
                grid-template-columns: 1fr;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .info-item {
                width: 100%;
                display: flex;
                justify-content: space-between;
            }
        }

        /* =========================================
           POPUP STYLE (SMALL & COMPACT TICKET)
           ========================================= */
        .swal2-popup.small-ticket-popup {
            width: 380px !important;
            /* UKURAN KECIL (COMPACT) */
            max-width: 90vw !important;
            padding: 0 !important;
            border-radius: 16px !important;
            font-family: 'Poppins', sans-serif !important;
        }

        .st-header {
            padding: 20px 20px 10px;
            text-align: center;
            background: #fff;
        }

        .st-icon {
            width: 50px;
            height: 50px;
            background: #9C7E5C;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 10px;
            box-shadow: 0 4px 10px rgba(156, 126, 92, 0.2);
        }

        .st-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #374151;
            margin-bottom: 4px;
        }

        .st-pill {
            background: #F3F4F6;
            color: #6B7280;
            padding: 3px 12px;
            border-radius: 50px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .st-divider {
            border-bottom: 2px dashed #EEE;
            margin: 15px 0;
            width: 100%;
        }

        .st-body {
            padding: 5px 25px 25px;
            text-align: left;
        }

        .st-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .st-row:last-child {
            margin-bottom: 0;
        }

        .st-lbl {
            text-transform: uppercase;
            color: #9CA3AF;
            font-weight: 600;
            font-size: 0.65rem;
            letter-spacing: 0.5px;
        }

        .st-val {
            font-weight: 600;
            color: #374151;
            text-align: right;
            font-size: 0.85rem;
        }

        .st-val.highlight {
            color: #9C7E5C;
            font-size: 1rem;
            font-weight: 800;
        }

        .st-val.danger {
            color: #DC2626;
        }

        .st-mp-box {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 20px;
            background: #FAFAFA;
            padding: 10px;
            border-radius: 10px;
            border: 1px dashed #DDD;
        }

        .st-mp-title {
            font-size: 0.65rem;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .st-mp-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        .btn-maps-small {
            display: flex;
            width: 100%;
            padding: 10px;
            background: #9C7E5C;
            color: white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: 0.2s;
        }

        .btn-maps-small:hover {
            background: #7B5E3A;
            box-shadow: 0 4px 12px rgba(156, 126, 92, 0.3);
        }

        .swal2-close {
            color: #ccc !important;
            font-size: 2rem !important;
            top: 10px !important;
            right: 10px !important;
            box-shadow: none !important;
        }

        .swal2-close:hover {
            color: #d33 !important;
            background: transparent !important;
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div class="page-decor">
        <div class="container">

            <div class="stats-section">
                <div class="stats-title"><i class="fa-solid fa-chart-pie"></i> Ringkasan Aktivitas</div>
                <div class="stats-container">
                    <div class="stat-box total">
                        <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="stat-num"><?= $totalTrips ?></div>
                        <div class="stat-label">Total Trip</div>
                    </div>
                    <div class="stat-box pending">
                        <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="stat-num"><?= $pendingCount ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                    <div class="stat-box paid">
                        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-num"><?= $paidCount ?></div>
                        <div class="stat-label">Lunas</div>
                    </div>
                    <div class="stat-box done">
                        <div class="stat-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                        <div class="stat-num"><?= $finishedCount ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            </div>

            <?php if (empty($myTrips)): ?>
                <div class="empty-state animate__animated animate__fadeInUp">
                    <i class="fa-solid fa-person-hiking empty-icon"></i>
                    <h3>Belum Ada Petualangan</h3>
                    <p style="color:#999; font-size:0.9rem;">Mulai perjalanan Anda sekarang!</p>
                    <a href="<?= getPageUrl('index.php') ?>#paketTrips" class="btn-explore">
                        <i class="fa-solid fa-compass"></i> Cari Paket Trip
                    </a>
                </div>
            <?php else: ?>
                <div class="section-title">Riwayat Trip</div>

                <div class="trips-grid">
                    <?php foreach ($myTrips as $trip):
                        $sb = strtolower($trip['status_booking']);
                        $statusInfo = [
                            'pending' => ['class' => 'pending', 'icon' => 'fa-hourglass-half', 'text' => 'Menunggu'],
                            'paid' => ['class' => 'paid', 'icon' => 'fa-check-circle', 'text' => 'Lunas'],
                            'confirmed' => ['class' => 'paid', 'icon' => 'fa-check-circle', 'text' => 'Confirmed'],
                            'finished' => ['class' => 'finished', 'icon' => 'fa-flag', 'text' => 'Selesai'],
                            'cancelled' => ['class' => 'cancelled', 'icon' => 'fa-times-circle', 'text' => 'Batal']
                        ][$sb] ?? ['class' => 'pending', 'icon' => 'fa-info-circle', 'text' => ucfirst($sb)];
                    ?>
                        <div class="trip-card">
                            <div class="trip-img-box">
                                <img src="<?= htmlspecialchars($trip['gambar']) ?>" alt="Gunung">
                                <div class="status-badge <?= $statusInfo['class'] ?>">
                                    <i class="fa-solid <?= $statusInfo['icon'] ?>"></i> <?= $statusInfo['text'] ?>
                                </div>
                                <?php if (strtolower($trip['status_trip']) === 'done'): ?>
                                    <img src="<?= $navbarPath ?>assets/completed-stamp.png" style="position:absolute; bottom:10px; right:10px; width:80px; opacity:0.9;" alt="Selesai">
                                <?php endif; ?>
                            </div>

                            <div class="trip-body">
                                <div class="trip-name"><?= htmlspecialchars($trip['nama_gunung']) ?></div>
                                <div class="trip-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= date('d M Y', strtotime($trip['tanggal_trip'])) ?>
                                </div>

                                <div class="trip-meta">
                                    <div class="meta-item">
                                        <span class="meta-lbl">Jalur</span>
                                        <span class="meta-val"><?= htmlspecialchars($trip['via_gunung']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-lbl">Peserta</span>
                                        <span class="meta-val"><?= $trip['jumlah_orang'] ?> Org</span>
                                    </div>
                                </div>

                                <div class="trip-footer">
                                    <button class="btn-card btn-outline" onclick='openDetail(<?= htmlspecialchars(json_encode($trip), ENT_QUOTES); ?>)'>
                                        <i class="fa-regular fa-eye"></i> Detail
                                    </button>
                                    <?php if ($sb === 'pending'): ?>
                                        <a href="<?= $navbarPath; ?>user/payment-status.php?booking_id=<?= $trip['id_booking']; ?>" class="btn-card btn-fill">
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
    </div>

    <script>
        const formatRupiah = (num) => 'Rp ' + parseInt(num).toLocaleString('id-ID');

        // FUNCTION OPEN DETAIL (SMALL TICKET)
        function openDetail(d) {
            const tDate = new Date(d.tanggal_trip).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            const time = d.waktu_kumpul ? d.waktu_kumpul.substring(0, 5) + ' WIB' : '00:00 WIB';

            Swal.fire({
                html: `
                    <div class="st-header">
                        <div class="st-icon"><i class="fa-solid fa-ticket"></i></div>
                        <div class="st-title">${d.nama_gunung}</div>
                        <div class="st-pill">ID Pesanan #${d.id_booking}</div>
                    </div>

                    <div class="st-body">
                        
                        <div class="st-divider"></div>

                        <div class="st-row">
                            <span class="st-lbl">Tanggal Trip</span>
                            <span class="st-val">${tDate}</span>
                        </div>
                        <div class="st-row">
                            <span class="st-lbl">Via Jalur</span>
                            <span class="st-val">${d.via_gunung}</span>
                        </div>
                        <div class="st-row">
                            <span class="st-lbl">Jumlah Peserta</span>
                            <span class="st-val">${d.jumlah_orang} Orang</span>
                        </div>
                        <div class="st-row">
                            <span class="st-lbl">Waktu Kumpul</span>
                            <span class="st-val danger">${time}</span>
                        </div>

                        <div class="st-divider"></div>

                        <div class="st-row">
                            <span class="st-lbl">Total Pembayaran</span>
                            <span class="st-val highlight">${formatRupiah(d.total_harga)}</span>
                        </div>

                        <div class="st-mp-box">
                            <div class="st-mp-title">TITIK KUMPUL / MEETING POINT</div>
                            <div class="st-mp-name">${d.nama_lokasi}</div>
                        </div>

                        ${d.link_map ? `
                        <a href="${d.link_map}" target="_blank" class="btn-maps-small">
                            <i class="fa-solid fa-map-location-dot"></i> Buka Google Maps
                        </a>` : ''}
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    popup: 'small-ticket-popup'
                }
            });
        }
    </script>
</body>

</html>