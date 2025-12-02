<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

// 1. DEFINISI PATH NAVBAR
$navbarPath = '../';

if (!isset($_SESSION['id_user'])) {
    header('Location: ' . getPageUrl('index.php'));
    exit();
}

// Logic Notifikasi
$notif_message = '';
$notif_type = '';
if (isset($_SESSION['pesan_sukses'])) {
    $notif_message = $_SESSION['pesan_sukses'];
    $notif_type = 'success';
    unset($_SESSION['pesan_sukses']);
} elseif (isset($_SESSION['pesan_error'])) {
    $notif_message = $_SESSION['pesan_error'];
    $notif_type = 'error';
    unset($_SESSION['pesan_error']);
}

$id_user = $_SESSION['id_user'];
$userData = null;

$stmt = $conn->prepare("SELECT username, email, no_wa, alamat, foto_profil FROM users WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
}
$stmt->close();

if (!$userData) {
    $userData = [
        'username' => 'Pengguna',
        'email' => '-',
        'no_wa' => '-',
        'alamat' => '-',
        'foto_profil' => 'default.jpg'
    ];
}

$fotoPath = !empty($userData['foto_profil']) && file_exists('../img/profile/' . $userData['foto_profil'])
    ? '../img/profile/' . $userData['foto_profil']
    : '../img/profile/default.jpg';

$isDefaultPhoto = ($fotoPath === '../img/profile/default.jpg');
$initials = strtoupper(substr($userData['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | Majelis MDPL</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ================================================================= */
        /* 🔥 FIX BUTTON CLICK & FONT 🔥 */
        /* ================================================================= */

        :root {
            --bs-body-font-family: 'Poppins', sans-serif !important;
            --bs-font-sans-serif: 'Poppins', sans-serif !important;
            --brown-primary: #9C7E5C;
            --brown-dark: #7B5E3A;
            --brown-soft: #EFEBE9;
            --bg-page: #FDFBF9;
            --surface: #FFFFFF;
            --text-main: #374151;
            --text-muted: #9CA3AF;
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: var(--bg-page);
            padding-top: 120px;
            color: var(--text-main);
        }

        /* Paksa Ikon menggunakan FontAwesome */
        .fa,
        .fas,
        .fa-solid,
        .fa-regular,
        .fa-brands {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }

        .navbar {
            font-family: 'Poppins', sans-serif !important;
        }

        /* ================================================================= */
        /* STYLE PROFIL */
        /* ================================================================= */

        .profile-content-scope {
            font-family: 'Inter', sans-serif !important;
        }

        /* FIX UTAMA: pointer-events: none agar background tidak menghalangi klik */
        .profile-bg-decor::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 0;
            width: 100%;
            height: 500px;
            background: linear-gradient(180deg, #F3ECE7 0%, rgba(253, 251, 249, 0) 100%);
            z-index: 0;
            /* Layer paling bawah */
            pointer-events: none;
            /* PENTING: Agar klik tembus ke bawah */
        }

        .profile-page-wrapper {
            position: relative;
            min-height: 80vh;
            z-index: 1;
            /* Konten di atas background */
        }

        .container {
            position: relative;
            z-index: 2;
            /* Container lebih tinggi lagi */
        }

        /* --- LEFT CARD --- */
        .profile-card-clean {
            background: #FFFFFF;
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 30px -5px rgba(156, 126, 92, 0.1);
            height: 100%;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease;
            z-index: 3;
            /* Pastikan kartu bisa diklik */
        }

        .profile-card-clean:hover {
            transform: translateY(-5px);
        }

        .profile-accent-top {
            height: 6px;
            width: 100%;
            background: linear-gradient(to right, #9C7E5C, #7B5E3A);
        }

        .profile-content-clean {
            padding: 40px 30px 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-avatar-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Animasi Putar */
        .profile-avatar-container::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px dashed rgba(156, 126, 92, 0.3);
            z-index: 0;
            animation: spin 20s linear infinite;
            pointer-events: none;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .profile-avatar-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 2;
            cursor: pointer;
        }

        .profile-main-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #FFFFFF;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-initials {
            width: 140px;
            height: 140px;
            background: #EFEBE9;
            color: #9C7E5C;
            font-family: 'Poppins', sans-serif !important;
            font-size: 3.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 4px solid #FFFFFF;
        }

        .profile-btn-cam {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 36px;
            height: 36px;
            background: #9C7E5C;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #FFFFFF;
            transition: 0.2s;
            cursor: pointer;
            z-index: 10;
            /* Tombol kamera harus paling atas */
        }

        .profile-btn-cam:hover {
            background: #7B5E3A;
            transform: scale(1.1);
        }

        .profile-user-name {
            font-family: 'Poppins', sans-serif !important;
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
        }

        .profile-user-email {
            font-size: 0.9rem;
            color: #9CA3AF;
            margin-bottom: 15px;
            background: #F9FAFB;
            padding: 4px 12px;
            border-radius: 50px;
            display: inline-block;
        }

        .profile-menu-list {
            padding: 0 20px 20px;
        }

        .profile-menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 12px;
            text-decoration: none;
            color: #374151;
            background: #fff;
            border: 1px solid #F3F4F6;
            transition: all 0.2s;
            font-weight: 500;
            cursor: pointer;
            position: relative;
            z-index: 5;
        }

        .profile-menu-item:hover {
            background: #EFEBE9;
            color: #9C7E5C;
            border-color: transparent;
            transform: translateX(5px);
        }

        .profile-menu-item i {
            width: 30px;
            color: #D1D5DB;
            transition: 0.2s;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }

        .profile-menu-item:hover i {
            color: #9C7E5C;
        }

        .profile-menu-item.logout {
            color: #DC2626;
            border-color: #FEF2F2;
            background: #FEF2F2;
            margin-top: 10px;
        }

        .profile-menu-item.logout:hover {
            background: #FEE2E2;
            border-color: #FECACA;
            color: #B91C1C;
        }

        .profile-menu-item.logout i {
            color: #FCA5A5;
        }

        .profile-menu-item.logout:hover i {
            color: #EF4444;
        }

        /* --- RIGHT CARD --- */
        .profile-card-original {
            background: #FFFFFF;
            border: 1px solid rgba(156, 115, 82, 0.1);
            border-radius: 24px;
            box-shadow: 0 10px 30px -5px rgba(156, 126, 92, 0.1);
            height: 100%;
            overflow: hidden;
            position: relative;
            z-index: 3;
        }

        .original-header {
            padding: 25px 35px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: linear-gradient(to right, #fff, #FDFBF7);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .original-header h5 {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700;
            color: #7B5E3A;
            margin-bottom: 2px;
        }

        .original-header i {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 400;
        }

        .original-detail-row {
            padding: 22px 35px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .original-detail-row:hover {
            background-color: #FCFCFC;
        }

        .original-detail-row:last-child {
            border-bottom: none;
        }

        .original-label-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .original-label-text {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9CA3AF;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .original-value-text {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
        }

        .profile-btn-edit-outline {
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #9C7E5C;
            background: transparent;
            border: 1px solid #E8DFD8;
            border-radius: 50px;
            transition: 0.2s;
            cursor: pointer;
            position: relative;
            z-index: 5;
        }

        .profile-btn-edit-outline i {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }

        .profile-btn-edit-outline:hover {
            background: #9C7E5C;
            color: white;
            border-color: #9C7E5C;
            box-shadow: 0 4px 12px rgba(156, 115, 82, 0.2);
        }

        /* MODAL */
        .form-control-modern {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 15px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif !important;
        }

        .form-control-modern:focus {
            background: #fff;
            border-color: #9C7E5C;
            box-shadow: 0 0 0 3px rgba(156, 126, 92, 0.1);
        }

        .btn-save-profile {
            background: #9C7E5C;
            color: white;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-save-profile:hover {
            background: #7B5E3A;
        }

        @media (max-width: 768px) {
            .profile-content-clean {
                padding: 30px 20px;
            }

            .original-detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .profile-btn-edit-outline {
                width: 100%;
                text-align: center;
                background: #FDFBF7;
            }
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <div class="profile-page-wrapper profile-bg-decor profile-content-scope">
        <div class="container pb-5">
            <div class="row g-4">

                <div class="col-lg-4 animate__animated animate__fadeInLeft">
                    <div class="profile-card-clean">
                        <div class="profile-accent-top"></div>
                        <div class="profile-content-clean">
                            <div class="profile-avatar-container">
                                <div class="profile-avatar-wrapper" onclick="openPhotoUpload()">
                                    <?php if ($isDefaultPhoto) : ?>
                                        <div class="profile-initials"><?= htmlspecialchars($initials) ?></div>
                                    <?php else : ?>
                                        <img src="<?= htmlspecialchars($fotoPath) ?>?v=<?= time() ?>" alt="Profile" class="profile-main-img">
                                    <?php endif; ?>
                                    <div class="profile-btn-cam"><i class="fa-solid fa-camera fa-sm"></i></div>
                                </div>
                            </div>
                            <div class="profile-user-name"><?= htmlspecialchars($userData['username']) ?></div>
                            <div class="profile-user-email"><i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($userData['email']) ?></div>
                        </div>
                        <div class="profile-menu-list">
                            <a href="<?= getPageUrl('user/lupa-password.php') ?>" class="profile-menu-item">
                                <i class="fa-solid fa-shield-halved"></i> <span>Ubah Kata Sandi</span>
                            </a>
                            <button class="profile-menu-item logout w-100 text-start" id="logout-trigger" type="button">
                                <i class="fa-solid fa-power-off"></i> <span>Keluar dari Akun</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 animate__animated animate__fadeInRight" style="animation-delay: 0.1s;">
                    <div class="profile-card-original">
                        <div class="original-header">
                            <div>
                                <h5 class="fw-bold mb-1">Informasi Akun</h5>
                                <small class="text-secondary">Kelola data diri Anda untuk keperluan trip.</small>
                            </div>
                            <i class="fa-regular fa-id-card fa-2x text-muted opacity-25"></i>
                        </div>
                        <div class="card-body p-0">
                            <div class="original-detail-row">
                                <div class="original-label-group">
                                    <div>
                                        <div class="original-label-text">Nama Lengkap</div>
                                        <div class="original-value-text"><?= htmlspecialchars($userData['username']) ?></div>
                                    </div>
                                </div>
                                <button type="button" class="profile-btn-edit-outline" onclick="openEditModal('username', 'Nama Lengkap', 'text', '<?= htmlspecialchars($userData['username']) ?>')"><i class="fa-solid fa-pen me-1"></i> Edit</button>
                            </div>
                            <div class="original-detail-row">
                                <div class="original-label-group">
                                    <div>
                                        <div class="original-label-text">Email Address</div>
                                        <div class="original-value-text text-secondary"><?= htmlspecialchars($userData['email']) ?></div>
                                        <div style="font-size: 0.75rem; color:#9ca3af; margin-top:2px;"><i class="fa-solid fa-lock me-1"></i> Permanen</div>
                                    </div>
                                </div>
                            </div>
                            <div class="original-detail-row">
                                <div class="original-label-group">
                                    <div>
                                        <div class="original-label-text">Nomor WhatsApp</div>
                                        <div class="original-value-text"><?= htmlspecialchars($userData['no_wa']) ?></div>
                                    </div>
                                </div>
                                <button type="button" class="profile-btn-edit-outline" onclick="openEditModal('no_wa', 'Nomor WhatsApp', 'text', '<?= htmlspecialchars($userData['no_wa']) ?>')"><i class="fa-solid fa-pen me-1"></i> Edit</button>
                            </div>
                            <div class="original-detail-row align-items-start">
                                <div class="original-label-group w-75">
                                    <div>
                                        <div class="original-label-text">Alamat Domisili</div>
                                        <div class="original-value-text" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($userData['alamat'])) ?></div>
                                    </div>
                                </div>
                                <button type="button" class="profile-btn-edit-outline mt-1" onclick="openEditModal('alamat', 'Alamat Domisili', 'textarea', '<?= htmlspecialchars(str_replace("\n", " ", $userData['alamat'])) ?>')"><i class="fa-solid fa-pen me-1"></i> Edit</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade profile-content-scope" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold" style="color: var(--brown-dark);">Edit Data</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="editForm" action="../backend/update-profile.php" method="POST">
                        <input type="hidden" name="field_to_update" id="fieldToUpdate">
                        <div id="dynamicInputArea" class="mb-4"></div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light px-4 py-2 fw-medium text-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Batal</button>
                            <button type="submit" class="btn-save-profile">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="photoForm" action="../backend/update-photo.php" method="POST" enctype="multipart/form-data" style="display: none;"><input type="file" name="foto_profil" id="inputPhotoFile" accept="image/*"></form>
    <form id="logout-form" action="../backend/logout.php" method="POST" style="display: none;"><input type="hidden" name="logout_request" value="1"></form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Pastikan fungsi didefinisikan di window agar bisa diakses onclick HTML ---
        window.editModalEl = document.getElementById('editModal');
        window.editModal = new bootstrap.Modal(window.editModalEl);

        const notifMessage = "<?= $notif_message ?>";
        const notifType = "<?= $notif_type ?>";

        if (notifMessage && notifType) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: notifType,
                title: notifMessage,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                iconColor: notifType === 'success' ? '#9c7352' : '#d33',
                background: '#fff',
                customClass: {
                    popup: 'rounded-4 shadow-sm'
                }
            });
        }

        window.openPhotoUpload = function() {
            document.getElementById('inputPhotoFile').click();
        };

        window.openEditModal = function(field, title, type, currentValue = '') {
            const inputArea = document.getElementById('dynamicInputArea');
            const fieldInput = document.getElementById('fieldToUpdate');

            fieldInput.value = field;
            inputArea.innerHTML = '';

            const labelHtml = `<label class="form-label text-secondary small fw-bold mb-2 text-uppercase">${title}</label>`;

            if (type === 'textarea') {
                inputArea.innerHTML = `${labelHtml}<textarea name="${field}" class="form-control form-control-modern" rows="4" placeholder="Ketik ${title}..." required>${currentValue.trim()}</textarea>`;
            } else {
                inputArea.innerHTML = `${labelHtml}<input type="${type}" name="${field}" class="form-control form-control-modern" value="${currentValue}" placeholder="Ketik ${title}..." required>`;
            }
            window.editModal.show();
            setTimeout(() => {
                const input = inputArea.querySelector('input, textarea');
                if (input) input.focus();
            }, 500);
        };

        const photoInput = document.getElementById('inputPhotoFile');
        if (photoInput) {
            photoInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    if (this.files[0].size > 2 * 1024 * 1024) {
                        Swal.fire({
                            title: 'Ukuran File Besar',
                            text: 'Maksimal ukuran foto adalah 2MB',
                            icon: 'error',
                            confirmButtonColor: '#9c7352'
                        });
                        this.value = '';
                        return;
                    }
                    Swal.fire({
                        title: 'Mengupdate Foto...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById('photoForm').submit();
                }
            });
        }

        const logoutTrigger = document.getElementById('logout-trigger');
        if (logoutTrigger) {
            logoutTrigger.addEventListener('click', () => {
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: "Apakah Anda yakin ingin logout?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#9c7352',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4'
                    }
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('logout-form').submit();
                });
            });
        }
    </script>
</body>

</html>