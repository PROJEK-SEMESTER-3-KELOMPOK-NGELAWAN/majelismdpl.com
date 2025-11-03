<?php
// profile.php
require_once '../backend/koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: /login.php');
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
        'username' => 'Pengguna MDPL',
        'email' => 'emailanda@contoh.com',
        'no_wa' => '08xxxxxxxxxx',
        'alamat' => 'Alamat belum diatur',
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8dcc4 100%);
            min-height: 100vh;
            padding-top: 80px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(169, 124, 80, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(212, 165, 116, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255, 184, 0, 0.04) 0%, transparent 50%);
            animation: particleFloat 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes particleFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -30px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* ============================================
           PREMIUM PROFILE HERO - LIGHT THEME
           ============================================ */

        .profile-hero {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-radius: 30px;
            padding: 60px 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(169, 124, 80, 0.2);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        /* Animated Gradient Orbs */
        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(169, 124, 80, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: orbFloat 15s ease-in-out infinite;
            filter: blur(60px);
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(212, 165, 116, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            animation: orbFloat 12s ease-in-out infinite reverse;
            filter: blur(50px);
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(50px, -50px) scale(1.2);
            }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .profile-avatar-wrapper {
            position: relative;
            cursor: pointer;
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .profile-avatar-wrapper:hover {
            transform: translateY(-10px) scale(1.05);
        }

        .avatar-border {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            padding: 5px;
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 50%, #ffb800 100%);
            position: relative;
            animation: rotateBorder 0.1s linear infinite;
            box-shadow: 0 10px 40px rgba(169, 124, 80, 0.3);
        }

        .profile-avatar,
        .profile-initials-circle {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            border: 5px solid #fff;
        }

        .profile-avatar {
            object-fit: cover;
        }

        .profile-initials-circle {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .avatar-camera-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(34, 153, 84, 0.5);
            transition: all 0.3s ease;
        }

        .profile-avatar-wrapper:hover .avatar-camera-icon {
            transform: scale(1.2) rotate(15deg);
            box-shadow: 0 12px 30px rgba(34, 153, 84, 0.7);
        }

        .hero-info {
            flex: 1;
        }

        .hero-info h1 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3D2F21 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .hero-email {
            font-size: 1.1rem;
            color: #6B5847;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-stats {
            margin-top: 30px;
            display: flex;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffb800 0%, #a97c50 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #9B8A76;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
            font-weight: 600;
        }

        /* ============================================
           3D FLIP CARDS - LIGHT THEME
           ============================================ */

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .flip-card {
            height: 220px;
            perspective: 1000px;
        }

        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .flip-card:hover .flip-card-inner {
            transform: rotateY(180deg);
        }

        .flip-card-front,
        .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 20px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .flip-card-front {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 2px solid rgba(169, 124, 80, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .flip-card-back {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            transform: rotateY(180deg);
            box-shadow: 0 15px 40px rgba(169, 124, 80, 0.3);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(169, 124, 80, 0.1) 0%, rgba(212, 165, 116, 0.15) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #a97c50;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .flip-card-front:hover .card-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, rgba(169, 124, 80, 0.2) 0%, rgba(212, 165, 116, 0.25) 100%);
        }

        .card-title {
            font-size: 0.8rem;
            color: #9B8A76;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .card-value {
            font-size: 1.2rem;
            color: #3D2F21;
            font-weight: 700;
            line-height: 1.5;
        }

        .flip-card-back .card-title {
            color: rgba(255, 255, 255, 0.8);
        }

        .flip-card-back .card-value {
            color: #fff;
        }

        .flip-card-back .edit-btn {
            margin-top: auto;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .flip-card-back .edit-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* ============================================
           ACTION BUTTONS - ANIMATED LIGHT THEME
           ============================================ */

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-btn {
            position: relative;
            padding: 35px;
            border-radius: 20px;
            border: 2px solid rgba(169, 124, 80, 0.2);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: rgba(169, 124, 80, 0.4);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .action-btn.password-btn:hover {
            background: linear-gradient(135deg, rgba(169, 124, 80, 0.1) 0%, rgba(212, 165, 116, 0.05) 100%);
        }

        .action-btn.logout-btn {
            border-color: rgba(217, 83, 79, 0.3);
        }

        .action-btn.logout-btn:hover {
            background: linear-gradient(135deg, rgba(217, 83, 79, 0.1) 0%, rgba(255, 71, 87, 0.05) 100%);
            border-color: rgba(217, 83, 79, 0.5);
        }

        .action-icon-big {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: linear-gradient(135deg, rgba(169, 124, 80, 0.1) 0%, rgba(212, 165, 116, 0.15) 100%);
            color: #a97c50;
            transition: all 0.3s ease;
        }

        .logout-btn .action-icon-big {
            background: linear-gradient(135deg, rgba(217, 83, 79, 0.1) 0%, rgba(255, 71, 87, 0.15) 100%);
            color: #d9534f;
        }

        .action-btn:hover .action-icon-big {
            transform: rotateY(360deg) scale(1.1);
        }

        .action-title-big {
            font-size: 1.5rem;
            font-weight: 800;
            color: #3D2F21;
            text-align: center;
            margin-bottom: 10px;
        }

        .logout-btn .action-title-big {
            color: #d9534f;
        }

        .action-desc {
            font-size: 0.95rem;
            color: #6B5847;
            text-align: center;
            line-height: 1.6;
        }

        /* ============================================
           MODAL - LIGHT THEME
           ============================================ */

        .edit-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .edit-modal-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px) saturate(180%);
            border: 2px solid rgba(169, 124, 80, 0.2);
            border-radius: 25px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
            max-width: 550px;
            width: 90%;
            padding: 40px;
            animation: modalSlideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(100px) scale(0.9);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .modal-glow {
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(169, 124, 80, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .edit-modal-box h3 {
            font-size: 1.8rem;
            color: #3D2F21;
            margin-bottom: 10px;
            font-weight: 800;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .edit-modal-box h3 i {
            color: #a97c50;
        }

        .modal-subtitle {
            font-size: 0.95rem;
            color: #6B5847;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(169, 124, 80, 0.2);
            position: relative;
            z-index: 1;
        }

        .edit-modal-box label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #a97c50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .edit-modal-box input,
        .edit-modal-box textarea {
            width: 100%;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 2px solid rgba(169, 124, 80, 0.3);
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            background: rgba(255, 255, 255, 0.8);
            color: #3D2F21;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .edit-modal-box input:focus,
        .edit-modal-box textarea:focus {
            border-color: #a97c50;
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(169, 124, 80, 0.1);
        }

        .edit-modal-box textarea {
            min-height: 100px;
            resize: vertical;
        }

        .edit-modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        .btn-save,
        .btn-cancel {
            padding: 15px 35px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-save {
            background: linear-gradient(135deg, #a97c50 0%, #d4a574 100%);
            color: #fff;
            box-shadow: 0 8px 25px rgba(169, 124, 80, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(169, 124, 80, 0.5);
        }

        .btn-cancel {
            background: rgba(169, 124, 80, 0.1);
            color: #a97c50;
            border: 2px solid rgba(169, 124, 80, 0.3);
        }

        .btn-cancel:hover {
            background: rgba(169, 124, 80, 0.2);
            border-color: rgba(169, 124, 80, 0.5);
        }

        /* ============================================
           RESPONSIVE
           ============================================ */

        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }

            .profile-container {
                padding: 0 15px;
                margin: 20px auto;
            }

            .profile-hero {
                padding: 40px 25px;
                border-radius: 20px;
            }

            .hero-content {
                flex-direction: column;
                text-align: center;
                gap: 30px;
            }

            .avatar-border {
                width: 150px;
                height: 150px;
            }

            .profile-avatar,
            .profile-initials-circle {
                width: 140px;
                height: 140px;
            }

            .profile-initials-circle {
                font-size: 3rem;
            }

            .hero-info h1 {
                font-size: 2rem;
            }

            .hero-stats {
                justify-content: center;
            }

            .cards-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .edit-modal-box {
                padding: 30px 25px;
                width: 95%;
            }

            .edit-modal-box h3 {
                font-size: 1.5rem;
            }

            .edit-modal-actions {
                flex-direction: column;
            }

            .btn-save,
            .btn-cancel {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .avatar-border {
                width: 130px;
                height: 130px;
            }

            .profile-avatar,
            .profile-initials-circle {
                width: 120px;
                height: 120px;
            }

            .profile-initials-circle {
                font-size: 2.5rem;
            }

            .avatar-camera-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .hero-info h1 {
                font-size: 1.6rem;
            }

            .action-icon-big {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }

            .action-title-big {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="profile-container">

        <!-- PREMIUM HERO -->
        <div class="profile-hero">
            <div class="hero-content">
                <div class="profile-avatar-wrapper" onclick="openEditModal('photo')">
                    <div class="avatar-border">
                        <?php if ($isDefaultPhoto) : ?>
                            <div class="profile-initials-circle">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                        <?php else : ?>
                            <img src="<?= htmlspecialchars($fotoPath) ?>?v=<?= time() ?>" alt="Foto Profil" class="profile-avatar">
                        <?php endif; ?>
                    </div>
                    <div class="avatar-camera-icon"><i class="fa-solid fa-camera"></i></div>
                </div>

                <div class="hero-info">
                    <h1><?= htmlspecialchars($userData['username']) ?></h1>
                    <p class="hero-email"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($userData['email']) ?></p>
                </div>
            </div>
        </div>

        <!-- 3D FLIP CARDS -->
        <div class="cards-grid">
            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <div class="card-icon"><i class="fa-solid fa-user"></i></div>
                        <div class="card-title">Username</div>
                        <div class="card-value"><?= htmlspecialchars($userData['username']) ?></div>
                    </div>
                    <div class="flip-card-back">
                        <div class="card-title">USERNAME</div>
                        <div class="card-value"><?= htmlspecialchars($userData['username']) ?></div>
                        <button class="edit-btn" onclick="openEditModal('username', 'Nama Pengguna', 'text', '<?= htmlspecialchars($userData['username']) ?>')">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    </div>
                </div>
            </div>

            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <div class="card-icon"><i class="fa-brands fa-whatsapp"></i></div>
                        <div class="card-title">WhatsApp</div>
                        <div class="card-value"><?= htmlspecialchars($userData['no_wa']) ?></div>
                    </div>
                    <div class="flip-card-back">
                        <div class="card-title">WHATSAPP</div>
                        <div class="card-value"><?= htmlspecialchars($userData['no_wa']) ?></div>
                        <button class="edit-btn" onclick="openEditModal('no_wa', 'Nomor WhatsApp', 'text', '<?= htmlspecialchars($userData['no_wa']) ?>')">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    </div>
                </div>
            </div>

            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <div class="card-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="card-title">Alamat</div>
                        <div class="card-value"><?= htmlspecialchars(strlen($userData['alamat']) > 40 ? substr($userData['alamat'], 0, 40) . '...' : $userData['alamat']) ?></div>
                    </div>
                    <div class="flip-card-back">
                        <div class="card-title">ADDRESS</div>
                        <div class="card-value" style="font-size: 1rem;"><?= htmlspecialchars($userData['alamat']) ?></div>
                        <button class="edit-btn" onclick="openEditModal('alamat', 'Alamat Tinggal', 'textarea', '<?= htmlspecialchars(str_replace("\n", " ", $userData['alamat'])) ?>')">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-buttons">
            <!-- REDIRECT KE LUPA PASSWORD -->
            <a href="lupa-password.php" class="action-btn password-btn" style="text-decoration: none;">
                <div class="action-icon-big">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="action-title-big">Ubah Password</div>
                <div class="action-desc">Perbarui kata sandi Anda secara berkala untuk keamanan</div>
            </a>

            <div class="action-btn logout-btn" id="logout-trigger">
                <div class="action-icon-big">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div class="action-title-big">Logout</div>
                <div class="action-desc">Keluar dari akun Anda dengan aman</div>
            </div>
        </div>


    </div>

    <!-- EDIT MODAL -->
    <div id="editModalOverlay" class="edit-modal-overlay">
        <div class="edit-modal-box">
            <div class="modal-glow"></div>
            <h3 id="modalTitle"><i class="fa-solid fa-pen-to-square"></i> Edit Data</h3>
            <p class="modal-subtitle">Perbarui informasi Anda</p>

            <form id="editForm" action="../backend/update-profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="field_to_update" id="fieldToUpdate">
                <div id="dynamicInputArea"></div>
                <div class="edit-modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-save"><i class="fa-solid fa-check"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <form id="photoForm" action="../backend/update-photo.php" method="POST" enctype="multipart/form-data" style="display: none;">
        <input type="file" name="foto_profil" id="inputPhotoFile" accept="image/*">
    </form>

    <form id="logout-form" action="../backend/logout.php" method="POST" style="display: none;">
        <input type="hidden" name="logout_request" value="1">
    </form>

    <script>
        const notifMessage = "<?= $notif_message ?>";
        const notifType = "<?= $notif_type ?>";

        if (notifMessage && notifType) {
            Swal.fire({
                icon: notifType,
                title: notifType === 'success' ? 'Berhasil! 🎉' : 'Gagal! 😟',
                text: notifMessage,
                timer: 3500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                background: notifType === 'success' ? '#d4edda' : '#f8d7da',
                color: notifType === 'success' ? '#155724' : '#721c24'
            });
        }

        function openEditModal(field, title, type, currentValue = '') {
            const modal = document.getElementById('editModalOverlay');
            const titleElement = document.getElementById('modalTitle');
            const inputArea = document.getElementById('dynamicInputArea');
            const form = document.getElementById('editForm');

            titleElement.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Ubah ${title}`;
            document.getElementById('fieldToUpdate').value = field;

            inputArea.innerHTML = '';

            if (field === 'photo') {
                document.getElementById('inputPhotoFile').click();
                return;
            } else if (field === 'password') {
                inputArea.innerHTML = `
                    <label for="current_password"><i class="fa-solid fa-key"></i> Password Lama</label>
                    <input type="password" id="current_password" name="current_password" required placeholder="Masukkan password lama">
                    <label for="new_password"><i class="fa-solid fa-lock"></i> Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="Masukkan password baru">
                    <label for="confirm_password"><i class="fa-solid fa-check-double"></i> Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
                `;
                form.action = '../backend/update-password.php';
            } else {
                if (type === 'textarea') {
                    inputArea.innerHTML = `
                        <label for="${field}"><i class="fa-solid fa-edit"></i> ${title}</label>
                        <textarea name="${field}" id="${field}" required placeholder="Masukkan ${title.toLowerCase()}">${currentValue.trim()}</textarea>
                    `;
                } else {
                    inputArea.innerHTML = `
                        <label for="${field}"><i class="fa-solid fa-edit"></i> ${title}</label>
                        <input type="${type}" name="${field}" id="${field}" value="${currentValue}" required placeholder="Masukkan ${title.toLowerCase()}">
                    `;
                }
                form.action = '../backend/update-profile.php';
            }

            modal.style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModalOverlay').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputPhotoFile = document.getElementById('inputPhotoFile');
            if (inputPhotoFile) {
                inputPhotoFile.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        if (this.files[0].size > 2 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: "Ukuran file terlalu besar! Maksimal 2MB.",
                            });
                            this.value = '';
                            return;
                        }
                        document.getElementById('photoForm').submit();
                    }
                });
            }

            const logoutTrigger = document.getElementById('logout-trigger');
            if (logoutTrigger) {
                logoutTrigger.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Yakin Ingin Keluar?',
                        text: "Anda akan diarahkan ke halaman login.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#a97c50',
                        confirmButtonText: '<i class="fa-solid fa-right-from-bracket"></i> Ya, Logout!',
                        cancelButtonText: '<i class="fa-solid fa-xmark"></i> Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            }

            document.getElementById('editModalOverlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEditModal();
                }
            });
        });
    </script>

</body>

</html>