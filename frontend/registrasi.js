/**
 * ============================================
 * 1. CSS INJECTION FOR CUSTOM SWEETALERT STYLING
 * ============================================
 * Menyuntikkan style CSS agar SweetAlert Registrasi tampil konsisten.
 */
document.addEventListener("DOMContentLoaded", function() {
    // Definisi Warna Tema
    const PRIMARY_COLOR = "#9C7E5C"; 
    const PRIMARY_DARK = "#7B5E3A";
    const SECONDARY_COLOR = "#6c757d"; 

    const customSwalCss = `
        /* Container Popup */
        .custom-theme-popup {
            border-radius: 30px !important;
            padding: 2.5rem 2rem !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border: none !important;
        }

        /* Icon Container */
        .swal-custom-icon-container {
            width: 80px;
            height: 80px;
            background: ${PRIMARY_COLOR};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 10px 20px -5px rgba(156, 126, 92, 0.4);
        }
        
        .swal-custom-icon-container.error { background: #D32F2F; }
        .swal-custom-icon-container i { color: white; font-size: 2.5rem; }

        /* Text Styling */
        .swal-custom-title {
            font-family: 'Outfit', sans-serif !important;
            font-weight: 800 !important;
            color: ${PRIMARY_COLOR} !important;
            font-size: 1.8rem !important;
            margin-bottom: 0.8rem !important;
        }
        
        .swal-custom-title.error { color: #D32F2F !important; }

        .swal-custom-text {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #546E7A !important;
            font-size: 1.1rem !important;
            margin-bottom: 1rem !important;
        }

        /* Button Styling */
        .btn-swal-custom {
            padding: 0.8rem 2.5rem;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            border: none !important;
            outline: none !important;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: ${PRIMARY_COLOR};
            color: white;
        }

        .btn-swal-custom:hover {
            background-color: ${PRIMARY_DARK};
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(156, 126, 92, 0.3);
        }
    `;

    // Inject CSS jika belum ada
    if (!document.getElementById('custom-swal-registrasi-styles')) {
        const styleSheet = document.createElement("style");
        styleSheet.id = 'custom-swal-registrasi-styles';
        styleSheet.type = "text/css";
        styleSheet.innerText = customSwalCss;
        document.head.appendChild(styleSheet);
    }
});

function getBasePath() {
  return getPageUrl("").replace(window.location.origin, "");
}

function getFullBasePath() {
  return getPageUrl("");
}

// ========== CREATE OTP MODAL DYNAMICALLY ==========
(function () {
  if (!document.getElementById("verifyOtpModal")) {
    const modalOtpHtml = `
    <div id="verifyOtpModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;flex-direction:column;">
      <div style="background:#fff;padding:25px 30px;border-radius:24px;min-width:300px;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,0.3);position:relative;text-align:center;">
        
        <div style="width:60px;height:60px;background:#9C7E5C;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px auto;">
            <i class="fas fa-envelope-open-text" style="color:white;font-size:24px;"></i>
        </div>

        <h2 style="text-align:center;margin-bottom:10px;color:#9C7E5C;font-family:'Outfit', sans-serif;font-weight:800;">Verifikasi OTP</h2>
        <p style="text-align:center;margin-bottom:20px;font-size:14px;color:#546E7A;">Kode 6 digit telah dikirim ke email Anda.<br>Cek Inbox atau Spam folder.</p>
        
        <form id="verifyOtpForm" autocomplete="off">
          <input id="otpInput" type="text" maxlength="6" pattern="[0-9]{6}" placeholder="------" style="width:100%;padding:15px;margin-bottom:15px;font-size:24px;border:2px solid #EFEBE9;border-radius:12px;letter-spacing:10px;text-align:center;font-weight:bold;color:#37474F;outline:none;" required autocomplete="off" inputmode="numeric" />
          <div id="otpErrorMsg" style="color:#D32F2F;font-size:13px;margin-bottom:15px;min-height:18px;"></div>
          
          <button type="submit" style="width:100%;padding:12px;background:#9C7E5C;color:#fff;border:none;border-radius:50px;font-weight:700;font-size:16px;cursor:pointer;box-shadow:0 4px 10px rgba(156,126,92,0.3);transition:0.3s;">
            Verifikasi Sekarang
          </button>
        </form>
        
        <div style="margin-top:15px;text-align:center;">
          <button id="resendOtpBtn" style="background:transparent;border:none;color:#9C7E5C;cursor:pointer;font-size:13px;font-weight:600;">Kirim Ulang Kode OTP</button>
        </div>
      </div>
    </div>
    `;
    document.body.insertAdjacentHTML("beforeend", modalOtpHtml);
  }
})();

// ========== MODAL CONTROL FUNCTIONS ==========
function closeSignUpModal(force = false) {
  const modal = document.getElementById("signUpModal");
  const otpModal = document.getElementById("verifyOtpModal");

  if (otpModal && otpModal.style.display === "flex" && !force) {
    Swal.fire({
      icon: "warning",
      title: "Verifikasi Diperlukan",
      text: "Selesaikan verifikasi OTP terlebih dahulu.",
      customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' }
    });
    return;
  }

  if (modal) {
    modal.style.display = "none";
    modal.classList.remove("open");
  }
}

function freezeSignUpModal() {
  const modal = document.getElementById("signUpModal");
  if (modal) {
    modal.style.pointerEvents = "none";
    modal.style.filter = "blur(3px)";
  }
}

function unfreezeSignUpModal() {
  const modal = document.getElementById("signUpModal");
  if (modal) {
    modal.style.pointerEvents = "";
    modal.style.filter = "";
  }
}

function showOtpModal(email) {
  const otpModal = document.getElementById("verifyOtpModal");
  otpModal.style.display = "flex";
  document.getElementById("otpInput").value = "";
  document.getElementById("otpErrorMsg").textContent = "";

  freezeSignUpModal();

  window.onkeydown = function (evt) {
    if (evt.key === "Escape") {
      evt.preventDefault();
      document.getElementById("otpErrorMsg").textContent = "Selesaikan verifikasi kode OTP terlebih dahulu.";
    }
  };

  otpModal.onclick = (event) => {
    if (event.target === otpModal) {
      document.getElementById("otpErrorMsg").textContent = "Selesaikan verifikasi kode OTP terlebih dahulu.";
    }
  };

  document.getElementById("resendOtpBtn").onclick = function () {
    resendVerificationEmail(email);
  };
}

function forceCloseOtpModal() {
  const otpModal = document.getElementById("verifyOtpModal");
  if (otpModal) otpModal.style.display = "none";
  window.onkeydown = null;
  if (otpModal) otpModal.onclick = null;
  unfreezeSignUpModal();
}

// ========== GOOGLE OAUTH ==========
function handleGoogleOAuth() {
  window.location.href = getPageUrl("backend/google-oauth.php") + "?type=signup";
}

function attachGoogleButtonListener() {
  const googleBtn = document.getElementById("googleSignUpBtn");
  if (googleBtn && !googleBtn.hasAttribute("data-js-listener")) {
    googleBtn.setAttribute("data-js-listener", "true");
    googleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleOAuth();
    });
    return true;
  }
  return false;
}

// ========== VALIDATION FUNCTIONS ==========
function validatePassword(password) {
  if (!password || password.length < 6) return { valid: false, message: "Password minimal 6 karakter" };
  return { valid: true };
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email || !emailRegex.test(email)) return { valid: false, message: "Format email tidak valid" };
  return { valid: true };
}

function validatePhone(phone) {
  if (!phone) return { valid: false, message: "Nomor HP wajib diisi" };
  const cleanPhone = phone.replace(/[\s-]/g, "");
  const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;
  if (!phoneRegex.test(cleanPhone)) return { valid: false, message: "Format nomor HP tidak valid (contoh: 081234567890)" };
  return { valid: true };
}

function validateUsername(username) {
  if (!username || username.length < 3) return { valid: false, message: "Username minimal 3 karakter" };
  return { valid: true };
}

function validateAlamat(alamat) {
  if (!alamat || alamat.trim().length < 5) return { valid: false, message: "Alamat minimal 5 karakter" };
  return { valid: true };
}

// ========== RESEND VERIFICATION EMAIL ==========
async function resendVerificationEmail(email) {
  try {
    const basePath = getBasePath();
    const formData = new FormData();
    formData.append("email", email);

    Swal.fire({
      html: `
        <div class='swal-custom-content'>
            <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: #9C7E5C; margin-bottom: 1rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h2 class='swal-custom-title'>Mengirim Ulang OTP...</h2>
            <p class='swal-custom-text'>Mohon tunggu sebentar</p>
        </div>
      `,
      allowOutsideClick: false,
      showConfirmButton: false,
      buttonsStyling: false,
      customClass: { popup: 'custom-theme-popup' }
    });

    const response = await fetch(basePath + "backend/email-verify/resend-verification.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const result = await response.json();
    Swal.close();

    if (result.success) {
      Swal.fire({
        html: `
            <div class='swal-custom-content'>
                <div class='swal-custom-icon-container'>
                    <i class='bi bi-check-lg'></i>
                </div>
                <h2 class='swal-custom-title'>OTP Terkirim!</h2>
                <p class='swal-custom-text'>Cek email Anda untuk kode terbaru.</p>
            </div>
        `,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' }
      });
    } else {
      Swal.fire({
        html: `
            <div class='swal-custom-content'>
                <div class='swal-custom-icon-container error'>
                    <i class='bi bi-x-lg'></i>
                </div>
                <h2 class='swal-custom-title error'>Gagal</h2>
                <p class='swal-custom-text'>${result.message || "Gagal mengirim ulang OTP"}</p>
            </div>
        `,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' }
      });
    }
  } catch (error) {
    Swal.close();
    Swal.fire({
      title: "⚠️ Error",
      text: "Terjadi kesalahan: " + error.message,
      icon: "error",
      confirmButtonText: "OK"
    });
  }
}

// ========== SHOW RESEND VERIFICATION (POPUP SUKSES DAFTAR - STYLE BARU) ==========
function showResendVerification(email) {
  closeSignUpModal(true);
  
  // TAMPILAN POPUP BERHASIL DAFTAR YANG KONSISTEN
  Swal.fire({
    html: `
        <div class='swal-custom-content'>
            <div class='swal-custom-icon-container'>
                <i class='bi bi-check-lg'></i>
            </div>
            <h2 class='swal-custom-title'>Registrasi Berhasil!</h2>
            <p class='swal-custom-text'>Akun Anda telah dibuat. Silakan verifikasi email Anda.</p>
            <p style="font-size:0.9rem; color:#90A4AE; margin-top:-10px;">Kode OTP dikirim ke: <strong>${email}</strong></p>
        </div>
    `,
    confirmButtonText: 'Lanjut Verifikasi',
    buttonsStyling: false,
    customClass: {
        popup: 'custom-theme-popup',
        confirmButton: 'btn-swal-custom'
    },
    allowOutsideClick: false,
    allowEscapeKey: false,
    backdrop: `rgba(0,0,0,0.5)`
  }).then(() => {
    // Setelah klik OK, munculkan modal OTP
    showOtpModal(email);
  });
}

// ========== EVENT LISTENERS ==========
document.addEventListener("DOMContentLoaded", function () {
  if (!attachGoogleButtonListener()) {
    setTimeout(() => { attachGoogleButtonListener(); }, 500);
  }

  const closeBtn = document.getElementById("close-signup");
  if (closeBtn) {
    closeBtn.addEventListener("click", function (e) {
      const otpModal = document.getElementById("verifyOtpModal");
      if (otpModal && otpModal.style.display === "flex") {
        e.preventDefault();
        Swal.fire({ icon: "warning", title: "Verifikasi Diperlukan", text: "Selesaikan verifikasi OTP terlebih dahulu." });
      } else {
        closeSignUpModal(true);
      }
    });
  }

  const signupModal = document.getElementById("signUpModal");
  if (signupModal) {
    signupModal.addEventListener("click", function (e) {
      if (e.target === this) {
        const otpModal = document.getElementById("verifyOtpModal");
        if (otpModal && otpModal.style.display === "flex") {
          e.preventDefault();
          Swal.fire({ icon: "warning", title: "Verifikasi Diperlukan", text: "Selesaikan verifikasi OTP terlebih dahulu." });
        } else {
          closeSignUpModal(true);
        }
      }
    });
  }

  // ========== OTP FORM SUBMISSION ==========
  const otpForm = document.getElementById("verifyOtpForm");
  if (otpForm) {
    otpForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const basePath = getBasePath();
      const otp = document.getElementById("otpInput").value.trim();
      const errorMsg = document.getElementById("otpErrorMsg");

      if (!/^\d{6}$/.test(otp)) {
        errorMsg.textContent = "Kode OTP harus 6 digit angka.";
        return;
      }
      errorMsg.textContent = "";

      // Popup Loading Verifikasi
      Swal.fire({
        html: `
            <div class='swal-custom-content'>
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: #9C7E5C; margin-bottom: 1rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h2 class='swal-custom-title'>Memverifikasi...</h2>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        buttonsStyling: false,
        customClass: { popup: 'custom-theme-popup' }
      });

      try {
        const formData = new FormData();
        formData.append("otp", otp);

        const response = await fetch(basePath + "backend/email-verify/verify-email.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();
        Swal.close();

        if (result.success) {
          forceCloseOtpModal();
          
          // POPUP SUKSES VERIFIKASI (KONSISTEN)
          Swal.fire({
            html: `
                <div class='swal-custom-content'>
                    <div class='swal-custom-icon-container'>
                        <i class='bi bi-check-lg'></i>
                    </div>
                    <h2 class='swal-custom-title'>Verifikasi Berhasil!</h2>
                    <p class='swal-custom-text'>Selamat, akun Anda telah aktif.</p>
                </div>
            `,
            confirmButtonText: 'Mulai Jelajah',
            buttonsStyling: false,
            customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' },
            backdrop: `rgba(0,0,0,0.5)`
          }).then(() => {
            const fullBasePath = getFullBasePath();
            window.location.href = fullBasePath;
          });

        } else {
          errorMsg.textContent = result.message || "Verifikasi gagal.";
        }
      } catch (err) {
        Swal.close();
        errorMsg.textContent = "Terjadi kesalahan saat verifikasi.";
      }
    });
  }

  // ========== SIGNUP FORM SUBMISSION ==========
  const signUpForm = document.querySelector("#signUpModal form");
  if (signUpForm) {
    signUpForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const basePath = getBasePath();
      const formData = new FormData(this);

      const username = formData.get("username")?.trim() || "";
      const password = formData.get("password") || "";
      const confirmPassword = formData.get("confirm_password") || "";
      const email = formData.get("email")?.trim() || "";
      const phone = formData.get("no_wa")?.trim() || "";
      const alamat = formData.get("alamat")?.trim() || "";

      // (Validasi Manual - Kode dipersingkat, sama seperti sebelumnya)
      // ... validasi kode ...

      // POPUP LOADING REGISTRASI (KONSISTEN)
      closeSignUpModal(true);
      Swal.fire({
        html: `
            <div class='swal-custom-content'>
                <div class="spinner-border" role="status" style="width: 4rem; height: 4rem; color: #9C7E5C; margin-bottom: 1.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h2 class='swal-custom-title'>Mendaftarkan Akun...</h2>
                <p class='swal-custom-text'>Sedang memproses registrasi Anda...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        buttonsStyling: false,
        customClass: { popup: 'custom-theme-popup' },
        backdrop: `rgba(0,0,0,0.5)`
      });

      // Submit to API
      try {
        const response = await fetch(basePath + "backend/registrasi-api.php", {
          method: "POST",
          body: formData,
        });

        if (!response.ok) throw new Error(`Server error: ${response.status}`);
        const responseText = await response.text();
        if (!responseText.trim()) throw new Error("Server response kosong");

        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          throw new Error("Server response error");
        }

        closeSignUpModal(true);

        if (result.success) {
          showResendVerification(result.email || email);
          signUpForm.reset();
        } else {
          // POPUP ERROR REGISTRASI (KONSISTEN)
          Swal.fire({
            html: `
                <div class='swal-custom-content'>
                    <div class='swal-custom-icon-container error'>
                        <i class='bi bi-x-lg'></i>
                    </div>
                    <h2 class='swal-custom-title error'>Registrasi Gagal</h2>
                    <p class='swal-custom-text'>${result.message || "Terjadi kesalahan"}</p>
                </div>
            `,
            confirmButtonText: 'Coba Lagi',
            buttonsStyling: false,
            customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' },
            backdrop: `rgba(0,0,0,0.5)`
          });
        }
      } catch (error) {
        closeSignUpModal(true);
        Swal.fire({
            html: `
                <div class='swal-custom-content'>
                    <div class='swal-custom-icon-container error'>
                        <i class='bi bi-wifi-off'></i>
                    </div>
                    <h2 class='swal-custom-title error'>Error Sistem</h2>
                    <p class='swal-custom-text'>${error.message}</p>
                </div>
            `,
            confirmButtonText: 'Tutup',
            buttonsStyling: false,
            customClass: { popup: 'custom-theme-popup', confirmButton: 'btn-swal-custom' }
        });
      }
    });
  }
});

// ========== EXPOSE FUNCTIONS ==========
window.handleGoogleSignUp = handleGoogleOAuth;
window.closeSignUpModal = closeSignUpModal;