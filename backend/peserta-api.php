<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once 'koneksi.php';

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'all') {
                getAllParticipants($conn);
            } elseif ($action === 'detail' && isset($_GET['id'])) {
                getParticipantDetail($conn, $_GET['id']);
            } else {
                sendResponse(400, 'Invalid action or missing parameters');
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                createParticipant($conn);
            } elseif ($action === 'update' && isset($_GET['id'])) {
                // BARU: Handle update dengan file upload
                updateParticipantWithFile($conn, $_GET['id']);
            } else {
                sendResponse(400, 'Invalid action');
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                updateParticipant($conn, $_GET['id']);
            } else {
                sendResponse(400, 'Invalid action or missing ID');
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                deleteParticipant($conn, $_GET['id']);
            } else {
                sendResponse(400, 'Invalid action or missing ID');
            }
            break;
            
        default:
            sendResponse(405, 'Method not allowed');
    }
} catch (Exception $e) {
    sendResponse(500, 'Server error: ' . $e->getMessage());
}

// Function to get all participants with booking info
function getAllParticipants($conn) {
    $query = "
        SELECT 
            participants.*,
            bookings.id_booking,
            bookings.id_trip,
            bookings.jumlah_orang,
            bookings.total_harga,
            bookings.tanggal_booking,
            bookings.status
        FROM participants
        LEFT JOIN bookings ON participants.id_participant = bookings.id_participant
        ORDER BY participants.id_participant DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        sendResponse(500, 'Database query error: ' . $conn->error);
        return;
    }
    
    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    
    sendResponse(200, 'Success', $participants);
}

// Function to get participant detail
function getParticipantDetail($conn, $id) {
    $stmt = $conn->prepare("
        SELECT 
            participants.*,
            bookings.id_booking,
            bookings.id_trip,
            bookings.jumlah_orang,
            bookings.total_harga,
            bookings.tanggal_booking,
            bookings.status
        FROM participants
        LEFT JOIN bookings ON participants.id_participant = bookings.id_participant
        WHERE participants.id_participant = ?
    ");
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    
    $participant = $result->fetch_assoc();
    sendResponse(200, 'Success', $participant);
}

// BARU: Function untuk upload file
function uploadFile($file, $uploadDir = '../uploads/ktp/') {
    // Buat direktori jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validasi file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipe file tidak diizinkan. Gunakan JPEG, PNG, atau GIF.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file terlalu besar. Maksimal 5MB.');
    }
    
    // Generate nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'ktp_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/ktp/' . $fileName; // Return relative path untuk database
    } else {
        throw new Exception('Gagal mengupload file');
    }
}

// BARU: Function untuk update participant dengan file upload
function updateParticipantWithFile($conn, $id) {
    // Check if participant exists
    $checkStmt = $conn->prepare("SELECT foto_ktp FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    
    $currentData = $checkResult->fetch_assoc();
    $oldFotoPath = $currentData['foto_ktp'];
    
    // Ambil data dari POST
    $data = $_POST;
    $fotoKtpPath = $oldFotoPath; // Default: gunakan foto lama
    
    // Handle file upload jika ada
    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
        try {
            $fotoKtpPath = uploadFile($_FILES['foto_ktp']);
            
            // Hapus file lama jika ada dan berbeda
            if ($oldFotoPath && $oldFotoPath !== $fotoKtpPath && file_exists('../' . $oldFotoPath)) {
                unlink('../' . $oldFotoPath);
            }
        } catch (Exception $e) {
            sendResponse(400, 'Error upload file: ' . $e->getMessage());
            return;
        }
    }
    
    // Build dynamic update query
    $updateFields = [];
    $values = [];
    $types = '';
    
    $allowedFields = ['nama', 'email', 'no_wa', 'alamat', 'riwayat_penyakit', 'no_wa_darurat', 'tanggal_lahir', 'tempat_lahir', 'nik'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $data[$field];
            $types .= 's';
        }
    }
    
    // Tambahkan foto_ktp ke update
    $updateFields[] = "foto_ktp = ?";
    $values[] = $fotoKtpPath;
    $types .= 's';
    
    if (empty($updateFields)) {
        sendResponse(400, 'No valid fields to update');
        return;
    }
    
    $values[] = $id;
    $types .= 'i';
    
    $query = "UPDATE participants SET " . implode(', ', $updateFields) . " WHERE id_participant = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        sendResponse(200, 'Participant updated successfully');
    } else {
        sendResponse(500, 'Failed to update participant: ' . $stmt->error);
    }
}

// Function to create new participant
function createParticipant($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['nama', 'email', 'no_wa', 'alamat', 'tanggal_lahir', 'tempat_lahir', 'nik'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            sendResponse(400, "Field '$field' is required");
            return;
        }
    }
    
    // Check if email or NIK already exists
    $checkStmt = $conn->prepare("SELECT id_participant FROM participants WHERE email = ? OR nik = ?");
    $checkStmt->bind_param("ss", $data['email'], $data['nik']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        sendResponse(409, 'Email or NIK already exists');
        return;
    }
    
    // Insert new participant
    $stmt = $conn->prepare("
        INSERT INTO participants (nama, email, no_wa, alamat, riwayat_penyakit, no_wa_darurat, tanggal_lahir, tempat_lahir, nik, foto_ktp) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssssssssss", 
        $data['nama'],
        $data['email'],
        $data['no_wa'],
        $data['alamat'],
        $data['riwayat_penyakit'] ?? '',
        $data['no_wa_darurat'] ?? '',
        $data['tanggal_lahir'],
        $data['tempat_lahir'],
        $data['nik'],
        $data['foto_ktp'] ?? ''
    );
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        sendResponse(201, 'Participant created successfully', ['id_participant' => $newId]);
    } else {
        sendResponse(500, 'Failed to create participant: ' . $stmt->error);
    }
}

// Function to update participant (JSON only)
function updateParticipant($conn, $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if participant exists
    $checkStmt = $conn->prepare("SELECT id_participant FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    
    // Build dynamic update query
    $updateFields = [];
    $values = [];
    $types = '';
    
    $allowedFields = ['nama', 'email', 'no_wa', 'alamat', 'riwayat_penyakit', 'no_wa_darurat', 'tanggal_lahir', 'tempat_lahir', 'nik', 'foto_ktp'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $data[$field];
            $types .= 's';
        }
    }
    
    if (empty($updateFields)) {
        sendResponse(400, 'No valid fields to update');
        return;
    }
    
    $values[] = $id;
    $types .= 'i';
    
    $query = "UPDATE participants SET " . implode(', ', $updateFields) . " WHERE id_participant = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        sendResponse(200, 'Participant updated successfully');
    } else {
        sendResponse(500, 'Failed to update participant: ' . $stmt->error);
    }
}

// Function to delete participant
function deleteParticipant($conn, $id) {
    // Check if participant exists and get foto path
    $checkStmt = $conn->prepare("SELECT foto_ktp FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    
    $participantData = $checkResult->fetch_assoc();
    
    // Delete related bookings first
    $deleteBookingsStmt = $conn->prepare("DELETE FROM bookings WHERE id_participant = ?");
    $deleteBookingsStmt->bind_param("i", $id);
    $deleteBookingsStmt->execute();
    
    // Delete participant
    $deleteStmt = $conn->prepare("DELETE FROM participants WHERE id_participant = ?");
    $deleteStmt->bind_param("i", $id);
    
    if ($deleteStmt->execute()) {
        // Hapus file foto jika ada
        if ($participantData['foto_ktp'] && file_exists('../' . $participantData['foto_ktp'])) {
            unlink('../' . $participantData['foto_ktp']);
        }
        
        sendResponse(200, 'Participant deleted successfully');
    } else {
        sendResponse(500, 'Failed to delete participant: ' . $deleteStmt->error);
    }
}

// Helper function to send JSON response
function sendResponse($status, $message, $data = null) {
    http_response_code($status);
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}
?>
