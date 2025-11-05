<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

// ✅ Ambil ID Pembayaran dari URL
$payment_id = intval($_GET['payment_id'] ?? 0);

if ($payment_id <= 0) {
    die("
    <div style='font-family: Poppins, sans-serif; text-align: center; padding-top: 50px;'>
        <h1 style='color: #dc3545;'>Invoice Tidak Valid</h1>
        <p>ID Pembayaran tidak ditemukan.</p>
        <a href='payment-status.php' style='padding: 10px 20px; background: #a97c50; color: white; border: none; border-radius: 5px; text-decoration: none;'>Kembali ke Status Pembayaran</a>
    </div>
    ");
}

// ✅ Query Data Invoice dari Database
$stmt = $conn->prepare("
    SELECT 
        p.id_payment,
        p.order_id,
        p.jumlah_bayar,
        p.tanggal,
        p.metode,
        p.status_pembayaran,
        b.id_booking,
        b.tanggal_booking,
        b.total_harga,
        b.jumlah_orang,
        t.id_trip,
        t.nama_gunung,
        t.jenis_trip,
        t.tanggal as trip_date,
        t.durasi,
        t.harga,
        d.nama_lokasi,
        d.alamat,
        u.username,
        u.email,
        u.no_wa
    FROM payments p
    JOIN bookings b ON p.id_booking = b.id_booking
    JOIN paket_trips t ON b.id_trip = t.id_trip
    LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
    JOIN users u ON b.id_user = u.id_user
    WHERE p.id_payment = ? AND p.status_pembayaran IN ('paid', 'settlement')
");

$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$invoiceData = $result->fetch_assoc();
$stmt->close();

// ✅ Cek apakah invoice exist dan sudah dibayar
if (!$invoiceData) {
    die("
    <div style='font-family: Poppins, sans-serif; text-align: center; padding-top: 50px;'>
        <h1 style='color: #dc3545;'>Invoice Tidak Tersedia</h1>
        <p>Invoice hanya dapat dilihat untuk transaksi yang berstatus LUNAS/PAID.</p>
        <a href='payment-status.php' style='padding: 10px 20px; background: #a97c50; color: white; border: none; border-radius: 5px; text-decoration: none;'>Kembali ke Status Pembayaran</a>
    </div>
    ");
}

// ✅ Query Participants
$stmtPart = $conn->prepare("
    SELECT nama, tanggal_lahir, tempat_lahir, nik 
    FROM participants 
    WHERE id_booking = ?
");
$stmtPart->bind_param("i", $invoiceData['id_booking']);
$stmtPart->execute();
$resultPart = $stmtPart->get_result();
$participants = $resultPart->fetch_all(MYSQLI_ASSOC);
$stmtPart->close();

// ✅ Format Data
$invoiceNumber = 'INV-MDPL-' . date('Ymd', strtotime($invoiceData['tanggal'])) . '-' . str_pad($payment_id, 4, '0', STR_PAD_LEFT);
$formatDate = fn($date) => date('d M Y', strtotime($date));
$formatCurrency = fn($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

// Detail Perusahaan
$companyDetails = [
    'name' => 'Majelis MDPL',
    'address' => 'Jl. Pendaki No. 12, Puncak Sejati',
    'email' => 'admin@majelismdpl.com',
    'logo_path' => '../assets/majelis.png'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoiceNumber; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        /* ... COPY SEMUA CSS DARI FILE view-invoice.php YANG ANDA BERIKAN ... */
        :root {
            --color-mdpl-dark: #a97c50;
            --color-mdpl-light: #d6b38c;
            --color-success: #2e7d32;
            --color-primary-btn: #4a90e2;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
            line-height: 1.6;
        }

        .invoice-wrapper {
            max-width: 900px;
            margin: 30px auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .action-bar {
            text-align: center;
            max-width: 900px;
            margin: 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: var(--color-primary-btn);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Tanda Air PAID */
        .paid-stamp-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 10;
            pointer-events: none;
        }

        .paid-stamp-overlay::before {
            content: "PAID";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 15em;
            color: var(--color-success);
            opacity: 0.1;
            font-weight: 800;
            white-space: nowrap;
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 5px solid var(--color-mdpl-dark);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo-box {
            display: flex;
            align-items: center;
        }

        .logo-box img {
            height: 60px;
            margin-right: 15px;
        }

        .logo-box div h2 {
            font-size: 2em;
            margin: 0;
            font-weight: 800;
            color: var(--color-mdpl-dark);
        }

        .logo-box div p {
            margin: 0;
            font-size: 0.8em;
            color: #777;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h1 {
            color: #333;
            font-size: 3em;
            margin: 0;
            font-weight: 700;
        }

        .invoice-meta strong {
            color: var(--color-mdpl-dark);
            font-weight: 700;
        }

        /* Info Grid */
        .info-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-box {
            padding: 20px;
            background: #fcfcfc;
            border-left: 5px solid var(--color-mdpl-light);
            border-radius: 5px;
        }

        .info-box h4 {
            color: var(--color-mdpl-dark);
            font-size: 1.1em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            padding: 3px 0;
            font-size: 0.9em;
        }

        .info-row span:first-child {
            width: 45%;
            color: #777;
        }

        .info-row strong {
            width: 55%;
            color: #333;
        }

        /* Section Title */
        .section-title-table {
            font-size: 1.4em;
            color: var(--color-mdpl-dark);
            margin-bottom: 15px;
            font-weight: 700;
            border-bottom: 2px solid var(--color-mdpl-light);
            padding-bottom: 5px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #f4f4f4;
            color: #555;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .data-table td {
            padding: 8px 15px;
            border-bottom: 1px dashed #eee;
            font-size: 0.9em;
        }

        /* Total Summary */
        .total-summary {
            float: right;
            width: 380px;
            padding-top: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fcfcfc;
            margin-bottom: 20px;
        }

        .total-summary .info-row {
            padding: 8px 20px;
            border-bottom: 1px dashed #eee;
        }

        .grand-total-row {
            font-size: 1.4em !important;
            font-weight: 800 !important;
            color: var(--color-mdpl-dark) !important;
            padding: 12px 20px !important;
            border-top: 2px solid var(--color-mdpl-dark);
        }

        /* Footer */
        .invoice-footer {
            clear: both;
            border-top: 1px dashed #ddd;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
        }

        .invoice-footer p {
            font-size: 0.8em;
            color: #555;
        }

        @media print {
            .action-bar {
                display: none;
            }

            .invoice-wrapper {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>
    <div class="action-bar">
        <a href="payment-status.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <a href="download-invoice-pdf.php?payment_id=<?php echo $payment_id; ?>" class="btn btn-primary"><i class="fa-solid fa-download"></i> Unduh PDF</a>
    </div>

    <div class="invoice-wrapper">
        <div class="paid-stamp-overlay"></div>

        <header class="invoice-header">
            <div class="logo-box">
                <img src="<?php echo $companyDetails['logo_path']; ?>" alt="Logo Majelis MDPL" onerror="this.src='https://via.placeholder.com/60x60?text=Logo'">
                <div>
                    <h2>MAJELIS MDPL</h2>
                    <p>E-Invoice & Tiket Pendakian</p>
                </div>
            </div>

            <div class="invoice-meta">
                <h1>INVOICE</h1>
                <p>No. Invoice: <strong><?php echo $invoiceNumber; ?></strong></p>
                <p>Status: <strong style="color: var(--color-success);">PAID</strong></p>
            </div>
        </header>

        <div class="info-container">
            <div class="info-box">
                <h4><i class="fa-solid fa-receipt"></i> Detail Pembayaran</h4>
                <div class="info-row"><span>ID Booking:</span> <strong>#<?php echo $invoiceData['id_booking']; ?></strong></div>
                <div class="info-row"><span>Tgl. Pembayaran:</span> <strong><?php echo $formatDate($invoiceData['tanggal']); ?></strong></div>
                <div class="info-row"><span>Metode Bayar:</span> <strong><?php echo $invoiceData['metode']; ?></strong></div>
            </div>

            <div class="info-box">
                <h4><i class="fa-solid fa-user"></i> Pemesan</h4>
                <div class="info-row"><span>Nama:</span> <strong><?php echo $invoiceData['username']; ?></strong></div>
                <div class="info-row"><span>Email:</span> <strong><?php echo $invoiceData['email']; ?></strong></div>
                <div class="info-row"><span>Telepon:</span> <strong><?php echo $invoiceData['no_wa']; ?></strong></div>
            </div>
        </div>

        <div class="section-title-table">Detail Trip Pendakian</div>
        <div class="info-box" style="margin-bottom: 40px; border-left: 5px solid var(--color-mdpl-dark);">
            <div class="info-row" style="margin-bottom: 5px;">
                <span>Tujuan:</span> <strong style="color: var(--color-mdpl-dark);"><?php echo $invoiceData['nama_gunung']; ?> (Via <?php echo $invoiceData['jenis_trip']; ?>)</strong>
            </div>
            <div class="info-row">
                <span>Tanggal/Durasi:</span> <strong><?php echo $formatDate($invoiceData['trip_date']); ?> / <?php echo $invoiceData['durasi']; ?></strong>
            </div>
            <div class="info-row">
                <span>Basecamp:</span> <strong><?php echo $invoiceData['nama_lokasi']; ?></strong>
            </div>
        </div>

        <div class="section-title-table">Rincian Biaya</div>
        <table class="data-table cost-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 55%;">Deskripsi</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
                    <th style="width: 15%; text-align: right;">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Paket Trip Pendakian <?php echo $invoiceData['nama_gunung']; ?> (<?php echo $invoiceData['durasi']; ?>)</td>
                    <td style="text-align: center;"><?php echo $invoiceData['jumlah_orang']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($invoiceData['harga'], 0, ',', '.'); ?></td>
                    <td style="text-align: right;"><?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total-summary">
            <div class="info-row total-row">
                <span>Subtotal:</span>
                <strong>Rp <?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?></strong>
            </div>
            <div class="info-row total-row">
                <span>Pajak/Biaya Admin:</span>
                <strong>Rp 0</strong>
            </div>
            <div class="info-row total-row grand-total-row">
                <span>TOTAL DIBAYARKAN:</span>
                <strong style="font-size: 1.1em;">Rp <?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?></strong>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="section-title-table" style="margin-top: 20px;">Daftar Peserta Trip (<?php echo count($participants); ?> Orang)</div>
        <table class="data-table participants-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Nama Lengkap</th>
                    <th style="width: 25%;">Tanggal Lahir</th>
                    <th style="width: 30%;">NIK/ID</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($participants as $p): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($p['nama']); ?></td>
                        <td><?php echo $p['tempat_lahir'] . ', ' . date('d M Y', strtotime($p['tanggal_lahir'])); ?></td>
                        <td><?php echo $p['nik']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="invoice-footer">
            <div style="text-align: center;">
                <p><strong>DOKUMEN VALID DIBUAT SECARA DIGITAL</strong></p>
                <p style="font-size: 0.9em;">Invoice ini adalah bukti pembayaran sah untuk pemesanan trip Anda. Tidak diperlukan tanda tangan basah.</p>
            </div>
            <p style="text-align: center; margin-top: 15px; font-size: 0.8em; color: var(--color-mdpl-dark);">
                Terima kasih atas kepercayaan Anda kepada Majelis MDPL.
            </p>
        </div>
    </div>
</body>

</html>