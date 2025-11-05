<?php
require_once 'auth_check.php';
require_once '../config.php';

if (!class_exists('RoleHelper')) {
  class RoleHelper
  {
    public static function getRoleDisplayName($role)
    {
      return ucwords(str_replace('_', ' ', $role));
    }
  }
}
$user_role = $user_role ?? 'user';
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
      letter-spacing: .3px;
      margin: 0
    }

    .main {
      margin-left: 280px;
      min-height: 100vh;
      padding: 20px 25px;
      background: #f6f0e8;
      transition: margin-left .3s ease
    }

    @media (max-width:800px) {
      .main {
        margin-left: 0;
        padding-top: 90px;
        padding-left: 15px;
        padding-right: 15px
      }
    }

    .text-brown {
      color: #a97c50 !important
    }

    .bg-brown {
      background-color: #a97c50 !important;
      color: #fff
    }

    .main-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 32px;
      padding-bottom: 28px
    }

    .main-header h2 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #a97c50;
      margin-bottom: 0;
      letter-spacing: 1px
    }

    .permission-badge {
      background-color: #28a745;
      color: #fff;
      font-size: .7em;
      padding: 2px 6px;
      border-radius: 4px;
      margin-left: 8px
    }

    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
      transition: all .3s ease
    }

    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, .12)
    }

    .card-header {
      background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
      border-bottom: 2px solid #a97c50;
      border-radius: 15px 15px 0 0 !important;
      padding: 20px
    }

    .btn-primary {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 500;
      transition: all .3s ease
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(169, 124, 80, .4);
      background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%)
    }

    .btn-secondary {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      transition: all .3s ease
    }

    .table-responsive-custom {
      overflow-x: auto;
      margin-bottom: 0
    }

    table {
      width: 100%;
      border-radius: 1rem;
      overflow: hidden;
      border-collapse: collapse;
      font-size: 14px;
      border-spacing: 0
    }

    thead th {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: #fff;
      font-weight: 700;
      letter-spacing: .7px;
      font-size: 14px;
      padding: 15px;
      border: none;
      text-align: left;
      white-space: nowrap
    }

    tbody td {
      padding: 15px;
      text-align: left;
      font-weight: 500;
      color: #432f17;
      border-bottom: 1px solid #f2dbc1;
      vertical-align: middle;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis
    }

    tbody tr:last-child td {
      border-bottom: none
    }

    tbody tr:hover td {
      background-color: #f9e8d0;
      color: #a97c50
    }

    img.participant-photo {
      width: 60px;
      height: 40px;
      object-fit: cover;
      border-radius: 5px;
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease
    }

    img.participant-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 2px 8px rgba(0, 0, 0, .3)
    }

    .btn-action-group {
      display: flex;
      gap: 8px;
      justify-content: center;
      align-items: center
    }

    .btn-edit,
    .btn-delete {
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: all .2s ease-in-out;
      padding: 0;
      font-size: 1.2rem
    }

    .btn-edit {
      background-color: #ffc107;
      color: #432f17
    }

    .btn-delete {
      background-color: #dc3545;
      color: #fff
    }

    .btn-edit:hover {
      background-color: #e0a800;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, .2)
    }

    .btn-delete:hover {
      background-color: #c82333;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, .2)
    }

    .search-container {
      max-width: 450px;
      position: relative
    }

    .search-input {
      padding-left: 15px;
      padding-right: 40px;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      height: 42px;
      width: 100%;
      font-size: .95rem;
      color: #495057;
      transition: all .3s ease
    }

    .search-input:focus {
      outline: none;
      border-color: #a97c50;
      box-shadow: 0 0 0 .2rem rgba(169, 124, 80, .15);
      transform: translateY(-1px)
    }

    .search-icon {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      pointer-events: none;
      font-size: 1.1rem
    }

    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: .5rem;
      font-size: .9rem
    }

    .form-control,
    .form-select {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 10px 12px;
      font-size: .95rem;
      transition: all .3s ease;
      height: 42px
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #a97c50;
      box-shadow: 0 0 0 .2rem rgba(169, 124, 80, .15);
      transform: translateY(-1px)
    }

    .btn-close-white {
      filter: invert(1);
      opacity: .8
    }

    .btn-close-white:hover {
      opacity: 1;
      transform: scale(1.1)
    }

    #previewImageModal .modal-header {
      background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
      color: #fff;
      border: none
    }

    #imageLoadingSpinner .spinner-border {
      color: #a97c50 !important
    }

    #previewImageFull {
      max-height: 70vh;
      max-width: 90%;
      width: auto;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .15);
      object-fit: contain;
      transition: transform .3s ease
    }

    #previewImageModal .modal-body {
      background-color: #f8f9fa;
      min-height: 350px;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 20px
    }
  </style>
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <main class="main">
    <div class="main-header">
      <div>
        <h2>Kelola Data Peserta</h2>
        <small class="text-muted"><i class="bi bi-people-fill"></i> Daftar semua data pendaftar trip.
          <span class="permission-badge"><?= RoleHelper::getRoleDisplayName($user_role) ?></span>
        </small>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-brown"><i class="bi bi-table"></i> Data Peserta</h5>
        </div>
      </div>
      <div class="card-body p-4">
        <div class="d-flex flex-wrap gap-3 mb-3">
          <div class="search-container position-relative flex-grow-1">
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, email, atau ID Booking..." />
            <i class="bi bi-search search-icon"></i>
          </div>
          <div class="d-flex gap-2 align-items-center" style="min-width:250px;">
            <div class="flex-grow-1">
              <label for="filterGunung" class="form-label visually-hidden">Filter Gunung</label>
              <select id="filterGunung" class="form-select search-input">
                <option value="">Semua Gunung / Trip</option>
              </select>
            </div>
            <button id="printPdfBtn" type="button" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> Cetak</button>
          </div>
        </div>

        <div class="table-responsive-custom">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No WA</th>
                <th class="hide-col">Alamat</th>
                <th class="hide-col">Riwayat Penyakit</th>
                <th class="hide-col">No WA Darurat</th>
                <th class="hide-col">Tgl Lahir</th>
                <th class="hide-col">Tmp Lahir</th>
                <th class="hide-col">NIK</th>
                <th>Foto KTP</th>
                <th>ID Booking</th>
                <th>Trip</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="participantsTableBody">
              <tr>
                <td colspan="14" class="text-center">Memuat data peserta...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Edit Peserta -->
    <div class="modal fade" id="editPesertaModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-brown text-white">
            <h5 class="modal-title"><i class="bi bi-person-fill-gear"></i> Edit Data Peserta</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="formEditPeserta" enctype="multipart/form-data">
            <div class="modal-body">
              <input type="hidden" name="id_participant" id="edit_id_participant" />
              <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-person me-1"></i> Nama Lengkap</label><input type="text" class="form-control" name="nama" id="edit_nama" required /></div>
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-envelope me-1"></i> Email</label><input type="email" class="form-control" name="email" id="edit_email" required /></div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-whatsapp me-1"></i> No WhatsApp</label><input type="text" class="form-control" name="no_wa" id="edit_no_wa" required /></div>
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-telephone-inbound me-1"></i> No WA Darurat</label><input type="text" class="form-control" name="no_wa_darurat" id="edit_no_wa_darurat" /></div>
              </div>
              <div class="mb-3"><label class="form-label"><i class="bi bi-geo-alt me-1"></i> Alamat</label><textarea class="form-control" name="alamat" id="edit_alamat" rows="2" required></textarea></div>
              <div class="mb-3"><label class="form-label"><i class="bi bi-heart-pulse me-1"></i> Riwayat Penyakit</label><textarea class="form-control" name="riwayat_penyakit" id="edit_riwayat_penyakit" rows="2"></textarea></div>
              <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-calendar me-1"></i> Tanggal Lahir</label><input type="date" class="form-control" name="tanggal_lahir" id="edit_tanggal_lahir" required /></div>
                <div class="col-md-6 mb-3"><label class="form-label"><i class="bi bi-house-door me-1"></i> Tempat Lahir</label><input type="text" class="form-control" name="tempat_lahir" id="edit_tempat_lahir" required /></div>
              </div>
              <div class="mb-3"><label class="form-label"><i class="bi bi-person-badge me-1"></i> NIK</label><input type="text" class="form-control" name="nik" id="edit_nik" maxlength="16" required /></div>
              <div class="mb-3">
                <label class="form-label fw-bold"><i class="bi bi-card-image me-1"></i> Foto KTP</label>
                <input type="file" class="form-control" name="foto_ktp" id="edit_foto_ktp" accept="image/*" />
                <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                <div class="mt-2"><img id="edit_preview_ktp" alt="Preview KTP" style="max-width:200px;max-height:150px;display:none;border-radius:8px;border:2px solid #a97c50;" /></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Batal</button>
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Preview Image -->
    <div class="modal fade" id="previewImageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-brown text-white">
            <h5 class="modal-title"><i class="bi bi-image"></i> Preview Foto KTP</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center p-0">
            <div class="position-relative">
              <img id="previewImageFull" src="" alt="Preview KTP" class="img-fluid" style="max-height:70vh;width:auto;" />
              <div id="imageLoadingSpinner" class="position-absolute top-50 start-50 translate-middle" style="display:none;">
                <div class="spinner-border text-brown" role="status"><span class="visually-hidden">Loading...</span></div>
              </div>
              <div id="imageErrorMessage" class="alert alert-danger m-3" style="display:none;">
                <i class="bi bi-exclamation-triangle"></i> Gagal memuat gambar
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="participant-info me-auto">
              <small class="text-muted"><strong id="previewParticipantName">-</strong><span class="mx-2">•</span>NIK: <span id="previewParticipantNIK">-</span></small>
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Tutup</button>
            <a id="previewDownloadBtn" href="#" class="btn btn-primary" download><i class="bi bi-download"></i> Download</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- LOAD CONFIG.JS TERLEBIH DAHULU (CRITICAL!) -->
  <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>

  <!-- BARU LOAD PESERTA.JS -->
  <script src="<?php echo getAssetsUrl('frontend/peserta.js'); ?>"></script>
</body>

</html>