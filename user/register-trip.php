<?php
require_once '../config.php';
require_once '../backend/koneksi.php';
session_start();

$navbarPath = '../';

// 1. Cek Login
$isLogin = isset($_SESSION['id_user']);
if (!$isLogin) {
    header("Location: " . getPageUrl('detail.php') . "?id=" . ($_GET['id'] ?? '') . "&error=login_required");
    exit();
}

$userLogin = null;
if ($isLogin) {
    $stmt = $conn->prepare("SELECT username, email, alamat, no_wa FROM users WHERE id_user=?");
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $userLogin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// 2. Ambil ID Trip
$id_trip = $_GET['id'] ?? null;
if (!$id_trip) {
    header("Location: " . getPageUrl('index.php'));
    exit();
}

// 3. Ambil Data Trip
$stmtTrip = $conn->prepare("SELECT id_trip, nama_gunung, harga, slot, tanggal FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id_trip);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) {
    header("Location: " . getPageUrl('index.php'));
    exit();
}

$tripTitle = htmlspecialchars($trip['nama_gunung']);
$hargaPerPeserta = intval($trip['harga']);
$slotTersedia = intval($trip['slot']);
$tanggalTrip = date('d M Y', strtotime($trip['tanggal']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes" />
    <title>Daftar Trip: <?= $tripTitle ?> | Majelis MDPL</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>

    <style>
        /* CSS FORM REGISTRASI DAN MODAL PEMBAYARAN */
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
            background-image: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.06) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255, 184, 0, 0.04) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 0 1rem;
            position: relative;
            z-index: 1;
        }

        /* CARD STYLE UNTUK FORM REGISTRASI */
        .registration-card {
            background: var(--card-white);
            border: 2px solid rgba(208, 178, 140, 0.3);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-xl);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            background: var(--tan-lighter);
            border-bottom: 2px solid var(--tan-dark);
            text-align: center;
        }

        .card-header h1 {
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 900;
            color: var(--tan-darkest);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .card-header p {
            font-size: clamp(0.8rem, 2vw, 1rem);
            color: var(--text-medium);
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 400px;
        }

        /* FORM SLIDE STYLES */
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

        /* Scrollbar styling (for desktop) */
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

        /* SLIDE CONTROLS (Footer) */
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
            background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);;
            width: 24px;
            border-radius: 3px;
            box-shadow: 0 4px 15px rgba(180, 150, 102, 0.3);
        }

        .slide-nav-btn {
            background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
            border: 2px solid rgba(255, 184, 0, 0.3);
            color: white;
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
            background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
            border: 2px solid rgba(255, 184, 0, 0.3);
            color: white;
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
            background: var(--card-cream);
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
            background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(180, 150, 102, 0.3);
            flex: 1;
        }

        .btn-main:hover {
            background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
            box-shadow: 0 6px 20px rgba(180, 150, 102, 0.4);
            transform: translateY(-2px);
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

        /* PAYMENT MODAL (Keep minimal style for functionality) */
        #modal-payment {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(61, 47, 33, 0.45);
            backdrop-filter: blur(12px) brightness(0.85);
            -webkit-backdrop-filter: blur(12px) brightness(0.85);
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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            text-align: center;
            position: relative;
        }

        #hasil-pembayaran {
            color: var(--text-dark);
            padding: 1.5rem;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container {
                margin-top: 80px;
                padding: 0 0.75rem;
            }

            .card-header {
                padding: 1.25rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }

            .form-slide {
                padding: 1.25rem;
            }

            .slide-controls,
            .form-buttons {
                padding: 0.85rem 1rem;
                gap: 0.6rem;
            }

            .peserta-controls {
                margin-left: 0.5rem;
            }
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h1>Pendaftaran Trip: <?= $tripTitle ?></h1>
                <p>Tanggal: <?= $tanggalTrip ?> | Harga: Rp <?= number_format($hargaPerPeserta, 0, ',', '.') ?> / Peserta</p>
                <?php if ($slotTersedia <= 5): ?>
                    <p style="color: #f44336; font-weight: 700; margin-top: 5px;">*Tersisa hanya <?= $slotTersedia ?> slot! Segera daftar.</p>
                <?php endif; ?>
            </div>

            <form class="booking-form" id="form-book-trip" enctype="multipart/form-data">
                <input type="hidden" name="id_trip" value="<?= $trip['id_trip'] ?>">
                <input type="hidden" name="jumlah_peserta" id="jumlah-peserta" value="1">

                <div class="card-body">
                    <div class="form-slide-wrapper" id="formSlideWrapper">

                        <div class="form-slide">
                            <div class="group-title">Data Diri Anda (Peserta 1)</div>

                            <div class="form-row">
                                <div class="form-control">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama[]" required value="<?= htmlspecialchars($userLogin['username'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="form-control">
                                    <label>Email</label>
                                    <input type="email" name="email[]" required value="<?= htmlspecialchars($userLogin['email'] ?? '', ENT_QUOTES) ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-control">
                                    <label>Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir[]" required>
                                </div>
                                <div class="form-control">
                                    <label>Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir[]">
                                </div>
                            </div>

                            <div class="form-row full">
                                <div class="form-control">
                                    <label>NIK</label>
                                    <input type="text" name="nik[]" maxlength="20">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-control">
                                    <label>No. WA</label>
                                    <input type="text" name="no_wa[]" required value="<?= htmlspecialchars($userLogin['no_wa'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="form-control">
                                    <label>No. Darurat</label>
                                    <input type="text" name="no_wa_darurat[]">
                                </div>
                            </div>

                            <div class="form-row full">
                                <div class="form-control">
                                    <label>Alamat</label>
                                    <textarea name="alamat[]" required><?= htmlspecialchars($userLogin['alamat'] ?? '', ENT_QUOTES) ?></textarea>
                                </div>
                            </div>

                            <div class="form-row full">
                                <div class="form-control">
                                    <label>Riwayat Penyakit</label>
                                    <input type="text" name="riwayat_penyakit[]" maxlength="60">
                                </div>
                            </div>

                            <div class="form-row full">
                                <div class="form-control">
                                    <label>Foto KTP (Wajib)</label>
                                    <input type="file" name="foto_ktp[]" accept="image/*" required class="input-foto-ktp">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="slide-controls">
                    <button type="button" class="slide-nav-btn" id="prevBtn" onclick="prevSlide()" disabled>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div id="slideDots" style="display: flex; gap: 5px;"></div>
                    <button type="button" class="slide-nav-btn" id="nextBtn" onclick="nextSlide()">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <span class="slide-counter"><span id="slideCounter">1</span>/<span id="totalSlides">1</span></span>

                    <div class="peserta-controls">
                        <span class="peserta-label">Peserta</span>
                        <button type="button" class="btn-peserta-remove" id="btnRemovePeserta" onclick="removePeserta()" disabled>
                            <i class="bi bi-dash-lg"></i>
                        </button>
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
            </form>
        </div>
    </div>

    <div id="modal-payment">
        <div>
            <div id="hasil-pembayaran"></div>
        </div>
    </div>

    <script src="<?php echo getAssetsUrl('frontend/config.js'); ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../frontend/registrasi.js"></script>
    <script src="../frontend/login.js"></script>

    <script>
        let currentSlide = 0;
        let totalPeserta = 1;
        const slotTersedia = <?= $slotTersedia ?>;
        const hargaPerPeserta = <?= $hargaPerPeserta ?>;

        // URL untuk mengarahkan ke halaman status pembayaran
        const paymentStatusUrl = '<?= getPageUrl("user/payment-status.php") ?>';

        // Nama key untuk menyimpan data di localStorage. Gunakan ID Trip dan ID User agar unik
        const STORAGE_KEY = `trip_booking_data_<?= $id_trip ?>_<?= $_SESSION['id_user'] ?>`;

        // --- TEMPLATE PESERTA BARU (Digunakan untuk addPeserta dan restoreFormData) ---
        function getPesertaTemplate(pesertaNum) {
            return `
        <div class="group-title">Peserta ${pesertaNum}</div>
        <div class="form-row">
            <div class="form-control">
                <label>Nama Lengkap</label>
                <input type="text" name="nama[]" required>
            </div>
            <div class="form-control">
                <label>Email</label>
                <input type="email" name="email[]" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-control">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir[]" required>
            </div>
            <div class="form-control">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir[]">
            </div>
        </div>
        <div class="form-row full">
            <div class="form-control">
                <label>NIK</label>
                <input type="text" name="nik[]" maxlength="20">
            </div>
        </div>
        <div class="form-row">
            <div class="form-control">
                <label>No. WA</label>
                <input type="text" name="no_wa[]" required>
            </div>
            <div class="form-control">
                <label>No. Darurat</label>
                <input type="text" name="no_wa_darurat[]">
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
                <input type="text" name="riwayat_penyakit[]" maxlength="60">
            </div>
        </div>
        <div class="form-row full">
            <div class="form-control">
                <label>Foto KTP (Wajib)</label>
                <input type="file" name="foto_ktp[]" accept="image/*" required class="input-foto-ktp">
            </div>
        </div>
        `;
        }

        // --- CORE SLIDE LOGIC ---
        function updateSlideUI() {
            const wrapper = document.getElementById('formSlideWrapper');
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;

            document.getElementById('prevBtn').disabled = currentSlide === 0;
            document.getElementById('nextBtn').disabled = currentSlide === totalPeserta - 1;
            document.getElementById('btnRemovePeserta').disabled = totalPeserta === 1;

            document.getElementById('slideCounter').textContent = currentSlide + 1;
            document.getElementById('totalSlides').textContent = totalPeserta;

            const submitBtn = document.getElementById('submitBtn');
            if (currentSlide === totalPeserta - 1) {
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> <span id="btnText">Daftar & Booking</span>';
                submitBtn.onclick = submitForm;
            } else {
                submitBtn.innerHTML = '<i class="bi bi-chevron-right"></i> <span id="btnText">Lanjut</span>';
                submitBtn.onclick = nextSlide;
            }

            updateSlideDots();
        }

        function updateSlideDots() {
            const dotsContainer = document.getElementById('slideDots');
            dotsContainer.innerHTML = '';

            for (let i = 0; i < totalPeserta; i++) {
                const dot = document.createElement('div');
                dot.className = 'slide-dot' + (i === currentSlide ? ' active' : '');
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
                if (!validateCurrentSlide()) {
                    Swal.fire({
                        title: 'Data Belum Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi dan pastikan format input sudah benar.',
                        icon: 'warning',
                        confirmButtonColor: '#FFB800'
                    });
                    return;
                }
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

        function validateCurrentSlide() {
            const currentSlideElement = document.getElementById('formSlideWrapper').children[currentSlide];
            const requiredInputs = currentSlideElement.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                input.classList.remove('is-invalid');
                if (!input.value.trim() || (input.type === 'file' && input.files.length === 0)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                }
                if (input.type === 'email' && input.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
                    isValid = false;
                    input.classList.add('is-invalid');
                }
            });

            return isValid;
        }

        // --- LOCAL STORAGE & PERSISTENCY LOGIC ---

        function saveFormData() {
            const formData = {};
            const form = document.getElementById('form-book-trip');
            const slides = form.querySelectorAll('.form-slide');

            // Simpan jumlah peserta
            formData.totalPeserta = totalPeserta;

            // Simpan data setiap peserta (Hanya data teks)
            formData.pesertaData = [];

            slides.forEach((slide) => {
                const slideData = {};
                const inputs = slide.querySelectorAll('input, textarea');

                inputs.forEach(input => {
                    // Kita hanya menyimpan nilai untuk input non-file
                    if (input.type !== 'file') {
                        const name = input.name.replace('[]', '');
                        slideData[name] = input.value;
                    }
                });
                formData.pesertaData.push(slideData);
            });

            localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
        }

        function restoreFormData(data) {
            // 1. Atur jumlah peserta
            const targetPeserta = data.totalPeserta;
            const wrapper = document.getElementById('formSlideWrapper');

            // Tambah slide jika kurang
            while (totalPeserta < targetPeserta) {
                const newSlide = document.createElement('div');
                newSlide.className = 'form-slide';
                newSlide.innerHTML = getPesertaTemplate(totalPeserta + 1);
                wrapper.appendChild(newSlide);
                totalPeserta++;
            }
            // Hapus slide jika lebih
            while (totalPeserta > targetPeserta && totalPeserta > 1) {
                wrapper.removeChild(wrapper.lastChild);
                totalPeserta--;
            }

            document.getElementById('jumlah-peserta').value = totalPeserta;

            // 2. Isi data ke setiap slide
            const slides = document.querySelectorAll('.form-slide');
            data.pesertaData.forEach((slideData, index) => {
                if (slides[index]) {
                    // Hapus notifikasi upload sebelumnya
                    slides[index].querySelectorAll('.upload-warning').forEach(el => el.remove());

                    const inputs = slides[index].querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        const name = input.name.replace('[]', '');
                        if (slideData[name] !== undefined && input.type !== 'file') {
                            input.value = slideData[name];
                        }
                    });

                    // LOGIKA PEMBERITAHUAN UNTUK INPUT FILE
                    const fileInput = slides[index].querySelector('.input-foto-ktp');
                    if (fileInput) {
                        fileInput.value = ''; // Nilai input file harus dikosongkan setelah refresh

                        // Tambahkan elemen visual untuk mengingatkan user agar upload ulang
                        fileInput.insertAdjacentHTML('afterend',
                            '<p class="upload-warning" style="color:#F44336; font-size:0.75rem; margin-top:5px; font-weight: 600;">⚠️ **Mohon upload ulang Foto KTP**. Browser tidak menyimpan file setelah refresh.</p>'
                        );

                        // Hapus atribut 'required' sementara di slide ini (kecuali slide terakhir)
                        // Agar user bisa next slide tanpa upload file
                        if (index !== slides.length - 1) {
                            fileInput.removeAttribute('required');
                            // Kembalikan required saat pindah slide
                            slides[index].addEventListener('change', () => {
                                if (fileInput.files.length === 0) {
                                    fileInput.setAttribute('required', true);
                                } else {
                                    fileInput.removeAttribute('required');
                                }
                            });
                        }
                    }

                    // Perbarui judul slide jika lebih dari 1 peserta
                    if (index > 0) {
                        slides[index].querySelector('.group-title').textContent = `Peserta ${index + 1}`;
                    }
                }
            });

            // 3. Update UI dan navigasi
            currentSlide = 0;
            updateSlideUI();
        }

        function loadFormData() {
            const savedData = localStorage.getItem(STORAGE_KEY);

            if (savedData) {
                const data = JSON.parse(savedData);

                // Tampilkan pop-up konfirmasi pemulihan
                Swal.fire({
                    title: 'Data Pendaftaran Ditemukan!',
                    text: `Ditemukan data pendaftaran Trip: <?= $tripTitle ?> untuk ${data.totalPeserta} orang yang belum selesai. Apakah Anda ingin melanjutkan?`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#4CAF50',
                    cancelButtonColor: '#F44336',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Tidak, Mulai Baru'
                }).then((result) => {
                    if (result.isConfirmed) {
                        restoreFormData(data);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Data Berhasil Dipulihkan!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        // Jika pengguna memilih memulai baru, hapus data lama
                        localStorage.removeItem(STORAGE_KEY);
                    }
                });
            }
        }

        // --- PESERTA LOGIC ---

        function addPeserta() {
            if (totalPeserta >= slotTersedia) {
                Swal.fire({
                    title: 'Slot Terbatas',
                    text: `Hanya tersedia ${slotTersedia} slot untuk trip ini`,
                    icon: 'warning',
                    confirmButtonColor: '#FFB800'
                });
                return;
            }

            const wrapper = document.getElementById('formSlideWrapper');
            const newSlide = document.createElement('div');
            newSlide.className = 'form-slide';

            // Cek validasi slide saat ini sebelum menambah
            if (!validateCurrentSlide()) {
                Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: 'Mohon lengkapi semua field yang wajib diisi pada Peserta ' + (currentSlide + 1),
                    icon: 'warning',
                    confirmButtonColor: '#FFB800'
                });
                return;
            }


            newSlide.innerHTML = getPesertaTemplate(totalPeserta + 1);

            wrapper.appendChild(newSlide);
            totalPeserta++;
            document.getElementById('jumlah-peserta').value = totalPeserta;

            currentSlide = totalPeserta - 1;
            updateSlideUI();
            saveFormData(); // Simpan data setelah menambah peserta
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
                saveFormData(); // Simpan data setelah menghapus peserta
            }
        }

        // --- SUBMIT DAN LOGIKA PEMBAYARAN ---

        function submitForm() {
            if (!validateCurrentSlide()) {
                Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: 'Mohon lengkapi semua field yang wajib diisi dan pastikan format input sudah benar pada Peserta ' + (currentSlide + 1) + '. **Pastikan Foto KTP sudah di-upload!**',
                    icon: 'warning',
                    confirmButtonColor: '#FFB800'
                });
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            const formData = new FormData();
            formData.append('id_trip', document.querySelector('input[name="id_trip"]').value);
            formData.append('jumlah_peserta', document.querySelector('input[name="jumlah_peserta"]').value);

            const allSlides = document.querySelectorAll('.form-slide');

            allSlides.forEach((slide) => {
                const textInputs = slide.querySelectorAll('input[type="text"], input[type="email"], input[type="date"], textarea');
                textInputs.forEach(input => {
                    if (input.name && input.name.includes('[]')) {
                        formData.append(input.name, input.value || '');
                    }
                });

                const fileInput = slide.querySelector('input[type="file"]');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('foto_ktp[]', fileInput.files[0]);
                } else {
                    // Jika input file kosong (wajib diisi, tapi dikirim sebagai placeholder)
                    formData.append('foto_ktp[]', new Blob(), '');
                }
            });

            // Kirim data dengan fetch
            fetch(getApiUrl('booking-api.php'), {
                    method: 'POST',
                    body: formData
                })
                .then(async res => {
                    const contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const htmlText = await res.text();
                        console.error('Server returned HTML:', htmlText);
                        throw new Error('Server error, expected JSON. Check console for detail.');
                    }
                    return res.json();
                })
                .then(json => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Daftar & Booking';

                    if (json.success && json.id_booking) {

                        // Hapus data form dari localStorage setelah berhasil disimpan ke DB
                        localStorage.removeItem(STORAGE_KEY);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Pendaftaran Berhasil!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Lanjutkan ke pembayaran Midtrans Snap
                        openPayment(json.id_booking);

                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: json.message || 'Terjadi kesalahan saat menyimpan data pendaftaran.',
                            icon: 'error',
                            confirmButtonColor: '#FFB800'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Daftar & Booking';
                    Swal.fire({
                        title: 'Error Sistem',
                        text: 'Terjadi kesalahan pada sistem: ' + err.message,
                        icon: 'error',
                        confirmButtonColor: '#FFB800'
                    });
                });
        }

        function openPayment(id) {
            document.getElementById('modal-payment').style.display = 'flex';
            document.getElementById('hasil-pembayaran').innerHTML = '<i class="bi bi-hourglass-split"></i> Mempersiapkan Midtrans...';

            const paymentApiUrl = getApiUrl('payment-api.php') + '?booking=' + id;

            fetch(paymentApiUrl)
                .then(r => r.json())
                .then(resp => {
                    if (!resp.success) {
                        document.getElementById('modal-payment').style.display = 'none';
                        throw new Error(resp.error || 'Gagal mendapatkan token pembayaran');
                    }

                    document.getElementById('modal-payment').style.display = 'none';

                    if (resp.snap_token) {
                        window.snap.pay(resp.snap_token, {
                            onSuccess: (result) => {
                                // CASE 1: Pembayaran Selesai (Success)
                                Swal.fire({
                                    title: 'Pembayaran Berhasil!',
                                    text: 'Booking Anda telah dikonfirmasi. Anda akan diarahkan ke status pembayaran.',
                                    icon: 'success',
                                    confirmButtonColor: '#4CAF50'
                                }).then(() => window.location.href = paymentStatusUrl);
                            },
                            onPending: (result) => {
                                // CASE 2: Pembayaran Tertunda (Pending)
                                Swal.fire({
                                    title: 'Booking Berhasil!',
                                    text: 'Silakan lanjutkan pembayaran Anda melalui menu status pembayaran.',
                                    icon: 'info',
                                    confirmButtonColor: '#FFB800'
                                }).then(() => window.location.href = paymentStatusUrl);
                            },
                            onError: (result) => {
                                // CASE 3: Pembayaran Gagal (Error)
                                Swal.fire({
                                    title: 'Pembayaran Gagal',
                                    text: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi dari menu status pembayaran.',
                                    icon: 'error',
                                    confirmButtonColor: '#F44336'
                                }).then(() => window.location.href = paymentStatusUrl);
                            },
                            onClose: () => {
                                // CASE 4: Pop-up Ditutup oleh User (Incomplete/Deferred Payment)
                                Swal.fire({
                                    title: 'Booking Berhasil!',
                                    text: 'Silakan lanjutkan pembayaran Anda dari menu status pembayaran.',
                                    icon: 'info',
                                    confirmButtonColor: '#FFB800'
                                }).then(() => window.location.href = paymentStatusUrl);
                            }
                        });
                    } else {
                        throw new Error(resp.detail || 'Token pembayaran tidak diperoleh');
                    }
                })
                .catch(err => {
                    console.error('Payment error:', err);
                    document.getElementById('modal-payment').style.display = 'none';
                    Swal.fire({
                        title: 'Error Pembayaran',
                        text: 'Gagal memproses pembayaran. Cek koneksi Anda atau hubungi admin. Detail: ' + err.message,
                        icon: 'error',
                        confirmButtonColor: '#F44336'
                    });
                });
        }


        function closePayment() {
            document.getElementById('modal-payment').style.display = 'none';
        }

        // Initialize UI and Load Data on load
        document.addEventListener('DOMContentLoaded', () => {
            updateSlideUI();
            loadFormData();

            // Tambahkan event listener untuk menyimpan data setiap ada perubahan pada form
            const form = document.getElementById('form-book-trip');
            form.addEventListener('change', saveFormData);
            form.addEventListener('keyup', saveFormData); // Simpan saat ketikan
        });
    </script>
</body>

</html>