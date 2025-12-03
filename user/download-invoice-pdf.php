<?php
// user/download-invoice-pdf.php

require_once '../vendor/autoload.php'; // Pastikan path ini benar sesuai struktur folder Anda
require_once '../backend/koneksi.php';
session_start();

use Mpdf\Mpdf;

$payment_id = intval($_GET['payment_id'] ?? 0);

if ($payment_id <= 0) {
    die('Error: Invalid payment ID');
}

// ==========================================
// 1. AMBIL DATA DARI DATABASE
// ==========================================
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
    die('Error: Invoice not found or not paid');
}

// Ambil Data Peserta
$stmtPart = $conn->prepare("SELECT nama, nik FROM participants WHERE id_booking = ?");
$stmtPart->bind_param("i", $invoiceData['id_booking']);
$stmtPart->execute();
$resultPart = $stmtPart->get_result();
$participants = $resultPart->fetch_all(MYSQLI_ASSOC);
$stmtPart->close();

// ==========================================
// 2. FORMAT DATA & LOGO
// ==========================================
$invoiceNumber = 'INV/' . date('Ymd', strtotime($invoiceData['tanggal'])) . '/' . str_pad($payment_id, 5, '0', STR_PAD_LEFT);
$formatDate = fn($date) => date('d F Y', strtotime($date));
$formatCurrency = fn($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

// Persiapan Logo (Convert ke Base64 agar aman di PDF)
$logoPath = '../assets/majelis.png'; // Pastikan path file benar
$logoSrc = '';
if (file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $data = file_get_contents($logoPath);
    $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// ==========================================
// 3. MULAI HTML BUFFFERING
// ==========================================
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo $invoiceNumber; ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #1F2937;
            line-height: 1.5;
        }

        /* Layout Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }

        /* Header Styles */
        .header-container {
            border-bottom: 3px solid #9C7E5C;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #7B5E3A;
            margin: 0;
        }

        .company-address {
            font-size: 9pt;
            color: #6B7280;
        }

        .invoice-title {
            font-size: 26pt;
            font-weight: bold;
            color: #E5E7EB;
            /* Warna abu-abu terang seperti di desain */
            text-align: right;
            line-height: 1;
        }

        .invoice-details {
            text-align: right;
            font-size: 9pt;
            color: #374151;
        }

        .status-paid {
            color: #03543F;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }

        /* Info Boxes */
        .info-box {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 15px;
        }

        .info-title {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6B7280;
            font-weight: bold;
            border-bottom: 1px solid #E5E7EB;
            margin-bottom: 8px;
            padding-bottom: 5px;
        }

        .info-text {
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .info-label {
            color: #6B7280;
            font-size: 9pt;
        }

        /* Main Table */
        .main-table th {
            text-align: left;
            padding: 10px;
            background-color: #f3f4f6;
            color: #6B7280;
            font-size: 9pt;
            text-transform: uppercase;
            border-bottom: 2px solid #E5E7EB;
        }

        .main-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10pt;
        }

        /* Helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .accent-color {
            color: #9C7E5C;
        }

        /* Totals */
        .total-row td {
            padding: 5px 10px;
            font-size: 10pt;
        }

        .grand-total td {
            border-top: 2px solid #9C7E5C;
            padding-top: 10px;
            font-size: 12pt;
            font-weight: bold;
            color: #7B5E3A;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 8pt;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header-container">
        <table width="100%">
            <tr>
                <td width="60%">
                    <table width="100%">
                        <tr>
                            <?php if ($logoSrc): ?>
                                <td width="70" style="padding-right: 15px;">
                                    <img src="<?php echo $logoSrc; ?>" width="60" style="display:block;">
                                </td>
                            <?php endif; ?>
                            <td valign="middle">
                                <div class="company-name">MAJELIS MDPL</div>
                            </td>
                        </tr>
                    </table>
                    <div class="company-address" style="margin-top: 10px;">
                        Jl. Pendaki No. 12, Jawa Timur, Indonesia<br>
                        admin@majelismdpl.com
                    </div>
                </td>

                <td width="40%" align="right">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-details" style="margin-top: 5px;">
                        <strong>#<?php echo $invoiceNumber; ?></strong><br>
                        Terbit: <?php echo $formatDate($invoiceData['tanggal']); ?><br>
                        <span class="status-paid">LUNAS / PAID</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table width="100%" style="margin-bottom: 30px;" cellspacing="0" cellpadding="0">
        <tr>
            <td width="48%" class="info-box">
                <div class="info-title">Ditagihkan Kepada</div>
                <div class="info-text"><strong><?php echo htmlspecialchars($invoiceData['username']); ?></strong></div>
                <div class="info-text"><span class="info-label">Email:</span> <?php echo htmlspecialchars($invoiceData['email']); ?></div>
                <div class="info-text"><span class="info-label">No. WA:</span> <?php echo htmlspecialchars($invoiceData['no_wa']); ?></div>
            </td>
            <td width="4%"></td>
            <td width="48%" class="info-box">
                <div class="info-title">Detail Perjalanan</div>
                <div class="info-text"><strong><?php echo htmlspecialchars($invoiceData['nama_gunung']); ?></strong></div>
                <div class="info-text"><span class="info-label">Via:</span> <?php echo htmlspecialchars($invoiceData['via_gunung']); ?></div>
                <div class="info-text"><span class="info-label">Tanggal:</span> <?php echo $formatDate($invoiceData['trip_date']); ?></div>
                <div class="info-text"><span class="info-label">Durasi:</span> <?php echo htmlspecialchars($invoiceData['durasi']); ?></div>
            </td>
        </tr>
    </table>

    <table class="main-table" width="100%">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="50%">Deskripsi Layanan</th>
                <th width="15%" class="text-center">Qty</th>
                <th width="15%" class="text-right">Harga</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong>Paket Open Trip <?php echo htmlspecialchars($invoiceData['nama_gunung']); ?></strong><br>
                    <span style="font-size: 8pt; color: #6B7280;">Meeting Point: <?php echo htmlspecialchars($invoiceData['nama_lokasi']); ?></span>
                </td>
                <td class="text-center"><?php echo $invoiceData['jumlah_orang']; ?> Pax</td>
                <td class="text-right"><?php echo $formatCurrency($invoiceData['harga']); ?></td>
                <td class="text-right font-bold"><?php echo $formatCurrency($invoiceData['total_harga']); ?></td>
            </tr>
        </tbody>
    </table>

    <table width="100%" style="margin-top: 10px;">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <table width="100%">
                    <tr class="total-row">
                        <td class="text-right" style="color:#6B7280;">Subtotal</td>
                        <td class="text-right"><?php echo $formatCurrency($invoiceData['total_harga']); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td class="text-right" style="color:#6B7280;">Biaya Layanan</td>
                        <td class="text-right">Rp 0</td>
                    </tr>
                    <tr class="total-row grand-total">
                        <td class="text-right">Total Bayar</td>
                        <td class="text-right"><?php echo $formatCurrency($invoiceData['total_harga']); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; border: 1px dashed #D1D5DB; padding: 15px; background: #F9FAFB;">
        <div style="font-size: 9pt; font-weight: bold; color: #6B7280; text-transform: uppercase; margin-bottom: 10px;">
            Daftar Peserta (<?php echo count($participants); ?>)
        </div>
        <table width="100%">
            <tr>
                <?php foreach ($participants as $i => $p): ?>
                    <td width="50%" style="padding-bottom: 5px; font-size: 9pt;">
                        &#10003; <strong><?php echo htmlspecialchars($p['nama']); ?></strong>
                        <span style="color: #9CA3AF;">(<?php echo !empty($p['nik']) ? $p['nik'] : '-'; ?>)</span>
                    </td>
                    <?php if (($i + 1) % 2 == 0): ?>
            </tr>
            <tr><?php endif; ?>
        <?php endforeach; ?>
        <?php if (count($participants) % 2 != 0): ?><td width="50%"></td><?php endif; ?>
            </tr>
        </table>
    </div>

    <div class="footer">
        <strong>Terima Kasih atas Kepercayaan Anda!</strong><br>
        Dokumen ini sah dan diterbitkan secara otomatis oleh sistem Majelis MDPL.<br>
        Simpan dokumen ini sebagai bukti pembayaran yang sah.
    </div>

</body>

</html>
<?php
$html = ob_get_clean();

// ==========================================
// 4. GENERATE PDF (MPDF)
// ==========================================
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);

    // SETTINGS WATERMARK (LUNAS)
    // Ini membuat tulisan LUNAS besar, miring, dan transparan di background
    $mpdf->SetWatermarkText('LUNAS');
    $mpdf->showWatermarkText = true;
    $mpdf->watermarkTextAlpha = 0.1; // Transparansi (0.1 - 1)

    $mpdf->SetTitle("Invoice #" . $invoiceNumber);
    $mpdf->SetAuthor("Majelis MDPL");

    $mpdf->WriteHTML($html);

    // Download PDF
    $mpdf->Output($invoiceNumber . '.pdf', 'D');
} catch (\Mpdf\MpdfException $e) {
    echo "Terjadi kesalahan saat membuat PDF: " . $e->getMessage();
}
?>