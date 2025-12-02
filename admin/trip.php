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
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Trip | Majelis MDPL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f6f0e8;
            color: #232323;
            font-family: "Poppins", Arial, sans-serif;
            min-height: 100vh;
            letter-spacing: 0.3px;
            margin: 0;
        }

        /* ========== SWEETALERT2 CUSTOM STYLING ========== */

        /* Popup Styling */
        .swal2-popup {
            border-radius: 15px !important;
            font-family: "Poppins", Arial, sans-serif !important;
        }

        .swal2-title {
            color: #a97c50 !important;
            font-family: "Poppins", Arial, sans-serif !important;
            font-weight: 600 !important;
        }

        .swal2-html-container {
            font-family: "Poppins", Arial, sans-serif !important;
            color: #495057 !important;
        }

        /* Button Styling - Confirm (Primary) */
        .swal2-confirm {
            background-color: #a97c50 !important;
            border-radius: 8px !important;
            font-family: "Poppins", Arial, sans-serif !important;
            font-weight: 500 !important;
            padding: 10px 24px !important;
            transition: all 0.3s ease !important;
            border: none !important;
        }

        .swal2-confirm:hover {
            background-color: #8b6332 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4) !important;
        }

        /* Button Styling - Cancel (Secondary) */
        .swal2-cancel {
            background-color: #6c757d !important;
            border-radius: 8px !important;
            font-family: "Poppins", Arial, sans-serif !important;
            font-weight: 500 !important;
            padding: 10px 24px !important;
            transition: all 0.3s ease !important;
            border: none !important;
        }

        .swal2-cancel:hover {
            background-color: #5a6268 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3) !important;
        }

        /* Icon Styling */
        .swal2-icon.swal2-warning {
            border-color: #a97c50 !important;
            color: #a97c50 !important;
        }

        .swal2-icon.swal2-success {
            border-color: #28a745 !important;
        }

        .swal2-icon.swal2-error {
            border-color: #dc3545 !important;
        }

        .swal2-icon.swal2-info {
            border-color: #17a2b8 !important;
        }

        /* Toast Styling */
        .colored-toast.swal2-icon-success {
            background-color: #a5dc86 !important;
        }

        .colored-toast.swal2-icon-error {
            background-color: #f27474 !important;
        }

        .colored-toast.swal2-icon-warning {
            background-color: #f8bb86 !important;
        }

        .colored-toast.swal2-icon-info {
            background-color: #3fc3ee !important;
        }

        .colored-toast .swal2-title {
            color: white !important;
            font-size: 16px !important;
        }

        /* ========== END SWEETALERT2 STYLING ========== */

        .text-brown {
            color: #a97c50 !important;
        }

        .bg-brown {
            background-color: #a97c50 !important;
            color: white;
        }

        .main {
            margin-left: 240px;
            min-height: 100vh;
            padding: 20px 25px;
            background: #f6f0e8;
            transition: margin-left 0.25s;
        }

        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 32px;
            padding-bottom: 28px;
        }

        .main-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #a97c50;
            margin-bottom: 0;
            letter-spacing: 1px;
        }

        .permission-badge {
            background-color: #28a745;
            color: white;
            font-size: 0.7em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 8px;
        }

        .btn-primary,
        .btn-success {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%) !important;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white !important;
        }

        .btn-primary:hover,
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
            background: linear-gradient(135deg, #8b6332 0%, #a97c50 100%) !important;
        }

        .modal-header {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%);
            color: white;
            border: none;
            border-radius: 0.7rem 0.7rem 0 0;
            padding: 20px 25px;
        }

        .modal-title {
            color: white !important;
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            height: 42px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #a97c50;
            box-shadow: 0 0 0 0.2rem rgba(169, 124, 80, 0.15);
            outline: none;
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
            background: rgba(99, 196, 148, 0.8);
        }

        .trip-status.sold {
            background: rgba(212, 141, 154, 0.8);
        }

        .trip-status.done {
            background: rgba(108, 117, 125, 0.85);
        }

        .trip-status .bi {
            font-size: 1.1em;
            font-weight: 800;
            margin-right: 2px;
        }

        .trip-card-body {
            padding: 18px 22px;
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
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
            margin: 0 auto 12px;
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
            background-color: #ffc107;
            color: #432f17;
        }

        .btn-edit:hover {
            background-color: #e0a800;
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
            color: #6c757d;
            margin-top: 24px;
        }

        .modal-footer .btn-group-equal {
            width: auto;
        }

        .modal-footer .btn-group-equal>.btn {
            flex-grow: 1;
            min-width: 100px;
        }

        .modal-dialog.modal-custom-trip {
            max-width: 650px;
        }

        .modal-footer .btn-group-equal {
            width: auto;
        }

        .modal-footer .btn-group-equal>.btn {
            flex-grow: 1;
            min-width: 100px;
        }
    </style>

</head>

<body>
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="main-header">
            <div>
                <h2>Kelola Trip</h2>
                <small class="text-muted">
                    <i class="bi bi-signpost-2"></i> Kelola data perjalanan pendakian.
                    <span class="permission-badge"><?= RoleHelper::getRoleDisplayName($user_role) ?></span>
                </small>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTripModal">
                <i class="bi bi-plus-circle"></i> Tambah Trip
            </button>
        </div>

        <div id="tripList" class="trip-card-list"></div>
        <div id="emptyState" class="empty-state">Belum ada trip.<br>Silakan tambahkan trip baru!</div>
    </main>

    <div class="modal fade" id="tambahTripModal" tabindex="-1" aria-labelledby="tambahTripModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-custom-trip modal-dialog-centered">
                <form class="modal-content" id="formTambahTrip" enctype="multipart/form-data">
                    <input type="hidden" id="tripIdInput" name="id_trip">
                    <input type="hidden" id="actionType" name="action" value="addTrip">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahTripModalLabel">
                            <i class="bi bi-compass me-2"></i>
                            <span id="modalTitleText">Tambah Trip Baru</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_gunung" class="form-label"><i class="bi bi-mountain me-1"></i> Nama Gunung</label>
                                    <input type="text" class="form-control" id="nama_gunung" name="nama_gunung" required placeholder="Cth: Gunung Bromo" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label"><i class="bi bi-calendar-event me-1"></i> Tanggal Trip</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" required />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slot" class="form-label"><i class="bi bi-person-check-fill me-1"></i> Slot</label>
                                    <input type="number" class="form-control" id="slot" name="slot" required min="1" placeholder="Kuota" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="durasi" class="form-label"><i class="bi bi-clock-fill me-1"></i> Durasi</label>
                                    <input type="text" class="form-control" id="durasi" name="durasi" placeholder="misal: 2 Hari 1 Malam" required />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_trip" class="form-label"><i class="bi bi-bag-fill me-1"></i> Jenis Trip</label>
                                    <select class="form-select" id="jenis_trip" name="jenis_trip" required>
                                        <option value="" selected disabled>Pilih...</option>
                                        <option value="Tektok">Tektok</option>
                                        <option value="Camp">Camp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga" class="form-label"><i class="bi bi-currency-dollar me-1"></i> Harga (Rp)</label>
                                    <input type="number" class="form-control" id="harga" name="harga" required min="0" placeholder="Biaya per orang" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="via_gunung" class="form-label"><i class="bi bi-signpost me-1"></i> Jalur / Via</label>
                                    <input type="text" class="form-control" id="via_gunung" name="via_gunung" required placeholder="Cth: Via Sembalun" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gambar" class="form-label"><i class="bi bi-image-fill me-1"></i> Upload Gambar</label>
                                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label"><i class="bi bi-info-circle-fill me-1"></i> Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="done">Done</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3" id="linkDriveContainerContent" style="opacity: 0; height: 0; overflow: hidden; transition: all 0.2s ease-in-out;">
                                    <label for="link_drive" class="form-label"><i class="bi bi-link-45deg me-1"></i> Link Google Drive (Opsional)</label>
                                    <input type="url" class="form-control" id="link_drive" name="link_drive" placeholder="Masukkan link Google Drive Album Foto Trip" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex ms-auto btn-group-equal" style="gap: 10px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Tutup
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveButton">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <!-- LOAD CONFIG.JS TERLEBIH DAHULU (CRITICAL!) -->
        <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- BARU LOAD TRIP.JS -->
        <script src="<?php echo getAssetsUrl('frontend/trip.js'); ?>"></script>
</body>

</html>