<?php
// user/my-trips.php

// WAJIB: Sesuaikan dengan lokasi file koneksi dan navbar Anda
session_start();
require_once __DIR__ . '/../backend/koneksi.php'; // Sesuaikan path

// Cek status login
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    // Redirect ke halaman login jika belum login
    header("Location: ../index.php"); // Atau ke halaman login spesifik
    exit;
}

$id_user = $_SESSION['id_user'];
$userName = $_SESSION['username'] ?? 'Pengguna';

// Query untuk mengambil data pesanan trip user
// KOREKSI: Mengganti 'pemesanan' menjadi 'bookings' dan 'trip' menjadi 'paket_trips'
$sql_trips = "
    SELECT 
        b.id_booking,
        pt.nama_gunung AS nama_trip, -- Menggunakan nama_gunung sebagai nama trip
        pt.harga AS harga_trip_satuan, -- Harga satuan dari paket_trips
        b.tanggal_booking,
        b.jumlah_orang,
        b.total_harga,
        b.status AS status_pembayaran -- KOREKSI: Mengambil kolom 'status' dari tabel 'bookings'
    FROM 
        bookings b
    JOIN 
        paket_trips pt ON b.id_trip = pt.id_trip
    WHERE 
        b.id_user = ?
    ORDER BY 
        b.tanggal_booking DESC
";

$stmt_trips = $conn->prepare($sql_trips);

// Cek jika prepare gagal (kemungkinan ada masalah di query lain, tapi seharusnya fix sekarang)
if ($stmt_trips === false) {
    // Tambahkan error handling jika diperlukan
    // die('MySQL Prepare Error: ' . $conn->error);
}

$stmt_trips->bind_param("i", $id_user);
$stmt_trips->execute();
$result_trips = $stmt_trips->get_result();
$pemesanan_list = $result_trips->fetch_all(MYSQLI_ASSOC);
$stmt_trips->close();

// Fungsi untuk mendapatkan warna status
function get_status_color($status) {
    // KOREKSI: Menyesuaikan ENUM status dari tabel 'bookings' ('pending', 'paid', dll.)
    switch (strtolower($status)) {
        case 'pending':
            return 'status-pending'; // Kuning/Jingga
        case 'paid':
            return 'status-paid'; // Hijau
        case 'cancelled':
            return 'status-cancelled'; // Merah
        default:
            return 'status-default';
    }
}
// Fungsi untuk memformat status
function format_status_text($status) {
    if (strtolower($status) === 'pending') {
        return 'Menunggu Pembayaran';
    } elseif (strtolower($status) === 'paid') {
        return 'Terbayar';
    } elseif (strtolower($status) === 'cancelled') {
        return 'Dibatalkan';
    }
    return ucwords($status);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Trip Saya | Majelis MDPL</title>
    <style>
        .mytrips-page-container {
            padding: 100px 40px 40px 40px;
            max-width: 1200px;
            margin: 0 auto;
            min-height: 100vh;
        }

        /* === Header Halaman === */
        .page-header {
            text-align: center;
            margin-bottom: 50px;
            padding: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .header-icon {
            font-size: 3em;
            color: #b49666;
            margin-bottom: 10px;
            animation: pulse 1.5s infinite alternate;
        }

        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: 0.8;
            }

            to {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        .page-header h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-header p {
            color: #666;
            font-size: 1.1em;
            max-width: 700px;
            margin: 0 auto;
        }

        /* === Card List Trips === */
        .trip-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .trip-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #fcfcfc;
            border-bottom: 1px solid #f0f0f0;
        }

        .trip-name {
            font-size: 1.15em;
            font-weight: 600;
            color: #b49666;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .order-id {
            font-size: 0.9em;
            color: #999;
            font-weight: 500;
            background: #f4f4f4;
            padding: 4px 10px;
            border-radius: 8px;
        }

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

        .price-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }

        .price-value {
            font-size: 1.2em;
            color: #b49666;
        }

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

        /* Warna Status - Disesuaikan dengan status ENUM 'bookings' */
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

        .btn-detail {
            padding: 8px 15px;
            background: #a97c50;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.95em;
            font-weight: 600;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-detail:hover {
            background: #8b5e3c;
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

        .btn-explore {
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

        .btn-explore:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(180, 150, 102, 0.6);
        }


        /* === Responsive Design === */
        @media (max-width: 900px) {
            .mytrips-page-container {
                padding: 80px 15px 30px 15px;
            }

            .page-header h1 {
                font-size: 2em;
            }

            .page-header p {
                font-size: 1em;
            }

            .trip-cards-grid {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .order-id {
                align-self: flex-end;
            }

            .card-footer {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .btn-detail {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <?php
    // Memuat navbar
    include '../navbar.php'; // Sesuaikan path
    ?>

    <div class="mytrips-page-container">
        <header class="page-header">
            <i class="fa-solid fa-mountain header-icon"></i>
            <h1>Paket Trip Saya</h1>
            <p>Lihat semua riwayat pemesanan trip Anda, termasuk status pembayaran dan detail perjalanan.</p>
        </header>

        <section class="trip-list-section">
            <?php if (empty($pemesanan_list)) : ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open empty-icon"></i>
                    <h2>Belum Ada Pemesanan Trip</h2>
                    <p>Yuk, rencanakan petualangan mendaki Anda bersama kami!</p>
                    <a href="<?php echo $navbarPath; ?>#paketTrips" class="btn-explore">
                        <i class="fa-solid fa-compass"></i> Jelajahi Paket Trip
                    </a>
                </div>
            <?php else : ?>
                <div class="trip-cards-grid">
                    <?php foreach ($pemesanan_list as $pemesanan) :
                        $status_class = get_status_color($pemesanan['status_pembayaran']);
                        $status_text = format_status_text($pemesanan['status_pembayaran']); // Menggunakan fungsi format
                        $formatted_date = date("d M Y", strtotime($pemesanan['tanggal_booking'])); // Menggunakan tanggal_booking
                        $formatted_price = "Rp " . number_format($pemesanan['total_harga'], 0, ',', '.');
                    ?>
                        <div class="trip-card">
                            <div class="card-header">
                                <span class="trip-name">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    <?php echo htmlspecialchars($pemesanan['nama_trip']); ?>
                                </span>
                                <span class="order-id">#ID<?php echo $pemesanan['id_booking']; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-calendar-alt"></i> Tgl. Pesan:</span>
                                    <span class="detail-value"><?php echo $formatted_date; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-user-group"></i> Peserta:</span>
                                    <span class="detail-value"><?php echo $pemesanan['jumlah_orang']; ?> Orang</span>
                                </div>
                                <div class="detail-row price-row">
                                    <span class="detail-label"><i class="fa-solid fa-money-bill-wave"></i> Total Harga:</span>
                                    <span class="detail-value price-value"><?php echo $formatted_price; ?></span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="status-badge <?php echo $status_class; ?>">
                                    Status:
                                    <span class="status-text">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                                <a href="<?php echo $navbarPath; ?>user/payment-status.php?order_id=<?php echo $pemesanan['id_booking']; ?>" class="btn-detail">
                                    <i class="fa-solid fa-circle-info"></i> Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika untuk menyorot link "Paket Trip Saya" di dropdown
            const tripLink = document.querySelector('.user-dropdown a[href*="my-trips.php"]');

            if (tripLink) {
                // Hapus class 'active' dari semua link utama (jika ada)
                document.querySelectorAll('.navbar-menu a').forEach(link => {
                    link.classList.remove('active');
                });

                // Hapus class 'active' dari semua dropdown item lain (optional)
                document.querySelectorAll('.user-dropdown a').forEach(link => {
                    link.classList.remove('active');
                });

                // Tambahkan class 'active' ke link "Paket Trip Saya"
                tripLink.classList.add('active');
            }

            // ... (Kode JS navbar.php lainnya) ...
        });
    </script>
</body>

</html>