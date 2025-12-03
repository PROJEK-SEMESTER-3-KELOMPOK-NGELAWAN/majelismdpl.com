/**
 * ============================================
 * CSS INJECTION FOR CUSTOM SWEETALERT STYLING
 * ============================================
 * Update:
 * 1. Tombol 'No' (Deny) DIHAPUS TOTAL.
 * 2. Tombol Success & Error terpusat (Center).
 * 3. Styling warna Emas & Abu-abu.
 */
document.addEventListener("DOMContentLoaded", function () {
  const PRIMARY_COLOR = "#a9865a"; // Coklat Keemasan
  const SECONDARY_COLOR = "#6c757d"; // Abu-abu
  const PRIMARY_HOVER = "#8B6B4A"; // Coklat Gelap (Hover)
  const SECONDARY_HOVER = "#5a6268"; // Abu Gelap (Hover)

  const customSwalCss = `
        /* POPUP UTAMA */
        .swal2-popup {
            border-radius: 20px !important; 
            max-width: 400px; 
            width: 90%;
            padding: 30px 30px;
            text-align: center;
            font-family: 'Poppins', sans-serif; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
            box-sizing: border-box; 
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
            margin-bottom: 25px !important;
        }

        /* CONTAINER TOMBOL (FLEXBOX ROW) */
        /* REVISI: Menggunakan center agar tombol tunggal berada di tengah */
        .swal2-actions {
            width: 100% !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: center !important; /* SEBELUMNYA space-between */
            gap: 15px !important; 
            margin-top: 0 !important;
            box-sizing: border-box !important;
        }

        /* TOMBOL UMUM */
        .swal2-styled {
            flex: 1 !important; /* Tombol akan mengisi ruang (Full width jika sendiri, 50:50 jika berdua) */
            width: auto !important;
            border-radius: 10px !important; 
            border: none !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important; 
            padding: 12px 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: none !important;
            outline: none !important;
            margin: 0 !important; 
            transition: all 0.3s ease !important;
        }

        /* TOMBOL KONFIRMASI (Lanjutkan / Coba Lagi) */
        .swal2-styled.swal2-confirm {
            background-color: ${PRIMARY_COLOR} !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(169, 134, 90, 0.3) !important;
        }
        
        /* EFEK HOVER KONFIRMASI */
        .swal2-styled.swal2-confirm:hover {
            background-color: ${PRIMARY_HOVER} !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(169, 134, 90, 0.4) !important;
        }

        /* TOMBOL BATAL */
        .swal2-styled.swal2-cancel {
            background-color: ${SECONDARY_COLOR} !important;
            color: white !important;
        }

        /* EFEK HOVER BATAL */
        .swal2-styled.swal2-cancel:hover {
            background-color: ${SECONDARY_HOVER} !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3) !important;
        }

        /* HAPUS PAKSA TOMBOL DENY */
        .swal2-deny {
            display: none !important;
        }

        /* Icon Styles - Menyesuaikan warna icon dengan tema */
        .swal2-icon.swal2-success { border-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-error { border-color: #d33 !important; } /* Error tetap merah agar kontras */
        
        /* Responsif HP */
        @media (max-width: 480px) {
            .swal2-popup {
                padding: 25px 20px !important;
                width: 95% !important; 
            }
            .swal2-actions {
                gap: 10px !important;
            }
        }
    `;

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
 * MODAL FUNCTIONS
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
 * GOOGLE LOGIN
 * ============================================
 */
function handleGoogleLogin() {
  window.location.href =
    getPageUrl("backend/google-oauth.php") + "?type=login";
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
 * LOGIN FORM HANDLER
 * ============================================
 */
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");

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
          // ✅ LOGIN BERHASIL (DENGAN POPUP CENTER)
          closeLoginModal();

          if (typeof Swal !== "undefined") {
            Swal.fire({
              icon: "success",
              title: "Login Berhasil!",
              text: `Selamat datang kembali, ${result.username || "User"}`, // Menggunakan username dari result jika ada
              confirmButtonText: "Lanjutkan",
              buttonsStyling: true, // Menggunakan style custom kita
              backdrop: `rgba(0,0,0,0.5)`,
            }).then(() => {
              // Redirect setelah tombol diklik
              if (["admin", "super_admin"].includes(result.role)) {
                window.location.href = getPageUrl("admin/index.php");
              } else {
                window.location.href = getPageUrl("index.php");
              }
            });
          } else {
            // Fallback jika Swal tidak load
            if (["admin", "super_admin"].includes(result.role)) {
              window.location.href = getPageUrl("admin/index.php");
            } else {
              window.location.href = getPageUrl("index.php");
            }
          }
        } else {
          // ❌ LOGIN GAGAL
          closeLoginModal();
          const errorMessage =
            result.message || "Username atau kata sandi salah.";

          setTimeout(() => {
            if (typeof Swal !== "undefined") {
              Swal.fire({
                title: "Login Gagal",
                text: errorMessage,
                icon: "error",
                showConfirmButton: true,
                confirmButtonText: "Coba Lagi",
                showCancelButton: true,
                cancelButtonText: "Batal",
                showDenyButton: false, // Mematikan tombol deny
                backdrop: `rgba(0,0,0,0.5)`,
                buttonsStyling: true,
              }).then((action) => {
                if (action.isConfirmed) {
                  // Jika klik Coba Lagi
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
        closeLoginModal();
        console.error("Login Error:", error);
        setTimeout(() => {
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Terjadi Kesalahan",
              text: "Gagal terhubung ke server.",
              icon: "error",
              confirmButtonText: "Tutup",
              showCancelButton: false,
            });
          }
        }, 300);
      }
    });
  }

  // Error Modal Handlers (Fallback)
  const errorModal = document.getElementById("login-error-modal");
  const errorRetryBtn = document.getElementById("login-error-retry-btn");
  const errorCancelBtn = document.getElementById("login-error-cancel-btn");

  if (errorRetryBtn) {
    errorRetryBtn.addEventListener("click", function () {
      hideLoginErrorModal();
      setTimeout(() => {
        const loginModal = document.getElementById("loginModal");
        if (loginModal && typeof openModal === "function")
          openModal(loginModal);
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
      if (e.target === errorModal) hideLoginErrorModal();
    });
  }

  if (!attachGoogleLoginListener()) {
    setTimeout(attachGoogleLoginListener, 500);
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
    setTimeout(attachGoogleLoginListener, 200);
  }
});