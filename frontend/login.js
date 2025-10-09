function closeLoginModal() {
  const modal = document.getElementById("loginModal");
  if (modal) {
    modal.style.display = "none";
  }
}

// Fungsi untuk handle Google OAuth Login
function handleGoogleLogin() {
  window.location.href =
    window.location.origin +
    "/majelismdpl.com/backend/google-oauth.php?type=login";
}

// Function untuk attach Google login button listener
function attachGoogleLoginListener() {
  const googleLoginBtn = document.getElementById("googleLoginBtn");

  if (googleLoginBtn && !googleLoginBtn.hasAttribute("data-login-listener")) {
    googleLoginBtn.setAttribute("data-login-listener", "true");

    // Method 1: addEventListener
    googleLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleLogin();
    });

    // Method 2: onclick as backup
    googleLoginBtn.onclick = function (e) {
      e.preventDefault();
      handleGoogleLogin();
      return false;
    };

    return true;
  }
  return false;
}

// Handle regular form submission
document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.querySelector("#loginModal form");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch("/majelismdpl.com/backend/login-api.php", {
          method: "POST",
          body: formData,
        });

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const result = await response.json();
        closeLoginModal();

        if (result.success) {
          Swal.fire({
            title: "Login berhasil!",
            text: "Selamat datang " + formData.get("username"),
            icon: "success",
            confirmButtonText: "Lanjutkan",
          }).then(() => {
            if (result.role === "admin") {
              setTimeout(() => {
                window.location.href = "/majelismdpl.com/admin/index.php";
              }, 100);
            } else {
              window.location.href = "/majelismdpl.com";
            }
          });
        } else {
          Swal.fire({
            title: "Login gagal",
            text: result.message,
            icon: "error",
            confirmButtonText: "Coba lagi",
          });
        }
      } catch (error) {
        closeLoginModal();
        Swal.fire({
          title: "Error",
          text: "Terjadi kesalahan sistem. Silakan coba lagi.",
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    });
  }

  // Try to attach Google login listener immediately
  if (!attachGoogleLoginListener()) {
    // Try with delay
    setTimeout(() => {
      attachGoogleLoginListener();
    }, 500);
  }
});

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

// Global fallback function
window.handleGoogleLogin = handleGoogleLogin;
