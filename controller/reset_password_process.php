<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (empty($_SESSION['reset_user_id']) || empty($_SESSION['reset_otp_hash'])) {
    header('Location: /BIS/views/forgot_password.php');
    exit;
}

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

// check expiry
if (time() > (int)$_SESSION['reset_otp_expires']) {
    $_SESSION['error'] = 'OTP expired. Please request again.';
    // clear session reset
    unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_attempts']);
    header('Location: /BIS/views/forgot_password.php');
    exit;
}

// attempts
$_SESSION['reset_otp_attempts'] = ($_SESSION['reset_otp_attempts'] ?? 0) + 1;
if ($_SESSION['reset_otp_attempts'] > 5) {
    $_SESSION['error'] = 'Too many attempts. Please request again.';
    unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_attempts']);
    header('Location: /BIS/views/forgot_password.php');
    exit;
}

// verify OTP hash
if (hash('sha256', $otp) !== $_SESSION['reset_otp_hash']) {
    $_SESSION['error'] = 'Invalid OTP.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}

// update password
$userModel = new User($conn);
$ok = $userModel->updatePassword((int)$_SESSION['reset_user_id'], $password);

if (! $ok) {
    $_SESSION['error'] = 'Failed to reset password.';
    header('Location: /BIS/views/reset_password.php');
    exit;
}

// clear session reset
unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_attempts']);

$_SESSION['success'] = 'Password reset successful. You can now log in.';
header('Location: /BIS/views/login.php');
exit;
