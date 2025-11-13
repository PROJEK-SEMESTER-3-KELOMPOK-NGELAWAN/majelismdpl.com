/**
 * ============================================
 * CSS INJECTION FOR CUSTOM SWEETALERT STYLING
 * ============================================
 * Menyesuaikan tampilan SweetAlert2 agar mirip dengan gambar konfirmasi Logout.
 */
document.addEventListener("DOMContentLoaded", function() {
    // Definisi Warna
    const PRIMARY_COLOR = "#a9865a"; // Coklat Keemasan (Warna Tombol dan Teks)
    const SECONDARY_COLOR = "#6c757d"; // Abu-abu (Warna Tombol Batal)

    const customSwalCss = `
        .swal2-popup {
            border-radius: 20px !important; 
            max-width: 400px;
            width: 90%;
            padding: 30px 40px;
            text-align: center;
            font-family: inherit, sans-serif; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
        }

        .swal2-title {
            color: ${PRIMARY_COLOR} !important;
            font-size: 1.8em !important;
        }

        .swal2-styled.swal2-confirm {
            background-color: ${PRIMARY_COLOR} !important;
            color: white !important;
            border-radius: 12px !important; 
            border: none !important;
            font-size: 1em !important;
            font-weight: 600 !important; 
        }

        .swal2-styled.swal2-cancel {
            background-color: ${SECONDARY_COLOR} !important;
            color: white !important;
            border-radius: 12px !important;
            border: none !important;
            font-size: 1em !important;
            font-weight: 600 !important;
        }

        .swal2-icon.swal2-error {
            border-color: ${PRIMARY_COLOR} !important; 
        }
        .swal2-icon.swal2-error [class^=swal2-x-mark-line] {
            background-color: ${PRIMARY_COLOR} !important;
        }

        .swal2-icon.swal2-success [class^=swal2-success-line] {
            background-color: ${PRIMARY_COLOR} !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: ${PRIMARY_COLOR} !important;
        }

        .swal2-custom-background {
             background: none !important;
        }
    `;

    // Buat dan Sisipkan elemen <style> ke <head>
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = customSwalCss;
    document.head.appendChild(styleSheet);
});


/**
 * ============================================
 * MODAL & ERROR HANDLING FUNCTIONS (LEGACY/FALLBACK)
 * ============================================
 */
function closeLoginModal() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    // Close using the global closeModal function from auth-modals.php
    if (typeof closeModal === "function") {
      closeModal(modal);
    } else {
      modal.style.display = "none";
      modal.classList.remove("open");
      document.body.style.overflow = "";
    }
  }
}

// Fungsi untuk show error modal seperti logout confirmation (Hanya sebagai FALLBACK)
function showLoginErrorModal(errorMessage) {
  const errorModal = document.getElementById("login-error-modal");
  const errorMessageEl = document.getElementById("login-error-message");

  if (errorModal && errorMessageEl) {
    errorMessageEl.textContent =
      errorMessage || "Terjadi kesalahan. Silakan coba lagi.";
    errorModal.classList.add("show");
  }
}

// Fungsi untuk hide error modal (Hanya sebagai FALLBACK)
function hideLoginErrorModal() {
  const errorModal = document.getElementById("login-error-modal");
  if (errorModal) {
    errorModal.classList.remove("show");
  }
}

/**
 * ============================================
 * GOOGLE LOGIN & HELPER FUNCTIONS
 * ============================================
 */
// Fungsi untuk handle Google OAuth Login
function handleGoogleLogin() {
  window.location.href = getPageUrl("backend/google-oauth.php") + "?type=login";
}

// Helper function untuk detect base path - GUNAKAN CONFIG
function getBasePath() {
  // Asumsi getPageUrl("") mengembalikan base URL
  return getPageUrl("").replace(window.location.origin, "");
}

// Function untuk attach Google login button listener
function attachGoogleLoginListener() {
  const googleLoginBtn = document.getElementById("googleLoginBtn");

  if (googleLoginBtn && !googleLoginBtn.hasAttribute("data-login-listener")) {
    googleLoginBtn.setAttribute("data-login-listener", "true");

    googleLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleLogin();
    });

    googleLoginBtn.onclick = function (e) {
      e.preventDefault();
      handleGoogleLogin();
      return false;
    };

    return true;
  }
  return false;
}

// Global fallback function
window.handleGoogleLogin = handleGoogleLogin;

/**
 * ============================================
 * REGULAR FORM SUBMISSION HANDLER (INTEGRATED SWEETALERT2)
 * ============================================
 */
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");
  
  // Warna tombol/ikon/teks
  const PRIMARY_BUTTON_COLOR = "#a9865a"; 
  const SECONDARY_BUTTON_COLOR = "#6c757d"; 

  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch(getApiUrl("backend/login-api.php"), {
          method: "POST",
          body: formData,
        });

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const result = await response.json();

        if (result.success) {
          // ✅ LOGIN BERHASIL
          closeLoginModal();

          if (typeof Swal !== "undefined") {
            const username = result.username || formData.get("username");

            // SWEETALERT LOGIN BERHASIL (Menggunakan style CSS yang diinjeksi)
            Swal.fire({
              title: "Login Berhasil! 🎉",
              html: `Selamat datang, ${username}!`,
              icon: "success",
              showConfirmButton: true,
              confirmButtonText: "Lanjutkan",
              confirmButtonColor: PRIMARY_BUTTON_COLOR, 
              position: "center",
              toast: false,
              allowOutsideClick: false,
              backdrop: true, 
              customClass: {},
            }).then(() => {
              if (["admin", "super_admin"].includes(result.role)) {
                window.location.href = getPageUrl("admin/index.php");
              } else {
                window.location.href = getPageUrl("index.php");
              }
            });
          }
        } else {
          // ✅ LOGIN GAGAL - Tampilkan SweetAlert Error
          closeLoginModal();

          const errorMessage =
            result.message || "Username atau kata sandi salah.";

          setTimeout(() => {
            if (typeof Swal !== "undefined") {
              // SWEETALERT LOGIN GAGAL (Menggunakan style CSS yang diinjeksi)
              Swal.fire({
                title: "Login Gagal ⚠️",
                html: errorMessage,
                icon: "error",
                confirmButtonText: "Coba Lagi",
                confirmButtonColor: PRIMARY_BUTTON_COLOR, 
                showCancelButton: true,
                cancelButtonText: "Tutup",
                cancelButtonColor: SECONDARY_BUTTON_COLOR, 
                position: "center",
                allowOutsideClick: true,
                backdrop: true, 
                customClass: {},
              }).then((action) => {
                if (action.isConfirmed) {
                  // Re-open login modal
                  const loginModal = document.getElementById("loginModal");
                  if (loginModal && typeof openModal === "function") {
                    openModal(loginModal);
                  }
                }
              });
            } else {
              // Fallback error
              showLoginErrorModal(errorMessage);
            }
          }, 350);
        }
      } catch (error) {
        // ✅ ERROR SISTEM - Tampilkan SweetAlert Sistem Error
        closeLoginModal();

        setTimeout(() => {
          if (typeof Swal !== "undefined") {
            // SWEETALERT ERROR SISTEM (Menggunakan style CSS yang diinjeksi)
            Swal.fire({
              title: "Kesalahan Sistem ❌",
              text: "Terjadi kesalahan. Coba lagi nanti.", 
              icon: "error",
              confirmButtonText: "Tutup",
              confirmButtonColor: PRIMARY_BUTTON_COLOR, 
              position: "center",
              allowOutsideClick: true,
              backdrop: true, 
              customClass: {},
            });
          } else {
            // Fallback system error
            showLoginErrorModal(
              "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator."
            );
          }
        }, 350);
      }
    });
  }

  // ========== ERROR MODAL EVENT HANDLERS (Hanya untuk Fallback/Modal Lain) ==========
  const errorModal = document.getElementById("login-error-modal");
  const errorRetryBtn = document.getElementById("login-error-retry-btn");
  const errorCancelBtn = document.getElementById("login-error-cancel-btn");

  if (errorRetryBtn) {
    errorRetryBtn.addEventListener("click", function () {
      hideLoginErrorModal();
      // Re-open login modal
      setTimeout(() => {
        const loginModal = document.getElementById("loginModal");
        if (loginModal && typeof openModal === "function") {
          openModal(loginModal);
        }
      }, 300);
    });
  }

  if (errorCancelBtn) {
    errorCancelBtn.addEventListener("click", function () {
      hideLoginErrorModal();
    });
  }

  // Close modal dengan klik backdrop
  if (errorModal) {
    errorModal.addEventListener("click", function (e) {
      if (e.target === errorModal) {
        hideLoginErrorModal();
      }
    });
  }

  // Try to attach Google login listener immediately
  if (!attachGoogleLoginListener()) {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 500);
  }
});

/**
 * ============================================
 * GOOGLE LISTENER CHECK STRATEGIES
 * ============================================
 */
// Strategy for when DOM already loaded
if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  setTimeout(attachGoogleLoginListener, 100);
}

// Strategy: Interval check as last resort
let loginAttempts = 0;
const maxLoginAttempts = 20;
const loginCheckInterval = setInterval(() => {
  loginAttempts++;

  if (attachGoogleLoginListener()) {
    clearInterval(loginCheckInterval);
  } else if (loginAttempts >= maxLoginAttempts) {
    clearInterval(loginCheckInterval);
  }
}, 250);

// Listen for modal open events
document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "open-login") {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 200);
  }
});