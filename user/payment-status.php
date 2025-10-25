<?php
// user/payment-status.php
session_start();
require_once __DIR__ . '/../backend/koneksi.php';

// Cek status login
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$userName = $_SESSION['username'] ?? 'Pengguna';

// Query yang dikoreksi: Hanya mengambil data yang terjamin ada di skema Anda.
// Kita tidak mengambil midtrans_token di sini.
$sql_status = "
    SELECT 
        b.id_booking,
        pt.nama_gunung AS nama_trip,
        b.tanggal_booking,
        b.total_harga,
        b.status AS status_pembayaran -- Mengambil status langsung dari tabel bookings
    FROM 
        bookings b
    JOIN 
        paket_trips pt ON b.id_trip = pt.id_trip
    WHERE 
        b.id_user = ?
    ORDER BY 
        b.tanggal_booking DESC
";


$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("i", $id_user);
$stmt_status->execute();
$result_status = $stmt_status->get_result();
$booking_list = $result_status->fetch_all(MYSQLI_ASSOC);
$stmt_status->close();

// Fungsi untuk mendapatkan kelas warna status (DISINKRONKAN DARI MY-TRIPS)
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

// Fungsi untuk memformat teks status (DISINKRONKAN DARI MY-TRIPS)
function format_status_text_payment($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'Menunggu Pembayaran';
        case 'paid':
        case 'settlement':
            return 'Pembayaran Diterima';
        case 'expire':
            return 'Kadaluarsa';
        case 'failed':
            return 'Gagal';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return ucwords($status);
    }
}
?>

<!DOCTYPE html>
<html lang="id" class="root-scroll">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran Saya | Majelis MDPL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* ====================================================== */
        /* === CSS KRUSIAL (ANIMASI, SCROLL FIX, TAMPILAN) === */
        /* ====================================================== */
        html.root-scroll {
            overflow-y: scroll;
            height: 100%;
            position: static !important;
            transform: none !important;
        }

        /* 🔑 Kontainer Utama (Sama dengan my-trips.php) */
        .payment-page-container {
            padding: 150px 40px 60px 40px;
            max-width: 1200px;
            margin: 0 auto;
            min-height: 100vh;
        }

        /* 🔑 Header Halaman (Sama dengan my-trips.php) */
        .page-header {
            text-align: center;
            margin-bottom: 60px;
            padding: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .header-icon {
            font-size: 3em;
            color: #a97c50;
            /* Ganti dengan aksen MDPL */
            margin-bottom: 10px;
        }

        .page-header h1 {
            font-size: 2.8em;
            color: #333;
            font-weight: 700;
        }

        /* 🔑 Grid Card */
        .status-list-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        /* 🔑 Card Style */
        .status-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* 🔑 Card Header */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #fcfcfc;
            border-bottom: 1px solid #f0f0f0;
        }

        .trip-title {
            font-size: 1.15em;
            font-weight: 600;
            color: #b49666;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trip-order-id {
            font-size: 0.9em;
            color: #999;
            font-weight: 500;
            background: #f4f4f4;
            padding: 4px 10px;
            border-radius: 8px;
        }

        /* 🔑 Card Body & Detail Rows */
        .card-body {
            padding: 20px;
            flex-grow: 1;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .detail-row:last-of-type {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #a97c50;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .price-value {
            font-size: 1.2em;
            color: #b49666;
        }

        /* 🔑 Card Footer */
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #f9f9f9;
            border-top: 1px solid #f0f0f0;
        }

        .status-badge {
            font-size: 0.9em;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Warna Status - SAMA DENGAN MY-TRIPS */
        .status-pending {
            background-color: #ffe0b2;
            color: #e65100;
        }

        .status-paid {
            background-color: #c8e6c9;
            color: #2e7d32;
        }

        .status-cancelled {
            background-color: #ffcdd2;
            color: #c62828;
        }

        .status-default {
            background-color: #f0f0f0;
            color: #555;
        }

        .btn-action {
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-continue {
            padding: 12px 30px;
            background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(180, 150, 102, 0.4);
        }

        .btn-continue:hover {
            background: #8b5e3c;
        }

        .btn-detail {
            background: #f0f0f0;
            color: #555;
            text-decoration: none;
            border: 1px solid #ddd;
        }

        .btn-detail:hover {
            background: #e0e0e0;
        }

        /* === Empty State === */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #ffffff;
            border: 2px dashed #e0e0e0;
            border-radius: 20px;
            margin-top: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            font-size: 4em;
            color: #cccccc;
            margin-bottom: 20px;
        }


        .empty-state h2 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1.1em;
            color: #777;
            margin-bottom: 30px;
        }

        /* === Responsive === */
        @media (max-width: 900px) {
            .payment-page-container {
                padding: 80px 15px 30px 15px;
            }

            .status-list-grid {
                grid-template-columns: 1fr;
            }

            .card-header,
            .card-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .trip-order-id {
                align-self: flex-end;
            }

            .card-footer {
                align-items: stretch;
            }

            .btn-action,
            .btn-detail {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="payment-page-container">
        <header class="page-header">
            <i class="fa-solid fa-credit-card header-icon"></i>
            <h1>Status Pembayaran</h1>
            <p>Kelola dan lacak status pembayaran untuk semua pemesanan trip Anda.</p>
        </header>

        <section class="status-list-section">
            <?php if (empty($booking_list)) : ?>
                <div class="empty-state">
                    <i class="fa-solid fa-exclamation-circle empty-icon"></i>
                    <h2>Belum Ada Transaksi</h2>
                    <p>Anda belum memiliki riwayat pemesanan yang perlu dilacak.</p>
                    <a href="../#paketTrips" class="btn-continue">
                        <i class="fa-solid fa-compass"></i> Jelajahi Trip
                    </a>
                </div>
            <?php else : ?>
                <div class="status-list-grid">
                    <?php foreach ($booking_list as $booking) :
                        $status = strtolower($booking['status_pembayaran']);
                        $status_class = get_status_class_payment($status);
                        $status_text = format_status_text_payment($status);
                        $formatted_date = date("d M Y", strtotime($booking['tanggal_booking']));
                        $formatted_price = "Rp " . number_format($booking['total_harga'], 0, ',', '.');
                    ?>
                        <div class="status-card <?= $status_class; ?>">

                            <div class="card-header">
                                <span class="trip-title">
                                    <i class="fa-solid fa-mountain"></i>
                                    <?= htmlspecialchars($booking['nama_trip']); ?>
                                </span>
                                <span class="trip-order-id">#ID<?= $booking['id_booking']; ?></span>
                            </div>

                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-calendar-alt"></i> Tgl. Pesan:</span>
                                    <span class="detail-value"><?= $formatted_date; ?></span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-money-bill-wave"></i> Total Harga:</span>
                                    <span class="detail-value price-value"><?= $formatted_price; ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="status-badge <?= $status_class; ?>">
                                    Status: <span class="status-text"><?= $status_text; ?></span>
                                </div>

                                <div class="status-actions" style="flex-direction: row; gap: 8px; margin-left: 0;">

                                    <a class="btn-action btn-detail" href="#" onclick="showDetail(<?= $booking['id_booking']; ?>); return false;">
                                        <i class="fa-solid fa-info-circle"></i> Detail
                                    </a>

                                    <?php if ($status === 'pending') : ?>
                                        <button class="btn-action btn-continue" type="button" onclick="lanjutkanPembayaran(<?= $booking['id_booking']; ?>)">
                                            <i class="fa-solid fa-credit-card"></i> Lanjutkan Bayar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div id="modal-payment-midtrans" style="display:none;position:fixed;z-index:9999;inset:0;background:rgba(20,15,12,.88);align-items:center;justify-content:center;">
        <div style="background:#fff;padding:36px 20px;max-width:430px;width:97%;border-radius:17px;box-shadow:0 5px 65px #000a;text-align:center;position:relative;">
            <p id="midtrans-status-message">Menyiapkan pembayaran...</p>
            <button onclick="closePaymentModal()" style="margin-top: 15px; background: #eee; border: 1px solid #ccc; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Tutup</button>
        </div>
    </div>


    <script>
        // --- Logika Midtrans (Mengambil Token Saat Ini) ---

        function lanjutkanPembayaran(bookingId) {
            document.getElementById('modal-payment-midtrans').style.display = 'flex';
            document.getElementById('midtrans-status-message').textContent = "Meminta token pembayaran...";

            // Panggil API untuk menghasilkan token SNAP baru (atau mengambil yang sudah ada)
            fetch('../backend/payment-api.php?booking=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.snap_token) {
                        document.getElementById('midtrans-status-message').textContent = "Membuka jendela pembayaran...";

                        if (window.snap) {
                            window.snap.pay(data.snap_token, {
                                onSuccess: function(result) {
                                    Swal.fire('Sukses!', 'Pembayaran Berhasil. Status akan diperbarui.', 'success');
                                    setTimeout(() => window.location.reload(), 1500);
                                },
                                onPending: function(result) {
                                    Swal.fire('Pending', 'Pembayaran masih menunggu konfirmasi.', 'warning');
                                },
                                onError: function(result) {
                                    Swal.fire('Gagal', 'Pembayaran gagal diproses.', 'error');
                                    closePaymentModal();
                                },
                                onClose: function() {
                                    document.getElementById('midtrans-status-message').textContent = "Jendela pembayaran ditutup.";
                                }
                            });
                        }
                    } else {
                        Swal.fire('Error', data.message || 'Gagal mendapatkan token pembayaran.', 'error');
                        closePaymentModal();
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Kesalahan jaringan saat meminta token.', 'error');
                    closePaymentModal();
                });
        }

        function closePaymentModal() {
            document.getElementById('modal-payment-midtrans').style.display = 'none';
        }

        function showDetail(bookingId) {
            Swal.fire({
                title: 'Detail Transaksi #' + bookingId,
                html: 'Fungsi ini akan menampilkan detail lengkap transaksi. <br>Anda dapat mengarahkannya ke halaman detail (`user/transaction-detail.php?id=' + bookingId + '`)',
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        }
    </script>
</body>

</html>