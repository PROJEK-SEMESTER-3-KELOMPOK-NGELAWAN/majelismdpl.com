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

        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
            border-radius: 16px;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main">
        <h1 class="daftar-heading">Galeri</h1>

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
    <script src="../frontend/galeri.js"></script>
</body>

</html>