function getBasePath() {
  // Gunakan getPageUrl dari config.php
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
      <div style="background:#fff;padding:25px 30px;border-radius:10px;min-width:300px;max-width:400px;box-shadow:0 5px 20px rgba(0,0,0,0.3);position:relative;">
        <h2 style="text-align:center;margin-bottom:15px;color:#34495e;">Verifikasi Email dengan OTP</h2>
        <p style="text-align:center;margin-bottom:15px;font-size:15px;">Kode OTP sudah dikirim ke email Anda.<br>Silakan masukkan kode 6 digit berikut:</p>
        <form id="verifyOtpForm" autocomplete="off">
          <input id="otpInput" type="text" maxlength="6" pattern="[0-9]{6}" placeholder="Masukkan Kode OTP 6 digit" style="width:100%;padding:10px;margin-bottom:10px;font-size:18px;border:1px solid #ccc;border-radius:6px;letter-spacing:8px;text-align:center;" required autocomplete="off" inputmode="numeric" />
          <div id="otpErrorMsg" style="color:red;font-size:14px;margin-bottom:10px;min-height:18px;"></div>
          <button type="submit" style="width:100%;padding:10px;background:#667eea;color:#fff;border:none;border-radius:6px;font-weight:bold;font-size:16px;cursor:pointer;">
            Verifikasi Sekarang
          </button>
        </form>
        <div style="margin-top:12px;text-align:center;">
          <button id="resendOtpBtn" style="background:transparent;border:none;color:#667eea;cursor:pointer;text-decoration:underline;font-size:14px;padding:5px">Kirim Ulang Kode OTP</button>
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
      didOpen: () => {
        document.querySelector(".swal2-container").style.zIndex = 200000;
      },
    });
    return;
  }

  if (modal) {
    modal.style.display = "none";
    // Jika menggunakan class 'open' dari auth-modals.php
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

  // Prevent ESC key closing
  window.onkeydown = function (evt) {
    if (evt.key === "Escape") {
      evt.preventDefault();
      document.getElementById("otpErrorMsg").textContent =
        "Selesaikan verifikasi kode OTP terlebih dahulu.";
    }
  };

  // Prevent clicking outside to close
  otpModal.onclick = (event) => {
    if (event.target === otpModal) {
      document.getElementById("otpErrorMsg").textContent =
        "Selesaikan verifikasi kode OTP terlebih dahulu.";
    }
  };

  // Setup resend button
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
  window.location.href =
    getPageUrl("backend/google-oauth.php") + "?type=signup";
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
  if (!password || password.length < 6)
    return { valid: false, message: "Password minimal 6 karakter" };
  return { valid: true };
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email || !emailRegex.test(email))
    return { valid: false, message: "Format email tidak valid" };
  return { valid: true };
}

function validatePhone(phone) {
  if (!phone) return { valid: false, message: "Nomor HP wajib diisi" };
  const cleanPhone = phone.replace(/[\s-]/g, "");
  const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;
  if (!phoneRegex.test(cleanPhone))
    return {
      valid: false,
      message: "Format nomor HP tidak valid (contoh: 081234567890)",
    };
  return { valid: true };
}

function validateUsername(username) {
  if (!username || username.length < 3)
    return { valid: false, message: "Username minimal 3 karakter" };
  return { valid: true };
}

function validateAlamat(alamat) {
  if (!alamat || alamat.trim().length < 5)
    return { valid: false, message: "Alamat minimal 5 karakter" };
  return { valid: true };
}

// ========== RESEND VERIFICATION EMAIL ==========
async function resendVerificationEmail(email) {
  try {
    const basePath = getBasePath();
    const formData = new FormData();
    formData.append("email", email);

    Swal.fire({
      title: "📤 Mengirim Ulang OTP...",
      text: "Mohon tunggu sebentar",
      allowOutsideClick: false,
      showConfirmButton: false,
      willOpen: () => {
        Swal.showLoading();
      },
      didOpen: () => {
        document.querySelector(".swal2-container").style.zIndex = 200000;
      },
    });

    const response = await fetch(
      basePath + "backend/email-verify/resend-verification.php",
      {
        method: "POST",
        body: formData,
      }
    );

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const result = await response.json();
    Swal.close();

    if (result.success) {
      Swal.fire({
        title: "✅ OTP Dikirim!",
        html: `<p>${result.message}</p><p style="font-size:13px;color:#666;">Cek email dan masukkan OTP terbaru di sini</p>`,
        icon: "success",
        confirmButtonText: "OK",
        didOpen: () => {
          document.querySelector(".swal2-container").style.zIndex = 200000;
        },
      });
    } else {
      Swal.fire({
        title: "❌ Gagal Kirim OTP",
        text: result.message || "Gagal mengirim ulang OTP",
        icon: "error",
        confirmButtonText: "OK",
        didOpen: () => {
          document.querySelector(".swal2-container").style.zIndex = 200000;
        },
      });
    }
  } catch (error) {
    Swal.close();
    Swal.fire({
      title: "⚠️ Error",
      text: "Terjadi kesalahan saat mengirim ulang OTP: " + error.message,
      icon: "error",
      confirmButtonText: "OK",
      didOpen: () => {
        document.querySelector(".swal2-container").style.zIndex = 200000;
      },
    });
  }
}

// ========== SHOW RESEND VERIFICATION ==========
function showResendVerification(email) {
  closeSignUpModal(true);
  Swal.fire({
    title: "Kode OTP Telah Dikirim",
    html: `
      <div style="text-align: left;">
        <p style="margin-bottom: 16px;">
          <b>Pendaftaran berhasil!</b> Silakan cek email Anda.<br>
          - Kode OTP (6 digit) sudah dikirim ke: <b>${email}</b><br>
          - Masukkan kode OTP di layar berikut ini.
        </p>
        <span style="font-size:13px">Belum terima email? Klik "Kirim Ulang OTP" pada form OTP.</span>
      </div>
    `,
    icon: "info",
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    willClose: () => {
      showOtpModal(email);
    },
    didOpen: () => {
      document.querySelector(".swal2-container").style.zIndex = 200000;
    },
  });
  setTimeout(() => {
    Swal.close();
  }, 8000);
}

// ========== EVENT LISTENERS ==========
document.addEventListener("DOMContentLoaded", function () {
  // Attach Google button listener
  if (!attachGoogleButtonListener()) {
    setTimeout(() => {
      attachGoogleButtonListener();
    }, 500);
  }

  // Handle close button with OTP check
  const closeBtn = document.getElementById("close-signup");
  if (closeBtn) {
    closeBtn.addEventListener("click", function (e) {
      const otpModal = document.getElementById("verifyOtpModal");
      if (otpModal && otpModal.style.display === "flex") {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Verifikasi Diperlukan",
          text: "Selesaikan verifikasi OTP terlebih dahulu.",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
      } else {
        closeSignUpModal(true);
      }
    });
  }

  // Handle clicking outside modal
  const signupModal = document.getElementById("signUpModal");
  if (signupModal) {
    signupModal.addEventListener("click", function (e) {
      if (e.target === this) {
        const otpModal = document.getElementById("verifyOtpModal");
        if (otpModal && otpModal.style.display === "flex") {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Verifikasi Diperlukan",
            text: "Selesaikan verifikasi OTP terlebih dahulu.",
            didOpen: () => {
              document.querySelector(".swal2-container").style.zIndex = 200000;
            },
          });
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

      Swal.fire({
        title: "Memverifikasi Kode OTP...",
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => Swal.showLoading(),
        didOpen: () => {
          document.querySelector(".swal2-container").style.zIndex = 200000;
        },
      });

      try {
        const formData = new FormData();
        formData.append("otp", otp);

        const response = await fetch(
          basePath + "backend/email-verify/verify-email.php",
          {
            method: "POST",
            body: formData,
          }
        );

        const result = await response.json();
        Swal.close();

        if (result.success) {
          forceCloseOtpModal();
          Swal.fire({
            icon: "success",
            title: "✅ Verifikasi Berhasil",
            text: result.message,
            didOpen: () => {
              document.querySelector(".swal2-container").style.zIndex = 200000;
            },
          }).then(() => {
            // Redirect to home
            const fullBasePath = getFullBasePath();
            window.location.href = fullBasePath;
          });
        } else {
          errorMsg.textContent = result.message || "Verifikasi gagal.";
        }
      } catch (err) {
        Swal.close();
        errorMsg.textContent = "Terjadi kesalahan saat verifikasi.";
        console.error(err);
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

      // Get form values
      const username = formData.get("username")?.trim() || "";
      const password = formData.get("password") || "";
      const confirmPassword = formData.get("confirm_password") || "";
      const email = formData.get("email")?.trim() || "";
      const phone = formData.get("no_wa")?.trim() || "";
      const alamat = formData.get("alamat")?.trim() || "";

      // Validate all fields
      const usernameValidation = validateUsername(username);
      if (!usernameValidation.valid) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Validasi Gagal",
          text: usernameValidation.message,
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      const passwordValidation = validatePassword(password);
      if (!passwordValidation.valid) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Validasi Gagal",
          text: passwordValidation.message,
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      if (password !== confirmPassword) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Password Tidak Cocok",
          text: "Password dan konfirmasi harus sama",
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      const emailValidation = validateEmail(email);
      if (!emailValidation.valid) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Email Tidak Valid",
          text: emailValidation.message,
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      const phoneValidation = validatePhone(phone);
      if (!phoneValidation.valid) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Nomor HP Tidak Valid",
          text: phoneValidation.message,
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      const alamatValidation = validateAlamat(alamat);
      if (!alamatValidation.valid) {
        closeSignUpModal(true);
        Swal.fire({
          title: "Alamat Tidak Valid",
          text: alamatValidation.message,
          icon: "error",
          confirmButtonText: "OK",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
        return;
      }

      // Show loading
      closeSignUpModal(true);
      Swal.fire({
        title: "🏔️ Mendaftarkan Akun...",
        html: `<div style="padding: 20px;">
          <p>Sedang memproses registrasi Anda...</p>
          <p style="font-size: 14px; color: #666; margin-top: 10px;">Mohon tunggu sebentar</p>
        </div>`,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        },
        didOpen: () => {
          document.querySelector(".swal2-container").style.zIndex = 200000;
        },
      });

      // Submit to API
      try {
        const response = await fetch(basePath + "backend/registrasi-api.php", {
          method: "POST",
          body: formData,
        });

        if (!response.ok) {
          throw new Error(
            `Server error: ${response.status} - ${response.statusText}`
          );
        }

        const responseText = await response.text();

        if (!responseText.trim()) {
          throw new Error("Server mengembalikan response kosong");
        }

        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          throw new Error("Server response bukan JSON yang valid");
        }

        closeSignUpModal(true);

        if (result.success) {
          showResendVerification(result.email || email);
          signUpForm.reset();
        } else {
          closeSignUpModal(true);
          Swal.fire({
            title: "❌ Registrasi Gagal",
            text: result.message || "Terjadi kesalahan saat mendaftar",
            icon: "error",
            confirmButtonText: "Coba Lagi",
            didOpen: () => {
              document.querySelector(".swal2-container").style.zIndex = 200000;
            },
          });
        }
      } catch (error) {
        closeSignUpModal(true);
        Swal.fire({
          title: "⚠️ Error",
          text: error.message,
          icon: "error",
          confirmButtonText: "OK",
          footer:
            "<small>Jika masalah berlanjut, silakan hubungi administrator</small>",
          didOpen: () => {
            document.querySelector(".swal2-container").style.zIndex = 200000;
          },
        });
      }
    });
  }

  // ========== REAL-TIME VALIDATION ON BLUR ==========
  const fields = [
    {
      el: document.getElementById("signupPassword"),
      validate: validatePassword,
    },
    {
      el: document.getElementById("confirmPassword"),
      validate: (v) => ({ valid: true }),
    },
    {
      el: document.querySelector('#signUpModal input[name="email"]'),
      validate: validateEmail,
    },
    {
      el: document.querySelector('#signUpModal input[name="no_wa"]'),
      validate: validatePhone,
    },
    {
      el: document.querySelector('#signUpModal input[name="username"]'),
      validate: validateUsername,
    },
    {
      el: document.querySelector('#signUpModal input[name="alamat"]'),
      validate: validateAlamat,
    },
  ];

  fields.forEach((f) => {
    if (f.el) {
      f.el.addEventListener("blur", function () {
        const v = f.validate(this.value);
        this.style.borderColor = !v.valid && this.value ? "#e74c3c" : "";
        this.style.boxShadow =
          !v.valid && this.value ? "0 0 5px rgba(231,76,60,0.3)" : "";
      });
    }
  });
});

// ========== EXPOSE FUNCTIONS GLOBALLY ==========
window.handleGoogleSignUp = handleGoogleOAuth;
window.closeSignUpModal = closeSignUpModal;
