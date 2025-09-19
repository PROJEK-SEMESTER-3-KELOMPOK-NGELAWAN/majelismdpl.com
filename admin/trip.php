<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Trip | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f6f0e8;
      color: #232323;
      font-family: "Poppins", Arial, sans-serif;
      min-height: 100vh;
      letter-spacing: 0.3px;
    }

    .sidebar {
      background: #a97c50;
      min-height: 100vh;
      width: 240px;
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 34px;
      box-shadow: 2px 0 18px rgba(79, 56, 34, 0.06);
      z-index: 100;
      transition: width 0.25s;
    }

    .sidebar img {
      width: 43px;
      height: 43px;
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
        margin: 0;
        padding: 0;
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
      transition: margin-left 0.25s;
    }

    @media (max-width: 800px) {
      .main {
        margin-left: 0;
        padding-top: 85px;
      }
    }

    .daftar-heading {
      font-size: 1.4rem;
      font-weight: bold;
      color: #a97c50;
      margin: 32px 0 18px 0;
      letter-spacing: 1px;
    }

    .trip-card-list {
      display: flex;
      flex-wrap: wrap;
      gap: 32px;
      margin: 0 auto;
      justify-content: flex-start;
      max-width: calc(350px * 3 + 32px * 2);
    }

    .trip-card {
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 4px 18px rgba(60, 44, 33, 0.09);
      overflow: hidden;
      width: calc((100% - 64px) / 3);
      min-width: 280px;
      border: none;
      transition: box-shadow 0.15s, transform 0.11s;
      position: relative;
      padding-bottom: 13px;
      margin-bottom: 20px;
      text-align: center;
      display: flex;
      flex-direction: column;
    }

    .trip-card:hover {
      box-shadow: 0 8px 36px 0 rgba(60, 44, 33, 0.14);
      transform: translateY(-3px) scale(1.01);
    }

    .trip-thumb {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 18px 18px 0 0;
    }

    .trip-status {
      position: absolute;
      top: 15px;
      left: 18px;
      z-index: 3;
      padding: 3px 12px;
      border-radius: 12px;
      font-size: 0.75em;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .trip-status.available {
      background: rgba(99, 196, 148, 0.6);
    }

    .trip-status.sold {
      background: rgba(212, 141, 154, 0.6);
    }

    .trip-status .bi {
      font-size: 1.1em;
      font-weight: 800;
      margin-right: 2px;
    }

    .trip-card-body {
      padding: 18px 22px 18px 22px; 
      position: relative;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start; 
    }

    .trip-meta {
      font-size: 0.85em;
      color: #696969;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      padding: 0;
    }

    .trip-meta span {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .trip-title {
      font-size: 1.15em;
      font-weight: 700;
      color: #232323;
      margin-bottom: 10px;
      letter-spacing: 0.15px;
      text-align: center;
    }

    .trip-type {
      background: #d9d9db;
      color: #2c2b2b;
      border-radius: 12px;
      font-size: 0.85em;
      font-weight: 700;
      display: inline-flex;
      padding: 4px 16px;
      margin: 0 auto 12px auto;
      justify-content: center;
      align-items: center;
      max-width: max-content;
    }

    .trip-rating {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 0.9em;
      margin-bottom: 2px;
      color: #ffbf47;
      font-weight: 600;
      justify-content: center;
      flex-grow: 0;
    }

    .trip-rating i {
      font-size: 1.08em;
    }

    .trip-rating .sub {
      color: #3d3d3d;
      font-size: 0.95em;
      margin-left: 6px;
      font-weight: 400;
    }

    .trip-via {
      font-size: 0.9em;
      color: #595959;
      margin-bottom: 12px;
      text-align: center;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 6px;
    }

    .trip-via .bi {
      font-size: 1.01em;
    }

    .trip-price {
      font-size: 1.2em;
      font-weight: 700;
      color: #2ea564;
      margin-top: auto; 
      text-align: center;
      letter-spacing: 1.5px;
    }

    .btn-action-group {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
    }

    .btn-action {
      padding: 5px 12px;
      font-size: 0.85em;
      border-radius: 8px;
      font-weight: 600;
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

    .btn-detail {
      background-color: #28a745;
    }

    .btn-detail:hover {
      background-color: #1a6e2a;
    }

    .empty-state {
      text-align: center;
      margin: 55px 0 70px 0;
      color: #c7b597;
      font-size: 1.23em;
      font-weight: 500;
      opacity: 0.8;
    }

    @media (max-width: 1100px) {
      .trip-card-list {
        justify-content: center;
        max-width: 100%;
      }

      .trip-card {
        width: 45%;
        min-width: unset;
      }
    }

    @media (max-width: 700px) {
      .trip-card {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <aside class="sidebar">
    <img src="../img/majelis.png" alt="Majelis MDPL" />
    <div class="logo-text">Majelis MDPL</div>
    <nav class="sidebar-nav">
      <a href="index.php" class="nav-link"><i class="bi bi-bar-chart"></i>Dashboard</a>
      <a href="trip.php" class="nav-link active"><i class="bi bi-signpost-split"></i>Trip</a>
      <a href="peserta.php" class="nav-link"><i class="bi bi-people"></i>Peserta</a>
      <a href="pembayaran.php" class="nav-link"><i class="bi bi-credit-card"></i>Pembayaran</a>
      <a href="galeri.php" class="nav-link"><i class="bi bi-images"></i>Galeri</a>
      <a href="logout.php" class="nav-link logout"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </nav>
  </aside>
  <main class="main">
    <div class="d-flex justify-content-between align-items-center">
      <div class="daftar-heading">Daftar Trip</div>
      <button type="button" class="btn btn-success px-4 py-2" data-bs-toggle="modal" data-bs-target="#tambahTripModal">
        <i class="bi bi-plus-circle"></i> Tambah Trip
      </button>
    </div>
    <div id="tripList" class="trip-card-list"></div>
    <div id="emptyState" class="empty-state">Belum ada trip.<br>Silakan tambahkan trip baru!</div>
  </main>
  <div class="modal fade" id="tambahTripModal" tabindex="-1" aria-labelledby="tambahTripModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formTambahTrip" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="tambahTripModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Trip</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label fw-bold">Nama Gunung</label>
            <input type="text" class="form-control" name="nama_gunung" required />
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" required />
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Slot</label>
            <input type="number" class="form-control" name="slot" required />
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Durasi</label>
            <input type="text" class="form-control" name="durasi" placeholder="misal: 2 Hari 1 Malam" required />
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Jenis Trip</label>
            <select class="form-select" name="jenis_trip" required>
              <option value="" selected disabled>Pilih...</option>
              <option value="Tektok">Tektok</option>
              <option value="Camp">Camp</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Harga (Rp)</label>
            <input type="number" class="form-control" name="harga" required />
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Jalur / Via</label>
            <input type="text" class="form-control" name="via_gunung" required />
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Status</label>
            <select class="form-select" name="status" required>
              <option value="available">Available</option>
              <option value="sold">Sold</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label fw-bold">Upload Gambar</label>
            <input type="file" class="form-control" name="gambar" accept="image/*" />
            <img id="preview" alt="Preview Gambar" style="max-width: 150px; margin-top: 10px; display:none; border-radius: 8px;" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success px-4">Simpan</button>
        </div>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../frontend/trip.js"></script>

  <!-- UNTUK POP UP -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>