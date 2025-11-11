<?php
// user/download-invoice-pdf.php
require_once '../vendor/autoload.php';
require_once '../backend/koneksi.php';
session_start();

use Mpdf\Mpdf;

$payment_id = intval($_GET['payment_id'] ?? 0);

if ($payment_id <= 0) {
    die('Invalid payment ID');
}

// ✅ Query Data (SAMA PERSIS dengan view-invoice.php)
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

if (!$invoiceData) {
    die('Invoice not found or not paid');
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

// ✅ Build HTML (COPY EXACT dari view-invoice.php dengan scaling untuk PDF)
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        /* ✅ CSS SCALED DOWN 60% dari view-invoice.php untuk fit 1 page */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            font-size: 8pt;
            /* Original: 13pt (60% scale) */
            line-height: 1.3;
        }

        /* Header Invoice */
        .invoice-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #a97c50;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .logo-box {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }

        .logo-box h2 {
            font-size: 16pt;
            /* Original: 2em ~ 26pt */
            margin: 0;
            font-weight: 800;
            color: #a97c50;
        }

        .logo-box p {
            margin: 0;
            font-size: 6.5pt;
            /* Original: 0.8em */
            color: #777;
        }

        .invoice-meta {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: middle;
        }

        .invoice-meta h1 {
            color: #333;
            font-size: 20pt;
            /* Original: 3em ~ 39pt */
            margin: 0 0 2px 0;
            font-weight: 700;
        }

        .invoice-meta p {
            font-size: 6.5pt;
            margin: 2px 0;
        }

        .invoice-meta strong {
            color: #a97c50;
            font-weight: 700;
        }

        /* Info Container - Grid 2 Kolom */
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-col:first-child {
            padding-right: 10px;
        }

        .info-box {
            padding: 8px;
            background: #fcfcfc;
            border-left: 3px solid #d6b38c;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .info-box h4 {
            color: #a97c50;
            font-size: 8.5pt;
            /* Original: 1.1em */
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .info-row {
            display: table;
            width: 100%;
            padding: 2px 0;
            font-size: 7pt;
            /* Original: 0.9em */
        }

        .info-row span:first-child {
            display: table-cell;
            width: 45%;
            color: #777;
        }

        .info-row strong {
            display: table-cell;
            width: 55%;
            color: #333;
            font-weight: 600;
        }

        /* Trip Detail Box */
        .trip-detail-box {
            padding: 8px;
            background: #fcfcfc;
            border-left: 5px solid #a97c50;
            border-radius: 3px;
            margin-bottom: 15px;
        }

        /* Section Title */
        .section-title-table {
            font-size: 9pt;
            /* Original: 1.4em */
            color: #a97c50;
            margin-bottom: 6px;
            font-weight: 700;
            border-bottom: 2px solid #d6b38c;
            padding-bottom: 3px;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .data-table th {
            background-color: #f4f4f4;
            color: #555;
            padding: 5px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 6.5pt;
            border-bottom: 2px solid #ddd;
        }

        .data-table td {
            padding: 4px 6px;
            border-bottom: 1px dashed #eee;
            font-size: 6.5pt;
        }

        /* Total Summary */
        .total-summary {
            float: right;
            width: 45%;
            padding-top: 6px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            background-color: #fcfcfc;
            margin-bottom: 10px;
        }

        .total-summary .info-row {
            padding: 4px 10px;
            border-bottom: 1px dashed #eee;
            font-size: 6.5pt;
        }

        .grand-total-row {
            font-size: 9pt !important;
            /* Original: 1.4em */
            font-weight: 800 !important;
            color: #a97c50 !important;
            padding: 6px 10px !important;
            border-top: 2px solid #a97c50;
            border-bottom: none;
        }

        .grand-total-row strong {
            font-size: 9pt !important;
        }

        /* Footer */
        .invoice-footer {
            clear: both;
            border-top: 1px dashed #ddd;
            padding-top: 8px;
            margin-top: 12px;
            text-align: center;
        }

        .invoice-footer p {
            font-size: 6pt;
            color: #555;
            margin: 3px 0;
        }

        .invoice-footer strong {
            font-size: 6.5pt;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="invoice-header">
        <div class="logo-box">
            <h2>MAJELIS MDPL</h2>
            <p>E-Invoice & Tiket Pendakian</p>
        </div>
        <div class="invoice-meta">
            <h1>INVOICE</h1>
            <p>No. Invoice: <strong><?php echo $invoiceNumber; ?></strong></p>
            <p>Status: <strong style="color: #2e7d32;">PAID</strong></p>
        </div>
    </div>

    <!-- INFO CONTAINER (2 KOLOM) -->
    <div class="info-container">
        <div class="info-col">
            <!-- Detail Pembayaran -->
            <div class="info-box">
                <h4>Detail Pembayaran</h4>
                <div class="info-row">
                    <span>ID Booking:</span>
                    <strong>#<?php echo $invoiceData['id_booking']; ?></strong>
                </div>
                <div class="info-row">
                    <span>Tgl. Pembayaran:</span>
                    <strong><?php echo $formatDate($invoiceData['tanggal']); ?></strong>
                </div>
                <div class="info-row">
                    <span>Metode Bayar:</span>
                    <strong><?php echo $invoiceData['metode']; ?></strong>
                </div>
            </div>

            <!-- Pemesan -->
            <div class="info-box">
                <h4>Pemesan</h4>
                <div class="info-row">
                    <span>Nama:</span>
                    <strong><?php echo $invoiceData['username']; ?></strong>
                </div>
                <div class="info-row">
                    <span>Email:</span>
                    <strong><?php echo $invoiceData['email']; ?></strong>
                </div>
                <div class="info-row">
                    <span>Telepon:</span>
                    <strong><?php echo $invoiceData['no_wa']; ?></strong>
                </div>
            </div>
        </div>

        <div class="info-col">
            <!-- Detail Trip -->
            <div class="info-box">
                <h4>Detail Trip Pendakian</h4>
                <div class="info-row">
                    <span>Tujuan:</span>
                    <strong style="color: #a97c50;"><?php echo $invoiceData['nama_gunung']; ?> (Via <?php echo $invoiceData['jenis_trip']; ?>)</strong>
                </div>
                <div class="info-row">
                    <span>Tanggal/Durasi:</span>
                    <strong><?php echo $formatDate($invoiceData['trip_date']); ?> / <?php echo $invoiceData['durasi']; ?></strong>
                </div>
                <div class="info-row">
                    <span>Basecamp:</span>
                    <strong><?php echo $invoiceData['nama_lokasi']; ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- RINCIAN BIAYA -->
    <div class="section-title-table">Rincian Biaya</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 53%;">Deskripsi</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 17%; text-align: right;">Harga Satuan (Rp)</th>
                <th style="width: 17%; text-align: right;">Total (Rp)</th>
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

    <!-- TOTAL SUMMARY -->
    <div class="total-summary">
        <div class="info-row">
            <span>Subtotal:</span>
            <strong>Rp <?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?></strong>
        </div>
        <div class="info-row">
            <span>Pajak/Biaya Admin:</span>
            <strong>Rp 0</strong>
        </div>
        <div class="info-row grand-total-row">
            <span>TOTAL DIBAYARKAN:</span>
            <strong>Rp <?php echo number_format($invoiceData['total_harga'], 0, ',', '.'); ?></strong>
        </div>
    </div>

    <div style="clear: both;"></div>

    <!-- DAFTAR PESERTA -->
    <div class="section-title-table" style="margin-top: 10px;">Daftar Peserta Trip (<?php echo count($participants); ?> Orang)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 38%;">Nama Lengkap</th>
                <th style="width: 27%;">Tanggal Lahir</th>
                <th style="width: 30%;">NIK/ID</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($participants as $p): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($p['nama']); ?></td>
                    <td><?php echo $p['tempat_lahir'] . ', ' . date('d M Y', strtotime($p['tanggal_lahir'])); ?></td>
                    <td><?php echo $p['nik']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="invoice-footer">
        <p><strong>DOKUMEN VALID DIBUAT SECARA DIGITAL</strong></p>
        <p>Invoice ini adalah bukti pembayaran sah untuk pemesanan trip Anda. Tidak diperlukan tanda tangan basah.</p>
        <p style="margin-top: 3px; color: #a97c50;"><strong>Terima kasih atas kepercayaan Anda kepada Majelis MDPL.</strong></p>
    </div>
</body>

</html>
<?php
$html = ob_get_clean();

// ✅ Generate PDF dengan mPDF
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 8,
        'margin_right' => 8,
        'margin_top' => 8,
        'margin_bottom' => 8,
    ]);

    // ✅ SET WATERMARK "PAID" dengan opacity samar (8%)
    $mpdf->SetWatermarkText('PAID', 0.08);
    $mpdf->showWatermarkText = true;
    $mpdf->watermarkTextAlpha = 0.08;

    $mpdf->SetTitle('Invoice - ' . $invoiceNumber);
    $mpdf->SetAuthor('Majelis MDPL');
    $mpdf->WriteHTML($html);

    // ✅ Output PDF sebagai download
    $filename = $invoiceNumber . '.pdf';
    $mpdf->Output($filename, 'D');
} catch (\Mpdf\MpdfException $e) {
    die('Error generating PDF: ' . $e->getMessage());
}
?>