<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once 'koneksi.php';

// Get action parameter
$action = $_GET['action'] ?? '';

// Only allow GET requests for dashboard data
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only GET requests are supported.'
    ]);
    exit();
}

try {
    switch ($action) {
        case 'getDashboardStats':
            getDashboardStats($conn);
            break;
            
        case 'getTripCountByStatus':
            getTripCountByStatus($conn);
            break;
            
        case 'getTripsOverview':
            getTripsOverview($conn);
            break;
            
        case 'getParticipantsStats':
            getParticipantsStats($conn);
            break;
            
        case 'getBookingsStats':
            getBookingsStats($conn);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action. Available actions: getDashboardStats, getTripCountByStatus, getTripsOverview, getParticipantsStats, getBookingsStats'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

// FUNCTION: Get complete dashboard statistics
function getDashboardStats($conn) {
    try {
        $stats = [];
        
        // Count trip aktif (available)
        $result = $conn->query("SELECT COUNT(*) as count FROM paket_trips WHERE status = 'available'");
        $row = $result->fetch_assoc();
        $stats['trip_aktif'] = (int)$row['count'];
        
        // Count trip selesai (sold)
        $result = $conn->query("SELECT COUNT(*) as count FROM paket_trips WHERE status = 'sold'");
        $row = $result->fetch_assoc();
        $stats['trip_selesai'] = (int)$row['count'];
        
        // Count total trips
        $result = $conn->query("SELECT COUNT(*) as count FROM paket_trips");
        $row = $result->fetch_assoc();
        $stats['total_trips'] = (int)$row['count'];
        
        // Count total peserta (jika ada tabel participants)
        $checkTable = $conn->query("SHOW TABLES LIKE 'participants'");
        if ($checkTable->num_rows > 0) {
            $result = $conn->query("SELECT COUNT(*) as count FROM participants");
            $row = $result->fetch_assoc();
            $stats['total_peserta'] = (int)$row['count'];
        } else {
            $stats['total_peserta'] = 0;
        }
        
        // Count pembayaran pending (jika ada tabel bookings)
        $checkBookings = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($checkBookings->num_rows > 0) {
            $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
            $row = $result->fetch_assoc();
            $stats['pembayaran_pending'] = (int)$row['count'];
            
            // Count total bookings
            $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
            $row = $result->fetch_assoc();
            $stats['total_bookings'] = (int)$row['count'];
        } else {
            $stats['pembayaran_pending'] = 0;
            $stats['total_bookings'] = 0;
        }
        
        // Add timestamp
        $stats['last_updated'] = date('Y-m-d H:i:s');
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'data' => [
                'trip_aktif' => 0,
                'trip_selesai' => 0,
                'total_trips' => 0,
                'total_peserta' => 0,
                'pembayaran_pending' => 0,
                'total_bookings' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ],
            'error' => $e->getMessage()
        ]);
    }
}

// FUNCTION: Get trip count by specific status
function getTripCountByStatus($conn) {
    $status = $_GET['status'] ?? '';
    
    if (empty($status)) {
        echo json_encode([
            'success' => false, 
            'count' => 0, 
            'error' => 'Status parameter is required'
        ]);
        return;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM paket_trips WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'count' => (int)$row['count'],
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'count' => 0,
            'status' => $status,
            'error' => $e->getMessage()
        ]);
    }
}

// FUNCTION: Get detailed trips overview
function getTripsOverview($conn) {
    try {
        $overview = [];
        
        // Group trips by status
        $result = $conn->query("
            SELECT 
                status, 
                COUNT(*) as count,
                AVG(harga) as avg_price,
                SUM(slot) as total_slots
            FROM paket_trips 
            GROUP BY status
        ");
        
        while ($row = $result->fetch_assoc()) {
            $overview[$row['status']] = [
                'count' => (int)$row['count'],
                'avg_price' => round((float)$row['avg_price'], 2),
                'total_slots' => (int)$row['total_slots']
            ];
        }
        
        // Get recent trips (last 5)
        $result = $conn->query("
            SELECT id_trip, nama_gunung, status, tanggal, harga 
            FROM paket_trips 
            ORDER BY id_trip DESC 
            LIMIT 5
        ");
        
        $recent_trips = [];
        while ($row = $result->fetch_assoc()) {
            $recent_trips[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'overview' => $overview,
                'recent_trips' => $recent_trips,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'data' => [],
            'error' => $e->getMessage()
        ]);
    }
}

// FUNCTION: Get participants statistics
function getParticipantsStats($conn) {
    try {
        $stats = [];
        
        // Check if participants table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'participants'");
        if ($checkTable->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'data' => [],
                'error' => 'Participants table not found'
            ]);
            return;
        }
        
        // Total participants
        $result = $conn->query("SELECT COUNT(*) as count FROM participants");
        $row = $result->fetch_assoc();
        $stats['total'] = (int)$row['count'];
        
        // Participants with bookings
        $result = $conn->query("
            SELECT COUNT(DISTINCT p.id_participant) as count 
            FROM participants p 
            INNER JOIN bookings b ON p.id_participant = b.id_participant
        ");
        $row = $result->fetch_assoc();
        $stats['with_bookings'] = (int)$row['count'];
        
        // Recent participants (last 7 days)
        $result = $conn->query("
            SELECT COUNT(*) as count 
            FROM participants 
            WHERE tanggal_lahir >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $row = $result->fetch_assoc();
        $stats['recent'] = (int)$row['count'];
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'data' => [],
            'error' => $e->getMessage()
        ]);
    }
}

// FUNCTION: Get bookings statistics
function getBookingsStats($conn) {
    try {
        $stats = [];
        
        // Check if bookings table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($checkTable->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'data' => [],
                'error' => 'Bookings table not found'
            ]);
            return;
        }
        
        // Group bookings by status
        $result = $conn->query("
            SELECT 
                status, 
                COUNT(*) as count,
                SUM(total_harga) as total_revenue
            FROM bookings 
            GROUP BY status
        ");
        
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = [
                'count' => (int)$row['count'],
                'revenue' => (float)$row['total_revenue']
            ];
        }
        
        // Total revenue
        $result = $conn->query("SELECT SUM(total_harga) as total FROM bookings");
        $row = $result->fetch_assoc();
        $stats['total_revenue'] = (float)$row['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'data' => [],
            'error' => $e->getMessage()
        ]);
    }
}
?>
