<footer class="footer">
  <div class="footer-pattern"></div>

  <div class="container">
    
    <div class="footer-col brand-col">
      <div class="brand-wrapper">
        <h3 class="brand-title">Majelis <span>MDPL</span></h3>
      </div>
      <p class="brand-desc">
        Partner pendakian terbaik Anda. Nikmati keindahan alam Indonesia dengan layanan profesional, aman, dan penuh kenangan. <br><br>
        <em>"Setiap puncak punya cerita, mari ukir ceritamu bersama kami."</em>
      </p>
      
      <div class="social-links">
        <a href="https://www.tiktok.com/@majelis.mdpl" target="_blank" aria-label="TikTok">
            <i class="fa-brands fa-tiktok"></i>
        </a>
        <a href="https://www.instagram.com/majelis.mdpl" target="_blank" aria-label="Instagram">
            <i class="fa-brands fa-instagram"></i>
        </a>
        <a href="https://wa.me/628562898933" target="_blank" aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
      </div>
    </div>

    <div class="footer-col links-col">
      <h3>Jelajahi</h3>
      <ul class="footer-links">
        <li><a href="#home"><i class="fas fa-chevron-right"></i> Beranda</a></li>
        <li><a href="#profile"><i class="fas fa-chevron-right"></i> Tentang Kami</a></li>
        <li><a href="#paketTrips"><i class="fas fa-chevron-right"></i> Paket Trip</a></li>
        <li><a href="#gallerys"><i class="fas fa-chevron-right"></i> Galeri</a></li>
        <li><a href="#testimonials"><i class="fas fa-chevron-right"></i> Testimoni</a></li>
      </ul>
    </div>

    <div class="footer-col contact-col">
      <h3>Hubungi Kami</h3>
      <ul class="contact-list">
        <li>
          <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="text">
            <strong>Basecamp:</strong><br>
            Jl. Aseleole, Kaliwates,<br>Jember, Jawa Timur 55582
          </div>
        </li>
        <li>
          <div class="icon"><i class="fas fa-phone-alt"></i></div>
          <div class="text">
            <strong>WhatsApp:</strong><br>
            <a href="https://wa.me/628562898933" target="_blank">0856-2898-933</a>
          </div>
        </li>
        <li>
          <div class="icon"><i class="fas fa-envelope"></i></div>
          <div class="text">
            <strong>Email:</strong><br>
            <a href="mailto:majelismdpl@gmail.com">majelismdpl@gmail.com</a>
          </div>
        </li>
      </ul>
    </div>

  </div>

  <div class="copyright">
    <div class="container">
      <p>&copy; 2025 <strong>Majelis MDPL</strong>. All Rights Reserved.</p>
      <p class="dev-credit">Developed with <i class="fas fa-heart text-danger"></i> by Ngelawans Gang</p>
    </div>
  </div>
</footer>

<style>
/* =========================================
   FOOTER PREMIUM STYLE (Dark Brown & Gold)
   ========================================= */

.footer {
    position: relative;
    /* Gradient Coklat Tua Premium */
    background: linear-gradient(135deg, #2b2118 0%, #4a3b2a 100%);
    color: #e0e0e0;
    padding-top: 80px;
    font-family: 'Poppins', sans-serif;
    overflow: hidden;
}

/* Pattern Background Halus */
.footer-pattern {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background-image: radial-gradient(rgba(255, 212, 74, 0.05) 1px, transparent 1px);
    background-size: 30px 30px;
    opacity: 0.5;
    pointer-events: none;
}

.footer .container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

/* --- KOLOM FOOTER --- */
.footer-col {
    flex: 1;
    min-width: 280px;
    margin-bottom: 40px;
}

.footer-col h3 {
    color: #fff;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 25px;
    position: relative;
    display: inline-block;
}

/* Garis bawah judul */
.footer-col h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -8px;
    width: 40px;
    height: 3px;
    background: #A9865A; /* Warna Emas */
    border-radius: 2px;
}

/* BRAND COLUMN */
.brand-title {
    font-size: 1.8rem !important;
    margin-bottom: 15px !important;
}
.brand-title span { color: #A9865A; } /* Warna Emas */

.brand-desc {
    font-size: 0.95rem;
    line-height: 1.7;
    color: #ccc;
    margin-bottom: 25px;
}

/* SOCIAL ICONS */
.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    border-radius: 50%;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 1.1rem;
}

.social-links a:hover {
    background: #A9865A;
    border-color: #A9865A;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(169, 134, 90, 0.4);
}

/* LINKS COLUMN */
.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.footer-links a i {
    font-size: 0.7rem;
    color: #A9865A;
    transition: transform 0.3s;
}

.footer-links a:hover {
    color: #A9865A;
    padding-left: 5px;
}

.footer-links a:hover i {
    transform: translateX(3px);
}

/* CONTACT COLUMN */
.contact-list {
    list-style: none;
    padding: 0;
}

.contact-list li {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.contact-list .icon {
    width: 40px;
    height: 40px;
    background: rgba(169, 134, 90, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #A9865A;
    flex-shrink: 0;
}

.contact-list .text {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #ccc;
}

.contact-list strong {
    color: #fff;
    font-size: 1rem;
}

.contact-list a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s;
}

.contact-list a:hover {
    color: #A9865A;
}

/* COPYRIGHT BAR */
.copyright {
    background: #231b14; /* Lebih gelap dari footer utama */
    padding: 25px 0;
    margin-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.05);
    font-size: 0.9rem;
    color: #888;
}

.copyright .container {
    align-items: center;
    padding: 0 20px;
}

.copyright strong { color: #fff; }
.copyright .dev-credit { margin: 0; }

.text-danger { color: #e74c3c; }

/* RESPONSIVE */
@media (max-width: 900px) {
    .footer-col {
        flex: 1 1 45%; /* Tablet: 2 kolom */
    }
}

@media (max-width: 600px) {
    .footer-col {
        flex: 1 1 100%; /* Mobile: 1 kolom */
        text-align: center;
    }
    
    .footer-col h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .social-links {
        justify-content: center;
    }
    
    .footer-links a {
        justify-content: center;
    }
    
    .contact-list li {
        flex-direction: column;
        align-items: center;
    }
    
    .copyright .container {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>