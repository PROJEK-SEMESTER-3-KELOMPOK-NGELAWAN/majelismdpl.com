<?php
// tes ombak
$host = "localhost"; // Host
$username = "root";  // Username MySQL
$password = "";      // Password MySQL
$dbname = "db_majelis"; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);

}
?>
