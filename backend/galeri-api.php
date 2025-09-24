<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'koneksi.php';

// Function untuk generate unique filename
function generateUniqueFilename($originalName)
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name); // Remove special characters
    return $name . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
}

// Function untuk validasi file gambar
function validateImage($file)
{
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return "Tipe file tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.";
    }

    if ($file['size'] > $maxSize) {
        return "Ukuran file terlalu besar. Maksimal 5MB.";
    }

    return null;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET - Ambil semua gambar dari database
        if (isset($_GET['action']) && $_GET['action'] === 'get') {
            $sql = "SELECT * FROM tb_galleries ORDER BY id_galleries DESC";
            $result = $conn->query($sql);

            if ($result) {
                $galleries = [];
                while ($row = $result->fetch_assoc()) {
                    $galleries[] = $row;
                }

                echo json_encode([
                    'success' => true,
                    'data' => $galleries,
                    'message' => 'Data berhasil diambil'
                ]);
            } else {
                throw new Exception("Error mengambil data: " . $conn->error);
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            // DELETE - Hapus gambar
            $id = intval($_POST['id']);
            $imageName = $_POST['imageName'];

            // Hapus file dari folder img
            $imagePath = "../img/" . $imageName;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Hapus dari database
            $sql = "DELETE FROM tb_galleries WHERE id_galleries = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Gambar berhasil dihapus'
                ]);
            } else {
                throw new Exception("Error menghapus data: " . $stmt->error);
            }

            $stmt->close();
        } else {
            // POST - Upload gambar baru
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File tidak ditemukan atau terjadi error saat upload.");
            }

            $file = $_FILES['image'];

            // Validasi file
            $validationError = validateImage($file);
            if ($validationError) {
                throw new Exception($validationError);
            }

            // Generate nama file unik
            $uniqueFilename = generateUniqueFilename($file['name']);

            // Path untuk menyimpan file
            $uploadDir = "../img/";
            $uploadPath = $uploadDir . $uniqueFilename;

            // Buat direktori jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Upload file ke server
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Simpan informasi ke database
                $sql = "INSERT INTO tb_galleries (gallery) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $uniqueFilename);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Gambar berhasil diupload',
                        'data' => [
                            'id' => $conn->insert_id,
                            'filename' => $uniqueFilename
                        ]
                    ]);
                } else {
                    // Hapus file jika gagal simpan ke database
                    unlink($uploadPath);
                    throw new Exception("Error menyimpan ke database: " . $stmt->error);
                }

                $stmt->close();
            } else {
                throw new Exception("Gagal mengupload file ke server.");
            }
        }
    } else {
        throw new Exception("Method tidak didukung.");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
