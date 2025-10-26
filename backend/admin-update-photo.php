<?php
// Isi file: ../backend/admin-update-photo.php

require_once 'koneksi.php'; 
require_once '../admin/auth_check.php'; 
// Asumsi 'auth_check.php' memulai session dan mengisi $_SESSION['id_user']

// Cek apakah user sudah login dan memiliki ID
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$id_user = $_SESSION['id_user'];
// JALUR DISESUAIKAN: ../img/profile/ relatif dari file ini (yang berada di folder backend)
$target_dir = "../img/profile/"; 
$max_file_size = 2 * 1024 * 1024; // 2MB

// Pastikan direktori ada (meskipun Anda sudah membuatnya, ini untuk jaga-jaga)
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true); 
}

if (isset($_FILES["admin_foto_profil"]) && $_FILES["admin_foto_profil"]["error"] == 0) {
    $file = $_FILES["admin_foto_profil"];

    // 1. Validasi Ukuran & Jenis File (sesuai dengan JS)
    if ($file["size"] > $max_file_size) {
        header('Location: ../admin/index.php?error=access_denied&message=' . urlencode('Ukuran file terlalu besar. Maksimal 2MB.'));
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file["type"], $allowed_types)) {
        header('Location: ../admin/index.php?error=access_denied&message=' . urlencode('Hanya file JPG, PNG, dan GIF yang diizinkan.'));
        exit;
    }

    // 2. Tentukan Nama & Jalur File
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    // Nama file: [ID_USER]_[TIMESTAMP].[EXT]
    $new_file_name = $id_user . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_file_name;
    
    // Jalur yang akan disimpan di database (relatif dari root web)
    $db_file_path = 'img/profile/' . $new_file_name; 

    // 3. Pindahkan File dan Update Database
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        
        // Cek dan Hapus Foto Lama (Penting!)
        $stmt_select = $conn->prepare("SELECT foto_profil FROM users WHERE id_user = ?");
        $stmt_select->bind_param("i", $id_user);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $old_photo_row = $result_select->fetch_assoc();
        $stmt_select->close();

        if ($old_photo_row && !empty($old_photo_row['foto_profil'])) {
            $old_file_path = '../' . $old_photo_row['foto_profil']; // Tambahkan '../' untuk jalur relatif dari backend
            // Pastikan bukan file default dan file ada sebelum dihapus
            if (file_exists($old_file_path) && is_file($old_file_path) && strpos($old_file_path, 'default') === false) {
                unlink($old_file_path); 
            }
        }
        
        // Update database
        $stmt_update = $conn->prepare("UPDATE users SET foto_profil = ? WHERE id_user = ?");
        $stmt_update->bind_param("si", $db_file_path, $id_user);

        if ($stmt_update->execute()) {
            $_SESSION['foto_profil'] = $db_file_path; // Update session
            $stmt_update->close();
            $conn->close();
            header('Location: ../admin/index.php?success=photo_updated'); // Tambahkan pesan sukses
            exit;
        } else {
            // Gagal update DB, hapus file yang baru diupload
            unlink($target_file); 
            $stmt_update->close();
            $conn->close();
            header('Location: ../admin/index.php?error=access_denied&message=' . urlencode('Gagal menyimpan jalur foto ke database.'));
            exit;
        }

    } else {
        header('Location: ../admin/index.php?error=access_denied&message=' . urlencode('Gagal mengupload file ke server.'));
        exit;
    }

} else {
    // Error upload file lainnya
    header('Location: ../admin/index.php?error=access_denied&message=' . urlencode('Tidak ada file yang dipilih atau terjadi error saat upload.'));
    exit;
}
?>