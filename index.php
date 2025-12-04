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
  <?php include 'flash_handler.php'; ?>

  <!-- Hero -->
  <section class="hero-slider-section" id="home">

    <div class="slider-container">

      <div class="slide active">
        <div class="slide-bg">
          <div class="overlay"></div>
          <img src="assets/Gunung.jpg" alt="Mountain View">
        </div>
        <div class="container slide-content">
          <span class="slide-badge">MAJELIS MDPL ADVENTURE</span>
          <h1>YOUR BEST PARTNER<br>FOR HIKING ADVENTURE</h1>
          <p>Layanan pendakian profesional dengan prioritas keamanan, kenyamanan, dan dokumentasi terbaik.</p>
          <div class="btn-wrapper">
            <a href="#paketTrips" class="hero-btn primary">Lihat Layanan Kami</a>
          </div>
        </div>
      </div>

      <div class="slide">
        <div class="slide-bg">
          <div class="overlay"></div>
          <img src="assets/Prau.jpg" alt="Gunung Prau Dieng">
        </div>
        <div class="container slide-content">
          <span class="slide-badge">GOLDEN HOUR</span>
          <h1>MAGICAL<br>MT. PRAU</h1>
          <p>Nikmati pesona matahari terbit terbaik di Asia Tenggara dengan jalur pendakian yang ramah bagi pemula. Latar megah Sindoro-Sumbing siap memanjakan mata Anda.</p>
          <div class="btn-wrapper">
            <a href="#paketTrips" class="hero-btn primary">Lihat Destinasi</a>
          </div>
        </div>
      </div>

      <div class="slide">
        <div class="slide-bg">
          <div class="overlay"></div>
          <img src="assets/Anjani.jpg" alt="Hiking Challenge">
        </div>
        <div class="container slide-content">
          <span class="slide-badge">THE JOURNEY</span>
          <h1>LIMITLESS<br>EXPEDITION</h1>
          <p>Setiap puncak mengajarkan tentang perjuangan dan kerendahan hati. Kami hadir sebagai pendukung teknis agar Anda fokus pada tujuan.</p>
          <div class="btn-wrapper">
            <a href="#paketTrips" class="hero-btn primary">Mulai Petualangan</a>
          </div>
        </div>
      </div>

    </div>

    <button class="slider-nav prev" onclick="moveSlide(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="slider-nav next" onclick="moveSlide(1)"><i class="fas fa-chevron-right"></i></button>

  </section>

  <!-- Why Us Section -->
  <section class="why-us-section" id="profile">
    <div class="bg-pattern-dots"></div>

    <div class="container">

      <div class="section-header text-center" data-aos="fade-up">
        <span class="header-tagline">
          <i class="fas fa-crown"></i> KEUNGGULAN KAMI
        </span>
        <h2 class="section-title">
          Gak Perlu Ribet, <span class="text-highlight">Tinggal Berangkat!</span>
        </h2>
        <p class="section-subtitle">
          Mendaki itu soal menikmati perjalanan. Biarkan kami yang mengurus
          logistik, keamanan, dan kenyamanan Anda sampai puncak.
        </p>
      </div>

      <div class="features-grid">

        <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-box">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="feature-content">
            <h3>Aman Terkendali</h3>
            <p>Safety first. Tim kami bersertifikasi P3K dan sangat paham prosedur keamanan pendakian.</p>
          </div>
        </div>

        <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <i class="fas fa-user-friends"></i>
          </div>
          <div class="feature-content">
            <h3>Guide Asik</h3>
            <p>Guide berpengalaman yang tidak hanya hafal jalur, tapi juga seru diajak ngobrol sepanjang jalan.</p>
          </div>
        </div>

        <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
          <div class="icon-box">
            <i class="fas fa-utensils"></i>
          </div>
          <div class="feature-content">
            <h3>Makan Enak</h3>
            <p>Logistik terjamin! Menu makanan bergizi dan lezat dimasak langsung oleh koki gunung.</p>
          </div>
        </div>

        <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
          <div class="icon-box">
            <i class="fas fa-campground"></i>
          </div>
          <div class="feature-content">
            <h3>Alat Premium</h3>
            <p>Tenda, matras, dan sleeping bag selalu dicuci bersih, wangi, dan dalam kondisi prima.</p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- Mobile App Showcase Section -->
  <section class="app-showcase-section" id="mobile-app">
    <div class="container">
      <div class="app-wrapper">

        <div class="app-content">
          <span class="app-badge">COMING SOON</span>
          <h2 class="app-title">Petualangan dalam <br>Genggamanmu.</h2>
          <p class="app-desc">
            Nikmati kemudahan booking, cek jadwal trip, dan diskusi dengan guide langsung dari smartphone. Aplikasi Majelis MDPL hadir untuk mempermudah setiap langkahmu.
          </p>

          <ul class="app-features">
            <li><i class="fas fa-check-circle"></i> Booking Cepat & Mudah</li>
            <li><i class="fas fa-check-circle"></i> Notifikasi Jadwal Real-time</li>
            <li><i class="fas fa-check-circle"></i> Chat Guide & Porter</li>
          </ul>

          <div class="store-buttons">
            <a href="#" class="store-btn">
              <i class="fab fa-google-play"></i>
              <div class="btn-text">
                <small>GET IT ON</small>
                <span>Google Play</span>
              </div>
            </a>
            <a href="#" class="store-btn">
              <i class="fab fa-apple"></i>
              <div class="btn-text">
                <small>Download on the</small>
                <span>App Store</span>
              </div>
            </a>
          </div>
        </div>

        <div class="app-visual">
          <div class="circle-bg"></div>

          <div class="phones-container">

            <div class="phone-frame side-phone left">
              <div class="notch"></div>
              <img src="assets/Mountain.jpg" class="app-screen" alt="Screen 1">
            </div>

            <div class="phone-frame side-phone right">
              <div class="notch"></div>
              <img src="assets/Mobile1.jpg" class="app-screen" alt="Screen 2">
            </div>

            <div class="phone-frame center-phone">
              <div class="notch"></div>
              <img src="assets/Mobile2.jpg" class="app-screen" alt="Main Screen">
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- Paket Trip Section -->
  <section class="paket-trip-section" id="paketTrips">
    <div class="bg-pattern-simple"></div>

    <div class="section-header" data-aos="fade-up" data-aos-duration="800">
      <span class="header-tagline">
        <i class="fas fa-map-marked-alt"></i> EXPLORE INDONESIA
      </span>
      <h2 class="section-title">
        Paket <span class="text-highlight">Trip Kami.</span>
      </h2>
      <p class="section-subtitle">
        Temukan destinasi petualangan terbaik dengan fasilitas lengkap,
        aman, dan pengalaman tak terlupakan.
      </p>

    </div>

    <div class="destination-carousel" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300" data-aos-once="true">

      <div class="carousel-track">
        <div class="loading-container" style="width: 100%; display: flex; justify-content: center; padding: 50px;">
          <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; border: 4px solid #ddd; border-top: 4px solid #2ecc71; border-radius: 50%; animation: spin 1s linear infinite;">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>

      <div class="scroll-indicators">
        <button class="scroll-indicator prev" aria-label="Scroll Left" onclick="document.querySelector('.carousel-track').scrollBy({left: -320, behavior: 'smooth'})">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="scroll-indicator next" aria-label="Scroll Right" onclick="document.querySelector('.carousel-track').scrollBy({left: 320, behavior: 'smooth'})">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

    </div>
  </section>

  <style>
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>

  <!-- Gallery Section -->
  <section class="gallery-section" id="gallerys">

    <div id="particles-js"></div>
    <div class="container">
      <div class="section-header text-center" data-aos="fade-up">
        <span class="header-tagline">
          <i class="fas fa-camera"></i> DOKUMENTASI
        </span>
        <h2 class="section-title">
          Galeri <span class="text-highlight">Petualangan.</span>
        </h2>
        <p class="section-subtitle">
          Kumpulan momen terbaik para pendaki bersama Majelis MDPL.
        </p>
      </div>

      <div class="card-grid" id="galleryContainer">
        <div class="loading-container" style="grid-column: 1/-1; display: flex; justify-content: center; padding: 50px;">
          <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonial-section" id="testimonials">
    <div class="container-fluid">
      <div class="section-header text-center" data-aos="fade-up">
        <span class="header-tagline">
          <i class="fas fa-comments"></i> KATA MEREKA
        </span>
        <h2 class="section-title">
          Cerita para <span class="text-highlight">Pendaki.</span>
        </h2>
        <p class="section-subtitle">
          Pengalaman nyata tak terlupakan bersama Majelis MDPL.
        </p>
      </div>

      <div class="marquee-wrapper">

        <div class="marquee-row scroll-left">
          <div class="marquee-content">
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Trip ke Rinjani sangat terorganisir. Guide-nya asik, logistik melimpah. Saya pemula tapi merasa sangat aman!"</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                <div>
                  <h5>Rudi Saputra</h5>
                  <span>Jakarta</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Gak nyesel ikut open trip di sini. Temen baru seru-seru, makanannya enak banget kayak di resto!"</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User">
                <div>
                  <h5>Siti Aminah</h5>
                  <span>Bandung</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Dokumentasinya juara! Pulang trip langsung bisa pamer di Instagram tanpa perlu edit lagi."</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/men/85.jpg" alt="User">
                <div>
                  <h5>Budi Santoso</h5>
                  <span>Surabaya</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Pelayanan ramah dan fast respon. Alat camping wangi dan bersih. Recommended banget!"</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User">
                <div>
                  <h5>Sarah Wijaya</h5>
                  <span>Yogyakarta</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="marquee-row scroll-right">
          <div class="marquee-content">
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Pengalaman sunrise di Prau yang magis. Terima kasih Majelis MDPL sudah mewujudkan mimpi saya."</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/women/90.jpg" alt="User">
                <div>
                  <h5>Nina Agustina</h5>
                  <span>Malang</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Porter sangat membantu, guide sabar banget nungguin saya yang jalannya lambat. Top service!"</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="User">
                <div>
                  <h5>Doni Prasetyo</h5>
                  <span>Bekasi</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Harga sangat affordable untuk fasilitas selengkap ini. Pasti bakal repeat order ke Semeru nanti."</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/women/55.jpg" alt="User">
                <div>
                  <h5>Rina Sari</h5>
                  <span>Tangerang</span>
                </div>
              </div>
            </div>
            <div class="testi-card">
              <div class="quote-icon"><i class="fas fa-quote-right"></i></div>
              <p class="testi-text">"Solid banget timnya. Trek susah jadi berasa ringan karena suasananya fun abis."</p>
              <div class="user-info">
                <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="User">
                <div>
                  <h5>Fajar Nugraha</h5>
                  <span>Bogor</span>
                </div>
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
    /* =========================================
   INFINITE MARQUEE LOGIC
   ========================================= */
    document.addEventListener("DOMContentLoaded", function() {
      const marquees = document.querySelectorAll(".marquee-content");

      marquees.forEach((marquee) => {
        // Clone (Duplikat) semua isi kartu agar loop tidak putus
        const content = marquee.innerHTML;
        marquee.innerHTML = content + content + content; // Duplikat 3x biar aman di layar lebar
      });
    });


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
      const nomor = "6281358609650";
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

    // ========== GALERI API (FIXED) ==========
    fetch(getApiUrl('galeri-api.php') + '?action=get')
      .then(response => response.json())
      .then(result => {
        const galleryContainer = document.getElementById('galleryContainer'); // Gunakan ID
        if (galleryContainer && result.success) {
          galleryContainer.innerHTML = ''; // Bersihkan loader

          result.data.forEach((item, index) => {
            // Buat elemen div container kartu
            const card = document.createElement('div');

            // PENTING: Gunakan class 'gallery-card' agar CSS terbaca!
            card.className = 'gallery-card';

            // Tambahkan animasi AOS
            card.setAttribute('data-aos', 'fade-up');
            card.setAttribute('data-aos-delay', index * 100);

            // Cek path gambar (jaga-jaga)
            let imgPath = item.gallery;
            if (!imgPath.includes('/')) {
              imgPath = 'img/' + imgPath;
            }

            // Struktur HTML yang Cocok dengan CSS
            card.innerHTML = `
                <img src="${imgPath}" alt="Foto Galeri" loading="lazy">
                <div class="card-overlay">
                    <div class="caption">
                        <h4><i class="fas fa-camera"></i> Momen Seru</h4>
                        <p>Dokumentasi perjalanan Majelis MDPL</p>
                    </div>
                </div>
            `;

            galleryContainer.appendChild(card);
          });
        } else {
          console.error("Gagal mengambil data galeri atau data kosong");
        }
      })
      .catch(err => console.error(err));

    // ========== HERO SLIDER LOGIC (NO DOTS) ==========
    document.addEventListener('DOMContentLoaded', function() {
      const slides = document.querySelectorAll('.slide');

      // Cek jika elemen slide ada
      if (slides.length > 0) {
        let currentSlideIndex = 0;
        let slideInterval;
        const intervalTime = 6000; // 6 detik per slide

        // Fungsi Ganti Slide
        function showSlide(index) {
          if (index >= slides.length) {
            currentSlideIndex = 0;
          } else if (index < 0) {
            currentSlideIndex = slides.length - 1;
          } else {
            currentSlideIndex = index;
          }

          // Hapus class active dari semua slide
          slides.forEach(slide => slide.classList.remove('active'));

          // Tambah class active ke slide sekarang
          slides[currentSlideIndex].classList.add('active');
        }

        // Fungsi Timer Otomatis
        function startTimer() {
          slideInterval = setInterval(() => {
            showSlide(currentSlideIndex + 1);
          }, intervalTime);
        }

        function resetTimer() {
          clearInterval(slideInterval);
          startTimer();
        }

        // Expose fungsi ke window untuk tombol panah
        window.moveSlide = function(step) {
          showSlide(currentSlideIndex + step);
          resetTimer();
        }

        // Jalankan slide pertama & timer
        showSlide(0);
        startTimer();
      }
    });

    // ========== PARTICLES JS ==========
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof particlesJS !== 'undefined' && document.getElementById('particles-js')) {
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
        if (track) {
          const cards = Array.from(track.children);
          cards.forEach(card => {
            const clone1 = card.cloneNode(true);
            const clone2 = card.cloneNode(true);
            track.appendChild(clone1);
            track.appendChild(clone2);
          });
        }
      });
    });
  </script>

  <!-- Load external JavaScript files -->
  <script src="frontend/registrasi.js"></script>
  <script src="frontend/login.js"></script>
  <script src="frontend/trip-user.js"></script>

</body>

</html>