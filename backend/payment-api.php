<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1); error_reporting(E_ALL);

if (isset($_GET['status_id'])) {
    $id_booking = intval($_GET['status_id']);
    $status = '';
    if($id_booking > 0) {
        $stmt = $conn->prepare("SELECT status FROM bookings WHERE id_booking=?");
        $stmt->bind_param("i", $id_booking);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
    }
    echo json_encode(['status' => $status]);
    exit;
}

// Mendapatkan id booking
$id_booking = intval($_GET['booking'] ?? 0);
if ($id_booking <= 0) {
    echo json_encode(['error' => 'ID booking tidak valid']); exit();
}

$stmt = $conn->prepare("
    SELECT b.*, t.nama_gunung, t.harga, u.username, u.email
    FROM bookings b
    JOIN paket_trips t ON b.id_trip = t.id_trip
    JOIN users u ON b.id_user = u.id_user
    WHERE b.id_booking = ?
");
$stmt->bind_param("i", $id_booking);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo json_encode(['error' => 'Booking tidak ditemukan']); exit();
}

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Gunakan booking id yang sama tiap transaksi per booking
$order_id = 'ORDER-' . $id_booking . '-' . time();
$params = array(
    'transaction_details' => array(
        'order_id' => $order_id,
        'gross_amount' => intval($booking['total_harga'])
    ),
    'customer_details' => array(
        'first_name' => $booking['username'],
        'email'      => $booking['email']
    ),
    'item_details' => array([ 
        'id' => $booking['id_trip'],
        'price' => intval($booking['total_harga']),
        'quantity' => 1,
        'name' => $booking['nama_gunung']
    ])
);

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    $status = 'pending';
    $jenis = 'trip';
    $metode = 'midtrans';
    $sisa = 0;

    // Hindari insert duplicate
    $cek = $conn->prepare("SELECT 1 FROM payments WHERE id_booking=?");
    $cek->bind_param('i', $id_booking);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows == 0) {
        $stmtPay = $conn->prepare("INSERT INTO payments
            (id_booking, jumlah_bayar, tanggal, jenis_pembayaran, metode, sisa_bayar, status_pembayaran)
            VALUES (?, ?, CURDATE(), ?, ?, ?, ?)");
        $stmtPay->bind_param("iissis", $id_booking, $params['transaction_details']['gross_amount'], $jenis, $metode, $sisa, $status);
        $stmtPay->execute();
        $stmtPay->close();
    }
    $cek->close();

    echo json_encode(['snap_token' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
