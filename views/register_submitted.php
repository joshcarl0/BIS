<?php
session_start();
$submitted = $_SESSION['registration_submitted'] ?? null;
if (!$submitted) {
    $_SESSION['error'] = 'No submitted registration found.';
    header('Location: /BIS/views/register.php');
    exit;
}
unset($_SESSION['registration_submitted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Submitted</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h3 class="text-success">Registration submitted</h3>
                    <p class="mb-2">Reference Number:</p>
                    <h4 class="fw-bold"><?= htmlspecialchars($submitted['ref_no']) ?></h4>
                    <p class="mb-4">Status: <span class="badge bg-warning text-dark">Pending Approval</span></p>
                    <p class="text-muted">Please wait for admin review before you can log in as resident.</p>
                    <a href="/BIS/views/login.php" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
