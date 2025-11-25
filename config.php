<?php
// ========================================
// CONFIGURATION - SIMPLE VERSION
// ========================================

define('APP_MODE', 'NGROK'); // Pilih: 'LOCAL' | 'NGROK' | 'PRODUCTION'

const BASE_URL_MAP = [
    'LOCAL'      => 'http://localhost/majelismdpl.com',
    'NGROK'      => 'https://98b453eb556d.ngrok-free.app',      // ← UBAH INI
    'PRODUCTION' => 'https://majelismdpl.com',                  // ← UBAH INI
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
