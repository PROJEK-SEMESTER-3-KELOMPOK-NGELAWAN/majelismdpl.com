<?php
ini_set('display_errors', 0);
error_reporting(0);

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

try {
    if (!file_exists('koneksi.php')) {
        echo json_encode(['success' => false, 'message' => 'File koneksi.php tidak ditemukan']);
        exit;
    }
    require_once 'koneksi.php';

    if (!isset($conn) || $conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $no_wa    = trim($_POST['no_wa'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');

    if (!$username || !$password || !$email || !$no_wa || !$alamat) {
        echo json_encode(['success' => false, 'message' => 'Semua kolom wajib diisi']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        exit;
    }

    $checkStmt = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
    if (!$checkStmt) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan query']);
        exit;
    }
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        echo json_encode(['success' => false, 'message' => 'Username atau email sudah terdaftar']);
        exit;
    }
    $checkStmt->close();

    $tempDir = __DIR__ . '/temp/pending_users';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $pendingFiles = glob($tempDir . '/*.json');
    foreach ($pendingFiles as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && ($data['username'] === $username || $data['email'] === $email)) {
            echo json_encode(['success' => false, 'message' => 'Username atau email sudah dalam proses registrasi']);
            exit;
        }
    }

    foreach ($pendingFiles as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && isset($data['expires_at']) && strtotime($data['expires_at']) < time()) {
            unlink($file);
        }
    }

    // Generate OTP numeric 6 digit
    $otp = strval(random_int(100000, 999999));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $tempData = [
        'username' => $username,
        'password' => $hashed,
        'email' => $email,
        'no_wa' => $no_wa,
        'alamat' => $alamat,
        'verification_token' => $otp,
        'expires_at' => $expires_at,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $tempFile = $tempDir . '/' . $otp . '.json';
    if (!file_put_contents($tempFile, json_encode($tempData, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data sementara']);
        exit;
    }

    $emailSent = false;
    try {
        if (file_exists('email-verify/EmailService.php')) {
            require_once 'email-verify/EmailService.php';
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($email, $username, $otp);
        }
    } catch (Exception $e) {
        error_log("Email service error: " . $e->getMessage());
        $emailSent = false;
    }

    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP verifikasi akun.',
            'email_sent' => true,
            'email' => $email,
            'pending' => true
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil, namun email OTP verifikasi gagal dikirim. Silakan gunakan fitur kirim ulang.',
            'email_sent' => false,
            'email' => $email,
            'pending' => true
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan fatal: ' . $e->getMessage()]);
}
?>
