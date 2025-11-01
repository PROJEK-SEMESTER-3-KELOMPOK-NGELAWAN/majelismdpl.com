<?php
// backend/peserta-api.php

// Autoload & koneksi
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';

// Matikan display error; semua error akan diproses sebagai JSON (kecuali saat output PDF)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

session_start();

/**
 * Kirim response JSON yang bersih & valid:
 * - Bersihkan output buffer
 * - Hapus header sebelumnya
 * - Set Content-Type JSON + HTTP code
 */
function respond_json(int $http, array $payload)
{
    if (ob_get_length()) {
        ob_clean();
    }
    header_remove();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($http);
    echo json_encode($payload);
    exit;
}

// Error & exception handler → JSON (akan dinonaktifkan saat stream PDF)
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    respond_json(500, [
        'status' => 500,
        'error' => 'PHP Error',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
});
set_exception_handler(function ($e) {
    respond_json(500, [
        'status' => 500,
        'error' => 'Exception',
        'message' => $e->getMessage()
    ]);
});

// Midtrans config
\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// =========================
// Endpoint: trips (JSON)
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'trips') {
    $rows = [];
    if ($q = $conn->query("SELECT id_trip, nama_gunung FROM paket_trips ORDER BY nama_gunung ASC")) {
        while ($r = $q->fetch_assoc()) {
            $rows[] = $r;
        }
    }
    respond_json(200, ['status' => 200, 'data' => $rows]);
}

// =========================
// Endpoint: all (JSON)
// Daftar peserta dengan filter id_trip & search
// JOIN sesuai skema: p.id_booking -> b.id_booking -> t.id_trip
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'all') {
    $id_trip = isset($_GET['id_trip']) && $_GET['id_trip'] !== '' ? intval($_GET['id_trip']) : null;
    $search  = isset($_GET['search']) ? trim($_GET['search']) : '';

    $sql = "SELECT 
          p.id_participant, p.nama, p.email, p.no_wa, p.alamat, p.riwayat_penyakit,
          p.no_wa_darurat, p.tanggal_lahir, p.tempat_lahir, p.nik, p.foto_ktp,
          p.id_booking, t.nama_gunung
        FROM participants p
        LEFT JOIN bookings b ON p.id_booking = b.id_booking
        LEFT JOIN paket_trips t ON b.id_trip = t.id_trip
        WHERE 1=1";
    $types = '';
    $params = [];
    if ($id_trip) {
        $sql .= " AND b.id_trip=?";
        $types .= 'i';
        $params[] = $id_trip;
    }
    if ($search !== '') {
        $like = '%' . $search . '%';
        $sql .= " AND (p.nama LIKE ? OR p.email LIKE ? OR p.no_wa LIKE ? OR p.nik LIKE ? OR CAST(p.id_booking AS CHAR) LIKE ? OR p.alamat LIKE ? OR p.tempat_lahir LIKE ?)";
        $types .= 'sssssss';
        array_push($params, $like, $like, $like, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY p.nama ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();

    respond_json(200, ['status' => 200, 'data' => $rows]);
}

// =========================
// Endpoint: detail (JSON)
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'detail') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        respond_json(400, ['status' => 400, 'message' => 'ID tidak valid']);
    }
    $stmt = $conn->prepare("SELECT * FROM participants WHERE id_participant=?");
    if (!$stmt) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$data) {
        respond_json(404, ['status' => 404, 'message' => 'Peserta tidak ditemukan']);
    }
    respond_json(200, ['status' => 200, 'data' => $data]);
}

// =========================
// Endpoint: update (JSON)
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'update') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        respond_json(400, ['status' => 400, 'message' => 'ID tidak valid']);
    }

    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_wa = $_POST['no_wa'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $riwayat_penyakit = $_POST['riwayat_penyakit'] ?? '';
    $no_wa_darurat = $_POST['no_wa_darurat'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $nik = $_POST['nik'] ?? '';

    $foto_sql = '';
    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_ktp']['name'], PATHINFO_EXTENSION));
        $safe = 'ktp_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $dir = dirname(__DIR__) . '/uploads/ktp';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $dest = $dir . '/' . $safe;
        if (!move_uploaded_file($_FILES['foto_ktp']['tmp_name'], $dest)) {
            respond_json(500, ['status' => 500, 'message' => 'Gagal upload foto']);
        }
        $rel = 'uploads/ktp/' . $safe;
        $foto_sql = ", foto_ktp=?";
    }

    $sql = "UPDATE participants SET nama=?,email=?,no_wa=?,alamat=?,riwayat_penyakit=?,no_wa_darurat=?,tanggal_lahir=?,tempat_lahir=?,nik=?" . $foto_sql . " WHERE id_participant=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }

    if ($foto_sql) {
        $stmt->bind_param("ssssssssssi", $nama, $email, $no_wa, $alamat, $riwayat_penyakit, $no_wa_darurat, $tanggal_lahir, $tempat_lahir, $nik, $rel, $id);
    } else {
        $stmt->bind_param("sssssssssi", $nama, $email, $no_wa, $alamat, $riwayat_penyakit, $no_wa_darurat, $tanggal_lahir, $tempat_lahir, $nik, $id);
    }
    $stmt->execute();
    $stmt->close();
    respond_json(200, ['status' => 200, 'message' => 'Updated']);
}

// =========================
// Endpoint: delete (JSON)
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        respond_json(400, ['status' => 400, 'message' => 'ID tidak valid']);
    }
    $stmt = $conn->prepare("DELETE FROM participants WHERE id_participant=?");
    if (!$stmt) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $aff = $stmt->affected_rows;
    $stmt->close();
    respond_json(200, ['status' => 200, 'message' => 'Deleted', 'deleted' => $aff]);
}

// =========================
// Endpoint: print_pdf (mPDF, INLINE)
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'print_pdf') {
    // Putus handler JSON & header sebelum output PDF
    restore_error_handler();
    restore_exception_handler();
    if (ob_get_length()) {
        ob_clean();
    }
    header_remove();

    // Set timezone ke WIB
    date_default_timezone_set('Asia/Jakarta'); // WIB [web:106][web:110]

    $id_trip = isset($_GET['id_trip']) && $_GET['id_trip'] !== '' ? intval($_GET['id_trip']) : null;
    $search  = isset($_GET['search']) ? trim($_GET['search']) : '';

    $trip_name = 'Semua Trip';
    if ($id_trip) {
        $q = $conn->prepare("SELECT nama_gunung FROM paket_trips WHERE id_trip=?");
        if ($q) {
            $q->bind_param("i", $id_trip);
            $q->execute();
            $q->bind_result($nama);
            if ($q->fetch() && $nama) $trip_name = $nama;
            $q->close();
        }
    }

    $sql = "SELECT 
          p.id_participant, p.nama, p.email, p.no_wa, p.alamat, p.riwayat_penyakit,
          p.no_wa_darurat, p.tanggal_lahir, p.tempat_lahir, p.nik, p.foto_ktp,
          p.id_booking, t.nama_gunung
        FROM participants p
        LEFT JOIN bookings b ON p.id_booking = b.id_booking
        LEFT JOIN paket_trips t ON b.id_trip = t.id_trip
        WHERE 1=1";
    $types = '';
    $params = [];
    if ($id_trip) {
        $sql .= " AND b.id_trip=?";
        $types .= 'i';
        $params[] = $id_trip;
    }
    if ($search !== '') {
        $like = '%' . $search . '%';
        $sql .= " AND (p.nama LIKE ? OR p.email LIKE ? OR p.no_wa LIKE ? OR p.nik LIKE ? OR CAST(p.id_booking AS CHAR) LIKE ? OR p.alamat LIKE ? OR p.tempat_lahir LIKE ?)";
        $types .= 'sssssss';
        array_push($params, $like, $like, $like, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY p.nama ASC";

    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();

    $resolveKtp = function ($path) {
        if (!$path) return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        if (str_starts_with($path, 'uploads/')) {
            $abs = dirname(__DIR__) . '/' . $path;
            return file_exists($abs) ? $abs : '';
        }
        if (str_starts_with($path, '../')) {
            $abs = realpath(dirname(__FILE__) . '/' . $path);
            return ($abs && file_exists($abs)) ? $abs : '';
        }
        $abs = dirname(__DIR__) . '/uploads/ktp/' . $path;
        return file_exists($abs) ? $abs : '';
    };

    $today = date('d-m-Y H:i'); // WIB [web:106]

    $title = 'Daftar Peserta - ' . $trip_name;
    $subtitle = $search !== '' ? ' | Pencarian: ' . htmlspecialchars($search) : '';

    // Style untuk landscape: kolom sedikit diperlebar
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10.5px;color:#333}
    h1{font-size:18px;margin:0 0 6px 0}.meta{font-size:11px;color:#666;margin-bottom:12px}
    table{width:100%;border-collapse:collapse;table-layout:fixed}
    th,td{border:1px solid #bbb;padding:6px 6px;vertical-align:top;word-wrap:break-word}
    th{background:#f2ece4;text-align:left}
    .center{text-align:center}.nowrap{white-space:nowrap}.small{font-size:10px;color:#555}
    .ktp{width:100px;height:auto;object-fit:cover;border:1px solid #ccc;border-radius:4px}
    .w-id{width:45px}.w-nama{width:130px}.w-email{width:180px}.w-wa{width:100px}
    .w-alamat{width:220px}.w-rwp{width:200px}.w-wa2{width:120px}
    .w-tgl{width:105px}.w-tmp{width:130px}.w-nik{width:140px}
    .w-foto{width:130px}.w-book{width:110px}.w-trip{width:150px}
  </style></head><body>';

    $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
    $html .= '<div class="meta">Dicetak : ' . htmlspecialchars($today) . $subtitle . '</div>';

    $html .= '<table><thead><tr>
    <th class="w-id">ID</th>
    <th class="w-nama">Nama</th>
    <th class="w-email">Email</th>
    <th class="w-wa">No WA</th>
    <th class="w-alamat">Alamat</th>
    <th class="w-rwp">Riwayat Penyakit</th>
    <th class="w-wa2">No WA Darurat</th>
    <th class="w-tgl">Tgl Lahir</th>
    <th class="w-tmp">Tmp Lahir</th>
    <th class="w-nik">NIK</th>
    <th class="w-foto">Foto KTP</th>
    <th class="w-book">ID Booking</th>
    <th class="w-trip">Trip</th>
  </tr></thead><tbody>';

    if (!count($rows)) {
        $html .= '<tr><td colspan="13" class="center small">Tidak ada peserta pada filter ini</td></tr>';
    } else {
        foreach ($rows as $r) {
            $ktpAbs = $resolveKtp($r['foto_ktp'] ?? '');
            $ktpImg = $ktpAbs ? '<img src="' . htmlspecialchars($ktpAbs) . '" class="ktp" />' : '<span class="small">Tidak ada</span>';
            $html .= '<tr>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['id_participant'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['nama'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['email'] ?? '')) . '</td>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['no_wa'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['alamat'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['riwayat_penyakit'] ?? '')) . '</td>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['no_wa_darurat'] ?? '')) . '</td>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['tanggal_lahir'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['tempat_lahir'] ?? '')) . '</td>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['nik'] ?? '')) . '</td>' .
                '<td>' . $ktpImg . '</td>' .
                '<td class="nowrap">' . htmlspecialchars((string)($r['id_booking'] ?? '')) . '</td>' .
                '<td>' . htmlspecialchars((string)($r['nama_gunung'] ?? '')) . '</td>' .
                '</tr>';
        }
    }

    $html .= '</tbody></table>';
    $html .= '<div class="small" style="margin-top:8px;">Jumlah peserta: ' . count($rows) . '</div>';
    $html .= '</body></html>';

    // Inisialisasi mPDF Landscape
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',           // A4 Landscape [web:99][web:101]
        'orientation' => 'L',
        'displayDefaultOrientation' => true // bantu viewer mengikuti orientasi default [web:102]
    ]);
    $mpdf->WriteHTML($html);
    $slug = preg_replace('~[^a-z0-9]+~i', '-', $trip_name);
    $slug = trim($slug, '-');
    if ($slug === '') $slug = 'semua-trip';
    $filename = 'peserta-' . strtolower($slug) . '-' . date('Ymd-His') . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    exit;
}


// =========================
// Endpoint: status_id (JSON)
// =========================
if (isset($_GET['status_id'])) {
    $id_booking = intval($_GET['status_id']);
    $status = 'unknown';
    if ($id_booking > 0) {
        $stmt = $conn->prepare("SELECT status_pembayaran FROM payments WHERE id_booking=?");
        if ($stmt) {
            $stmt->bind_param("i", $id_booking);
            $stmt->execute();
            $stmt->bind_result($status);
            $stmt->fetch();
            $stmt->close();
        }
    }
    respond_json(200, ['status' => $status ?: 'no_payment']);
}

// =========================
if (isset($_GET['check_status'])) {
    $order_id = trim($_GET['check_status']);
    if ($order_id === '') {
        respond_json(400, ['status' => 400, 'error' => 'Order ID required']);
    }
    try {
        $status = \Midtrans\Transaction::status($order_id);
        $transaction_status = $status->transaction_status ?? 'pending';
        $fraud_status = $status->fraud_status ?? 'accept';
        $status_pembayaran =
            (($transaction_status === 'capture' && $fraud_status === 'accept') || $transaction_status === 'settlement') ? 'paid' : ($transaction_status === 'pending' ? 'pending' : (in_array($transaction_status, ['deny', 'expire', 'cancel']) ? 'failed' : 'pending'));

        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
        if (!$stmt) {
            $conn->rollback();
            respond_json(500, ['status' => 500, 'error' => 'Database error: ' . $conn->error]);
        }
        $stmt->bind_param("ss", $status_pembayaran, $order_id);
        $stmt->execute();
        $stmt->close();

        if ($status_pembayaran === 'paid') {
            $b = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
            if ($b) {
                $b->bind_param("s", $order_id);
                $b->execute();
                $b->close();
            }
        }
        $conn->commit();

        respond_json(200, ['success' => true, 'status' => $status_pembayaran, 'transaction_status' => $transaction_status]);
    } catch (Exception $e) {
        if ($conn && $conn->errno) $conn->rollback();
        respond_json(500, ['status' => 500, 'error' => 'Failed to check status', 'message' => $e->getMessage()]);
    }
}

// =========================
// Endpoint: booking (JSON) → Snap token
// =========================
if (isset($_GET['booking'])) {
    $id_booking = intval($_GET['booking']);
    if ($id_booking <= 0) {
        respond_json(400, ['status' => 400, 'error' => 'ID booking tidak valid']);
    }

    $stmt = $conn->prepare("SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
                        FROM bookings b
                        JOIN paket_trips t ON b.id_trip = t.id_trip
                        JOIN users u ON b.id_user = u.id_user
                        WHERE b.id_booking=?");
    if (!$stmt) {
        respond_json(500, ['status' => 500, 'error' => 'Database prepare error: ' . $conn->error]);
    }
    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$booking) {
        respond_json(404, ['status' => 404, 'error' => 'Booking tidak ditemukan']);
    }

    $order_id = 'ORDER-' . $id_booking . '-' . time();
    $params = [
        'transaction_details' => ['order_id' => $order_id, 'gross_amount' => intval($booking['total_harga'])],
        'customer_details' => ['first_name' => $booking['username'], 'email' => $booking['email']],
        'item_details' => [[
            'id' => $booking['id_trip'],
            'price' => intval($booking['total_harga']),
            'quantity' => 1,
            'name' => $booking['nama_gunung']
        ]]
    ];
    $snapToken = \Midtrans\Snap::getSnapToken($params);

    $cek = $conn->prepare("SELECT id_payment FROM payments WHERE id_booking=?");
    if (!$cek) {
        respond_json(500, ['status' => 500, 'error' => 'Database error: ' . $conn->error]);
    }
    $cek->bind_param('i', $id_booking);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        $stmtPay = $conn->prepare("INSERT INTO payments (id_booking,jumlah_bayar,tanggal,jenis_pembayaran,metode,status_pembayaran,order_id)
                             VALUES (?, ?, CURDATE(),'trip','midtrans','pending',?)");
        if (!$stmtPay) {
            respond_json(500, ['status' => 500, 'error' => 'Insert payment error: ' . $conn->error]);
        }
        $gross = intval($booking['total_harga']);
        $stmtPay->bind_param("iis", $id_booking, $gross, $order_id);
        $stmtPay->execute();
        $stmtPay->close();
    } else {
        $upd = $conn->prepare("UPDATE payments SET order_id=?, status_pembayaran='pending' WHERE id_booking=?");
        if ($upd) {
            $upd->bind_param("si", $order_id, $id_booking);
            $upd->execute();
            $upd->close();
        }
    }
    $cek->close();

    respond_json(200, ['status' => 200, 'success' => true, 'snap_token' => $snapToken, 'order_id' => $order_id]);
}

// =========================
// Webhook Midtrans (JSON)
// =========================
$json = file_get_contents('php://input');
if (empty($json)) {
    respond_json(200, ['ok' => true]);
}
$notification = json_decode($json);
if (!$notification) {
    respond_json(400, ['status' => 400, 'error' => 'Invalid notification']);
}

$validSignature = hash('sha512', ($notification->order_id ?? '') . ($notification->status_code ?? '') . ($notification->gross_amount ?? '') . \Midtrans\Config::$serverKey);
if (($notification->signature_key ?? '') !== $validSignature) {
    respond_json(403, ['status' => 403, 'error' => 'Invalid signature']);
}

$order_id = $notification->order_id;
$transaction_status = $notification->transaction_status ?? 'pending';
$fraud_status = $notification->fraud_status ?? 'accept';
$status_pembayaran =
    (($transaction_status === 'capture' && $fraud_status === 'accept') || $transaction_status === 'settlement') ? 'paid' : ($transaction_status === 'pending' ? 'pending' : (in_array($transaction_status, ['deny', 'expire', 'cancel']) ? 'failed' : 'pending'));

$conn->begin_transaction();
$stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
if ($stmt) {
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
} else {
    $conn->rollback();
    respond_json(500, ['status' => 500, 'error' => 'Database error: ' . $conn->error]);
}
if ($status_pembayaran === 'paid') {
    $b = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
    if ($b) {
        $b->bind_param("s", $order_id);
        $b->execute();
        $b->close();
    }
}
$conn->commit();

respond_json(200, ['status' => 200, 'success' => true, 'message' => 'Notification processed', 'payment_status' => $status_pembayaran, 'affected_rows' => $affected ?? 0]);
