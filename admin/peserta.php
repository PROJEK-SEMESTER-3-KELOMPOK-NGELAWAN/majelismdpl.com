<?php
// Load peserta dari file JSON
$participantsFile = 'participants.json';
$participants = [];
if (file_exists($participantsFile)) {
    $json = file_get_contents($participantsFile);
    $participants = json_decode($json, true);
    if (!is_array($participants)) $participants = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Peserta | Majelis MDPL</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body {
    background: #f6f0e8;
    color: #232323;
    font-family: "Poppins", Arial, sans-serif;
    min-height: 100vh;
    letter-spacing: 0.3px;
    margin: 0;
  }
  .sidebar {
    background: #a97c50;
    min-height: 100vh;
    width: 240px;
    position: fixed;
    left: 0; top: 0; bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 34px;
    box-shadow: 2px 0 18px rgba(79,56,34,0.06);
    z-index: 100;
    transition: width 0.25s;
  }
  .sidebar img {
    width: 43px; height: 43px;
    border-radius: 11px;
    background: #fff7eb;
    border: 2px solid #d9b680;
    margin-bottom: 13px;
  }
  .logo-text {
    font-size: 1.13em;
    font-weight: 700;
    color: #fffbe4;
    margin-bottom: 30px;
    letter-spacing: 1.5px;
  }
  .sidebar-nav {
    flex: 1 1 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .nav-link {
    width: 90%;
    color: #fff;
    font-weight: 500;
    border-radius: 0.7rem;
    margin-bottom: 5px;
    padding: 13px 22px;
    display: flex;
    align-items: center;
    font-size: 16px;
    gap: 11px;
    letter-spacing: 0.7px;
    text-decoration: none;
    transition: background 0.22s, color 0.22s;
  }
  .nav-link.active,
  .nav-link:hover {
    background: #432f17;
    color: #ffd49c;
  }
  .logout {
    color: #fff;
    background: #c19c72;
    font-weight: 600;
    margin-bottom: 15px;
  }
  .logout:hover {
    background: #432f17;
    color: #fffbe4;
  }
  @media (max-width: 800px) {
    .sidebar {
      width: 100vw;
      height: 70px;
      flex-direction: row;
      padding-top: 0;
      padding-bottom: 0;
      bottom: unset;
      top: 0;
      justify-content: center;
      align-items: center;
      position: fixed;
      z-index: 100;
    }
    .sidebar img,
    .logo-text {
      display: none;
    }
    .sidebar-nav {
      flex-direction: row;
      align-items: center;
      justify-content: center;
      width: 100vw;
      height: 70px;
      margin: 0; padding: 0;
    }
    .nav-link,
    .logout {
      width: auto;
      min-width: 70px;
      font-size: 15px;
      margin: 0 3px;
      border-radius: 14px;
      padding: 8px 10px;
      justify-content: center;
    }
    .logout {
      order: 99;
      margin-left: 8px;
      margin-bottom: 0;
    }
  }
  .main {
    margin-left: 240px;
    min-height: 100vh;
    padding: 20px 25px;
    background: #f6f0e8;
  }
  @media (max-width: 800px) {
    .main {
      margin-left: 0;
      padding-top: 90px;
      padding-left: 15px;
      padding-right: 15px;
    }
  }
  .search-container {
    max-width: 450px;
    margin-bottom: 15px;
    position: relative;
  }
  .search-input {
    padding-left: 15px;
    padding-right: 45px;
    border-radius: 50px;
    border: 1.5px solid #a97c50;
    height: 38px;
    width: 100%;
    font-size: 14px;
    color: #432f17;
    transition: border-color 0.3s ease;
  }
  .search-input:focus {
    outline: none;
    border-color: #432f17;
    box-shadow: 0 0 8px rgba(67, 47, 23, 0.3);
  }
  .search-icon {
    position: absolute;
    right: 15px;
    top: 8px;
    color: #a97c50;
    pointer-events: none;
  }
  .daftar-heading {
    font-size: 1.4rem;
    font-weight: bold;
    color: #a97c50;
    margin: 32px 0 18px 0;
    letter-spacing: 1px;
  }
  table {
    width: 100%;
    border-spacing: 0 8px;
    font-size: 13px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    background-color: #fff;
  }
  thead {
    background-color: #a97c50;
  }
  thead th {
    color: #fff;
    padding: 12px 10px;
    font-weight: 700;
    letter-spacing: 0.7px;
    text-align: left;
  }
  tbody tr {
    border-bottom: 1px solid #f2dbc1;
  }
  tbody tr:last-child {
    border-bottom: none;
  }
  tbody tr:hover {
    background-color: #f9e8d0;
    color: #a97c50;
  }
  tbody td {
    padding: 11px 10px;
    vertical-align: middle;
    font-weight: 500;
    color: #432f17;
  }
  img.participant-photo {
    width: 60px;
    height: 40px;
    object-fit: cover;
    border-radius: 5px;
  }
  .btn-action-group {
    display: flex;
    gap: 10px;
  }
  .btn-edit, .btn-delete {
    padding: 5px 12px;
    font-size: 0.85em;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    color: white;
    transition: background-color 0.3s ease;
  }
  .btn-edit {
    background-color: #007bff;
  }
  .btn-edit:hover {
    background-color: #0056b3;
  }
  .btn-delete {
    background-color: #dc3545;
  }
  .btn-delete:hover {
    background-color: #a71d2a;
  }
</style>
</head>
<body>
<aside class="sidebar">
  <img src="../img/majelis.png" alt="Majelis MDPL" />
  <div class="logo-text">Majelis MDPL</div>
  <nav class="sidebar-nav">
    <a href="index.php" class="nav-link"><i class="bi bi-bar-chart"></i>Dashboard</a>
    <a href="trip.php" class="nav-link"><i class="bi bi-signpost-split"></i>Trip</a>
    <a href="peserta.php" class="nav-link active"><i class="bi bi-people"></i>Peserta</a>
    <a href="pembayaran.php" class="nav-link"><i class="bi bi-credit-card"></i>Pembayaran</a>
    <a href="galeri.php" class="nav-link"><i class="bi bi-images"></i>Galeri</a>
    <a href="logout.php" class="nav-link logout"><i class="bi bi-box-arrow-right"></i>Logout</a>
  </nav>
</aside>
<main class="main">
  <div class="daftar-heading">Daftar Peserta</div>
  <div class="search-container position-relative">
    <input type="text" id="searchInput" class="search-input" placeholder="Cari peserta..." />
    <i class="bi bi-search search-icon"></i>
  </div>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Nama</th><th>Email</th><th>No WA</th><th>Alamat</th><th>Riwayat Penyakit</th><th>No WA Darurat</th><th>Tgl Lahir</th><th>Tmp Lahir</th><th>NIK</th><th>Foto KTP</th><th>ID Booking</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody id="participantsTableBody">
      <?php if(empty($participants)): ?>
        <tr>
          <td colspan="13" class="text-center opacity-50">Belum ada peserta</td>
        </tr>
      <?php else: foreach($participants as $p): ?>
        <tr>
          <td><?=htmlspecialchars($p['id_participant'])?></td>
          <td><?=htmlspecialchars($p['nama'])?></td>
          <td><?=htmlspecialchars($p['email'])?></td>
          <td><?=htmlspecialchars($p['no_wa'])?></td>
          <td><?=htmlspecialchars($p['alamat'])?></td>
          <td><?=htmlspecialchars($p['riwayat_penyakit'])?></td>
          <td><?=htmlspecialchars($p['no_wa_darurat'])?></td>
          <td><?=htmlspecialchars($p['tgl_lahir'])?></td>
          <td><?=htmlspecialchars($p['tmp_lahir'])?></td>
          <td><?=htmlspecialchars($p['nik'])?></td>
          <td>
            <?php if(!empty($p['foto_ktp'])): ?>
              <img src="<?=htmlspecialchars($p['foto_ktp'])?>" alt="KTP" class="participant-photo" />
            <?php else: ?>
              <span class="opacity-50">Tidak ada</span>
            <?php endif;?>
          </td>
          <td><?=htmlspecialchars($p['id_booking'])?></td>
          <td>
            <div class="btn-action-group">
              <button class="btn-edit" onclick="editParticipant(<?=htmlspecialchars($p['id_participant'])?>)">Edit</button>
              <button class="btn-delete" onclick="deleteParticipant(<?=htmlspecialchars($p['id_participant'])?>)">Hapus</button>
            </div>
          </td>
        </tr>
      <?php endforeach; endif;?>
    </tbody>
  </table>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const searchInput = document.getElementById('searchInput');
  const tableBody = document.getElementById('participantsTableBody');

  searchInput.addEventListener('input', function(){
    const filter = searchInput.value.toLowerCase();
    const rows = tableBody.querySelectorAll('tr');

    rows.forEach(row => {
      const textContent = row.textContent.toLowerCase();
      if(textContent.includes(filter)){
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
</script>
</body>
</html>
