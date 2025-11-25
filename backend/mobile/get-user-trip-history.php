<?php

/**
 * ============================================
 * GET USER TRIP HISTORY API
 * Menampilkan riwayat trip user: trip paket_trips.status='done' YANG sudah dibooking user (join bookings)
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__, 3) . '/config.php';
require_once dirname(__FILE__, 2) . '/koneksi.php';

// Handle preflight OPTIONS request
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

// Ambil id_user dari JSON atau POST
$input = json_decode(file_get_contents('php://input'), true);
$id_user = 0;

if (isset($input['id_user'])) {
    $id_user = intval($input['id_user']);
} elseif (isset($_POST['id_user'])) {
    $id_user = intval($_POST['id_user']);
}

if ($id_user <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID User tidak valid',
        'received_id_user' => $id_user
    ]);
    exit;
}

try {
    $query = "
    SELECT 
        b.id_booking, 
        b.id_trip, 
        b.status AS booking_status,
        pt.nama_gunung, 
        pt.tanggal, 
        pt.durasi, 
        pt.gambar,
        pt.jenis_trip,
        pt.status AS trip_status,
        b.jumlah_orang,
        b.total_harga,
        pt.slot
    FROM bookings b
    JOIN paket_trips pt ON b.id_trip = pt.id_trip
    WHERE b.id_user = ?
    AND pt.status = 'done'
    ORDER BY pt.tanggal DESC
";


    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("Gagal menyiapkan statement: " . $conn->error);

    $stmt->bind_param("i", $id_user);
    if (!$stmt->execute()) throw new Exception("Gagal mengeksekusi query: " . $stmt->error);


    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $imageUrl = '';
        if (!empty($row['gambar'])) {
            $imageUrl = BASE_URL . '/' . ltrim($row['gambar'], '/');
        }
        $history[] = [
            'id_booking' => intval($row['id_booking']),
            'id_trip' => intval($row['id_trip']),
            'mountain_name' => $row['nama_gunung'] ?? '',
            'date' => $row['tanggal'] ?? '',
            'duration' => $row['durasi'] ?? '',
            'participants' => intval($row['jumlah_orang']),
            'status' => $row['booking_status'] ?? '',
            'trip_status' => $row['trip_status'] ?? '',
            'image_url' => !empty($row['gambar']) ? BASE_URL . '/' . ltrim($row['gambar'], '/') : '',
            'jenis_trip' => $row['jenis_trip'] ?? '',
            'total_harga' => intval($row['total_harga']),
            'slot' => intval($row['slot'])
        ];
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'History trip berhasil diambil',
        'data' => $history
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
