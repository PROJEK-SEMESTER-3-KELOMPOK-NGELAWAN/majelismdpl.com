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
<!-- SweetAlert2 -->
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
      padding: 0 10px 18px 10px;
      background: #f6f0e8;
      transition: margin-left 0.25s;
    }
  @media (max-width: 800px) {
    .main {
      margin-left: 0;
      padding-top: 90px;
      padding-left: 15px;
      padding-right: 15px;
    }
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
    border-collapse: collapse;
    font-size: 13px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 7px rgba(120, 77, 37, 0.1);
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
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Nama</th><th>Email</th><th>No WA</th><th>Alamat</th><th>Riwayat Penyakit</th><th>No WA Darurat</th><th>Tgl Lahir</th><th>Tmp Lahir</th><th>NIK</th><th>Foto KTP</th><th>ID Booking</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($participants)): ?>
        <tr>
          <td colspan="13" style="text-align:center;opacity:0.5;">Belum ada peserta</td>
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
          <span style="opacity:0.5;">Tidak ada</span>
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

<!-- Modal Edit Participant -->
<div class="modal fade" id="editParticipantModal" tabindex="-1" aria-labelledby="editParticipantLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="editParticipantForm">
      <div class="modal-header">
        <h5 class="modal-title" id="editParticipantLabel">Edit Peserta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form fields matching data -->
        <input type="hidden" name="id_participant" id="edit_id_participant" />
        <div class="mb-3">
          <label for="edit_nama" class="form-label">Nama</label>
          <input type="text" id="edit_nama" name="nama" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="edit_email" class="form-label">Email</label>
          <input type="email" id="edit_email" name="email" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="edit_no_wa" class="form-label">No WA</label>
          <input type="text" id="edit_no_wa" name="no_wa" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="edit_alamat" class="form-label">Alamat</label>
          <textarea id="edit_alamat" name="alamat" class="form-control"></textarea>
        </div>
        <div class="mb-3">
          <label for="edit_riwayat_penyakit" class="form-label">Riwayat Penyakit</label>
          <textarea id="edit_riwayat_penyakit" name="riwayat_penyakit" class="form-control"></textarea>
        </div>
        <div class="mb-3">
          <label for="edit_no_wa_darurat" class="form-label">No WA Darurat</label>
          <input type="text" id="edit_no_wa_darurat" name="no_wa_darurat" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="edit_tgl_lahir" class="form-label">Tanggal Lahir</label>
          <input type="date" id="edit_tgl_lahir" name="tgl_lahir" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="edit_tmp_lahir" class="form-label">Tempat Lahir</label>
          <input type="text" id="edit_tmp_lahir" name="tmp_lahir" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="edit_nik" class="form-label">NIK</label>
          <input type="text" id="edit_nik" name="nik" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="edit_foto_ktp" class="form-label">Foto KTP (URL)</label>
          <input type="text" id="edit_foto_ktp" name="foto_ktp" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="edit_id_booking" class="form-label">ID Booking</label>
          <input type="text" id="edit_id_booking" name="id_booking" class="form-control" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  let participants = <?=json_encode($participants)?>;
  const editModal = new bootstrap.Modal(document.getElementById('editParticipantModal'));
  const form = document.getElementById('editParticipantForm');

  function editParticipant(id) {
    const participant = participants.find(p => p.id_participant == id);
    if (!participant) return;

    // Isi form dengan data peserta
    form.id_participant.value = participant.id_participant;
    form.nama.value = participant.nama;
    form.email.value = participant.email;
    form.no_wa.value = participant.no_wa;
    form.alamat.value = participant.alamat;
    form.riwayat_penyakit.value = participant.riwayat_penyakit;
    form.no_wa_darurat.value = participant.no_wa_darurat;
    form.tgl_lahir.value = participant.tgl_lahir;
    form.tmp_lahir.value = participant.tmp_lahir;
    form.nik.value = participant.nik;
    form.foto_ktp.value = participant.foto_ktp;
    form.id_booking.value = participant.id_booking;

    editModal.show();
  }

  function deleteParticipant(id) {
    Swal.fire({
      title: 'Yakin hapus peserta?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        // Hapus peserta secara lokal (simulasi)
        participants = participants.filter(p => p.id_participant != id);
        // Refresh tabel dengan update peserta
        Swal.fire('Dihapus!', 'Peserta sudah dihapus.', 'success').then(() => {
          location.reload();
        });
      }
    });
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = parseInt(form.id_participant.value);
    let pIndex = participants.findIndex(p => p.id_participant == id);
    if (pIndex === -1) {
      Swal.fire('Error', 'Peserta tidak ditemukan.', 'error');
      return;
    }

    // Update data peserta di array
    participants[pIndex] = {
      id_participant: id,
      nama: form.nama.value,
      email: form.email.value,
      no_wa: form.no_wa.value,
      alamat: form.alamat.value,
      riwayat_penyakit: form.riwayat_penyakit.value,
      no_wa_darurat: form.no_wa_darurat.value,
      tgl_lahir: form.tgl_lahir.value,
      tmp_lahir: form.tmp_lahir.value,
      nik: form.nik.value,
      foto_ktp: form.foto_ktp.value,
      id_booking: form.id_booking.value
    };

    Swal.fire('Berhasil', 'Data peserta berhasil diperbarui', 'success').then(() => {
      editModal.hide();
      location.reload(); // refresh halaman agar tampil perubahan
    });
  });
</script>

</body>
</html>
