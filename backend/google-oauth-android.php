<?php
session_start();
require_once 'koneksi.php';

// ==================== CONFIGURATION ====================
// 🟡 GANTI URL NGROK INI SETIAP KALI RESTART NGROK
$NGROK_URL = 'https://567007e9f30d.ngrok-free.app'; // ← UBAH SESUAI NGROK URL KAMU

// 🔵 DEVELOPMENT CONFIGURATION
$DEV_PROJECT_FOLDER = 'majelismdpl.com';
$DEV_BACKEND_FOLDER = 'backend';

// 🟠 PRODUCTION CONFIGURATION
$PROD_DOMAIN = 'yourdomain.com';
$PROD_API_PATH = 'api';

// OAuth Credentials
$GOOGLE_CLIENT_ID = '330248433279-1dj4e4squfhatlfkcrqa149kobuftqq0.apps.googleusercontent.com';
$GOOGLE_CLIENT_SECRET = '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';

// ==================== ENVIRONMENT DETECTION ====================
// Deteksi environment berdasarkan domain
$is_production = strpos($_SERVER['HTTP_HOST'], $PROD_DOMAIN) !== false;

// ⚠️ SELALU PAKAI NGROK UNTUK DEVELOPMENT/TESTING
if ($is_production) {
    // 🟠 PRODUCTION MODE
    $base_url = "https://{$PROD_DOMAIN}/{$PROD_API_PATH}";
    $redirect_uri = "{$base_url}/google-oauth-android.php";

    error_log("OAUTH: PRODUCTION mode - " . $_SERVER['HTTP_HOST']);
} else {
    // 🟢 DEVELOPMENT MODE - SELALU PAKAI NGROK
    $base_url = "{$NGROK_URL}/{$DEV_PROJECT_FOLDER}/{$DEV_BACKEND_FOLDER}";
    $redirect_uri = "{$base_url}/google-oauth-android.php";

    error_log("OAUTH: DEVELOPMENT mode - ALWAYS using NGROK: " . $NGROK_URL);
    error_log("OAUTH: Accessed from host: " . $_SERVER['HTTP_HOST'] . " but redirecting to NGROK");
}

$google_oauth_client_id = $GOOGLE_CLIENT_ID;
$google_oauth_client_secret = $GOOGLE_CLIENT_SECRET;
$google_oauth_redirect_uri = $redirect_uri;

// Debug: Log URL yang akan digunakan
error_log("OAUTH CONFIG: Redirect URI = " . $redirect_uri);

// Determine auth type
$auth_type = $_GET['type'] ?? 'login';

// Validasi credentials
if (empty($google_oauth_client_id) || empty($google_oauth_client_secret)) {
    error_log("OAUTH ERROR: Missing credentials");
    $params = http_build_query([
        'success' => '0',
        'message' => 'OAuth configuration error'
    ]);
    $redirect_url = "majelismdpl://oauth/callback?{$params}";
    header("Location: {$redirect_url}");
    exit;
}

// Step 1: Redirect to Google if no code
if (!isset($_GET['code'])) {
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $auth_type
    ];

    $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);

    error_log("OAUTH: Redirecting to Google with ngrok URI: " . $google_oauth_redirect_uri);

    header('Location: ' . $auth_url);
    exit;
}

// Step 2: Handle callback from Google
if (isset($_GET['code']) && !empty($_GET['code'])) {
    error_log("OAUTH: Received authorization code from Google");

    $auth_type = $_GET['state'] ?? 'login';

    // Exchange code for token
    $params = [
        'code' => $_GET['code'],
        'client_id' => $google_oauth_client_id,
        'client_secret' => $google_oauth_client_secret,
        'redirect_uri' => $google_oauth_redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    error_log("OAUTH: Exchanging code for token with redirect_uri: " . $google_oauth_redirect_uri);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);

    if (curl_error($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("OAUTH ERROR: cURL error - " . $error);

        $params = http_build_query([
            'success' => '0',
            'message' => 'Error connecting to Google: ' . $error
        ]);
        $redirect_url = "majelismdpl://oauth/callback?{$params}";
        header("Location: {$redirect_url}");
        exit;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("OAUTH: Google token response HTTP code: " . $http_code);
    error_log("OAUTH: Google token response: " . $response);

    $response = json_decode($response, true);

    if (isset($response['error'])) {
        error_log("OAUTH ERROR: Token exchange failed - " . json_encode($response));
        $error_msg = $response['error_description'] ?? $response['error'] ?? 'Unknown error';

        $params = http_build_query([
            'success' => '0',
            'message' => 'Google OAuth Error: ' . $error_msg
        ]);
        $redirect_url = "majelismdpl://oauth/callback?{$params}";
        header("Location: {$redirect_url}");
        exit;
    }

    if (isset($response['access_token'])) {
        error_log("OAUTH: Successfully got access token");

        // Get user info
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        error_log("OAUTH: User data received: " . json_encode($user_data));

        if (isset($user_data['email'])) {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id_user, username, role FROM users WHERE email = ?");
            if (!$stmt) {
                error_log("OAUTH ERROR: Database prepare failed - " . mysqli_error($conn));
                $params = http_build_query([
                    'success' => '0',
                    'message' => 'Database error'
                ]);
                $redirect_url = "majelismdpl://oauth/callback?{$params}";
                header("Location: {$redirect_url}");
                exit;
            }

            $stmt->bind_param("s", $user_data['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // User exists - LOGIN
                $user = $result->fetch_assoc();
                error_log("OAUTH LOGIN SUCCESS: User=" . $user['username'] . ", Email=" . $user_data['email']);

                $params = http_build_query([
                    'success' => '1',
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'message' => 'Login berhasil! Selamat datang ' . $user['username']
                ]);

                $redirect_url = "majelismdpl://oauth/callback?{$params}";
                error_log("OAUTH: Redirecting to Android app: " . $redirect_url);
                header("Location: {$redirect_url}");
                exit;
            } else {
                // User doesn't exist
                if ($auth_type === 'login') {
                    error_log("OAUTH LOGIN FAILED: User not found - " . $user_data['email']);

                    $params = http_build_query([
                        'success' => '0',
                        'message' => 'Akun Google belum terdaftar. Silakan daftar terlebih dahulu.'
                    ]);
                } else {
                    // Create new user for signup
                    $username = $user_data['name'] ?? 'GoogleUser_' . time();
                    $email = $user_data['email'];
                    $password = password_hash('google_oauth_' . uniqid(), PASSWORD_DEFAULT);

                    $stmt2 = $conn->prepare(
                        "INSERT INTO users (username, password, role, email, no_wa, alamat) VALUES (?, ?, 'user', ?, '', '')"
                    );

                    if (!$stmt2) {
                        error_log("OAUTH ERROR: Database prepare failed for insert - " . mysqli_error($conn));
                        $params = http_build_query([
                            'success' => '0',
                            'message' => 'Database error during signup'
                        ]);
                    } else {
                        $stmt2->bind_param("sss", $username, $password, $email);

                        if ($stmt2->execute()) {
                            error_log("OAUTH SIGNUP SUCCESS: User=" . $username . ", Email=" . $email);

                            $params = http_build_query([
                                'success' => '1',
                                'username' => $username,
                                'role' => 'user',
                                'message' => 'Registrasi berhasil! Selamat datang ' . $username
                            ]);
                        } else {
                            error_log("OAUTH SIGNUP FAILED: Database execute error - " . mysqli_error($conn));

                            $params = http_build_query([
                                'success' => '0',
                                'message' => 'Gagal membuat akun. Silakan coba lagi.'
                            ]);
                        }
                        $stmt2->close();
                    }
                }

                $redirect_url = "majelismdpl://oauth/callback?{$params}";
                error_log("OAUTH: Redirecting to Android app: " . $redirect_url);
                header("Location: {$redirect_url}");
                exit;
            }
            $stmt->close();
        } else {
            error_log("OAUTH ERROR: No email in user data - " . json_encode($user_data));
            $params = http_build_query([
                'success' => '0',
                'message' => 'Tidak dapat mengambil email dari Google'
            ]);
            $redirect_url = "majelismdpl://oauth/callback?{$params}";
            header("Location: {$redirect_url}");
            exit;
        }
    } else {
        error_log("OAUTH ERROR: No access token in response - " . json_encode($response));
        $params = http_build_query([
            'success' => '0',
            'message' => 'Failed to get access token from Google'
        ]);
        $redirect_url = "majelismdpl://oauth/callback?{$params}";
        header("Location: {$redirect_url}");
        exit;
    }
}

// Fallback error
error_log("OAUTH FALLBACK ERROR: Reached end of script without processing");
$params = http_build_query([
    'success' => '0',
    'message' => 'OAuth process failed - please try again'
]);
$redirect_url = "majelismdpl://oauth/callback?{$params}";
header("Location: {$redirect_url}");
exit;
