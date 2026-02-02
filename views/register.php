<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Don Galo - Register</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/BIS/assets/css/register.css">
</head>
<body>

<!-- Register Form Container -->
<div id="login-container" class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <h3 class="card-title text-primary">Create Account</h3>
                        <p class="text-muted">Register to access the Community Portal</p>
                    </div>

                    <!-- Alerts -->
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="/BIS/controller/register_process.php" id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" autocomplete="name" required>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
        </div>

        <div class="password-container mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="new-password" required>
            <button type="button" class="password-toggle" id="toggle-password">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>

        <div class="password-container mb-3">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password" required>
            <button type="button" class="password-toggle" id="toggle-confirm-password">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">Register</button>
        </div>

        <div class="text-center">
            Already have an account? <a href="login.php" class="text-decoration-none">Login</a>
        </div>
</form>


                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        &copy; 2026 Barangay Don Galo. All rights reserved.
        <a href="#" class="ms-3">Privacy Policy</a>
        <a href="#" class="ms-3">Terms of Service</a>
    </div>
</footer>

<!-- JS -->
<script src="/BIS/assets/js/register.js"></script>

</body>
</html>
