<?php
session_start();
require_once '../config.php';
require_once 'koneksi.php';

// ========================================
// CONFIGURATION
// ========================================
$google_oauth_client_id = '330248433279-1dj4e4squfhatlfkcrqa149kobuftqq0.apps.googleusercontent.com';
$google_oauth_client_secret = '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';
$google_oauth_redirect_uri = getPageUrl('backend/google-oauth.php');

// Helper function untuk set flash message sweetalert
function setFlashSwal($type, $title, $text, $buttonText, $redirectUrl)
{
    $_SESSION['flash_swal'] = [
        'type' => $type, // 'success' or 'error'
        'title' => $title,
        'text' => $text,
        'buttonText' => $buttonText
    ];
    header("Location: " . $redirectUrl);
    exit;
}

if (empty($google_oauth_client_id) || empty($google_oauth_client_secret)) {
    setFlashSwal('error', 'Config Error', 'Google OAuth Configuration Error', 'OK', getPageUrl('index.php'));
}

// ========================================
// STEP 1: REDIRECT TO GOOGLE
// ========================================
if (!isset($_GET['code'])) {
    $auth_type = $_GET['type'] ?? 'signup';
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $auth_type
    ];
    header('Location: https://accounts.google.com/o/oauth2/auth?' . http_build_query($params));
    exit;
}

// ========================================
// STEP 2: HANDLE CALLBACK
// ========================================
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $auth_type = $_GET['state'] ?? 'signup';

    // Exchange code for token
    $params = [
        'code' => $_GET['code'],
        'client_id' => $google_oauth_client_id,
        'client_secret' => $google_oauth_client_secret,
        'redirect_uri' => $google_oauth_redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    if (curl_error($ch)) {
        curl_close($ch);
        setFlashSwal('error', 'Koneksi Gagal', 'Gagal terhubung ke Google.', 'OK', getPageUrl('index.php'));
    }
    curl_close($ch);
    $response = json_decode($response, true);

    if (isset($response['access_token'])) {
        // Get User Info
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        if (isset($user_data['email'])) {
            // Check User in DB
            $stmt = $conn->prepare("SELECT id_user, username, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $user_data['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // ========================================
                // LOGIN BERHASIL
                // ========================================
                $user = $result->fetch_assoc();
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['user_id'] = $user['id_user']; // Compatibility
                $_SESSION['user_email'] = $user_data['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                }

                $redirect_url = getPageUrl('index.php');
                if (in_array($user['role'], ['admin', 'super_admin'])) {
                    $redirect_url = getPageUrl('admin/index.php');
                }

                // SET FLASH MESSAGE SUCCESS & REDIRECT
                setFlashSwal(
                    'success',
                    'Login Berhasil!',
                    'Selamat datang kembali, ' . $user['username'],
                    'Lanjutkan',
                    $redirect_url
                );
            } else {
                // ========================================
                // USER BELUM ADA
                // ========================================
                if ($auth_type === 'login') {
                    // Gagal Login (Akun tidak ada)
                    setFlashSwal(
                        'error',
                        'Akun Tidak Ditemukan',
                        'Email ini belum terdaftar. Silakan lakukan registrasi.',
                        'Kembali',
                        getPageUrl('index.php')
                    );
                } else {
                    // ========================================
                    // REGISTRASI BARU
                    // ========================================
                    $username = $user_data['name'] ?? 'GoogleUser_' . time();
                    $email = $user_data['email'];
                    $password = password_hash('google_oauth_' . uniqid(), PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, email, no_wa, alamat) VALUES (?, ?, 'user', ?, '', '')");
                    $stmt->bind_param("ssss", $username, $password, $email);

                    if ($stmt->execute()) {
                        $new_user_id = $conn->insert_id;
                        $_SESSION['id_user'] = $new_user_id;
                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = 'user';
                        $_SESSION['last_activity'] = time();

                        // SET FLASH MESSAGE REGISTER SUCCESS & REDIRECT
                        setFlashSwal(
                            'success',
                            'Registrasi Berhasil!',
                            'Selamat bergabung, ' . $username,
                            'Mulai Jelajah',
                            getPageUrl('index.php')
                        );
                    } else {
                        setFlashSwal('error', 'Error', 'Gagal registrasi database.', 'OK', getPageUrl('index.php'));
                    }
                }
            }
            $stmt->close();
        }
    }
} else {
    header("Location: " . getPageUrl('index.php'));
    exit;
}
