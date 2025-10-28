<?php
require_once "koneksi.php";
session_start();
header('Content-Type: application/json');

// ✅ Nonaktifkan error HTML agar hanya return JSON
ini_set('display_errors', 0);
error_reporting(0);

// ✅ Error handler custom - tangkap semua error sebagai JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $errstr]);
    exit;
});

try {
    // ✅ Validasi login
    $id_user = $_SESSION['id_user'] ?? null;
    if (!$id_user) {
        echo json_encode(['success' => false, 'message' => 'Login diperlukan']);
        exit;
    }

    // ✅ Validasi input dasar
    $id_trip = intval($_POST['id_trip'] ?? 0);
    $jumlah_peserta = intval($_POST['jumlah_peserta'] ?? 1);

    if ($id_trip <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Trip tidak valid']);
        exit;
    }

    if ($jumlah_peserta <= 0) {
        echo json_encode(['success' => false, 'message' => 'Jumlah peserta tidak valid']);
        exit;
    }

    // ✅ Validasi field peserta
    $required_fields = ['nama', 'email', 'tanggal_lahir', 'tempat_lahir', 'nik', 'alamat', 'no_wa', 'no_wa_darurat', 'riwayat_penyakit'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || !is_array($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Data peserta ($field) tidak valid"]);
            exit;
        }
        if (count($_POST[$field]) < $jumlah_peserta) {
            echo json_encode(['success' => false, 'message' => "Data peserta ($field) tidak lengkap"]);
            exit;
        }
    }

    // ✅ Function untuk upload KTP dengan validasi ketat
    function uploadKTP($file, $idx)
    {
        if (!isset($file['name'][$idx]) || $file['error'][$idx] !== UPLOAD_ERR_OK) {
            return '';
        }

        // Validasi tipe file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name'][$idx]);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return '';
        }

        // Validasi ukuran file (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'][$idx] > $maxSize) {
            return '';
        }

        // Generate nama file unik
        $ext = strtolower(pathinfo($file['name'][$idx], PATHINFO_EXTENSION));
        $fileName = 'ktp_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upDir = dirname(__DIR__) . '/uploads/ktp/';
        
        // Buat folder jika belum ada
        if (!is_dir($upDir)) {
            mkdir($upDir, 0755, true);
        }

        $target = $upDir . $fileName;
        if (move_uploaded_file($file['tmp_name'][$idx], $target)) {
            return 'uploads/ktp/' . $fileName;
        }

        return '';
    }

    // ✅ Cek ketersediaan trip dengan prepared statement
    $stmt = $conn->prepare("SELECT slot, harga, status FROM paket_trips WHERE id_trip = ?");
    $stmt->bind_param("i", $id_trip);
    $stmt->execute();
    $result = $stmt->get_result();
    $trip = $result->fetch_assoc();
    $stmt->close();

    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip tidak ditemukan']);
        exit;
    }

    if ($trip['status'] != 'available') {
        echo json_encode(['success' => false, 'message' => 'Trip tidak tersedia untuk booking']);
        exit;
    }

    if (intval($trip['slot']) < $jumlah_peserta) {
        echo json_encode(['success' => false, 'message' => 'Slot tidak cukup. Tersedia: ' . $trip['slot'] . ' slot']);
        exit;
    }

    // ✅ Ambil data peserta dari POST
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

    // ✅ Mulai transaksi database
    $conn->begin_transaction();

    // ✅ Simpan data peserta
    $stmtParticipant = $conn->prepare("
        INSERT INTO participants 
        (nama, email, tanggal_lahir, tempat_lahir, nik, alamat, no_wa, no_wa_darurat, riwayat_penyakit, foto_ktp) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < $jumlah_peserta; $i++) {
        // Upload KTP
        $ktpPath = '';
        if ($foto_ktp_files && isset($foto_ktp_files['tmp_name'][$i])) {
            $ktpPath = uploadKTP($foto_ktp_files, $i);
        }

        // Validasi NIK (harus 16 digit)
        if (!empty($nik[$i]) && strlen($nik[$i]) != 16) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => "NIK peserta " . ($i+1) . " harus 16 digit"]);
            exit;
        }

        // Validasi email format
        if (!filter_var($email[$i], FILTER_VALIDATE_EMAIL)) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => "Email peserta " . ($i+1) . " tidak valid"]);
            exit;
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
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan peserta: ' . $conn->error]);
            exit;
        }

        $participant_ids[] = $conn->insert_id;
    }
    $stmtParticipant->close();

    // ✅ Hitung total harga
    $total_harga = $trip['harga'] * $jumlah_peserta;
    $tanggal_booking = date('Y-m-d');
    $status_booking = "pending";

    // ✅ Insert booking
    $stmtBooking = $conn->prepare("
        INSERT INTO bookings 
        (id_user, id_trip, jumlah_orang, total_harga, tanggal_booking, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
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
        echo json_encode(['success' => false, 'message' => 'Gagal membuat booking: ' . $conn->error]);
        exit;
    }

    $id_booking = $conn->insert_id;
    $stmtBooking->close();

    // ✅ Update relasi participant dengan booking
    if (!empty($participant_ids)) {
        $stmtUpdateParticipant = $conn->prepare("UPDATE participants SET id_booking = ? WHERE id_participant = ?");
        foreach ($participant_ids as $pid) {
            $stmtUpdateParticipant->bind_param("ii", $id_booking, $pid);
            $stmtUpdateParticipant->execute();
        }
        $stmtUpdateParticipant->close();
    }

    // ✅ Update slot trip
    $stmtUpdateSlot = $conn->prepare("
        UPDATE paket_trips 
        SET slot = slot - ? 
        WHERE id_trip = ? AND slot >= ?
    ");
    $stmtUpdateSlot->bind_param("iii", $jumlah_peserta, $id_trip, $jumlah_peserta);
    
    if (!$stmtUpdateSlot->execute()) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Gagal update slot trip']);
        exit;
    }
    $stmtUpdateSlot->close();

    // ✅ Cek sisa slot dan update status jika habis
    $stmtCheckSlot = $conn->prepare("SELECT slot FROM paket_trips WHERE id_trip = ?");
    $stmtCheckSlot->bind_param("i", $id_trip);
    $stmtCheckSlot->execute();
    $resultSlot = $stmtCheckSlot->get_result();
    $sisaSlot = $resultSlot->fetch_assoc()['slot'];
    $stmtCheckSlot->close();

    if ($sisaSlot <= 0) {
        $stmtUpdateStatus = $conn->prepare("UPDATE paket_trips SET status = 'sold' WHERE id_trip = ?");
        $stmtUpdateStatus->bind_param("i", $id_trip);
        $stmtUpdateStatus->execute();
        $stmtUpdateStatus->close();
    }

    // ✅ Commit transaksi
    $conn->commit();

    // ✅ Return success
    echo json_encode([
        'success' => true,
        'id_booking' => $id_booking,
        'total_harga' => $total_harga,
        'sisa_slot' => $sisaSlot,
        'message' => 'Pendaftaran berhasil! Silakan lanjut ke pembayaran.'
    ]);

} catch (Exception $e) {
    // ✅ Rollback jika ada error
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>
