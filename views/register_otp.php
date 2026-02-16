<?php
session_start();

$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id'])) {
    $_SESSION['error'] = 'Registration session expired. Please register again.';
    header('Location: /BIS/views/register.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:520px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h4 class="mb-1">Verify Email OTP</h4>
      <p class="text-muted mb-3">
        Ref: <b><?= htmlspecialchars($flow['ref_no'] ?? '') ?></b><br>
        Email: <?= htmlspecialchars($flow['email'] ?? '') ?>
      </p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" action="/BIS/controller/register_verify_otp.php" class="mb-3">
        <div class="mb-3">
          <label class="form-label">Enter OTP</label>
          <input type="text" name="otp" class="form-control" maxlength="6" required>
          <div class="form-text">OTP is 6 digits.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
      </form>

      <form method="post" action="/BIS/controller/register_resend_otp.php">
        <button type="submit" class="btn btn-outline-secondary w-100">Resend OTP</button>
      </form>

      <div class="mt-3 text-center">
        <a href="/BIS/views/register.php" class="text-decoration-none">Start over</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
