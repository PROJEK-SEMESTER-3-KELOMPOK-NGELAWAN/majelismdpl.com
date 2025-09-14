<?php
session_start();

$peserta = [
    [
        "id_participant" => 1,
        "nama" => "John Doe",
        "email" => "johndoe@example.com",
        "no_wa" => "08123456789",
        "alamat" => "Jl. Merdeka No. 123",
        "riwayat_penyakit" => "Diabetes",
        "no_wa_darurat" => "08123456788",
        "tgl_lahir" => "1990-01-01",
        "tmp_lahir" => "Jakarta",
        "nik" => "1234567890",
        "foto_ktp" => "path_to_image.jpg",
        "id_booking" => "123456789"
    ],
    [
        "id_participant" => 2,
        "nama" => "Jane Smith",
        "email" => "janesmith@example.com",
        "no_wa" => "08234567890",
        "alamat" => "Jl. Raya No. 45",
        "riwayat_penyakit" => "Hipertensi",
        "no_wa_darurat" => "08234567889",
        "tgl_lahir" => "1985-05-12",
        "tmp_lahir" => "Bandung",
        "nik" => "9876543210",
        "foto_ktp" => "path_to_image.jpg",
        "id_booking" => "987654321"
    ]
];

// Hapus peserta berdasarkan ID
if (isset($_POST['hapus_id'])) {
    $hapus_id = $_POST['hapus_id'];

    $peserta = array_filter($peserta, function($p) use ($hapus_id) {
        return $p['id_participant'] != $hapus_id;
    });
    $peserta = array_values($peserta);
    foreach ($peserta as $index => $p) {
        $peserta[$index]['id_participant'] = $index + 1;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Peserta - Admin | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">

</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3 d-flex flex-column" id="sidebar">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="mb-0">Menu</h2>
      <button class="btn-toggle" id="toggleBtn"><i class="bi bi-list"></i></button>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i><span> Dashboard</span></a></li>
      <li class="nav-item"><a href="trip.php" class="nav-link"><i class="bi bi-map-fill"></i><span> Trip</span></a></li>
      <li class="nav-item"><a href="peserta.php" class="nav-link active"><i class="bi bi-people-fill"></i><span> Peserta</span></a></li>
      <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i><span> Pembayaran</span></a></li>
      <li class="nav-item"><a href="galeri.php" class="nav-link"><i class="bi bi-images"></i><span> Galeri</span></a></li>
      <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span> Logout</span></a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1 p-4" id="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Daftar Peserta</h1>
    </div>

    <div class="table-container">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>No WA</th>
              <th>Alamat</th>
              <th>Riwayat Penyakit</th>
              <th>No. WA Darurat</th>
              <th>Tanggal Lahir</th>
              <th>Tempat Lahir</th>
              <th>NIK</th>
              <th>Foto KTP</th>
              <th>ID Booking</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($peserta)): ?>
              <tr>
                <td colspan="13" class="text-center text-muted">Belum ada peserta</td>
              </tr>
            <?php else: ?>
              <?php foreach ($peserta as $p): ?>
                <tr>
                  <td><?= $p['id_participant'] ?></td>
                  <td><?= $p['nama'] ?></td>
                  <td><?= $p['email'] ?></td>
                  <td><?= $p['no_wa'] ?></td>
                  <td><?= $p['alamat'] ?></td>
                  <td><?= $p['riwayat_penyakit'] ?></td>
                  <td><?= $p['no_wa_darurat'] ?></td>
                  <td><?= $p['tgl_lahir'] ?></td>
                  <td><?= $p['tmp_lahir'] ?></td>
                  <td><?= $p['nik'] ?></td>
                  <td><img src="<?= $p['foto_ktp'] ?>" alt="Foto KTP"></td>
                  <td><?= $p['id_booking'] ?></td>
                  <td>
                    <button class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $p['id_participant'] ?>">
                      <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $p['id_participant'] ?>" onclick="confirmDelete(<?= $p['id_participant'] ?>)">
                      <i class="bi bi-trash"></i> Hapus
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Peserta -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Peserta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <input type="hidden" id="editId">
          <div class="mb-3">
            <label for="editNama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="editNama" required>
          </div>
          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="editEmail" required>
          </div>
          <div class="mb-3">
            <label for="editNoWa" class="form-label">No. WA</label>
            <input type="text" class="form-control" id="editNoWa" required>
          </div>
          <div class="mb-3">
            <label for="editAlamat" class="form-label">Alamat</label>
            <input type="text" class="form-control" id="editAlamat" required>
          </div>
          <div class="mb-3">
            <label for="editRiwayatPenyakit" class="form-label">Riwayat Penyakit</label>
            <input type="text" class="form-control" id="editRiwayatPenyakit" required>
          </div>
          <div class="mb-3">
            <label for="editNoWaDarurat" class="form-label">No. WA Darurat</label>
            <input type="text" class="form-control" id="editNoWaDarurat" required>
          </div>
          <div class="mb-3">
            <label for="editTglLahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="editTglLahir" required>
          </div>
          <div class="mb-3">
            <label for="editTmpLahir" class="form-label">Tempat Lahir</label>
            <input type="text" class="form-control" id="editTmpLahir" required>
          </div>
          <div class="mb-3">
            <label for="editNIK" class="form-label">NIK</label>
            <input type="text" class="form-control" id="editNIK" required>
          </div>
          <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>

    // Sidebar toggle
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggleBtn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");
    });
    // Sidebar toggle end

  // Fungsi Edit
  document.querySelectorAll(".edit-btn").forEach((btn) => {
    btn.addEventListener("click", function() {
      const id = this.getAttribute("data-id");
      const row = this.closest("tr");

      document.getElementById("editId").value = id;
      document.getElementById("editNama").value = row.cells[1].innerText;
      document.getElementById("editEmail").value = row.cells[2].innerText;
      document.getElementById("editNoWa").value = row.cells[3].innerText;
      document.getElementById("editAlamat").value = row.cells[4].innerText;
      document.getElementById("editRiwayatPenyakit").value = row.cells[5].innerText;
      document.getElementById("editNoWaDarurat").value = row.cells[6].innerText;
      document.getElementById("editTglLahir").value = row.cells[7].innerText;
      document.getElementById("editTmpLahir").value = row.cells[8].innerText;
      document.getElementById("editNIK").value = row.cells[9].innerText;
    });
  });

  // Fungsi Hapus dengan konfirmasi
  function confirmDelete(id) {
    const confirmDelete = window.confirm("Apakah Anda yakin ingin menghapus peserta ini?");
    if (confirmDelete) {
      
      const row = document.querySelector(`button[data-id='${id}']`).closest("tr");
      row.remove(); 

      updateParticipantIds(); 
    }
  }

  // Fungsi untuk memperbarui ID peserta setelah penghapusan
  function updateParticipantIds() {
    const rows = document.querySelectorAll("table tbody tr");
    rows.forEach((row, index) => {
      const idCell = row.cells[0]; 
      idCell.innerText = index + 1; 
    });
  }

  // Form Edit Submit
  document.getElementById("editForm").addEventListener("submit", function(e) {
    e.preventDefault();

    // form edit peserta
    const id = document.getElementById("editId").value;
    const nama = document.getElementById("editNama").value;
    const email = document.getElementById("editEmail").value;
    const noWa = document.getElementById("editNoWa").value;
    const alamat = document.getElementById("editAlamat").value;
    const riwayatPenyakit = document.getElementById("editRiwayatPenyakit").value;
    const noWaDarurat = document.getElementById("editNoWaDarurat").value;
    const tglLahir = document.getElementById("editTglLahir").value;
    const tmpLahir = document.getElementById("editTmpLahir").value;
    const nik = document.getElementById("editNIK").value;

    // Update baris peserta di tabel
    const row = document.querySelector(`button[data-id='${id}']`).closest("tr");
    row.cells[1].innerText = nama;
    row.cells[2].innerText = email;
    row.cells[3].innerText = noWa;
    row.cells[4].innerText = alamat;
    row.cells[5].innerText = riwayatPenyakit;
    row.cells[6].innerText = noWaDarurat;
    row.cells[7].innerText = tglLahir;
    row.cells[8].innerText = tmpLahir;
    row.cells[9].innerText = nik;

    const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
    modal.hide();
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
