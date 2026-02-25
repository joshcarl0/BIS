<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Don Galo - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>
    

    <!-- Login Form Container -->
    <div id="login-container" class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="card-title text-primary">Barangay Don Galo</h3>
                            <p class="text-muted">Community Portal</p>
                        </div>

                        <!-- Display Error -->
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Display Success (e.g., after registration) -->
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning d-none" id="cookieConsentWarning" role="alert">
                            You must agree to the Cookie Consent Agreement before signing in.
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="/BIS/controller/login_process.php" id="loginForm">
                                    <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                    placeholder="Enter your username" required autocomplete="username">
                                            </div>

                            <div class="password-container mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                <button type="button" class="password-toggle" id="toggle-password">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                            </div>

                            <div class="text-center">
                                <a href="../views/forgot_password.php" class="text-decoration-none">Forgot password?</a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="../views/register.php" class="text-decoration-none">Register</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            &copy; 2026 Barangay Don Galo. All rights reserved.
            <a href="/BIS/views/privacy_policy.php" class="text-white text-decoration-none ms-3">Privacy Policy</a>
            <a href="#" class="text-white text-decoration-none ms-3">Terms of Service</a>
        </div>    
    </footer>

    <?php include_once __DIR__ . '/../includes/cookie_consent_modal.php'; ?>

    <!-- JS Scripts -->
    <script src="/BIS/assets/js/login.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const consentWarning = document.getElementById('cookieConsentWarning');
            const consentKey = 'bis_cookie_consent_v1';

            function hasCookieConsent() {
                return localStorage.getItem(consentKey) === 'accepted';
            }

            if (loginForm) {
                loginForm.addEventListener('submit', function (event) {
                    if (!hasCookieConsent()) {
                        event.preventDefault();
                        if (consentWarning) {
                            consentWarning.classList.remove('d-none');
                        }
                        if (typeof window.bisShowCookieConsentModal === 'function') {
                            window.bisShowCookieConsentModal();
                        }
                    }
                });
            }

            document.addEventListener('bis-cookie-consent-accepted', function () {
                if (consentWarning) {
                    consentWarning.classList.add('d-none');
                }
            });
        });
    </script>
</body>
</html>
