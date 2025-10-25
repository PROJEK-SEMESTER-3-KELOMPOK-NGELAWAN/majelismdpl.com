<?php
// user/view-invoice-compact.php
// Invoice Premium Majelis MDPL - Versi Sangat Singkat & Padat

// Ambil ID Pembayaran dari URL
$payment_id = $_GET['payment_id'] ?? 0;

// =======================================================
// SIMULASI PENGAMBILAN DATA INVOICE LENGKAP
// =======================================================
$invoiceData = null;

if ($payment_id == 23) {
    // Data untuk payment_id 23 (Gunung Raung) - Selesai Dibayar
    $invoiceData = [
        'invoice_number' => 'INV-MDPL-20251021-0023',
        'payment_id' => 23,
        'booking_id' => 26,
        'tanggal_issue' => '2025-10-21',
        'tanggal_bayar' => '2025-10-21',
        'metode_bayar' => 'Midtrans (VA Bank BNI)',
        'status_bayar' => 'PAID',
        
        'trip_name' => 'Gunung Raung',
        'trip_via' => 'Bondowoso',
        'trip_date' => '2025-09-26', 
        'duration' => '2 Hari 1 Malam',
        'basecamp' => 'Base Camp Kalibaru, Bondowoso',

        'customer_name' => 'Samid',
        'customer_email' => 'samid@example.com',
        'customer_phone' => '085233463369',

        'rincian_biaya' => [
            ['deskripsi' => 'Paket Trip Pendakian Gunung Raung (2H/1M)', 'unit_price' => 400000, 'quantity' => 1, 'total' => 400000],
        ],
        'subtotal' => 400000,
        'pajak_ppn' => 0,
        'total_amount' => 400000,
        
        'participants' => [
            ['name' => 'Samid', 'dob' => '02 Okt 2025', 'nik' => '123321'],
            ['name' => 'Budi Santoso', 'dob' => '11 Jan 1998', 'nik' => '123322'],
            ['name' => 'Citra Dewi', 'dob' => '20 Feb 2001', 'nik' => '123323'],
        ]
    ];
} elseif ($payment_id == 24) {
    // Data untuk payment_id 24 (Gunung Slamet) - Sudah lunas
    $invoiceData = [
        'invoice_number' => 'INV-MDPL-20251024-0024',
        'payment_id' => 24,
        'booking_id' => 28,
        'tanggal_issue' => '2025-10-24',
        'tanggal_bayar' => '2025-10-24',
        'metode_bayar' => 'Midtrans (Transfer Bank Mandiri)',
        'status_bayar' => 'PAID',
        
        'trip_name' => 'Gunung Slamet',
        'trip_via' => 'Rambipuji',
        'trip_date' => '2025-10-30',
        'duration' => '3 Hari 2 Malam',
        'basecamp' => 'Basecamp Slamet (Rambipuji)',

        'customer_name' => 'Dimas Febrian',
        'customer_email' => 'dimas.f@example.com',
        'customer_phone' => '081234567890',

        'rincian_biaya' => [
            ['deskripsi' => 'Paket Trip Pendakian Gunung Slamet (3H/2M)', 'unit_price' => 500000, 'quantity' => 1, 'total' => 500000],
        ],
        'subtotal' => 500000,
        'pajak_ppn' => 0,
        'total_amount' => 500000,
        
        'participants' => [
            ['name' => 'Dimas Febrian', 'dob' => '15 Sep 1995', 'nik' => '987654321'],
        ]
    ];
}


// Cek ketersediaan data dan status LUNAS/PAID
$isPaid = (isset($invoiceData['status_bayar']) && $invoiceData['status_bayar'] == 'PAID');

if (!$invoiceData || !$isPaid) {
    die("
    <div style='font-family: Poppins, sans-serif; text-align: center; padding-top: 50px;'>
        <h1 style='color: #dc3545;'>Invoice Tidak Tersedia</h1>
        <p>Invoice hanya dapat dilihat untuk transaksi yang berstatus LUNAS/PAID.</p>
        <a href='payment-status.php' style='padding: 10px 20px; background: #a97c50; color: white; border: none; border-radius: 5px; text-decoration: none;'>Kembali ke Status Pembayaran</a>
    </div>
    ");
}

// Detail Perusahaan (untuk Header & Footer)
$companyDetails = [
    'name' => 'Majelis MDPL',
    'address' => 'Jl. Pendaki No. 12, Puncak Sejati',
    'email' => 'admin@majelismdpl.com',
    'logo_path' => '../img/majelis.png'
];

$formatDate = fn($date) => date('d M Y', strtotime($date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoiceData['invoice_number']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        /* CSS DARI KODE SEBELUMNYA HANYA DIAMBIL YANG PENTING DAN DIMODIFIKASI */
        :root {
            --color-mdpl-dark: #a97c50;
            --color-mdpl-light: #d6b38c;
            --color-success: #2e7d32;
            --color-primary-btn: #4a90e2;
        }
        body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; background-color: #f0f0f0; color: #333; line-height: 1.6; }
        .invoice-wrapper {
            max-width: 900px; margin: 30px auto; padding: 40px; background: #fff; box-shadow: 0 0 30px rgba(0, 0, 0, 0.15); border-radius: 12px; position: relative; overflow: hidden; z-index: 1;
        }
        .action-bar { text-align: center; max-width: 900px; margin: 20px auto; display: flex; justify-content: flex-end; gap: 15px; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-primary { background: var(--color-primary-btn); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }

        /* Tanda Air */
        .paid-stamp-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 10; pointer-events: none; }
        .paid-stamp-overlay::before { content: "PAID"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 15em; color: var(--color-success); opacity: 0.1; font-weight: 800; white-space: nowrap; }

        /* Header */
        .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid var(--color-mdpl-dark); padding-bottom: 20px; margin-bottom: 30px; }
        .logo-box { display: flex; align-items: center; }
        .logo-box img { height: 60px; margin-right: 15px; }
        .logo-box div h2 { font-size: 2em; margin: 0; font-weight: 800; color: var(--color-mdpl-dark); }
        .logo-box div p { margin: 0; font-size: 0.8em; color: #777; }
        .invoice-meta { text-align: right; }
        .invoice-meta h1 { color: #333; font-size: 3em; margin: 0; font-weight: 700; }
        .invoice-meta strong { color: var(--color-mdpl-dark); font-weight: 700; }
        
        /* Info Grid */
        .info-container { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-box { padding: 20px; background: #fcfcfc; border-left: 5px solid var(--color-mdpl-light); border-radius: 5px; }
        .info-box h4 { color: var(--color-mdpl-dark); font-size: 1.1em; margin-bottom: 10px; font-weight: 700; }
        .info-row { display: flex; padding: 3px 0; font-size: 0.9em; }
        .info-row span:first-child { width: 45%; color: #777; } /* Ditingkatkan untuk info-box */
        .info-row strong { width: 55%; color: #333; }

        /* Judul Section */
        .section-title-table {
            font-size: 1.4em; color: var(--color-mdpl-dark); margin-bottom: 15px; font-weight: 700; border-bottom: 2px solid var(--color-mdpl-light); padding-bottom: 5px;
        }

        /* Tabel Biaya & Peserta */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th { background-color: #f4f4f4; color: #555; padding: 12px 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        .data-table td { padding: 8px 15px; border-bottom: 1px dashed #eee; font-size: 0.9em; }
        .cost-table td:nth-child(5) { text-align: right; font-weight: 600; }
        
        /* Ringkasan Total (Diusahakan diletakkan di samping tabel biaya) */
        .total-summary {
            float: right;
            width: 380px;
            padding-top: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fcfcfc;
            margin-bottom: 20px; /* Kurangi margin bawah */
        }
        .total-summary .info-row { padding: 8px 20px; border-bottom: 1px dashed #eee; }
        .grand-total-row {
            font-size: 1.4em !important; /* Dikecilkan sedikit */
            font-weight: 800 !important;
            color: var(--color-mdpl-dark) !important;
            padding: 12px 20px !important; /* Dikecilkan sedikit */
            border-top: 2px solid var(--color-mdpl-dark);
        }
        .grand-total-row strong { font-size: 1em !important; }

        /* Footer */
        .invoice-footer { clear: both; border-top: 1px dashed #ddd; padding-top: 20px; margin-top: 40px; text-align: center; } /* Kurangi margin atas */
        .invoice-footer p { font-size: 0.8em; color: #555; }
        
        @media print {
            .action-bar { display: none; }
            .invoice-wrapper { box-shadow: none; margin: 0; border-radius: 0; }
            .paid-stamp-overlay::before { opacity: 0.15; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="payment-status.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <button class="btn btn-primary" onclick="simulateDownload('<?php echo $invoiceData['invoice_number']; ?>')"><i class="fa-solid fa-download"></i> Unduh PDF</button>
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
                <p>No. Invoice: <strong><?php echo $invoiceData['invoice_number']; ?></strong></p>
                <p>Status: <strong style="color: var(--color-success);"><?php echo $invoiceData['status_bayar']; ?></strong></p>
            </div>
        </header>

        <div class="info-container">
            <div class="info-box">
                <h4><i class="fa-solid fa-receipt"></i> Detail Pembayaran</h4>
                <div class="info-row"><span>ID Booking:</span> <strong>#<?php echo $invoiceData['booking_id']; ?></strong></div>
                <div class="info-row"><span>Tgl. Pembayaran:</span> <strong><?php echo $formatDate($invoiceData['tanggal_bayar']); ?></strong></div>
                <div class="info-row"><span>Metode Bayar:</span> <strong><?php echo $invoiceData['metode_bayar']; ?></strong></div>
            </div>
            
            <div class="info-box">
                <h4><i class="fa-solid fa-user"></i> Pemesan</h4>
                <div class="info-row"><span>Nama:</span> <strong><?php echo $invoiceData['customer_name']; ?></strong></div>
                <div class="info-row"><span>Email:</span> <strong><?php echo $invoiceData['customer_email']; ?></strong></div>
                <div class="info-row"><span>Telepon:</span> <strong><?php echo $invoiceData['customer_phone']; ?></strong></div>
            </div>
        </div>

        <div class="section-title-table">Detail Trip Pendakian</div>
        <div class="info-box" style="margin-bottom: 40px; border-left: 5px solid var(--color-mdpl-dark);">
            <div class="info-row" style="margin-bottom: 5px;">
                <span>Tujuan:</span> <strong style="color: var(--color-mdpl-dark);"><?php echo $invoiceData['trip_name']; ?> (Via <?php echo $invoiceData['trip_via']; ?>)</strong>
            </div>
            <div class="info-row">
                <span>Tanggal/Durasi:</span> <strong><?php echo $formatDate($invoiceData['trip_date']); ?> / <?php echo $invoiceData['duration']; ?></strong>
            </div>
            <div class="info-row">
                <span>Basecamp:</span> <strong><?php echo $invoiceData['basecamp']; ?></strong>
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
                <?php $no = 1; ?>
                <?php foreach ($invoiceData['rincian_biaya'] as $item): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($item['deskripsi']); ?></td>
                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['total'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-summary">
            <div class="info-row total-row">
                <span>Subtotal:</span>
                <strong>Rp <?php echo number_format($invoiceData['subtotal'], 0, ',', '.'); ?></strong>
            </div>
            <div class="info-row total-row">
                <span>Pajak/Biaya Admin:</span>
                <strong>Rp <?php echo number_format($invoiceData['pajak_ppn'], 0, ',', '.'); ?></strong>
            </div>
            <div class="info-row total-row grand-total-row">
                <span>TOTAL DIBAYARKAN:</span>
                <strong style="font-size: 1.1em;">Rp <?php echo number_format($invoiceData['total_amount'], 0, ',', '.'); ?></strong>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="section-title-table" style="margin-top: 20px;">Daftar Peserta Trip (<?php echo count($invoiceData['participants']); ?> Orang)</div>
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
                <?php foreach ($invoiceData['participants'] as $p): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo $p['dob']; ?></td>
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

    <script>
        function simulateDownload(invoiceNumber) {
            alert(`Simulasi: Invoice ${invoiceNumber}.pdf sedang dibuat dan akan diunduh sekarang.`);
        }
    </script>
</body>
</html>