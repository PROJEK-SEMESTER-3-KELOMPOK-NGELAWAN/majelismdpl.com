<?php
header('Content-Type: application/json');
require_once '../koneksi.php';
require_once 'EmailService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email diperlukan']);
        exit;
    }

    $tempDir = __DIR__ . '/../temp/pending_users';

    $pendingFiles = glob($tempDir . '/*.json');
    $foundFile = null;
    $pendingData = null;

    foreach ($pendingFiles as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['email'] === $email) {
            // Cek apakah expired
            if (strtotime($data['expires_at']) < time()) {
                unlink($file);
                continue;
            }
            $foundFile = $file;
            $pendingData = $data;
            break;
        }
    }

    if (!$foundFile) {
        echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan dalam daftar pending registrasi atau sudah kedaluwarsa']);
        exit;
    }

    // Generate OTP 6 digit digit numeric
    $newOtp = random_int(100000, 999999);
    $newExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $pendingData['verification_token'] = strval($newOtp);
    $pendingData['expires_at'] = $newExpires;

    // Hapus file lama
    unlink($foundFile);

    $newFile = $tempDir . '/' . $pendingData['verification_token'] . '.json';
    if (file_put_contents($newFile, json_encode($pendingData, JSON_PRETTY_PRINT))) {
        try {
            $emailService = new EmailService();
            if ($emailService->sendVerificationEmail($email, $pendingData['username'], $pendingData['verification_token'])) {
                echo json_encode(['success' => true, 'message' => 'Email OTP verifikasi telah dikirim ulang']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengirim email']);
            }
        } catch (Exception $e) {
            error_log("Resend email error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengirim email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui OTP']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
}
?>
