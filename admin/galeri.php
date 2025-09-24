<?php
require_once 'auth_check.php';
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

        h1.daftar-heading {
            color: #a97c50;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 32px 0 18px 0;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .upload-section {
            margin-bottom: 25px;
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .btn-upload {
            background-color: #a97c50;
            color: white;
            font-weight: 600;
        }

        .btn-upload:hover {
            background-color: #805d31;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }

        /* Updated Gallery Item Styling */
        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
            background: white;
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

        .gallery-item .position-relative {
            position: relative;
        }

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
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay .btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
        }

        .gallery-info {
            padding: 12px;
            text-align: center;
        }

        .btn-delete {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .btn-delete:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }

        .btn-view {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }

        .btn-view:hover {
            background-color: #0056b3 !important;
            border-color: #004085 !important;
        }

        /* Toast Container */
        .toast-container {
            z-index: 9999;
        }

        /* Loading State */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }

            .gallery-item img {
                height: 150px;
            }

            .gallery-overlay .btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
        }

        /* Alert Styling */
        .alert-custom {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Custom Modal Delete Styles */
        .modal-delete {
            backdrop-filter: blur(5px);
        }

        .modal-delete .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-delete .modal-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 20px 30px;
        }

        .modal-delete .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-delete .image-preview {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 15px;
            margin: 20px auto;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .modal-delete .btn-confirm-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-delete .btn-confirm-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(220, 53, 69, 0.4);
        }

        .modal-delete .btn-cancel {
            background: #6c757d;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-delete .btn-cancel:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.7) translateY(-50px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-delete.show .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main">
        <h1 class="daftar-heading">Galeri</h1>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <section class="upload-section">
            <form id="formUpload" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fileInput" class="form-label fw-bold">Upload Gambar</label>
                    <input type="file" id="fileInput" name="fileInput" accept="image/*" class="form-control" required />
                    <img id="imagePreview" style="display:none; max-width: 300px; margin-top: 10px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);" />
                </div>
                <button type="submit" class="btn btn-upload">Upload</button>
            </form>
        </section>

        <section class="gallery-grid" id="galleryGrid">
            <!-- Gambar akan dimunculkan disini -->
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/galeri.js"></script>
</body>

</html>