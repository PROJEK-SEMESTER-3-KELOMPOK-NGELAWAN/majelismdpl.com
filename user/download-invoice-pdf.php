<?php
// user/download-invoice-pdf.php
// PASTIKAN TIDAK ADA SPASI ATAU BARIS KOSONG DI ATAS INI

require_once '../vendor/autoload.php';
require_once '../backend/koneksi.php';
session_start();

use Mpdf\Mpdf;

$payment_id = intval($_GET['payment_id'] ?? 0);

if ($payment_id <= 0) {
    die('Error: Invalid payment ID');
}

// 1. Query Data Invoice
$stmt = $conn->prepare("
    SELECT 
        p.id_payment, p.order_id, p.jumlah_bayar, p.tanggal, p.metode, p.status_pembayaran,
        b.id_booking, b.tanggal_booking, b.total_harga, b.jumlah_orang,
        t.id_trip, t.nama_gunung, t.jenis_trip, t.tanggal as trip_date, t.durasi, t.harga,
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
    die('Error: Invoice not found or not paid');
}

// 2. Query Participants
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


// 3. Format Data
$invoiceNumber = 'INV-MDPL-' . date('Ymd', strtotime($invoiceData['tanggal'])) . '-' . str_pad($payment_id, 4, '0', STR_PAD_LEFT);
$formatDate = fn($date) => date('d M Y', strtotime($date));
$formatCurrency = fn($amount) => 'Rp ' . number_format($amount, 0, ',', '.');


// 4. Mulai Output Buffering untuk menangkap HTML
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - <?php echo $invoiceNumber; ?></title>
    <style>
        /* --- CSS Dioptimasi untuk mPDF (Menggunakan PT dan HEX Codes) --- */
        
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            line-height: 1.5;
            font-size: 10pt; /* Ukuran standar PDF */
        }
        
        .invoice-wrapper {
            max-width: 100%;
            margin: 0;
            padding: 0; 
            box-shadow: none;
            position: relative;
            z-index: 1;
        }
        
        .paid-stamp-overlay::before {
            content: "PAID";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150pt; 
            color: #2e7d32; /* Warna Success */
            opacity: 0.1;
            font-weight: 800;
            white-space: nowrap;
        }

        /* Header */
        .invoice-header {
            display: table;
            width: 100%;
            border-bottom: 5px solid #a97c50; /* Warna Dark */
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo-box, .invoice-meta {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .logo-box {
            display: flex;
            align-items: center;
        }

        .logo-box div h2 {
            font-size: 20pt;
            margin: 0;
            font-weight: 800;
            color: #a97c50; /* Warna Dark */
        }

        .logo-box div p {
            margin: 0;
            font-size: 8pt;
            color: #777;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h1 {
            color: #333;
            font-size: 28pt; 
            margin: 0;
            font-weight: 700;
        }

        /* Info Grid (Menggunakan display: table untuk mPDF) */
        .info-container {
            display: table;
            width: 100%; 
            border-spacing: 20px 0; 
            margin-bottom: 30px;
        }
        
        .info-container > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-box {
            padding: 15px; 
            background: #fcfcfc;
            border-radius: 5px;
        }
        
        .info-box.pemesan-box { border-left: 5px solid #d6b38c; /* Warna Light */ }
        .info-box.trip-box { border-left: 5px solid #a97c50; /* Warna Dark */ }

        .info-box h4 {
            color: #a97c50; /* Warna Dark */
            font-size: 10pt;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .info-row {
            display: table;
            width: 100%;
            padding: 2px 0;
            font-size: 8pt;
        }
        
        .info-row span, .info-row strong { display: table-cell; }

        .info-row span:first-child { width: 45%; color: #777; }
        .info-row strong { width: 55%; color: #333; }
        
        /* Section Title */
        .section-title-table {
            font-size: 12pt;
            color: #a97c50; /* Warna Dark */
            margin-bottom: 10px;
            font-weight: 700;
            border-bottom: 2px solid #d6b38c; /* Warna Light */
            padding-bottom: 5px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8pt;
        }

        .data-table th {
            background-color: #f4f4f4;
            color: #555;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .data-table td {
            padding: 6px 10px;
            border-bottom: 1px dashed #eee;
        }
        
        /* Gaya sub-tabel peserta */
        .sub-participant-list {
            margin-top: 5px;
            padding: 5px 0 0 0;
            border-top: 1px dashed #ddd;
            font-size: 7pt;
            color: #555;
        }
        
        .sub-participant-list strong {
            display: block;
            margin-bottom: 3px;
            color: #333;
        }

        /* Footer */
        .invoice-footer {
            clear: both;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
            margin-top: 20px;
            text-align: center;
        }

        .invoice-footer p {
            font-size: 7pt;
            color: #555;
            margin: 3px 0;
        }
        
        /* Menghilangkan Font Awesome Ikon */
        .fa-solid { display: none; } 

        @page { margin: 15mm; }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="paid-stamp-overlay"></div>

        <header class="invoice-header">
            <div class="logo-box">
                <div>
                    <h2>MAJELIS MDPL</h2>
                    <p>E-Invoice & Tiket Pendakian</p>
                </div>
            </div>

            <div class="invoice-meta">
                <h1>INVOICE</h1>
                <p>No. Invoice: <strong><?php echo $invoiceNumber; ?></strong></p>
                <p>Status: <strong style="color: #2e7d32;">PAID</strong></p>
                <p style="font-size: 8pt; margin-top: 10px;">
                    Dibayar: <strong><?php echo $formatDate($invoiceData['tanggal']); ?></strong> (Metode: <?php echo $invoiceData['metode']; ?>)
                </p>
            </div>
        </header>
        
        <div class="info-container">
            <div class="info-box pemesan-box">
                <h4>Detail Pemesan</h4>
                <div class="info-row"><span>Nama:</span> <strong><?php echo $invoiceData['username']; ?></strong></div>
                <div class="info-row"><span>Email:</span> <strong><?php echo $invoiceData['email']; ?></strong></div>
                <div class="info-row"><span>Telepon:</span> <strong><?php echo $invoiceData['no_wa']; ?></strong></div>
            </div>

            <div class="info-box trip-box">
                <h4>Detail Trip Pendakian</h4>
                <div class="info-row"><span>Tujuan:</span> <strong style="color: #a97c50;"><?php echo $invoiceData['nama_gunung']; ?></strong></div>
                <div class="info-row"><span>Via/Jenis Trip:</span> <strong><?php echo $invoiceData['jenis_trip']; ?></strong></div>
                <div class="info-row"><span>Tgl/Durasi:</span> <strong><?php echo $formatDate($invoiceData['trip_date']); ?> / <?php echo $invoiceData['durasi']; ?></strong></div>
                <div class="info-row"><span>Basecamp:</span> <strong><?php echo $invoiceData['nama_lokasi']; ?></strong></div>
            </div>
        </div>
        
        <div class="section-title-table">Rincian Biaya</div>
        <table class="data-table cost-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 55%;">Deskripsi</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 15%;">Harga Satuan (Rp)</th>
                    <th style="width: 15%;">Total (Rp)</th>
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

                <tr style="border-top: 2px solid #a97c50; font-weight: 700; background: #fcfcfc;">
                    <td colspan="4" style="text-align: right; padding-top: 15px;">TOTAL DIBAYARKAN</td>
                    <td style="text-align: right; padding-top: 15px; color: #2e7d32; font-size: 11pt;">
                        <?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
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
            <p><strong>DOKUMEN VALID DIBUAT SECARA DIGITAL</strong></p>
            <p>Invoice ini adalah bukti pembayaran sah untuk pemesanan trip Anda. Tidak diperlukan tanda tangan basah.</p>
            <p style="margin-top: 3px; color: #a97c50;"><strong>Terima kasih atas kepercayaan Anda kepada Majelis MDPL.</strong></p>
        </div>
    </div>
</body>

</html>
<?php
$html = ob_get_clean();

// 6. Generate PDF dengan mPDF
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
    ]);

    $mpdf->SetWatermarkText('PAID', 0.08);
    $mpdf->showWatermarkText = true;
    $mpdf->watermarkTextAlpha = 0.08;
    
    $mpdf->SetTitle('Invoice - ' . $invoiceNumber);
    $mpdf->SetAuthor('Majelis MDPL');
    
    $mpdf->WriteHTML($html);

    // 7. Output PDF
    $filename = $invoiceNumber . '.pdf';
    
    // Matikan semua output buffer sebelum mengirim file biner
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    $mpdf->Output($filename, 'D');

} catch (\Mpdf\MpdfException $e) {
    // Jika gagal, tampilkan pesan error yang jelas
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/plain');
    die('Error generating PDF: ' . $e->getMessage() . ' | Harap cek file koneksi.php dan file lain yang di-include untuk spasi di luar tag PHP.');
}