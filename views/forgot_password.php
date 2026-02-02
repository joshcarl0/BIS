<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your CSS -->
    <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>

<div id="login-container" class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <h3 class="card-title text-primary">Forgot Password</h3>
                        <p class="text-muted">Enter your registered email</p>
                    </div>

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

                    <form method="POST" action="/BIS/controller/forgot_password_process.php">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Send OTP</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/BIS/views/login.php" class="text-decoration-none">Back to Login</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        &copy; <?= date('Y') ?> Barangay Don Galo. All rights reserved.
    </div>
</footer>

</body>
</html>
