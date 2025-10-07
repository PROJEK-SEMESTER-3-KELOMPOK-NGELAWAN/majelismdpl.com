<?php
require_once 'koneksi.php';

// Cek apakah ada log lebih dari 30 hari sebelum dihapus
$delCheck = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
$delRow = $delCheck->fetch_assoc();

// Reset activity log jika ada yang >30 hari
$wasReset = false;
if ($delRow['cnt'] > 0) {
    $conn->query("DELETE FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
    $wasReset = true;
}

// Query log terbaru: LEFT JOIN supaya log user yang sudah dihapus/id_user NULL tetap muncul
$sql = "SELECT 
            activity_logs.aktivitas, 
            activity_logs.waktu, 
            activity_logs.status, 
            users.username 
        FROM activity_logs 
        LEFT JOIN users ON activity_logs.id_user = users.id_user 
        ORDER BY activity_logs.waktu DESC 
        LIMIT 10";
$res = $conn->query($sql);

// Fungsi untuk menyederhanakan teks aktivitas dengan tetap informatif
function simplifyActivity($text) {
    if (preg_match('/Menambahkan gambar gallery baru \(ID: (\d+), Nama File: (.+)\)/i', $text, $matches)) {
        return "Menambahkan gambar gallery baru dengan ID {$matches[1]}";
    }
    if (preg_match('/Menghapus gambar gallery \(ID: (\d+), Nama File: (.+)\)/i', $text, $matches)) {
        return "Menghapus gambar gallery dengan ID {$matches[1]}";
    }
    if (preg_match('/Trip "(.+)" diupdate/i', $text, $matches)) {
        return "Trip \"{$matches[1]}\" diupdate";
    }
    if (preg_match('/dihapus/i', $text, $matches)) {
        return $text; // biarkan log hapus tampil apa adanya
    }
    // Jika tidak cocok dengan pola, tampilkan teks asli dengan pembatasan panjang
    $maxLen = 80;
    if (strlen($text) > $maxLen) {
        return substr($text, 0, $maxLen) . '...';
    }
    return $text;
}

$rows = '';
$no = 1;
while ($row = $res->fetch_assoc()) {
    $status = strtolower($row['status']);
    $badgeClass = '';
    switch ($status) {
        case 'delete':
            $badgeClass = 'badge-delete'; break;
        case 'pending':
            $badgeClass = 'badge-pending'; break;
        case 'success':
            $badgeClass = 'badge-success'; break;
        case 'info':
            $badgeClass = 'badge-info'; break;
        case 'update':
            $badgeClass = 'badge-update'; break;
        default:
            $badgeClass = 'badge-info'; break;
    }

    $aktivitasSingkat = simplifyActivity($row['aktivitas']);

    // Jika username NULL (karena user dihapus), tampilkan tanda "- (dihapus)"
    $kolomPelaku = $row['username'] !== null ? htmlspecialchars($row['username']) : "<span style='color:#c00;font-style:italic'>(dihapus)</span>";

    $rows .= "<tr>
        <td>" . htmlspecialchars($no) . "</td>
        <td>" . htmlspecialchars($aktivitasSingkat) . "</td>
        <td>" . $kolomPelaku . "</td>
        <td>" . date('d/m/Y H:i', strtotime($row['waktu'])) . "</td>
        <td><span class='badge {$badgeClass}'>" . htmlspecialchars(ucfirst($status)) . "</span></td>
    </tr>";
    $no++;
}

if ($wasReset) {
    echo "<script>
    setTimeout(function() {
        Swal.fire({
            icon: 'success',
            title: 'Riwayat aktivitas sudah direset',
            text: 'Riwayat aktivitas otomatis direset karena sudah 30 hari.',
            confirmButtonText: 'OK'
        });
    }, 400);
    </script>";
}

// Tampilkan rows log jika script ini dipanggil via include atau ajax
echo $rows;
