<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';

$otp = trim($_POST['otp'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

if ($otp === '' || $password === '' || $confirm === '') {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}
if (!preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['error'] = 'OTP must be 6 digits.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}
if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}

$otpHash = hash('sha256', $otp);

$resetModel = new PasswordReset($conn);
$row = $resetModel->findValidByOtpHash($otpHash);

if (! $row) {
    // If you want stricter rate limiting, you can track attempts by a separate lookup,
    // but this is already thesis-acceptable with otp_attempts.
    $_SESSION['error'] = 'Invalid or expired OTP.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}

$userModel = new User($conn);

$conn->begin_transaction();
try {
    if (!$userModel->updatePassword((int)$row['user_id'], $password)) {
        throw new Exception('Failed to update password.');
    }
    if (! $resetModel->markOtpUsed((int)$row['id'])) {
        throw new Exception('Failed to finalize OTP reset.');
    }
    $conn->commit();

    $_SESSION['success'] = 'Password reset successful via OTP.';
    header('Location: /BIS/views/login.php');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: /BIS/views/reset_password.php');
    exit;
}
