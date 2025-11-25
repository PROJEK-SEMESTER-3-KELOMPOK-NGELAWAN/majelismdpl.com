<?php
session_start();

// Enable CORS untuk mobile
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../config.php';
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi']);
        exit;
    }

    // Query dengan data lengkap untuk mobile
    $stmt = $conn->prepare("SELECT id_user, username, password, role, email, no_wa, alamat, foto_profil FROM users WHERE username = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['last_activity'] = time();

            // Set flag untuk admin
            if (in_array($user['role'], ['admin', 'super_admin'])) {
                $_SESSION['admin_logged_in'] = true;
                if ($user['role'] === 'super_admin') {
                    $_SESSION['super_admin_logged_in'] = true;
                }
            }

            session_write_close();

            // Response untuk mobile (dengan data user lengkap)
            $redirect_url = '';
            switch ($user['role']) {
                case 'super_admin':
                case 'admin':
                    $redirect_url = getPageUrl('admin/index.php');
                    break;
                case 'user':
                default:
                    $redirect_url = getPageUrl('index.php');
                    break;
            }

            echo json_encode([
                'success' => true,
                'role' => $user['role'],
                'username' => $user['username'],
                'redirect_url' => $redirect_url,
                'message' => 'Login berhasil sebagai ' . ucfirst(str_replace('_', ' ', $user['role'])),
                'user' => [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'email' => $user['email'] ?? '',
                    'whatsapp' => $user['no_wa'] ?? '',
                    'alamat' => $user['alamat'] ?? '',
                    'foto_url' => $user['foto_profil'] ?? '',
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
}
