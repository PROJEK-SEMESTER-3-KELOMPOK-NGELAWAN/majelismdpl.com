<?php
// ✅ JANGAN ada space/newline sebelum tag ini
ob_start();

require_once "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

ob_clean();

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $errstr,
        'debug' => [
            'file' => basename($errfile),
            'line' => $errline
        ]
    ]);
    exit;
});

set_exception_handler(function ($exception) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $exception->getMessage()
    ]);
    exit;
});

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $id_user = $_SESSION['id_user'] ?? null;
    if (!$id_user) {
        throw new Exception('Login diperlukan');
    }

    $id_trip = intval($_POST['id_trip'] ?? 0);
    $jumlah_peserta = intval($_POST['jumlah_peserta'] ?? 1);

    if ($id_trip <= 0) {
        throw new Exception('ID Trip tidak valid');
    }

    if ($jumlah_peserta <= 0 || $jumlah_peserta > 10) {
        throw new Exception('Jumlah peserta tidak valid (max 10 orang)');
    }

    $required_fields = ['nama', 'email', 'tanggal_lahir', 'tempat_lahir', 'nik', 'alamat', 'no_wa', 'no_wa_darurat', 'riwayat_penyakit'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || !is_array($_POST[$field])) {
            throw new Exception("Data peserta ($field) tidak valid");
        }
        if (count($_POST[$field]) < $jumlah_peserta) {
            throw new Exception("Data peserta ($field) tidak lengkap");
        }
    }

    function uploadKTP($file, $idx)
    {
        if (!isset($file['name'][$idx]) || $file['error'][$idx] !== UPLOAD_ERR_OK) {
            return '';
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name'][$idx]);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return '';
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'][$idx] > $maxSize) {
            return '';
        }

        $ext = strtolower(pathinfo($file['name'][$idx], PATHINFO_EXTENSION));
        // Buat nama file yang aman
        $fileName = 'ktp_' . time() . '_' . rand(100000, 999999) . '.' . $ext;

        $upDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ktp' . DIRECTORY_SEPARATOR;

        // Buat folder jika belum ada
        if (!is_dir($upDir)) {
            if (!mkdir($upDir, 0755, true)) {
                error_log("Failed to create directory: $upDir");
                return '';
            }
        }

        $target = $upDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'][$idx], $target)) {
            error_log("Failed to move uploaded file to: $target");
            return '';
        }

        // Return hanya filename (akan diakses dari path uploads/ktp/)
        return $fileName;
    }

    $stmt = $conn->prepare("SELECT slot, harga, status FROM paket_trips WHERE id_trip = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param("i", $id_trip);
    $stmt->execute();
    $result = $stmt->get_result();
    $trip = $result->fetch_assoc();
    $stmt->close();

    if (!$trip) {
        throw new Exception('Trip tidak ditemukan');
    }

    if ($trip['status'] != 'available') {
        throw new Exception('Trip tidak tersedia untuk booking');
    }

    if (intval($trip['slot']) < $jumlah_peserta) {
        throw new Exception('Slot tidak cukup. Tersedia: ' . $trip['slot'] . ' slot');
    }

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $nik = $_POST['nik'];
    $alamat = $_POST['alamat'];
    $no_wa = $_POST['no_wa'];
    $no_wa_darurat = $_POST['no_wa_darurat'];
    $riwayat_penyakit = $_POST['riwayat_penyakit'];
    $foto_ktp_files = $_FILES['foto_ktp'] ?? null;
    $participant_ids = [];

    $conn->begin_transaction();

    $stmtParticipant = $conn->prepare("
        INSERT INTO participants 
        (nama, email, tanggal_lahir, tempat_lahir, nik, alamat, no_wa, no_wa_darurat, riwayat_penyakit, foto_ktp) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtParticipant) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    for ($i = 0; $i < $jumlah_peserta; $i++) {
        // Validasi data sebelum insert
        if (empty($nama[$i])) {
            $conn->rollback();
            throw new Exception("Nama peserta " . ($i + 1) . " tidak boleh kosong");
        }

        if (!filter_var($email[$i], FILTER_VALIDATE_EMAIL)) {
            $conn->rollback();
            throw new Exception("Email peserta " . ($i + 1) . " tidak valid");
        }

        if (!empty($nik[$i]) && strlen(preg_replace('/\D/', '', $nik[$i])) != 16) {
            $conn->rollback();
            throw new Exception("NIK peserta " . ($i + 1) . " harus 16 digit");
        }

        // Upload KTP
        $ktpPath = '';
        if ($foto_ktp_files && isset($foto_ktp_files['tmp_name'][$i]) && $foto_ktp_files['error'][$i] === UPLOAD_ERR_OK) {
            $ktpPath = uploadKTP($foto_ktp_files, $i);
            if (empty($ktpPath)) {
                error_log("Warning: KTP upload failed for participant " . ($i + 1));
                // Lanjut tanpa KTP (opsional - bisa juga throw exception)
            }
        }

        $stmtParticipant->bind_param(
            "ssssssssss",
            $nama[$i],
            $email[$i],
            $tanggal_lahir[$i],
            $tempat_lahir[$i],
            $nik[$i],
            $alamat[$i],
            $no_wa[$i],
            $no_wa_darurat[$i],
            $riwayat_penyakit[$i],
            $ktpPath
        );

        if (!$stmtParticipant->execute()) {
            $conn->rollback();
            throw new Exception('Gagal menyimpan peserta ' . ($i + 1) . ': ' . $stmtParticipant->error);
        }

        $participant_ids[] = $conn->insert_id;
    }
    $stmtParticipant->close();

    $total_harga = $trip['harga'] * $jumlah_peserta;
    $tanggal_booking = date('Y-m-d');
    $status_booking = "pending";

    $stmtBooking = $conn->prepare("
        INSERT INTO bookings 
        (id_user, id_trip, jumlah_orang, total_harga, tanggal_booking, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtBooking) {
        $conn->rollback();
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmtBooking->bind_param(
        "iiisss",
        $id_user,
        $id_trip,
        $jumlah_peserta,
        $total_harga,
        $tanggal_booking,
        $status_booking
    );

    if (!$stmtBooking->execute()) {
        $conn->rollback();
        throw new Exception('Gagal membuat booking: ' . $stmtBooking->error);
    }

    $id_booking = $conn->insert_id;
    $stmtBooking->close();

    if (!empty($participant_ids)) {
        $stmtUpdateParticipant = $conn->prepare("UPDATE participants SET id_booking = ? WHERE id_participant = ?");
        if (!$stmtUpdateParticipant) {
            $conn->rollback();
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        foreach ($participant_ids as $pid) {
            $stmtUpdateParticipant->bind_param("ii", $id_booking, $pid);
            $stmtUpdateParticipant->execute();
        }
        $stmtUpdateParticipant->close();
    }

    $conn->commit();

    ob_clean();

    echo json_encode([
        'success' => true,
        'id_booking' => $id_booking,
        'total_harga' => $total_harga,
        'message' => 'Pendaftaran berhasil! Silakan lanjut ke pembayaran.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    ob_clean();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} finally {
    if (isset($conn)) {
        $conn->close();
    }

    ob_end_flush();
}
