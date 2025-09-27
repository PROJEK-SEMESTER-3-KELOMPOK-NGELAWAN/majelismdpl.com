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

// Start session and ambil id_user
session_start();
$id_user = $_SESSION['id_user'] ?? null;

// Helper log function
function logActivity($conn, $id_user, $aktivitas, $statusLog)
{
    if (!$id_user) return;
    $stmt = $conn->prepare("INSERT INTO activity_logs (aktivitas, waktu, status, id_user) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("ssi", $aktivitas, $statusLog, $id_user);
    $stmt->execute();
    $stmt->close();
}

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
                createParticipant($conn, $id_user);
            } elseif ($action === 'update' && isset($_GET['id'])) {
                updateParticipantWithFile($conn, $_GET['id'], $id_user);
            } else {
                sendResponse(400, 'Invalid action');
            }
            break;

        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                updateParticipant($conn, $_GET['id'], $id_user);
            } else {
                sendResponse(400, 'Invalid action or missing ID');
            }
            break;

        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                deleteParticipant($conn, $_GET['id'], $id_user);
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
function getAllParticipants($conn)
{
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
function getParticipantDetail($conn, $id)
{
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

// Function untuk upload file
function uploadFile($file, $uploadDir = '../uploads/ktp/')
{
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

// Function untuk update participant dengan file upload
function updateParticipantWithFile($conn, $id, $id_user)
{
    $checkStmt = $conn->prepare("SELECT foto_ktp, nama FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    $currentData = $checkResult->fetch_assoc();
    $oldFotoPath = $currentData['foto_ktp'];
    $participantNama = $currentData['nama'];
    $data = $_POST;
    $fotoKtpPath = $oldFotoPath; // Default gunakan foto lama

    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
        try {
            $fotoKtpPath = uploadFile($_FILES['foto_ktp']);
            if ($oldFotoPath && $oldFotoPath !== $fotoKtpPath && file_exists('../' . $oldFotoPath)) {
                unlink('../' . $oldFotoPath);
            }
        } catch (Exception $e) {
            sendResponse(400, 'Error upload file: ' . $e->getMessage());
            return;
        }
    }

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
        if ($id_user) {
            $aktivitas = "Mengupdate data peserta (ID: $id, Nama: $participantNama)";
            logActivity($conn, $id_user, $aktivitas, "update");
        }
        sendResponse(200, 'Participant updated successfully');
    } else {
        sendResponse(500, 'Failed to update participant: ' . $stmt->error);
    }
}

// Function to create new participant
function createParticipant($conn, $id_user)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $required_fields = ['nama', 'email', 'no_wa', 'alamat', 'tanggal_lahir', 'tempat_lahir', 'nik'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            sendResponse(400, "Field '$field' is required");
            return;
        }
    }
    $checkStmt = $conn->prepare("SELECT id_participant FROM participants WHERE email = ? OR nik = ?");
    $checkStmt->bind_param("ss", $data['email'], $data['nik']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        sendResponse(409, 'Email or NIK already exists');
        return;
    }
    $stmt = $conn->prepare("
        INSERT INTO participants (nama, email, no_wa, alamat, riwayat_penyakit, no_wa_darurat, tanggal_lahir, tempat_lahir, nik, foto_ktp)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssssssss",
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
        if ($id_user) {
            $aktivitas = "Menambahkan peserta baru: " . $data['nama'] . " (ID: $newId)";
            logActivity($conn, $id_user, $aktivitas, "publish");
        }
        sendResponse(201, 'Participant created successfully', ['id_participant' => $newId]);
    } else {
        sendResponse(500, 'Failed to create participant: ' . $stmt->error);
    }
}

// Function to update participant (JSON only)
function updateParticipant($conn, $id, $id_user)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $checkStmt = $conn->prepare("SELECT id_participant, nama FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    $participant = $checkResult->fetch_assoc();
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
        if ($id_user) {
            $aktivitas = "Mengupdate data peserta (ID: $id, Nama: " . $participant['nama'] . ")";
            logActivity($conn, $id_user, $aktivitas, "update");
        }
        sendResponse(200, 'Participant updated successfully');
    } else {
        sendResponse(500, 'Failed to update participant: ' . $stmt->error);
    }
}

// Function to delete participant
function deleteParticipant($conn, $id, $id_user)
{
    $checkStmt = $conn->prepare("SELECT foto_ktp, nama FROM participants WHERE id_participant = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows === 0) {
        sendResponse(404, 'Participant not found');
        return;
    }
    $participantData = $checkResult->fetch_assoc();
    $participantNama = $participantData['nama'];
    $deleteBookingsStmt = $conn->prepare("DELETE FROM bookings WHERE id_participant = ?");
    $deleteBookingsStmt->bind_param("i", $id);
    $deleteBookingsStmt->execute();
    $deleteStmt = $conn->prepare("DELETE FROM participants WHERE id_participant = ?");
    $deleteStmt->bind_param("i", $id);
    if ($deleteStmt->execute()) {
        if ($participantData['foto_ktp'] && file_exists('../' . $participantData['foto_ktp'])) {
            unlink('../' . $participantData['foto_ktp']);
        }
        if ($id_user) {
            $aktivitas = "Menghapus peserta (ID: $id, Nama: $participantNama)";
            logActivity($conn, $id_user, $aktivitas, "delete");
        }
        sendResponse(200, 'Participant deleted successfully');
    } else {
        sendResponse(500, 'Failed to delete participant: ' . $deleteStmt->error);
    }
}

// Helper function to send JSON response
function sendResponse($status, $message, $data = null)
{
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
