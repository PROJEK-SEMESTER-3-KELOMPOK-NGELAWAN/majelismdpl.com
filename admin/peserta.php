<?php
require_once 'auth_check.php';
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

    /* START: New/Updated Table Styles */
    .table-responsive-custom {
      overflow-x: auto;
      margin-bottom: 20px;
    }

    table {
      min-width: 700px;
      width: 100%;
      border-spacing: 0 8px;
      font-size: 14px;
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
      padding: 14px 15px;
      font-weight: 700;
      letter-spacing: 0.7px;
      text-align: left;
      white-space: nowrap;
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
      padding: 13px 15px;
      vertical-align: middle;
      font-weight: 500;
      color: #432f17;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Hide specific columns on the main table view */
    .hide-col {
      display: none !important;
    }

    img.participant-photo {
      width: 60px;
      height: 40px;
      object-fit: cover;
      border-radius: 5px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    img.participant-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .btn-action-group {
      display: flex;
      gap: 10px;
    }

    .btn-edit,
    .btn-delete {
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

    /* END: New/Updated Table Styles */

    .loading {
      text-align: center;
      padding: 20px;
      color: #666;
    }

    /* Style untuk modal preview - UPDATED untuk perfect centering */
    #previewImageModal .modal-body {
      background-color: #f8f9fa;
      min-height: 500px;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 20px;
    }

    #previewImageModal .modal-body .position-relative {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      position: relative;
    }

    #previewImageFull {
      max-height: 70vh;
      max-width: 90%;
      width: auto;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
      object-fit: contain;
      transition: transform 0.3s ease;
    }

    #previewImageFull:hover {
      transform: scale(1.02);
    }

    /* Loading dan Error positioning */
    #imageLoadingSpinner {
      position: absolute !important;
      top: 50% !important;
      left: 50% !important;
      transform: translate(-50%, -50%) !important;
      z-index: 20;
    }

    #imageErrorMessage {
      position: absolute !important;
      top: 50% !important;
      left: 50% !important;
      transform: translate(-50%, -50%) !important;
      z-index: 20;
      margin: 0 !important;
      min-width: 200px;
      text-align: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      #previewImageModal .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
      }

      #previewImageModal .modal-body {
        min-height: 300px;
        padding: 15px;
      }

      #previewImageFull {
        max-height: 60vh;
      }

      #previewImageModal .modal-footer {
        flex-direction: column;
        gap: 10px;
      }

      #previewImageModal .participant-info {
        margin: 0 !important;
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <!-- Include Sidebar -->
  <?php include 'sidebar.php'; ?>


  <main class="main">
    <div class="daftar-heading">Daftar Peserta</div>

    <div class="search-container position-relative">
      <input type="text" id="searchInput" class="search-input" placeholder="Cari peserta..." />
      <i class="bi bi-search search-icon"></i>
    </div>

    <div class="table-responsive-custom">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No WA</th>
            <th>Alamat</th>
            <th>Riwayat Penyakit</th>
            <th>No WA Darurat</th>
            <th>Tgl Lahir</th>
            <th>Tmp Lahir</th>
            <th>NIK</th>
            <th>Foto KTP</th>
            <th>ID Booking</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="participantsTableBody">
          <tr>
            <td colspan="13" class="loading">Memuat data peserta...</td>
          </tr>
        </tbody>
      </table>
    </div> 

    <!-- Modal Edit Peserta-->
    <div class="modal fade" id="editPesertaModal" tabindex="-1" aria-labelledby="editPesertaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #a97c50; color: white;">
            <h5 class="modal-title" id="editPesertaModalLabel">
              <i class="bi bi-person-fill-gear"></i> Edit Data Peserta
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="formEditPeserta" enctype="multipart/form-data">
            <div class="modal-body">
              <!-- Input ID Participant (Hidden) -->
              <input type="hidden" name="id_participant" id="edit_id_participant" />

              <!-- Nama -->
              <div class="mb-3">
                <label class="form-label fw-bold">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama" id="edit_nama" required />
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" class="form-control" name="email" id="edit_email" required />
              </div>

              <!-- No WhatsApp -->
              <div class="mb-3">
                <label class="form-label fw-bold">No WhatsApp</label>
                <input type="text" class="form-control" name="no_wa" id="edit_no_wa" placeholder="08xxxxxxxxxx" required />
              </div>

              <!-- Alamat -->
              <div class="mb-3">
                <label class="form-label fw-bold">Alamat</label>
                <textarea class="form-control" name="alamat" id="edit_alamat" rows="3" required></textarea>
              </div>

              <!-- Riwayat Penyakit -->
              <div class="mb-3">
                <label class="form-label fw-bold">Riwayat Penyakit</label>
                <textarea class="form-control" name="riwayat_penyakit" id="edit_riwayat_penyakit" rows="2" placeholder="Kosongkan jika tidak ada"></textarea>
              </div>

              <!-- No WA Darurat -->
              <div class="mb-3">
                <label class="form-label fw-bold">No WA Darurat</label>
                <input type="text" class="form-control" name="no_wa_darurat" id="edit_no_wa_darurat" placeholder="08xxxxxxxxxx" />
              </div>

              <!-- Tanggal Lahir -->
              <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" id="edit_tanggal_lahir" required />
              </div>

              <!-- Tempat Lahir -->
              <div class="mb-3">
                <label class="form-label fw-bold">Tempat Lahir</label>
                <input type="text" class="form-control" name="tempat_lahir" id="edit_tempat_lahir" required />
              </div>

              <!-- NIK -->
              <div class="mb-3">
                <label class="form-label fw-bold">NIK</label>
                <input type="text" class="form-control" name="nik" id="edit_nik" maxlength="16" required />
              </div>

              <!-- Upload Foto KTP -->
              <div class="mb-3">
                <label class="form-label fw-bold">Foto KTP</label>
                <input type="file" class="form-control" name="foto_ktp" id="edit_foto_ktp" accept="image/*" />
                <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                <!-- Preview Foto KTP -->
                <div class="mt-2">
                  <img id="edit_preview_ktp" alt="Preview KTP" style="max-width: 200px; max-height: 150px; display: none; border-radius: 8px; border: 2px solid #a97c50;" />
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Batal
              </button>
              <button type="submit" class="btn btn-primary" style="background-color: #a97c50; border-color: #a97c50;">
                <i class="bi bi-check-circle"></i> Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <!-- Modal Preview Gambar KTP -->
    <div class="modal fade" id="previewImageModal" tabindex="-1" aria-labelledby="previewImageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #a97c50; color: white;">
            <h5 class="modal-title" id="previewImageModalLabel">
              <i class="bi bi-image"></i> Preview Foto KTP
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-0">
            <div class="position-relative">
              <img id="previewImageFull" src="" alt="Preview KTP" class="img-fluid" style="max-height: 70vh; width: auto;" />

              <!-- Loading spinner -->
              <div id="imageLoadingSpinner" class="position-absolute top-50 start-50 translate-middle" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <!-- Error message -->
              <div id="imageErrorMessage" class="alert alert-danger m-3" style="display: none;">
                <i class="bi bi-exclamation-triangle"></i> Gagal memuat gambar
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="participant-info me-auto">
              <small class="text-muted">
                <strong id="previewParticipantName">-</strong>
                <span class="mx-2">•</span>
                <span>NIK: <span id="previewParticipantNIK">-</span></span>
              </small>
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Tutup
            </button>
            <a id="previewDownloadBtn" href="#" class="btn btn-primary" download style="background-color: #a97c50; border-color: #a97c50;">
              <i class="bi bi-download"></i> Download
            </a>
          </div>
        </div>
      </div>
    </div>


  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../frontend/peserta.js"></script>
</body>

</html>