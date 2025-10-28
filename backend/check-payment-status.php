<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';
header('Content-Type: application/json');

// Nonaktifkan error HTML
ini_set('display_errors', 0);
error_reporting(0);

// Error handler
set_error_handler(function($errno, $errstr) {
    echo json_encode(['error' => 'PHP Error: ' . $errstr]);
    exit;
});

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

try {
    $order_id = $_GET['order_id'] ?? '';
    
    if (empty($order_id)) {
        echo json_encode(['error' => 'Order ID required']);
        exit;
    }

    // Get status dari Midtrans API
    $statusResponse = \Midtrans\Transaction::status($order_id);
    
    // ✅ SOLUSI FINAL: Akses langsung dengan null coalescing operator
    $transaction_status = $statusResponse->transaction_status;
    $fraud_status = 'accept'; // Default value
    
    // Cek fraud_status secara aman
    if (isset($statusResponse->fraud_status)) {
        $fraud_status = $statusResponse->fraud_status;
    }
    
    // Tentukan status pembayaran
    $status_pembayaran = 'pending';
    
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'accept') {
            $status_pembayaran = 'paid';
        }
    } else if ($transaction_status == 'settlement') {
        $status_pembayaran = 'paid';
    } else if ($transaction_status == 'pending') {
        $status_pembayaran = 'pending';
    } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
        $status_pembayaran = 'failed';
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
    if (!$stmt) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    // Update booking status jika paid
    if ($status_pembayaran == 'paid') {
        $stmtBooking = $conn->prepare("
            UPDATE bookings 
            SET status='confirmed' 
            WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)
        ");
        if ($stmtBooking) {
            $stmtBooking->bind_param("s", $order_id);
            $stmtBooking->execute();
            $stmtBooking->close();
        }
    }
    
    // Ambil gross_amount dengan cara yang aman
    $gross_amount = null;
    if (isset($statusResponse->gross_amount)) {
        $gross_amount = $statusResponse->gross_amount;
    }
    
    echo json_encode([
        'success' => true,
        'status' => $status_pembayaran,
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status,
        'order_id' => $order_id,
        'updated_rows' => $affected_rows,
        'gross_amount' => $gross_amount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Midtrans API Error',
        'message' => $e->getMessage(),
        'order_id' => isset($order_id) ? $order_id : 'unknown'
    ]);
}
?>
