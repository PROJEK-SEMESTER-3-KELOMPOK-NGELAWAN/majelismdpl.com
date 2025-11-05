<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
require_once dirname(__FILE__, 2) . '/config.php';
require_once 'koneksi.php';

header('Content-Type: application/json; charset=utf-8');

use App\Helpers\Mailer;

// ========== ERROR HANDLING ==========
ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

function sendJsonError($message, $detail = null, $statusCode = 400)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'detail' => $detail
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function sendJsonSuccess($data = [], $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_WARNING || $errno === E_NOTICE) {
        return true;
    }
    sendJsonError('PHP Error', $errstr, 500);
});

set_exception_handler(function ($e) {
    sendJsonError('System Error', $e->getMessage(), 500);
});

// ========== MIDTRANS CONFIG - SIMPLE ==========
try {
    if (!class_exists('\Midtrans\Config')) {
        throw new Exception('Midtrans SDK tidak ditemukan');
    }

    \Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // Check if PRODUCTION mode
    $isProduction = (defined('APP_MODE') && APP_MODE === 'PRODUCTION');

    if ($isProduction) {
        \Midtrans\Config::$curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30
        );
    } else {
        \Midtrans\Config::$curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true
        );
    }
} catch (Exception $e) {
    sendJsonError('Midtrans Config Error', $e->getMessage(), 500);
}

// ========== VERIFY DATABASE CONNECTION ==========
if (!$conn || $conn->connect_error) {
    sendJsonError('Database connection error', $conn->connect_error ?? 'Unknown', 500);
}

// ========== MAIN LOGIC ==========
try {
    $order_id = $_GET['order_id'] ?? '';
    if (empty($order_id)) {
        sendJsonError('Order ID required');
    }

    // Get status dari Midtrans
    try {
        @$statusResponse = \Midtrans\Transaction::status($order_id);
    } catch (\Exception $e) {
        $statusResponse = null;
    }

    if ($statusResponse) {
        $transaction_status = $statusResponse->transaction_status ?? 'pending';
        $fraud_status = $statusResponse->fraud_status ?? 'accept';
        $gross_amount = $statusResponse->gross_amount ?? null;
    } else {
        $transaction_status = 'pending';
        $fraud_status = 'accept';
        $gross_amount = null;
    }

    // Map status
    $status_pembayaran = 'pending';
    if ($transaction_status === 'capture') {
        $status_pembayaran = ($fraud_status === 'accept') ? 'paid' : 'pending';
    } elseif ($transaction_status === 'settlement') {
        $status_pembayaran = 'paid';
    } elseif ($transaction_status === 'deny') {
        $status_pembayaran = 'failed';
    } elseif ($transaction_status === 'expire') {
        $status_pembayaran = 'expire';
    } elseif ($transaction_status === 'cancel') {
        $status_pembayaran = 'cancel';
    }

    // Update database
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE payments SET status_pembayaran=? WHERE order_id=?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("ss", $status_pembayaran, $order_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    // Get booking & user info
    $id_booking = null;
    $id_payment = null;
    $sentPaid = 0;
    $email = $username = $nama_gunung = '';
    $tanggal_booking = '';
    $total_harga = 0;

    $g = $conn->prepare("SELECT p.id_booking, p.id_payment, p.sent_paid_email, u.email, u.username, t.nama_gunung, b.tanggal_booking, b.total_harga
                         FROM payments p
                         JOIN bookings b ON b.id_booking=p.id_booking
                         JOIN users u ON u.id_user=b.id_user
                         JOIN paket_trips t ON t.id_trip=b.id_trip
                         WHERE p.order_id=? LIMIT 1");
    if (!$g) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $g->bind_param("s", $order_id);
    if (!$g->execute()) {
        throw new Exception('Database execute error: ' . $g->error);
    }
    $g->bind_result($id_booking, $id_payment, $sentPaid, $email, $username, $nama_gunung, $tanggal_booking, $total_harga);
    $g->fetch();
    $g->close();

    $id_booking = intval($id_booking ?? 0);
    $id_payment = intval($id_payment ?? 0);

    // Handle paid status
    if ($id_booking) {
        if ($status_pembayaran === 'paid') {
            $ub = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id_booking=?");
            if ($ub) {
                $ub->bind_param("i", $id_booking);
                $ub->execute();
                $ub->close();
            }

            if ((int)$sentPaid === 0 && class_exists(Mailer::class)) {
                try {
                    $html = Mailer::buildPaidTemplate([
                        'order_id' => $order_id,
                        'nama_gunung' => $nama_gunung,
                        'tanggal_booking' => date('d M Y', strtotime($tanggal_booking)),
                        'total_harga' => $total_harga,
                        'nama_user' => $username,
                        'payment_id' => $id_payment
                    ]);
                    Mailer::send($email, $username, 'Pembayaran Berhasil', $html, 'Pembayaran berhasil');
                } catch (Exception $e) {
                    // Silent fail
                }
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
                try {
                    $subject = $status_pembayaran === 'expire' ? 'Pesanan Kedaluwarsa' : ($status_pembayaran === 'cancel' ? 'Pesanan Dibatalkan' : 'Pembayaran Gagal');
                    $html = Mailer::buildFailedTemplate([
                        'order_id' => $order_id,
                        'nama_gunung' => $nama_gunung,
                        'tanggal_booking' => date('d M Y', strtotime($tanggal_booking)),
                        'total_harga' => $total_harga,
                        'nama_user' => $username
                    ]);
                    Mailer::send($email, $username, $subject, $html, $subject);
                } catch (Exception $e) {
                    // Silent fail
                }
            }
        }
    }

    $conn->commit();

    sendJsonSuccess([
        'status' => $status_pembayaran,
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status,
        'order_id' => $order_id,
        'updated_rows' => $affected_rows,
        'gross_amount' => $gross_amount
    ]);
} catch (Exception $e) {
    if ($conn) $conn->rollback();
    sendJsonError('Check payment status failed', $e->getMessage(), 500);
}
?>
