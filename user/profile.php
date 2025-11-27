<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

// --- LOGIC PHP (TIDAK DIUBAH SAMA SEKALI) ---
if (!isset($_SESSION['id_user'])) {
    header('Location: ' . getPageUrl('index.php'));
    exit();
}

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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* --- COLOR PALETTE PROFESSIONAL --- */
            --brown-primary: #9c7352;
            --brown-dark: #7a583e;
            --brown-light: #fdfbf9;
            --brown-subtle: #f0e6de;

            --text-main: #1a1a1a;
            --text-secondary: #6c757d;
            --border-subtle: #f1f1f1;

            --bg-page: #fafafa;
            --surface: #ffffff;

            --shadow-card: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
            --shadow-float: 0 20px 40px -10px rgba(156, 115, 82, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            padding-top: 100px;
            color: var(--text-main);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Dekorasi Background Halus */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 350px;
            background: linear-gradient(180deg, #eae0d9 0%, rgba(250, 250, 250, 0) 100%);
            z-index: -1;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        /* --- COMPONENTS --- */

        /* Buttons */
        .btn-pro-brown {
            background-color: var(--brown-primary);
            color: white;
            border: none;
            padding: 12px 28px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-pro-brown:hover {
            background-color: var(--brown-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(156, 115, 82, 0.25);
        }

        .btn-edit-outline {
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brown-primary);
            background: var(--brown-light);
            border: 1px solid transparent;
            border-radius: 50px;
            /* Pill Shape */
            transition: 0.2s;
        }

        .btn-edit-outline:hover {
            background: var(--brown-primary);
            color: white;
            transform: scale(1.05);
        }

        /* Cards */
        .card-pro {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: var(--shadow-card);
            overflow: hidden;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-pro:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-float);
        }

        /* --- PROFILE SECTION (LEFT) --- */
        .profile-section {
            padding: 45px 30px 30px;
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
        }

        .avatar-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 25px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .avatar-wrapper:hover {
            transform: scale(1.02);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .avatar-initials {
            width: 100%;
            height: 100%;
            background: var(--brown-subtle);
            color: var(--brown-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 10px 25px rgba(156, 115, 82, 0.15);
        }

        .btn-cam {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 38px;
            height: 38px;
            background: rgba(43, 43, 43, 0.9);
            backdrop-filter: blur(4px);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-cam:hover {
            background: var(--brown-primary);
            transform: rotate(15deg);
        }

        .status-badge {
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid #d1fae5;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 18px 30px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            border-top: 1px solid var(--border-subtle);
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .menu-link:hover {
            background-color: var(--brown-light);
            color: var(--brown-primary);
            padding-left: 35px;
            /* Slight nudge effect */
        }

        .menu-link.danger-zone:hover {
            background-color: #fff1f2;
            color: #e11d48;
        }

        /* --- DETAILS SECTION (RIGHT) --- */
        .header-title {
            padding: 25px 35px;
            border-bottom: 1px solid var(--border-subtle);
            background: #fff;
        }

        .detail-row {
            padding: 20px 35px;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .detail-row:hover {
            background-color: #faf9f8;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .label-text {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #9ca3af;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .value-text {
            font-size: 1.05rem;
            font-weight: 500;
            color: var(--text-main);
        }

        /* --- MODAL --- */
        .form-control-pro {
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #f3f4f6;
            background: #f9fafb;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control-pro:focus {
            background: #fff;
            border-color: var(--brown-primary);
            box-shadow: 0 0 0 4px rgba(156, 115, 82, 0.1);
        }

        @media (max-width: 768px) {
            .profile-section {
                padding: 30px 20px;
            }

            .detail-row {
                padding: 20px 25px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .btn-edit-outline {
                width: 100%;
                text-align: center;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-4 animate__animated animate__fadeInLeft">
                <div class="card-pro">
                    <div class="profile-section">
                        <div class="avatar-wrapper" onclick="openPhotoUpload()">
                            <?php if ($isDefaultPhoto) : ?>
                                <div class="avatar-initials"><?= htmlspecialchars($initials) ?></div>
                            <?php else : ?>
                                <img src="<?= htmlspecialchars($fotoPath) ?>?v=<?= time() ?>" alt="Profile" class="avatar-img">
                            <?php endif; ?>

                            <div class="btn-cam">
                                <i class="fa-solid fa-camera fa-sm"></i>
                            </div>
                        </div>

                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($userData['username']) ?></h4>
                        <p class="text-secondary small mb-3"><?= htmlspecialchars($userData['email']) ?></p>

                        <span class="badge rounded-pill status-badge px-3 py-2">
                            <i class="fa-solid fa-circle-check me-1"></i> Akun Terverifikasi
                        </span>
                    </div>

                    <div class="mt-2">
                        <a href="<?= getPageUrl('user/lupa-password.php') ?>" class="menu-link">
                            <i class="fa-solid fa-lock me-3 text-secondary" style="width: 20px;"></i> Ubah Kata Sandi
                        </a>
                        <button class="menu-link danger-zone w-100 bg-transparent border-0 text-start" id="logout-trigger">
                            <i class="fa-solid fa-right-from-bracket me-3 text-secondary" style="width: 20px;"></i> Keluar
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 animate__animated animate__fadeInRight" style="animation-delay: 0.1s;">
                <div class="card-pro">
                    <div class="header-title d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Informasi Pribadi</h5>
                            <small class="text-secondary">Detail data diri anda yang terdaftar</small>
                        </div>
                        <i class="fa-regular fa-id-card fa-2x text-muted opacity-25"></i>
                    </div>

                    <div class="card-body p-0">
                        <div class="detail-row">
                            <div>
                                <div class="label-text">Nama Lengkap</div>
                                <div class="value-text"><?= htmlspecialchars($userData['username']) ?></div>
                            </div>
                            <button type="button" class="btn-edit-outline" onclick="openEditModal('username', 'Nama Lengkap', 'text', '<?= htmlspecialchars($userData['username']) ?>')">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                        </div>

                        <div class="detail-row">
                            <div>
                                <div class="label-text">Email Address</div>
                                <div class="value-text text-secondary"><?= htmlspecialchars($userData['email']) ?></div>
                                <div class="mt-1">
                                    <small class="text-muted fst-italic" style="font-size: 11px;"><i class="fa-solid fa-lock me-1"></i>Tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div>
                                <div class="label-text">Nomor WhatsApp</div>
                                <div class="value-text"><?= htmlspecialchars($userData['no_wa']) ?></div>
                            </div>
                            <button type="button" class="btn-edit-outline" onclick="openEditModal('no_wa', 'Nomor WhatsApp', 'text', '<?= htmlspecialchars($userData['no_wa']) ?>')">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                        </div>

                        <div class="detail-row align-items-start">
                            <div class="pe-3 w-75">
                                <div class="label-text">Alamat Domisili</div>
                                <div class="value-text" style="line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($userData['alamat'])) ?>
                                </div>
                            </div>
                            <button type="button" class="btn-edit-outline mt-1" onclick="openEditModal('alamat', 'Alamat Domisili', 'textarea', '<?= htmlspecialchars(str_replace("\n", " ", $userData['alamat'])) ?>')">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold">Perbarui Data</h5>
                        <p class="text-secondary small mb-0">Pastikan data yang Anda masukkan valid.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="editForm" action="../backend/update-profile.php" method="POST">
                        <input type="hidden" name="field_to_update" id="fieldToUpdate">

                        <div id="dynamicInputArea" class="mb-4">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light px-4 py-2 fw-medium text-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Batal</button>
                            <button type="submit" class="btn-pro-brown">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="photoForm" action="../backend/update-photo.php" method="POST" enctype="multipart/form-data" style="display: none;">
        <input type="file" name="foto_profil" id="inputPhotoFile" accept="image/*">
    </form>

    <form id="logout-form" action="../backend/logout.php" method="POST" style="display: none;">
        <input type="hidden" name="logout_request" value="1">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inisialisasi Modal
        const editModalEl = document.getElementById('editModal');
        const editModal = new bootstrap.Modal(editModalEl);

        // Notifikasi Logic
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

        // --- GLOBAL FUNCTIONS ---

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
                inputArea.innerHTML = `
                    ${labelHtml}
                    <textarea name="${field}" class="form-control form-control-pro" rows="4" placeholder="Masukkan ${title} baru..." required>${currentValue.trim()}</textarea>
                `;
            } else {
                inputArea.innerHTML = `
                    ${labelHtml}
                    <input type="${type}" name="${field}" class="form-control form-control-pro" value="${currentValue}" placeholder="Masukkan ${title} baru..." required>
                `;
            }
            editModal.show();

            // Auto focus input
            setTimeout(() => {
                const input = inputArea.querySelector('input, textarea');
                if (input) input.focus();
            }, 500);
        };

        // Event Listener Foto Change
        const photoInput = document.getElementById('inputPhotoFile');
        if (photoInput) {
            photoInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    if (this.files[0].size > 2 * 1024 * 1024) {
                        Swal.fire({
                            title: 'Terlalu Besar',
                            text: 'Maksimal ukuran file 2MB',
                            icon: 'error',
                            confirmButtonColor: '#9c7352',
                            confirmButtonText: 'Oke, Mengerti'
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

        // Event Listener Logout
        const logoutTrigger = document.getElementById('logout-trigger');
        if (logoutTrigger) {
            logoutTrigger.addEventListener('click', () => {
                Swal.fire({
                    title: 'Konfirmasi Keluar',
                    text: "Apakah Anda yakin ingin mengakhiri sesi ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#f3f4f6',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: '<span style="color:#555">Batal</span>',
                    reverseButtons: true,
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