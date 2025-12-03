<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

$navbarPath = '../';

if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// --- LOGIKA PHP ASLI (TIDAK DIUBAH) ---

// 1. Auto-expire pending >24 jam
$expire_url = getPageUrl('backend/payment-api.php') . '?expire_stale=1';
$chx = curl_init();
curl_setopt($chx, CURLOPT_URL, $expire_url);
curl_setopt($chx, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chx, CURLOPT_TIMEOUT, 3);
curl_setopt($chx, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($chx);
curl_close($chx);

// 2. Auto-check pending payments
$pendingStmt = $conn->prepare("SELECT DISTINCT p.order_id FROM payments p
    JOIN bookings b ON p.id_booking = b.id_booking
    WHERE b.id_user = ? AND p.status_pembayaran = 'pending' AND p.order_id IS NOT NULL AND p.order_id != ''");

if ($pendingStmt) {
    $pendingStmt->bind_param("i", $id_user);
    $pendingStmt->execute();
    $pendingResult = $pendingStmt->get_result();

    while ($row = $pendingResult->fetch_assoc()) {
        $order_id = $row['order_id'];
        $check_url = getPageUrl('backend/payment-api.php') . '?check_status=' . urlencode($order_id);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $check_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
    $pendingStmt->close();
}

// 3. Get booking data
$stmt = $conn->prepare("SELECT b.id_booking, b.tanggal_booking, b.total_harga, b.jumlah_orang, b.status as booking_status,
    t.nama_gunung, t.jenis_trip, t.gambar, t.durasi, d.nama_lokasi, d.waktu_kumpul, d.link_map,
    p.id_payment, p.status_pembayaran, p.order_id, p.tanggal as payment_date
    FROM bookings b 
    JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
    LEFT JOIN payments p ON b.id_booking = p.id_booking
    WHERE b.id_user = ? ORDER BY b.tanggal_booking DESC");

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$booking_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - Majelis MDPL</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* --- DESIGN SYSTEM --- */
        :root {
            --primary: #9C7E5C;
            --primary-dark: #7B5E3A;
            --bg-page: #FDFBF9;
            --surface: #FFFFFF;
            --text-main: #374151;
            --text-muted: #9CA3AF;
            --radius-xl: 24px;
            --radius-md: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            padding-top: 120px;
            min-height: 100vh;
        }

        /* Background Decor */
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px 60px;
            position: relative;
            z-index: 2;
        }

        /* HEADER */
        .dashboard-header {
            background: var(--surface);
            border-radius: var(--radius-xl);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(156, 126, 92, 0.06);
            border: 1px solid rgba(156, 126, 92, 0.1);
            text-align: center;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .header-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* CARD LIST */
        .payment-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .pay-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(156, 126, 92, 0.1);
            display: grid;
            grid-template-columns: 240px 1fr;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .pay-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(156, 126, 92, 0.12);
            border-color: rgba(156, 126, 92, 0.2);
        }

        .card-img {
            position: relative;
            height: 100%;
            min-height: 200px;
            overflow: hidden;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .pay-card:hover .card-img img {
            transform: scale(1.05);
        }

        .status-float {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 2;
        }

        .status-float.pending {
            color: #F59E0B;
        }

        .status-float.paid {
            color: #10B981;
        }

        .status-float.cancelled {
            color: #EF4444;
        }

        .status-float.expire {
            color: #AD1457;
        }

        .card-content {
            padding: 25px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .trip-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .booking-id {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            background: #F9FAFB;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #EFEBE9;
        }

        .info-item label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #9CA3AF;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-item div {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .info-item.price div {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-act {
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-detail {
            background: transparent;
            color: var(--text-main);
            border: 1px solid #E5E7EB;
        }

        .btn-detail:hover {
            background: #F9FAFB;
            border-color: #D1D5DB;
        }

        .btn-pay {
            background: var(--primary);
            color: white;
            padding: 10px 25px;
        }

        .btn-pay:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(156, 126, 92, 0.25);
        }

        .btn-cancel-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: #FEF2F2;
            color: #DC2626;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            margin-right: auto;
            flex-shrink: 0;
        }

        .btn-cancel-icon:hover {
            background: #FEE2E2;
            transform: scale(1.1);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--surface);
            border-radius: var(--radius-xl);
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

        .refresh-indicator {
            position: fixed;
            top: 90px;
            right: 20px;
            background: rgba(156, 126, 92, 0.9);
            color: white;
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s;
        }

        .refresh-indicator.show {
            display: flex;
        }

        .refresh-indicator i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================
           POPUP STYLE FINAL (COMPACT & RESPONSIVE)
           ========================================= */

        .swal2-popup.ticket-popup {
            width: 600px !important;
            /* UKURAN DIPERKECIL (COMPACT) */
            max-width: 95vw !important;
            padding: 0 !important;
            border-radius: 20px !important;
            background: #fff !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2) !important;
            overflow: hidden !important;
            font-family: 'Poppins', sans-serif !important;
        }

        /* Header Compact */
        .ticket-header {
            background: #A98762;
            padding: 20px 25px;
            text-align: center;
            color: white;
            position: relative;
        }

        .ticket-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .ticket-sub {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Body Compact */
        .ticket-body {
            padding: 25px;
            background: #fff;
            text-align: left;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #E5E7EB;
        }

        .info-group label {
            display: block;
            font-size: 0.7rem;
            color: #9CA3AF;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .info-group div {
            font-size: 0.95rem;
            font-weight: 700;
            color: #374151;
        }

        /* Section Peserta (Scrollable) */
        .participant-section {
            margin-bottom: 20px;
            background: #FAFAFA;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #F3F4F6;
        }

        .p-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #5D4037;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* SCROLL CONTAINER: Agar tidak memanjang ke bawah */
        .p-list-container {
            max-height: 180px;
            /* Batas tinggi scroll */
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Custom Scrollbar */
        .p-list-container::-webkit-scrollbar {
            width: 5px;
        }

        .p-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .p-list-container::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        /* Item Peserta */
        .p-item-compact {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .p-item-compact:last-child {
            margin-bottom: 0;
        }

        .p-name-row {
            font-weight: 700;
            font-size: 0.9rem;
            color: #333;
        }

        .p-contact-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.8rem;
            color: #666;
        }

        .p-contact-row span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .p-contact-row i {
            color: #A98762;
        }

        .ticket-footer {
            text-align: center;
            margin-top: 10px;
        }

        .btn-invoice-only {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 30px;
            background: #E5E7EB;
            color: #6B7280;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .btn-invoice-only.active {
            background: #374151;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-invoice-only.active:hover {
            background: #1F2937;
            transform: translateY(-2px);
        }

        .swal2-close {
            color: #555 !important;
            /* Ubah jadi Abu-abu Gelap agar terlihat */
            font-size: 1.5rem !important;
            top: 15px !important;
            /* Jarak dari atas */
            right: 15px !important;
            /* Jarak dari kanan */
            background: transparent !important;
            opacity: 0.6 !important;
            box-shadow: none !important;
            outline: none !important;
            z-index: 9999 !important;
            /* Pastikan muncul di layer paling atas */
        }

        .swal2-close:hover {
            color: #DC2626 !important;
            /* Berubah Merah saat diarahkan mouse */
            background: rgba(0, 0, 0, 0.05) !important;
            /* Efek kotak tipis saat hover */
            opacity: 1 !important;
            transform: scale(1.1);
            /* Efek membesar sedikit */
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 10px;
            }

            .pay-card {
                grid-template-columns: 1fr;
            }

            .card-img {
                height: 160px;
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

            .card-actions {
                justify-content: space-between;
                width: 100%;
            }
        }

        .custom-success-icon-wrapper {
            width: 80px;
            height: 80px;
            border: 5px solid #a8e6cf;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px auto 20px auto;
        }

        .custom-success-icon {
            font-size: 48px;
            color: #7bc07b;
        }

        .swal2-popup.custom-success-popup {
            max-width: 380px !important;
            padding: 20px 0 !important;
            border-radius: 20px !important;
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div class="page-decor">
        <div id="refresh-indicator" class="refresh-indicator">
            <i class="fa-solid fa-sync"></i> Update...
        </div>

        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1 class="header-title"><i class="fa-solid fa-receipt"></i> Status Pembayaran</h1>
                    <p class="header-desc">Pantau tagihan dan riwayat transaksi Anda.</p>
                </div>
            </div>

            <?php if (empty($booking_list)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-file-invoice-dollar empty-icon"></i>
                    <h3>Tidak Ada Tagihan</h3>
                    <p style="color:#999; margin-bottom:20px">Anda belum memiliki riwayat transaksi saat ini.</p>
                    <a href="<?= getPageUrl('index.php') ?>#paketTrips" class="btn-explore">
                        <i class="fa-solid fa-compass"></i> Cari Trip Baru
                    </a>
                </div>
            <?php else: ?>
                <div class="payment-list">
                    <?php foreach ($booking_list as $b):
                        $status = strtolower($b['status_pembayaran'] ?? 'pending');
                        $statusInfo = [
                            'pending' => ['class' => 'pending', 'icon' => 'fa-hourglass-half', 'text' => 'Menunggu'],
                            'paid' => ['class' => 'paid', 'icon' => 'fa-check-circle', 'text' => 'Lunas'],
                            'settlement' => ['class' => 'paid', 'icon' => 'fa-check-circle', 'text' => 'Lunas'],
                            'expire' => ['class' => 'expire', 'icon' => 'fa-clock', 'text' => 'Expired'],
                            'cancel' => ['class' => 'cancelled', 'icon' => 'fa-ban', 'text' => 'Batal'],
                            'failed' => ['class' => 'cancelled', 'icon' => 'fa-times-circle', 'text' => 'Gagal']
                        ][$status] ?? ['class' => 'pending', 'icon' => 'fa-info-circle', 'text' => ucfirst($status)];
                        $img = !empty($b['gambar']) ? (strpos($b['gambar'], 'img/') === 0 ? $navbarPath . $b['gambar'] : $navbarPath . 'img/' . $b['gambar']) : $navbarPath . 'img/default-mountain.jpg';
                    ?>
                        <div class="pay-card" data-booking-id="<?= $b['id_booking']; ?>" data-order-id="<?= htmlspecialchars($b['order_id'] ?? ''); ?>">
                            <div class="card-img">
                                <img src="<?= htmlspecialchars($img) ?>" alt="Trip">
                                <div class="status-float <?= $statusInfo['class'] ?>">
                                    <i class="fa-solid <?= $statusInfo['icon'] ?>"></i> <?= $statusInfo['text'] ?>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-top">
                                    <h3 class="trip-name"><?= htmlspecialchars($b['nama_gunung']) ?></h3>
                                    <span class="booking-id">#<?= $b['id_booking'] ?></span>
                                </div>
                                <div class="info-row">
                                    <div class="info-item">
                                        <label>Tanggal Order</label>
                                        <div><?= date("d M Y", strtotime($b['tanggal_booking'])) ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Peserta</label>
                                        <div><?= $b['jumlah_orang'] ?> Orang</div>
                                    </div>
                                    <div class="info-item price">
                                        <label>Total Tagihan</label>
                                        <div>Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <?php if ($status === 'pending'): ?>
                                        <button class="btn-cancel-icon" onclick="cancelPayment(<?= $b['id_booking']; ?>)" title="Batalkan Pesanan">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-act btn-detail" onclick="showDetail(<?= $b['id_booking']; ?>)">
                                        <i class="fa-regular fa-file-lines"></i> Detail
                                    </button>
                                    <?php if ($status === 'pending' && !empty($b['order_id'])): ?>
                                        <button class="btn-act btn-pay" onclick="pay(<?= $b['id_booking']; ?>)">
                                            <i class="fa-solid fa-wallet"></i> Bayar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="modal-payment" style="display:none;"></div>

    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>
    <script>
        const formatRupiah = (num) => 'Rp ' + parseInt(num).toLocaleString('id-ID');

        function periodicRefresh() {
            const cards = Array.from(document.querySelectorAll('.pay-card[data-order-id]'));
            const pendings = cards.filter(c => {
                const pill = c.querySelector('.status-float');
                return pill && pill.classList.contains('pending');
            });
            pendings.forEach(c => {
                const orderId = c.getAttribute('data-order-id');
                if (orderId) checkStatus(orderId, true);
            });
        }

        function checkStatus(orderId, quiet = false) {
            fetch('../backend/payment-api.php?check_status=' + encodeURIComponent(orderId))
                .then(r => r.json())
                .then(resp => {
                    if (resp && resp.success && ['paid', 'failed', 'expire', 'cancel'].includes(resp.status)) {
                        // Jika status berubah jadi sukses, reload halaman agar list terupdate
                        if (!quiet) {
                            showSuccessPopup();
                        } else {
                            document.getElementById('refresh-indicator').classList.add('show');
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    }
                })
                .catch(err => console.log('Check error:', err));
        }

        // FUNGSI POPUP SUKSES BAYAR (KONSISTEN)
        function showSuccessPopup() {
            Swal.fire({
                html: `
                    <div class="custom-success-icon-wrapper">
                        <i class="custom-success-icon fas fa-check"></i>
                    </div>
                    <div class="swal2-title" style="background:none !important; color:#333 !important; padding-bottom:0 !important; margin-bottom:10px;">Pembayaran Berhasil!</div>
                    <div class="swal2-html-container" style="color:#666;">Terima kasih, pembayaran Anda telah kami terima.</div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#7568c8', // Ungu atau Coklat (#9C7E5C) sesuai tema
                customClass: {
                    popup: 'custom-success-popup' // Gunakan class CSS global yang sudah ada
                },
                allowOutsideClick: false
            }).then(() => {
                window.location.reload();
            });
        }

        // FUNGSI BAYAR (MIDTRANS)
        function pay(bookingId) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('../backend/payment-api.php?booking=' + bookingId)
                .then(r => r.json())
                .then(resp => {
                    Swal.close();
                    if (resp.snap_token) {
                        window.snap.pay(resp.snap_token, {
                            onSuccess: function(result) {
                                // PANGGIL POPUP SUKSES DISINI (SEBELUM RELOAD)
                                showSuccessPopup();

                                // Panggil API backend untuk update status di database segera
                                fetch('../backend/payment-api.php?check_status=' + resp.order_id);
                            },
                            onPending: function(result) {
                                Swal.fire('Menunggu Pembayaran', 'Silakan selesaikan pembayaran Anda.', 'info')
                                    .then(() => location.reload());
                            },
                            onError: function(result) {
                                Swal.fire('Pembayaran Gagal', 'Terjadi kesalahan saat pembayaran.', 'error');
                            },
                            onClose: function() {
                                // Opsional: Cek status jika user menutup popup tanpa bayar (siapa tahu sudah bayar di tab lain)
                                checkStatus(resp.order_id, true);
                            }
                        });
                    } else {
                        Swal.fire('Gagal', resp.message || 'Token error', 'error');
                    }
                })
                .catch(err => Swal.fire('Error', 'Koneksi gagal', 'error'));
        }

        function cancelPayment(bookingId) {
            Swal.fire({
                title: 'Batalkan?',
                text: "Pesanan akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#F5F5F5',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: '<span style="color:#555">Kembali</span>',
                customClass: {
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = new FormData();
                    form.append('cancel_booking', bookingId);
                    fetch('../backend/payment-api.php', {
                            method: 'POST',
                            body: form
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    title: 'Dibatalkan',
                                    icon: 'success',
                                    confirmButtonColor: '#9C7E5C'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', res.error, 'error');
                            }
                        });
                }
            })
        }

        function showDetail(bookingId) {
            // ... (Isi fungsi showDetail SAMA PERSIS dengan kode sebelumnya) ...
            Swal.fire({
                title: 'Memuat...',
                didOpen: () => Swal.showLoading()
            });

            fetch(`../backend/get-booking-detail.php?id=${bookingId}`)
                .then(r => r.json())
                .then(d => {
                    Swal.close();
                    if (d.error) {
                        Swal.fire('Error', d.error, 'error');
                        return;
                    }

                    const tBook = new Date(d.tanggal_booking).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    const jam = d.waktu_kumpul ? d.waktu_kumpul.substring(0, 5) + ' WIB' : '00:00 WIB';
                    const lokasi = d.nama_lokasi || 'Basecamp Utama';
                    const totalHarga = 'Rp ' + parseInt(d.total_harga).toLocaleString('id-ID');
                    const orderId = d.order_id || d.id_booking;

                    let pesertaHtml = '';
                    if (d.participants && d.participants.length > 0) {
                        d.participants.forEach((p, index) => {
                            const pName = p.nama ? p.nama.toUpperCase() : 'PESERTA';
                            const pEmail = p.email || '-';
                            const pPhone = p.no_wa || p.no_hp || p.phone || '-';

                            pesertaHtml += `
                                 <div class="p-item-compact">
                                     <div class="p-name-row">${index + 1}. ${pName}</div>
                                     <div class="p-contact-row">
                                         <span><i class="fa-solid fa-envelope"></i> ${pEmail}</span>
                                         <span><i class="fa-brands fa-whatsapp"></i> ${pPhone}</span>
                                     </div>
                                 </div>`;
                        });
                    } else {
                        pesertaHtml = '<div style="text-align:center; color:#999; font-style:italic; padding:10px;">Data peserta belum diinput.</div>';
                    }

                    const isPaid = (d.status_pembayaran === 'paid' || d.status_pembayaran === 'settlement');
                    let btnHtml = isPaid ? `
                         <a href="view-invoice.php?payment_id=${d.id_payment}" target="_blank" class="btn-invoice-only active">
                             <i class="fa-solid fa-print"></i> Invoice
                         </a>` : `
                         <div class="btn-invoice-only" style="cursor:not-allowed;">
                             <i class="fa-solid fa-lock"></i> Invoice 
                         </div>`;

                    Swal.fire({
                        html: `
                             <div class="ticket-header">
                                 <div class="ticket-title">Transaksi #${orderId}</div>
                                 <div class="ticket-sub">${d.nama_gunung}</div>
                             </div>
                             <div class="ticket-body">
                                 <div class="info-grid">
                                     <div class="info-group">
                                         <label>Tanggal Order</label>
                                         <div>${tBook}</div>
                                     </div>
                                     <div class="info-group" style="text-align:right;">
                                         <label>Total Tagihan</label>
                                         <div style="color:#A98762;">${totalHarga}</div>
                                     </div>
                                     <div class="info-group">
                                         <label>Lokasi Kumpul</label>
                                         <div>${lokasi}</div>
                                     </div>
                                     <div class="info-group" style="text-align:right;">
                                         <label>Waktu Kumpul</label>
                                         <div style="color:#DC2626;">${jam}</div>
                                     </div>
                                 </div>
                                 <div class="participant-section">
                                     <div class="p-title">
                                         <i class="fa-solid fa-users"></i> Daftar Peserta (${d.participants ? d.participants.length : 0})
                                     </div>
                                     <div class="p-list-container">
                                         ${pesertaHtml}
                                     </div>
                                 </div>
                                 <div class="ticket-footer">${btnHtml}</div>
                             </div>`,
                        width: 600,
                        showConfirmButton: false,
                        showCloseButton: true,
                        customClass: {
                            popup: 'ticket-popup'
                        }
                    });
                })
                .catch(err => Swal.fire('Gagal', 'Koneksi terputus.', 'error'));
        }

        document.addEventListener('DOMContentLoaded', () => {
            setInterval(periodicRefresh, 10000);
        });
    </script>
</body>

</html>