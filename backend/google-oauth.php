<?php
session_start();
require_once '../config.php';
require_once 'koneksi.php';

// ========================================
// GOOGLE OAUTH CONFIGURATION
// ========================================

// Google OAuth Credentials
$google_oauth_client_id = '330248433279-1dj4e4squfhatlfkcrqa149kobuftqq0.apps.googleusercontent.com';
$google_oauth_client_secret = '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';

// ========== DYNAMIC REDIRECT URI (AUTO SYNC WITH config.php) ==========
// Otomatis membaca dari config.php dan membuat redirect URI yang benar
// Tidak perlu mengubah di sini, cukup ubah di config.php!

$google_oauth_redirect_uri = getPageUrl('backend/google-oauth.php');

// Determine if this is login or signup
$auth_type = $_GET['type'] ?? 'signup'; // default to signup for backward compatibility

// Validasi credentials
if (empty($google_oauth_client_id) || empty($google_oauth_client_secret)) {
    die("
    <script>
        alert('Google OAuth Configuration Error: Credentials not properly configured');
        window.location.href = '" . getPageUrl('index.php') . "';
    </script>
    ");
}

// ========================================
// STEP 1: REDIRECT TO GOOGLE
// ========================================
if (!isset($_GET['code'])) {
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $auth_type // Pass the auth type through state parameter
    ];

    $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    header('Location: ' . $auth_url);
    exit;
}

// ========================================
// STEP 2: HANDLE CALLBACK FROM GOOGLE
// ========================================
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Get auth type from state parameter
    $auth_type = $_GET['state'] ?? 'signup';

    // Exchange authorization code for access token
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
        die("
        <script>
            alert('Error connecting to Google. Please try again.');
            window.location.href = '" . getPageUrl('index.php') . "';
        </script>
        ");
    }
    curl_close($ch);

    $response = json_decode($response, true);

    if (isset($response['access_token'])) {
        // Get user info from Google
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        if (isset($user_data['email'])) {
            // Check if user already exists
            $stmt = $conn->prepare("SELECT id_user, username, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $user_data['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // ========================================
                // USER EXISTS - LOGIN
                // ========================================
                $user = $result->fetch_assoc();

                // Set session variables (consistent with login-api.php)
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['user_id'] = $user['id_user']; // For backward compatibility
                $_SESSION['user_email'] = $user_data['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Set admin flag if needed
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                }

                // Determine redirect based on role - DYNAMIC URL
                $redirect_url = getPageUrl('index.php');

                if (in_array($user['role'], ['admin', 'super_admin'])) {
                    $redirect_url = getPageUrl('admin/index.php');
                }

                echo "<!DOCTYPE html>
                    <html>
                    <head>
                        <title>Login Berhasil</title>
                        <meta charset='UTF-8'>
                        <style>
                            body {
                                background-image: url('" . getAssetsUrl('assets/login-bg.jpg') . "');
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-position: bottom center;
                                margin: 0;
                                padding: 0;
                                height: 100vh;
                                min-height: 100vh;
                            }
                        </style>
                    </head>
                    <body>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                            Swal.fire({
                                title: 'Login Berhasil!',
                                text: 'Selamat datang kembali " . addslashes($user['username']) . "',
                                icon: 'success',
                                confirmButtonText: 'Lanjutkan'
                            }).then(() => {
                                window.location.href = '" . $redirect_url . "';
                            });
                        </script>
                    </body>
                    </html>";
            } else {
                // ========================================
                // USER DOESN'T EXIST
                // ========================================
                if ($auth_type === 'login') {
                    // Login attempt but user doesn't exist
                    echo "<!DOCTYPE html>
                    <html>
                    <head>
                        <title>Login Gagal</title>
                        <meta charset='UTF-8'>
                    </head>
                    <body>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                            Swal.fire({
                                title: 'Akun Tidak Ditemukan',
                                text: 'Akun Google Anda belum terdaftar. Silakan daftar terlebih dahulu.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '" . getPageUrl('index.php') . "';
                            });
                        </script>
                    </body>
                    </html>";
                } else {
                    // ========================================
                    // SIGNUP - CREATE NEW USER
                    // ========================================
                    $username = $user_data['name'] ?? 'GoogleUser_' . time();
                    $email = $user_data['email'];
                    $password = password_hash('google_oauth_' . uniqid(), PASSWORD_DEFAULT);
                    $no_wa = '';
                    $alamat = '';

                    $stmt = $conn->prepare(
                        "INSERT INTO users (username, password, role, email, no_wa, alamat) VALUES (?, ?, 'user', ?, ?, ?)"
                    );
                    $stmt->bind_param("sssss", $username, $password, $email, $no_wa, $alamat);

                    if ($stmt->execute()) {
                        $new_user_id = $conn->insert_id;
                        $_SESSION['id_user'] = $new_user_id;
                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = 'user';
                        $_SESSION['last_activity'] = time();

                        echo "<!DOCTYPE html>
                        <html>
                        <head>
                            <title>Registrasi Berhasil</title>
                            <meta charset='UTF-8'>
                        </head>
                        <body>
                            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                            <script>
                                Swal.fire({
                                    title: 'Registrasi Berhasil!',
                                    text: 'Selamat datang " . addslashes($username) . "',
                                    icon: 'success',
                                    confirmButtonText: 'Ke Beranda'
                                }).then(() => {
                                    window.location.href = '" . getPageUrl('index.php') . "';
                                });
                            </script>
                        </body>
                        </html>";
                    } else {
                        echo "<script>
                            alert('Terjadi kesalahan saat registrasi. Silakan coba lagi.');
                            window.location.href = '" . getPageUrl('index.php') . "';
                        </script>";
                    }
                }
            }
            $stmt->close();
        } else {
            echo "<script>
                alert('Gagal mendapatkan informasi email dari Google. Silakan coba lagi.');
                window.location.href = '" . getPageUrl('index.php') . "';
            </script>";
        }
    } else {
        echo "<script>
            alert('Gagal mendapatkan akses dari Google. Silakan coba lagi.');
            window.location.href = '" . getPageUrl('index.php') . "';
        </script>";
    }
} else {
    echo "<script>
        alert('Authorization code tidak ditemukan. Silakan coba lagi.');
        window.location.href = '" . getPageUrl('index.php') . "';
    </script>";
}
