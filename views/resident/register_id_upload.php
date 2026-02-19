<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ResidentRegistration.php';

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) die("Database connection missing (\$db or \$conn).");

$registrationModel = new ResidentRegistration($mysqli);

/* =========================
   FLOW VALIDATION
========================= */

$flow = $_SESSION['register_flow'] ?? null;
$regId = (int)($flow['registration_id'] ?? 0);
$otpVerified = !empty($flow['otp_verified']);

if ($regId <= 0 || !$otpVerified) {
    $_SESSION['error'] = 'Please verify OTP first.';
    header("Location: /BIS/views/register_otp.php");
    exit;
}

$reg = $registrationModel->findById($regId);
if (!$reg) {
    $_SESSION['error'] = 'Registration not found.';
    header("Location: /BIS/views/register.php");
    exit;
}

$status = (string)($reg['status'] ?? '');

if ($status === 'pending_approval') {
    header("Location: /BIS/views/resident/register_success.php");
    exit;
}

if (!in_array($status, ['pending_id','id_required'], true)) {
    $_SESSION['error'] = 'You cannot access this step.';
    header("Location: /BIS/views/register.php");
    exit;
}

/* =========================
   CSRF
========================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

/* =========================
   HANDLE UPLOAD
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid request token.'];
        header("Location: register_id_upload.php");
        exit;
    }

    if (empty($_FILES['valid_id']['name'])) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Please choose a valid ID file.'];
        header("Location: register_id_upload.php");
        exit;
    }

    $file = $_FILES['valid_id'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Upload failed. Try again.'];
        header("Location: register_id_upload.php");
        exit;
    }

    $allowed = ['image/jpeg','image/png','application/pdf'];
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($mime, $allowed, true)) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Only JPG, PNG, or PDF allowed.'];
        header("Location: register_id_upload.php");
        exit;
    }

    $uploadsDir = __DIR__ . '/../../uploads/ids';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeName = 'reg_' . $regId . '_' . time() . '.' . $ext;
    $destPath = $uploadsDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Could not save file.'];
        header("Location: register_id_upload.php");
        exit;
    }

    $publicPath = '/BIS/uploads/ids/' . $safeName;

    $ok = $registrationModel->attachIdAndSetPending(
        $regId,
        $publicPath,
        (string)$file['name']
    );

    if (!$ok) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Failed to update registration.'];
        header("Location: register_id_upload.php");
        exit;
    }

    header("Location: register_success.php");
    exit;
}

$refNo = (string)($flow['ref_no'] ?? ($reg['ref_no'] ?? ''));
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Upload Valid ID</title>

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
        <span class="fw-bold text-dark">Upload ID</span>
        <span>Approval</span>
      </div>
      <div class="progress" style="height:8px;">
        <div class="progress-bar" style="width:75%"></div>
      </div>
    </div>
  </div>

  <div class="card app-card shadow-lg border-0">
    <div class="card-body p-4">

      <h4 class="mb-2">Upload Valid ID</h4>

      <?php if ($refNo): ?>
        <div class="alert alert-info">
          <strong>Reference No:</strong> <?= htmlspecialchars($refNo) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
          <label class="form-label">Valid ID (JPG / PNG / PDF)</label>
          <input type="file" name="valid_id" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
        </div>

        <button class="btn btn-primary w-100">Submit ID</button>
      </form>

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
