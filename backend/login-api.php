<?php
session_start();
header('Content-Type: application/json');
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_user, username, password, role FROM users WHERE username = ?");
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
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Set flag khusus untuk admin
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
            }
            
            // Pastikan session tersimpan sebelum redirect
            session_write_close();
            
            echo json_encode(['success' => true, 'role' => $user['role']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan']);
    }
    $stmt->close();
} else {
    echo json_encode(['success'=>false,'message'=>'Metode tidak diizinkan']);
}
?>
