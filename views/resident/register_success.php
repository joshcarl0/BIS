<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$flow = $_SESSION['register_flow'] ?? null;
$refNo = (string)($flow['ref_no'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registration Submitted</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 760px;">

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between small text-muted mb-2">
        <span>Step 1: Register</span>
        <span>Step 2: OTP</span>
        <span>Step 3: Upload ID</span>
        <span class="fw-semibold text-dark">Step 4: Admin Approval</span>
      </div>
      <div class="progress" style="height: 10px;">
        <div class="progress-bar" style="width: 100%;"></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body text-center p-4">
      <h4 class="mb-2">Submitted Successfully ✅</h4>
      <p class="text-muted mb-3">
        Your registration is now <b>Pending Admin Approval</b>.
      </p>

      <?php if (!empty($refNo)): ?>
        <div class="alert alert-info">
          <div class="fw-semibold">Reference Number</div>
          <div style="font-size:18px; font-weight:700;">
            <?= htmlspecialchars($refNo) ?>
          </div>
          <div class="small text-muted mt-1">Save this reference number for follow-up.</div>
        </div>
      <?php endif; ?>

      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
        <a class="btn btn-primary" href="/BIS/views/login.php">Go to Login</a>
        <a class="btn btn-outline-secondary" href="/BIS/views/register.php">Register Another</a>
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
