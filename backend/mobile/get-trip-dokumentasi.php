<?php
/**
 * ============================================
 * GET TRIP DOKUMENTASI API
 * Return SEMUA trip yang DONE (bukan hanya 1)
 * FIXED: Resolve GROUP BY error dengan MAX()
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

// Ambil id_user
$input = json_decode(file_get_contents('php://input'), true);
$id_user = 0;

if (isset($input['id_user'])) {
    $id_user = intval($input['id_user']);
} elseif (isset($_POST['id_user'])) {
    $id_user = intval($_POST['id_user']);
} elseif (isset($_REQUEST['id_user'])) {
    $id_user = intval($_REQUEST['id_user']);
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
    // Query untuk mendapatkan SEMUA trip yang DONE
    // Gunakan MAX() untuk kolom yang tidak di GROUP BY
    $query = "
        SELECT 
            pt.id_trip,
            MAX(tg.galery_name) as galery_name,
            MAX(tg.gdrive_link) as gdrive_link,
            pt.nama_gunung,
            pt.tanggal,
            pt.durasi,
            pt.status,
            pt.gambar,
            pt.jenis_trip,
            MAX(b.id_booking) as id_booking,
            MAX(b.status) as booking_status
        FROM bookings b
        INNER JOIN paket_trips pt ON b.id_trip = pt.id_trip
        LEFT JOIN trip_galleries tg ON pt.id_trip = tg.id_trip
        WHERE b.id_user = ?
        AND b.status = 'confirmed'
        AND pt.status = 'done'
        AND tg.gdrive_link IS NOT NULL
        AND tg.gdrive_link != ''
        GROUP BY pt.id_trip, pt.nama_gunung, pt.tanggal, pt.durasi, pt.status, pt.gambar, pt.jenis_trip
        ORDER BY pt.tanggal DESC
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

    // Cek apakah ada dokumentasi
    if ($result->num_rows === 0) {
        // Cek apakah user punya booking confirmed tapi trip belum done
        $checkQuery = "
            SELECT COUNT(*) as count
            FROM bookings b
            INNER JOIN paket_trips pt ON b.id_trip = pt.id_trip
            WHERE b.id_user = ?
            AND b.status = 'confirmed'
            AND pt.status != 'done'
        ";
        
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $id_user);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkData = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($checkData['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Trip Anda belum selesai. Dokumentasi akan tersedia setelah trip selesai.',
                'status' => 'trip_not_done'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Anda belum memiliki trip yang selesai atau dokumentasi belum tersedia.',
                'status' => 'no_completed_trip'
            ]);
        }
        
        $stmt->close();
        exit;
    }

    // Ambil SEMUA data dokumentasi (bukan hanya 1)
    $dokumentasiList = [];
    while ($row = $result->fetch_assoc()) {
        // Format URL gambar dengan benar
        $gambarUrl = '';
        if (!empty($row['gambar'])) {
            // Jika gambar sudah full URL, gunakan langsung
            if (strpos($row['gambar'], 'http') === 0) {
                $gambarUrl = $row['gambar'];
            } else {
                // Jika path relatif, tambahkan BASE_URL
                $gambarPath = ltrim($row['gambar'], '/');
                $gambarUrl = BASE_URL . '/' . $gambarPath;
            }
        }

        $dokumentasiList[] = [
            'id_trip' => intval($row['id_trip']),
            'id_booking' => intval($row['id_booking']),
            'galery_name' => $row['galery_name'] ?? '',
            'gdrive_link' => $row['gdrive_link'] ?? '',
            'nama_gunung' => $row['nama_gunung'] ?? '',
            'tanggal' => $row['tanggal'] ?? '',
            'durasi' => $row['durasi'] ?? '',
            'jenis_trip' => $row['jenis_trip'] ?? '',
            'status' => $row['status'] ?? '',
            'gambar' => $gambarUrl,
            'booking_status' => $row['booking_status'] ?? ''
        ];
    }

    $stmt->close();

    // Format response dengan array
    $response = [
        'success' => true,
        'message' => 'Data dokumentasi berhasil diambil',
        'count' => count($dokumentasiList),
        'data' => $dokumentasiList
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
