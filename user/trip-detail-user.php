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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* ============================================
           LUXURY GRADIENT BACKGROUND + RESPONSIVE FORM
           ============================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Luxury Color Palette */
            --luxury-gold: #D4AF37;
            --luxury-gold-light: #E6C45E;
            --luxury-brown: #8B7355;
            --dark-charcoal: #1C1917;
            --dark-slate: #292524;
            --dark-stone: #44403C;
            --text-light: #FAFAF9;
            --text-muted: rgba(250, 250, 249, 0.75);
            --glass-bg: rgba(68, 64, 60, 0.3);
            --glass-border: rgba(212, 175, 55, 0.15);

            /* Shadows */
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.15);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 0 20px rgba(212, 175, 55, 0.3);

            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.4s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background:
                radial-gradient(ellipse at top left, rgba(212, 175, 55, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(139, 115, 85, 0.06) 0%, transparent 50%),
                linear-gradient(135deg, #1C1917 0%, #292524 25%, #1C1917 50%, #292524 75%, #1C1917 100%);
            background-attachment: fixed;
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(212, 175, 55, 0.01) 2px, rgba(212, 175, 55, 0.01) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(212, 175, 55, 0.01) 2px, rgba(212, 175, 55, 0.01) 4px);
            background-size: 80px 80px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* ============================================
           HERO SECTION
           ============================================ */

        .hero {
            position: relative;
            height: 100vh;
            width: 100vw;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .hero img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.45) contrast(1.1);
            z-index: 1;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(212, 175, 55, 0.12) 0%, transparent 50%),
                linear-gradient(135deg, rgba(28, 25, 23, 0.8) 0%, rgba(41, 37, 36, 0.6) 50%, transparent 100%);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 4;
            color: var(--text-light);
            max-width: 700px;
            padding: 0 5%;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--luxury-gold-light);
            margin-bottom: 15px;
        }

        .hero-text {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 900;
            letter-spacing: 0.02em;
            text-shadow: 0 5px 30px rgba(0, 0, 0, 0.6);
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #FAFAF9 0%, var(--luxury-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-hero-wrapper {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-hero {
            position: relative;
            background: linear-gradient(135deg, var(--luxury-gold), var(--luxury-brown));
            color: #FFF;
            padding: 15px 35px;
            font-weight: 700;
            font-size: 1.05rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all var(--transition-normal);
            border: 2px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            overflow: hidden;
            box-shadow: var(--shadow-glow);
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transform: translate(-50%, -50%);
            transition: all 0.6s ease;
        }

        .btn-hero:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.4);
            border-color: var(--luxury-gold);
        }

        .btn-hero:disabled {
            background: linear-gradient(135deg, #666, #888);
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* ============================================
           INFO BAR
           ============================================ */

        .info-bar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(150%);
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            animation: fadeIn 0.6s ease-out 0.2s backwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-bar:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .info-item {
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        .info-item i {
            font-size: 2.5rem;
            color: var(--luxury-gold);
            transition: all var(--transition-normal);
        }

        .info-item:hover i {
            transform: scale(1.15);
        }

        .info-item span:first-of-type {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        .info-item span:last-child {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-light);
        }

        /* ============================================
           CONTENT AREA
           ============================================ */

        .content-area {
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(150%);
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            padding: 40px;
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-md);
            margin-bottom: 40px;
            animation: fadeIn 0.6s ease-out 0.4s backwards;
        }

        section.detail-section {
            padding: 30px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }

        section.detail-section:last-child {
            border-bottom: none;
        }

        section.detail-section h2 {
            font-size: 1.8rem;
            font-weight: 900;
            margin-bottom: 25px;
            color: var(--luxury-gold);
            position: relative;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        section.detail-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--luxury-gold), transparent);
            border-radius: 2px;
        }

        .icon-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .icon-list li {
            margin-bottom: 15px;
            padding-left: 35px;
            position: relative;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-muted);
            transition: all var(--transition-fast);
        }

        .icon-list li:hover {
            color: var(--text-light);
            padding-left: 40px;
        }

        .icon-list li i {
            position: absolute;
            left: 0;
            top: 3px;
            font-size: 1.2rem;
            color: var(--luxury-gold);
        }

        .map-container {
            margin-top: 25px;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-sm);
        }

        .map-container iframe {
            width: 100%;
            height: 350px;
            border: 0;
            display: block;
        }

        /* ============================================
           CUSTOM LOGIN WARNING MODAL - LUXURY STYLE
           ============================================ */

        #loginWarningModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        }

        #loginWarningModal.active {
            display: flex;
        }

        .login-warning-container {
            background: linear-gradient(135deg, #3C3731 0%, #292524 100%);
            border: 2px solid rgba(212, 175, 55, 0.25);
            border-radius: 25px;
            max-width: 450px;
            width: 90%;
            padding: 45px 35px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-warning-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.08));
            border: 3px solid var(--luxury-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 35px rgba(212, 175, 55, 0.5);
            }
        }

        .login-warning-icon i {
            font-size: 3rem;
            color: var(--luxury-gold);
        }

        .login-warning-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-light);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .login-warning-text {
            font-size: 1.05rem;
            color: var(--text-muted);
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .login-warning-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-warning-login,
        .btn-warning-cancel {
            flex: 1;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
        }

        .btn-warning-login {
            background: linear-gradient(135deg, var(--luxury-gold), var(--luxury-brown));
            color: #FFF;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }

        .btn-warning-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.5);
        }

        .btn-warning-cancel {
            background: rgba(250, 250, 249, 0.08);
            color: var(--text-light);
            border: 2px solid rgba(250, 250, 249, 0.15);
        }

        .btn-warning-cancel:hover {
            background: rgba(250, 250, 249, 0.15);
            border-color: rgba(250, 250, 249, 0.3);
        }

        /* ============================================
           BOOKING MODAL
           ============================================ */

        #modal-booking {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            padding: 110px 20px 40px 20px;
            animation: fadeIn 0.3s ease-out;
            overflow-y: auto;
        }

        #modal-booking.active {
            display: flex;
        }

        #modal-booking .booking-modal-box {
            background: linear-gradient(135deg, #292524 0%, #1C1917 100%);
            max-width: 650px;
            width: 100%;
            margin: auto;
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            position: relative;
            animation: slideUp 0.4s ease-out;
            margin-bottom: 40px;
        }

        .scroll-area-modal {
            width: 100%;
            max-height: calc(85vh - 150px);
            overflow-y: auto;
            padding: 45px 35px 35px;
        }

        .scroll-area-modal::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-area-modal::-webkit-scrollbar-track {
            background: rgba(212, 175, 55, 0.08);
            border-radius: 10px;
        }

        .scroll-area-modal::-webkit-scrollbar-thumb {
            background: var(--luxury-gold);
            border-radius: 10px;
        }

        .booking-modal-box h3 {
            margin: 0 0 25px;
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--luxury-gold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: center;
        }

        .booking-form label {
            display: block;
            font-weight: 600;
            margin: 15px 0 7px;
            font-size: 0.85rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .booking-form input[type=text],
        .booking-form input[type=email],
        .booking-form input[type=date],
        .booking-form textarea,
        .booking-form input[type=file] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--glass-border);
            background: rgba(212, 175, 55, 0.05);
            color: var(--text-light);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all var(--transition-normal);
            font-family: 'Poppins', sans-serif;
        }

        .booking-form input:focus,
        .booking-form textarea:focus {
            outline: none;
            border-color: var(--luxury-gold);
            background: rgba(212, 175, 55, 0.08);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.2);
        }

        .booking-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .booking-form .group-title {
            margin: 28px 0 18px;
            font-size: 1.1rem;
            color: var(--luxury-gold);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }

        .booking-form .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
            margin-bottom: 8px;
        }

        .booking-form .btn-add,
        .booking-form .btn-rm {
            margin: 18px 0 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            background: none;
            border: none;
            color: var(--luxury-gold);
            font-weight: 600;
            transition: all var(--transition-fast);
            text-align: left;
            padding: 0;
        }

        .booking-form .btn-add:hover,
        .btn-rm:hover {
            color: var(--luxury-gold-light);
        }

        .btn-main {
            margin-top: 25px;
            background: linear-gradient(135deg, var(--luxury-gold), var(--luxury-brown));
            color: #FFF;
            border: none;
            border-radius: 50px;
            padding: 15px;
            width: 100%;
            font-weight: 800;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all var(--transition-normal);
            cursor: pointer;
            box-shadow: var(--shadow-glow);
        }

        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.4);
        }

        .btn-main:disabled {
            background: linear-gradient(135deg, #666, #888);
            cursor: not-allowed;
            opacity: 0.5;
        }

        .btn-cancel {
            margin-top: 10px;
            background: rgba(250, 250, 249, 0.08);
            color: var(--text-light);
            border: 1px solid rgba(250, 250, 249, 0.15);
            border-radius: 50px;
            padding: 13px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normal);
        }

        .btn-cancel:hover {
            background: rgba(250, 250, 249, 0.12);
            border-color: rgba(250, 250, 249, 0.25);
        }

        .booking-modal-box .close-btn {
            position: absolute;
            top: 18px;
            right: 18px;
            font-size: 1.4rem;
            background: rgba(250, 250, 249, 0.08);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid rgba(250, 250, 249, 0.15);
            color: var(--luxury-gold);
            cursor: pointer;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .booking-modal-box .close-btn:hover {
            background: rgba(250, 250, 249, 0.15);
            border-color: var(--luxury-gold);
            transform: rotate(90deg);
        }

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

        #modal-payment {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
        }

        #modal-payment>div {
            background: linear-gradient(135deg, #292524 0%, #1C1917 100%);
            padding: 40px 30px;
            max-width: 450px;
            width: 95%;
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            text-align: center;
            position: relative;
        }

        #modal-payment button {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(250, 250, 249, 0.08);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid rgba(250, 250, 249, 0.15);
            color: var(--luxury-gold);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all var(--transition-normal);
        }

        #modal-payment button:hover {
            background: rgba(250, 250, 249, 0.15);
            transform: rotate(90deg);
        }

        #hasil-pembayaran {
            color: var(--text-light);
            padding: 20px;
            font-size: 1.1rem;
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
                if ($id_booking_pending) {
                    echo '<button class="btn-main" onclick="openPayment(' . $id_booking_pending . ')">Lanjutkan Pembayaran</button>';
                }
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
                            echo '<p><a href="' . htmlspecialchars($linkMap) . '" target="_blank" rel="noopener" style="color:var(--luxury-gold);">Buka Google Maps</a></p>';
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

    <!-- ✅ CUSTOM LOGIN WARNING MODAL -->
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

    <!-- Modal Booking (tetap sama) -->
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

    <!-- Modal Payment (tetap sama) -->
    <div id="modal-payment">
        <div>
            <button onclick="closePayment()"><i class="bi bi-x-lg"></i></button>
            <div id="hasil-pembayaran"></div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <!-- Tombol WhatsApp -->
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

    <!-- ✅ SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        // ========== LOGIN WARNING MODAL FUNCTIONS ==========
        function showLoginWarning() {
            document.getElementById('loginWarningModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginWarning() {
            document.getElementById('loginWarningModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function openLoginFromWarning() {
            // Close warning modal
            closeLoginWarning();

            // Open login modal
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.style.display = 'flex';
                loginModal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        // ========== BOOKING MODAL FUNCTIONS ==========
        function bookTripModal() {
            <?php if (!$isLogin): ?>
                // Show custom warning modal instead of SweetAlert
                showLoginWarning();
                return;
            <?php endif; ?>

            // Jika sudah login, buka modal booking
            document.getElementById('modal-booking').classList.add('active');
            document.querySelector('.scroll-area-modal').scrollTop = 0;
        }

        function closeBooking() {
            document.getElementById('modal-booking').classList.remove('active');
        }

        function addPeserta() {
            const id = document.querySelectorAll('#extra-participants .peserta-baru').length + 2;
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
        }

        function updateJumlah() {
            document.getElementById('jumlah-peserta').value = document.querySelectorAll('.peserta-baru').length + 1;
        }

        // ========== BOOKING FORM SUBMISSION ==========
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
                            background: '#292524',
                            color: '#FAFAF9',
                            confirmButtonColor: '#D4AF37'
                        });
                        closeBooking();
                        setTimeout(() => openPayment(json.id_booking), 1100);
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: json.message || 'Terjadi kesalahan',
                            icon: 'error',
                            background: '#292524',
                            color: '#FAFAF9',
                            confirmButtonColor: '#D4AF37'
                        });
                    }
                } catch (err) {
                    console.error('Error:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem: ' + err.message,
                        icon: 'error',
                        background: '#292524',
                        color: '#FAFAF9',
                        confirmButtonColor: '#D4AF37'
                    });
                }
            };
        <?php endif; ?>

        // ========== PAYMENT MODAL FUNCTIONS ==========
        function openPayment(id) {
            document.getElementById('modal-payment').style.display = 'flex';
            document.getElementById('hasil-pembayaran').innerHTML = "Memproses...";

            // ✅ FIX: Path relatif yang benar
            fetch('../backend/payment-api.php?booking=' + id)
                .then(r => r.json())
                .then(resp => {
                    if (resp.snap_token) {
                        window.snap.pay(resp.snap_token, {
                            onSuccess: () => {
                                document.getElementById('hasil-pembayaran').innerHTML = "Pembayaran Berhasil!";
                                setTimeout(() => {
                                    closePayment();
                                    window.location.reload();
                                }, 2000);
                            },
                            onPending: () => {
                                document.getElementById('hasil-pembayaran').innerHTML = "Menunggu Pembayaran...";
                            },
                            onError: () => {
                                document.getElementById('hasil-pembayaran').innerHTML = "Pembayaran Gagal!";
                            },
                            onClose: () => {
                                document.getElementById('hasil-pembayaran').innerHTML = "Popup Ditutup";
                                setTimeout(closePayment, 1500);
                            }
                        });
                    } else {
                        document.getElementById('hasil-pembayaran').innerHTML = resp.error || 'Error memuat pembayaran';
                    }
                })
                .catch(err => {
                    console.error('Payment error:', err);
                    document.getElementById('hasil-pembayaran').innerHTML = 'Error: ' + err.message;
                });
        }

        function closePayment() {
            document.getElementById('modal-payment').style.display = 'none';
        }

        // ========== WHATSAPP BUTTON - RESPONSIVE ==========
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