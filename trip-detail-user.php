<?php
require_once 'backend/koneksi.php';
session_start();

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
if (!$id) { header("Location: index.php"); exit(); }

$stmtTrip = $conn->prepare("SELECT * FROM paket_trips WHERE id_trip = ?");
$stmtTrip->bind_param("i", $id);
$stmtTrip->execute();
$resultTrip = $stmtTrip->get_result();
$trip = $resultTrip->fetch_assoc();
$stmtTrip->close();

if (!$trip) { header("Location: index.php"); exit(); }

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

function createIconList($text, $iconClass) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?=htmlspecialchars($trip['nama_gunung'])?> | Majelis MDPL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-KFnuwUuiq_i1OUJf"></script>
    <style>
    body { font-family: 'Poppins', sans-serif; }
    .container { max-width:1200px; margin:55px auto 0; }
    .hero { position:relative; height:77vh; width:100vw; overflow:hidden; box-shadow:0 2px 10px #9993; display:flex; align-items:center; }
    .hero img { position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;filter:brightness(.57);}
    .hero-overlay { position:absolute;inset:0;background:linear-gradient(90deg,#000c 0,#0005 50%,#0002 100%);}
    .hero-content { position:relative;z-index:2;color:#fff;max-width:535px;padding-left:3vw;}
    .hero-text { font-size:2.4rem;font-weight:800;text-shadow:0 2px 9px #000b;margin-bottom:16px;}
    .hero-subtitle { font-size:1.17rem;margin-bottom:11px;text-shadow:0 1px 5px #0007;}
    .btn-hero-wrapper {margin-top:2.2rem;}
    .btn-hero {background:#a97c50;color:#fff;padding:12px 24px;font-weight:700;font-size:1rem;border-radius:30px;cursor:pointer;transition:.18s; border:none;}
    .btn-hero:hover {background:#8d6331;}
    .btn-hero:disabled{background:#aaa;}
    .info-bar { background:#fff;box-shadow:0 2px 10px #0001;border-radius:8px;padding:20px 26px;display:flex;gap:18px 10px;margin-bottom:38px;justify-content:space-around;}
    .info-item{display:flex;align-items:center;gap:9px;font-size:1.05rem;flex-direction:column;text-align:center;}
    .info-item i{font-size:2.0rem;color:#a97c50;}
    .info-item span:last-child{font-weight:700;}
    .content-area{background:#fff;padding:30px 38px;border-radius:13px;box-shadow:0 4px 22px #0002;}
    section.detail-section{padding:18px 0 0;margin-bottom:10px;}
    section.detail-section h2{font-size:1.21rem;font-weight:700;margin-bottom:10px;color:#a97c50;}
    .icon-list{list-style:none;margin:0;padding-left:1vw;}
    .icon-list li{margin-bottom:8px;}
    #modal-booking { display:none; position:fixed; inset:0; z-index:999; background:rgba(25,20,10,.82); align-items:center; justify-content:center; }
    #modal-booking.active { display:flex; }
    #modal-booking .booking-modal-box { background:#fff; max-width:450px; width:96%; border-radius:16px; box-shadow:0 6px 45px #0008; margin:5vh auto 5vh; position:relative; display:flex; flex-direction:column;}
    .scroll-area-modal { width:100%; max-height:78vh; overflow-y:auto; padding:34px 19px 22px 19px;}
    .booking-modal-box h3{margin-top:0;margin-bottom:11px;font-size:1.11rem;font-weight:800;}
    .booking-form label{display:block;font-weight:600;margin:7px 0 2px 2px;font-size:0.94rem;}
    .booking-form input[type=text],.booking-form input[type=email],.booking-form input[type=date],.booking-form textarea,.booking-form input[type=file]{width:100%;padding:8px 9px;border:1.3px solid #bfa477;background:#fafaee;color:#28281a;border-radius:6px;font-size:1.02rem;}
    .booking-form textarea {resize:vertical; height:39px;}
    .booking-form .group-title{margin:1.23em 0 0.6em;font-size:1.08em;color:#7a6b5e;font-weight:700;}
    .booking-form .row{display:flex;gap:17px;}
    .booking-form .row > div{flex:1;}
    .booking-form .btn-add,.booking-form .btn-rm{margin:.18em 0 0 .5em;cursor:pointer;text-decoration:underline;font-size:0.97em;background:none;border:none;color:#e29700;}
    .booking-form .btn-add:hover,.btn-rm:hover{color:#973900;}
    .btn-main{margin-top:18px;background:#a97c50;color:#fff;border:none;border-radius:8px;padding:12px 6px;width:100%;font-weight:800;font-size:1.07em;transition:.2s;}
    .btn-main:disabled{background:#c1b8a3;}
    .btn-cancel{margin-top:8px;background:#eee;color:#222;border:none;border-radius:8px;padding:9px 6px;width:100%;font-weight:600;}
    .booking-modal-box .close-btn{position:absolute;top:15px;right:15px;font-size:1.21rem;background:none;border:none;color:#967c57;cursor:pointer;}
    .booking-modal-box .close-btn:hover{color:#bb3d3c;}
    @media(max-width:700px){
        .container{margin:13vw auto 0;width:99%;}
        .content-area{padding:9px;}
        #modal-booking .booking-modal-box {max-width:99vw;}
        .scroll-area-modal{padding:8px 3vw;}
        .info-bar{gap:6px 5px; flex-wrap:wrap;}
        .booking-form .row{flex-direction:column;gap:7px;}
    }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<?php $heroSubtitle = "Jelajahi keindahan alam abadi di puncak tertinggi."; ?>
<?php
    $imgPath = 'img/default-mountain.jpg';
    if (!empty($trip['gambar'])) {
        $imgPath = (strpos($trip['gambar'],'img/') === 0) ? $trip['gambar'] : 'img/'.$trip['gambar'];
    }
    $soldOut = ($trip['status'] !== 'available' || intval($trip['slot']) <= 0);
?>

<section class="hero">
    <img src="<?=htmlspecialchars($imgPath)?>" alt="Foto Gunung <?=htmlspecialchars($trip['nama_gunung'])?>" />
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-subtitle"><?=htmlspecialchars($heroSubtitle)?></p>
        <div class="hero-text"><?=htmlspecialchars($trip['nama_gunung'])?></div>
        <div class="btn-hero-wrapper">
            <?php if($soldOut): ?>
                <button class="btn-hero" type="button" disabled aria-disabled="true">
                    <i class="bi bi-x-circle"></i> Sold Out
                </button>
            <?php else: ?>
                <button class="btn-hero" type="button" onclick="bookTripModal()" aria-label="Daftar sekarang">
                    <i class="bi bi-calendar-check"></i> Daftar Sekarang
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container" role="main" aria-label="Detail trip <?=htmlspecialchars($trip['nama_gunung'])?>">
    <nav class="info-bar" aria-label="Informasi ringkas trip">
        <div class="info-item" title="Tanggal Trip">
            <i class="bi bi-calendar-event"></i>
            <span>Tanggal</span>
            <span><?=date('d M Y', strtotime($trip['tanggal']))?></span>
        </div>
        <div class="info-item" title="Durasi Trip">
            <i class="bi bi-clock"></i>
            <span>Durasi</span>
            <span><?=htmlspecialchars($trip['durasi'])?></span>
        </div>
        <div class="info-item" title="Slot">
            <i class="bi bi-people-fill"></i>
            <span>Slot Tersisa</span>
            <span><?=htmlspecialchars($trip['slot'])?></span>
        </div>
        <div class="info-item" title="Harga">
            <i class="bi bi-currency-dollar"></i>
            <span>Harga Mulai</span>
            <span>Rp <?=number_format($trip['harga'], 0, ',', '.')?></span>
        </div>
    </nav>
    <div class="content-area">
        <?php
        // Tampilkan tombol lanjutkan pembayaran jika status masih pending
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
            <p><strong>Nama Lokasi :</strong> <?=htmlspecialchars($detail['nama_lokasi'])?></p>
            <p><strong>Alamat :</strong> <?=nl2br(htmlspecialchars($detail['alamat']))?></p>
            <p><strong>Waktu Kumpul :</strong> <?=htmlspecialchars($detail['waktu_kumpul'])?></p>
            <?php if (!empty($detail['link_map'])): ?>
            <div class="map-container">
                <?php
                    $linkMap = trim($detail['link_map']);
                    if (!$linkMap) {
                        echo '<p><em>Belum ada link Google Map Meeting Point</em></p>';
                    } elseif (strpos($linkMap, '/maps/embed?') !== false) {
                        echo '<iframe src="'.htmlspecialchars($linkMap).'" allowfullscreen loading="lazy" style="width:100%;height:320px;border:0;"></iframe>';
                    } elseif (preg_match('#^https://(www\.)?google\.(com|co\.id)/maps/#', $linkMap)) {
                        $embedUrl = str_replace('/maps/', '/maps/embed/', $linkMap);
                        echo '<iframe src="'.htmlspecialchars($embedUrl).'" allowfullscreen loading="lazy" style="width:100%;height:320px;border:0;"></iframe>';
                    } elseif (preg_match('#^https://maps\.app\.goo\.gl/[\w]+#', $linkMap)) {
                        echo '<p><a href="'.htmlspecialchars($linkMap).'" target="_blank" rel="noopener" style="color:#3779e1;font-weight:600;">Buka peta di Google Maps</a></p>';
                    } else {
                        echo '<p><a href="'.htmlspecialchars($linkMap).'" target="_blank" rel="noopener" style="color:#3779e1;font-weight:600;">Buka peta di Google Maps</a></p>';
                    }
                ?>
            </div>
            <?php endif; ?>
        </section>
        <section class="detail-section">
            <h2>Include</h2>
            <?= createIconList($detail['include'], 'bi bi-check-circle-fill icon-list-include') ?>
        </section>
        <section class="detail-section">
            <h2>Exclude</h2>
            <?= createIconList($detail['exclude'], 'bi bi-x-octagon-fill icon-list-exclude') ?>
        </section>
        <section class="detail-section">
            <h2>Syarat & Ketentuan</h2>
            <?= createIconList($detail['syaratKetentuan'], 'bi bi-exclamation-triangle-fill icon-list-syarat') ?>
        </section>
    </div>
</div>

<!-- Modal Pendaftaran Multi Peserta -->
<div id="modal-booking">
    <div class="booking-modal-box">
        <button class="close-btn" onclick="closeBooking()" title="Tutup"><i class="bi bi-x-lg"></i></button>
        <div class="scroll-area-modal">
        <?php if(!$isLogin): ?>
            <h3>Login Diperlukan</h3>
            <p>Silakan login untuk mendaftar dan mengajak teman.</p>
            <a href="login.php" class="btn-main" style="background:#a97c50;">Login Sekarang</a>
            <button class="btn-cancel" type="button" onclick="closeBooking()">Tutup</button>
        <?php else: ?>
            <form class="booking-form" id="form-book-trip" autocomplete="off" enctype="multipart/form-data">
                <h3>Form Pendaftaran Trip</h3>
                <input type="hidden" name="id_trip" value="<?=htmlspecialchars($trip['id_trip'])?>" />
                <input type="hidden" name="jumlah_peserta" id="jumlah-peserta" value="1" />
                <div class="group-title">Data Diri Anda</div>
                <div class="row">
                    <div>
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama[]" required value="<?=htmlspecialchars($userLogin['username'] ?? '')?>" />
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email[]" required value="<?=htmlspecialchars($userLogin['email'] ?? '')?>" />
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
                        <input type="text" name="no_wa[]" required value="<?=htmlspecialchars($userLogin['no_wa'] ?? '')?>" />
                    </div>
                    <div>
                        <label>No. Darurat</label>
                        <input type="text" name="no_wa_darurat[]" />
                    </div>
                </div>
                <label>Alamat</label>
                <textarea name="alamat[]" required><?=htmlspecialchars($userLogin['alamat'] ?? '')?></textarea>
                <label>Riwayat Penyakit</label>
                <input type="text" name="riwayat_penyakit[]" maxlength="60" />
                <label>Foto KTP</label>
                <input type="file" name="foto_ktp[]" accept="image/*" />

                <div id="extra-participants"></div>
                <button class="btn-add" type="button" onclick="addPeserta()">+ Tambah Peserta Lain</button>
                <button type="submit" class="btn-main">Daftar & Booking</button>
                <button type="button" class="btn-cancel" onclick="closeBooking()">Batal</button>                
            </form>
        <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Popup Pembayaran -->
<div id="modal-payment" style="display:none;position:fixed;z-index:9999;inset:0;background:rgba(20,15,12,.88);align-items:center;justify-content:center;">
    <div style="background:#fff;padding:36px 20px;max-width:430px;width:97%;border-radius:17px;box-shadow:0 5px 65px #000a;text-align:center;position:relative;">
        <button onclick="closePayment()" style="position:absolute;top:17px;right:17px;background:none;border:none;font-size:1.19rem;color:#a97c50;cursor:pointer;">
            <i class="bi bi-x-lg"></i>
        </button>
        <div id="hasil-pembayaran"></div>
    </div>
</div>

<script>
function bookTripModal() {
    document.getElementById('modal-booking').classList.add('active');
    document.querySelector('.scroll-area-modal').scrollTop = 0;
}
function closeBooking() {
    document.getElementById('modal-booking').classList.remove('active');
}
function addPeserta() {
    const id = document.querySelectorAll('#extra-participants .peserta-baru').length + 2;
    const kontainer = document.createElement('div');
    kontainer.className = 'peserta-baru';
    kontainer.innerHTML = `
        <div class="group-title">Peserta Tambahan #${id}</div>
        <div class="row">
            <div>
                <label>Nama Lengkap</label>
                <input type="text" name="nama[]" required />
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email[]" required />
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
                <input type="text" name="no_wa[]" required />
            </div>
            <div>
                <label>No. Darurat</label>
                <input type="text" name="no_wa_darurat[]" />
            </div>
        </div>
        <label>Alamat</label>
        <textarea name="alamat[]" required></textarea>
        <label>Riwayat Penyakit</label>
        <input type="text" name="riwayat_penyakit[]" maxlength="60" />
        <label>Foto KTP</label>
        <input type="file" name="foto_ktp[]" accept="image/*" />
        <button class="btn-rm" type="button" onclick="this.parentElement.remove();updateJumlahPeserta();">Hapus Peserta</button>
    `;
    document.getElementById('extra-participants').appendChild(kontainer);
    updateJumlahPeserta();
}
function updateJumlahPeserta() {
    document.getElementById('jumlah-peserta').value = document.querySelectorAll('.peserta-baru').length + 1;
}

<?php if($isLogin): ?>
document.getElementById('form-book-trip').onsubmit = async function(e){
    e.preventDefault();
    const konfirmasi = await Swal.fire({
        title: 'Konfirmasi Data',
        text: 'Apakah Anda yakin data yang Anda masukkan sudah sesuai?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Lanjut Ke Pembayaran',
        cancelButtonText: 'Cek Dulu'
    });
    if (!konfirmasi.isConfirmed) return false;

    const form = e.target;
    const data = new FormData(form);
    try {
        let res = await fetch('backend/booking-api.php', {
            method:'POST',
            body: data
        });
        let text = await res.text();
        let json = false;
        try { json = JSON.parse(text); } catch(ex){ json = false; }
        if(json && json.success && json.id_booking){
            Swal.fire('Berhasil!', json.message, 'success');
            closeBooking();
            setTimeout(() => openPayment(json.id_booking), 1100);
        } else if(json && json.message){
            Swal.fire('Gagal', json.message, 'error');
        } else {
            Swal.fire('Error', 'Terjadi kesalahan jaringan<br><small>Respon: '+text+'</small>', 'error');
        }
    } catch(err){
        Swal.fire('Error', 'Terjadi kesalahan jaringan<br>'+err, 'error');
    }
    return false;
};
<?php endif; ?>

function openPayment(id_booking) {
    document.getElementById('modal-payment').style.display = 'flex';
    document.getElementById('hasil-pembayaran').innerHTML = "Memproses pembayaran...";
    fetch('backend/payment-api.php?booking=' + id_booking)
        .then(r => r.json())
        .then(resp => {
            if(resp.snap_token){
                window.snap.pay(resp.snap_token, {
                    onSuccess: function(result){
                        document.getElementById('hasil-pembayaran').innerHTML = "Pembayaran sukses!";
                        setTimeout(closePayment, 2000);
                    },
                    onPending: function(result){
                        document.getElementById('hasil-pembayaran').innerHTML = "Status masih pending.";
                    },
                    onError: function(result){
                        document.getElementById('hasil-pembayaran').innerHTML = "Pembayaran gagal!";
                    },
                    onClose: function(){
                        document.getElementById('hasil-pembayaran').innerHTML = "Popup ditutup.";
                    }
                });
            } else {
                document.getElementById('hasil-pembayaran').innerHTML = resp.error || 'Snap gagal';
            }
        });
}
function closePayment() {
    document.getElementById('modal-payment').style.display = 'none';
}
</script>
</body>
</html>
