<?php
require_once 'auth_check.php';
require_once '../config.php';

// Asumsi RoleHelper sudah dimuat di auth_check.php atau file lain yang diperlukan.
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
    <title>Galeri | Majelis MDPL</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />


    <style>
        /* --- CSS KONSISTENSI UTAMA DARI MASTER ADMIN --- */
        body {
            background: #f6f0e8;
            color: #232323;
            font-family: "Poppins", Arial, sans-serif;
            min-height: 100vh;
            letter-spacing: 0.3px;
            margin: 0;
        }


        .text-brown {
            color: #a97c50 !important;
        }


        .bg-brown {
            background-color: #a97c50 !important;
            color: white;
        }


        /* --- Sidebar Styling (Dipertahankan) --- */
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


        /* --- Main Content & Header KONSISTENSI --- */
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


        /* --- CARD & BUTTON KONSISTENSI --- */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }


        .card-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-bottom: 2px solid #a97c50;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }


        .btn-primary,
        .btn-upload {
            background: linear-gradient(135deg, #a97c50 0%, #8b6332 100%) !important;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white !important;
        }


        .btn-primary:hover,
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(169, 124, 80, 0.4);
        }


        /* Form Control KONSISTENSI */
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }


        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            height: 42px;
        }


        /* --- END KONSISTENSI MASTER ADMIN --- */



        /* --- GALERI SECTION STYLING --- */
        .upload-section {
            margin-bottom: 25px;
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }


        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
            background: white;
            padding: 0;
        }


        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }


        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }


        /* --- GALLERY OVERLAY DAN TOMBOL AKSI REVISI --- */
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }


        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }


        .gallery-overlay .btn {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: white;
            opacity: 0.9;
            transition: opacity 0.2s, transform 0.2s;
        }


        .gallery-overlay .btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }


        /* WARNA TOMBOL KONSISTENSI MASTER ADMIN */
        .btn-delete {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }


        .btn-delete:hover {
            background-color: #c82333 !important;
        }

        /* Dihapus: Styles untuk .btn-view dan .btn-view:hover */


        /* Dihapus: Styles untuk .modal-body img.img-fluid karena modal detail dihilangkan */
    </style>
</head>


<body>
    <?php include 'sidebar.php'; ?>


    <main class="main">


        <div class="main-header">
            <div>
                <h2>Galeri Foto</h2>
                <small class="text-muted">
                    <i class="bi bi-images"></i> Kelola koleksi gambar pendakian.
                    <span class="permission-badge">
                        <?= RoleHelper::getRoleDisplayName($user_role) ?>
                    </span>
                </small>
            </div>
        </div>


        <section class="upload-section">
            <form id="formUpload" enctype="multipart/form-data">
                <div class="row align-items-end">
                    <div class="col-md-9 mb-3 mb-md-0">
                        <label for="fileInput" class="form-label fw-bold">Pilih Gambar untuk Diunggah</label>
                        <input type="file" id="fileInput" name="fileInput" accept="image/*" class="form-control" required />
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-upload w-100" style="height: 42px;">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Unggah
                        </button>
                    </div>
                </div>


            </form>
        </section>


        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-brown">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Koleksi Unggahan
                </h5>
            </div>
            <div class="card-body">
                <div id="alertContainer"></div>
                <section class="gallery-grid" id="galleryGrid">
                    <div class="col-12 text-center text-muted" style="justify-content: center">Memuat galeri...</div>
                </section>
            </div>
        </div>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>
    <script src="<?php echo getAssetsUrl('frontend/galeri.js'); ?>"></script>
</body>


</html>