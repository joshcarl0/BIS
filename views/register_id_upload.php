<?php
session_start();
$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id']) || empty($flow['otp_verified'])) {
    $_SESSION['error'] = 'Please verify OTP first.';
    header('Location: /BIS/views/register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Valid ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-primary text-center mb-1">Upload Valid ID</h3>
                    <p class="text-center text-muted">Reference: <b><?= htmlspecialchars($flow['ref_no'] ?? '') ?></b></p>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/BIS/controller/register_upload_id.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="valid_id" class="form-label">Valid ID (JPG, PNG, PDF up to 5MB)</label>
                            <input class="form-control" type="file" name="valid_id" id="valid_id" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Submit Registration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
