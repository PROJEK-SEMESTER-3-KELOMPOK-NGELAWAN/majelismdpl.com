<?php
/**
 * ============================================
 * EDIT PROFILE API
 * Update data profil user + upload foto ke ../../img/profile/
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__, 3) . '/config.php';
require_once dirname(__FILE__, 2) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST'
    ]);
    exit;
}

$id_user = 0;
$username = '';
$email = '';
$no_wa = '';
$alamat = '';
$password = '';
$max_file_size = 2 * 1024 * 1024; // 2MB max      
$fotoProfilPath = null;

// Jika upload file (form-data)
if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
    $id_user = intval($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_wa = trim($_POST['whatsapp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- Proses upload foto ---
    $target_dir = "../../img/profile/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file = $_FILES['foto_profil'];
    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];

    if ($file['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2 MB.']);
        exit;
    }
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Format file hanya JPG, JPEG, PNG.']);
        exit;
    }

    $new_file_name = $id_user . '_' . time() . '.' . $file_type;
    $target_file = $target_dir . $new_file_name;

    // Ambil nama file lama
    $old_photo_name = '';
    $stmt_select = $conn->prepare("SELECT foto_profil FROM users WHERE id_user = ?");
    $stmt_select->bind_param("i", $id_user);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    if ($row = $result_select->fetch_assoc()) {
        $old_photo_name = $row['foto_profil'];
    }
    $stmt_select->close();

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Update db
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET username=?, email=?, no_wa=?, alamat=?, password=?, foto_profil=? WHERE id_user=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssi", $username, $email, $no_wa, $alamat, $hashedPassword, $new_file_name, $id_user);
        } else {
            $query = "UPDATE users SET username=?, email=?, no_wa=?, alamat=?, foto_profil=? WHERE id_user=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $username, $email, $no_wa, $alamat, $new_file_name, $id_user);
        }
        $success = $stmt->execute();
        $stmt->close();

        // Hapus foto lama jika ada (kecuali default)
        if ($success && !empty($old_photo_name) && $old_photo_name !== 'default.jpg' && file_exists($target_dir . $old_photo_name)) {
            unlink($target_dir . $old_photo_name);
        }

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Profil dan foto berhasil diperbarui!',
                'foto_profil_url' => BASE_URL . '/img/profile/' . $new_file_name
            ]);
        } else {
            // Jika gagal update DB, hapus file baru
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            echo json_encode([
                'success' => false,
                'message' => 'Gagal update database!'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal upload/memindahkan file. Pastikan folder img/profile/ writable.'
        ]);
    }
} else {
    // Tidak upload foto, hanya update data
    $input = json_decode(file_get_contents('php://input'), true);
    $id_user = intval($input['id_user'] ?? 0);
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $no_wa = trim($input['whatsapp'] ?? '');
    $alamat = trim($input['alamat'] ?? '');
    $password = trim($input['password'] ?? '');

    if ($id_user <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID User tidak valid']);
        exit;
    }
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username dan email wajib diisi']);
        exit;
    }

    // Update database tanpa foto_profil
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username=?, email=?, no_wa=?, alamat=?, password=? WHERE id_user=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $username, $email, $no_wa, $alamat, $hashedPassword, $id_user);
    } else {
        $query = "UPDATE users SET username=?, email=?, no_wa=?, alamat=? WHERE id_user=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $username, $email, $no_wa, $alamat, $id_user);
    }
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode([
        'success' => $success ? true : false,
        'message' => $success ? 'Profil berhasil diperbarui' : 'Gagal update database!'
    ]);
}

$conn->close();
?>
