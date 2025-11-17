<?php
/**
 * ============================================
 * GET PROFILE API
 * Mengambil data profil user dari database
 * Format konsisten dengan get-meeting-point.php
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Include config dan koneksi dengan path relatif
require_once dirname(__FILE__, 3) . '/config.php';
require_once dirname(__FILE__, 2) . '/koneksi.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST'
    ]);
    exit;
}

// Ambil id_user dari berbagai sumber (JSON body, POST, atau REQUEST)
$input = json_decode(file_get_contents('php://input'), true);
$id_user = 0;

if (isset($input['id_user'])) {
    $id_user = intval($input['id_user']);
} elseif (isset($_POST['id_user'])) {
    $id_user = intval($_POST['id_user']);
} elseif (isset($_REQUEST['id_user'])) {
    $id_user = intval($_REQUEST['id_user']);
}

// Validasi id_user
if ($id_user <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID User tidak valid',
        'received_id_user' => $id_user
    ]);
    exit;
}

// Query database
try {
    $query = "
        SELECT 
            id_user, 
            username, 
            email, 
            no_wa, 
            alamat, 
            foto_profil, 
            role, 
            email_verified 
        FROM users 
        WHERE id_user = ? 
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_user);

    if (!$stmt->execute()) {
        throw new Exception("Gagal mengeksekusi query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Cek apakah user ditemukan
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan dalam database',
            'received_id_user' => $id_user
        ]);
        $stmt->close();
        exit;
    }

    // Ambil data user
    $user = $result->fetch_assoc();
    $stmt->close();

    // Format foto profil URL jika ada
    $fotoProfil = '';
    if (!empty($user['foto_profil'])) {
        $fotoProfil = BASE_URL . '/' . ltrim($user['foto_profil'], '/');
    }

    // Format response
    $response = [
        'success' => true,
        'message' => 'Data profil berhasil diambil',
        'data' => [
            'id_user' => intval($user['id_user']),
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'whatsapp' => $user['no_wa'] ?? '',
            'alamat' => $user['alamat'] ?? '',
            'foto_profil' => $fotoProfil,
            'role' => $user['role'] ?? 'user',
            'email_verified' => intval($user['email_verified']) === 1
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
