<?php
// auth-modals.php - Modal untuk Login dan Sign Up
// Compatible dengan login.js dan registrasi.js yang sudah ada
?>

<style>
    /* ========== MODAL BASE STYLES ========== */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
        justify-content: center;
        align-items: center;
    }

    .modal.open {
        display: flex !important;
    }

    .modal.closing {
        animation: fadeOut 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    /* Close Button */
    .close-btn {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        color: #fff;
        cursor: pointer;
        z-index: 10;
        text-decoration: none;
        transition: color 0.3s ease, transform 0.3s ease;
    }

    .close-btn:hover {
        color: #b089f4;
        transform: scale(1.1) rotate(90deg);
    }

    /* Modal Container */
    .modal-container,
    .signup-modal {
        background: rgba(34, 36, 58, 0.26);
        border-radius: 16px;
        box-shadow: 0 8px 22px #0006;
        max-width: 350px;
        min-width: 220px;
        width: 100%;
        padding: 0;
        overflow: hidden;
        backdrop-filter: blur(9px);
        -webkit-backdrop-filter: blur(9px);
        border: 2px solid rgba(255, 255, 255, 0.14);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .signup-modal {
        max-width: 410px;
    }

    .modal-logo,
    .signup-modal .modal-logo {
        width: 44px;
        height: 44px;
        margin: 13px auto 6px auto;
        background: rgba(255, 255, 255, 0.13);
        border-radius: 50%;
        box-shadow: 0 1px 6px #e2c7fd33;
        object-fit: contain;
        display: block;
    }

    .modal-left,
    .signup-modal .modal-left {
        width: 100%;
        background: none;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }

    .modal-right,
    .signup-modal .modal-right {
        width: 100%;
        background: none;
        padding: 12px 14px 10px 14px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
    }

    .modal-container h2,
    .signup-modal h2 {
        font-size: 1.08rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #fff;
        text-align: center;
        letter-spacing: .45px;
    }

    .signup-modal .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 12px;
        margin-bottom: 10px;
    }

    .signup-modal .form-grid .field-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 540px) {

        .signup-modal,
        .modal-container {
            max-width: 99vw;
        }

        .signup-modal .form-grid {
            grid-template-columns: 1fr;
            gap: 8px 0;
        }

        .signup-modal .form-grid .field-full {
            grid-column: auto;
        }
    }

    .modal-container .input-group,
    .signup-modal .input-group {
        margin-bottom: 10px;
    }

    .modal-container .input-group input,
    .signup-modal .input-group input {
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1.2px solid rgba(255, 255, 255, 0.15);
        border-radius: 7px;
        font-weight: 500;
        font-size: 13px;
        color: #fff !important;
        padding: 8px 10px;
        margin-bottom: 3px;
        min-width: 0;
        width: 100%;
    }

    .modal-container .input-group input:focus,
    .signup-modal .input-group input:focus {
        background: rgba(255, 255, 255, 0.19) !important;
        border-color: #b089f4;
        color: #fff !important;
        outline: none;
    }

    ::placeholder {
        color: #d0d0d0;
        opacity: 0.93;
    }

    .modal-container .divider,
    .signup-modal .divider {
        margin: 9px 0 6px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        gap: 0.35em;
        text-align: center;
    }

    .modal-container .divider::before,
    .signup-modal .divider::before,
    .modal-container .divider::after,
    .signup-modal .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.22) 100%);
        border-radius: 1.3px;
    }

    .modal-container .divider span,
    .signup-modal .divider span {
        display: inline-block;
        background: rgba(34, 36, 58, 0.78);
        font-weight: 800;
        font-size: .98rem;
        color: #fff;
        text-align: center;
        padding: 3px 10px;
        border-radius: 7px;
        letter-spacing: .5px;
        box-shadow: 0 1px 3px #0001;
    }

    .modal-container .btn-google,
    .signup-modal .btn-google {
        background: rgba(255, 255, 255, 0.14);
        color: #fff !important;
        border-radius: 7px;
        border: 1.8px solid #e1e1e1;
        font-size: 13px;
        padding: 8px 10px;
        margin-bottom: 5px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .modal-container .btn-google:hover,
    .signup-modal .btn-google:hover {
        background: #44337060;
        border-color: #b089f4;
    }

    .modal-container .btn-login,
    .signup-modal .btn-login {
        background: linear-gradient(90deg, #a97c50 65%, #b089f4 100%);
        color: #fff !important;
        width: 100%;
        border: 0;
        border-radius: 8px;
        padding: 9px 0;
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: .4px;
        margin-top: 8px;
        margin-bottom: 3px;
        transition: background .3s;
        box-shadow: 0 2px 9px #b089f433;
        cursor: pointer;
    }

    .modal-container .btn-login:hover,
    .signup-modal .btn-login:hover {
        background: linear-gradient(90deg, #b089f4 32%, #a97c50 100%);
    }

    .modal-container .password-group,
    .signup-modal .password-group {
        position: relative;
    }

    .modal-container .password-group input,
    .signup-modal .password-group input {
        padding-right: 42px !important;
        box-sizing: border-box;
    }

    .modal-container .password-toggle,
    .signup-modal .password-toggle {
        position: absolute;
        top: 0;
        bottom: 0;
        right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 28px;
        background: none;
        border: none;
        color: #eee;
        cursor: pointer;
        z-index: 11;
        margin: 0;
        padding: 0;
        transition: color .18s;
    }

    .modal-container .password-toggle:hover,
    .signup-modal .password-toggle:hover {
        color: #b089f4;
    }

    .modal-container .eye-icon,
    .signup-modal .eye-icon {
        width: 18px;
        height: 18px;
        display: block;
        stroke: currentColor;
        pointer-events: none;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        display: none;
    }

    /* ========== LOGIN ERROR MODAL STYLES ========== */
    .login-error-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }

    .login-error-modal-overlay.show {
        display: flex;
    }

    .login-error-modal-container {
        background: #fff;
        border-radius: 20px;
        padding: 40px 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease;
    }

    .login-error-modal-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #d9534f 0%, #c9302c 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(217, 83, 79, 0.3);
    }

    .login-error-modal-icon i {
        font-size: 2.5em;
        color: #fff;
    }

    .login-error-modal-title {
        font-size: 1.5em;
        font-weight: 700;
        color: #d9534f;
        margin-bottom: 10px;
    }

    .login-error-modal-text {
        font-size: 1em;
        color: #666;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .login-error-modal-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .login-error-btn-confirm,
    .login-error-btn-cancel {
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .login-error-btn-confirm {
        background: linear-gradient(135deg, #b49666 0%, #a97c50 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(180, 150, 102, 0.3);
    }

    .login-error-btn-confirm:hover {
        background: linear-gradient(135deg, #a97c50 0%, #8b5e3c 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(180, 150, 102, 0.4);
    }

    .login-error-btn-cancel {
        background: #6c757d;
        color: #fff;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .login-error-btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }
</style>

<!-- POPUP LOGIN -->
<div id="loginModal" class="modal">
    <div class="modal-container">
        <a href="#" class="close-btn" id="close-login">&times;</a>

        <!-- Left section dengan logo -->
        <div class="modal-left">
            <div class="logo-container">
                <img src="<?php echo isset($navbarPath) ? $navbarPath : ''; ?>assets/logo_majelis_noBg.png" alt="Majelis MDPL Logo" class="modal-logo">
            </div>
        </div>

        <!-- Right section dengan form -->
        <div class="modal-right">
            <h2>Login</h2>

            <form method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required autocomplete="username" />
                </div>

                <!-- Input Password dengan toggle show/hide -->
                <div class="input-group password-group">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required autocomplete="current-password" />
                    <button type="button" class="password-toggle" id="toggleLoginPassword">
                        <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" width="20" height="20" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <button type="submit" class="btn-login">Masuk</button>
                <div style="text-align:center;margin-top:13px;">
                    <a href="<?php echo isset($navbarPath) ? $navbarPath : ''; ?>user/lupa-password.php" style="color: #a97c50; text-decoration: underline; font-size:14px;">
                        Lupa password?
                    </a>
                </div>

                <!-- Tombol Login dengan Google -->
                <div class="divider">
                    <span>atau</span>
                </div>

                <div class="google-login-section">
                    <button type="button" id="googleLoginBtn" class="btn-google">
                        <img src="<?php echo isset($navbarPath) ? $navbarPath : ''; ?>assets/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
                        Login with Google
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- POPUP SIGN UP -->
<div id="signUpModal" class="modal">
    <div class="modal-container signup-modal">
        <a href="#" class="close-btn" id="close-signup">&times;</a>

        <!-- Left section dengan logo -->
        <div class="modal-left">
            <div class="logo-container">
                <img src="<?php echo isset($navbarPath) ? $navbarPath : ''; ?>assets/logo_majelis_noBg.png" alt="Majelis MDPL Logo" class="modal-logo">
            </div>
        </div>

        <!-- Right section dengan form -->
        <div class="modal-right">
            <h2>Sign Up</h2>

            <form method="POST" novalidate>
                <div class="form-grid">
                    <!-- Row 1 -->
                    <div class="input-group">
                        <input type="text" name="username" placeholder="Username" autocomplete="username" required minlength="3" />
                        <small class="error-message">Username minimal 3 karakter</small>
                    </div>
                    <div class="input-group password-group">
                        <input type="password" name="password" id="signupPassword" placeholder="Password" autocomplete="new-password" required minlength="6" />
                        <button type="button" class="password-toggle" id="toggleSignupPassword">
                            <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                        <small class="error-message">Password minimal 6 karakter</small>
                    </div>

                    <!-- Row 2 -->
                    <div class="input-group password-group">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Konfirmasi Password" autocomplete="new-password" required />
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <svg class="eye-icon show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="eye-icon hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                        <small class="error-message">Password tidak cocok</small>
                    </div>
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Email" autocomplete="email" required />
                        <small class="error-message">Format email tidak valid</small>
                    </div>

                    <!-- Row 3 -->
                    <div class="input-group">
                        <input type="tel" name="no_wa" placeholder="No HP (contoh: 081234567890)" inputmode="tel" autocomplete="tel" required />
                        <small class="error-message">Format nomor HP tidak valid</small>
                    </div>
                    <div class="input-group">
                        <input type="text" name="alamat" placeholder="Alamat" autocomplete="street-address" required />
                    </div>
                </div>

                <button type="submit" class="btn-login">Daftar</button>

                <!-- Tombol Sign Up dengan Google -->
                <div class="divider">
                    <span>atau</span>
                </div>
                <div class="google-signup-section">
                    <button type="button" id="googleSignUpBtn" class="btn-google">
                        <img src="<?php echo isset($navbarPath) ? $navbarPath : ''; ?>assets/g-logo.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
                        Sign up with Google
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LOGIN ERROR MODAL -->
<div id="login-error-modal" class="login-error-modal-overlay">
    <div class="login-error-modal-container">
        <div class="login-error-modal-icon">
            <i class="fa-solid fa-exclamation"></i>
        </div>
        <h2 class="login-error-modal-title">Login Gagal</h2>
        <p class="login-error-modal-text" id="login-error-message">Terjadi kesalahan. Silakan coba lagi.</p>
        <div class="login-error-modal-buttons">
            <button id="login-error-retry-btn" class="login-error-btn-confirm">Coba Lagi</button>
            <button id="login-error-cancel-btn" class="login-error-btn-cancel">Batal</button>
        </div>
    </div>
</div>

<script>
    // ========== MODAL OPEN/CLOSE FUNCTIONS ==========
    // Minimal script hanya untuk buka/tutup modal
    // Logika form submit, validasi, dan API ada di login.js & registrasi.js

    const OPEN = "open";
    const CLOSING = "closing";
    const DURATION = 300;

    function openModal(el) {
        if (!el) return;
        el.classList.remove(CLOSING);
        el.style.display = "flex";
        void el.offsetWidth; // Force reflow
        el.classList.add(OPEN);
        document.body.style.overflow = "hidden";
    }

    function closeModal(el) {
        if (!el) return;
        el.classList.remove(OPEN);
        el.classList.add(CLOSING);
        setTimeout(() => {
            el.classList.remove(CLOSING);
            el.style.display = "none";
            document.body.style.overflow = "";
        }, DURATION);
    }

    // ========== PASSWORD TOGGLE HELPER ==========
    function togglePasswordVisibility(passwordInput, toggleButton) {
        const showIcon = toggleButton.querySelector('.eye-icon.show');
        const hideIcon = toggleButton.querySelector('.eye-icon.hide');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'block';
        } else {
            passwordInput.type = 'password';
            showIcon.style.display = 'block';
            hideIcon.style.display = 'none';
        }
    }

    // ========== INIT MODALS WHEN DOM LOADED ==========
    document.addEventListener('DOMContentLoaded', function() {
        // LOGIN MODAL
        const loginModal = document.getElementById("loginModal");
        const openLogin = document.getElementById("open-login");
        const closeLogin = document.getElementById("close-login");

        if (openLogin && loginModal && closeLogin) {
            openLogin.addEventListener("click", (e) => {
                e.preventDefault();
                openModal(loginModal);
            });

            closeLogin.addEventListener("click", (e) => {
                e.preventDefault();
                closeModal(loginModal);
            });

            loginModal.addEventListener("click", (e) => {
                if (e.target === loginModal) closeModal(loginModal);
            });
        }

        // SIGNUP MODAL
        const signUpModal = document.getElementById("signUpModal");
        const openSignUp = document.getElementById("open-signup");
        const closeSignUp = document.getElementById("close-signup");

        if (openSignUp && signUpModal && closeSignUp) {
            openSignUp.addEventListener("click", (e) => {
                e.preventDefault();
                openModal(signUpModal);
            });

            closeSignUp.addEventListener("click", (e) => {
                e.preventDefault();
                closeModal(signUpModal);
            });

            signUpModal.addEventListener("click", (e) => {
                if (e.target === signUpModal) closeModal(signUpModal);
            });
        }

        // ESC KEY HANDLER
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                if (signUpModal && signUpModal.classList.contains('open')) closeModal(signUpModal);
                if (loginModal && loginModal.classList.contains('open')) closeModal(loginModal);
            }
        });

        // PASSWORD TOGGLES
        const toggleLoginPassword = document.getElementById('toggleLoginPassword');
        const loginPassword = document.getElementById('loginPassword');
        if (toggleLoginPassword && loginPassword) {
            toggleLoginPassword.addEventListener('click', function() {
                togglePasswordVisibility(loginPassword, toggleLoginPassword);
            });
        }

        const toggleSignupPassword = document.getElementById('toggleSignupPassword');
        const signupPassword = document.getElementById('signupPassword');
        if (toggleSignupPassword && signupPassword) {
            toggleSignupPassword.addEventListener('click', function() {
                togglePasswordVisibility(signupPassword, toggleSignupPassword);
            });
        }

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        if (toggleConfirmPassword && confirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                togglePasswordVisibility(confirmPassword, toggleConfirmPassword);
            });
        }
    });

    // Expose close functions globally untuk digunakan oleh login.js dan registrasi.js
    window.closeLoginModal = function() {
        const modal = document.getElementById("loginModal");
        if (modal) closeModal(modal);
    };

    window.closeSignUpModal = function(force = false) {
        const modal = document.getElementById("signUpModal");
        if (modal) closeModal(modal);
    };
</script>