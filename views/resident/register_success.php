<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$flow = $_SESSION['register_flow'] ?? null;
$refNo = (string)($flow['ref_no'] ?? '');

// Optional: clear flow after success
unset($_SESSION['register_flow']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registration Submitted</title>

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
        <span>OTP</span>
        <span>Upload ID</span>
        <span class="fw-bold text-dark">Admin Approval</span>
      </div>
      <div class="progress" style="height:8px;">
        <div class="progress-bar bg-success" style="width:100%"></div>
      </div>
    </div>
  </div>

  <!-- Success Card -->
  <div class="card app-card shadow-lg border-0">
    <div class="card-body text-center p-5">

      <div class="mb-3">
        <div style="font-size:50px;"></div>
        <h4 class="mt-2">Registration Submitted Successfully</h4>
      </div>

      <p class="text-muted mb-4">
        Your registration is now <strong>Pending Admin Approval</strong>.<br>
        Please wait for confirmation from Barangay Don Galo.
      </p>

      <?php if (!empty($refNo)): ?>
        <div class="alert alert-info">
          <div class="fw-semibold">Reference Number</div>
          <div style="font-size:20px; font-weight:700;">
            <?= htmlspecialchars($refNo) ?>
          </div>
          <div class="small text-muted mt-1">
            Please save this reference number for follow-up.
          </div>
        </div>
      <?php endif; ?>

      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
        <a class="btn btn-primary px-4" href="/BIS/views/login.php">
          Go to Login
        </a>
        <a class="btn btn-outline-light px-4" href="/BIS/views/register.php">
          Register Another
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
