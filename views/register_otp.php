<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id'])) {
    $_SESSION['error'] = 'Registration session expired. Please register again.';
    header('Location: /BIS/views/register.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$refNo = (string)($flow['ref_no'] ?? '');
$email = (string)($flow['email'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Email OTP Verification</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body class="bg-app d-flex flex-column min-vh-100">

<div class="container flex-grow-1 py-5" style="max-width:760px;">

  <!-- Progress -->
  <div class="card app-card shadow-sm mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between small text-muted mb-2">
        <span>Register</span>
        <span class="fw-bold text-dark">OTP</span>
        <span>Upload ID</span>
        <span>Approval</span>
      </div>
      <div class="progress" style="height:8px;">
        <div class="progress-bar" style="width:50%"></div>
      </div>
    </div>
  </div>

  <!-- OTP Card -->
  <div class="card app-card shadow-lg border-0">
    <div class="card-body p-4">

      <h4 class="mb-2">Verify Email OTP</h4>
      <p class="text-muted mb-3">
        Enter the 6-digit OTP sent to your email address.
      </p>

      <?php if ($refNo): ?>
        <div class="alert alert-info">
          <strong>Reference No:</strong> <?= htmlspecialchars($refNo) ?><br>
          <strong>Email:</strong> <?= htmlspecialchars($email) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- Verify OTP Form -->
      <form method="post" action="/BIS/controller/register_verify_otp.php" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
          <label class="form-label">Enter OTP</label>
          <input
            type="text"
            name="otp"
            class="form-control"
            maxlength="6"
            pattern="\d{6}"
            inputmode="numeric"
            placeholder="Enter 6-digit OTP"
            required
          >
          <div class="form-text">OTP must be 6 digits.</div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
          Verify OTP
        </button>
      </form>

      <!-- Resend OTP -->
      <form method="post" action="/BIS/controller/register_resend_otp.php" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button type="submit" class="btn btn-outline-secondary w-100">
          Resend OTP
        </button>
      </form>

      <!-- Start Over -->
      <div class="text-center">
        <a href="/BIS/views/register.php" class="text-decoration-none">
          Start over
        </a>
      </div>

    </div>
  </div>

</div>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    &copy; 2026 Barangay Don Galo. All rights reserved.
    <a href="#" class="text-white text-decoration-none ms-3">Privacy Policy</a>
    <a href="#" class="text-white text-decoration-none ms-3">Terms of Service</a>
  </div>
</footer>

</body>
</html>