<?php

/**
 * ============================================
 * GET USER TRIP HISTORY API
 * Menampilkan riwayat trip user yang sudah selesai
 * HANYA trip dengan booking_status = 'confirmed'
 * Dengan COUNT peserta yang AKURAT dari tabel participants
 * Format tanggal: DD-MM-YYYY
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
    // FIXED: Gunakan LEFT JOIN dengan participants untuk hitung jumlah peserta yang REAL
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
            pt.slot,
            COUNT(p.id_participant) AS total_participants
        FROM bookings b
        JOIN paket_trips pt ON b.id_trip = pt.id_trip
        LEFT JOIN participants p ON p.id_booking = b.id_booking
        WHERE b.id_user = ?
        AND b.status = 'confirmed'
        AND pt.status = 'done'
        GROUP BY b.id_booking, b.id_trip, b.status, pt.nama_gunung, pt.tanggal, pt.durasi, pt.gambar, pt.jenis_trip, pt.status, b.jumlah_orang, b.total_harga, pt.slot
        ORDER BY pt.tanggal DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("Gagal menyiapkan statement: " . $conn->error);

    $stmt->bind_param("i", $id_user);
    if (!$stmt->execute()) throw new Exception("Gagal mengeksekusi query: " . $stmt->error);

    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        // Generate random rating 4.0 - 5.0 (1 desimal)
        $rating = mt_rand(40, 50) / 10.0;
        
        // Gunakan total_participants dari COUNT JOIN
        $participants = intval($row['total_participants']);
        
        // Fallback: jika tidak ada data di tabel participants (COUNT = 0), gunakan jumlah_orang
        if ($participants == 0) {
            $participants = intval($row['jumlah_orang']);
        }
        
        // FIXED: Format tanggal dari YYYY-MM-DD menjadi DD-MM-YYYY
        $tanggal = $row['tanggal'] ?? '';
        $tanggalFormatted = '';
        if (!empty($tanggal)) {
            $date = DateTime::createFromFormat('Y-m-d', $tanggal);
            if ($date) {
                $tanggalFormatted = $date->format('d-m-Y');
            } else {
                $tanggalFormatted = $tanggal; // Fallback jika format tidak valid
            }
        }
        
        $history[] = [
            'id_booking' => intval($row['id_booking']),
            'id_trip' => intval($row['id_trip']),
            'mountain_name' => $row['nama_gunung'] ?? '',
            'date' => $tanggalFormatted,  // FIXED: Format DD-MM-YYYY
            'duration' => $row['durasi'] ?? '',
            'participants' => $participants,
            'status' => $row['booking_status'] ?? '',
            'trip_status' => $row['trip_status'] ?? '',
            'image_url' => !empty($row['gambar']) ? BASE_URL . '/' . ltrim($row['gambar'], '/') : '',
            'jenis_trip' => $row['jenis_trip'] ?? '',
            'total_harga' => intval($row['total_harga']),
            'slot' => intval($row['slot']),
            'rating' => $rating
        ];
    }
    $stmt->close();

    // Better message untuk empty history
    if (empty($history)) {
        echo json_encode([
            'success' => false,
            'message' => 'Belum ada riwayat trip yang selesai',
            'data' => []
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'History trip berhasil diambil',
            'count' => count($history),
            'data' => $history
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
