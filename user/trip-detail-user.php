<?php
require_once '../backend/koneksi.php';
session_start();

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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes" />
    <title><?= htmlspecialchars($trip['nama_gunung']) ?> | Majelis MDPL</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-tan: #D0B28C;
            --tan-dark: #B89968;
            --tan-darker: #A08456;
            --tan-darkest: #846A43;
            --tan-light: #E0C9A8;
            --tan-lighter: #EBD9BD;
            --tan-pale: #F5EAD8;
            --card-white: #F8F4EE;
            --card-cream: #F2EDE5;
            --accent-gold: #FFB800;
            --accent-gold-hover: #E6A600;
            --white: #FFFFFF;
            --text-dark: #3D2F21;
            --text-medium: #6B5847;
            --text-light: #9B8A76;
            --glass-strong: rgba(255, 255, 255, 0.35);
            --glass-medium: rgba(255, 255, 255, 0.25);
            --glass-light: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.4);
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
            padding: 0 1rem;
            position: relative;
            z-index: 1;
        }

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
            padding: 0 2rem;
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
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 1rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .hero-text {
            font-size: clamp(2rem, 8vw, 5.5rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 0.95;
            margin-bottom: 2rem;
            color: var(--white);
            text-shadow:
                0 4px 16px rgba(0, 0, 0, 0.5),
                0 12px 48px rgba(0, 0, 0, 0.3);
        }

        .btn-hero-wrapper {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            position: relative;
            background: var(--accent-gold);
            color: var(--tan-darkest);
            padding: 1rem 2rem;
            font-weight: 800;
            font-size: clamp(0.8rem, 2vw, 1rem);
            border-radius: 12px;
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
            transform: translateY(-3px);
            box-shadow:
                0 12px 40px rgba(255, 184, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .btn-hero:disabled {
            background: rgba(155, 138, 118, 0.5);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-hero i {
            margin-right: 0.5rem;
        }

        .info-bar {
            background: var(--card-white);
            border: 2px solid rgba(208, 178, 140, 0.3);
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
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
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .info-item {
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
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
            transform: scale(1.03);
            box-shadow:
                0 8px 24px rgba(208, 178, 140, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .info-item i {
            font-size: 1.75rem;
            color: var(--accent-gold);
            filter: drop-shadow(0 4px 12px rgba(255, 184, 0, 0.4));
            transition: all 0.4s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .info-item:hover i {
            transform: scale(1.1) rotate(-5deg);
            filter: drop-shadow(0 8px 20px rgba(255, 184, 0, 0.6));
        }

        .info-item span:first-of-type {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-medium);
            font-weight: 700;
        }

        .info-item span:last-child {
            font-weight: 800;
            font-size: clamp(0.9rem, 2vw, 1.2rem);
            color: var(--text-dark);
        }

        .content-area {
            background: var(--card-white);
            padding: 1.5rem;
            border-radius: 1.5rem;
            border: 2px solid rgba(208, 178, 140, 0.3);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            animation: cardSlideUp 1s cubic-bezier(0.33, 1, 0.68, 1) 0.4s backwards;
            transition: all 0.5s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .content-area:hover {
            box-shadow: var(--shadow-xl);
        }

        section.detail-section {
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(208, 178, 140, 0.2);
        }

        section.detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        section.detail-section h2 {
            font-size: clamp(1.2rem, 4vw, 1.75rem);
            font-weight: 900;
            margin-bottom: 1rem;
            color: var(--tan-darkest);
            position: relative;
            display: inline-block;
            letter-spacing: -0.02em;
        }

        section.detail-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-gold);
            border-radius: 2px;
        }

        section.detail-section p {
            line-height: 1.7;
            color: var(--text-medium);
            font-size: clamp(0.85rem, 2vw, 1rem);
            margin-bottom: 1rem;
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
            margin-bottom: 1rem;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            position: relative;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            line-height: 1.6;
            color: var(--text-medium);
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            border-radius: 12px;
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
            padding-left: 3.25rem;
            box-shadow:
                0 4px 16px rgba(208, 178, 140, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .icon-list li i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            color: var(--accent-gold);
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .icon-list li:hover i {
            transform: translateY(-50%) scale(1.15) rotate(-8deg);
            filter: drop-shadow(0 2px 8px rgba(255, 184, 0, 0.5));
        }

        .map-container {
            margin-top: 1rem;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid rgba(208, 178, 140, 0.3);
            box-shadow: var(--shadow-md);
        }

        .map-container iframe {
            width: 100%;
            height: clamp(250px, 50vw, 350px);
            border: 0;
            display: block;
        }

        /* LOGIN WARNING MODAL */

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
            padding: 1rem;
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
            border-radius: 1.5rem;
            max-width: 450px;
            width: 100%;
            padding: clamp(1.5rem, 5vw, 2.5rem);
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
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
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
            font-size: 2.5rem;
            color: var(--accent-gold-hover);
        }

        .login-warning-title {
            font-size: clamp(1.3rem, 4vw, 1.7rem);
            font-weight: 900;
            color: var(--tan-darkest);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .login-warning-text {
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: var(--text-medium);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .login-warning-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-warning-login,
        .btn-warning-cancel {
            flex: 1;
            min-width: 120px;
            padding: 0.8rem 1.5rem;
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            font-weight: 800;
            border-radius: 10px;
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

        /* BOOKING MODAL - FIXED HEIGHT UNTUK SEMUA DEVICE */

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
            padding: 1rem;
            animation: modalBackdrop 0.4s ease-out;
        }

        #modal-booking.active {
            display: flex;
        }

        #modal-booking .booking-modal-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            width: 100%;
            max-width: 550px;
            border-radius: 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.6);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
            animation: modalSlideIn 0.6s cubic-bezier(0.33, 1, 0.68, 1);
            display: flex;
            flex-direction: column;
            max-height: 85vh;
            height: 85vh;
        }

        .booking-modal-header {
            padding: 1.5rem;
            border-bottom: 2px solid rgba(208, 178, 140, 0.2);
            flex-shrink: 0;
        }

        .booking-modal-box h3 {
            margin: 0;
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            font-weight: 900;
            color: var(--tan-darkest);
            text-align: center;
            letter-spacing: -0.02em;
        }

        .booking-form-container {
            width: 100%;
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .form-slide-wrapper {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.5s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .form-slide {
            width: 100%;
            height: 100%;
            flex-shrink: 0;
            padding: 1.5rem;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .form-slide::-webkit-scrollbar {
            width: 8px;
        }

        .form-slide::-webkit-scrollbar-track {
            background: rgba(208, 178, 140, 0.1);
            border-radius: 10px;
        }

        .form-slide::-webkit-scrollbar-thumb {
            background: rgba(208, 178, 140, 0.4);
            border-radius: 10px;
        }

        .form-slide::-webkit-scrollbar-thumb:hover {
            background: rgba(208, 178, 140, 0.6);
        }

        .group-title {
            margin: 0 0 1.25rem 0;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            color: var(--tan-darkest);
            font-weight: 800;
            letter-spacing: 0.05em;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(208, 178, 140, 0.25);
            text-transform: uppercase;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-control {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .form-control label {
            display: block;
            font-weight: 700;
            font-size: 0.65rem;
            color: var(--tan-darkest);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .form-control input[type=text],
        .form-control input[type=email],
        .form-control input[type=date],
        .form-control textarea,
        .form-control input[type=file] {
            width: 100%;
            padding: 0.65rem 0.8rem;
            border: 2px solid rgba(208, 178, 140, 0.35);
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-dark);
            border-radius: 8px;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        .form-control input:focus,
        .form-control textarea:focus {
            outline: none;
            border-color: var(--accent-gold);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(255, 184, 0, 0.1);
        }

        .form-control textarea {
            resize: none;
            min-height: 60px;
        }

        .form-control input[type=file] {
            padding: 0.5rem 0.7rem;
            cursor: pointer;
            font-size: 0.75rem;
        }

        /* SLIDE CONTROLS */

        .slide-controls {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
            padding: 1rem 1.25rem;
            border-top: 2px solid rgba(208, 178, 140, 0.2);
            background: rgba(245, 234, 216, 0.5);
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .slide-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(208, 178, 140, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .slide-dot.active {
            background: var(--accent-gold);
            width: 24px;
            border-radius: 3px;
            box-shadow: 0 0 10px rgba(255, 184, 0, 0.4);
        }

        .slide-nav-btn {
            background: rgba(255, 184, 0, 0.15);
            border: 2px solid rgba(255, 184, 0, 0.3);
            color: var(--accent-gold);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .slide-nav-btn:hover:not(:disabled) {
            background: rgba(255, 184, 0, 0.3);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(255, 184, 0, 0.3);
        }

        .slide-nav-btn:disabled {
            opacity: 0.25;
            cursor: not-allowed;
        }

        .slide-counter {
            font-size: 0.75rem;
            color: var(--text-medium);
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            background: rgba(208, 178, 140, 0.1);
            border-radius: 5px;
            min-width: 50px;
            text-align: center;
        }

        .peserta-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-left: auto;
        }

        .btn-peserta-add,
        .btn-peserta-remove {
            background: rgba(255, 184, 0, 0.15);
            border: 2px solid rgba(255, 184, 0, 0.3);
            color: var(--accent-gold);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-weight: bold;
            font-size: 0.9rem;
            padding: 0;
        }

        .btn-peserta-add:hover {
            background: rgba(76, 175, 80, 0.2);
            border-color: rgba(76, 175, 80, 0.5);
            color: #4CAF50;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-peserta-remove:hover:not(:disabled) {
            background: rgba(244, 67, 54, 0.2);
            border-color: rgba(244, 67, 54, 0.5);
            color: #f44336;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        .btn-peserta-remove:disabled {
            opacity: 0.25;
            cursor: not-allowed;
        }

        .peserta-label {
            font-size: 0.7rem;
            color: var(--text-medium);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        /* FORM BUTTONS */

        .form-buttons {
            display: flex;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-top: 2px solid rgba(208, 178, 140, 0.2);
            background: rgba(245, 234, 216, 0.3);
            flex-shrink: 0;
            justify-content: center;
        }

        .btn-main {
            margin: 0;
            border: none;
            border-radius: 8px;
            padding: 0.9rem 1.8rem;
            font-weight: 800;
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-height: 42px;
            background: var(--accent-gold);
            color: var(--tan-darkest);
            box-shadow: 0 4px 16px rgba(255, 184, 0, 0.3);
            flex: 1;
        }

        .btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 184, 0, 0.4);
        }

        .btn-main:active {
            transform: translateY(0);
        }

        .btn-main:disabled {
            background: rgba(155, 138, 118, 0.5);
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }

        .btn-main i {
            font-size: 0.9rem;
        }

        .booking-modal-box .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1rem;
            background: rgba(208, 178, 140, 0.15);
            width: 36px;
            height: 36px;
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

        /* PAYMENT MODAL */

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
            padding: 1rem;
        }

        #modal-payment>div {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            padding: clamp(1.5rem, 4vw, 3rem);
            max-width: 500px;
            width: 100%;
            border-radius: 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.6);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            text-align: center;
            position: relative;
        }

        #modal-payment button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(208, 178, 140, 0.15);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(208, 178, 140, 0.3);
            color: var(--tan-dark);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.33, 1, 0.68, 1);
        }

        #modal-payment button:hover {
            background: rgba(208, 178, 140, 0.25);
            transform: rotate(90deg);
        }

        #hasil-pembayaran {
            color: var(--text-dark);
            padding: 1.5rem;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        /* === WHATSAPP BUTTON === */
        /* === WHATSAPP BUTTON === */
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

        @keyframes shimmer {

            0%,
            100% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            50% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
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

        .ping-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
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


        @keyframes shake {

            0%,
            100% {
                transform: translateX(0) rotate(0deg);
            }

            25% {
                transform: translateX(-6px) rotate(-10deg);
            }

            75% {
                transform: translateX(6px) rotate(10deg);
            }
        }

        @keyframes tooltipBounce {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-4px) scale(1.02);
            }
        }

        /* ==================== RESPONSIVE BREAKPOINTS ==================== */

        @media (max-width: 992px) {
            .container {
                margin-top: 70px;
            }

            .info-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 65px;
                padding: 0 0.75rem;
            }

            .booking-modal-header {
                padding: 1.25rem;
            }

            .booking-modal-box h3 {
                font-size: 1.15rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }

            .info-bar {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 1rem;
            }

            .info-item {
                padding: 0.75rem;
            }

            .content-area {
                padding: 1rem;
            }

            .map-container iframe {
                height: 280px;
            }

            .form-slide {
                padding: 1.25rem;
            }

            .slide-controls {
                padding: 0.85rem 1rem;
                gap: 0.6rem;
            }

            .form-buttons {
                padding: 0.85rem 1rem;
                gap: 0.6rem;
            }

            .peserta-controls {
                margin-left: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 0.5rem;
            }

            .hero-content {
                padding: 0 1rem;
            }

            #modal-booking .booking-modal-box {
                max-height: 90vh;
                height: 90vh;
            }

            .booking-modal-header {
                padding: 1rem;
            }

            .booking-modal-box h3 {
                font-size: 1rem;
            }

            .form-slide {
                padding: 1rem;
            }

            .form-row {
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .group-title {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .form-control label {
                font-size: 0.6rem;
            }

            .form-control input,
            .form-control textarea {
                font-size: 0.8rem;
                padding: 0.6rem 0.7rem;
            }

            .form-control textarea {
                min-height: 55px;
            }

            .slide-controls {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .form-buttons {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .btn-main {
                padding: 0.8rem 1.5rem;
            }

            .slide-counter {
                font-size: 0.7rem;
                padding: 0.35rem 0.6rem;
            }

            .peserta-label {
                font-size: 0.65rem;
            }

            .info-bar {
                padding: 0.75rem;
                gap: 0.6rem;
                border-radius: 1rem;
            }

            .info-item {
                padding: 0.6rem;
            }

            .info-item i {
                font-size: 1.4rem;
            }

            .content-area {
                padding: 0.75rem;
                border-radius: 1rem;
            }

            section.detail-section {
                padding: 1rem 0;
                margin-bottom: 1rem;
            }

            section.detail-section h2 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            section.detail-section h2::after {
                width: 40px;
                height: 2px;
                bottom: -8px;
            }

            .icon-list li {
                margin-bottom: 0.75rem;
                padding: 0.7rem 0.85rem 0.7rem 2.3rem;
                font-size: 0.8rem;
            }

            .icon-list li i {
                font-size: 0.95rem;
                left: 0.85rem;
            }

            .map-container iframe {
                height: 250px;
            }
        }

        @media (max-width: 360px) {
            .container {
                padding: 0 0.4rem;
            }

            #modal-booking .booking-modal-box {
                max-height: 92vh;
            }

            .form-slide {
                padding: 0.85rem;
            }

            .form-row {
                gap: 0.4rem;
                margin-bottom: 0.4rem;
            }

            .form-control input,
            .form-control textarea {
                font-size: 0.75rem;
                padding: 0.55rem 0.65rem;
            }

            .btn-main {
                font-size: 0.7rem;
                padding: 0.7rem 1.2rem;
            }

            .info-bar {
                grid-template-columns: 1fr;
            }

            .slide-dot.active {
                width: 20px;
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

    <!-- LOGIN WARNING MODAL -->
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

    <!-- BOOKING MODAL -->
    <div id="modal-booking">
        <div class="booking-modal-box">
            <button class="close-btn" onclick="closeBooking()"><i class="bi bi-x-lg"></i></button>
            <div class="booking-modal-header">
                <h3>Form Pendaftaran Trip</h3>
            </div>

            <div class="booking-form-container">
                <div class="form-slide-wrapper" id="formSlideWrapper">
                    <!-- FORM UTAMA (Peserta 1) -->
                    <div class="form-slide">
                        <form class="booking-form" id="form-book-trip" enctype="multipart/form-data" style="display: none;">
                            <input type="hidden" name="id_trip" value="<?= $trip['id_trip'] ?>" />
                            <input type="hidden" name="jumlah_peserta" id="jumlah-peserta" value="1" />
                        </form>

                        <div class="group-title">Data Diri Anda</div>

                        <div class="form-row">
                            <div class="form-control">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama[]" required value="<?= htmlspecialchars($userLogin['username'] ?? '') ?>" />
                            </div>
                            <div class="form-control">
                                <label>Email</label>
                                <input type="email" name="email[]" required value="<?= htmlspecialchars($userLogin['email'] ?? '') ?>" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-control">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir[]" required />
                            </div>
                            <div class="form-control">
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir[]" />
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-control">
                                <label>NIK</label>
                                <input type="text" name="nik[]" maxlength="20" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-control">
                                <label>No. WA</label>
                                <input type="text" name="no_wa[]" required value="<?= htmlspecialchars($userLogin['no_wa'] ?? '') ?>" />
                            </div>
                            <div class="form-control">
                                <label>No. Darurat</label>
                                <input type="text" name="no_wa_darurat[]" />
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-control">
                                <label>Alamat</label>
                                <textarea name="alamat[]" required><?= htmlspecialchars($userLogin['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-control">
                                <label>Riwayat Penyakit</label>
                                <input type="text" name="riwayat_penyakit[]" maxlength="60" />
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-control">
                                <label>Foto KTP</label>
                                <input type="file" name="foto_ktp[]" accept="image/*" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slide-controls">
                <button class="slide-nav-btn" id="prevBtn" onclick="prevSlide()" disabled>
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div id="slideDots" style="display: flex; gap: 5px;"></div>
                <button class="slide-nav-btn" id="nextBtn" onclick="nextSlide()">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <span class="slide-counter"><span id="slideCounter">1</span>/<span id="totalSlides">1</span></span>

                <div class="peserta-controls">
                    <button type="button" class="btn-peserta-remove" id="btnRemovePeserta" onclick="removePeserta()" disabled>
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <span class="peserta-label">PESERTA</span>
                    <button type="button" class="btn-peserta-add" onclick="addPeserta()">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>

            <div class="form-buttons">
                <button type="button" class="btn-main" id="submitBtn" onclick="if(currentSlide === totalPeserta - 1) submitForm(); else nextSlide();">
                    <i class="bi bi-check-circle"></i> <span id="btnText">Daftar & Booking</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div id="modal-payment">
        <div>
            <button onclick="closePayment()"><i class="bi bi-x-lg"></i></button>
            <div id="hasil-pembayaran"></div>
        </div>
    </div>

    <!-- WHATSAPP BUTTON -->
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
        let currentSlide = 0;
        let totalPeserta = 1;
        const slotTersedia = <?= intval($trip['slot']) ?>;

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
            currentSlide = 0;
            totalPeserta = 1;
            updateSlideUI();
            document.body.style.overflow = 'hidden';
        }

        function closeBooking() {
            document.getElementById('modal-booking').classList.remove('active');
            currentSlide = 0;
            totalPeserta = 1;
            resetForm();
            document.body.style.overflow = '';
        }

        function resetForm() {
            const wrapper = document.getElementById('formSlideWrapper');
            while (wrapper.children.length > 1) {
                wrapper.removeChild(wrapper.lastChild);
            }
            totalPeserta = 1;
            currentSlide = 0;
            document.getElementById('jumlah-peserta').value = 1;
        }

        function updateSlideUI() {
            const wrapper = document.getElementById('formSlideWrapper');
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;

            document.getElementById('prevBtn').disabled = currentSlide === 0;
            document.getElementById('nextBtn').disabled = currentSlide === totalPeserta - 1;
            document.getElementById('btnRemovePeserta').disabled = totalPeserta <= 1;

            document.getElementById('slideCounter').textContent = currentSlide + 1;
            document.getElementById('totalSlides').textContent = totalPeserta;

            const submitBtn = document.getElementById('submitBtn');
            if (currentSlide === totalPeserta - 1) {
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> <span id="btnText">Daftar & Booking</span>';
            } else {
                submitBtn.innerHTML = '<i class="bi bi-chevron-right"></i> <span id="btnText">Lanjut</span>';
            }

            updateSlideDots();
        }

        function updateSlideDots() {
            const dotsContainer = document.getElementById('slideDots');
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalPeserta; i++) {
                const dot = document.createElement('div');
                dot.className = `slide-dot ${i === currentSlide ? 'active' : ''}`;
                dot.onclick = () => goToSlide(i);
                dotsContainer.appendChild(dot);
            }
        }

        function goToSlide(n) {
            if (n >= 0 && n < totalPeserta) {
                currentSlide = n;
                updateSlideUI();
            }
        }

        function nextSlide() {
            if (currentSlide < totalPeserta - 1) {
                currentSlide++;
                updateSlideUI();
            }
        }

        function prevSlide() {
            if (currentSlide > 0) {
                currentSlide--;
                updateSlideUI();
            }
        }

        function addPeserta() {
            if (totalPeserta >= slotTersedia) {
                Swal.fire({
                    title: 'Slot Terbatas',
                    text: `Hanya tersisa ${slotTersedia} slot untuk trip ini`,
                    icon: 'warning',
                    background: 'rgba(255, 255, 255, 0.95)',
                    color: '#3D2F21',
                    confirmButtonColor: '#FFB800'
                });
                return;
            }

            const wrapper = document.getElementById('formSlideWrapper');
            const newSlide = document.createElement('div');
            newSlide.className = 'form-slide';

            const pesertaNum = totalPeserta + 1;

            newSlide.innerHTML = `
                <div class="group-title">Peserta #${pesertaNum}</div>

                <div class="form-row">
                    <div class="form-control">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama[]" required />
                    </div>
                    <div class="form-control">
                        <label>Email</label>
                        <input type="email" name="email[]" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-control">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir[]" required />
                    </div>
                    <div class="form-control">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir[]" />
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-control">
                        <label>NIK</label>
                        <input type="text" name="nik[]" maxlength="20" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-control">
                        <label>No. WA</label>
                        <input type="text" name="no_wa[]" required />
                    </div>
                    <div class="form-control">
                        <label>No. Darurat</label>
                        <input type="text" name="no_wa_darurat[]" />
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-control">
                        <label>Alamat</label>
                        <textarea name="alamat[]" required></textarea>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-control">
                        <label>Riwayat Penyakit</label>
                        <input type="text" name="riwayat_penyakit[]" maxlength="60" />
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-control">
                        <label>Foto KTP</label>
                        <input type="file" name="foto_ktp[]" accept="image/*" />
                    </div>
                </div>
            `;

            wrapper.appendChild(newSlide);
            totalPeserta++;
            document.getElementById('jumlah-peserta').value = totalPeserta;
            currentSlide = totalPeserta - 1;
            updateSlideUI();
        }

        function removePeserta() {
            if (totalPeserta > 1) {
                const wrapper = document.getElementById('formSlideWrapper');
                wrapper.removeChild(wrapper.lastChild);
                totalPeserta--;
                document.getElementById('jumlah-peserta').value = totalPeserta;
                if (currentSlide >= totalPeserta) {
                    currentSlide = totalPeserta - 1;
                }
                updateSlideUI();
            }
        }

        function submitForm() {
            const form = document.getElementById('form-book-trip');
            const formElements = document.querySelectorAll('.form-slide input, .form-slide textarea');

            formElements.forEach(el => {
                if (el.name && el.name !== 'id_trip' && el.name !== 'jumlah_peserta') {
                    const clone = el.cloneNode();
                    clone.value = el.value;
                    if (el.type === 'file' && el.files.length > 0) {
                        const dataTransfer = new DataTransfer();
                        for (let file of el.files) {
                            dataTransfer.items.add(file);
                        }
                        clone.files = dataTransfer.files;
                    }
                    form.appendChild(clone);
                }
            });

            form.onsubmit = async function(e) {
                e.preventDefault();
                const data = new FormData(form);

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

            form.dispatchEvent(new Event('submit'));
        }

        function openPayment(id) {
            document.getElementById('modal-payment').style.display = 'flex';
            document.getElementById('hasil-pembayaran').innerHTML = "⏳ Memproses pembayaran...";

            fetch('../backend/payment-api.php?booking=' + id)
                .then(r => {
                    const contentType = r.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server mengembalikan HTML bukan JSON');
                    }
                    return r.json();
                })
                .then(resp => {
                    if (resp.snap_token) {
                        window.snap.pay(resp.snap_token, {
                            onSuccess: (result) => {
                                document.getElementById('hasil-pembayaran').innerHTML = "✅ Pembayaran Berhasil!";
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

        (function() {
            const whatsappBtn = document.getElementById('whatsappBtn');

            if (whatsappBtn) {
                let expandTimeout;
                let isExpanded = false;

                whatsappBtn.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!isExpanded) {
                            e.preventDefault();
                            e.stopPropagation();

                            this.classList.add('expanded');
                            isExpanded = true;

                            clearTimeout(expandTimeout);
                            expandTimeout = setTimeout(() => {
                                whatsappBtn.classList.remove('expanded');
                                isExpanded = false;
                            }, 3000);
                        } else {
                            clearTimeout(expandTimeout);
                        }
                    }
                });

                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        whatsappBtn.classList.remove('expanded');
                        isExpanded = false;
                        clearTimeout(expandTimeout);
                    }
                });

                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && isExpanded) {
                        if (!whatsappBtn.contains(e.target)) {
                            whatsappBtn.classList.remove('expanded');
                            isExpanded = false;
                            clearTimeout(expandTimeout);
                        }
                    }
                });
            }
        })();

        // ========== BUKA WHATSAPP FUNCTION ==========
        function bukaWhatsapp() {
            const nomor = "6285233463360";
            const pesan = encodeURIComponent("Halo! Saya ingin bertanya tentang paket trip Majelis MDPL.");
            const url = `https://wa.me/${nomor}?text=${pesan}`;

            window.open(url, "_blank");

            const whatsappBtn = document.getElementById('whatsappBtn');
            if (whatsappBtn) {
                whatsappBtn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    whatsappBtn.style.transform = '';
                }, 150);
            }
        }
    </script>
</body>

</html>