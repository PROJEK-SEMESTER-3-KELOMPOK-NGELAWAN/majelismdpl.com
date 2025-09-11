<?php
include('koneksi.php'); // Menghubungkan ke database

// Ambil data input dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Query untuk mencari pengguna berdasarkan username
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah pengguna ditemukan
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password tanpa hashing
    if ($password == $user['password']) {
        // Login berhasil, cek role dan arahkan
        if ($user['role'] == 'admin') {
            // Arahkan ke halaman admin
            echo json_encode(["message" => "Login berhasil", "role" => "admin"]);
            
        } else {
            // Arahkan ke halaman utama (index.php)
            echo json_encode(["message" => "Login berhasil", "role" => "user"]);
        }
    } else {
        // Password salah
        echo json_encode(["message" => "Password salah", "status" => false]);
    }
} else {
    // Username tidak ditemukan
    echo json_encode(["message" => "Username tidak ditemukan", "status" => false]);
}

$conn->close();
?>
