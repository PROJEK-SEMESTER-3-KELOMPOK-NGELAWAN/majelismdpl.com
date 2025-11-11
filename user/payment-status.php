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

// Auto-expire pending >24 jam (non-blocking ping)
$expire_url = getPageUrl('backend/payment-api.php') . '?expire_stale=1';
$chx = curl_init();
curl_setopt($chx, CURLOPT_URL, $expire_url);
curl_setopt($chx, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chx, CURLOPT_TIMEOUT, 3);
curl_setopt($chx, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($chx);
curl_close($chx);

// Auto-check pending payments (server-side) agar status segera tersinkron saat page load
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

function get_status_class($status)
{
    $s = strtolower($status ?? '');
    if ($s === 'pending') return 'pending';
    if ($s === 'paid' || $s === 'settlement') return 'paid';
    return 'cancelled';
}

/**
 * REVISI: Fungsi untuk tampilan status di kartu (ikon di atas teks, 2 baris)
 */
function format_status_card($status)
{
    $s = strtolower($status ?? '');
    $icon = '';
    $text = '';

    if ($s === 'pending') {
        $icon = 'fa-solid fa-hourglass-half';
        $text = 'MENUNGGU<br>PEMBAYARAN';
    } elseif ($s === 'paid' || $s === 'settlement') {
        $icon = 'fa-solid fa-circle-check';
        $text = 'PEMBAYARAN<br>DITERIMA';
    } elseif ($s === 'expire') {
        $icon = 'fa-solid fa-clock-rotate-left';
        $text = 'SUDAH<br>KEDALUWARSA';
    } else { // 'failed', 'cancel', default
        $icon = 'fa-solid fa-ban';
        $text = 'DIBATALKAN';
    }

    return [
        'icon' => $icon,
        'text' => $text
    ];
}

/**
 * REVISI: Fungsi untuk tampilan status detail di modal (ikon di samping teks, 1 baris)
 */
function format_status_detail($status)
{
    $s = strtolower($status ?? '');

    $status_data = [
        'text' => 'DIBATALKAN',
        'color' => '#c62828',
        'icon' => '<i class="fa-solid fa-times-circle"></i>'
    ];

    if ($s === 'paid' || $s === 'settlement') {
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
    } elseif ($s === 'cancel' || $s === 'failed') {
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
    <title>Status Pembayaran - Majelis MDPL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* CSS DEFAULT DARI AWAL (DIUBAH HANYA BAGIAN STATUS CARD & EMPTY STATE) */
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

        .card-title i {
            color: #a97c50;
            font-size: 1rem;
        }

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

        .info-label i {
            font-size: 0.7rem;
        }

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
            min-width: 170px;
        }

        /* CSS untuk tampilan status card (ikon di atas teks, 2 baris) */
        .status-badge {
            padding: 0;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            white-space: normal;
            text-align: center;
            line-height: 1.1;
        }

        .status-badge i {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .status-badge span {
            font-weight: 800;
        }

        .status-badge.pending {
            color: #ffb800;
        }

        .status-badge.pending i {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .status-badge.paid {
            color: #28a745;
        }

        .status-badge.paid i {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .status-badge.cancelled {
            color: #dc3545;
        }

        .status-badge.cancelled i {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        /* Akhir CSS Status Card */

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

        /* CSS BARU untuk Empty State (Mengikuti mytrip.php) */
        .empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 18px;
            margin-top: 30px;
            border: 2px solid rgba(169, 124, 80, .2);
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
        }

        /* Gaya Ikon Besar Baru */
        .empty .empty-icon {
            font-size: 6rem;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 4px 8px rgba(169, 124, 80, 0.4));
        }

        .empty h2 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #3D2F21;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .empty p {
            font-size: 1rem;
            color: #6B5847;
            margin-bottom: 35px;
            max-width: 500px;
            line-height: 1.6;
        }

        /* Tombol CTA Baru */
        .btn-explore {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(135deg, #a97c50 100%, #e6a700 0%);
            box-shadow: 0 5px 18px rgba(169, 124, 80, .4);
            transition: all .3s ease;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .btn-explore:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(169, 124, 80, .6);
            background: linear-gradient(135deg, #d4a574 100%, #ffc107 0%);
        }
        /* Akhir CSS BARU untuk Empty State */

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

        .modal.active {
            display: flex;
        }

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

        .modal-btn:hover {
            background: #ddd;
        }

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

        .refresh-indicator.show {
            display: flex;
        }

        .refresh-indicator i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

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
            background-clip: text;
            color: transparent;
            font-weight: 800;
        }

        .participant-list {
            margin-top: 10px;
            max-height: 150px;
            overflow-y: auto;
        }

        .participant-item {
            padding: 6px 0;
            border-bottom: 1px dotted #e0e0e0;
        }

        .participant-item:last-child {
            border-bottom: none;
        }

        .participant-item p {
            margin: 0;
            font-weight: 600;
            color: #444;
            font-size: 0.85rem;
            margin-bottom: 3px;
        }

        .participant-item small {
            color: #777;
            font-size: 0.7rem;
            margin-right: 5px;
        }

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
            box-shadow: 0 3px 10px rgba(0, 0, 0, .2);
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

        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }

            .container {
                padding: 15px 12px;
            }

            .header {
                padding: 15px 18px;
                border-radius: 12px;
            }

            .title {
                font-size: 1.3rem;
            }
            
            .title i {
                font-size: 1.4rem; 
            }

            .card {
                grid-template-columns: 1fr;
            }
            
            .card-info {
                flex-direction: column;
                gap: 10px;
            }

            .card-sidebar {
                border-left: none;
                border-top: 1px solid rgba(169, 124, 80, .1);
                /* Revisi: Ubah ke vertikal */
                flex-direction: column; 
                min-width: auto;
                padding: 15px 15px; 
                align-items: flex-start; 
                gap: 12px; 
            }

            .card-sidebar .status-badge {
                /* Status badge dalam mode horizontal, sekarang memanjang ke kiri */
                flex-direction: row;
                gap: 8px;
                padding: 0;
                font-size: 0.85rem;
                text-align: left;
                align-items: center;
                width: 100%; 
            }
            
            .card-sidebar .status-badge span {
                display: inline;
                font-size: 0.85rem; 
                line-height: 1.2;
            }

            .card-sidebar .status-badge i {
                font-size: 1.2rem;
                margin-bottom: 0;
            }

            .actions {
                /* Container untuk tombol "Detail" dan "Lanjut Bayar" */
                flex-direction: row;
                gap: 8px; 
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap; 
            }
            
            /* Aturan khusus untuk tombol "Batalkan" agar selalu di bawah/terpisah di baris baru */
            .actions .btn-detail[style*="b02a37"] { 
                order: 3; /* Pastikan tombol ini di posisi ketiga */
                width: 100%; /* Lebar penuh */
                margin-top: 5px; 
                font-size: 0.75rem;
            }
            
            /* TARGET UTAMA: Membuat tombol "Detail" dan "Lanjut Bayar" sama lebarnya */
            .actions .btn-detail,
            .actions .btn-pay {
                flex: 1; 
                width: calc(50% - 4px); /* Paksa 50% lebar dikurangi setengah gap (8px/2 = 4px) */
                padding: 8px 10px;
                font-size: 0.7rem; 
                min-width: 0; /* Hapus min-width agar kalkulasi 50% berhasil */
                box-sizing: border-box; /* Pastikan padding/border dihitung dalam lebar */
            }


            .info-row {
                flex-direction: column;
                gap: 2px;
            }

            .refresh-indicator {
                top: 75px;
                right: 10px;
                font-size: .7rem;
                padding: 6px 10px;
            }

            /* Penyesuaian responsif untuk Empty State */
            .empty h2 { font-size: 1.5rem; }
            .empty p { font-size: 0.9rem; }
            .empty .empty-icon { font-size: 5rem; }
            .btn-explore { padding: 12px 25px; font-size: 0.9rem; }
        }
        
        /* Tambahan: Media Query untuk layar sangat kecil */
        @media (max-width: 400px) {
            .card-sidebar {
                padding: 12px 10px;
                gap: 10px;
            }
            
            .actions {
                gap: 5px; /* Kurangi gap */
            }

            .actions .btn-detail,
            .actions .btn-pay {
                font-size: 0.65rem;
                padding: 7px 8px;
                /* Ulangi kalkulasi lebar 50% dengan gap 5px (5px/2 = 2.5px) */
                width: calc(50% - 2.5px); 
            }
            
            .actions .btn-detail[style*="b02a37"] {
                font-size: 0.7rem; 
                margin-top: 5px;
            }
        }


        /* --- Penyesuaian Global SweetAlert2 untuk Tampilan Ramping --- */
        .swal2-popup.custom-warning-popup {
            max-width: 450px !important;
            /* Lebar pop-up yang lebih kecil */
            padding: 20px 0 20px 0 !important;
            /* Memberi sedikit padding atas/bawah */
            border-radius: 20px !important;
        }

        /* Mengatur ulang style judul agar tebal dan tidak memiliki background */
        .swal2-popup.custom-warning-popup .swal2-title {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            color: #444 !important;
            padding: 0 !important;
            background: none !important;
            border-radius: 0 !important;
        }

        /* Mengatur ulang style teks agar rapi */
        .swal2-popup.custom-warning-popup .swal2-html-container {
            font-size: 1rem !important;
            color: #666;
            margin: 0 30px 20px 30px !important;
            padding: 0 !important;
        }

        /* Memastikan tombol Batal di kiri dan Ya, batalkan di kanan */
        .swal2-actions {
            gap: 15px !important;
            margin-top: 20px !important;
            flex-direction: row-reverse;
        }

        /* Gaya tombol Batal (Kiri, Abu-abu) */
        .swal2-cancel {
            background-color: #6c757d !important;
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            order: 1 !important;
        }

        /* Gaya tombol Konfirmasi (Kanan, Ungu) */
        .swal2-confirm {
            background-color: #7568c8 !important;
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            order: 2 !important;
        }

        /* --- CSS BARU UNTUK POP-UP BERHASIL / SUKSES (Centang Hijau Berbuletan) --- */
        .swal2-popup.custom-success-popup {
            max-width: 380px !important;
            padding: 20px 0 20px 0 !important;
            border-radius: 20px !important;
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
            box-sizing: border-box;
        }

        .custom-success-icon {
            font-size: 48px;
            color: #7bc07b;
        }

        /* Gaya Judul dan Teks untuk Pop-up Sukses */
        .swal2-popup.custom-success-popup .swal2-title {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            color: #444 !important;
            padding: 0 !important;
            background: none !important;
            border-radius: 0 !important;
        }

        .swal2-popup.custom-success-popup .swal2-html-container {
            font-size: 1rem !important;
            color: #666;
            margin: 0 30px 20px 30px !important;
            padding: 0 !important;
        }

        /* Pastikan tombol OK untuk sukses pop-up juga Ungu */
        .swal2-popup.custom-success-popup .swal2-confirm {
            background-color: #7568c8 !important;
            /* Ungu */
        }


        /* --- CSS BARU UNTUK POP-UP INFO / PENDING (Jam Pasir Berbuletan) --- */
        .swal2-popup.custom-info-popup {
            max-width: 380px !important;
            padding: 20px 0 20px 0 !important;
            border-radius: 20px !important;
        }

        .custom-info-icon-wrapper {
            width: 80px;
            height: 80px;
            border: 5px solid #ffddb3;
            /* Outline Oranye Muda */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px auto 20px auto;
            box-sizing: border-box;
        }

        .custom-info-icon {
            font-size: 48px;
            color: #ffb74d;
            /* Ikon Oranye */
        }

        /* Gaya Judul dan Teks untuk Pop-up Info */
        .swal2-popup.custom-info-popup .swal2-title {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            color: #444 !important;
            padding: 0 !important;
            background: none !important;
            border-radius: 0 !important;
        }

        .swal2-popup.custom-info-popup .swal2-html-container {
            font-size: 1rem !important;
            color: #666;
            margin: 0 30px 20px 30px !important;
            padding: 0 !important;
        }

        /* Pastikan tombol OK untuk info pop-up juga Ungu */
        .swal2-popup.custom-info-popup .swal2-confirm {
            background-color: #7568c8 !important;
            /* Ungu */
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div id="refresh-indicator" class="refresh-indicator">
        <i class="fa-solid fa-sync"></i>
        <span>Memperbarui...</span>
    </div>

    <div class="container">
        <div class="header">
            <h1 class="title"><i class="fa-solid fa-credit-card"></i><span>Status Pembayaran</span></h1>
            <p class="subtitle">Lacak transaksi pemesanan Anda</p>
        </div>

        <?php if (empty($booking_list)): ?>
            <div class="empty">
                <i class="fa-solid fa-money-bill-transfer empty-icon"></i> 
                <h2>Belum Ada Transaksi</h2>
                <p>Belum ada riwayat pembayaran yang tercatat. Mari mulai trip pertama Anda!</p>
                <a href="<?= getPageUrl('index.php') ?>#paketTrips" class="btn-explore">
                    <i class="fa-solid fa-compass"></i> Jelajahi Paket Trip
                </a>
            </div>
            <?php else: ?>
            <div class="cards">
                <?php foreach ($booking_list as $b):
                    $status = strtolower($b['status_pembayaran'] ?? 'pending');
                    $status_class = get_status_class($status);

                    // Menggunakan fungsi format_status_card
                    $card_status_data = format_status_card($status);
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
                                    <p class="info-label"><i class="fa-solid fa-calendar-alt"></i> Tanggal</p>
                                    <p class="info-value"><?= date("d M Y", strtotime($b['tanggal_booking'])); ?></p>
                                </div>
                                <div class="info-item">
                                    <p class="info-label"><i class="fa-solid fa-tag"></i> Total</p>
                                    <p class="info-value price">Rp <?= number_format($b['total_harga'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-sidebar">
                            <div class="status-badge <?= $status_class; ?>">
                                <i class="<?= $card_status_data['icon']; ?>"></i>
                                <span><?= $card_status_data['text']; ?></span>
                            </div>
                            <div class="actions">
                                <button class="btn btn-detail" onclick="showDetail(<?= $b['id_booking']; ?>)">
                                    <i class="fa-solid fa-search"></i> Detail
                                </button>
                                <?php if ($status === 'pending' && !empty($b['order_id'])): ?>
                                    <button class="btn btn-pay" onclick="pay(<?= $b['id_booking']; ?>)">
                                        <i class="fa-solid fa-credit-card"></i> Lanjut Bayar
                                    </button>
                                    <button class="btn btn-detail" style="background:linear-gradient(135deg,#b02a37 0%,#dc3545 100%);" onclick="cancelPayment(<?= $b['id_booking']; ?>)">
                                        <i class="fa-solid fa-ban"></i> Batalkan
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
            <p id="modal-text" class="modal-text">Menyiapkan pembayaran...</p>
            <button onclick="closeModal()" class="modal-btn">Tutup</button>
        </div>
    </div>

    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>
    <script>
        // Memindahkan semua fungsi JS ke konteks global untuk mengatasi ReferenceError

        function periodicRefresh() {
            const cards = Array.from(document.querySelectorAll('.card[data-order-id]'));
            const pendings = cards.filter(c => {
                const badge = c.querySelector('.status-badge');
                return badge && badge.classList.contains('pending');
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
                    if (resp && resp.success) {
                        if (['paid', 'failed', 'expire', 'cancel'].includes(resp.status)) {
                            showRefresh();
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } else if (!quiet) {
                        console.log('Status check info:', resp);
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
            document.getElementById('modal-text').textContent = "Meminta token pembayaran...";

            fetch('../backend/payment-api.php?booking=' + bookingId)
                .then(r => r.json())
                .then(resp => {
                    if (resp.snap_token) {
                        document.getElementById('modal-text').textContent = "Membuka halaman pembayaran...";
                        setTimeout(() => {
                            closeModal();
                            window.snap.pay(resp.snap_token, {
                                onSuccess: () => {
                                    showRefresh();
                                    fetch('../backend/payment-api.php?check_status=' + resp.order_id)
                                        .then(r => r.json())
                                        .then(s => {
                                            // --- POP-UP PEMBAYARAN BERHASIL (Centang Hijau Berbuletan) ---
                                            if (s.status === 'paid' || s.status === 'settlement') {
                                                Swal.fire({
                                                    html: `
                                                <div class="custom-success-icon-wrapper">
                                                    <i class="custom-success-icon fas fa-check"></i>
                                                </div>
                                                <div class="swal2-title" style="margin-bottom: 10px;">Pembayaran Berhasil!</div>
                                                <div class="swal2-html-container" style="margin-bottom: 20px;">Pemesanan dikonfirmasi.</div>
                                            `,
                                                    customClass: {
                                                        popup: 'custom-success-popup'
                                                    },
                                                    confirmButtonText: 'OK',
                                                    confirmButtonColor: '#7568c8' // Tombol OK Ungu
                                                }).then(() => window.location.reload());
                                            } else {
                                                // Status pending/diproses
                                                Swal.fire({
                                                    html: `
                                                <div class="custom-info-icon-wrapper">
                                                    <i class="custom-info-icon fas fa-hourglass-half"></i>
                                                </div>
                                                <div class="swal2-title" style="margin-bottom: 10px;">Pembayaran Diproses</div>
                                                <div class="swal2-html-container" style="margin-bottom: 20px;">Menunggu konfirmasi pembayaran.</div>
                                            `,
                                                    customClass: {
                                                        popup: 'custom-info-popup'
                                                    },
                                                    confirmButtonText: 'OK',
                                                    confirmButtonColor: '#a97c50' // Warna tema cokelat/info
                                                }).then(() => window.location.reload());
                                            }
                                        });
                                },
                                onPending: () => {
                                    // --- POP-UP PEMBAYARAN MENUNGGU (Jam Pasir Berbuletan) ---
                                    Swal.fire({
                                        html: `
                                    <div class="custom-info-icon-wrapper">
                                        <i class="custom-info-icon fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="swal2-title" style="margin-bottom: 10px;">Pembayaran Menunggu</div>
                                    <div class="swal2-html-container" style="margin-bottom: 20px;">Mohon selesaikan pembayaran Anda di halaman Midtrans.</div>
                                `,
                                        customClass: {
                                            popup: 'custom-info-popup'
                                        },
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#7568c8' // Tombol OK Ungu
                                    }).then(() => window.location.reload());
                                },
                                onError: (r) => {
                                    // --- POP-UP ERROR (Menggunakan ikon bawaan) ---
                                    Swal.fire({
                                        title: 'Pembayaran Gagal',
                                        text: r.status_message || 'Terjadi kesalahan',
                                        icon: 'error',
                                        confirmButtonColor: '#a97c50'
                                    });
                                },
                                onClose: () => setTimeout(() => window.location.reload(), 1000)
                            });
                        }, 500);
                    } else {
                        throw new Error(resp.detail || resp.message || resp.error || 'Gagal mendapatkan token');
                    }
                })
                .catch(err => {
                    closeModal();
                    Swal.fire({
                        title: 'Kesalahan Pembayaran',
                        text: err.message,
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                });
        }

        // ... (Di dalam tag <script> Anda)

        function cancelPayment(bookingId) {
            Swal.fire({
                // Menggunakan icon bawaan SweetAlert2 (dengan asumsi CSS defaultnya sudah bagus)
                icon: 'warning',
                title: 'Batalkan Pembayaran?',
                text: 'Peserta pada booking ini akan dihapus dan transaksi dibatalkan.',

                // Kelas kustom untuk tata letak dan ukuran pop-up
                customClass: {
                    // Gunakan class CSS yang sama untuk ukuran pop-up
                    popup: 'custom-warning-popup'
                },

                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Tidak',

                // Warna tombol persis seperti di gambar (Ungu dan Abu-abu)
                confirmButtonColor: '#7568c8', // Ungu
                cancelButtonColor: '#6c757d' // Abu-abu

            }).then(res => {
                if (res.isConfirmed) {
                    const form = new FormData();
                    form.append('cancel_booking', bookingId);
                    fetch('../backend/payment-api.php', {
                            method: 'POST',
                            body: form
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Transaksi telah dibatalkan.',
                                    icon: 'success',
                                    confirmButtonColor: '#a97c50'
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: j.error || 'Gagal membatalkan.',
                                    icon: 'error',
                                    confirmButtonColor: '#a97c50'
                                });
                            }
                        })
                        .catch(err => {
                            Swal.fire({
                                title: 'Error',
                                text: err.message,
                                icon: 'error',
                                confirmButtonColor: '#a97c50'
                            });
                        });
                }
            });
        }

        function closeModal() {
            document.getElementById('modal-payment').classList.remove('active');
        }

        function showDetail(bookingId) {
            Swal.fire({
                title: 'Memuat...',
                html: '<i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:#a97c50;"></i>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`../backend/get-booking-detail.php?id=${bookingId}`)
                .then(r => r.json())
                .then(d => {
                    if (d.error) {
                        Swal.fire({
                            title: 'Kesalahan',
                            text: d.error,
                            icon: 'error',
                            confirmButtonColor: '#a97c50'
                        });
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
                    } else parts += '<p style="color:#999">Tidak ada data peserta</p>';
                    parts += '</div>';

                    const invBtn = isPaid ?
                        `<a href="view-invoice.php?payment_id=${d.id_payment}" class="btn-invoice">
               <i class="fa-solid fa-file-invoice"></i> Lihat Invoice
             </a>` :
                        `<button disabled class="btn-invoice">
               <i class="fa-solid fa-times-circle"></i> Invoice T/A
             </button>`;

                    // Fungsi JS untuk memformat status di modal (menggantikan fungsi PHP di sini)
                    const formatStatusDetailJs = (s) => {
                        let status_text_detail = 'DIBATALKAN';
                        let status_color = '#c62828';
                        let status_icon = '<i class="fa-solid fa-times-circle"></i>';

                        if (s === 'paid' || s === 'settlement') {
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
                        } else if (s === 'cancel' || s === 'failed') {
                            status_text_detail = 'DIBATALKAN';
                            status_color = '#dc3545';
                            status_icon = '<i class="fa-solid fa-ban"></i>';
                        }
                        return `<span style="color:${status_color};font-weight:700;">${status_icon} ${status_text_detail}</span>`;
                    };

                    const fmt = formatStatusDetailJs(d.status_pembayaran);

                    Swal.fire({
                        title: `Transaksi #${d.id_booking}`,
                        html: `<div style="text-align:left">
             <div class="info-group">
               <h4><i class="fa-solid fa-receipt"></i> Ringkasan</h4>
               <div class="info-row"><span>Invoice:</span><strong>${inv}</strong></div>
               <div class="info-row"><span>Pemesanan:</span><strong>#${d.id_booking}</strong></div>
               <div class="info-row"><span>Tanggal Transaksi:</span><strong>${d.tanggal_booking_formatted}</strong></div>
               <div class="info-row"><span>Status:</span>${fmt}</div>
               <div class="info-row"><span>Jumlah Peserta:</span><strong>${d.jumlah_orang} Orang</strong></div>
               <div class="info-row"><span>Total Harga:</span><strong class="price-total">Rp ${parseInt(d.total_harga).toLocaleString('id-ID')}</strong></div>
             </div>
             <div class="info-group">
               <h4><i class="fa-solid fa-mountain"></i> Detail Trip</h4>
               <div class="info-row"><span>Gunung:</span><strong>${d.nama_gunung}</strong></div>
               <div class="info-row"><span>Tipe:</span><strong>${d.jenis_trip||'N/A'}</strong></div>
               <div class="info-row"><span>Tanggal Trip:</span><strong>${d.tanggal_trip_formatted}</strong></div>
               <div class="info-row"><span>Durasi:</span><strong>${d.durasi||'N/A'}</strong></div>
               <div class="info-row"><span>Waktu Kumpul:</span><strong>${d.waktu_kumpul||'N/A'} WIB</strong></div>
               <div class="info-row"><span>Lokasi Kumpul:</span><strong>${d.nama_lokasi||'N/A'}</strong></div>
             </div>
             <div class="info-group">
               <h4><i class="fa-solid fa-users"></i> Peserta (${d.jumlah_orang})</h4>
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
                    Swal.fire({
                        title: 'Kesalahan',
                        text: err.message,
                        icon: 'error',
                        confirmButtonColor: '#a97c50'
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 0) Trigger auto-expire di backend (sudah dipanggil server-side juga)
            fetch('../backend/payment-api.php?expire_stale=1').catch(() => {});

            // 1) Cek status untuk setiap kartu pending saat halaman selesai load
            document.querySelectorAll('.card[data-order-id]').forEach(card => {
                const orderId = card.getAttribute('data-order-id');
                const badge = card.querySelector('.status-badge');
                if (orderId && orderId.trim() !== '' && badge && badge.classList.contains('pending')) {
                    checkStatus(orderId);
                }
            });
            // 2) Polling ringan setiap 12 detik untuk kartu pending
            setInterval(periodicRefresh, 12000);
        });
    </script>
</body>

</html>