<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../koneksi.php';
require_once '../email-verify/EmailService.php';

// Disable error display untuk production
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ambil data dari request
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_wa = trim($_POST['no_wa'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        // Validasi input
        if (empty($username) || empty($password) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Username, password, dan email harus diisi'
            ]);
            exit;
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Format email tidak valid'
            ]);
            exit;
        }

        // Cek apakah username sudah ada (gunakan id_user sesuai table)
        $checkUsername = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
        if (!$checkUsername) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $resultUsername = $checkUsername->get_result();

        if ($resultUsername->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Username sudah digunakan'
            ]);
            exit;
        }
        $checkUsername->close();

        // Cek apakah email sudah ada (gunakan id_user sesuai table)
        $checkEmail = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
        if (!$checkEmail) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();

        if ($resultEmail->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ]);
            exit;
        }
        $checkEmail->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generate OTP 6 digit
        $otp = sprintf("%06d", random_int(0, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Simpan data pending user ke file JSON
        $tempDir = __DIR__ . '/../email-verify/temp/pending_users';
        
        // Buat folder jika belum ada
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $pendingData = [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'no_wa' => $no_wa,
            'alamat' => $alamat,
            'verification_token' => $otp,
            'expires_at' => $expires_at,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $tempFile = $tempDir . '/' . $otp . '.json';

        if (file_put_contents($tempFile, json_encode($pendingData, JSON_PRETTY_PRINT))) {
            // Kirim email OTP
            try {
                $emailService = new EmailService();
                if ($emailService->sendVerificationEmail($email, $username, $otp)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'OTP berhasil dikirim ke email Anda',
                        'email' => $email,
                        'expires_at' => $expires_at
                    ]);
                } else {
                    // Hapus file temp jika email gagal dikirim
                    if (file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal mengirim email OTP'
                    ]);
                }
            } catch (Exception $e) {
                // Hapus file temp jika terjadi error
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                error_log("Email sending error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim email'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data pendaftaran'
            ]);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid'
    ]);
}
?>
