<?php
// trip_detail.php opsi B dengan header slide otomatis dan tanpa galeri gambar
if (!isset($_GET['id'])) {
    header('Location: trip.php');
    exit;
}
$id = (int)$_GET['id'];
$file = __DIR__ . "/trips.json";
$trips = [];
if (file_exists($file)) {
    $trips = json_decode(file_get_contents($file), true) ?: [];
}
$trip = null;
foreach ($trips as $t) {
    if ((int)$t['id'] === $id) {
        $trip = $t;
        break;
    }
}
if (!$trip) {
    echo "Trip tidak ditemukan.";
    exit;
}

$images = !empty($trip['gambar']) ? (is_array($trip['gambar']) ? $trip['gambar'] : [$trip['gambar']]) : ['default.jpg'];
$include = $trip['include'] ?? "Tidak ada informasi";
$exclude = $trip['exclude'] ?? "Tidak ada informasi";
$syarat = $trip['syarat'] ?? "Tidak ada informasi";
$meeting_point = $trip['meeting_point'] ?? [
    'nama' => '-',
    'alamat' => '-',
    'waktu' => '-',
    'link_maps' => '#'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Detail Trip - <?= htmlspecialchars($trip['nama_gunung']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body {
        font-family: 'Nunito', sans-serif;
        background: #f0f3f7;
        color: #2c3e50;
        scroll-behavior: smooth;
    }
    .hero-header {
        height: 80vh;
        position: relative;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        font-weight: 700;
        text-shadow: 0 3px 15px rgba(0,0,0,0.6);
        background-position: center center;
        background-size: cover;
        background-repeat: no-repeat;
        transition: background-image 1s ease-in-out;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 0;
    }
    .hero-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 800px;
        padding: 0 15px;
    }
    
    #sideNav {
        position: sticky;
        top: 100px;
        width: 220px;
    }
    #sideNav .nav-link {
        color: #34495e;
        font-weight: 600;
        margin-bottom: 10px;
        border-left: 4px solid transparent;
        padding-left: 15px;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }
    #sideNav .nav-link.active {
        border-left-color: #e67e22;
        background-color: #fdebd3;
        color: #e67e22;
    }
    #sideNav .nav-link:hover {
        color: #e67e22;
        background-color: #fef4e7;
    }
    /* Main content */
    .content-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 7px 22px rgba(0,0,0,0.1);
    }
    .content-section h2 {
        font-weight: 700;
        color: #e67e22;
        margin-bottom: 20px;
        border-bottom: 3px solid #e67e22;
        display: inline-block;
        padding-bottom: 6px;
    }
    .info-list {
        list-style: none;
        padding-left: 0;
        font-size: 1.1rem;
        color: #555;
    }
    .info-list li {
        margin-bottom: 14px;
    }
    .info-label {
        font-weight: 600;
        display: inline-block;
        width: 140px;
        color: #34495e;
    }
    .maps-link {
        color: #e67e22;
        font-weight: 600;
        text-decoration: none;
    }
    .maps-link:hover {
        text-decoration: underline;
    }
    @media (max-width: 991px) {
        #sideNav {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
            top: auto;
        }
    }
</style>
</head>
<body>

<div class="hero-header" role="banner" aria-label="Gambar utama trip <?= htmlspecialchars($trip['nama_gunung']) ?>">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <?= htmlspecialchars($trip['nama_gunung']) ?>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Side Nav for Scrollspy -->
        <nav id="sideNav" class="col-lg-3 mb-4" aria-label="Navigasi informasi trip">
            <nav class="nav flex-column sticky-top">
                <a class="nav-link active" href="#info">Informasi Trip</a>
                <a class="nav-link" href="#include">Include</a>
                <a class="nav-link" href="#exclude">Exclude</a>
                <a class="nav-link" href="#syarat">Syarat & Ketentuan</a>
                <a class="nav-link" href="#meeting">Meeting Point</a>
            </nav>
        </nav>
        <!-- Main Content -->
        <main class="col-lg-9">
            <section id="info" class="content-section" tabindex="0" aria-label="Informasi Trip">
                <h2>Informasi Trip</h2>
                <ul class="info-list">
                    <li><span class="info-label">Tanggal:</span> <?= date("d M Y", strtotime($trip['tanggal'])) ?></li>
                    <li><span class="info-label">Durasi:</span> <?= ($trip['jenis_trip'] === "Camp") ? htmlspecialchars($trip['durasi']) : "1 hari" ?></li>
                    <li><span class="info-label">Status:</span> <?= ($trip['status'] == 'available') ? '<span style="color:green;">Available</span>' : '<span style="color:red;">Sold</span>' ?></li>
                    <li><span class="info-label">Via Gunung:</span> <?= htmlspecialchars($trip['via_gunung'] ?? '-') ?></li>
                    <li><span class="info-label">Slot Peserta:</span> <?= htmlspecialchars($trip['slot']) ?> orang</li>
                </ul>
            </section>

            <section id="include" class="content-section" tabindex="0" aria-label="Include trip">
                <h2>Include</h2>
                <p><?= nl2br(htmlspecialchars($include)) ?></p>
            </section>

            <section id="exclude" class="content-section" tabindex="0" aria-label="Exclude trip">
                <h2>Exclude</h2>
                <p><?= nl2br(htmlspecialchars($exclude)) ?></p>
            </section>

            <section id="syarat" class="content-section" tabindex="0" aria-label="Syarat dan ketentuan trip">
                <h2>Syarat & Ketentuan</h2>
                <p><?= nl2br(htmlspecialchars($syarat)) ?></p>
            </section>

            <section id="meeting" class="content-section" tabindex="0" aria-label="Meeting Point">
                <h2>Meeting Point</h2>
                <p><strong>Nama Lokasi:</strong> <?= htmlspecialchars($meeting_point['nama'] ?? '-') ?></p>
                <p><strong>Alamat:</strong><br><?= nl2br(htmlspecialchars($meeting_point['alamat'] ?? '-')) ?></p>
                <p><strong>Waktu Kumpul:</strong> <?= htmlspecialchars($meeting_point['waktu'] ?? '-') ?></p>
                <p><strong>Link Maps:</strong> 
                    <?php if (!empty($meeting_point['link_maps']) && $meeting_point['link_maps'] !== '#'): ?>
                        <a href="<?= htmlspecialchars($meeting_point['link_maps']) ?>" target="_blank" rel="noopener noreferrer" class="maps-link">Buka Google Maps</a>
                    <?php else: ?> - <?php endif; ?>
                </p>
            </section>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const hero = document.querySelector('.hero-header');
    const images = <?= json_encode($images); ?>;
    let currentIndex = 0;

    function changeBackground() {
        hero.style.backgroundImage = `url('../img/${images[currentIndex]}')`;
        currentIndex = (currentIndex + 1) % images.length;
    }
    changeBackground();
    setInterval(changeBackground, 5000); 

    const navLinks = document.querySelectorAll('#sideNav .nav-link');
    const sections = Array.from(navLinks).map(link => document.querySelector(link.getAttribute('href')));

    function onScroll() {
        const scrollPos = window.scrollY + 120;
        let currentSection = sections.findIndex((sec, i) => {
            const top = sec.offsetTop;
            const nextTop = i + 1 < sections.length ? sections[i+1].offsetTop : Infinity;
            return scrollPos >= top && scrollPos < nextTop;
        });
        if (currentSection === -1) currentSection = 0;
        navLinks.forEach(link => link.classList.remove('active'));
        if(navLinks[currentSection]) navLinks[currentSection].classList.add('active');
    }
    window.addEventListener('scroll', onScroll);
    onScroll();
})();
</script>

</body>
</html>
