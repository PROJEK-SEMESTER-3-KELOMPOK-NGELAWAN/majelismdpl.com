<?php
// ✅ INCLUDE DATABASE
require_once '../backend/koneksi.php';
session_start();

// ✅ SET NAVBAR PATH
$navbarPath = '../';

// ✅ CEK STATUS LOGIN
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ✅ QUERY REAL DATA DARI DATABASE
$stmt = $conn->prepare("
    SELECT 
        b.id_booking,
        b.tanggal_booking,
        b.total_harga,
        b.jumlah_orang,
        b.status as booking_status,
        t.nama_gunung,
        t.jenis_trip,
        p.id_payment,
        p.status_pembayaran,
        p.order_id,
        p.tanggal as payment_date
    FROM bookings b
    JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN payments p ON b.id_booking = p.id_booking
    WHERE b.id_user = ?
    ORDER BY b.tanggal_booking DESC
");

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$booking_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
            return '<i class="fa-solid fa-question-circle"></i> ' . ucwords($status);
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
        /* ... CSS yang sama seperti sebelumnya ... */
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
                            $status = strtolower($booking['status_pembayaran'] ?? 'pending');
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
                                        <?= htmlspecialchars($booking['nama_gunung']); ?>
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
                                        <?php if ($status === 'pending' && !empty($booking['order_id'])) : ?>
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

    <!-- ✅ PAYMENT MODAL -->
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
        // ✅ FUNGSI LANJUTKAN PEMBAYARAN (REAL INTEGRATION)
        function lanjutkanPembayaran(bookingId) {
            const modal = document.getElementById('modal-payment-midtrans');
            modal.classList.add('active');
            document.getElementById('midtrans-status-message').textContent = "Meminta token pembayaran...";

            fetch('../backend/payment-api.php?booking=' + bookingId)
                .then(r => {
                    const contentType = r.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server error - bukan JSON');
                    }
                    return r.json();
                })
                .then(resp => {
                    if (resp.snap_token) {
                        document.getElementById('midtrans-status-message').textContent = "Membuka jendela pembayaran...";

                        setTimeout(() => {
                            closePaymentModal();

                            // ✅ BUKA MIDTRANS SNAP
                            window.snap.pay(resp.snap_token, {
                                onSuccess: (result) => {
                                    // ✅ AUTO CHECK STATUS SETELAH PEMBAYARAN
                                    fetch('../backend/check-payment-status.php?order_id=' + resp.order_id)
                                        .then(r => r.json())
                                        .then(statusResp => {
                                            if (statusResp.status === 'paid') {
                                                Swal.fire({
                                                    title: 'Pembayaran Berhasil!',
                                                    text: 'Booking Anda telah dikonfirmasi.',
                                                    icon: 'success',
                                                    confirmButtonColor: '#a97c50'
                                                }).then(() => {
                                                    window.location.reload();
                                                });
                                            } else {
                                                Swal.fire({
                                                    title: 'Pembayaran Diproses',
                                                    text: 'Menunggu konfirmasi pembayaran.',
                                                    icon: 'info',
                                                    confirmButtonColor: '#a97c50'
                                                }).then(() => {
                                                    window.location.reload();
                                                });
                                            }
                                        })
                                        .catch(err => {
                                            console.error('Status check error:', err);
                                            window.location.reload();
                                        });
                                },
                                onPending: (result) => {
                                    Swal.fire({
                                        title: 'Pembayaran Pending',
                                        text: 'Silakan selesaikan pembayaran Anda.',
                                        icon: 'info',
                                        confirmButtonColor: '#a97c50'
                                    });
                                },
                                onError: (result) => {
                                    Swal.fire({
                                        title: 'Pembayaran Gagal',
                                        text: result.status_message || 'Terjadi kesalahan',
                                        icon: 'error',
                                        confirmButtonColor: '#a97c50'
                                    });
                                },
                                onClose: () => {
                                    console.log('Popup ditutup');
                                }
                            });
                        }, 500);

                    } else {
                        throw new Error(resp.error || 'Gagal mendapatkan token');
                    }
                })
                .catch(err => {
                    console.error('Payment error:', err);
                    closePaymentModal();
                    Swal.fire({
                        title: 'Error Pembayaran',
                        text: err.message,
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                });
        }

        function closePaymentModal() {
            const modal = document.getElementById('modal-payment-midtrans');
            modal.classList.remove('active');
        }

        // ✅ FUNGSI SHOW DETAIL (FETCH DARI DATABASE)
        function showDetail(bookingId) {
            Swal.fire({
                title: 'Memuat Detail...',
                html: '<i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:#a97c50;"></i>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`../backend/get-booking-detail.php?id=${bookingId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire({
                            title: 'Error',
                            text: data.error,
                            icon: 'error',
                            confirmButtonColor: '#a97c50'
                        });
                        return;
                    }

                    // ✅ RENDER DETAIL LENGKAP
                    const invoiceNumber = `INV-MDPL-PAY-${data.id_payment || 'N/A'}`;
                    const isPaid = data.status_pembayaran === 'paid' || data.status_pembayaran === 'settlement';
                    const invoiceUrl = `view-invoice.php?payment_id=${data.id_payment}`;

                    let participantsHTML = '<div class="participant-list-detail">';
                    if (data.participants && data.participants.length > 0) {
                        data.participants.forEach((p, index) => {
                            participantsHTML += `
                        <div class="participant-item">
                            <p><strong>${index + 1}. ${p.nama}</strong></p>
                            <small>📧 ${p.email}</small><br>
                            <small>📱 ${p.no_wa}</small> | <small>🆔 ${p.nik}</small><br>
                            <small>🎂 ${p.tempat_lahir}, ${p.tanggal_lahir}</small>
                        </div>
                    `;
                        });
                    } else {
                        participantsHTML += '<p style="color: #999;">Detail peserta tidak tersedia.</p>';
                    }
                    participantsHTML += '</div>';

                    const invoiceButton = isPaid ?
                        `<a href="${invoiceUrl}" target="_blank" class="btn-invoice-detail"><i class="fa-solid fa-file-invoice"></i> Lihat Invoice</a>` :
                        `<button disabled class="btn-invoice-detail disabled-btn"><i class="fa-solid fa-times-circle"></i> Invoice Belum Tersedia</button>`;

                    const formatStatus = (status) => {
                        if (status === 'paid' || status === 'settlement') return '<span style="color:#2e7d32;">✅ Lunas</span>';
                        if (status === 'pending') return '<span style="color:#e65100;">⏳ Menunggu Pembayaran</span>';
                        return '<span style="color:#c62828;">❌ ' + status + '</span>';
                    };

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
                .participant-list-detail { margin-top: 10px; max-height: 180px; overflow-y: auto; padding: 0 5px; }
                .participant-item { padding: 8px 0; border-bottom: 1px dotted #e0e0e0; }
                .participant-item:last-child { border-bottom: none; }
                .participant-item p { margin: 0; font-weight: 600; color: #444; font-size: clamp(0.85rem, 2vw, 0.95rem); margin-bottom: 3px; }
                .participant-item small { color: #777; font-size: clamp(0.75rem, 1.8vw, 0.8rem); display: inline-block; margin-right: 5px; }
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
                    <div class="info-row-detail"><span>ID Booking:</span> <strong>#${data.id_booking}</strong></div>
                    <div class="info-row-detail"><span>Tanggal Pesan:</span> <strong>${data.tanggal_booking_formatted}</strong></div>
                    <div class="info-row-detail"><span>Status Pembayaran:</span> <strong>${formatStatus(data.status_pembayaran)}</strong></div>
                    <div class="info-row-detail"><span>Jumlah Peserta:</span> <strong>${data.jumlah_orang} Orang</strong></div>
                    <div class="info-row-detail"><span>Total Tagihan:</span> <strong class="total-price-detail">Rp ${parseInt(data.total_harga).toLocaleString('id-ID')}</strong></div>
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-mountain"></i> Detail Trip</h4>
                    <div class="info-row-detail"><span>Nama Trip:</span> <strong>${data.nama_gunung}</strong></div>
                    <div class="info-row-detail"><span>Via:</span> <strong>${data.jenis_trip || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Tanggal Trip:</span> <strong>${data.tanggal_trip_formatted}</strong></div>
                    <div class="info-row-detail"><span>Durasi:</span> <strong>${data.durasi || 'N/A'}</strong></div>
                    <div class="info-row-detail"><span>Waktu Kumpul:</span> <strong>${data.waktu_kumpul || 'N/A'} WIB</strong></div>
                    <div class="info-row-detail"><span>Lokasi Kumpul:</span> <strong>${data.nama_lokasi || 'N/A'}</strong></div>
                </div>

                <div class="info-box-group">
                    <h4><i class="fa-solid fa-clipboard-list"></i> Info Penting</h4>
                    <div style="padding: 5px 0;"><span><strong>Include:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.include || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Exclude:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.exclude || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span><strong>Syarat & Ketentuan:</strong></span> <br><small style="margin-left: 10px; display: block;font-size:clamp(0.8rem, 2vw, 0.9rem);">${data.syarat_ketentuan || 'N/A'}</small></div>
                </div>
                
                <div class="info-box-group">
                    <h4><i class="fa-solid fa-users"></i> Daftar Peserta (${data.jumlah_orang} Orang)</h4>
                    ${participantsHTML}
                </div>
                
                <div class="invoice-section">
                    ${invoiceButton}
                </div>
            </div>
        `;

                    Swal.fire({
                        title: `Detail Transaksi #${data.id_booking}`,
                        html: modalContent,
                        icon: false,
                        width: 'clamp(300px, 90vw, 700px)',
                        showCloseButton: true,
                        showConfirmButton: false,
                        focusConfirm: false,
                    });
                })
                .catch(err => {
                    console.error('Detail error:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat detail transaksi: ' + err.message,
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                });
        }
    </script>
</body>

</html>