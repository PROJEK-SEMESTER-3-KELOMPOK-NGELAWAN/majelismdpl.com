<?php
/**
 * ============================================
 * FILE: mobile-config-api.php
 * LOKASI: backend/mobile/
 * FUNGSI: Return config untuk mobile app
 * ============================================
 */

require_once dirname(__FILE__, 3) . '/config.php';

// Enable CORS for mobile
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Return config
$config = [
    'success' => true,
    'environment' => APP_MODE,
    'base_url' => BASE_URL,
    'api_url' => API_URL,
    'assets_url' => ASSETS_URL,
    'endpoints' => [
        'login' => getApiUrl('login-api.php'),
        'register' => getApiUrl('registrasi-api.php'),
        'dashboard' => getApiUrl('dashboard-api.php'),
        'google_oauth' => getApiUrl('google-oauth-android.php'),
        'trips' => getApiUrl('trip-api.php'),
        'bookings' => getApiUrl('booking-api.php'),
        'payments' => getApiUrl('payment-api.php'),
    ],
    'mobile_api_path' => BASE_URL . '/backend/mobile',
    'version' => '1.0.0',
    'last_updated' => date('Y-m-d H:i:s'),
    'timezone' => 'Asia/Jakarta'
];

echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
