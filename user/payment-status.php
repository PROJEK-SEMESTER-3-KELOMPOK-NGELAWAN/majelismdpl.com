<?php
// ✅ START SESSION
session_start();

// ✅ SET NAVBAR PATH
$navbarPath = '../';

// ✅ CEK STATUS LOGIN
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// ✅ CONTOH DATA TRANSAKSI (HARDCODED)
$booking_list = [
    [
        'id_booking' => 28,
        'nama_trip' => 'Gunung Slamet',
        'tanggal_booking' => '2025-10-23',
        'total_harga' => 500000,
        'status_pembayaran' => 'pending',
    ],
    [
        'id_booking' => 26,
        'nama_trip' => 'Gunung Raung',
        'tanggal_booking' => '2025-10-21',
        'total_harga' => 400000,
        'status_pembayaran' => 'settlement',
    ],
    [
        'id_booking' => 13,
        'nama_trip' => 'Gunung Argopuro',
        'tanggal_booking' => '2025-10-10',
        'total_harga' => 600000,
        'status_pembayaran' => 'cancelled',
    ],
    [
        'id_booking' => 17,
        'nama_trip' => 'Gunung Semeru',
        'tanggal_booking' => '2025-10-11',
        'total_harga' => 300000,
        'status_pembayaran' => 'expire',
    ],
];

// ✅ FUNGSI HELPER
function get_status_class_payment($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'status-pending';
        case 'paid':
        case 'settlement':
            return 'status-paid';
        case 'expire':
        case 'failed':
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-default';
    }
}

function format_status_text_payment($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return '<i class="fa-solid fa-hourglass-half"></i> Menunggu Pembayaran';
        case 'paid':
        case 'settlement':
            return '<i class="fa-solid fa-check-circle"></i> Pembayaran Diterima';
        case 'expire':
            return '<i class="fa-solid fa-clock-rotate-left"></i> Kadaluarsa';
        case 'failed':
            return '<i class="fa-solid fa-times-circle"></i> Gagal';
        case 'cancelled':
            return '<i class="fa-solid fa-ban"></i> Dibatalkan';
        default:
            return ucwords($status);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Status Pembayaran Saya | Majelis MDPL</title>

    <!-- ✅ LOAD LIBRARIES -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background-color: #f4f4f4;
            overflow-x: hidden;
        }

        .payment-page-container {
            padding-top: 100px;
            min-height: 100vh;
        }

        .payment-section {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-content {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
            line-height: 1.2;
        }

        .subtitle {
            color: #666;
            font-size: clamp(0.85rem, 3vw, 1.1rem);
            padding: 0 10px;
        }

        .page-title i {
            color: #a97c50;
            margin-right: 10px;
        }

        .status-list-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .status-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 200px;
            align-items: stretch;
            border: 1px solid #e0e0e0;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        .card-main-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .trip-title {
            font-size: clamp(1.2rem, 4vw, 1.6rem);
            font-weight: 700;
            color: #222;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .trip-order-id {
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            color: #a97c50;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .detail-group {
            border-top: 1px dashed #eee;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .detail-item {
            flex-basis: 50%;
        }

        .detail-label {
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            color: #777;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .detail-value {
            font-size: clamp(0.95rem, 2.5vw, 1.1rem);
            font-weight: 600;
            color: #333;
        }

        .price-value {
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            font-weight: 700;
            color: #a97c50;
        }

        .card-status-action {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 20px 15px;
            border-left: 1px solid #f0f0f0;
            background: #fcfcfc;
        }

        .status-badge-container {
            text-align: center;
            padding: 10px;
            border-radius: 10px;
            width: 100%;
            margin-bottom: 15px;
        }

        .status-icon-big {
            font-size: clamp(2rem, 5vw, 2.5rem);
            margin-bottom: 5px;
            display: block;
        }

        .status-text-small {
            font-size: clamp(0.7rem, 2vw, 0.9rem);
            font-weight: 600;
            text-transform: uppercase;
            display: block;
        }

        .status-pending .status-icon-big {
            color: #ffc107;
        }

        .status-pending .status-text-small {
            color: #e65100;
        }

        .status-paid .status-icon-big {
            color: #28a745;
        }

        .status-paid .status-text-small {
            color: #2e7d32;
        }

        .status-cancelled .status-icon-big {
            color: #dc3545;
        }

        .status-cancelled .status-text-small {
            color: #c62828;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .btn-action {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: clamp(0.8rem, 2.2vw, 0.9rem);
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-continue {
            background: #a97c50;
            color: #fff;
            box-shadow: 0 3px 10px rgba(169, 124, 80, 0.4);
        }

        .btn-continue:hover {
            background: #8b5e3c;
        }

        .btn-detail {
            background: #4a4a4a;
            color: #fff;
        }

        .btn-detail:hover {
            background: #333;
        }

        .btn-action:disabled {
            background: #ccc;
            cursor: not-allowed;
            color: #777;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            border-radius: 15px;
        }

        .empty-icon {
            font-size: clamp(2rem, 8vw, 3rem);
            color: #ccc;
            margin-bottom: 15px;
        }

        /* ✅ PAYMENT MODAL STYLING */
        .payment-modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(20, 15, 12, 0.88);
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .payment-modal-overlay.active {
            display: flex;
        }

        .payment-modal-content {
            background: #fff;
            padding: 30px 20px;
            max-width: 430px;
            width: 100%;
            border-radius: 17px;
            box-shadow: 0 5px 65px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        .payment-modal-text {
            font-size: clamp(0.9rem, 3vw, 1rem);
            color: #333;
            margin-bottom: 20px;
        }

        .payment-modal-btn {
            margin-top: 15px;
            background: #eee;
            border: 1px solid #ccc;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .payment-modal-btn:hover {
            background: #ddd;
        }

        /* ✅ RESPONSIVE BREAKPOINTS */
        @media (max-width: 768px) {
            .payment-page-container {
                padding-top: 90px;
            }

            .status-card {
                grid-template-columns: 1fr;
            }

            .card-status-action {
                border-left: none;
                border-top: 1px solid #f0f0f0;
                flex-direction: row;
                gap: 12px;
                padding: 15px;
            }

            .status-badge-container {
                width: 32%;
                margin-bottom: 0;
            }

            .action-group {
                width: 68%;
                flex-direction: row;
                gap: 6px;
            }

            .btn-action {
                flex: 1;
                font-size: 0.85rem;
                padding: 10px 8px;
            }

            .detail-group {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .payment-page-container {
                padding-top: 85px;
            }

            .payment-section {
                margin: 25px auto;
                padding: 0 12px;
            }

            .status-card {
                border-radius: 12px;
            }

            .card-main-info {
                padding: 16px;
            }

            .card-status-action {
                flex-direction: column;
                gap: 10px;
            }

            .status-badge-container {
                width: 100%;
                margin-bottom: 8px;
            }

            .action-group {
                width: 100%;
                flex-direction: column;
                gap: 8px;
            }

            .btn-action {
                width: 100%;
                padding: 11px 10px;
            }
        }

        @media (max-width: 375px) {
            .page-title {
                font-size: 1.4rem;
            }

            .subtitle {
                font-size: 0.85rem;
            }

            .trip-title {
                font-size: 1.1rem;
            }

            .trip-order-id {
                font-size: 0.75rem;
            }

            .detail-label {
                font-size: 0.75rem;
            }

            .detail-value {
                font-size: 0.9rem;
            }

            .price-value {
                font-size: 1.05rem;
            }
        }
    </style>
</head>

<body>
    <!-- ✅ INCLUDE NAVBAR -->
    <?php include '../navbar.php'; ?>

    <!-- ✅ INCLUDE AUTH MODALS -->
    <?php include '../auth-modals.php'; ?>

    <div class="payment-page-container">
        <main class="payment-section">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fa-solid fa-credit-card"></i> Status Pembayaran Saya
                </h1>
                <p class="subtitle">Lacak riwayat transaksi Anda untuk setiap pemesanan trip.</p>
            </div>

            <section class="status-list-section">
                <?php if (empty($booking_list)) : ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-exclamation-circle empty-icon"></i>
                        <h2>Belum Ada Transaksi</h2>
                        <p>Anda belum memiliki riwayat pemesanan yang perlu dilacak.</p>
                        <a href="<?= $navbarPath; ?>#paketTrips" class="btn-continue">
                            <i class="fa-solid fa-compass"></i> Jelajahi Trip
                        </a>
                    </div>
                <?php else : ?>
                    <div class="status-list-grid">
                        <?php foreach ($booking_list as $booking) :
                            $status = strtolower($booking['status_pembayaran']);
                            $status_class = get_status_class_payment($status);
                            $status_text_full = format_status_text_payment($status);
                            $status_text_clean = strip_tags($status_text_full);

                            $icon_match = [];
                            preg_match('/<i class="[^"]+"><\/i>/', $status_text_full, $icon_match);
                            $icon_html = $icon_match[0] ?? '<i class="fa-solid fa-question-circle"></i>';

                            $formatted_date = date("d M Y", strtotime($booking['tanggal_booking']));
                            $formatted_price = "Rp " . number_format($booking['total_harga'], 0, ',', '.');
                        ?>
                            <div class="status-card">
                                <div class="card-main-info">
                                    <h3 class="trip-title">
                                        <i class="fa-solid fa-mountain-sun" style="color: #a97c50;"></i>
                                        <?= htmlspecialchars($booking['nama_trip']); ?>
                                    </h3>
                                    <p class="trip-order-id">
                                        Booking ID: #<?= $booking['id_booking']; ?>
                                    </p>

                                    <div class="detail-group">
                                        <div class="detail-item">
                                            <p class="detail-label"><i class="fa-solid fa-calendar-alt"></i> Tanggal Pesan</p>
                                            <p class="detail-value"><?= $formatted_date; ?></p>
                                        </div>
                                        <div class="detail-item">
                                            <p class="detail-label"><i class="fa-solid fa-tag"></i> Total Tagihan</p>
                                            <p class="detail-value price-value"><?= $formatted_price; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-status-action">
                                    <div class="status-badge-container <?= $status_class; ?>">
                                        <span class="status-icon-big"><?= $icon_html; ?></span>
                                        <span class="status-text-small"><?= $status_text_clean; ?></span>
                                    </div>

                                    <div class="action-group">
                                        <button class="btn-action btn-detail" type="button" onclick="showDetail(<?= $booking['id_booking']; ?>)">
                                            <i class="fa-solid fa-search"></i> Detail
                                        </button>
                                        <?php if ($status === 'pending') : ?>
                                            <button class="btn-action btn-continue" type="button" onclick="lanjutkanPembayaran(<?= $booking['id_booking']; ?>)">
                                                <i class="fa-solid fa-credit-card"></i> Bayar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- ✅ PAYMENT MODAL (FIXED: display none by default) -->
    <div id="modal-payment-midtrans" class="payment-modal-overlay">
        <div class="payment-modal-content">
            <p id="midtrans-status-message" class="payment-modal-text">Menyiapkan pembayaran...</p>
            <button onclick="closePaymentModal()" class="payment-modal-btn">Tutup</button>
        </div>
    </div>

    <!-- ✅ LOAD JAVASCRIPT FILES -->
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        // ✅ SIMULASI DATA LENGKAP TRANSAKSI
        const transactionDetails = {
            28: {
                booking_id: 28,
                payment_id: 24,
                trip_name: 'Gunung Slamet',
                trip_via: 'Rambipuji',
                trip_date: '30 Okt 2025',
                total_price: 500000,
                status: 'pending',
                booked_date: '23 Okt 2025',
                participants_count: 1,
                basecamp: 'Basecamp Slamet',
                include: 'makan, minum, tenda',
                exclude: 'doa',
                sk: 'wajib membawa surat keterangan sehat dan fotokopi KTP. Pembayaran DP 50% harus dilakukan dalam 1x24 jam.',
                waktu_kumpul: '02:00',
                participants: [{
                    id: 55,
                    name: 'Samid',
                    email: 'samid@example.com',
                    phone: '6285362783678',
                    dob: '23 Okt 2025',
                    nik: '123456789'
                }]
            },
            26: {
                booking_id: 26,
                payment_id: 23,
                trip_name: 'Gunung Raung',
                trip_via: 'Bondowoso',
                trip_date: '26 Sep 2025',
                total_price: 400000,
                status: 'settlement',
                booked_date: '21 Okt 2025',
                participants_count: 1,
                basecamp: 'Base Camp Kalibaru',
                include: 'makan, transport pp',
                exclude: 'minum, asuransi',
                sk: 'peserta dilarang membawa barang yang tidak perlu dan wajib mematuhi protokol basecamp.',
                waktu_kumpul: '11:11',
                participants: [{
                    id: 48,
                    name: 'John Doe',
                    email: 'john@example.com',
                    phone: '6285362783678',
                    dob: '02 Okt 2025',
                    nik: '123321'
                }]
            },
            13: {
                status: 'cancelled',
                payment_id: 13,
                booking_id: 13,
                total_price: 600000
            },
            17: {
                status: 'expire',
                payment_id: 17,
                booking_id: 17,
                total_price: 300000
            },
        };

        const simulatedBookingData = {
            28: {
                snap_token: 'dummy-token-28'
            },
            26: {
                snap_token: 'dummy-token-26'
            },
            13: {
                message: 'Transaksi dibatalkan.'
            },
            17: {
                message: 'Transaksi kadaluarsa.'
            },
        };

        function lanjutkanPembayaran(bookingId) {
            // ✅ Show modal dengan class 'active'
            const modal = document.getElementById('modal-payment-midtrans');
            modal.classList.add('active');
            document.getElementById('midtrans-status-message').textContent = "Meminta token pembayaran...";

            const data = simulatedBookingData[bookingId];

            if (data && data.snap_token) {
                document.getElementById('midtrans-status-message').textContent = "Membuka jendela pembayaran...";

                setTimeout(() => {
                    closePaymentModal();
                    Swal.fire({
                        title: 'Simulasi Pembayaran',
                        text: `Token SNAP berhasil didapatkan untuk #ID${bookingId}. Jendela Midtrans akan muncul di sini.`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Simulasi Sukses',
                        cancelButtonText: 'Simulasi Pending',
                        confirmButtonColor: '#a97c50',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sukses!',
                                text: 'Pembayaran Berhasil Disimulasikan.',
                                icon: 'success',
                                confirmButtonColor: '#a97c50'
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: 'Pending',
                                text: 'Pembayaran masih menunggu konfirmasi.',
                                icon: 'warning',
                                confirmButtonColor: '#a97c50'
                            });
                        }
                    });
                }, 1000);

            } else {
                setTimeout(() => {
                    closePaymentModal();
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Gagal mendapatkan token pembayaran.',
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                }, 1000);
            }
        }

        function closePaymentModal() {
            // ✅ Hide modal dengan remove class 'active'
            const modal = document.getElementById('modal-payment-midtrans');
            modal.classList.remove('active');
        }

        function formatStatusText(status) {
            switch (status.toLowerCase()) {
                case 'pending':
                    return '<span style="color:#e65100;">Menunggu Pembayaran</span>';
                case 'settlement':
                case 'paid':
                    return '<span style="color:#2e7d32;">Pembayaran Diterima</span>';
                case 'expire':
                    return '<span style="color:#c62828;">Kadaluarsa</span>';
                case 'cancelled':
                    return '<span style="color:#c62828;">Dibatalkan</span>';
                default:
                    return status;
            }
        }

        function showDetail(bookingId) {
            const data = transactionDetails[bookingId];

            if (!data) {
                Swal.fire({
                    title: 'Error',
                    text: 'Detail transaksi tidak ditemukan.',
                    icon: 'error',
                    confirmButtonColor: '#a97c50'
                });
                return;
            }

            const invoiceNumber = `INV-MDPL-PAY-${data.payment_id || 'N/A'}`;

            let participantsHTML = '<div class="participant-list-detail">';
            if (data.participants) {
                data.participants.forEach((p, index) => {
                    participantsHTML += `
                        <div class="participant-item">
                            <p><strong>${index + 1}. ${p.name}</strong></p>
                            <small>Email: ${p.email}</small> | <small>NIK: ${p.nik}</small>
                        </div>
                    `;
                });
            } else {
                participantsHTML += '<p style="color: #999;">Detail peserta tidak tersedia.</p>';
            }
            participantsHTML += '</div>';

            const invoiceUrl = `../user/view-invoice.php?payment_id=${data.payment_id}`;
            const isPaid = data.status.toLowerCase() === 'settlement' || data.status.toLowerCase() === 'paid';

            const invoiceButton = isPaid ?
                `<a href="${invoiceUrl}" target="_blank" class="btn-invoice-detail"><i class="fa-solid fa-file-invoice"></i> Lihat Invoice</a>` :
                `<button disabled class="btn-invoice-detail disabled-btn"><i class="fa-solid fa-times-circle"></i> Invoice Belum Tersedia</button>`;

            const modalContent = `
            <style>
                .swal2-popup { font-size: clamp(0.85rem, 2vw, 1rem) !important; padding: clamp(15px, 3vw, 25px) !important; }
                .swal2-title { color: #a97c50 !important; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px; font-size: clamp(1.1rem, 4vw, 1.5rem) !important; }
                .info-box-group { margin-bottom: 20px; border: 1px solid #eee; border-radius: 10px; padding: clamp(12px, 2.5vw, 15px); background: #fcfcfc; }
                .info-box-group h4 { color: #333; margin-bottom: 8px; font-weight: 600; font-size: clamp(0.95rem, 2.5vw, 1.1rem); border-bottom: 1px dashed #ddd; padding-bottom: 5px; display: flex; align-items: center; gap: 8px; }
                .info-box-group h4 i { color: #a97c50; }
                .info-row-detail { display: flex; justify-content: space-between; padding: 5px 0; font-size: clamp(0.8rem, 2vw, 0.95rem); flex-wrap: wrap; gap: 5px; }
                .info-row-detail strong { color: #222; }
                .info-row-detail span { color: #555; }
                .total-price-detail { font-size: clamp(1.1rem, 3vw, 1.4rem); color: #a97c50; font-weight: 700; margin-top: 8px; }
                .participant-list-detail { margin-top: 10px; max-height: 120px; overflow-y: auto; padding: 0 5px; }
                .participant-item { padding: 6px 0; border-bottom: 1px dotted #e0e0e0; }
                .participant-item p { margin: 0; font-weight: 600; color: #444; font-size: clamp(0.85rem, 2vw, 0.95rem); }
                .participant-item small { color: #777; font-size: clamp(0.75rem, 1.8vw, 0.8rem); }
                .btn-invoice-detail { margin-top: 15px; background: #333; color: #fff; padding: clamp(8px, 2vw, 10px) clamp(15px, 3vw, 20px); border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border: none; font-weight: 600; font-size: clamp(0.85rem, 2vw, 0.95rem); }
                .btn-invoice-detail:hover:not(:disabled) { background: #000; }
                .disabled-btn { background: #ccc; color: #777; cursor: not-allowed; }
                .invoice-section { border-top: 1px solid #ddd; padding-top: 15px; text-align: center; }
                @media (max-width: 480px) {
                    .info-row-detail { flex-direction: column; gap: 2px; }
                    .info-row-detail span { font-size: 0.8rem; }
                    .info-row-detail strong { font-size: 0.9rem; }
                }
            </style>
            
            <div class="transaction-detail-content" style="text-align: left;">
                <div class="info-box-group">
                    <h4><i class="fa-solid fa-receipt"></i> Ringkasan Transaksi</h4>
                    <div class="info-row-detail"><span>Nomor Invoice:</span> <strong>${invoiceNumber}</strong></div>
                    <div class="info-row-detail"><span>ID Booking:</span> <strong>#${data.booking_id}</strong></div>
                    <div class="info-row-detail"><span>Tanggal Pesan:</span> <strong>${data.booked_date || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Status Pembayaran:</span> <strong>${formatStatusText(data.status)}</strong></div>
                    <div class="info-row-detail"><span>Jumlah Peserta:</span> <strong>${data.participants_count || 'N/A'} Orang</strong></div>
                    <div class="info-row-detail"><span>Total Tagihan:</span> <strong class="total-price-detail">Rp ${data.total_price ? data.total_price.toLocaleString('id-ID') : 'N/A'}</strong></div>
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-mountain"></i> Detail Trip</h4>
                    <div class="info-row-detail"><span>Nama Trip:</span> <strong>${data.trip_name || 'N/A'} (Via ${data.trip_via || 'N/A'})</strong></div>
                    <div class="info-row-detail"><span>Tanggal Trip:</span> <strong>${data.trip_date || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Waktu Kumpul:</span> <strong>${data.waktu_kumpul || 'N/A'} WIB</strong></div>
                    <div class="info-row-detail"><span>Lokasi Kumpul:</span> <strong>${data.basecamp || 'N/A'}</strong></div>
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-clipboard-list"></i> Info Penting</h4>
                    <div style="padding: 5px 0;"><span><strong>Include:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.include || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Exclude:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.exclude || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Syarat & Ketentuan:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.sk || 'N/A'}</small></div>
                </div>
                
                <div class="info-box-group">
                    <h4><i class="fa-solid fa-users"></i> Daftar Peserta (${data.participants_count || 'N/A'} Orang)</h4>
                    ${participantsHTML}
                </div>
                
                <div class="invoice-section">
                    ${invoiceButton}
                </div>
            </div>
        `;

            Swal.fire({
                title: `Detail Transaksi #${data.booking_id}`,
                html: modalContent,
                icon: false,
                width: 'clamp(300px, 90vw, 700px)',
                showCloseButton: true,
                showConfirmButton: false,
                focusConfirm: false,
            });
        }
    </script>
</body>

</html>
