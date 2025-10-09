function closeSignUpModal() {
  const modal = document.getElementById("signUpModal");
  if (modal) {
    modal.style.display = "none";
  }
}

// Fungsi untuk handle Google OAuth
function handleGoogleOAuth() {
  window.location.href =
    window.location.origin + "/majelismdpl.com/backend/google-oauth.php";
}

// Multiple strategies to attach the event listener
function attachGoogleButtonListener() {
  const googleBtn = document.getElementById("googleSignUpBtn");

  if (googleBtn && !googleBtn.hasAttribute("data-js-listener")) {
    googleBtn.setAttribute("data-js-listener", "true");

    // Method 1: addEventListener
    googleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleGoogleOAuth();
    });

    // Method 2: onclick as backup
    googleBtn.onclick = function (e) {
      e.preventDefault();
      handleGoogleOAuth();
      return false;
    };

    return true;
  }
  return false;
}

// Strategy 1: DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  // Try immediately
  if (!attachGoogleButtonListener()) {
    // Try with delay
    setTimeout(() => {
      attachGoogleButtonListener();
    }, 500);
  }

  // Handle form submission
  const signUpForm = document.querySelector("#signUpModal form");
  if (signUpForm) {
    signUpForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      try {
        const response = await fetch(
          "/majelismdpl.com/backend/registrasi-api.php",
          {
            method: "POST",
            body: formData,
          }
        );

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const result = await response.json();
        closeSignUpModal();

        if (result.success) {
          Swal.fire({
            title: "Registrasi Berhasil!",
            text: "Selamat datang " + formData.get("username"),
            icon: "success",
            confirmButtonText: "Ke beranda",
          }).then(() => {
            window.location.href = "/majelismdpl.com";
          });
        } else {
          Swal.fire({
            title: "Registrasi Gagal",
            text: result.message,
            icon: "error",
            confirmButtonText: "Coba lagi",
          });
        }
      } catch (error) {
        closeSignUpModal();
        Swal.fire({
          title: "Error",
          text: "Terjadi kesalahan sistem. Silakan coba lagi.",
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    });
  }
});

// Strategy 2: Immediate execution if DOM already loaded
if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  setTimeout(attachGoogleButtonListener, 100);
}

// Strategy 3: Interval check as last resort
let attempts = 0;
const maxAttempts = 20;
const checkInterval = setInterval(() => {
  attempts++;

  if (attachGoogleButtonListener()) {
    clearInterval(checkInterval);
  } else if (attempts >= maxAttempts) {
    clearInterval(checkInterval);
  }
}, 250);

// Strategy 4: Listen for modal open events
document.addEventListener("click", function (e) {
  if (e.target && e.target.id === "open-signup") {
    setTimeout(() => {
      attachGoogleButtonListener();
    }, 200);
  }
});

// Global fallback function
window.handleGoogleSignUp = handleGoogleOAuth;
