<?php
session_start();
$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id'])) {
    $_SESSION['error'] = 'Please register first.';
    header('Location: /BIS/views/register.php');
    exit;
}

$maskedEmail = preg_replace('/(^.).*(@.*$)/', '$1***$2', (string)($flow['email'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/login.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-primary text-center">Registration OTP</h3>
                    <p class="text-center text-muted">Ref: <b><?= htmlspecialchars($flow['ref_no'] ?? '') ?></b></p>
                    <p class="text-center text-muted">Sent to <?= htmlspecialchars($maskedEmail) ?></p>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/BIS/controller/register_verify_otp.php">
                        <div class="mb-3">
                            <label class="form-label" for="otp">OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" maxlength="6" pattern="\d{6}" required>
                        </div>
                        <button class="btn btn-primary w-100">Verify OTP</button>
                    </form>

                    <form method="POST" action="/BIS/controller/register_resend_otp.php" class="mt-2">
                        <button class="btn btn-outline-secondary w-100" type="submit">Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
