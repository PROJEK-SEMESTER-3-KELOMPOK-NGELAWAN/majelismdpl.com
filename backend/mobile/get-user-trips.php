<?php
/**
 * ============================================
 * UNTUK MENU DAFTAR PESERTA 
 * GET USER TRIPS API
 * Mengambil semua trip AKTIF yang pernah dibooking user
 * Hanya tampilkan trip dengan status 'available'
 * Format konsisten dengan get-profile.php
 * ============================================
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Include config dan koneksi dengan path relatif
require_once dirname(__FILE__, 3) . '/config.php';
require_once dirname(__FILE__, 2) . '/koneksi.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST'
    ]);
    exit;
}

// Ambil id_user dari berbagai sumber (JSON body, POST, atau REQUEST)
$input = json_decode(file_get_contents('php://input'), true);
$id_user = 0;

if (isset($input['id_user'])) {
    $id_user = intval($input['id_user']);
} elseif (isset($_POST['id_user'])) {
    $id_user = intval($_POST['id_user']);
} elseif (isset($_REQUEST['id_user'])) {
    $id_user = intval($_REQUEST['id_user']);
}

// Validasi id_user
if ($id_user <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID User tidak valid',
        'received_id_user' => $id_user
    ]);
    exit;
}

// Query database
try {
    // Query untuk mengambil trip AKTIF yang pernah dibooking user
    // PENTING: Tambahkan filter pt.status = 'available'
    $query = "
        SELECT 
            pt.id_trip,
            pt.nama_gunung,
            pt.jenis_trip,
            pt.tanggal,
            pt.durasi,
            pt.harga,
            pt.slot,
            pt.status,
            pt.gambar,
            b.id_booking,
            b.status AS booking_status,
            b.tanggal_booking,
            b.jumlah_orang,
            (SELECT COUNT(DISTINCT p2.id_participant) 
             FROM participants p2 
             INNER JOIN bookings b2 ON p2.id_booking = b2.id_booking 
             WHERE b2.id_trip = pt.id_trip 
             AND b2.status IN ('confirmed', 'paid')) as total_peserta
        FROM 
            bookings b
        INNER JOIN 
            paket_trips pt ON b.id_trip = pt.id_trip
        WHERE 
            b.id_user = ?
            AND b.status IN ('confirmed', 'paid', 'pending')
            AND pt.status = 'available'
        GROUP BY 
            pt.id_trip, b.id_booking
        ORDER BY 
            pt.tanggal DESC, b.tanggal_booking DESC
    ";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_user);

    if (!$stmt->execute()) {
        throw new Exception("Gagal mengeksekusi query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Cek apakah ada trip aktif
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tidak ada trip aktif yang ditemukan',
            'data' => [],
            'total_trips' => 0,
            'received_id_user' => $id_user
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Proses data trips
    $trips = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format gambar URL
        $gambarUrl = '';
        if (!empty($row['gambar'])) {
            // Jika sudah full URL (dimulai dengan http)
            if (strpos($row['gambar'], 'http') === 0) {
                $gambarUrl = $row['gambar'];
            } 
            // Jika hanya nama file atau path relatif
            else {
                // Hapus "img/" jika ada di awal
                $gambarFile = str_replace('img/', '', $row['gambar']);
                // Buat full URL
                $gambarUrl = BASE_URL . '/img/' . $gambarFile;
            }
        }

        $trips[] = [
            'id_trip' => intval($row['id_trip']),
            'nama_gunung' => $row['nama_gunung'] ?? '',
            'jenis_trip' => $row['jenis_trip'] ?? '',
            'tanggal' => $row['tanggal'] ?? '',
            'durasi' => $row['durasi'] ?? '',
            'harga' => intval($row['harga'] ?? 0),
            'slot' => intval($row['slot'] ?? 0),
            'status' => $row['status'] ?? '',
            'gambar_url' => $gambarUrl,
            'gambar_file' => $row['gambar'] ?? '',
            'id_booking' => intval($row['id_booking']),
            'booking_status' => $row['booking_status'] ?? '',
            'tanggal_booking' => $row['tanggal_booking'] ?? '',
            'jumlah_orang' => intval($row['jumlah_orang'] ?? 0),
            'total_peserta' => intval($row['total_peserta'] ?? 0)
        ];
    }
    
    $stmt->close();

    // Format response
    $response = [
        'success' => true,
        'message' => 'Data trip aktif berhasil diambil',
        'data' => $trips,
        'total_trips' => count($trips),
        'id_user' => $id_user,
        'base_url' => BASE_URL
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
