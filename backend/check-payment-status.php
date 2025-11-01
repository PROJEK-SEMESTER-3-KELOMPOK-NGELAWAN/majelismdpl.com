<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once 'koneksi.php';
header('Content-Type: application/json');

use App\Helpers\Mailer;

ini_set('display_errors', 0);
error_reporting(0);

set_error_handler(function ($errno, $errstr) {
    echo json_encode(['error' => 'PHP Error: ' . $errstr]);
    exit;
});

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

    $statusResponse = \Midtrans\Transaction::status($order_id);

    $transaction_status = $statusResponse->transaction_status ?? 'pending';
    $fraud_status = $statusResponse->fraud_status ?? 'accept';
    $gross_amount = $statusResponse->gross_amount ?? null;

    $status_pembayaran = 'pending';
    if ($transaction_status === 'capture') {
        if ($fraud_status === 'accept') $status_pembayaran = 'paid';
    } elseif ($transaction_status === 'settlement') {
        $status_pembayaran = 'paid';
    } elseif ($transaction_status === 'deny') {
        $status_pembayaran = 'failed';
    } elseif ($transaction_status === 'expire') {
        $status_pembayaran = 'expire';
    } elseif ($transaction_status === 'cancel') {
        $status_pembayaran = 'cancel';
    }

    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
    if (!$stmt) {
        $conn->rollback();
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    $id_booking = null;
    $id_payment = null;
    $sentPaid = 0;
    $g = $conn->prepare("SELECT p.id_booking, p.id_payment, p.sent_paid_email, u.email, u.username, t.nama_gunung, b.tanggal_booking, b.total_harga
                         FROM payments p
                         JOIN bookings b ON b.id_booking=p.id_booking
                         JOIN users u ON u.id_user=b.id_user
                         JOIN paket_trips t ON t.id_trip=b.id_trip
                         WHERE p.order_id=? LIMIT 1");
    $email = $username = $nama_gunung = '';
    $tanggal_booking = '';
    $total_harga = 0;
    if ($g) {
        $g->bind_param("s", $order_id);
        $g->execute();
        $g->bind_result($id_booking, $id_payment, $sentPaid, $email, $username, $nama_gunung, $tanggal_booking, $total_harga);
        $g->fetch();
        $g->close();
        $id_booking = intval($id_booking);
        $id_payment = intval($id_payment);
    }

    if ($id_booking) {
        if ($status_pembayaran === 'paid') {
            $ub = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=?");
            if ($ub) {
                $ub->bind_param("i", $id_booking);
                $ub->execute();
                $ub->close();
            }
            if ((int)$sentPaid === 0 && class_exists(Mailer::class)) {
                $html = Mailer::buildPaidTemplate([
                    'order_id' => $order_id,
                    'nama_gunung' => $nama_gunung,
                    'tanggal_booking' => date('d M Y', strtotime($tanggal_booking)),
                    'total_harga' => $total_harga,
                    'nama_user' => $username,
                    'payment_id' => $id_payment
                ]);
                Mailer::send($email, $username, 'Pembayaran Berhasil', $html, 'Pembayaran berhasil');
                $conn->query("UPDATE payments SET sent_paid_email=1 WHERE id_booking=" . $id_booking);
            }
        } elseif (in_array($status_pembayaran, ['failed', 'expire', 'cancel'])) {
            $ub = $conn->prepare("UPDATE bookings SET status='cancelled' WHERE id_booking=?");
            if ($ub) {
                $ub->bind_param("i", $id_booking);
                $ub->execute();
                $ub->close();
            }
            $dp = $conn->prepare("DELETE FROM participants WHERE id_booking=?");
            if ($dp) {
                $dp->bind_param("i", $id_booking);
                $dp->execute();
                $dp->close();
            }
            if (class_exists(Mailer::class)) {
                $subject = $status_pembayaran === 'expire' ? 'Pesanan Kedaluwarsa' : ($status_pembayaran === 'cancel' ? 'Pesanan Dibatalkan' : 'Pembayaran Gagal');
                $html = Mailer::buildFailedTemplate([
                    'order_id' => $order_id,
                    'nama_gunung' => $nama_gunung,
                    'tanggal_booking' => date('d M Y', strtotime($tanggal_booking)),
                    'total_harga' => $total_harga,
                    'nama_user' => $username
                ]);
                Mailer::send($email, $username, $subject, $html, $subject);
            }
        }
    }

    $conn->commit();

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
    if ($conn && $conn->errno) {
        $conn->rollback();
    }
    echo json_encode([
        'error' => 'Midtrans API Error',
        'message' => $e->getMessage(),
        'order_id' => isset($order_id) ? $order_id : 'unknown'
    ]);
}
