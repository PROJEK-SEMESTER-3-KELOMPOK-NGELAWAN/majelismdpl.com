<?php
// my-trips.php
// Anda sudah memiliki logika untuk $navbarPath dari navbar.php
$navbarPath = '../'; // Contoh path relatif dari user/ ke root
require_once $navbarPath . 'navbar.php';

// =======================================================
// CONTOH DATA TRIP SAYA (SIMULASI DARI JOIN DATABASE)
// Status yang mungkin: pending, paid, cancelled, finished
// =======================================================
$myTrips = [
    [
        'id_booking' => 28,
        'nama_gunung' => 'Gunung Slamet',
        'jenis_trip' => 'Camp',
        'tanggal_trip' => '2025-10-30',
        'durasi' => '3 hari 2 malam',
        'via_gunung' => 'Rambipuji',
        'nama_lokasi' => 'Basecamp slamet',
        'tanggal_booking' => '2025-10-23',
        'jumlah_orang' => 1,
        'total_harga' => 500000,
        'status_booking' => 'pending',
        'gambar' => $navbarPath . 'img/ijen.jpg', // Ganti dengan path gambar Anda
        'include' => 'makan, minum, tenda, porter',
        'exclude' => 'doa, snack pribadi',
        'syaratKetentuan' => 'ga ngalem: wajib membawa surat keterangan sehat dan fotokopi KTP. Pembayaran DP 50% harus dilakukan dalam 1x24 jam.',
        'waktu_kumpul' => '02:00:00',
    ],
    [
        'id_booking' => 26,
        'nama_gunung' => 'Gunung Raung',
        'jenis_trip' => 'Camp',
        'tanggal_trip' => '2025-09-26',
        'durasi' => '2 Hari 1 Malam',
        'via_gunung' => 'bondowoso',
        'nama_lokasi' => 'Base Camp Kalibaru',
        'tanggal_booking' => '2025-10-21',
        'jumlah_orang' => 1,
        'total_harga' => 400000,
        'status_booking' => 'paid',
        'gambar' => $navbarPath . 'img/rinjani.jpg', // Ganti dengan path gambar Anda
        'include' => 'mangan, transport pp',
        'exclude' => 'ngombe, asuransi',
        'syaratKetentuan' => 'ada deh: peserta dilarang membawa barang yang tidak perlu dan wajib mematuhi protokol basecamp.',
        'waktu_kumpul' => '11:11:00',
    ],
    [
        'id_booking' => 13,
        'nama_gunung' => 'Gunung Bokong',
        'jenis_trip' => 'Camp',
        'tanggal_trip' => '2025-10-03',
        'durasi' => '2 Hari 1 Malam',
        'via_gunung' => 'Sumberwringin',
        'nama_lokasi' => 'Base Camp Sumberwringin',
        'tanggal_booking' => '2025-10-10',
        'jumlah_orang' => 2,
        'total_harga' => 600000,
        'status_booking' => 'finished',
        'gambar' => $navbarPath . 'img/20250917123322_Magelang.jpg', // Ganti dengan path gambar Anda
        'include' => 'makanan, air mineral',
        'exclude' => 'tidak ada',
        'syaratKetentuan' => 'd: trip telah selesai dan peserta mendapatkan sertifikat digital.',
        'waktu_kumpul' => '23:08:00',
    ],
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Trip Saya - Majelis MDPL</title>
    <style>
        .page-container {
            padding-top: 80px;
            /* Jarak dari fixed navbar */
            min-height: 100vh;
        }

        .my-trips-section {
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

        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .page-title i {
            color: #a97c50;
            /* Warna Emas/Coklat Khas */
            margin-right: 10px;
        }

        /* --- List Card Styling (Eksklusif) --- */
        .trips-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .list-trip-card {
            display: flex;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
            align-items: center;
            /* Tengah vertikal */
        }

        .list-trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .card-image-box {
            width: 250px;
            /* Lebar gambar di kiri */
            min-width: 200px;
            height: 180px;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 10px;
        }

        .trip-type-badge {
            background: rgba(169, 124, 80, 0.9);
            /* Latar belakang coklat gelap transparan */
            color: #fff;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85em;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .card-info {
            flex-grow: 1;
            padding: 15px 25px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8em;
            color: #fff;
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-transform: uppercase;
        }

        /* Warna Status */
        .status-pending {
            background-color: #ffc107;
            color: #333;
        }

        .status-paid {
            background-color: #28a745;
        }

        .status-cancelled {
            background-color: #dc3545;
        }

        .status-finished {
            background-color: #6c757d;
        }

        .card-info h3 {
            font-size: 1.4rem;
            color: #222;
            margin-bottom: 15px;
        }

        .trip-meta {
            font-size: 0.95em;
            color: #555;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .trip-meta i {
            color: #a97c50;
            width: 20px;
            text-align: center;
        }

        .card-actions-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px;
            min-width: 180px;
            border-left: 1px solid #f0f0f0;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.95em;
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

        .btn-detail-list {
            background: #4a4a4a;
            color: #fff;
        }

        .btn-detail-list:hover {
            background: #333;
        }

        .btn-payment-list {
            background: #a97c50;
            color: #fff;
        }

        .btn-payment-list:hover {
            background: #8b5e3c;
        }

        /* --- Modal Styling (Dipertahankan dan Disesuaikan) --- */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            display: flex;
            opacity: 1;
        }

        .modal-container {
            background: #fff;
            border-radius: 15px;
            max-width: 650px;
            width: 90%;
            padding: 30px;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
            /* Pop effect */
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-overlay.show .modal-container {
            transform: scale(1);
        }

        .modal-close-btn {
            position: sticky;
            top: 0;
            right: 0;
            align-self: flex-end;
            background: #f8f8f8;
            border: none;
            font-size: 2rem;
            color: #aaa;
            cursor: pointer;
            z-index: 1;
            padding: 0 10px 10px 10px;
            line-height: 1;
        }

        .modal-close-btn:hover {
            color: #dc3545;
        }

        #modal-title {
            color: #a97c50;
            border-bottom: 2px solid #a97c50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .info-group {
            padding: 15px;
            border: 1px solid #e0e0e0;
            background-color: #fcfcfc;
            border-radius: 10px;
        }

        .info-group h3 {
            font-size: 1.15rem;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .info-group h3 i {
            color: #a97c50;
            margin-right: 8px;
            font-size: 1.3em;
        }

        .info-group p {
            font-size: 0.95em;
            color: #666;
            line-height: 1.6;
            margin-left: 30px;
            white-space: pre-wrap;
            /* Mempertahankan format baris */
        }

        .btn-map {
            margin-top: 15px;
            background: #333;
            color: #fff;
            display: inline-block;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.95em;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-map:hover {
            background: #000;
        }

        /* No Trips State (Dipertahankan) */
        .no-trips {
            text-align: center;
            padding: 60px 20px;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            border-radius: 15px;
            margin-top: 20px;
        }

        .no-trips i {
            font-size: 3em;
            color: #ccc;
            margin-bottom: 15px;
        }

        .explore-btn {
            margin-top: 20px;
            display: inline-block;
            background: #a97c50;
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
        }

        .explore-btn:hover {
            background: #8b5e3c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .list-trip-card {
                flex-direction: column;
                /* Ubah menjadi tumpukan vertikal */
                align-items: stretch;
            }

            .card-image-box {
                width: 100%;
                height: 150px;
                /* Kurangi tinggi untuk mobile */
                min-width: 100%;
            }

            .card-info {
                padding: 15px;
            }

            .card-actions-list {
                flex-direction: row;
                /* Tombol jadi horizontal */
                border-left: none;
                border-top: 1px solid #f0f0f0;
                padding: 15px;
                min-width: auto;
            }

            .btn-detail-list,
            .btn-payment-list {
                flex: 1;
                /* Agar lebar tombol sama */
            }

            .modal-container {
                padding: 20px;
            }

            .info-group p {
                margin-left: 0;
                margin-top: 10px;
            }

            .info-group h3 i {
                display: none;
                /* Sembunyikan ikon di mobile agar judul lebih pendek */
            }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <main class="my-trips-section">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fa-solid fa-mountain-sun"></i> Paket Trip Saya
                </h1>
                <p class="subtitle">Daftar reservasi dan info penting untuk pendakian Anda.</p>
            </div>

            <div class="trips-list">
                <?php if (empty($myTrips)): ?>
                    <div class="no-trips">
                        <i class="fa-solid fa-box-open"></i>
                        <h2>Oops! Belum Ada Trip</h2>
                        <p>Anda belum memesan paket trip apapun. Yuk, jelajahi penawaran kami!</p>
                        <a href="<?php echo $navbarPath; ?>#paketTrips" class="btn explore-btn">Lihat Paket Trip</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($myTrips as $trip): ?>
                        <div class="list-trip-card" data-status="<?php echo strtolower($trip['status_booking']); ?>">
                            <div class="card-image-box" style="background-image: url('<?php echo htmlspecialchars($trip['gambar']); ?>');">
                                <span class="trip-type-badge"><?php echo htmlspecialchars($trip['jenis_trip']); ?></span>
                            </div>
                            <div class="card-info">
                                <span class="status-badge status-<?php echo strtolower($trip['status_booking']); ?>">
                                    <?php
                                    $status = strtolower($trip['status_booking']);
                                    if ($status === 'pending') echo '<i class="fa-solid fa-hourglass-half"></i> Menunggu Pembayaran';
                                    else if ($status === 'paid') echo '<i class="fa-solid fa-check-circle"></i> Pembayaran Selesai';
                                    else if ($status === 'cancelled') echo '<i class="fa-solid fa-times-circle"></i> Dibatalkan';
                                    else if ($status === 'finished') echo '<i class="fa-solid fa-flag-checkered"></i> Selesai';
                                    else echo ucwords($trip['status_booking']);
                                    ?>
                                </span>

                                <h3><?php echo htmlspecialchars($trip['nama_gunung']); ?> (Via <?php echo htmlspecialchars($trip['via_gunung']); ?>)</h3>

                                <div class="trip-meta">
                                    <p><i class="fa-solid fa-calendar-alt"></i> Trip: <strong><?php echo date('d M Y', strtotime($trip['tanggal_trip'])); ?></strong> (<?php echo htmlspecialchars($trip['durasi']); ?>)</p>
                                    <p><i class="fa-solid fa-users"></i> Peserta: <strong><?php echo htmlspecialchars($trip['jumlah_orang']); ?> Orang</strong></p>
                                    <p><i class="fa-solid fa-tag"></i> Total: <strong>Rp <?php echo number_format($trip['total_harga'], 0, ',', '.'); ?></strong></p>
                                </div>
                            </div>
                            <div class="card-actions-list">
                                <button class="btn btn-detail-list" onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($trip)); ?>)" title="Lihat Detail & Perlengkapan">
                                    <i class="fa-solid fa-eye"></i> Detail Trip
                                </button>
                                <?php if (strtolower($trip['status_booking']) == 'pending'): ?>
                                    <a href="<?php echo $navbarPath; ?>user/payment-status.php?booking_id=<?php echo $trip['id_booking']; ?>" class="btn btn-payment-list" title="Lanjutkan Proses Pembayaran">
                                        <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="detail-modal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close-btn" onclick="closeDetailModal()">×</button>
            <h2 id="modal-title"></h2>
            <div class="modal-content">
                <div class="info-group">
                    <h3><i class="fa-solid fa-location-dot"></i> Lokasi & Waktu Kumpul</h3>
                    <p><strong>Tempat Kumpul:</strong> <span id="modal-lokasi"></span></p>
                    <p><strong>Alamat Lengkap:</strong> <span id="modal-alamat"></span></p>
                    <p><strong>Waktu Kumpul:</strong> <span id="modal-waktu-kumpul"></span> WIB</p>
                    <a id="modal-link-map" href="#" target="_blank" class="btn btn-map"><i class="fa-solid fa-map-location-dot"></i> Lihat Peta</a>
                </div>

                <div class="info-group">
                    <h3><i class="fa-solid fa-check-circle"></i> Include (Termasuk)</h3>
                    <p id="modal-include"></p>
                </div>

                <div class="info-group">
                    <h3><i class="fa-solid fa-times-circle"></i> Exclude (Tidak Termasuk)</h3>
                    <p id="modal-exclude"></p>
                </div>

                <div class="info-group last-group">
                    <h3><i class="fa-solid fa-file-contract"></i> Syarat & Ketentuan</h3>
                    <p id="modal-sk"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript Logic (Sama seperti sebelumnya, memastikan fungsi openDetailModal() bekerja)
        function openDetailModal(tripData) {
            const modal = document.getElementById('detail-modal');

            // Perbarui konten modal
            document.getElementById('modal-title').innerText = `Detail Trip: ${tripData.nama_gunung}`;
            document.getElementById('modal-lokasi').innerText = tripData.nama_lokasi;
            document.getElementById('modal-alamat').innerText = tripData.alamat;
            document.getElementById('modal-waktu-kumpul').innerText = tripData.waktu_kumpul.substring(0, 5); // Hanya ambil jam:menit
            document.getElementById('modal-include').innerText = tripData.include || 'Tidak ada data include.';
            document.getElementById('modal-exclude').innerText = tripData.exclude || 'Tidak ada data exclude.';
            document.getElementById('modal-sk').innerText = tripData.syaratKetentuan || 'Tidak ada data syarat & ketentuan.';

            // Perbaiki link map:
            const mapLinkElement = document.getElementById('modal-link-map');
            let mapUrl = tripData.link_map;

            // Logika sederhana untuk membersihkan URL iframe/embed
            if (mapUrl && mapUrl.includes('http://googleusercontent.com/maps.google.com/')) {
                // Menggunakan link dummy yang lebih baik:
                mapUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(tripData.nama_lokasi);
            }

            mapLinkElement.href = mapUrl || '#'; // Set link
            mapLinkElement.style.display = mapUrl ? 'inline-block' : 'none'; // Sembunyikan jika tidak ada link

            // Tampilkan modal
            modal.classList.add('show');
        }

        function closeDetailModal() {
            const modal = document.getElementById('detail-modal');
            modal.classList.remove('show');
        }

        // Tutup modal jika user mengklik di luar area modal
        document.getElementById('detail-modal').addEventListener('click', (e) => {
            if (e.target.id === 'detail-modal') {
                closeDetailModal();
            }
        });
    </script>
</body>

</html>