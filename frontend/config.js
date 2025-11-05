// ========================================
// CONFIGURATION - SIMPLE VERSION
// UPDATED FOR SEAMLESS LOCAL, NGROK, PRODUCTION
// ========================================

/**
 * Deteksi environment dari hostname
 * - LOCAL: localhost atau 127.0.0.1
 * - NGROK: Sudah punya domain ngrok
 * - PRODUCTION: majelismdpl.com atau www.majelismdpl.com
 */
function getEnvironment() {
  const hostname = window.location.hostname;
  
  // Deteksi NGROK
  if (hostname.includes("ngrok") || hostname.includes("ngrok-free")) {
    return "NGROK";
  }
  
  // Deteksi PRODUCTION
  if (hostname === "majelismdpl.com" || hostname === "www.majelismdpl.com") {
    return "PRODUCTION";
  }
  
  // Default LOCAL
  return "LOCAL";
}


/**
 * Base URL mapping untuk berbagai environment
 * 
 * LOCAL:      http://localhost/majelismdpl.com
 * NGROK:      https://xxxx-xx-xxx-xxx-xx.ngrok-free.app (auto-detected)
 * PRODUCTION: https://majelismdpl.com
 */
const BASE_URL_MAP = {
  LOCAL: "http://localhost/majelismdpl.com",
  NGROK: window.location.origin,
  PRODUCTION: "https://majelismdpl.com",
};


// ========== MAIN CONFIG ==========
const ENVIRONMENT = getEnvironment();
const BASE_URL = BASE_URL_MAP[ENVIRONMENT];
const API_URL = BASE_URL + "/backend";
const ASSETS_URL = BASE_URL;


// ========== HELPER FUNCTIONS ==========

/**
 * Get base URL dari aplikasi
 * Contoh output: "http://localhost/majelismdpl.com" atau "https://majelismdpl.com"
 */
function getBaseUrl() {
  return BASE_URL;
}


/**
 * Get API URL dengan endpoint
 * @param {string} endpoint - Path API (contoh: "login-api.php" atau "/backend/login-api.php")
 * @returns {string} - Full API URL (contoh: "http://localhost/majelismdpl.com/backend/login-api.php")
 */
function getApiUrl(endpoint = "") {
  // Hapus leading slash jika ada
  endpoint = endpoint.replace(/^\//, "");
  
  // Jika endpoint sudah include /backend, gunakan langsung
  if (endpoint.startsWith("backend/")) {
    return BASE_URL + "/" + endpoint;
  }
  
  // Jika endpoint tanpa /backend, tambahkan
  return API_URL + "/" + endpoint;
}


/**
 * Get Assets URL dengan path
 * @param {string} path - Path file (contoh: "assets/logo.png" atau "/img/profile/photo.jpg")
 * @returns {string} - Full assets URL
 */
function getAssetsUrl(path = "") {
  path = path.replace(/^\//, "");
  return ASSETS_URL + "/" + path;
}


/**
 * Get Page URL untuk redirect atau link
 * @param {string} page - Path page (contoh: "index.php" atau "user/profile.php")
 * @returns {string} - Full page URL
 */
function getPageUrl(page = "") {
  page = page.replace(/^\//, "");
  return BASE_URL + "/" + page;
}


/**
 * Get Gallery/Image URL dengan auto-detect path
 * SMART FUNCTION untuk image paths yang flexible
 * @param {string} filename - Nama file gambar atau path lengkap
 * @returns {string} - Full image URL
 */
function getImageUrl(filename = "") {
  if (!filename) return getAssetsUrl("img/");
  
  filename = filename.replace(/^\//, "");
  
  // Jika sudah punya path lengkap (ada /)
  if (filename.includes('/')) {
    // Jika sudah mulai dengan img/, gunakan langsung
    if (filename.startsWith('img/')) {
      return getAssetsUrl(filename);
    }
    // Jika ada path lain, asumsikan relatif dari assets
    return getAssetsUrl(filename);
  }
  
  // Jika hanya filename, coba cari di berbagai lokasi dengan priority:
  // 1. /img/gallery/
  // 2. /img/
  // Gunakan /img/ sebagai default
  return getAssetsUrl('img/' + filename);
}


/**
 * Get Gallery Image URL (khusus untuk galeri)
 * Untuk images dari database yang disimpan di /img/gallery/
 * @param {string} filename - Nama file dari database
 * @returns {string} - Full gallery image URL
 */
function getGalleryImageUrl(filename = "") {
  if (!filename) return getAssetsUrl("img/gallery/");
  
  filename = filename.replace(/^\//, "");
  
  // Jika sudah punya img/gallery/ prefix
  if (filename.startsWith('img/gallery/')) {
    return getAssetsUrl(filename);
  }
  
  // Jika hanya filename
  return getAssetsUrl('img/gallery/' + filename);
}


// ========== EXPORT TO WINDOW ==========
// Membuat function dan variable tersedia globally

window.getBaseUrl = getBaseUrl;
window.getApiUrl = getApiUrl;
window.getAssetsUrl = getAssetsUrl;
window.getPageUrl = getPageUrl;
window.getImageUrl = getImageUrl;
window.getGalleryImageUrl = getGalleryImageUrl;

// Expose config variables
window.BASE_URL = BASE_URL;
window.API_URL = API_URL;
window.ASSETS_URL = ASSETS_URL;
window.ENVIRONMENT = ENVIRONMENT;


// ========== DEBUG INFO (Optional - bisa dihapus di production) ==========
if (window.location.hash === "#debug-config") {
  console.log("=== MAJELIS MDPL CONFIG DEBUG ===");
  console.log("Environment:", ENVIRONMENT);
  console.log("Base URL:", BASE_URL);
  console.log("API URL:", API_URL);
  console.log("Assets URL:", ASSETS_URL);
  console.log("Current Hostname:", window.location.hostname);
  console.log("Current Origin:", window.location.origin);
  console.log("=====================================");
}
