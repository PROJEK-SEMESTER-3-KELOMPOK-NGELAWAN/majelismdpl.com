<?php
// profile.php
// Pastikan file ini berada di dalam folder 'user/'
require_once '../backend/koneksi.php'; // Sesuaikan path koneksi Anda
session_start();

// Cek jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['id_user'])) {
    header('Location: ' . $navbarPath . 'login.php'); // Asumsi ada login.php di root
    exit();
}

// Cek dan tampilkan notifikasi dari sesi
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

// ... Sisa kode PHP (Ambil data user) ...

$id_user = $_SESSION['id_user'];
$userData = null;

// Ambil data user dari database (Asumsi kolom foto_profil sudah ditambahkan)
$stmt = $conn->prepare("SELECT username, email, no_wa, alamat, foto_profil FROM users WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
}
$stmt->close();

// Fallback data jika gagal ambil dari DB
if (!$userData) {
    $userData = [
        'username' => 'Pengguna MDPL',
        'email' => 'emailanda@contoh.com',
        'no_wa' => '08xxxxxxxxxx',
        'alamat' => 'Alamat belum diatur',
        'foto_profil' => 'default.jpg' 
    ];
}

// Menentukan path foto profil
$fotoPath = !empty($userData['foto_profil']) && file_exists('../img/profile/' . $userData['foto_profil']) 
    ? '../img/profile/' . $userData['foto_profil'] 
    : '../img/profile/default.jpg';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | Majelis MDPL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    
    <style>
        /* === BASE STYLES === */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5; /* Background Abu-abu Muda */
            padding-top: 80px; 
            min-height: 100vh;
        }

        .profile-container {
            max-width: 600px;
            width: 100%;
            margin: 20px auto;
            padding: 0 15px;
        }

        /* === HEADER DISPLAY (Gaya WhatsApp) === */
        .profile-header {
            text-align: center;
            padding: 30px 0;
            background: linear-gradient(180deg, #a97c50 0%, #8b5e3c 100%); /* Earth Tone Gradient */
            color: white;
            border-radius: 15px 15px 0 0;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .profile-photo-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .profile-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        
        .profile-photo-wrapper:hover .profile-photo {
            transform: scale(1.05);
        }

        .camera-icon-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #25d366; /* Hijau (seperti WA) */
            color: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 3px solid #fff;
        }

        .profile-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        /* === LIST DETAIL (Mirip Setting Aplikasi) === */
        .profile-list-group {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden; /* Penting untuk border list */
        }

        .list-header {
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #a97c50; /* Warna utama */
            border-bottom: 1px solid #eee;
            background-color: #fdfdfd;
        }

        .list-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .list-item:hover {
            background-color: #f5f5f5;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-icon {
            font-size: 1.2rem;
            color: #8b5e3c;
            width: 30px;
            text-align: center;
        }

        .list-details {
            flex-grow: 1;
            margin-left: 15px;
        }

        .list-details .title {
            font-size: 0.85rem;
            color: #777;
            margin: 0;
        }

        .list-details .value {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            line-height: 1.4;
        }

        .list-action-icon {
            color: #aaa;
        }

        /* === MODAL POPUP EDIT (List Edit) === */
        .edit-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .edit-modal-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.4);
            max-width: 450px;
            width: 90%;
            padding: 25px;
            animation: modalPop 0.3s ease-out;
        }

        @keyframes modalPop {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .edit-modal-box h3 {
            border-bottom: 2px solid #a97c50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #a97c50;
        }
        
        .edit-modal-box input, .edit-modal-box textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .edit-modal-box input:focus, .edit-modal-box textarea:focus {
            border-color: #a97c50;
            outline: none;
        }

        .edit-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .btn-save, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-save {
            background-color: #a97c50;
            color: white;
        }

        .btn-save:hover {
            background-color: #8b5e3c;
        }

        .btn-cancel {
            background-color: #ccc;
            color: #333;
        }
        
        /* Logout Button di List */
        .list-item.logout {
            background-color: #fbebeb;
            border-radius: 0 0 10px 10px;
        }
        .list-item.logout .list-icon {
            color: #d9534f;
        }
        .list-item.logout .list-details .value {
            color: #d9534f;
            font-weight: 700;
        }
        .list-item.logout:hover {
            background-color: #f7e0e0;
        }
    </style>
</head>
<body>

<?php // include '../navbar.php'; // Sertakan navbar di sini jika perlu ?>

<div class="profile-container">
    
    <div class="profile-header">
        <div class="profile-photo-wrapper" onclick="openEditModal('photo')">
            <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto Profil" class="profile-photo">
            <div class="camera-icon-overlay"><i class="fa-solid fa-camera"></i></div>
        </div>
        <h1><?= htmlspecialchars($userData['username']) ?></h1>
    </div>

    <div class="profile-list-group">
        
        <div class="list-header"><i class="fa-solid fa-info-circle"></i> Informasi Akun</div>
        
        <div class="list-item" onclick="openEditModal('username', 'Nama Pengguna', 'text', '<?= htmlspecialchars($userData['username']) ?>')">
            <div class="list-icon"><i class="fa-solid fa-user"></i></div>
            <div class="list-details">
                <p class="title">Nama Pengguna</p>
                <p class="value"><?= htmlspecialchars($userData['username']) ?></p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <div class="list-item" onclick="openEditModal('email', 'Email', 'email', '<?= htmlspecialchars($userData['email']) ?>')">
            <div class="list-icon"><i class="fa-solid fa-envelope"></i></div>
            <div class="list-details">
                <p class="title">Email</p>
                <p class="value"><?= htmlspecialchars($userData['email']) ?></p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <div class="list-item" onclick="openEditModal('no_wa', 'Nomor WhatsApp', 'text', '<?= htmlspecialchars($userData['no_wa']) ?>')">
            <div class="list-icon"><i class="fa-brands fa-whatsapp"></i></div>
            <div class="list-details">
                <p class="title">Nomor WhatsApp</p>
                <p class="value"><?= htmlspecialchars($userData['no_wa']) ?></p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <div class="list-item" onclick="openEditModal('alamat', 'Alamat Tinggal', 'textarea', '<?= htmlspecialchars(str_replace("\n", " ", $userData['alamat'])) ?>')">
            <div class="list-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div class="list-details">
                <p class="title">Alamat</p>
                <p class="value"><?= htmlspecialchars(substr($userData['alamat'], 0, 40)) . (strlen($userData['alamat']) > 40 ? '...' : '') ?></p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <div class="list-header" style="margin-top: 10px; border-top: 1px solid #eee;"><i class="fa-solid fa-shield-alt"></i> Pengaturan Keamanan</div>

        <div class="list-item" onclick="openEditModal('password', 'Ganti Password', 'password')">
            <div class="list-icon"><i class="fa-solid fa-lock"></i></div>
            <div class="list-details">
                <p class="title">Kata Sandi</p>
                <p class="value">Ubah kata sandi Anda secara berkala</p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <div class="list-item logout" id="logout-trigger">
            <div class="list-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
            <div class="list-details">
                <p class="title">&nbsp;</p>
                <p class="value">Logout</p>
            </div>
            <div class="list-action-icon"><i class="fa-solid fa-chevron-right"></i></div>
        </div>
        
    </div>

</div>

<div id="editModalOverlay" class="edit-modal-overlay">
    <div class="edit-modal-box">
        <h3 id="modalTitle">Edit Data</h3>
        
        <form id="editForm" action="backend/update-profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="field_to_update" id="fieldToUpdate">

            <div id="dynamicInputArea">
                </div>

            <div class="edit-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<form id="photoForm" action="../backend/update-photo.php" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="file" name="foto_profil" id="inputPhotoFile" accept="image/*">
    <button type="submit" id="submitPhotoBtn"></button>
</form>

<?php // include 'logout-modal.html'; ?> 
<script>

    // Logika menampilkan notifikasi SweetAlert2
    const notifMessage = "<?= $notif_message ?>";
    const notifType = "<?= $notif_type ?>";

    if (notifMessage && notifType) {
        Swal.fire({
            icon: notifType,
            title: notifType === 'success' ? 'Berhasil!' : 'Gagal!',
            text: notifMessage,
            timer: 3500,
            showConfirmButton: false
        });
    }
    
    // --- Logika Modal Edit Dinamis ---
    function openEditModal(field, title, type, currentValue = '') {
        const modal = document.getElementById('editModalOverlay');
        const titleElement = document.getElementById('modalTitle');
        const inputArea = document.getElementById('dynamicInputArea');
        const form = document.getElementById('editForm');
        
        // Atur judul dan field yang akan diupdate
        titleElement.textContent = `Ubah ${title}`;
        document.getElementById('fieldToUpdate').value = field;
        
        inputArea.innerHTML = ''; // Kosongkan area input

        if (field === 'photo') {
            // Jika klik foto, langsung trigger input file dan keluar
            document.getElementById('inputPhotoFile').click();
            return; 
        } else if (field === 'password') {
            // Form Ganti Password
            inputArea.innerHTML = `
                <label for="current_password">Password Lama</label>
                <input type="password" id="current_password" name="current_password" required placeholder="Masukkan password lama">
                <label for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password" required placeholder="Masukkan password baru">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
            `;
            form.action = 'backend/update-password.php';
            form.method = 'POST';
        } else {
            // Form Edit Data Umum (Username, Email, WA, Alamat)
            if (type === 'textarea') {
                inputArea.innerHTML = `<textarea name="${field}" required placeholder="Masukkan ${title}">${currentValue.trim()}</textarea>`;
            } else {
                inputArea.innerHTML = `<input type="${type}" name="${field}" value="${currentValue}" required placeholder="Masukkan ${title}">`;
            }
            form.action = 'backend/update-profile.php';
            form.method = 'POST';
        }

        if (field !== 'photo') {
             modal.style.display = 'flex';
        }
    }

    function closeEditModal() {
        document.getElementById('editModalOverlay').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Logika untuk mengirim form foto setelah file dipilih
        const inputPhotoFile = document.getElementById('inputPhotoFile');
        if (inputPhotoFile) {
            inputPhotoFile.addEventListener('change', function() {
                if (this.files.length > 0) {
                    // Cek ukuran file (Max 2MB)
                    if (this.files[0].size > 2 * 1024 * 1024) {
                         alert("Ukuran file terlalu besar! Maksimal 2MB.");
                         this.value = ''; // Reset input
                         return;
                    }
                    // Langsung kirim form foto setelah memilih file
                    document.getElementById('photoForm').submit();
                }
            });
        }
        
        // Logika untuk trigger Logout Modal (Asumsi Anda menggunakan modal yang sama di navbar)
        const logoutTrigger = document.getElementById('logout-trigger');
        if (logoutTrigger) {
             logoutTrigger.addEventListener('click', () => {
                const logoutModal = document.getElementById('logout-modal'); 
                if (logoutModal) {
                    logoutModal.classList.add('show');
                } else {
                    // Jika modal tidak ditemukan, fallback ke konfirmasi dasar
                    if (confirm("Apakah Anda Yakin Ingin Logout?")) {
                        document.getElementById('logout-form').submit();
                    }
                }
            });
        }
    });

</script>

</body>
</html>