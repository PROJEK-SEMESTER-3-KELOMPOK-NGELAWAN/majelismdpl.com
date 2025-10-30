<?php
require_once '../backend/koneksi.php';
session_start();

$navbarPath = '../';

if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Auto-check pending payments
$pendingStmt = $conn->prepare("SELECT DISTINCT p.order_id FROM payments p
    JOIN bookings b ON p.id_booking = b.id_booking
    WHERE b.id_user = ? AND p.status_pembayaran = 'pending' AND p.order_id IS NOT NULL AND p.order_id != ''");

if ($pendingStmt) {
    $pendingStmt->bind_param("i", $id_user);
    $pendingStmt->execute();
    $pendingResult = $pendingStmt->get_result();

    while ($row = $pendingResult->fetch_assoc()) {
        $order_id = $row['order_id'];
        $check_url = '../backend/payment-api.php?check_status=' . urlencode($order_id);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $check_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
    $pendingStmt->close();
}

// Get booking data
$stmt = $conn->prepare("SELECT b.id_booking, b.tanggal_booking, b.total_harga, b.jumlah_orang, b.status as booking_status,
    t.nama_gunung, t.jenis_trip, p.id_payment, p.status_pembayaran, p.order_id, p.tanggal as payment_date
    FROM bookings b JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN payments p ON b.id_booking = p.id_booking
    WHERE b.id_user = ? ORDER BY b.tanggal_booking DESC");

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$booking_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function get_status_class($status) {
    $s = strtolower($status);
    if ($s === 'pending') return 'pending';
    if ($s === 'paid' || $s === 'settlement') return 'paid';
    return 'cancelled';
}

function format_status($status) {
    $s = strtolower($status);
    if ($s === 'pending') return '<i class="fa-solid fa-hourglass-half"></i> Pending';
    if ($s === 'paid' || $s === 'settlement') return '<i class="fa-solid fa-check-circle"></i> Paid';
    if ($s === 'expire') return '<i class="fa-solid fa-clock-rotate-left"></i> Expired';
    if ($s === 'failed') return '<i class="fa-solid fa-times-circle"></i> Failed';
    return '<i class="fa-solid fa-ban"></i> Cancelled';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Majelis MDPL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 20px 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(169, 124, 80, 0.15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            text-align: center;
        }
        .title {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3D2F21 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .title i {
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }
        .subtitle {
            font-size: 0.8rem;
            color: #6B5847;
        }

        /* Payment Cards Compact */
        .cards {
            display: flex;
            flex-direction: column;
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
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-body {
            padding: 18px;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3D2F21;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-title i { color: #a97c50; font-size: 1rem; }
        .card-id {
            font-size: 0.75rem;
            color: #a97c50;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .card-info {
            display: flex;
            gap: 20px;
            padding-top: 12px;
            border-top: 1px solid rgba(169, 124, 80, 0.1);
        }
        .info-item {
            flex: 1;
        }
        .info-label {
            font-size: 0.7rem;
            color: #6B5847;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .info-label i { font-size: 0.7rem; }
        .info-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #3D2F21;
        }
        .info-value.price {
            font-size: 1rem;
            color: #a97c50;
            font-weight: 700;
        }

        .card-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 18px 15px;
            border-left: 1px solid rgba(169, 124, 80, 0.1);
            background: rgba(169, 124, 80, 0.02);
            gap: 10px;
            min-width: 140px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        .status-badge.pending {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            color: #333;
        }
        .status-badge.paid {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
        }
        .status-badge.cancelled {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #fff;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }
        .btn {
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .btn-detail {
            background: linear-gradient(135deg, #4a4a4a 0%, #2d2d2d 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }
        .btn-pay {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(169, 124, 80, 0.25);
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
        }

        /* Empty State */
        .empty {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 50px 30px;
            text-align: center;
            border: 2px dashed rgba(169, 124, 80, 0.2);
        }
        .empty i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: #fff;
            padding: 25px 20px;
            max-width: 400px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .modal-text {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 15px;
        }
        .modal-btn {
            margin-top: 10px;
            background: #eee;
            border: 1px solid #ccc;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .modal-btn:hover { background: #ddd; }

        /* Refresh Indicator */
        .refresh-indicator {
            position: fixed;
            top: 90px;
            right: 20px;
            background: rgba(169, 124, 80, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 6px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        .refresh-indicator.show { display: flex; }
        .refresh-indicator i { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* SweetAlert */
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
        .swal2-close { font-size: 1.8rem !important; color: rgba(255, 255, 255, 0.8) !important; }

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
            font-size: 1rem;
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
        .info-row:last-child { border-bottom: none; }
        .info-row span { color: #6B5847; }
        .info-row strong { color: #3D2F21; font-weight: 600; }
        .price-total {
            font-size: 1.2rem;
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            font-weight: 800;
        }
        .participant-list { margin-top: 10px; max-height: 150px; overflow-y: auto; }
        .participant-item { padding: 6px 0; border-bottom: 1px dotted #e0e0e0; }
        .participant-item:last-child { border-bottom: none; }
        .participant-item p { margin: 0; font-weight: 600; color: #444; font-size: 0.85rem; margin-bottom: 3px; }
        .participant-item small { color: #777; font-size: 0.7rem; margin-right: 5px; }
        
        /* Button Invoice - Updated */
        .btn-invoice {
            margin-top: 12px;
            background: linear-gradient(135deg, #333 0%, #000 100%);
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
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            text-transform: uppercase;
            border: none;
            cursor: pointer;
        }
        .btn-invoice:hover {
            background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
            transform: translateY(-2px);
        }
        .btn-invoice:disabled {
            background: #ccc;
            color: #777;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .container { padding: 15px 12px; }
            .header { padding: 15px 18px; border-radius: 12px; }
            .title { font-size: 1.3rem; flex-direction: column; }
            .card {
                grid-template-columns: 1fr;
            }
            .card-sidebar {
                border-left: none;
                border-top: 1px solid rgba(169, 124, 80, 0.1);
                flex-direction: row;
                min-width: auto;
                justify-content: space-between;
            }
            .actions { flex-direction: row; }
            .info-row { flex-direction: column; gap: 2px; }
            .refresh-indicator { top: 75px; right: 10px; font-size: 0.7rem; padding: 6px 10px; }
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div id="refresh-indicator" class="refresh-indicator">
        <i class="fa-solid fa-sync"></i>
        <span>Updating...</span>
    </div>

    <div class="container">
        <div class="header">
            <h1 class="title"><i class="fa-solid fa-credit-card"></i><span>Payment Status</span></h1>
            <p class="subtitle">Track your booking transactions</p>
        </div>

        <?php if (empty($booking_list)): ?>
            <div class="empty">
                <i class="fa-solid fa-receipt"></i>
                <h2>No Transactions</h2>
                <p>You don't have any booking history yet</p>
                <a href="<?= $navbarPath; ?>index.php#paketTrips" class="btn-explore">
                    <i class="fa-solid fa-compass"></i> Explore Trips
                </a>
            </div>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($booking_list as $b):
                    $status = strtolower($b['status_pembayaran'] ?? 'pending');
                    $status_class = get_status_class($status);
                    $status_text = format_status($status);
                ?>
                    <div class="card" data-booking-id="<?= $b['id_booking']; ?>" data-order-id="<?= htmlspecialchars($b['order_id'] ?? ''); ?>">
                        <div class="card-body">
                            <h3 class="card-title">
                                <i class="fa-solid fa-mountain-sun"></i>
                                <?= htmlspecialchars($b['nama_gunung']); ?>
                            </h3>
                            <p class="card-id">Booking ID: #<?= $b['id_booking']; ?></p>
                            <div class="card-info">
                                <div class="info-item">
                                    <p class="info-label"><i class="fa-solid fa-calendar-alt"></i> Date</p>
                                    <p class="info-value"><?= date("d M Y", strtotime($b['tanggal_booking'])); ?></p>
                                </div>
                                <div class="info-item">
                                    <p class="info-label"><i class="fa-solid fa-tag"></i> Total</p>
                                    <p class="info-value price">Rp <?= number_format($b['total_harga'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-sidebar">
                            <div class="status-badge <?= $status_class; ?>"><?= $status_text; ?></div>
                            <div class="actions">
                                <button class="btn btn-detail" onclick="showDetail(<?= $b['id_booking']; ?>)">
                                    <i class="fa-solid fa-search"></i> Detail
                                </button>
                                <?php if ($status === 'pending' && !empty($b['order_id'])): ?>
                                    <button class="btn btn-pay" onclick="pay(<?= $b['id_booking']; ?>)">
                                        <i class="fa-solid fa-credit-card"></i> Pay
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="modal-payment" class="modal">
        <div class="modal-content">
            <p id="modal-text" class="modal-text">Preparing payment...</p>
            <button onclick="closeModal()" class="modal-btn">Close</button>
        </div>
    </div>

    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card[data-order-id]').forEach(card => {
                const orderId = card.getAttribute('data-order-id');
                const badge = card.querySelector('.status-badge');
                if (orderId && orderId.trim() !== '' && badge && badge.classList.contains('pending')) {
                    checkStatus(orderId);
                }
            });
        });

        function checkStatus(orderId) {
            fetch('../backend/payment-api.php?check_status=' + encodeURIComponent(orderId))
                .then(r => r.json())
                .then(resp => {
                    if (resp.success && resp.status === 'paid') {
                        showRefresh();
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(err => console.log('Check error:', err));
        }

        function showRefresh() {
            const ind = document.getElementById('refresh-indicator');
            if (ind) ind.classList.add('show');
        }

        function pay(bookingId) {
            const modal = document.getElementById('modal-payment');
            modal.classList.add('active');
            document.getElementById('modal-text').textContent = "Requesting payment token...";

            fetch('../backend/payment-api.php?booking=' + bookingId)
                .then(r => r.json())
                .then(resp => {
                    if (resp.snap_token) {
                        document.getElementById('modal-text').textContent = "Opening payment...";
                        setTimeout(() => {
                            closeModal();
                            window.snap.pay(resp.snap_token, {
                                onSuccess: () => {
                                    showRefresh();
                                    fetch('../backend/payment-api.php?check_status=' + resp.order_id)
                                        .then(r => r.json())
                                        .then(s => {
                                            Swal.fire({
                                                title: s.status === 'paid' ? 'Payment Success!' : 'Payment Processed',
                                                text: s.status === 'paid' ? 'Booking confirmed' : 'Waiting confirmation',
                                                icon: s.status === 'paid' ? 'success' : 'info',
                                                confirmButtonColor: '#a97c50'
                                            }).then(() => window.location.reload());
                                        });
                                },
                                onPending: () => {
                                    Swal.fire({
                                        title: 'Payment Pending',
                                        text: 'Please complete your payment',
                                        icon: 'info',
                                        confirmButtonColor: '#a97c50'
                                    }).then(() => window.location.reload());
                                },
                                onError: (r) => {
                                    Swal.fire({
                                        title: 'Payment Failed',
                                        text: r.status_message || 'Error occurred',
                                        icon: 'error',
                                        confirmButtonColor: '#a97c50'
                                    });
                                },
                                onClose: () => setTimeout(() => window.location.reload(), 1000)
                            });
                        }, 500);
                    } else throw new Error(resp.error || 'Failed to get token');
                })
                .catch(err => {
                    closeModal();
                    Swal.fire({
                        title: 'Payment Error',
                        text: err.message,
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                });
        }

        function closeModal() {
            document.getElementById('modal-payment').classList.remove('active');
        }

        function showDetail(bookingId) {
            Swal.fire({
                title: 'Loading...',
                html: '<i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:#a97c50;"></i>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`../backend/get-booking-detail.php?id=${bookingId}`)
                .then(r => r.json())
                .then(d => {
                    if (d.error) {
                        Swal.fire({ title: 'Error', text: d.error, icon: 'error', confirmButtonColor: '#a97c50' });
                        return;
                    }

                    const inv = `INV-MDPL-${d.id_payment || 'N/A'}`;
                    const isPaid = d.status_pembayaran === 'paid' || d.status_pembayaran === 'settlement';

                    let parts = '<div class="participant-list">';
                    if (d.participants && d.participants.length > 0) {
                        d.participants.forEach((p, i) => {
                            parts += `<div class="participant-item"><p><strong>${i+1}. ${p.nama}</strong></p>
                                <small>📧 ${p.email}</small><br>
                                <small>📱 ${p.no_wa}</small> | <small>🆔 ${p.nik}</small></div>`;
                        });
                    } else parts += '<p style="color:#999">No participant data</p>';
                    parts += '</div>';

                    // ✅ Button untuk redirect ke halaman invoice (tanpa target="_blank")
                    const invBtn = isPaid ? 
                        `<a href="view-invoice.php?payment_id=${d.id_payment}" class="btn-invoice">
                            <i class="fa-solid fa-file-invoice"></i> View Invoice
                        </a>` :
                        `<button disabled class="btn-invoice">
                            <i class="fa-solid fa-times-circle"></i> Invoice N/A
                        </button>`;

                    const fmt = s => {
                        if (s === 'paid' || s === 'settlement') return '<span style="color:#2e7d32">✅ Paid</span>';
                        if (s === 'pending') return '<span style="color:#e65100">⏳ Pending</span>';
                        return '<span style="color:#c62828">❌ ' + s + '</span>';
                    };

                    Swal.fire({
                        title: `Transaction #${d.id_booking}`,
                        html: `<div style="text-align:left">
                            <div class="info-group">
                                <h4><i class="fa-solid fa-receipt"></i> Summary</h4>
                                <div class="info-row"><span>Invoice:</span><strong>${inv}</strong></div>
                                <div class="info-row"><span>Booking:</span><strong>#${d.id_booking}</strong></div>
                                <div class="info-row"><span>Date:</span><strong>${d.tanggal_booking_formatted}</strong></div>
                                <div class="info-row"><span>Status:</span>${fmt(d.status_pembayaran)}</div>
                                <div class="info-row"><span>Participants:</span><strong>${d.jumlah_orang} People</strong></div>
                                <div class="info-row"><span>Total:</span><strong class="price-total">Rp ${parseInt(d.total_harga).toLocaleString('id-ID')}</strong></div>
                            </div>
                            <div class="info-group">
                                <h4><i class="fa-solid fa-mountain"></i> Trip Details</h4>
                                <div class="info-row"><span>Mountain:</span><strong>${d.nama_gunung}</strong></div>
                                <div class="info-row"><span>Type:</span><strong>${d.jenis_trip||'N/A'}</strong></div>
                                <div class="info-row"><span>Date:</span><strong>${d.tanggal_trip_formatted}</strong></div>
                                <div class="info-row"><span>Duration:</span><strong>${d.durasi||'N/A'}</strong></div>
                                <div class="info-row"><span>Time:</span><strong>${d.waktu_kumpul||'N/A'} WIB</strong></div>
                                <div class="info-row"><span>Location:</span><strong>${d.nama_lokasi||'N/A'}</strong></div>
                            </div>
                            <div class="info-group">
                                <h4><i class="fa-solid fa-users"></i> Participants (${d.jumlah_orang})</h4>
                                ${parts}
                            </div>
                            <div style="text-align:center;padding-top:10px;border-top:1px solid #ddd">${invBtn}</div>
                        </div>`,
                        width: '700px',
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    Swal.fire({ title: 'Error', text: err.message, icon: 'error', confirmButtonColor: '#a97c50' });
                });
        }
    </script>
</body>
</html>
