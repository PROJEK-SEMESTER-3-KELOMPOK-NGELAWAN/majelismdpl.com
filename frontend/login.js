/**
 * ============================================
 * CSS INJECTION FOR CUSTOM SWEETALERT STYLING
 * ============================================
 * Menyesuaikan tampilan SweetAlert2 agar mirip dengan gambar konfirmasi Logout.
 */
document.addEventListener("DOMContentLoaded", function () {
  // Definisi Warna
  const PRIMARY_COLOR = "#a9865a"; // Coklat Keemasan
  const SECONDARY_COLOR = "#6c757d"; // Abu-abu

  const customSwalCss = `
        .swal2-popup {
            border-radius: 20px !important; 
            max-width: 420px; /* Sedikit diperlebar agar tombol muat */
            width: 90%;
            padding: 30px 40px;
            text-align: center;
            font-family: 'Poppins', sans-serif; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
        }

        .swal2-title {
            color: ${PRIMARY_COLOR} !important;
            font-size: 1.6em !important;
            font-weight: 700 !important;
            margin-bottom: 10px !important;
        }

        .swal2-html-container {
            color: #555 !important;
            font-size: 1em !important;
            margin-bottom: 20px !important;
        }

        /* Container Tombol */
        .swal2-actions {
            width: 100% !important;
            justify-content: center !important;
            gap: 15px !important; /* Jarak antar tombol */
            margin-top: 10px !important;
        }

        /* Tombol Konfirmasi (Login) */
        .swal2-styled.swal2-confirm {
            background-color: ${PRIMARY_COLOR} !important;
            color: white !important;
            border-radius: 10px !important; 
            border: none !important;
            font-size: 1rem !important;
            font-weight: 600 !important; 
            padding: 12px 0 !important; /* Padding atas bawah */
            width: 140px !important;    /* LEBAR TETAP */
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 10px rgba(169, 134, 90, 0.3) !important;
        }

        /* Tombol Batal */
        .swal2-styled.swal2-cancel {
            background-color: ${SECONDARY_COLOR} !important;
            color: white !important;
            border-radius: 10px !important;
            border: none !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            padding: 12px 0 !important; /* Padding atas bawah */
            width: 140px !important;    /* LEBAR TETAP (SAMA DENGAN KONFIRMASI) */
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Icon Styles */
        .swal2-icon.swal2-error { border-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-error [class^=swal2-x-mark-line] { background-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border-color: ${PRIMARY_COLOR} !important; }

        .swal2-custom-background { background: none !important; }
    `;

  // Buat dan Sisipkan elemen <style> ke <head>
  if (!document.getElementById("custom-swal-styles")) {
    const styleSheet = document.createElement("style");
    styleSheet.id = "custom-swal-styles";
    styleSheet.type = "text/css";
    styleSheet.innerText = customSwalCss;
    document.head.appendChild(styleSheet);
  }
});

/**
 * ============================================
 * MODAL & ERROR HANDLING FUNCTIONS
 * ============================================
 */
function closeLoginModal() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    if (typeof closeModal === "function") {
      closeModal(modal);
    } else {
      modal.style.display = "none";
      modal.classList.remove("open");
      document.body.style.overflow = "";
    }
  }
}

function showLoginErrorModal(errorMessage) {
  const errorModal = document.getElementById("login-error-modal");
  const errorMessageEl = document.getElementById("login-error-message");

  if (errorModal && errorMessageEl) {
    errorMessageEl.textContent =
      errorMessage || "Terjadi kesalahan. Silakan coba lagi.";
    errorModal.classList.add("show");
  }
}

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
function handleGoogleLogin() {
  window.location.href = getPageUrl("backend/google-oauth.php") + "?type=login";
}

function getBasePath() {
  return getPageUrl("").replace(window.location.origin, "");
}

function attachGoogleLoginListener() {
  const googleLoginBtn = document.getElementById("googleLoginBtn");

  if (googleLoginBtn && !googleLoginBtn.hasAttribute("data-login-listener")) {
    googleLoginBtn.setAttribute("data-login-listener", "true");
    googleLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleLogin();
    });
    return true;
  }
  return false;
}

window.handleGoogleLogin = handleGoogleLogin;

/**
 * ============================================
 * REGULAR FORM SUBMISSION HANDLER
 * ============================================
 */
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");

  // Warna untuk JS config (fallback jika CSS tidak load)
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
          // ✅ 1. LOGIN BERHASIL
          closeLoginModal();

          // Redirect langsung (Popup sukses ditangani oleh flash_handler.php di halaman tujuan)
          if (["admin", "super_admin"].includes(result.role)) {
            window.location.href = getPageUrl("admin/index.php");
          } else {
            window.location.href = getPageUrl("index.php");
          }
        } else {
          // ❌ 2. LOGIN GAGAL
          closeLoginModal();
          const errorMessage =
            result.message || "Username atau kata sandi salah.";

          setTimeout(() => {
            if (typeof Swal !== "undefined") {
              Swal.fire({
                title: "Login Gagal",
                text: errorMessage,
                icon: "error",
                confirmButtonText: "Coba Lagi",
                // Hapus color inline agar CSS yang menangani
                // confirmButtonColor: PRIMARY_BUTTON_COLOR,
                showCancelButton: true,
                cancelButtonText: "Batal",
                // cancelButtonColor: SECONDARY_BUTTON_COLOR,
                backdrop: `rgba(0,0,0,0.5)`,
                buttonsStyling: true, // Pastikan styling aktif
                customClass: {
                  // Tidak perlu class khusus jika CSS global sudah menargetkan .swal2-popup
                },
              }).then((action) => {
                if (action.isConfirmed) {
                  const loginModal = document.getElementById("loginModal");
                  if (loginModal && typeof openModal === "function") {
                    openModal(loginModal);
                  }
                }
              });
            } else {
              alert(errorMessage);
            }
          }, 300);
        }
      } catch (error) {
        // ⚠️ 3. ERROR SISTEM
        closeLoginModal();
        console.error("Login Error:", error);

        setTimeout(() => {
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Terjadi Kesalahan",
              text: "Gagal terhubung ke server.",
              icon: "error",
              confirmButtonText: "Tutup",
            });
          }
        }, 300);
      }
    });
  }

  // Error Modal Handlers
  const errorModal = document.getElementById("login-error-modal");
  const errorRetryBtn = document.getElementById("login-error-retry-btn");
  const errorCancelBtn = document.getElementById("login-error-cancel-btn");

  if (errorRetryBtn) {
    errorRetryBtn.addEventListener("click", function () {
      hideLoginErrorModal();
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

  if (errorModal) {
    errorModal.addEventListener("click", function (e) {
      if (e.target === errorModal) {
        hideLoginErrorModal();
      }
    });
  }

  if (!attachGoogleLoginListener()) {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 500);
  }
});

/**
 * ============================================
 * GOOGLE LISTENER CHECK
 * ============================================
 */
if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  setTimeout(attachGoogleLoginListener, 100);
}

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

document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "open-login") {
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 200);
  }
});
