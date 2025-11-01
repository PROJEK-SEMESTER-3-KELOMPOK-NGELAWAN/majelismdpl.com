<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$respond = function (int $code, array $data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header_remove();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data);
    exit;
};

set_error_handler(function ($errno, $errstr) use ($respond) {
    $respond(500, ['error' => 'PHP Error: ' . $errstr]);
});

\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$mapStatus = function ($transaction_status, $fraud_status) {
    $fraud_status = $fraud_status ?? 'accept';
    if ($transaction_status === 'capture') return ($fraud_status === 'accept') ? 'paid' : 'pending';
    if ($transaction_status === 'settlement') return 'paid';
    if ($transaction_status === 'pending') return 'pending';
    if (in_array($transaction_status, ['deny', 'expire', 'cancel'])) return 'failed';
    return 'pending';
};

try {
    $order_id = $_GET['order_id'] ?? '';
    if ($order_id === '') $respond(400, ['error' => 'Order ID required']);

    $statusResponse = \Midtrans\Transaction::status($order_id); // [web:124][web:126]
    $transaction_status = $statusResponse->transaction_status ?? 'pending';
    $fraud_status = $statusResponse->fraud_status ?? 'accept';
    $gross_amount = $statusResponse->gross_amount ?? null;

    $status_pembayaran = $mapStatus($transaction_status, $fraud_status);

    $conn->begin_transaction();
    $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
    if (!$stmt) {
        $conn->rollback();
        $respond(500, ['error' => 'Database error: ' . $conn->error]);
    }
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($status_pembayaran === 'paid') {
        $stmtBooking = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=(SELECT id_booking FROM payments WHERE order_id=?)");
        if ($stmtBooking) {
            $stmtBooking->bind_param("s", $order_id);
            $stmtBooking->execute();
            $stmtBooking->close();
        }
    }
    $conn->commit();

    $respond(200, [
        'success' => true,
        'status' => $status_pembayaran,
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status,
        'order_id' => $order_id,
        'updated_rows' => $affected_rows,
        'gross_amount' => $gross_amount
    ]);
} catch (Exception $e) {
    if ($conn && $conn->errno) {
        $conn->rollback();
    }
    $respond(500, ['error' => 'Midtrans API Error', 'message' => $e->getMessage(), 'order_id' => $order_id ?? 'unknown']);
}
