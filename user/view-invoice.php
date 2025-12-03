<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

// ✅ Ambil ID Pembayaran
$payment_id = intval($_GET['payment_id'] ?? 0);

if ($payment_id <= 0) {
    die("ID Pembayaran tidak valid.");
}

// ✅ Query Data Invoice
$stmt = $conn->prepare("
    SELECT 
        p.id_payment, p.order_id, p.jumlah_bayar, p.tanggal, p.metode, p.status_pembayaran,
        b.id_booking, b.tanggal_booking, b.total_harga, b.jumlah_orang,
        t.id_trip, t.nama_gunung, t.jenis_trip, t.via_gunung, t.tanggal as trip_date, t.durasi, t.harga,
        d.nama_lokasi, d.alamat,
        u.username, u.email, u.no_wa
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

if (!$invoiceData) {
    die("Invoice tidak tersedia atau belum lunas.");
}

// ✅ Query Participants
$stmtPart = $conn->prepare("SELECT nama, tanggal_lahir, nik FROM participants WHERE id_booking = ?");
$stmtPart->bind_param("i", $invoiceData['id_booking']);
$stmtPart->execute();
$resultPart = $stmtPart->get_result();
$participants = $resultPart->fetch_all(MYSQLI_ASSOC);
$stmtPart->close();

// ✅ Format Data
$invoiceNumber = 'INV/' . date('Ymd', strtotime($invoiceData['tanggal'])) . '/' . str_pad($payment_id, 5, '0', STR_PAD_LEFT);
$formatDate = fn($date) => date('d F Y', strtotime($date));
$formatCurrency = fn($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

// ✅ Company Details
$companyDetails = [
    'name' => 'MAJELIS MDPL',
    'address' => 'Jl. Pendaki No. 12, Jawa Timur, Indonesia',
    'email' => 'admin@majelismdpl.com',
    'logo_path' => '../assets/majelis.png'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">

    <title>Invoice #<?php echo $invoiceNumber; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary: #9C7E5C;
            --primary-dark: #7B5E3A;
            --dark: #1F2937;
            --gray: #6B7280;
            --light: #F3F4F6;
            --border: #E5E7EB;
            --success-bg: #DEF7EC;
            --success-text: #03543F;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #FFFFFF;
            margin: 0;
            padding: 40px 0;
            color: var(--dark);
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            -webkit-print-color-adjust: exact;
        }

        /* --- ACTION BAR --- */
        .action-bar {
            width: 794px;
            margin-bottom: 25px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }

        .btn {
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-back {
            background: #f3f4f6;
            color: var(--dark);
            border: 1px solid #d1d5db;
        }

        .btn-back:hover {
            background: #e5e7eb;
        }

        .btn-print {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(156, 126, 92, 0.25);
        }

        .btn-print:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* --- KERTAS INVOICE (A4) --- */
        .invoice-box {
            width: 794px;
            min-height: 1000px;
            background: var(--white);
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            border-top: 8px solid var(--primary);
        }

        /* WATERMARK (DIPERJELAS) */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 10rem;
            font-weight: 900;
            color: var(--dark);
            opacity: 0.15; /* REVISI: Opacity dinaikkan agar lebih jelas */
            font-family: 'Poppins', sans-serif;
            letter-spacing: 10px;
            z-index: 0;
            pointer-events: none;
            border: 5px dashed rgba(0,0,0,0.1); /* Opsional: Border agar lebih tegas */
            padding: 0 20px;
            border-radius: 20px;
        }

        /* HEADER */
        .inv-header {
            padding: 50px 50px 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--border);
            position: relative;
            z-index: 1;
        }

        /* REVISI LAYOUT BRANDING (KIRI) */
        .company-branding {
            display: flex;
            flex-direction: column; /* Stack vertikal: Atas (Logo+Teks), Bawah (Alamat) */
            align-items: flex-start;
            gap: 8px;
        }

        .brand-top-row {
            display: flex;
            align-items: center; /* Sejajar vertikal antara logo dan teks */
            gap: 15px;
        }

        .company-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        .company-name {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem; /* Sedikit diperbesar */
            color: var(--primary-dark);
            font-weight: 800;
            margin: 0;
            line-height: 1;
            letter-spacing: -0.5px;
        }

        .company-addr {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
            max-width: 350px;
            margin-top: 5px; /* Jarak dari logo+judul */
        }

        /* Details Kanan */
        .invoice-details {
            text-align: right;
        }

        .invoice-label {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: #E2E8F0;
            line-height: 0.8;
            letter-spacing: 2px;
            margin: 0 0 10px;
        }

        .invoice-num {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dark);
            background: var(--light);
            padding: 5px 12px;
            border-radius: 6px;
            display: inline-block;
        }

        .invoice-date {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 6px;
        }

        .status-badge {
            display: inline-block;
            background: var(--success-bg);
            color: var(--success-text);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* BODY CONTENT */
        .inv-body {
            padding: 40px 50px;
            position: relative;
            z-index: 1;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 50px;
        }

        .info-block h3 {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--gray);
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            border-bottom: 1px solid var(--border);
            padding-bottom: 6px;
        }

        .info-content div {
            margin-bottom: 4px;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .info-content strong {
            font-weight: 600;
            font-size: 1.05rem;
            display: block;
            margin-bottom: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .info-content .sub {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* TABLE STYLE */
        .table-container {
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            padding: 12px 0;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--gray);
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            font-family: 'Poppins', sans-serif;
        }

        th.th-right,
        td.td-right {
            text-align: right;
        }
        
        th.th-center,
        td.td-center {
            text-align: center;
        }

        tbody td {
            padding: 18px 0;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .item-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
            display: block;
            margin-bottom: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .item-desc {
            font-size: 0.85rem;
            color: var(--gray);
            display: block;
        }

        .price-total {
            font-weight: 700;
            color: var(--dark);
            font-size: 1rem;
        }

        /* TOTAL SUMMARY */
        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 50px;
        }

        .summary-box {
            width: 320px;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .sum-row.final {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--primary);
            font-size: 1.2rem;
            color: var(--primary-dark);
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
        }

        /* PARTICIPANTS LIST */
        .participants-section {
            margin-top: 20px;
        }

        .p-head {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .p-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 30px;
        }

        .p-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--dark);
        }

        .p-item i {
            color: var(--primary);
            font-size: 0.8rem;
        }

        /* FOOTER */
        .inv-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: var(--light);
            padding: 30px 50px;
            text-align: center;
            color: var(--gray);
            font-size: 0.8rem;
            box-sizing: border-box;
        }

        .inv-footer strong {
            color: var(--primary-dark);
            display: block;
            margin-bottom: 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .action-bar {
                display: none;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                margin: 0;
            }

            .inv-header,
            .inv-body,
            .inv-footer {
                padding: 20px 0;
            }

            .watermark {
                opacity: 0.15 !important; /* Pastikan tetap terlihat saat print */
                -webkit-print-color-adjust: exact;
                border: none; /* Hilangkan border putus-putus saat print jika diinginkan */
            }
        }
    </style>
</head>

<body>

    <div class="action-bar">
        <a href="payment-status.php" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <a href="download-invoice-pdf.php?payment_id=<?php echo $payment_id; ?>" class="btn btn-print">
            <i class="fa-solid fa-download"></i> Download PDF
        </a>
    </div>

    <div class="invoice-box">
        <div class="watermark">LUNAS</div>

        <header class="inv-header">
            <div class="company-branding">
                <div class="brand-top-row">
                    <img src="<?php echo $companyDetails['logo_path']; ?>" alt="Logo" class="company-logo" onerror="this.style.display='none'">
                    <h2 class="company-name"><?php echo $companyDetails['name']; ?></h2>
                </div>
                
                <div class="company-addr">
                    <?php echo $companyDetails['address']; ?><br>
                    <?php echo $companyDetails['email']; ?>
                </div>
            </div>

            <div class="invoice-details">
                <h1 class="invoice-label">INVOICE</h1>
                <div class="invoice-num">#<?php echo $invoiceNumber; ?></div>
                <div class="invoice-date">Terbit: <?php echo $formatDate($invoiceData['tanggal']); ?></div>
                <span class="status-badge">LUNAS / PAID</span>
            </div>
        </header>

        <div class="inv-body">

            <div class="info-grid">
                <div class="info-block">
                    <h3>Ditagihkan Kepada</h3>
                    <div class="info-content">
                        <strong><?php echo htmlspecialchars($invoiceData['username']); ?></strong>
                        <div class="sub"><?php echo htmlspecialchars($invoiceData['email']); ?></div>
                        <div class="sub"><?php echo htmlspecialchars($invoiceData['no_wa']); ?></div>
                    </div>
                </div>
                <div class="info-block">
                    <h3>Detail Perjalanan</h3>
                    <div class="info-content">
                        <strong><?php echo htmlspecialchars($invoiceData['nama_gunung']); ?></strong>
                        <div class="sub">Via: <?php echo htmlspecialchars($invoiceData['via_gunung']); ?></div>
                        <div class="sub">Tgl: <?php echo $formatDate($invoiceData['trip_date']); ?></div>
                        <div class="sub">Durasi: <?php echo htmlspecialchars($invoiceData['durasi']); ?></div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="50%">Deskripsi Layanan</th>
                            <th width="10%" class="th-center">Qty</th>
                            <th width="15%" class="th-right">Harga</th>
                            <th width="20%" class="th-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <span class="item-name">Paket Open Trip <?php echo htmlspecialchars($invoiceData['nama_gunung']); ?></span>
                                <span class="item-desc">Meeting Point: <?php echo htmlspecialchars($invoiceData['nama_lokasi']); ?></span>
                            </td>
                            <td class="td-center"><?php echo $invoiceData['jumlah_orang']; ?> Pax</td>
                            <td class="td-right"><?php echo $formatCurrency($invoiceData['harga']); ?></td>
                            <td class="td-right price-total"><?php echo $formatCurrency($invoiceData['total_harga']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary-container">
                <div class="summary-box">
                    <div class="sum-row">
                        <span>Subtotal</span>
                        <span><?php echo $formatCurrency($invoiceData['total_harga']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span>Biaya Layanan</span>
                        <span>Rp 0</span>
                    </div>
                    <div class="sum-row final">
                        <span>Total Bayar</span>
                        <span><?php echo $formatCurrency($invoiceData['total_harga']); ?></span>
                    </div>
                </div>
            </div>

            <div class="participants-section">
                <div class="p-head">Daftar Peserta (<?php echo count($participants); ?>)</div>
                <div class="p-list">
                    <?php foreach ($participants as $index => $p): ?>
                        <div class="p-item">
                            <i class="fa-solid fa-circle-check"></i>
                            <?php echo ($index + 1) . '. ' . htmlspecialchars($p['nama']); ?>
                            <?php if (!empty($p['nik'])): ?>
                                <span style="font-size:0.8rem; color:#9ca3af;">(<?php echo htmlspecialchars($p['nik']); ?>)</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <footer class="inv-footer">
            <strong>Terima Kasih atas Kepercayaan Anda!</strong>
            Dokumen ini sah dan diterbitkan secara otomatis oleh sistem Majelis MDPL.<br>
            Simpan dokumen ini sebagai bukti pembayaran yang sah.
        </footer>
    </div>

</body>

</html>