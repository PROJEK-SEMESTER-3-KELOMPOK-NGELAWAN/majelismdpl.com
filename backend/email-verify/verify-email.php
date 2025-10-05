<?php
require_once '../koneksi.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    if (!$otp) {
        echo json_encode(['success' => false, 'message' => 'Kode OTP tidak boleh kosong']);
        exit;
    }

    $tempDir = __DIR__ . '/../temp/pending_users';
    $tempFile = $tempDir . '/' . $otp . '.json';

    if (!file_exists($tempFile)) {
        echo json_encode(['success' => false, 'message' => 'Kode OTP salah atau sudah kedaluwarsa']);
        exit;
    }

    $pendingData = json_decode(file_get_contents($tempFile), true);
    if (!$pendingData) {
        echo json_encode(['success' => false, 'message' => 'Data OTP tidak valid']);
        exit;
    }

    if (strtotime($pendingData['expires_at']) < time()) {
        unlink($tempFile);
        echo json_encode(['success' => false, 'message' => 'Kode OTP sudah expired, silakan daftar ulang']);
        exit;
    }

    // Masukkan user ke database
    $conn->begin_transaction();
    try {
        $insertStmt = $conn->prepare(
            "INSERT INTO users (username, password, role, email, no_wa, alamat, email_verified)
            VALUES (?, ?, 'user', ?, ?, ?, 1)"
        );
        if (!$insertStmt) {
            throw new Exception("Gagal menyiapkan statement insert");
        }
        $insertStmt->bind_param(
            "sssss",
            $pendingData['username'],
            $pendingData['password'],
            $pendingData['email'],
            $pendingData['no_wa'],
            $pendingData['alamat']
        );
        if (!$insertStmt->execute()) {
            throw new Exception("Gagal membuat akun: " . $insertStmt->error);
        }
        $insertStmt->close();

        $conn->commit();
        unlink($tempFile);

        echo json_encode([
            'success' => true,
            'message' => 'Akun berhasil diverifikasi! Silakan login.',
            'username' => $pendingData['username']
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
}
?>
