<?php
// require_once 'koneksi.php'; // Biarkan ini tetap seperti sebelumnya

if (!defined('DB_KONEKSI_LOADED')) {
    require_once 'koneksi.php';
}

// Cek apakah ada log lebih dari 30 hari sebelum dihapus (Kode sebelumnya)
$delCheck = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
$delRow = $delCheck->fetch_assoc();

$wasReset = false;
if ($delRow['cnt'] > 0) {
    $conn->query("DELETE FROM activity_logs WHERE waktu < NOW() - INTERVAL 30 DAY");
    $wasReset = true;
}

// Query log terbaru (Kode sebelumnya)
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

// FUNGSI BARU UNTUK MENYEDERHANAKAN & MEMPERJELAS TEKS AKTIVITAS
function simplifyActivity($text, $status) {
    // 1. Logika untuk UPDATE yang detail (ASUMSI log backend sudah detail)
    if (strtolower($status) === 'update') {
        // Pola umum: Trip "NamaTrip" diupdate: [detail perubahan]
        if (preg_match('/Trip "(.+)" diupdate\s*:?\s*(.*)/i', $text, $matches)) {
            $tripName = htmlspecialchars($matches[1]);
            $details = trim($matches[2]);
            
            if (!empty($details)) {
                // Log sudah cukup detail, tampilkan
                return "Trip \"{$tripName}\" diupdate: <strong style='color:#007bff;'>{$details}</strong>";
            } else {
                // Jika details kosong, berikan pesan default
                 return "Trip \"{$tripName}\" diupdate. (Detail perubahan tidak dicatat)";
            }
        }
        
        // Pola umum: Peserta "NamaPeserta" diupdate: [detail perubahan]
        if (preg_match('/Peserta \(ID: (\d+), Nama: (.+)\) diupdate\s*:?\s*(.*)/i', $text, $matches)) {
            $pesertaName = htmlspecialchars($matches[2]);
            $details = trim($matches[3]);
            
            if (!empty($details)) {
                return "Peserta \"{$pesertaName}\" diupdate: <strong style='color:#007bff;'>{$details}</strong>";
            }
        }
        
        // Jika hanya 'diupdate', biarkan, tapi akan kurang informatif.
        if (strpos(strtolower($text), 'diupdate') !== false) {
             return htmlspecialchars($text);
        }
    }
    
    // 2. Logika untuk CREATE dan DELETE (mempertahankan kejelasan)
    if (preg_match('/Menambahkan gambar gallery baru \(ID: (\d+), Nama File: (.+)\)/i', $text, $matches)) {
        return "Menambahkan gambar gallery baru dengan ID {$matches[1]}";
    }
    if (preg_match('/Menghapus gambar gallery \(ID: (\d+), Nama File: (.+)\)/i', $text, $matches)) {
        return "Menghapus gambar gallery dengan ID {$matches[1]}";
    }
    
    // Log Delete atau Create (biarkan log tampil apa adanya)
    if (preg_match('/(dihapus|menambahkan|menghapus)/i', $text, $matches)) {
         return htmlspecialchars($text);
    }

    // 3. Logika Default
    $maxLen = 80;
    if (strlen($text) > $maxLen) {
        return htmlspecialchars(substr($text, 0, $maxLen) . '...');
    }
    return htmlspecialchars($text);
}

$rows = '';
$no = 1;
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $status = strtolower($row['status']);
        $badgeClass = '';
        switch ($status) {
            case 'delete':
                $badgeClass = 'badge-delete'; break;
            // ... case lainnya (PENDING, SUCCESS, INFO, UPDATE) ...
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

        // Meneruskan status ke fungsi simplifyActivity
        $aktivitasSingkat = simplifyActivity($row['aktivitas'], $status);
        
        $dataStatus = !empty($row['status']) ? htmlspecialchars($status) : 'info';

        $kolomPelaku = $row['username'] !== null 
            ? htmlspecialchars($row['username']) 
            : "<span style='color:#c00;font-style:italic'>(dihapus)</span>";

        $rows .= "<tr data-status=\"{$dataStatus}\">
            <td data-original-no=\"{$no}\">" . $no . "</td>
            <td>{$aktivitasSingkat}</td>
            <td>{$kolomPelaku}</td>
            <td>" . date('d/m/Y H:i', strtotime($row['waktu'])) . "</td>
            <td><span class='badge {$badgeClass}'>" . htmlspecialchars(ucfirst($status)) . "</span></td>
        </tr>";
        $no++;
    }
} else {
    // ... (Logika No Data) ...
}

