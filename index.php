<?php
require_once 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Majelis MDPL</title>

  <!-- CSS utama -->
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <!-- Config JS - UNTUK DYNAMIC URL ROUTING -->
  <script src="frontend/config.js"></script>

</head>

<body>
  <!-- Navbar -->
  <?php include 'navbar.php'; ?>
  <?php include 'auth-modals.php'; ?>

  <!-- Hero -->
  <section class="hero-home" id="home">
    <div class="hero-bg-container">
      <img src="assets/profil_foto.jpeg" alt="Gunung Bromo" class="hero-bg" id="hero-bg">
      <div class="hero-overlay"></div>
    </div>
    <div class="hero-content">
      <span class="hero-offer" id="hero-offer">BEST OFFERS</span>
      <h1 class="hero-title" id="hero-title">GUNUNG BROMO</h1>
      <div class="hero-days hero-days-wrapper">
        <span class="highlight" id="hero-days">1 HARI</span>
      </div>
      <p class="hero-desc" id="hero-desc">Rasakan keindahan golden sunrise Gunung Bromo yang menyegarkan</p>
      <a href="#paketTrips" class="hero-btn" style="text-decoration: none; ">DETAIL</a>

      <div class="hero-carousel" id="hero-carousel">
        <img src="assets/herooo.jpg" alt="Gunung Bromo" class="carousel-item active" data-index="0">
        <img src="assets/ijen.jpg" alt="Sunrise" class="carousel-item" data-index="1">
        <img src="assets/rinjani.jpg" alt="Forest" class="carousel-item" data-index="2">
      </div>
    </div>
  </section>

  <!-- profile -->
  <section class="why-explorer style-4" id="profile">
    <!-- Animated Gradient Background - Brown Theme -->
    <div class="gradient-bg"></div>
    <div class="blur"></div>
    <div class="floating-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <!-- Header -->
    <div class="profile-header" data-aos="fade-down" data-aos-duration="600" data-aos-once="true">
      <h2 class="profile-title">
        <span class="gradient-text">Kenapa Pilih</span>
        <span class="gradient-text">Majelis Mdpl?</span>
      </h2>
      <p class="profile-subtitle">
        Pengalaman petualangan yang tak terlupakan dimulai dari sini
      </p>
    </div>

    <!-- Glass Cards Container -->
    <div class="glass-cards-wrapper">
      <!-- Left: Large Feature Card -->
      <div class="main-feature-card" data-aos="fade-right" data-aos-delay="200" data-aos-once="true">
        <div class="feature-badge">
          <i class="fas fa-award"></i>
          <span>Trusted Partner</span>
        </div>
        <h3>5000+ Happy Travelers</h3>
        <p>Bergabunglah dengan ribuan traveler yang telah merasakan pengalaman tak terlupakan bersama kami</p>

        <div class="feature-stats">
          <div class="stat-mini">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-info">
              <div class="stat-num">4.9</div>
              <div class="stat-text">Rating</div>
            </div>
          </div>
          <div class="stat-mini">
            <div class="stat-icon"><i class="fas fa-mountain"></i></div>
            <div class="stat-info">
              <div class="stat-num">50+</div>
              <div class="stat-text">Destinations</div>
            </div>
          </div>
        </div>

        <!-- Image Preview (Not Video) -->
        <div class="image-preview">
          <img src="assets/profil_foto.jpeg" alt="Adventure">
        </div>
      </div>

      <!-- Right: Grid of Small Cards (No Learn More Button) -->
      <div class="features-grid">
        <div class="glass-card" data-aos="zoom-in" data-aos-delay="300" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-route"></i>
          </div>
          <h4>Flexible Routes</h4>
          <p>Pilih destinasi sesuai keinginan dengan berbagai pilihan rute</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="400" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4>Safety First</h4>
          <p>Keamanan prioritas dengan guide bersertifikat</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="500" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <h4>Easy Payment</h4>
          <p>Berbagai metode pembayaran modern dan aman</p>
        </div>

        <div class="glass-card" data-aos="zoom-in" data-aos-delay="600" data-aos-once="true">
          <div class="card-icon gradient-icon">
            <i class="fas fa-headset"></i>
          </div>
          <h4>24/7 Support</h4>
          <p>Tim support siap membantu kapan saja</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Paket Trip Section -->
  <section class="paket-trip-section" id="paketTrips">
    <!-- Simple Pattern Background -->
    <div class="bg-pattern-simple"></div>

    <!-- Section Header -->
    <div class="section-header" data-aos="fade-up" data-aos-duration="600" data-aos-once="true">
      <h2 class="section-title">
        <span data-aos="fade-right" data-aos-delay="100" data-aos-once="true">Paket</span>
        <span data-aos="fade-left" data-aos-delay="200" data-aos-once="true">Trip</span>
        <span data-aos="fade-right" data-aos-delay="300" data-aos-once="true">Kami</span>
      </h2>
      <p class="section-subtitle" data-aos="fade-up" data-aos-delay="400" data-aos-once="true">
        Pilihan destinasi petualangan terbaik untuk liburan Anda
      </p>
    </div>

    <!-- Destination Carousel -->
    <div class="destination-carousel" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300" data-aos-once="true">
      <!-- Carousel Track (Horizontal Scroll) -->
      <div class="carousel-track">
        <!-- Loading spinner -->
        <div class="loading-container">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>

      <!-- Scroll Indicators -->
      <div class="scroll-indicators">
        <button class="scroll-indicator prev" aria-label="Scroll Left">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="scroll-indicator next" aria-label="Scroll Right">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Mountain Wave Divider -->
    <div class="mountain-wave-divider">
      <svg viewBox="0 0 1440 200" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <defs>
          <linearGradient id="mountainGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#8b5e3c;stop-opacity:0.6" />
            <stop offset="100%" style="stop-color:#5c3922;stop-opacity:0.8" />
          </linearGradient>
        </defs>
        <path fill="url(#mountainGradient)" d="M0,150 L60,130 L120,145 L180,120 L240,135 L300,110 L360,125 L420,100 L480,115 L540,95 L600,110 L660,90 L720,105 L780,85 L840,100 L900,80 L960,95 L1020,75 L1080,90 L1140,70 L1200,85 L1260,65 L1320,80 L1380,60 L1440,75 L1440,200 L0,200 Z"></path>
        <path fill="#4b3625" opacity="0.5" d="M0,165 L80,155 L160,165 L240,150 L320,160 L400,145 L480,155 L560,140 L640,150 L720,135 L800,145 L880,130 L960,140 L1040,125 L1120,135 L1200,120 L1280,130 L1360,115 L1440,125 L1440,200 L0,200 Z"></path>
      </svg>
    </div>
  </section>

  <!-- Gallery Section -->
  <section class="gallery-cardstyle" id="gallerys">
    <!-- Particles.js Background (reduced) -->
    <div id="particles-js"></div>

    <!-- Gallery Header -->
    <div class="gallery-header">
      <h2 class="gallery-title">
        <span>Galeri</span>
        <span>Foto</span>
        <span>Kami</span>
      </h2>
      <p class="gallery-subtitle">
        Dokumentasi perjalanan petualangan yang tak terlupakan
      </p>
    </div>

    <!-- Card Grid -->
    <div class="card-grid">
      <div class="card">
        <div class="card-overlay"></div>
        <img src="assets/gambar3.jpg" alt="Gunung Bromo" />
        <div class="caption">
          <i class="fas fa-mountain"></i>
          Gunung Bromo Sunrise
        </div>
      </div>

      <div class="card">
        <div class="card-overlay"></div>
        <img src="assets/gambar2.jpg" alt="Camping" />
        <div class="caption">
          <i class="fas fa-campground"></i>
          Camping Savana
        </div>
      </div>

      <div class="card">
        <div class="card-overlay"></div>
        <img src="assets/gambar1.jpg" alt="Trip Bersama" />
        <div class="caption">
          <i class="fas fa-users"></i>
          Trip Seru Bersama
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="testimonials">
    <!-- Modern Background Pattern -->
    <div class="testimonial-bg-pattern"></div>

    <div class="container">
      <div class="testimonial-header" data-aos="fade-up" data-aos-once="true">
        <h2><span class="title-large">Apa Kata Mereka?</span></h2>
        <p class="testimonial-subtitle">Pengalaman nyata dari para traveler kami</p>
      </div>

      <!-- Row 1: Moving LEFT -->
      <div class="testimonial-row row-left">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Trip ke Rinjani sangat terorganisir. Guide-nya asik dan perhatian. Saya yang pemula merasa aman banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Rudi"></div>
              <div class="author-details">
                <h4>Rudi Saputra</h4>
                <p>Jakarta</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Sunrise di Bromo, camping di savana, semua sempurna. Majelis MDPL benar-benar profesional!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Andi"></div>
              <div class="author-details">
                <h4>Andi Pratama</h4>
                <p>Surabaya</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Fasilitas lengkap, harga terjangkau, pelayanan memuaskan. Recommended banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Sarah"></div>
              <div class="author-details">
                <h4>Sarah Wijaya</h4>
                <p>Yogyakarta</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 2: Moving RIGHT -->
      <div class="testimonial-row row-right">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Baru pertama kali ikut open trip, tapi langsung jatuh cinta. Banyak teman baru dan pengalaman tak terlupakan.</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dewi"></div>
              <div class="author-details">
                <h4>Dewi Lestari</h4>
                <p>Bandung</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Tim guide sangat berpengalaman dan ramah. Perjalanan jadi lebih seru dan aman. Pasti bakal ikut lagi!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/85.jpg" alt="Budi"></div>
              <div class="author-details">
                <h4>Budi Hermawan</h4>
                <p>Semarang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Dokumentasi keren, momen tak terlupakan. Customer service responsif banget!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/90.jpg" alt="Nina"></div>
              <div class="author-details">
                <h4>Nina Agustina</h4>
                <p>Malang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 3: Moving LEFT -->
      <div class="testimonial-row row-left">
        <div class="testimonial-track">
          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Pengalaman hiking terbaik! View spektakuler, persiapan matang, semua worth it banget.</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Doni"></div>
              <div class="author-details">
                <h4>Doni Prasetyo</h4>
                <p>Bekasi</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Pelayanan ramah, harga bersahabat, destinasi keren. Majelis MDPL is the best!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/women/55.jpg" alt="Rina"></div>
              <div class="author-details">
                <h4>Rina Sari</h4>
                <p>Tangerang</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <p class="testimonial-text">Open trip paling seru yang pernah saya ikuti. Semua terorganisir dengan baik!</p>
            <div class="testimonial-author">
              <div class="author-image"><img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Eko"></div>
              <div class="author-details">
                <h4>Eko Prabowo</h4>
                <p>Solo</p>
                <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>

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

  <!-- AOS Library Script -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <!-- Load SweetAlert first -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Load Particles.js -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

  <!-- Global functions that might be needed -->
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      mirror: false,
      offset: 100
    });

    // ========== WHATSAPP BUTTON - CUTE & RESPONSIVE ==========
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

    // ========== GALERI API ==========
    fetch(getApiUrl('galeri-api.php') + '?action=get')
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          const galleryContainer = document.querySelector('.card-grid');
          galleryContainer.innerHTML = '';
          result.data.forEach(item => {
            const card = document.createElement('div');
            card.classList.add('card');
            card.innerHTML = `<img src="img/${item.gallery}" alt="Foto" />`;
            galleryContainer.appendChild(card);
          });
        } else {
          console.error(result.message);
        }
      })
      .catch(err => console.error(err));

    // ========== SECTION HERO HOME ==========
    document.addEventListener('DOMContentLoaded', () => {
      const heroSlides = [{
          image: "assets/herooo.jpg",
          title: "GUNUNG BROMO",
          offer: "BEST OFFERS",
          days: "1 HARI",
          desc: "Rasakan keindahan golden sunrise Gunung Bromo yang menyegarkan"
        },
        {
          image: "assets/ijen.jpg",
          title: "GUNUNG IJEN",
          offer: "IJEN ADVENTURE",
          days: "2 HARI",
          desc: "Nikmati mendaki gunung berapi biru di Jawa Timur."
        },
        {
          image: "assets/rinjani.jpg",
          title: "GUNUNG RINJANI",
          offer: "RINJANI EXPEDITION",
          days: "3 HARI",
          desc: "Rasakan petualangan mendaki puncak tertinggi di Nusa Tenggara Barat."
        }
      ];

      const heroBg = document.getElementById('hero-bg');
      const carouselItems = document.querySelectorAll('.carousel-item');
      const titleElem = document.getElementById('hero-title');
      const offerElem = document.getElementById('hero-offer');
      const daysElem = document.getElementById('hero-days');
      const descElem = document.getElementById('hero-desc');

      if (!heroBg || !titleElem || !offerElem || !daysElem || !descElem) {
        console.error('Hero elements not found!');
        return;
      }

      const daysWrapper = daysElem.closest('.hero-days-wrapper') || daysElem.parentElement;

      let currentIndex = 0;
      let slideInterval;
      let isAnimating = false;
      const total = heroSlides.length;

      function setActiveIndex(index) {
        if (isAnimating) return;
        isAnimating = true;

        carouselItems.forEach((item, i) => {
          item.classList.toggle('active', i === index);
        });

        const textElements = [offerElem, titleElem, daysWrapper, descElem];

        textElements.forEach(elem => {
          if (elem) {
            elem.classList.remove('fade-in');
            elem.classList.add('fade-out');
          }
        });

        setTimeout(() => {
          heroBg.classList.remove('fade-in');
          heroBg.classList.add('fade-out');
        }, 300);

        setTimeout(() => {
          heroBg.src = heroSlides[index].image;

          heroBg.classList.remove('fade-out');
          void heroBg.offsetWidth;
          heroBg.classList.add('fade-in');

        }, 900);

        setTimeout(() => {
          titleElem.textContent = heroSlides[index].title;
          offerElem.textContent = heroSlides[index].offer;
          daysElem.textContent = heroSlides[index].days;
          descElem.textContent = heroSlides[index].desc;

          textElements.forEach(elem => {
            if (elem) elem.classList.remove('fade-out');
          });

          void offerElem.offsetWidth;

          setTimeout(() => offerElem.classList.add('fade-in'), 100);
          setTimeout(() => titleElem.classList.add('fade-in'), 250);
          setTimeout(() => {
            if (daysWrapper) daysWrapper.classList.add('fade-in');
          }, 400);
          setTimeout(() => descElem.classList.add('fade-in'), 550);

          currentIndex = index;

          setTimeout(() => {
            isAnimating = false;
          }, 1000);

        }, 1400);
      }

      function startSlideShow() {
        slideInterval = setInterval(() => {
          let nextIndex = (currentIndex + 1) % total;
          setActiveIndex(nextIndex);
        }, 6000);
      }

      carouselItems.forEach(item => {
        item.addEventListener('click', () => {
          if (isAnimating) return;
          clearInterval(slideInterval);
          setActiveIndex(parseInt(item.dataset.index));
          setTimeout(startSlideShow, 2000);
        });
      });

      setActiveIndex(0);
      startSlideShow();
    });

    // ========== PARTICLES JS ==========
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
          particles: {
            number: {
              value: 40,
              density: {
                enable: true,
                value_area: 1000
              }
            },
            color: {
              value: ['#ffffff', '#ffd44a']
            },
            shape: {
              type: 'circle'
            },
            opacity: {
              value: 0.3,
              random: true,
              anim: {
                enable: true,
                speed: 0.5,
                opacity_min: 0.1,
                sync: false
              }
            },
            size: {
              value: 3,
              random: true,
              anim: {
                enable: false
              }
            },
            line_linked: {
              enable: true,
              distance: 150,
              color: '#ffd44a',
              opacity: 0.15,
              width: 1
            },
            move: {
              enable: true,
              speed: 1,
              direction: 'none',
              random: false,
              straight: false,
              out_mode: 'out',
              bounce: false
            }
          },
          interactivity: {
            detect_on: 'canvas',
            events: {
              onhover: {
                enable: false
              },
              onclick: {
                enable: false
              },
              resize: true
            }
          },
          retina_detect: true
        });
      }
    });

    // ========== COUNTER ANIMATION ==========
    document.addEventListener("DOMContentLoaded", function() {
      const counters = document.querySelectorAll('.stat-number');
      const speed = 200;

      const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const inc = target / speed;

        if (count < target) {
          counter.innerText = Math.ceil(count + inc);
          setTimeout(() => animateCounter(counter), 1);
        } else {
          counter.innerText = target;
        }
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            counters.forEach(counter => animateCounter(counter));
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.5
      });

      const statsContainer = document.querySelector('.stats-container');
      if (statsContainer) {
        observer.observe(statsContainer);
      }
    });

    // ========== TESTIMONIAL INFINITE MARQUEE ==========
    document.addEventListener('DOMContentLoaded', function() {
      const rows = document.querySelectorAll('.testimonial-row');

      rows.forEach(row => {
        const track = row.querySelector('.testimonial-track');
        const cards = Array.from(track.children);

        cards.forEach(card => {
          const clone1 = card.cloneNode(true);
          const clone2 = card.cloneNode(true);
          track.appendChild(clone1);
          track.appendChild(clone2);
        });
      });
    });
  </script>

  <!-- Load external JavaScript files -->
  <script src="frontend/registrasi.js"></script>
  <script src="frontend/login.js"></script>
  <script src="frontend/trip-user.js"></script>

</body>

</html>
