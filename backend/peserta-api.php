<?php

require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once dirname(__FILE__, 2) . '/config.php';
require_once 'koneksi.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

session_start();

// ========== HELPER FUNCTIONS ==========

/**
 * Response JSON standard
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

/**
 * Log activity ke database
 */
function logActivity($conn, $id_user, $aktivitas, $statusLog)
{
    if (!$id_user) return;
    $stmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Validasi path untuk prevent path traversal
 */
function validateFilePath($filename)
{
    if (
        strpos($filename, '..') !== false ||
        strpos($filename, '/') !== false ||
        strpos($filename, '\\') !== false
    ) {
        return false;
    }
    return true;
}

/**
 * Get KTP upload directory - FIXED PATH uploads/ktp/
 */
function getKtpUploadDir()
{
    $base_path = dirname(__DIR__);
    return $base_path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ktp';
}

/**
 * Delete file foto KTP dari filesystem
 * @param string $foto_ktp_path Path dari database (misal: 'uploads/ktp/filename.jpg' atau 'ktp_1732023400_5678.jpg')
 * @param string $participant_name Nama peserta untuk logging
 * @return array Result dengan 'success' dan 'message'
 */
function deleteKtpFile($foto_ktp_path, $participant_name = 'Unknown')
{
    if (empty($foto_ktp_path)) {
        return ['success' => true, 'message' => 'Tidak ada file yang perlu dihapus'];
    }

    try {
        // Extract filename saja (remove path prefix)
        $filename = basename($foto_ktp_path);

        // Validasi filename
        if (!validateFilePath($filename)) {
            return ['success' => false, 'message' => 'Path file tidak valid'];
        }

        // Get KTP folder path
        $ktp_folder = getKtpUploadDir();
        $full_path = $ktp_folder . DIRECTORY_SEPARATOR . $filename;

        // Verifikasi file ada
        if (!file_exists($full_path)) {
            return ['success' => true, 'message' => 'File tidak ditemukan (sudah terhapus)'];
        }

        // Gunakan realpath untuk absolute path - SECURITY CHECK
        $real_path = realpath($full_path);
        $real_ktp_folder = realpath($ktp_folder);

        if ($real_path === false || $real_ktp_folder === false) {
            return ['success' => false, 'message' => 'Gagal resolve path file'];
        }

        // Pastikan file berada di dalam folder uploads/ktp (security)
        if (strpos($real_path, $real_ktp_folder) !== 0) {
            return ['success' => false, 'message' => 'Path file berada di luar folder yang diizinkan'];
        }

        // HAPUS FILE dengan realpath - SECURITY CHECKED
        if (@unlink($real_path)) {
            return ['success' => true, 'message' => 'File berhasil dihapus'];
        } else {
            $error = error_get_last();
            $error_msg = $error ? $error['message'] : 'Gagal menghapus file';
            return ['success' => false, 'message' => 'Gagal menghapus file: ' . $error_msg];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error menghapus file: ' . $e->getMessage()];
    }
}

// ========== ERROR HANDLERS ==========

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

// ========== MIDTRANS CONFIG ==========

\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// ========== ENDPOINTS ==========

// GET: trips
if (isset($_GET['action']) && $_GET['action'] === 'trips') {
    $rows = [];
    if ($q = $conn->query("SELECT id_trip, nama_gunung FROM paket_trips ORDER BY nama_gunung ASC")) {
        while ($r = $q->fetch_assoc()) {
            $rows[] = $r;
        }
    }
    respond_json(200, ['status' => 200, 'data' => $rows]);
}

// GET: all peserta
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

// GET: detail peserta
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

// POST: update peserta
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

    // Validasi input
    if (empty($nama) || empty($email) || empty($no_wa) || empty($alamat) || empty($tanggal_lahir) || empty($tempat_lahir) || empty($nik)) {
        respond_json(400, ['status' => 400, 'message' => 'Semua field yang wajib harus diisi']);
    }

    $foto_sql = '';
    $new_foto_path = null;

    // AMBIL DATA LAMA TERLEBIH DAHULU
    $stmt_old = $conn->prepare("SELECT foto_ktp FROM participants WHERE id_participant=?");
    if (!$stmt_old) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $old_data = $stmt_old->get_result()->fetch_assoc();
    $stmt_old->close();

    if (!$old_data) {
        respond_json(404, ['status' => 404, 'message' => 'Peserta tidak ditemukan']);
    }

    // HANDLE FOTO KTP UPLOAD
    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['foto_ktp']['name'], PATHINFO_EXTENSION));

        // Validasi extension
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_ext)) {
            respond_json(400, ['status' => 400, 'message' => 'Format file tidak didukung. Gunakan: ' . implode(', ', $allowed_ext)]);
        }

        // Validasi ukuran file (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['foto_ktp']['size'] > $max_size) {
            respond_json(400, ['status' => 400, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
        }

        // Generate safe filename
        $safe_filename = 'ktp_' . time() . '_' . mt_rand(10000, 99999) . '.' . $file_ext;

        $ktp_dir = getKtpUploadDir();

        // Buat folder jika belum ada
        if (!is_dir($ktp_dir)) {
            if (!mkdir($ktp_dir, 0755, true)) {
                respond_json(500, ['status' => 500, 'message' => 'Gagal membuat folder upload']);
            }
        }

        $dest_path = $ktp_dir . DIRECTORY_SEPARATOR . $safe_filename;

        // Upload file
        if (!move_uploaded_file($_FILES['foto_ktp']['tmp_name'], $dest_path)) {
            respond_json(500, ['status' => 500, 'message' => 'Gagal upload foto KTP']);
        }

        // Path untuk disimpan ke database (relative path)
        $new_foto_path = $safe_filename;
        $foto_sql = ", foto_ktp=?";

        // HAPUS FILE LAMA JIKA ADA
        if ($old_data && !empty($old_data['foto_ktp'])) {
            $delete_result = deleteKtpFile($old_data['foto_ktp'], $nama);
        }
    }

    // UPDATE DATABASE
    $sql = "UPDATE participants SET nama=?, email=?, no_wa=?, alamat=?, riwayat_penyakit=?, no_wa_darurat=?, tanggal_lahir=?, tempat_lahir=?, nik=?" . $foto_sql . " WHERE id_participant=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Rollback: hapus file yang baru diupload
        if ($new_foto_path) {
            deleteKtpFile($new_foto_path, $nama);
        }
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }

    if ($foto_sql) {
        $stmt->bind_param("ssssssssssi", $nama, $email, $no_wa, $alamat, $riwayat_penyakit, $no_wa_darurat, $tanggal_lahir, $tempat_lahir, $nik, $new_foto_path, $id);
    } else {
        $stmt->bind_param("sssssssssi", $nama, $email, $no_wa, $alamat, $riwayat_penyakit, $no_wa_darurat, $tanggal_lahir, $tempat_lahir, $nik, $id);
    }

    if ($stmt->execute()) {
        $stmt->close();

        // Log activity
        if (isset($_SESSION['id_user'])) {
            logActivity($conn, $_SESSION['id_user'], "Update peserta (ID: $id, Nama: $nama)", "update");
        }

        respond_json(200, ['status' => 200, 'message' => 'Peserta berhasil diupdate']);
    } else {
        // Rollback: hapus file yang baru diupload jika gagal
        if ($new_foto_path) {
            deleteKtpFile($new_foto_path, $nama);
        }
        $stmt->close();
        respond_json(500, ['status' => 500, 'message' => 'Gagal update peserta: ' . $conn->error]);
    }
}

// DELETE: peserta + file foto - FIXED TO DELETE FILE FROM FOLDER
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        respond_json(400, ['status' => 400, 'message' => 'ID tidak valid']);
    }

    // AMBIL DATA PESERTA TERLEBIH DAHULU
    $stmt_select = $conn->prepare("SELECT id_participant, nama, foto_ktp FROM participants WHERE id_participant=?");
    if (!$stmt_select) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $participant = $stmt_select->get_result()->fetch_assoc();
    $stmt_select->close();

    if (!$participant) {
        respond_json(404, ['status' => 404, 'message' => 'Peserta tidak ditemukan']);
    }

    // HAPUS FILE FOTO DARI FILESYSTEM TERLEBIH DAHULU - IMPORTANT!
    $file_delete_result = null;
    if (!empty($participant['foto_ktp'])) {
        $file_delete_result = deleteKtpFile($participant['foto_ktp'], $participant['nama']);
    }

    // HAPUS DARI DATABASE
    $stmt_delete = $conn->prepare("DELETE FROM participants WHERE id_participant=?");
    if (!$stmt_delete) {
        respond_json(500, ['status' => 500, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        $aff = $stmt_delete->affected_rows;
        $stmt_delete->close();

        // Log activity
        if (isset($_SESSION['id_user'])) {
            $aktivitas = "Menghapus peserta (ID: $id, Nama: " . $participant['nama'] . ")";
            logActivity($conn, $_SESSION['id_user'], $aktivitas, "delete");
        }

        respond_json(200, [
            'status' => 200,
            'message' => 'Peserta dan file foto berhasil dihapus',
            'deleted' => $aff,
            'fileDeleteInfo' => $file_delete_result
        ]);
    } else {
        $stmt_delete->close();
        respond_json(500, ['status' => 500, 'message' => 'Gagal menghapus peserta: ' . $conn->error]);
    }
}

// GET: print_pdf
if (isset($_GET['action']) && $_GET['action'] === 'print_pdf') {
    restore_error_handler();
    restore_exception_handler();
    if (ob_get_length()) {
        ob_clean();
    }
    header_remove();

    date_default_timezone_set('Asia/Jakarta');

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

        // Path format: 'ktp_1732023400_5678.jpg' atau 'uploads/ktp/filename.jpg'
        if (str_starts_with($path, 'uploads/ktp/')) {
            $abs = dirname(__DIR__) . '/' . $path;
            return file_exists($abs) ? $abs : '';
        }

        // Jika hanya filename saja
        $abs = dirname(__DIR__) . '/uploads/ktp/' . $path;
        return file_exists($abs) ? $abs : '';
    };

    $today = date('d-m-Y H:i');
    $title = 'Daftar Peserta - ' . $trip_name;
    $subtitle = $search !== '' ? ' | Pencarian: ' . htmlspecialchars($search) : '';

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10.5px;color:#333}
    h1{font-size:18px;margin:0 0 6px 0}.meta{font-size:11px;color:#666;margin-bottom:12px}
    table{width:100%;border-collapse:collapse;table-layout:fixed}
    th,td{border:1px solid #bbb;padding:6px 6px;vertical-align:top;word-wrap:break-word}
    th{background:#f2ece4;text-align:left;font-weight:bold}
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

    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',
        'orientation' => 'L',
        'displayDefaultOrientation' => true
    ]);
    $mpdf->WriteHTML($html);
    $slug = preg_replace('~[^a-z0-9]+~i', '-', $trip_name);
    $slug = trim($slug, '-');
    if ($slug === '') $slug = 'semua-trip';
    $filename = 'peserta-' . strtolower($slug) . '-' . date('Ymd-His') . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    exit;
}

// GET: status_id
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

// GET: check_status
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

// GET: booking
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
        $stmtPay = $conn->prepare("INSERT INTO payments (id_booking, jumlah_bayar, tanggal, jenis_pembayaran, metode, status_pembayaran, order_id)
                             VALUES (?, ?, CURDATE(), 'trip', 'midtrans', 'pending', ?)");
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

// Webhook Midtrans
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
