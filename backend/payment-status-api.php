<?php
require_once 'koneksi.php';
header('Content-Type: application/json');

// Nonaktifkan error HTML
ini_set('display_errors', 0);
error_reporting(0);

// Error handler untuk return JSON
set_error_handler(function($errno, $errstr) {
    echo json_encode(['error' => 'PHP Error: ' . $errstr]);
    exit;
});

try {
    $id_booking = intval($_GET['id'] ?? 0);

    if ($id_booking <= 0) {
        echo json_encode(['error' => 'Invalid booking ID']);
        exit;
    }

    // Query dengan LEFT JOIN untuk handle case payment belum ada
    $stmt = $conn->prepare("
        SELECT 
            p.status_pembayaran, 
            p.order_id,
            b.status as booking_status,
            b.total_harga,
            b.jumlah_orang
        FROM bookings b
        LEFT JOIN payments p ON p.id_booking = b.id_booking
        WHERE b.id_booking = ?
    ");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        echo json_encode([
            'success' => true,
            'status' => $data['status_pembayaran'] ?? 'no_payment',
            'booking_status' => $data['booking_status'],
            'order_id' => $data['order_id'] ?? null,
            'total_harga' => $data['total_harga'],
            'jumlah_orang' => $data['jumlah_orang']
        ]);
    } else {
        echo json_encode([
            'error' => 'Booking not found',
            'status' => 'unknown'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
