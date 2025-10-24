<?php
// backend/update-photo.php

require_once 'koneksi.php'; // Pastikan path ke koneksi.php sudah benar
session_start();

// Cek status login
if (!isset($_SESSION['id_user'])) {
    // Redirect atau kirim respons error
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$target_dir = "../img/profile/"; // Folder tujuan penyimpanan gambar (relatif dari file ini)
$max_file_size = 2 * 1024 * 1024; // Maksimal 2 MB

// Pastikan folder target ada
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Cek apakah file benar-benar diunggah
if (empty($_FILES['foto_profil']['name'])) {
    $_SESSION['pesan_error'] = "Tidak ada file yang diunggah.";
    header('Location: ../user/profile.php');
    exit();
}

$file = $_FILES['foto_profil'];
$file_name = basename($file['name']);
$file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Nama file baru: id_user_timestamp.ext
$new_file_name = $id_user . '_' . time() . '.' . $file_type;
$target_file = $target_dir . $new_file_name;

// Cek Error Unggahan
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['pesan_error'] = "Gagal unggah file. Kode error: " . $file['error'];
    header('Location: ../user/profile.php');
    exit();
}

// Cek Ukuran File
if ($file['size'] > $max_file_size) {
    $_SESSION['pesan_error'] = "Ukuran file terlalu besar. Maksimal 2 MB.";
    header('Location: ../user/profile.php');
    exit();
}

// Cek Tipe File yang Diizinkan
$allowed_types = ['jpg', 'jpeg', 'png'];
if (!in_array($file_type, $allowed_types)) {
    $_SESSION['pesan_error'] = "Hanya file JPG, JPEG, dan PNG yang diizinkan.";
    header('Location: ../user/profile.php');
    exit();
}

// --- Proses Unggah dan Update Database ---

// 1. Ambil nama file lama (jika ada) untuk dihapus
$old_photo_name = '';
$stmt_select = $conn->prepare("SELECT foto_profil FROM users WHERE id_user = ?");
$stmt_select->bind_param("i", $id_user);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
if ($row = $result_select->fetch_assoc()) {
    $old_photo_name = $row['foto_profil'];
}
$stmt_select->close();

// 2. Pindahkan file baru ke folder target
if (move_uploaded_file($file['tmp_name'], $target_file)) {
    
    // 3. Update database
    $stmt_update = $conn->prepare("UPDATE users SET foto_profil = ? WHERE id_user = ?");
    $stmt_update->bind_param("si", $new_file_name, $id_user);
    
    if ($stmt_update->execute()) {
        
        // 4. Hapus file lama, kecuali file default
        if (!empty($old_photo_name) && $old_photo_name !== 'default.jpg' && file_exists($target_dir . $old_photo_name)) {
            unlink($target_dir . $old_photo_name);
        }
        
        $_SESSION['pesan_sukses'] = "Foto profil berhasil diperbarui!";
        
    } else {
        // Jika gagal update DB, hapus file yang baru diunggah
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        $_SESSION['pesan_error'] = "Gagal memperbarui database: " . $stmt_update->error;
    }
    $stmt_update->close();

} else {
    $_SESSION['pesan_error'] = "Gagal memindahkan file yang diunggah. Pastikan folder 'img/profile/' memiliki izin tulis (write permission 0777).";
}

// Redirect kembali ke halaman profil
header('Location: ../user/profile.php');
exit();
?>