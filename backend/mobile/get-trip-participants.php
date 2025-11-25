<?php
/**
 * ============================================
 * UNTUK MENU DAFTAR PESERTA 
 * GET TRIP PARTICIPANTS API 
 * Mengambil data peserta berdasarkan id_trip
 * Hanya untuk trip dengan status 'available'
 * Join: paket_trips -> bookings -> participants -> users
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

// Ambil id_trip dari berbagai sumber (JSON body, POST, atau REQUEST)
$input = json_decode(file_get_contents('php://input'), true);
$id_trip = 0;

if (isset($input['id_trip'])) {
    $id_trip = intval($input['id_trip']);
} elseif (isset($_POST['id_trip'])) {
    $id_trip = intval($_POST['id_trip']);
} elseif (isset($_REQUEST['id_trip'])) {
    $id_trip = intval($_REQUEST['id_trip']);
}

// Validasi id_trip
if ($id_trip <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID Trip tidak valid',
        'received_id_trip' => $id_trip
    ]);
    exit;
}

// Query database
try {
    // CEK DULU: Apakah trip masih available?
    $checkQuery = "SELECT status FROM paket_trips WHERE id_trip = ? LIMIT 1";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    }
    
    $checkStmt->bind_param("i", $id_trip);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Trip tidak ditemukan',
            'received_id_trip' => $id_trip
        ]);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    
    $tripData = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    // Jika status bukan 'available', tolak request
    if ($tripData['status'] !== 'available') {
        echo json_encode([
            'success' => false,
            'message' => 'Trip ini sudah tidak aktif (status: ' . $tripData['status'] . ')',
            'trip_status' => $tripData['status'],
            'received_id_trip' => $id_trip
        ]);
        $conn->close();
        exit;
    }
    
    // Lanjutkan query peserta jika trip masih available
    $query = "
        SELECT 
            p.id_participant,
            p.nama,
            p.email,
            p.nik,
            p.no_wa,
            p.tempat_lahir,
            p.tanggal_lahir,
            p.alamat,
            b.id_booking,
            b.jumlah_orang,
            b.total_harga,
            b.status AS booking_status,
            b.tanggal_booking,
            u.id_user,
            u.username,
            u.foto_profil,
            pt.id_trip,
            pt.nama_gunung,
            pt.jenis_trip,
            pt.status AS trip_status
        FROM 
            participants p
        INNER JOIN 
            bookings b ON p.id_booking = b.id_booking
        INNER JOIN 
            paket_trips pt ON b.id_trip = pt.id_trip
        LEFT JOIN 
            users u ON b.id_user = u.id_user
        WHERE 
            pt.id_trip = ?
            AND pt.status = 'available'
        ORDER BY 
            b.tanggal_booking DESC, p.id_participant ASC
    ";
    
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_trip);

    if (!$stmt->execute()) {
        throw new Exception("Gagal mengeksekusi query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Cek apakah ada peserta
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tidak ada peserta untuk trip ini',
            'data' => [],
            'total_participants' => 0,
            'received_id_trip' => $id_trip
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Proses data peserta
    $participants = [];
    
    while ($row = $result->fetch_assoc()) {
        // Tentukan status berdasarkan booking_status
        $status = 'Belum Konfirmasi';
        if ($row['booking_status'] === 'confirmed' || $row['booking_status'] === 'paid') {
            $status = 'HADIR';
        } elseif ($row['booking_status'] === 'cancelled') {
            $status = 'IZIN';
        }
        
        // Format avatar URL
        $avatarUrl = 'dimasdwi'; // default avatar
        if (!empty($row['username'])) {
            $avatarUrl = strtolower($row['username']);
        }

        // Format foto profil URL jika ada
        $fotoProfil = '';
        if (!empty($row['foto_profil'])) {
            $fotoProfil = BASE_URL . '/' . ltrim($row['foto_profil'], '/');
        }
        
        $participants[] = [
            'id' => (string)$row['id_participant'],
            'nama' => $row['nama'] ?? '',
            'email' => $row['email'] ?? '',
            'status' => $status,
            'avatarUrl' => $avatarUrl,
            'nomorWa' => $row['no_wa'] ?? '',
            'nik' => $row['nik'] ?? '',
            'tempat_lahir' => $row['tempat_lahir'] ?? '',
            'tanggal_lahir' => $row['tanggal_lahir'] ?? '',
            'alamat' => $row['alamat'] ?? '',
            'id_booking' => intval($row['id_booking']),
            'booking_status' => $row['booking_status'] ?? '',
            'total_harga' => intval($row['total_harga'] ?? 0),
            'jumlah_orang' => intval($row['jumlah_orang'] ?? 0),
            'username' => $row['username'] ?? '',
            'foto_profil' => $fotoProfil
        ];
    }
    
    $stmt->close();

    // Format response
    $response = [
        'success' => true,
        'message' => 'Data peserta berhasil diambil',
        'data' => $participants,
        'total_participants' => count($participants),
        'id_trip' => $id_trip,
        'trip_status' => 'available'
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
