<?php
session_start();

$pending = $_SESSION['pending_login'] ?? null;
if (!$pending) {
    $_SESSION['error'] = 'Please log in first.';
    header('Location: /BIS/views/login.php');
    exit();
}

$maskedEmail = preg_replace('/(^.).*(@.*$)/', '$1***$2', (string) ($pending['email'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="card-title text-primary">OTP Verification</h3>
                        <p class="text-muted mb-0">Enter the 6-digit code sent to</p>
                        <small><?= htmlspecialchars($maskedEmail) ?></small>
                    </div>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/BIS/controller/verify_login_otp.php">
                        <div class="mb-3">
                            <label for="otp" class="form-label">One-Time Password</label>
                            <input type="text" class="form-control" id="otp" name="otp" maxlength="6" pattern="\d{6}" placeholder="Enter 6-digit OTP" required>
                        </div>
                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-primary btn-lg">Verify OTP</button>
                        </div>
                    </form>

                    <form method="POST" action="/BIS/controller/resend_login_otp.php" class="d-grid mb-2">
                        <button type="submit" class="btn btn-outline-secondary">Resend OTP</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/BIS/views/login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
