<?php
require_once 'koneksi.php';
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

try {
    $id_booking = intval($_GET['id'] ?? 0);

    if ($id_booking <= 0) {
        echo json_encode(['error' => 'Invalid booking ID']);
        exit;
    }

    // ✅ Query sesuai struktur database yang benar
    $stmt = $conn->prepare("
        SELECT 
            b.id_booking,
            b.tanggal_booking,
            b.total_harga,
            b.jumlah_orang,
            b.status as booking_status,
            t.id_trip,
            t.nama_gunung,
            t.jenis_trip,
            t.harga,
            t.tanggal,
            t.durasi,
            d.nama_lokasi,
            d.alamat,
            d.waktu_kumpul,
            d.link_map,
            d.include,
            d.exclude,
            d.syaratKetentuan,
            p.id_payment,
            p.status_pembayaran,
            p.order_id
        FROM bookings b
        JOIN paket_trips t ON b.id_trip = t.id_trip
        LEFT JOIN detail_trips d ON t.id_trip = d.id_trip
        LEFT JOIN payments p ON b.id_booking = p.id_booking
        WHERE b.id_booking = ?
    ");

    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }

    // ✅ Query participants
    $stmtPart = $conn->prepare("
        SELECT 
            nama, 
            email, 
            nik, 
            no_wa,
            tanggal_lahir,
            tempat_lahir,
            alamat,
            no_wa_darurat,
            riwayat_penyakit
        FROM participants 
        WHERE id_booking = ?
    ");
    $stmtPart->bind_param("i", $id_booking);
    $stmtPart->execute();
    $resultPart = $stmtPart->get_result();
    $participants = $resultPart->fetch_all(MYSQLI_ASSOC);
    $stmtPart->close();

    // ✅ Format data
    $booking['participants'] = $participants;
    $booking['tanggal_booking_formatted'] = date("d M Y", strtotime($booking['tanggal_booking']));
    $booking['tanggal_trip_formatted'] = date("d M Y", strtotime($booking['tanggal']));
    
    // Rename key untuk JavaScript
    $booking['syarat_ketentuan'] = $booking['syaratKetentuan'];
    unset($booking['syaratKetentuan']);

    echo json_encode($booking);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
