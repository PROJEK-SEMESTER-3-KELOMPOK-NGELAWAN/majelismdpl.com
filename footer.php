<footer class="footer">
  <div class="container">
    <div class="footer-col">
      <h3 class="brand">Pendakian Majelis MDPL</h3>
      <p>
        ✨ Nikmati pengalaman tak terlupakan bersama Majelis MDPL Open Trip. <br>
        Ikuti serunya pendakian tektok maupun camping, rasakan panorama puncak
        yang menakjubkan, dan ciptakan kenangan berharga di setiap perjalanan. 🌲🏔
      </p>
      <div class="social-links">
        <a href="https://www.tiktok.com/@majelis.mdpl?_r=1&_t=ZS-91uZgzyDdqf"><i class="fa-brands fa-tiktok"></i></a>
        <a href="https://www.instagram.com/majelis.mdpl?igsh=cmFpZHVpbjFmMTB3"><i class="fa-brands fa-instagram"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h3>Kontak <span>Kami</span></h3>
      <p><strong>Alamat Kami</strong><br>Jl. aseleole, Kaliwates, Jember 55582</p>
      <p><strong>Whatsapp</strong><br>08562898933</p>
      <p><strong>Email</strong><br>majelismdpl@gmail.com</p>
    </div>
    <div class="footer-col">
      <h3>Quick <span>Link</span></h3>
      <ul>
        <li><a href="#">Profile</a></li>
        <li><a href="#">Paket Open Trip</a></li>
        <li><a href="#">Kontak</a></li>
      </ul>
    </div>
  </div>
  <div class="copyright">
    <p>Copyright © 2025 Majelis Mdpl. All rights reserved. Developed with ❤ by Ngelawans Gang</p>
  </div>
</footer>

<style>
  /* ============================================
    FOOTER - MODERN FUTURISTIC BROWN THEME
    ============================================ */

.footer {
  position: relative;
  overflow: hidden;
  padding: 60px 20px 20px;

  /* Dark Brown Gradient Background (Warna Baru) */
  background: linear-gradient(
    135deg,
    #B29262 0%,
    #AB8053 100%
  );

  color: #fff;
}

/* Animated Mesh Background */
.footer::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(
      circle at 10% 20%,
      rgba(255, 212, 74, 0.08) 0%,
      transparent 40%
    ),
    radial-gradient(
      circle at 90% 80%,
      rgba(169, 124, 80, 0.06) 0%,
      transparent 45%
    ),
    radial-gradient(
      circle at 50% 50%,
      rgba(92, 57, 34, 0.05) 0%,
      transparent 50%
    );
  animation: footerMesh 15s ease-in-out infinite;
  pointer-events: none;
  z-index: 1;
}

@keyframes footerMesh {
  0%,
  100% {
    opacity: 0.4;
  }
  50% {
    opacity: 0.6;
  }
}

/* Grid Pattern */
.footer::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: linear-gradient(
      rgba(255, 212, 74, 0.02) 1px,
      transparent 1px
    ),
    linear-gradient(90deg, rgba(255, 212, 74, 0.02) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.3;
  pointer-events: none;
  z-index: 1;
}

.footer .container {
  display: flex;
  justify-content: space-between;
  max-width: 1200px;
  margin: auto;
  gap: 50px;
  flex-wrap: wrap;
  position: relative;
  z-index: 10;
}

/* Footer Column - Modern Glass Card */
.footer-col {
  flex: 1 1 280px;
  min-width: 250px;
  margin-bottom: 30px;
  padding: 25px;
  background: rgba(61, 48, 32, 0.3);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 212, 74, 0.2);
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3),
    inset 0 1px 2px rgba(255, 255, 255, 0.1);
  transition: all 0.4s ease;
}

.footer-col:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(255, 212, 74, 0.3),
    inset 0 1px 2px rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 212, 74, 0.4);
  background: rgba(61, 48, 32, 0.4);
}

/* Brand Title - Gradient */
.footer-col h3.brand {
  font-size: 1.5rem;
  margin-bottom: 18px;
  font-weight: 900;
  background: linear-gradient(135deg, #fff 0%, #ffd44a 50%, #ffb700 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 30px rgba(255, 212, 74, 0.5);
  line-height: 1.3;
}

/* Section Headers */
.footer-col h3 {
  font-size: 1.3rem;
  margin-bottom: 18px;
  font-weight: 700;
  color: #fff;
  text-shadow: 0 0 20px rgba(255, 212, 74, 0.4);
  position: relative;
  display: inline-block;
}

.footer-col h3 span {
  color: #ffd44a;
  text-shadow: 0 0 20px rgba(255, 212, 74, 0.6);
}

/* Add line under headers */
.footer-col h3::after {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, #ffd44a, transparent);
  border-radius: 2px;
}

/* Paragraph Text */
.footer-col p {
  margin: 10px 0;
  line-height: 1.7;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.85);
}

.footer-col p strong {
  color: #ffd44a;
  font-weight: 600;
}

/* Links List */
.footer-col ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.footer-col ul li {
  margin-bottom: 12px;
  position: relative;
  padding-left: 20px;
}

/* Custom bullet with icon */
.footer-col ul li::before {
  content: "▸";
  position: absolute;
  left: 0;
  color: #ffd44a;
  font-weight: bold;
}

.footer-col ul li a {
  text-decoration: none;
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.95rem;
  transition: all 0.3s ease;
  position: relative;
}

.footer-col ul li a::after {
  content: "";
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: #ffd44a;
  transition: width 0.3s ease;
}

.footer-col ul li a:hover {
  color: #ffd44a;
  text-shadow: 0 0 10px rgba(255, 212, 74, 0.5);
  padding-left: 5px;
}

.footer-col ul li a:hover::after {
  width: 100%;
}

/* Social Links - Modern Icons */
.social-links {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.social-links a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  font-size: 1.2rem;
  color: #fff;
  background: rgba(255, 212, 74, 0.1);
  border: 2px solid rgba(255, 212, 74, 0.3);
  border-radius: 50%;
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
}

.social-links a::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 212, 74, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.4s ease;
}

.social-links a:hover::before {
  width: 100%;
  height: 100%;
}

.social-links a:hover {
  color: #ffd44a;
  border-color: #ffd44a;
  box-shadow: 0 0 20px rgba(255, 212, 74, 0.6);
  transform: translateY(-5px) rotate(360deg);
}

.social-links a i {
  position: relative;
  z-index: 1;
}

/* Copyright Section */
.footer .copyright {
  text-align: center;
  border-top: 1px solid rgba(255, 212, 74, 0.2);
  margin-top: 40px;
  padding-top: 25px;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.75);
  position: relative;
  z-index: 10;
}

.footer .copyright::before {
  content: "";
  position: absolute;
  top: -1px;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 3px;
  background: linear-gradient(90deg, transparent, #ffd44a, transparent);
  border-radius: 2px;
}

/* ============================================
    RESPONSIVE
    ============================================ */

/* Tablet */
@media (max-width: 900px) {
  .footer .container {
    gap: 30px;
  }

  .footer-col {
    flex: 1 1 45%;
    min-width: 230px;
    padding: 20px;
  }
}

/* Mobile */
@media (max-width: 600px) {
  .footer {
    padding: 40px 15px 15px;
  }

  .footer .container {
    flex-direction: column;
    gap: 20px;
    padding: 0;
  }

  .footer-col {
    flex: 1 1 100%;
    min-width: 0;
    margin-bottom: 20px;
    padding: 20px;
  }

  .footer-col h3.brand {
    font-size: 1.3rem;
  }

  .footer-col h3 {
    font-size: 1.15rem;
  }

  .social-links {
    justify-content: center;
  }

  .footer .copyright {
    font-size: 0.85rem;
    padding: 20px 10px;
  }
}
</style>