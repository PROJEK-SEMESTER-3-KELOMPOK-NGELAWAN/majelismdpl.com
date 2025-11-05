<?php
// ========================================
// CONFIGURATION - SIMPLE VERSION
// ========================================

// 🟢 UBAH SINI untuk ganti environment ganti yang sebelah kanan tulisan APP_MODE
define('APP_MODE', 'LOCAL'); // Pilih: 'LOCAL' | 'NGROK' | 'PRODUCTION'

// 🔵 UBAH URL SINI saat Ngrok/Production berubah
const BASE_URL_MAP = [
    'LOCAL'      => 'http://localhost/majelismdpl.com',
    'NGROK'      => 'https://33242e509934.ngrok-free.app',      // ← UBAH INI
    'PRODUCTION' => 'https://majelismdpl.com',                   // ← UBAH INI
];

$base_url = BASE_URL_MAP[APP_MODE];
define('BASE_URL', $base_url);
define('API_URL', $base_url . '/backend');
define('ASSETS_URL', $base_url);

// Helper functions
function getBaseUrl()
{
    return BASE_URL;
}
function getApiUrl($endpoint = '')
{
    return API_URL . '/' . ltrim($endpoint, '/');
}
function getAssetsUrl($path = '')
{
    return ASSETS_URL . '/' . ltrim($path, '/');
}
function getPageUrl($page = '')
{
    return BASE_URL . '/' . ltrim($page, '/');
}
