/**
 * ============================================
 * CSS INJECTION FOR CUSTOM SWEETALERT STYLING
 * ============================================
 * (Tetap dibutuhkan untuk popup Error atau popup Session nanti)
 */
document.addEventListener("DOMContentLoaded", function () {
  const PRIMARY_COLOR = "#a9865a"; 
  const SECONDARY_COLOR = "#6c757d"; 
  const PRIMARY_HOVER = "#8B6B4A"; 
  const SECONDARY_HOVER = "#5a6268"; 

  const customSwalCss = `
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
        .swal2-actions {
            width: 100% !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: center !important;
            gap: 15px !important; 
            margin-top: 0 !important;
            box-sizing: border-box !important;
        }
        .swal2-styled {
            flex: 1 !important;
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
        .swal2-styled.swal2-confirm {
            background-color: ${PRIMARY_COLOR} !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(169, 134, 90, 0.3) !important;
        }
        .swal2-styled.swal2-confirm:hover {
            background-color: ${PRIMARY_HOVER} !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(169, 134, 90, 0.4) !important;
        }
        .swal2-styled.swal2-cancel {
            background-color: ${SECONDARY_COLOR} !important;
            color: white !important;
        }
        .swal2-styled.swal2-cancel:hover {
            background-color: ${SECONDARY_HOVER} !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3) !important;
        }
        .swal2-deny {
            display: none !important;
        }
        .swal2-icon.swal2-success { border-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border-color: ${PRIMARY_COLOR} !important; }
        .swal2-icon.swal2-error { border-color: #d33 !important; }
        
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

  if (loginForm && !loginForm.hasAttribute("data-form-listener")) {
    
    loginForm.setAttribute("data-form-listener", "true");

    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      e.stopImmediatePropagation(); 

      if (this.getAttribute("data-submitting") === "true") {
        return;
      }

      this.setAttribute("data-submitting", "true");
      
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

          // ------------------------------------------------------------------
          // PERUBAHAN UTAMA DI SINI:
          // SweetAlert dihapus. JS hanya bertugas redirect.
          // Popup akan muncul otomatis karena PHP sudah set Session.
          // ------------------------------------------------------------------
          
          if (result.redirect_url) {
             window.location.href = result.redirect_url;
          } else {
             // Fallback jika API tidak kirim redirect_url
             if (["admin", "super_admin"].includes(result.role)) {
                window.location.href = getPageUrl("admin/index.php");
             } else {
                window.location.href = getPageUrl("index.php");
             }
          }

        } else {
          // ❌ LOGIN GAGAL (Tetap pakai SweetAlert di sini karena halaman tidak reload)
          this.removeAttribute("data-submitting");
          
          closeLoginModal();
          const errorMessage = result.message || "Username atau kata sandi salah.";

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
                showDenyButton: false,
                backdrop: `rgba(0,0,0,0.5)`,
                buttonsStyling: true,
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
        this.removeAttribute("data-submitting");
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

  // Error Modal Handlers
  const errorRetryBtn = document.getElementById("login-error-retry-btn");
  if (errorRetryBtn && !errorRetryBtn.hasAttribute("data-listener")) {
    errorRetryBtn.setAttribute("data-listener", "true");
    errorRetryBtn.addEventListener("click", function () {
      hideLoginErrorModal();
      setTimeout(() => {
        const loginModal = document.getElementById("loginModal");
        if (loginModal && typeof openModal === "function")
          openModal(loginModal);
      }, 300);
    });
  }

  const errorCancelBtn = document.getElementById("login-error-cancel-btn");
  if (errorCancelBtn && !errorCancelBtn.hasAttribute("data-listener")) {
    errorCancelBtn.setAttribute("data-listener", "true");
    errorCancelBtn.addEventListener("click", function () {
      hideLoginErrorModal();
    });
  }

  const errorModal = document.getElementById("login-error-modal");
  if (errorModal && !errorModal.hasAttribute("data-listener")) {
    errorModal.setAttribute("data-listener", "true");
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