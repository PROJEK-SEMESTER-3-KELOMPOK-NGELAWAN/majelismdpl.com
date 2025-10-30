<?php
require_once '../backend/koneksi.php';
session_start();

// ✅ Set navbar path untuk file di folder user/
$navbarPath = '../';

$isLogin = isset($_SESSION['id_user']);
$userLogin = null;
if ($isLogin) {
    $stmt = $conn->prepare("SELECT username, email, alamat, no_wa FROM users WHERE id_user=?");
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $userLogin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: ../index.php");
    exit();
}

$stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) {
    header("Location: ../index.php");
    exit();
}

$stmtDetail = $conn->prepare("SELECT * FROM detail_trips WHERE id_trip = ?");
$stmtDetail->bind_param("i", $id);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();
$detail = $resultDetail->fetch_assoc();
$stmtDetail->close();

if (!$detail) {
    $detail = [
        'nama_lokasi' => 'Belum ditentukan',
        'alamat' => 'Belum ditentukan',
        'waktu_kumpul' => 'Belum ditentukan',
        'link_map' => '',
        'include' => "Informasi akan diupdate segera",
        'exclude' => "Informasi akan diupdate segera",
        'syaratKetentuan' => "Informasi akan diupdate segera"
    ];
}

function createIconList($text, $iconClass)
{
    $items = array_filter(array_map('trim', explode("\n", $text)));
    if (count($items) <= 1 && empty($items[0])) {
        return '<p>' . nl2br(htmlspecialchars($text)) . '</p>';
    }
    $html = '<ul class="icon-list">';
    foreach ($items as $item) {
        if (!empty($item)) {
            $html .= '<li><i class="' . htmlspecialchars($iconClass) . '"></i> ' . htmlspecialchars($item) . '</li>';
        }
    }
    $html .= '</ul>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
    <title><?= htmlspecialchars($trip['nama_gunung']) ?> | Majelis MDPL</title>

    <!-- ✅ WAJIB: Load Font Poppins (SAMA dengan navbar.php) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* ============================================
           ENHANCED LIQUID GLASS THEME 2025
           Color: #D0B28C + Strong Glass Effect
           ============================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Main Color Palette - D0B28C Base */
            --primary-tan: #D0B28C;
            --tan-dark: #B89968;
            --tan-darker: #A08456;
            --tan-darkest: #846A43;
            --tan-light: #E0C9A8;
            --tan-lighter: #EBD9BD;
            --tan-pale: #F5EAD8;

            /* Card Colors */
            --card-white: #F8F4EE;
            --card-cream: #F2EDE5;

            /* Accent */
            --accent-gold: #FFB800;
            --accent-gold-hover: #E6A600;

            /* Neutral */
            --white: #FFFFFF;
            --text-dark: #3D2F21;
            --text-medium: #6B5847;
            --text-light: #9B8A76;

            /* Enhanced Liquid Glass - More Visible */
            --glass-strong: rgba(255, 255, 255, 0.35);
            --glass-medium: rgba(255, 255, 255, 0.25);
            --glass-light: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.4);

            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(61, 47, 33, 0.08);
            --shadow-md: 0 4px 16px rgba(61, 47, 33, 0.12);
            --shadow-lg: 0 8px 32px rgba(61, 47, 33, 0.16);
            --shadow-xl: 0 16px 48px rgba(61, 47, 33, 0.20);
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #D0B28C;
            background-attachment: fixed;
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 184, 0, 0.04) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1280px;
            margin: 80px auto 0;
            padding: 0 32px;
            position: relative;
            z-index: 1;
        }

        /* ============================================
           HERO SECTION - FULL SCREEN
           ============================================ */

        .hero {
            position: relative;
            height: 100vh;
            width: 100vw;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.65) contrast(1.08) saturate(1.15);
            z-index: 1;
            transition: transform 20s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .hero:hover img {
            transform: scale(1.08);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(36, 34, 31, 0.67);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 4;
            color: var(--white);
            max-width: 900px;
            text-align: center;
            padding: 0 5%;
            animation: heroFadeIn 1.5s cubic-bezier(0.33, 1, 0.68, 1);
        }

        @keyframes heroFadeIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 20px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .hero-text {
            font-size: clamp(3rem, 10vw, 6.5rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 0.95;
            margin-bottom: 32px;
            color: var(--white);
            text-shadow:
                0 4px 16px rgba(0, 0, 0, 0.5),
                0 12px 48px rgba(0, 0, 0, 0.3);
        }

        .btn-hero-wrapper {
            margin-top: 40px;
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            position: relative;
            background: var(--accent-gold);
            color: var(--tan-darkest);
            padding: 18px 48px;
            font-weight: 800;
            font-size: 1rem;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.33, 1, 0.68, 1);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow:
                0 8px 32px rgba(255, 184, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.25);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .btn-hero:hover::before {
            opacity: 1;
        }

        .btn-hero:hover {
            transform: translateY(-4px);
            box-shadow:
                0 16px 48px rgba(255, 184, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .btn-hero:disabled {
            background: rgba(155, 138, 118, 0.5);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-hero i {
            margin-right: 10px;
        }

        /* ============================================
           INFO BAR - Enhanced Glass Effect
           ============================================ */

        .info-bar {
            background: var(--card-white);
            border: 2px solid rgba(208, 178, 140, 0.3);
            border-radius: 24px;
            padding: 40px 36px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 32px;
            margin-bottom: 48px;
            box-shadow: var(--shadow-lg);
            transition: all 0.5s cubic-bezier(0.33, 1, 0.68, 1);
            animation: cardSlideUp 1s cubic-bezier(0.33, 1, 0.68, 1) 0.2s backwards;
        }

        @keyframes cardSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-bar:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .info-item {
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 14px;
            text-align: center;
            padding: 20px 16px;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.33, 1, 0.68, 1);
            background: var(--glass-strong);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 2px solid var(--glass-border);
            box-shadow:
                0 4px 16px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .info-item:hover {
            background: rgba(255, 255, 255, 0.45);
            transform: scale(1.05);
            box-shadow:
                0 8px 24px rgba(208, 178, 140, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .info-item i {
            font-size: 2.8rem;
            color: var(--accent-gold);
            filter: drop-shadow(0 4px 12px rgba(255, 184, 0, 0.4));
            transition: all 0.4s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .info-item:hover i {
            transform: scale(1.15) rotate(-5deg);
            filter: drop-shadow(0 8px 20px rgba(255, 184, 0, 0.6));
        }

        .info-item span:first-of-type {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-medium);
            font-weight: 700;
        }

        .info-item span:last-child {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--text-dark);
        }

        /* ============================================
           CONTENT AREA - Enhanced Glass Items
           ============================================ */

        .content-area {
            background: var(--card-white);
            padding: 56px 48px;
            border-radius: 24px;
            border: 2px solid rgba(208, 178, 140, 0.3);
            box-shadow: var(--shadow-lg);
            margin-bottom: 48px;
            animation: cardSlideUp 1s cubic-bezier(0.33, 1, 0.68, 1) 0.4s backwards;
            transition: all 0.5s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .content-area:hover {
            box-shadow: var(--shadow-xl);
        }

        section.detail-section {
            padding: 40px 0;
            margin-bottom: 32px;
            border-bottom: 2px solid rgba(208, 178, 140, 0.2);
        }

        section.detail-section:last-child {
            border-bottom: none;
        }

        section.detail-section h2 {
            font-size: 1.85rem;
            font-weight: 900;
            margin-bottom: 32px;
            color: var(--tan-darkest);
            position: relative;
            display: inline-block;
            letter-spacing: -0.02em;
        }

        section.detail-section h2::after {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-gold);
            border-radius: 2px;
        }

        section.detail-section p {
            line-height: 1.8;
            color: var(--text-medium);
            font-size: 1.05rem;
            margin-bottom: 14px;
        }

        section.detail-section p strong {
            color: var(--text-dark);
            font-weight: 700;
        }

        .icon-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .icon-list li {
            margin-bottom: 18px;
            padding: 18px 22px 18px 60px;
            position: relative;
            font-size: 1.02rem;
            line-height: 1.7;
            color: var(--text-medium);
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            border-radius: 14px;
            background: var(--glass-strong);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 2px solid var(--glass-border);
            box-shadow:
                0 2px 8px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .icon-list li:hover {
            color: var(--text-dark);
            background: rgba(255, 255, 255, 0.45);
            padding-left: 66px;
            box-shadow:
                0 4px 16px rgba(208, 178, 140, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .icon-list li i {
            position: absolute;
            left: 22px;
            top: 20px;
            font-size: 1.3rem;
            color: var(--accent-gold);
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .icon-list li:hover i {
            transform: scale(1.2) rotate(-8deg);
            filter: drop-shadow(0 2px 8px rgba(255, 184, 0, 0.5));
        }

        .map-container {
            margin-top: 36px;
            border-radius: 18px;
            overflow: hidden;
            border: 2px solid rgba(208, 178, 140, 0.3);
            box-shadow: var(--shadow-md);
        }

        .map-container iframe {
            width: 100%;
            height: 420px;
            border: 0;
            display: block;
        }

        /* ============================================
           LOGIN WARNING MODAL - Enhanced Blur
           ============================================ */

        #loginWarningModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(25px) brightness(0.92);
            -webkit-backdrop-filter: blur(25px) brightness(0.92);
            align-items: center;
            justify-content: center;
            animation: modalBackdrop 0.4s ease-out;
        }

        @keyframes modalBackdrop {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        #loginWarningModal.active {
            display: flex;
        }

        .login-warning-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            max-width: 500px;
            width: 90%;
            padding: 56px 48px;
            text-align: center;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
            animation: modalSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(80px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-warning-icon {
            width: 92px;
            height: 92px;
            margin: 0 auto 32px;
            background: rgba(255, 184, 0, 0.12);
            border: 3px solid var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 32px rgba(255, 184, 0, 0.3);
            animation: iconPulse 2.5s ease-in-out infinite;
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 32px rgba(255, 184, 0, 0.3);
            }

            50% {
                transform: scale(1.06);
                box-shadow: 0 0 48px rgba(255, 184, 0, 0.5);
            }
        }

        .login-warning-icon i {
            font-size: 3.2rem;
            color: var(--accent-gold-hover);
        }

        .login-warning-title {
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--tan-darkest);
            margin-bottom: 18px;
            letter-spacing: -0.02em;
        }

        .login-warning-text {
            font-size: 1.08rem;
            color: var(--text-medium);
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .login-warning-buttons {
            display: flex;
            gap: 14px;
            justify-content: center;
        }

        .btn-warning-login,
        .btn-warning-cancel {
            flex: 1;
            padding: 16px 32px;
            font-size: 1rem;
            font-weight: 800;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .btn-warning-login {
            background: var(--accent-gold);
            color: var(--tan-darkest);
            box-shadow: 0 4px 20px rgba(255, 184, 0, 0.35);
        }

        .btn-warning-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(255, 184, 0, 0.5);
        }

        .btn-warning-cancel {
            background: rgba(208, 178, 140, 0.15);
            color: var(--tan-darkest);
            border: 2px solid rgba(208, 178, 140, 0.3);
        }

        .btn-warning-cancel:hover {
            background: rgba(208, 178, 140, 0.25);
            border-color: rgba(208, 178, 140, 0.5);
        }

        /* ============================================
           BOOKING MODAL - Enhanced Blur
           ============================================ */

        #modal-booking {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(61, 47, 33, 0.45);
            backdrop-filter: blur(12px) brightness(0.85);
            -webkit-backdrop-filter: blur(12px) brightness(0.85);
            align-items: center;
            justify-content: center;
            padding: 25px 20px;
            animation: modalBackdrop 0.4s ease-out;
            overflow-y: auto;
        }

        #modal-booking.active {
            display: flex;
        }

        #modal-booking .booking-modal-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            max-width: 680px;
            width: 92%;
            max-height: 92vh;
            margin: auto;
            border-radius: 28px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
            animation: modalSlideIn 0.6s cubic-bezier(0.33, 1, 0.68, 1);
            display: flex;
            flex-direction: column;
        }

        .scroll-area-modal {
            width: 100%;
            max-height: calc(92vh - 60px);
            overflow-y: auto;
            padding: 56px 48px 48px;
            flex: 1;
        }

        .scroll-area-modal::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-area-modal::-webkit-scrollbar-track {
            background: rgba(208, 178, 140, 0.15);
            border-radius: 10px;
        }

        .scroll-area-modal::-webkit-scrollbar-thumb {
            background: var(--accent-gold);
            border-radius: 10px;
        }

        .scroll-area-modal::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gold-hover);
        }

        .booking-modal-box h3 {
            margin: 0 0 40px;
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--tan-darkest);
            text-align: center;
            letter-spacing: -0.02em;
        }

        .booking-form label {
            display: block;
            font-weight: 700;
            margin: 20px 0 10px;
            font-size: 0.88rem;
            color: var(--tan-darkest);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .booking-form input[type=text],
        .booking-form input[type=email],
        .booking-form input[type=date],
        .booking-form textarea,
        .booking-form input[type=file] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(208, 178, 140, 0.3);
            background: rgba(255, 255, 255, 0.65);
            color: var(--text-dark);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            font-family: 'Poppins', sans-serif;
        }

        .booking-form input:focus,
        .booking-form textarea:focus {
            outline: none;
            border-color: var(--accent-gold);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(255, 184, 0, 0.15);
        }

        .booking-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .booking-form .group-title {
            margin: 40px 0 28px;
            font-size: 1.3rem;
            color: var(--tan-darkest);
            font-weight: 900;
            letter-spacing: -0.01em;
            padding-bottom: 16px;
            border-bottom: 2px solid rgba(208, 178, 140, 0.25);
        }

        .booking-form .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 12px;
        }

        .booking-form .row>div {
            display: flex;
            flex-direction: column;
        }

        .booking-form .row label {
            display: block;
            font-weight: 700;
            margin: 0 0 10px;
            font-size: 0.88rem;
            color: var(--tan-darkest);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .booking-form .row input[type=text],
        .booking-form .row input[type=email],
        .booking-form .row input[type=date] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(208, 178, 140, 0.3);
            background: rgba(255, 255, 255, 0.65);
            color: var(--text-dark);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            font-family: 'Poppins', sans-serif;
        }

        .booking-form .btn-add,
        .booking-form .btn-rm {
            margin: 28px 0 12px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
            background: none;
            border: none;
            color: var(--accent-gold-hover);
            font-weight: 700;
            transition: all 0.25s cubic-bezier(0.33, 1, 0.68, 1);
            text-align: left;
            padding: 0;
        }

        .booking-form .btn-add:hover,
        .btn-rm:hover {
            color: var(--accent-gold);
            transform: translateX(6px);
        }

        .btn-main {
            margin-top: 36px;
            background: var(--accent-gold);
            color: var(--tan-darkest);
            border: none;
            border-radius: 14px;
            padding: 18px;
            width: 100%;
            font-weight: 900;
            font-size: 1.08rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(255, 184, 0, 0.35);
        }

        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(255, 184, 0, 0.5);
        }

        .btn-main:active {
            transform: translateY(-1px);
        }

        .btn-main:disabled {
            background: rgba(155, 138, 118, 0.5);
            cursor: not-allowed;
            opacity: 0.5;
            box-shadow: none;
        }

        .btn-cancel {
            margin-top: 16px;
            background: rgba(208, 178, 140, 0.15);
            color: var(--tan-darkest);
            border: 2px solid rgba(208, 178, 140, 0.3);
            border-radius: 14px;
            padding: 16px;
            width: 100%;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .btn-cancel:hover {
            background: rgba(208, 178, 140, 0.25);
            border-color: rgba(208, 178, 140, 0.5);
        }

        .booking-modal-box .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.3rem;
            background: rgba(208, 178, 140, 0.15);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(208, 178, 140, 0.3);
            color: var(--tan-dark);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .booking-modal-box .close-btn:hover {
            background: rgba(208, 178, 140, 0.25);
            border-color: rgba(208, 178, 140, 0.5);
            transform: rotate(90deg);
        }

        #modal-payment {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(25px) brightness(0.92);
            -webkit-backdrop-filter: blur(25px) brightness(0.92);
            align-items: center;
            justify-content: center;
        }

        #modal-payment>div {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            padding: 56px 48px;
            max-width: 540px;
            width: 95%;
            border-radius: 28px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            text-align: center;
            position: relative;
        }

        #modal-payment button {
            position: absolute;
            top: 24px;
            right: 24px;
            background: rgba(208, 178, 140, 0.15);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 2px solid rgba(208, 178, 140, 0.3);
            color: var(--tan-dark);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        #modal-payment button:hover {
            background: rgba(208, 178, 140, 0.25);
            transform: rotate(90deg);
        }

        #hasil-pembayaran {
            color: var(--text-dark);
            padding: 24px;
            font-size: 1.1rem;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */

        @media (max-width: 1024px) {
            .container {
                padding: 0 24px;
            }

            #modal-booking .booking-modal-box {
                max-width: 90%;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .hero {
                height: 100vh;
                min-height: 100vh;
                min-height: -webkit-fill-available;
            }

            .hero-content {
                padding: 0 6%;
            }

            .hero-text {
                font-size: clamp(2.2rem, 8vw, 3.5rem);
                margin-bottom: 24px;
            }

            .hero-subtitle {
                font-size: 0.75rem;
                margin-bottom: 16px;
            }

            .btn-hero-wrapper {
                margin-top: 32px;
                gap: 12px;
            }

            .btn-hero {
                padding: 16px 40px;
                font-size: 0.95rem;
                border-radius: 14px;
            }

            .info-bar {
                grid-template-columns: 1fr 1fr;
                padding: 30px 20px;
                gap: 20px;
            }

            .content-area {
                padding: 36px 24px;
            }

            .btn-main,
            .btn-hero {
                width: 100%;
            }

            #modal-booking {
                padding: 15px 10px;
            }

            #modal-booking .booking-modal-box {
                max-width: 95%;
                width: 95%;
                max-height: 95vh;
                border-radius: 20px;
            }

            .scroll-area-modal {
                padding: 40px 20px 32px;
                max-height: calc(95vh - 40px);
            }

            .booking-modal-box h3 {
                font-size: 1.5rem;
                margin-bottom: 28px;
            }

            .booking-form .group-title {
                font-size: 1.1rem;
                margin: 30px 0 20px;
            }

            .booking-form label {
                font-size: 0.8rem;
                margin: 15px 0 8px;
            }

            .booking-form input[type=text],
            .booking-form input[type=email],
            .booking-form input[type=date],
            .booking-form textarea,
            .booking-form input[type=file] {
                padding: 12px 14px;
                font-size: 0.95rem;
            }

            .booking-form .row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .booking-modal-box .close-btn {
                top: 15px;
                right: 15px;
                width: 38px;
                height: 38px;
                font-size: 1.1rem;
            }

            .btn-main,
            .btn-cancel {
                font-size: 0.95rem;
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 12px;
            }

            .hero {
                height: 100vh;
                min-height: 100vh;
                min-height: -webkit-fill-available;
            }

            .hero-content {
                padding: 0 5%;
            }

            .hero-text {
                font-size: clamp(1.8rem, 7vw, 2.5rem);
                margin-bottom: 20px;
                line-height: 1.1;
            }

            .hero-subtitle {
                font-size: 0.7rem;
                letter-spacing: 0.15em;
                margin-bottom: 14px;
            }

            .btn-hero-wrapper {
                margin-top: 28px;
                gap: 10px;
            }

            .btn-hero {
                padding: 14px 32px;
                font-size: 0.85rem;
                border-radius: 12px;
            }

            .btn-hero i {
                margin-right: 8px;
                font-size: 0.9rem;
            }

            .info-bar {
                gap: 16px;
                padding: 24px 16px;
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 28px 20px;
            }

            #modal-booking {
                padding: 10px 5px;
            }

            #modal-booking .booking-modal-box {
                max-width: 98%;
                width: 98%;
                max-height: 97vh;
                border-radius: 16px;
            }

            .scroll-area-modal {
                padding: 35px 16px 28px;
                max-height: calc(97vh - 30px);
            }

            .booking-modal-box h3 {
                font-size: 1.3rem;
                margin-bottom: 24px;
            }

            .booking-form .group-title {
                font-size: 1rem;
                margin: 25px 0 18px;
                padding-bottom: 12px;
            }

            .booking-form label {
                font-size: 0.75rem;
                margin: 12px 0 6px;
            }

            .booking-form input[type=text],
            .booking-form input[type=email],
            .booking-form input[type=date],
            .booking-form textarea,
            .booking-form input[type=file] {
                padding: 11px 12px;
                font-size: 0.9rem;
                border-radius: 10px;
            }

            .booking-form textarea {
                min-height: 80px;
            }

            .booking-modal-box .close-btn {
                top: 12px;
                right: 12px;
                width: 34px;
                height: 34px;
                font-size: 1rem;
            }

            .btn-main {
                font-size: 0.9rem;
                padding: 14px;
                margin-top: 28px;
            }

            .btn-cancel {
                font-size: 0.85rem;
                padding: 13px;
            }

            .btn-add,
            .btn-rm {
                font-size: 0.85rem;
            }

            .login-warning-container {
                max-width: 90%;
                padding: 40px 28px;
                border-radius: 20px;
            }

            .login-warning-icon {
                width: 72px;
                height: 72px;
                margin-bottom: 24px;
            }

            .login-warning-icon i {
                font-size: 2.5rem;
            }

            .login-warning-title {
                font-size: 1.5rem;
                margin-bottom: 14px;
            }

            .login-warning-text {
                font-size: 0.95rem;
                margin-bottom: 32px;
            }

            .login-warning-buttons {
                flex-direction: column;
            }

            .btn-warning-login,
            .btn-warning-cancel {
                width: 100%;
                padding: 14px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .hero-text {
                font-size: clamp(1.5rem, 6vw, 2rem);
            }

            .hero-subtitle {
                font-size: 0.65rem;
            }

            .btn-hero {
                padding: 13px 28px;
                font-size: 0.8rem;
            }

            .booking-modal-box h3 {
                font-size: 1.15rem;
            }

            .scroll-area-modal {
                padding: 30px 12px 24px;
            }

            .booking-form input[type=text],
            .booking-form input[type=email],
            .booking-form input[type=date],
            .booking-form textarea,
            .booking-form input[type=file] {
                padding: 10px 11px;
                font-size: 0.85rem;
            }

            .btn-main {
                font-size: 0.85rem;
                padding: 13px;
            }
        }

        /* ========== WHATSAPP BUTTON - CUTE & ANIMATED ========== */
        .whatsapp-container {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }

        .whatsapp-button {
            background: linear-gradient(135deg, #25d366 0%, #1ebe5b 100%);
            color: white;
            padding: 14px 22px;
            border-radius: 50px;
            border: none;
            cursor: pointer;

            display: flex;
            align-items: center;
            gap: 12px;

            font-family: "Poppins", Arial, sans-serif;
            font-size: 15px;
            font-weight: 600;

            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);

            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4),
                0 0 0 0 rgba(37, 211, 102, 0.7);

            position: relative;
            overflow: hidden;
            order: 2;
            /* Button di bawah */
        }

        /* Shimmer Effect */
        .whatsapp-button::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.3) 50%,
                    transparent 70%);
            transform: rotate(45deg);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {

            0%,
            100% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            50% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Pulse Ring Animation */
        .whatsapp-button::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 50px;
            border: 2px solid #25d366;
            transform: translate(-50%, -50%);
            animation: pulseRing 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            opacity: 0;
        }

        @keyframes pulseRing {
            0% {
                width: 100%;
                height: 100%;
                opacity: 0.8;
            }

            100% {
                width: 140%;
                height: 180%;
                opacity: 0;
            }
        }

        /* Icon Wrapper with Ping Dot */
        .whatsapp-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            z-index: 1;
        }

        .whatsapp-button i {
            font-size: 28px;
            animation: wobble 2s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }

        /* Cute Wobble Animation */
        @keyframes wobble {

            0%,
            100% {
                transform: rotate(0deg) scale(1);
            }

            15% {
                transform: rotate(-15deg) scale(1.1);
            }

            30% {
                transform: rotate(10deg) scale(1.05);
            }

            45% {
                transform: rotate(-10deg) scale(1.1);
            }

            60% {
                transform: rotate(5deg) scale(1);
            }

            75% {
                transform: rotate(-5deg) scale(1.05);
            }
        }

        /* Ping Dot (Online Indicator) */
        .ping-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            background: #ff4444;
            border: 2px solid white;
            border-radius: 50%;
            animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
            z-index: 3;
        }

        @keyframes ping {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.3);
                opacity: 0.8;
            }
        }

        /* WhatsApp Text */
        .whatsapp-text {
            position: relative;
            z-index: 1;
            white-space: nowrap;
        }

        /* Hover Effects */
        .whatsapp-button:hover {
            background: linear-gradient(135deg, #1ebe5b 0%, #128c42 100%);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(37, 211, 102, 0.6),
                0 0 0 8px rgba(37, 211, 102, 0.2);
        }

        .whatsapp-button:hover i {
            animation: shake 0.5s ease-in-out;
        }

        /* Shake Animation on Hover */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0) rotate(0deg);
            }

            25% {
                transform: translateX(-8px) rotate(-10deg);
            }

            75% {
                transform: translateX(8px) rotate(10deg);
            }
        }

        .whatsapp-button:active {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        }

        /* ========== TOOLTIP - SEKARANG DI ATAS ========== */
        .whatsapp-tooltip {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #333;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            font-family: "Poppins", Arial, sans-serif;

            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);

            border: 1px solid rgba(37, 211, 102, 0.2);

            opacity: 0;
            visibility: hidden;
            transform: translateY(10px) scale(0.9);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);

            position: relative;
            white-space: nowrap;
            order: 1;
            /* Tooltip di atas */
        }

        /* Tooltip Arrow - SEKARANG MENUNJUK KE BAWAH */
        .whatsapp-tooltip::after {
            content: "";
            position: absolute;
            bottom: -6px;
            /* Arrow di bawah tooltip */
            right: 40px;
            /* Posisi arrow */
            width: 12px;
            height: 12px;
            background: white;
            transform: rotate(45deg);
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.05);
            border-right: 1px solid rgba(37, 211, 102, 0.2);
            border-bottom: 1px solid rgba(37, 211, 102, 0.2);
        }

        /* Show tooltip on container hover */
        .whatsapp-container:hover .whatsapp-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        /* Bounce animation untuk tooltip */
        @keyframes tooltipBounce {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-5px) scale(1.02);
            }
        }

        .whatsapp-container:hover .whatsapp-tooltip {
            animation: tooltipBounce 0.6s ease-in-out;
        }

        /* ========== RESPONSIVE - TABLET ========== */
        @media (max-width: 1024px) {
            .whatsapp-container {
                bottom: 20px;
                right: 20px;
            }

            .whatsapp-button {
                padding: 12px 20px;
                font-size: 14px;
            }

            .whatsapp-button i {
                font-size: 26px;
            }

            .whatsapp-tooltip {
                font-size: 12px;
                padding: 9px 14px;
            }

            .whatsapp-tooltip::after {
                right: 35px;
            }
        }

        /* ========== RESPONSIVE - MOBILE ========== */
        @media (max-width: 768px) {
            .whatsapp-container {
                bottom: 15px;
                right: 15px;
            }

            /* Compact button on mobile - hide text initially */
            .whatsapp-button {
                padding: 12px;
                width: 56px;
                height: 56px;
                border-radius: 50%;
                justify-content: center;
            }

            .whatsapp-text {
                display: none;
            }

            .whatsapp-icon-wrapper {
                width: 28px;
                height: 28px;
            }

            .whatsapp-button i {
                font-size: 32px;
            }

            /* Expanded state when tapped */
            .whatsapp-button.expanded {
                width: auto;
                padding: 12px 18px;
                border-radius: 50px;
                gap: 10px;
            }

            .whatsapp-button.expanded .whatsapp-text {
                display: inline;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            /* Hide tooltip on mobile */
            .whatsapp-tooltip {
                display: none;
            }

            /* Adjust ping dot size */
            .ping-dot {
                width: 8px;
                height: 8px;
                top: -1px;
                right: -1px;
            }
        }

        /* ========== RESPONSIVE - SMALL MOBILE ========== */
        @media (max-width: 480px) {
            .whatsapp-container {
                bottom: 12px;
                right: 12px;
            }

            .whatsapp-button {
                width: 52px;
                height: 52px;
                padding: 10px;
            }

            .whatsapp-icon-wrapper {
                width: 26px;
                height: 26px;
            }

            .whatsapp-button i {
                font-size: 28px;
            }

            .whatsapp-button.expanded {
                padding: 10px 16px;
            }
        }

        /* ========== ACCESSIBILITY ========== */
        @media (prefers-reduced-motion: reduce) {

            .whatsapp-button,
            .whatsapp-button i,
            .whatsapp-button::before,
            .whatsapp-button::after,
            .ping-dot,
            .whatsapp-tooltip {
                animation: none !important;
                transition: opacity 0.2s ease !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .whatsapp-tooltip {
                background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
                color: #ffffff;
                border-color: rgba(37, 211, 102, 0.3);
            }

            .whatsapp-tooltip::after {
                background: #2d2d2d;
                border-right-color: rgba(37, 211, 102, 0.3);
                border-bottom-color: rgba(37, 211, 102, 0.3);
            }
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../auth-modals.php'; ?>

    <?php $heroSubtitle = "Extraordinary Adventure Awaits"; ?>

    <?php
    $imgPath = '../img/default-mountain.jpg';
    if (!empty($trip['gambar'])) {
        if (strpos($trip['gambar'], 'img/') === 0) {
            $imgPath = '../' . $trip['gambar'];
        } else {
            $imgPath = '../img/' . $trip['gambar'];
        }
    }
    $soldOut = ($trip['status'] !== 'available' || intval($trip['slot']) <= 0);
    ?>

    <section class="hero">
        <img src="<?= htmlspecialchars($imgPath) ?>" alt="Foto Gunung <?= htmlspecialchars($trip['nama_gunung']) ?>" />
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-subtitle"><?= htmlspecialchars($heroSubtitle) ?></p>
            <div class="hero-text"><?= htmlspecialchars($trip['nama_gunung']) ?></div>
            <div class="btn-hero-wrapper">
                <?php if ($soldOut): ?>
                    <button class="btn-hero" type="button" disabled>
                        <i class="bi bi-x-circle"></i> Sold Out
                    </button>
                <?php else: ?>
                    <button class="btn-hero" type="button" onclick="bookTripModal()">
                        <i class="bi bi-calendar-check"></i> Daftar Sekarang
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="container">
        <nav class="info-bar">
            <div class="info-item">
                <i class="bi bi-calendar-event"></i>
                <span>Tanggal</span>
                <span><?= date('d M Y', strtotime($trip['tanggal'])) ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-clock"></i>
                <span>Durasi</span>
                <span><?= htmlspecialchars($trip['durasi']) ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-people-fill"></i>
                <span>Slot Tersisa</span>
                <span><?= htmlspecialchars($trip['slot']) ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-currency-dollar"></i>
                <span>Harga Mulai</span>
                <span>Rp <?= number_format($trip['harga'], 0, ',', '.') ?></span>
            </div>
        </nav>

        <div class="content-area">
            <?php
            if ($isLogin) {
                $id_booking_pending = null;
                $stmtBP = $conn->prepare("SELECT id_booking FROM payments WHERE status_pembayaran='pending' AND id_booking IN (SELECT id_booking FROM bookings WHERE id_user=?) ORDER BY id_payment DESC LIMIT 1");
                $stmtBP->bind_param("i", $_SESSION['id_user']);
                $stmtBP->execute();
                $stmtBP->bind_result($id_booking_pending);
                $stmtBP->fetch();
                $stmtBP->close();
            }
            ?>

            <section class="detail-section">
                <h2>Meeting Point</h2>
                <p><strong>Nama Lokasi:</strong> <?= htmlspecialchars($detail['nama_lokasi']) ?></p>
                <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($detail['alamat'])) ?></p>
                <p><strong>Waktu Kumpul:</strong> <?= htmlspecialchars($detail['waktu_kumpul']) ?></p>
                <?php if (!empty($detail['link_map'])): ?>
                    <div class="map-container">
                        <?php
                        $linkMap = trim($detail['link_map']);
                        if (!$linkMap) {
                            echo '<p><em>Belum ada link Google Map</em></p>';
                        } elseif (strpos($linkMap, '/maps/embed?') !== false) {
                            echo '<iframe src="' . htmlspecialchars($linkMap) . '" allowfullscreen loading="lazy"></iframe>';
                        } elseif (preg_match('#^https://(www\.)?google\.(com|co\.id)/maps/#', $linkMap)) {
                            $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
                            echo '<iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
                        } else {
                            echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" style="color:var(--accent-gold-hover);">Buka Google Maps</a></p>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="detail-section">
                <h2>Include</h2>
                <?= createIconList($detail['include'], 'bi bi-check-circle-fill') ?>
            </section>

            <section class="detail-section">
                <h2>Exclude</h2>
                <?= createIconList($detail['exclude'], 'bi bi-x-octagon-fill') ?>
            </section>

            <section class="detail-section">
                <h2>Syarat & Ketentuan</h2>
                <?= createIconList($detail['syaratKetentuan'], 'bi bi-exclamation-triangle-fill') ?>
            </section>
        </div>
    </div>

    <div id="loginWarningModal">
        <div class="login-warning-container">
            <div class="login-warning-icon">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <h2 class="login-warning-title">Login Diperlukan</h2>
            <p class="login-warning-text">Silakan login terlebih dahulu untuk melakukan booking.</p>
            <div class="login-warning-buttons">
                <button class="btn-warning-login" onclick="openLoginFromWarning()">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
                <button class="btn-warning-cancel" onclick="closeLoginWarning()">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </div>
    </div>

    <div id="modal-booking">
        <div class="booking-modal-box">
            <button class="close-btn" onclick="closeBooking()"><i class="bi bi-x-lg"></i></button>
            <div class="scroll-area-modal">
                <?php if (!$isLogin): ?>
                    <h3>Login Diperlukan</h3>
                    <p>Silakan login untuk mendaftar.</p>
                    <a href="login.php" class="btn-main">Login</a>
                    <button class="btn-cancel" type="button" onclick="closeBooking()">Tutup</button>
                <?php else: ?>
                    <form class="booking-form" id="form-book-trip" enctype="multipart/form-data">
                        <h3>Form Pendaftaran Trip</h3>
                        <input type="hidden" name="id_trip" value="<?= $trip['id_trip'] ?>" />
                        <input type="hidden" name="jumlah_peserta" id="jumlah-peserta" value="1" />

                        <div class="group-title">Data Diri Anda</div>
                        <div class="row">
                            <div>
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama[]" required value="<?= htmlspecialchars($userLogin['username'] ?? '') ?>" />
                            </div>
                            <div>
                                <label>Email</label>
                                <input type="email" name="email[]" required value="<?= htmlspecialchars($userLogin['email'] ?? '') ?>" />
                            </div>
                        </div>

                        <div class="row">
                            <div>
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir[]" required />
                            </div>
                            <div>
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir[]" />
                            </div>
                        </div>

                        <label>NIK</label>
                        <input type="text" name="nik[]" maxlength="20" />

                        <div class="row">
                            <div>
                                <label>No. WA</label>
                                <input type="text" name="no_wa[]" required value="<?= htmlspecialchars($userLogin['no_wa'] ?? '') ?>" />
                            </div>
                            <div>
                                <label>No. Darurat</label>
                                <input type="text" name="no_wa_darurat[]" />
                            </div>
                        </div>

                        <label>Alamat</label>
                        <textarea name="alamat[]" required><?= htmlspecialchars($userLogin['alamat'] ?? '') ?></textarea>

                        <label>Riwayat Penyakit</label>
                        <input type="text" name="riwayat_penyakit[]" maxlength="60" />

                        <label>Foto KTP</label>
                        <input type="file" name="foto_ktp[]" accept="image/*" />

                        <div id="extra-participants"></div>
                        <button class="btn-add" type="button" onclick="addPeserta()">+ Tambah Peserta</button>

                        <button type="submit" class="btn-main">Daftar & Booking</button>
                        <button type="button" class="btn-cancel" onclick="closeBooking()">Batal</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modal-payment">
        <div>
            <button onclick="closePayment()"><i class="bi bi-x-lg"></i></button>
            <div id="hasil-pembayaran"></div>
        </div>
    </div>

    <!-- whatsapp -->
    <div class="whatsapp-container" data-aos="zoom-in" data-aos-delay="500">
        <button class="whatsapp-button" id="whatsappBtn" onclick="bukaWhatsapp()">
            <div class="whatsapp-icon-wrapper">
                <i class="fab fa-whatsapp"></i>
                <span class="ping-dot"></span>
            </div>
            <span class="whatsapp-text">Chat WhatsApp</span>
        </button>
        <div class="whatsapp-tooltip">Ada yang bisa kami bantu? 💬</div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        function showLoginWarning() {
            document.getElementById('loginWarningModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginWarning() {
            document.getElementById('loginWarningModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function openLoginFromWarning() {
            closeLoginWarning();
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.style.display = 'flex';
                loginModal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function bookTripModal() {
            <?php if (!$isLogin): ?>
                showLoginWarning();
                return;
            <?php endif; ?>
            document.getElementById('modal-booking').classList.add('active');
            document.querySelector('.scroll-area-modal').scrollTop = 0;
        }

        function closeBooking() {
            document.getElementById('modal-booking').classList.remove('active');
        }

        function updateJumlah() {
            document.getElementById('jumlah-peserta').value = document.querySelectorAll('.peserta-baru').length + 1;
        }

        <?php if ($isLogin): ?>
            document.getElementById('form-book-trip').onsubmit = async function(e) {
                e.preventDefault();
                const data = new FormData(e.target);

                try {
                    let res = await fetch('../backend/booking-api.php', {
                        method: 'POST',
                        body: data
                    });

                    let json = await res.json();

                    if (json.success && json.id_booking) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: json.message,
                            icon: 'success',
                            background: 'rgba(255, 255, 255, 0.95)',
                            color: '#3D2F21',
                            confirmButtonColor: '#FFB800'
                        });
                        closeBooking();
                        setTimeout(() => openPayment(json.id_booking), 1100);
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: json.message || 'Terjadi kesalahan',
                            icon: 'error',
                            background: 'rgba(255, 255, 255, 0.95)',
                            color: '#3D2F21',
                            confirmButtonColor: '#FFB800'
                        });
                    }
                } catch (err) {
                    console.error('Error:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem: ' + err.message,
                        icon: 'error',
                        background: 'rgba(255, 255, 255, 0.95)',
                        color: '#3D2F21',
                        confirmButtonColor: '#FFB800'
                    });
                }
            };
        <?php endif; ?>

        function openPayment(id) {
            document.getElementById('modal-payment').style.display = 'flex';
            document.getElementById('hasil-pembayaran').innerHTML = "⏳ Memproses pembayaran...";

            fetch('../backend/payment-api.php?booking=' + id)
                .then(r => {
                    const contentType = r.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server mengembalikan HTML bukan JSON. Periksa error di backend.');
                    }
                    return r.json();
                })
                .then(resp => {
                    if (resp.snap_token) {
                        window.snap.pay(resp.snap_token, {
                            onSuccess: (result) => {
                                document.getElementById('hasil-pembayaran').innerHTML = "✅ Pembayaran Berhasil! Mengecek status...";

                                fetch('../backend/check-payment-status.php?order_id=' + resp.order_id)
                                    .then(r => r.json())
                                    .then(statusResp => {
                                        if (statusResp.status === 'paid') {
                                            setTimeout(() => {
                                                closePayment();
                                                Swal.fire({
                                                    title: 'Pembayaran Berhasil!',
                                                    text: 'Booking Anda telah dikonfirmasi.',
                                                    icon: 'success',
                                                    background: 'rgba(255, 255, 255, 0.95)',
                                                    color: '#3D2F21',
                                                    confirmButtonColor: '#FFB800'
                                                }).then(() => {
                                                    window.location.reload();
                                                });
                                            }, 1000);
                                        } else {
                                            document.getElementById('hasil-pembayaran').innerHTML = "⚠️ Status: " + statusResp.status;
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Status check error:', err);
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    });
                            },
                            onPending: (result) => {
                                document.getElementById('hasil-pembayaran').innerHTML = "⏳ Menunggu Pembayaran...";
                                setTimeout(() => {
                                    closePayment();
                                    Swal.fire({
                                        title: 'Pembayaran Pending',
                                        text: 'Silakan selesaikan pembayaran Anda.',
                                        icon: 'info',
                                        background: 'rgba(255, 255, 255, 0.95)',
                                        color: '#3D2F21',
                                        confirmButtonColor: '#FFB800'
                                    });
                                }, 2000);
                            },
                            onError: (result) => {
                                document.getElementById('hasil-pembayaran').innerHTML = "❌ Pembayaran Gagal!";
                                setTimeout(() => {
                                    closePayment();
                                    Swal.fire({
                                        title: 'Pembayaran Gagal',
                                        text: result.status_message || 'Terjadi kesalahan',
                                        icon: 'error',
                                        background: 'rgba(255, 255, 255, 0.95)',
                                        color: '#3D2F21',
                                        confirmButtonColor: '#FFB800'
                                    });
                                }, 2000);
                            },
                            onClose: () => {
                                document.getElementById('hasil-pembayaran').innerHTML = "⚠️ Popup Ditutup";
                                setTimeout(() => {
                                    closePayment();
                                }, 1500);
                            }
                        });
                    } else {
                        throw new Error(resp.error || 'Gagal mendapatkan Snap Token');
                    }
                })
                .catch(err => {
                    console.error('Payment error:', err);
                    document.getElementById('hasil-pembayaran').innerHTML = '❌ Error: ' + err.message;

                    setTimeout(() => {
                        closePayment();
                        Swal.fire({
                            title: 'Error Pembayaran',
                            text: err.message,
                            icon: 'error',
                            background: 'rgba(255, 255, 255, 0.95)',
                            color: '#3D2F21',
                            confirmButtonColor: '#FFB800'
                        });
                    }, 2000);
                });
        }

        function closePayment() {
            document.getElementById('modal-payment').style.display = 'none';
        }

        const slotTersedia = <?= intval($trip['slot']) ?>;

        function addPeserta() {
            const jumlahPesertaSaatIni = document.querySelectorAll('#extra-participants .peserta-baru').length + 1;

            if (jumlahPesertaSaatIni >= slotTersedia) {
                const warningDiv = document.getElementById('slot-warning');
                if (!warningDiv) {
                    const warning = document.createElement('div');
                    warning.id = 'slot-warning';
                    warning.style.cssText = `
                background: rgba(255, 71, 87, 0.15);
                border: 2px solid rgba(255, 71, 87, 0.4);
                border-radius: 12px;
                padding: 16px 20px;
                margin: 20px 0 12px;
                color: #C92A2A;
                font-weight: 700;
                font-size: 0.95rem;
                display: flex;
                align-items: center;
                gap: 12px;
                animation: shake 0.5s ease;
            `;
                    warning.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.4rem; color: #FF4757;"></i>
                <span>⚠️ Slot tidak cukup! Hanya tersisa <strong>${slotTersedia} slot</strong> untuk trip ini.</span>
            `;

                    const btnAdd = document.querySelector('.btn-add');
                    btnAdd.parentNode.insertBefore(warning, btnAdd);

                    setTimeout(() => {
                        warning.style.transition = 'all 0.3s ease-out';
                        warning.style.opacity = '0';
                        warning.style.transform = 'translateY(-10px)';
                        setTimeout(() => warning.remove(), 300);
                    }, 5000);
                }

                return;
            }

            const id = jumlahPesertaSaatIni + 1;
            const div = document.createElement('div');
            div.className = 'peserta-baru';
            div.innerHTML = `
        <div class="group-title">Peserta #${id}</div>
        <div class="row">
            <div><label>Nama</label><input type="text" name="nama[]" required /></div>
            <div><label>Email</label><input type="email" name="email[]" required /></div>
        </div>
        <div class="row">
            <div><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir[]" required /></div>
            <div><label>Tempat Lahir</label><input type="text" name="tempat_lahir[]" /></div>
        </div>
        <label>NIK</label><input type="text" name="nik[]" />
        <div class="row">
            <div><label>No. WA</label><input type="text" name="no_wa[]" required /></div>
            <div><label>No. Darurat</label><input type="text" name="no_wa_darurat[]" /></div>
        </div>
        <label>Alamat</label><textarea name="alamat[]" required></textarea>
        <label>Riwayat Penyakit</label><input type="text" name="riwayat_penyakit[]" />
        <label>Foto KTP</label><input type="file" name="foto_ktp[]" accept="image/*" />
        <button class="btn-rm" type="button" onclick="this.parentElement.remove();updateJumlah();">Hapus</button>
    `;
            document.getElementById('extra-participants').appendChild(div);
            updateJumlah();

            const existingWarning = document.getElementById('slot-warning');
            if (existingWarning) {
                existingWarning.remove();
            }
        }
    </script>
</body>

</html>