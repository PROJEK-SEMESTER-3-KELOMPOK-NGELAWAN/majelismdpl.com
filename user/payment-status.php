<?php
// user/payment-status.php
session_start();

// Anda sudah memiliki logika untuk $navbarPath dari navbar.php
$navbarPath = '../'; // Contoh path relatif dari user/ ke root
require_once $navbarPath . 'navbar.php'; // Ganti require_once __DIR__ . '/../backend/koneksi.php';

// Cek status login (dipertahankan)
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// =======================================================
// CONTOH DATA TRANSAKSI (SIMULASI DARI JOIN DATABASE)
// Status yang mungkin: pending, settlement (paid), expire, cancelled
// =======================================================
$booking_list = [
    [
        'id_booking' => 28,
        'nama_trip' => 'Gunung Slamet',
        'tanggal_booking' => '2025-10-23',
        'total_harga' => 500000,
        'status_pembayaran' => 'pending', // Menunggu pembayaran
    ],
    [
        'id_booking' => 26,
        'nama_trip' => 'Gunung Raung',
        'tanggal_booking' => '2025-10-21',
        'total_harga' => 400000,
        'status_pembayaran' => 'settlement', // Selesai/Dibayar (Paid)
    ],
    [
        'id_booking' => 13,
        'nama_trip' => 'Gunung Bokong',
        'tanggal_booking' => '2025-10-10',
        'total_harga' => 600000,
        'status_pembayaran' => 'cancelled', // Dibatalkan
    ],
    [
        'id_booking' => 17,
        'nama_trip' => 'Gunung Gulgulan',
        'tanggal_booking' => '2025-10-11',
        'total_harga' => 300000,
        'status_pembayaran' => 'expire', // Kadaluarsa
    ],
];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran Saya | Majelis MDPL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* === BASE & UTILITY === */
        body {
            font-family: "Poppins", Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .payment-page-container {
            /* Jarak dari fixed navbar */
            padding-top: 80px;
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
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .page-title i {
            color: #a97c50;
            margin-right: 10px;
        }

        /* === GRID & CARD STYLE (NEW DESIGN) === */
        .status-list-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
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
            /* Main Info | Status & Action */
            align-items: stretch;
            border: 1px solid #e0e0e0;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        /* ----- Kolom Utama: Detail Trip ----- */
        .card-main-info {
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .trip-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 5px;
        }

        .trip-order-id {
            font-size: 0.9em;
            color: #a97c50;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .detail-group {
            border-top: 1px dashed #eee;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .detail-item {
            flex-basis: 50%;
            /* Bagi dua kolom */
        }

        .detail-label {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .detail-value {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }

        .price-value {
            font-size: 1.4em;
            font-weight: 700;
            color: #a97c50;
            /* Warna aksen */
        }

        /* ----- Kolom Status & Aksi ----- */
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
            /* Memaksimalkan lebar */
            margin-bottom: 15px;
        }

        .status-icon-big {
            font-size: 2.5em;
            margin-bottom: 5px;
        }

        .status-text-small {
            font-size: 0.9em;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Warna Status */
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


        /* Tombol Aksi */
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .btn-action {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9em;
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

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            color: #777;
        }

        /* === Responsive === */
        @media (max-width: 768px) {
            .status-card {
                grid-template-columns: 1fr;
                /* Ubah menjadi tumpukan vertikal */
            }

            .card-status-action {
                border-left: none;
                border-top: 1px solid #f0f0f0;
                flex-direction: row;
                /* Atur status dan aksi menjadi baris */
                gap: 15px;
                padding: 15px;
            }

            .status-badge-container {
                /* Mengambil 1/3 lebar */
                width: 30%;
                margin-bottom: 0;
            }

            .action-group {
                /* Mengambil 2/3 lebar */
                width: 70%;
                flex-direction: row;
                gap: 5px;
            }

            .btn-action {
                flex: 1;
            }

            .detail-group {
                flex-direction: column;
                padding-top: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include $navbarPath . 'navbar.php'; ?>

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

                            // Dapatkan Ikon (Contoh: <i class="fa-solid fa-hourglass-half"></i>)
                            $icon_match = [];
                            preg_match('/<i class="[^"]+"><\/i>/', $status_text_full, $icon_match);
                            $icon_html = $icon_match[0] ?? '<i class="fa-solid fa-question-circle"></i>';

                            $formatted_date = date("d M Y", strtotime($booking['tanggal_booking']));
                            $formatted_price = "Rp " . number_format($booking['total_harga'], 0, ',', '.');
                        ?>
                            <div class="status-card">

                                <div class="card-main-info">
                                    <h3 class="trip-title">
                                        <i class="fa-solid fa-mountain-sun" style="color: #a97c50; margin-right: 5px;"></i>
                                        <?= htmlspecialchars($booking['nama_trip']); ?>
                                    </h3>
                                    <p class="trip-order-id">
                                        Booking ID: #**<?= $booking['id_booking']; ?>**
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
                                            <i class="fa-solid fa-search"></i> Lihat Detail
                                        </button>
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
        </main>
    </div>

    <div id="modal-payment-midtrans" style="display:none;position:fixed;z-index:9999;inset:0;background:rgba(20,15,12,.88);align-items:center;justify-content:center;">
        <div style="background:#fff;padding:36px 20px;max-width:430px;width:97%;border-radius:17px;box-shadow:0 5px 65px #000a;text-align:center;position:relative;">
            <p id="midtrans-status-message">Menyiapkan pembayaran...</p>
            <button onclick="closePaymentModal()" style="margin-top: 15px; background: #eee; border: 1px solid #ccc; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Tutup</button>
        </div>
    </div>


    <script>
        // SIMULASI DATA LENGKAP TRANSAKSI DARI BACKEND (DIpertahankan dari kode sebelumnya)
        const transactionDetails = {
            // ID 28: Pending
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
                basecamp: 'Basecamp slamet',
                include: 'makan, minum, tenda',
                exclude: 'doa',
                sk: 'ga ngalem: wajib membawa surat keterangan sehat dan fotokopi KTP. Pembayaran DP 50% harus dilakukan dalam 1x24 jam.',
                waktu_kumpul: '02:00',
                participants: [{
                    id: 55,
                    name: 'samid5',
                    email: 'dimasdwinugroho15@gmail.com',
                    phone: '6285362783678',
                    dob: '23 Okt 2025',
                    nik: '123456789'
                }]
            },
            // ID 26: Paid/Settlement
            26: {
                booking_id: 26,
                payment_id: 23,
                trip_name: 'Gunung Raung',
                trip_via: 'bondowoso',
                trip_date: '26 Sep 2025',
                total_price: 400000,
                status: 'settlement',
                booked_date: '21 Okt 2025',
                participants_count: 1,
                basecamp: 'Base Camp Kalibaru',
                include: 'mangan, transport pp',
                exclude: 'ngombe, asuransi',
                sk: 'ada deh: peserta dilarang membawa barang yang tidak perlu dan wajib mematuhi protokol basecamp.',
                waktu_kumpul: '11:11',
                participants: [{
                    id: 48,
                    name: 'samidek',
                    email: 'dimasdwinugroho15@gmail.com',
                    phone: '6285362783678',
                    dob: '02 Okt 2025',
                    nik: '123321'
                }]
            },
            // ID 13 dan 17 tidak perlu detail penuh untuk simulasi ini
            13: { status: 'cancelled', payment_id: 13, booking_id: 13, total_price: 600000 },
            17: { status: 'expire', payment_id: 17, booking_id: 17, total_price: 300000 },
        };

        const simulatedBookingData = {
            28: { snap_token: 'dummy-token-28' },
            26: { snap_token: 'dummy-token-26' },
            13: { message: 'Transaksi dibatalkan.' },
            17: { message: 'Transaksi kadaluarsa.' },
        };

        function lanjutkanPembayaran(bookingId) {
            document.getElementById('modal-payment-midtrans').style.display = 'flex';
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
                        cancelButtonText: 'Simulasi Pending'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire('Sukses!', 'Pembayaran Berhasil Disimulasikan. (Reload untuk melihat status PAID)', 'success');
                            // Dalam realita: Kirim AJAX update status, lalu reload page.
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire('Pending', 'Pembayaran masih menunggu konfirmasi.', 'warning');
                        }
                    });
                }, 1000);

            } else {
                setTimeout(() => {
                    closePaymentModal();
                    Swal.fire('Error', data.message || 'Gagal mendapatkan token pembayaran (Simulasi Gagal).', 'error');
                }, 1000);
            }
        }

        function closePaymentModal() {
            document.getElementById('modal-payment-midtrans').style.display = 'none';
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
                Swal.fire('Error', 'Detail transaksi tidak ditemukan. (Data simulasi tidak lengkap)', 'error');
                return;
            }

            // --- Penentuan Nomor Invoice ---
            const invoiceNumber = `INV-MDPL-PAY-${data.payment_id || 'N/A'}`;

            // --- 1. Generate Peserta HTML ---
            let participantsHTML = '<div class="participant-list-detail">';
            // Periksa apakah data.participants ada sebelum iterasi
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

            // Tentukan URL dan Tombol "Lihat Invoice"
            const invoiceUrl = `../user/view-invoice.php?payment_id=${data.payment_id}`;
            const isPaid = data.status.toLowerCase() === 'settlement' || data.status.toLowerCase() === 'paid';

            // *** PERUBAHAN DI SINI: Tombol "Cetak Invoice" diganti "Lihat Invoice" ***
            const invoiceButton = isPaid ?
                `<a href="${invoiceUrl}" target="_blank" class="btn-invoice-detail"><i class="fa-solid fa-file-invoice"></i> Lihat Invoice</a>` :
                `<button disabled class="btn-invoice-detail disabled-btn"><i class="fa-solid fa-times-circle"></i> Invoice Belum Tersedia</button>`;

            // --- 2. Generate Konten Modal HTML --- (Gaya di-injeksi di sini agar konsisten)
            const modalContent = `
            <style>
                .swal2-title { color: #a97c50 !important; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; }
                .info-box-group { margin-bottom: 25px; border: 1px solid #eee; border-radius: 10px; padding: 15px; background: #fcfcfc; }
                .info-box-group h4 { color: #333; margin-bottom: 10px; font-weight: 600; font-size: 1.1em; border-bottom: 1px dashed #ddd; padding-bottom: 5px; display: flex; align-items: center; gap: 8px; }
                .info-box-group h4 i { color: #a97c50; }
                .info-row-detail { display: flex; justify-content: space-between; padding: 5px 0; font-size: 0.95em; }
                .info-row-detail strong { color: #222; }
                .info-row-detail span { color: #555; }
                .total-price-detail { font-size: 1.4em; color: #a97c50; font-weight: 700; margin-top: 10px; }
                .participant-list-detail { margin-top: 10px; max-height: 150px; overflow-y: auto; padding: 0 5px; }
                .participant-item { padding: 8px 0; border-bottom: 1px dotted #e0e0e0; }
                .participant-item p { margin: 0; font-weight: 600; color: #444; }
                .participant-item small { color: #777; font-size: 0.8em; }
                .btn-invoice-detail { margin-top: 15px; background: #333; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border: none; font-weight: 600; }
                .btn-invoice-detail:hover:not(:disabled) { background: #000; }
                .disabled-btn { background: #ccc; color: #777; cursor: not-allowed; }
                .invoice-section { border-top: 1px solid #ddd; padding-top: 20px; text-align: center; }
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
                    <div style="padding: 5px 0;"><span>**Include (Termasuk):**</span> <br><small style="margin-left: 10px; display: block;">${data.include || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span>**Exclude (Tidak Termasuk):**</span> <br><small style="margin-left: 10px; display: block;">${data.exclude || 'N/A'}</small></div>
                    <div style="padding: 5px 0;"><span>**Syarat & Ketentuan:**</span> <br><small style="margin-left: 10px; display: block;">${data.sk || 'N/A'}</small></div>
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

            // --- 3. Tampilkan SweetAlert2 ---
            Swal.fire({
                title: `Detail Transaksi #${data.booking_id}`,
                html: modalContent,
                icon: false,
                width: '700px', // Lebar modal yang lebih besar
                showCloseButton: true,
                showConfirmButton: false,
                focusConfirm: false,
            });
        }
    </script>
</body>

</html>