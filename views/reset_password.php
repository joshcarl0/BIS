<?php
session_start();

if (empty($_SESSION['reset_user_id']) || empty($_SESSION['reset_otp_hash'])) {
    header('Location: /BIS/views/forgot_password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>

<div id="login-container" class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="row w-100 justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card">
        <div class="card-body p-4">
          <h4 class="text-center mb-2">Reset Password</h4>
          <p class="text-muted text-center mb-4">Enter OTP sent to your email</p>

          <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['reset_email']) ?>" disabled>
          </div>

          <form method="POST" action="/BIS/controller/reset_password_process.php">
            <div class="mb-3">
              <label class="form-label">OTP Code</label>
              <input type="text" name="otp" class="form-control" minlength="6" maxlength="6" required>
            </div>

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Reset Password</button>
          </form>

          <div class="text-center mt-3">
            <a href="/BIS/views/login.php">Back to Login</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<footer class="footer">© <?= date('Y') ?> Barangay Don Galo</footer>
</body>
</html>
