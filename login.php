<?php
// Mulai session untuk menyimpan data login
session_start();

// Ambil data dari form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input
if (empty($username) || empty($password)) {
    echo "Username dan password harus diisi!";
    exit;
}

// URL endpoint API login
$api_url = 'http://localhost/majelismdpl.com/backend/login-api.php';

// Data yang akan dikirim ke API
$data = [
    'username' => $username,
    'password' => $password
];

// Inisialisasi cURL untuk mengirim request ke API
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// Eksekusi request dan ambil respons
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug: Cek apakah API response berhasil
if ($response === false || $http_code !== 200) {
    echo "Error: Tidak dapat terhubung ke API login";
    exit;
}

// Decode respons JSON
$result = json_decode($response, true);

// Debug: Cek apakah JSON decode berhasil
if ($result === null) {
    echo "Error: Response dari API tidak valid";
    echo "<br>Raw response: " . $response;
    exit;
}


// Tangani respons dari API
if ($result['status'] === false) {
    // Jika login gagal
    echo $result['message'];
    // Kembalikan ke halaman login dengan pesan error
    header("Refresh: 3; url=login.html");
    exit;
} else {
    // Jika login berhasil, simpan data session
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $result['role'];
    
    // Redirect berdasarkan role
    if ($result['role'] === 'admin') {
        // Arahkan ke halaman admin
        echo "<script>
            alert('selamat datang admin:)');
            window.location.href = 'admin/index.php';
        </script>";
        exit;
    } else {
        // Arahkan ke halaman utama
        echo "<script>
            alert('Login berhasil!');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
}
?>