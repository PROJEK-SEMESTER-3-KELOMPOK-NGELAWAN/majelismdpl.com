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
    // Query untuk mengambil semua data pembayaran dengan join ke tabel terkait
    $query = "
        SELECT 
            p.id_payment,
            p.id_booking,
            p.jumlah_bayar,
            p.tanggal,
            p.jenis_pembayaran,
            p.metode,
            p.status_pembayaran,
            b.total_harga as total_trip,
            b.jumlah_orang,
            b.id_user,
            u.username,
            u.email,
            pt.nama_gunung,
            pt.jenis_trip
        FROM payments p
        INNER JOIN bookings b ON p.id_booking = b.id_booking
        INNER JOIN users u ON b.id_user = u.id_user
        INNER JOIN paket_trips pt ON b.id_trip = pt.id_trip
        ORDER BY p.tanggal DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Database query error: ' . $conn->error);
    }
    
    $payments = [];
    
    while ($row = $result->fetch_assoc()) {
        // Hitung sisa bayar
        $sisaBayar = $row['total_trip'] - $row['jumlah_bayar'];
        if ($sisaBayar < 0) $sisaBayar = 0;
        
        $payments[] = [
            'idpayment' => $row['id_payment'],
            'idbooking' => $row['id_booking'],
            'gunung' => $row['nama_gunung'],
            'jenis_trip' => $row['jenis_trip'],
            'jumlahbayar' => (int)$row['jumlah_bayar'],
            'tanggal' => $row['tanggal'],
            'jenispembayaran' => $row['jenis_pembayaran'],
            'metode' => $row['metode'],
            'statuspembayaran' => $row['status_pembayaran'],
            'total_trip' => (int)$row['total_trip'],
            'sisabayar' => $sisaBayar,
            'jumlah_orang' => (int)$row['jumlah_orang'],
            'username' => $row['username'],
            'email' => $row['email'],
            'id_user' => $row['id_user']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $payments
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
