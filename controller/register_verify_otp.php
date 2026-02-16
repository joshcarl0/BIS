<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found. Check database.php variable name (\$db or \$conn).");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BIS/views/register_otp.php');
    exit;
}

$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id'])) {
    $_SESSION['error'] = 'Registration session expired. Please register again.';
    header('Location: /BIS/views/register.php');
    exit;
}

$otp = trim($_POST['otp'] ?? '');
if (!preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['error'] = 'OTP must be 6 digits.';
    header('Location: /BIS/views/register_otp.php');
    exit;
}

$registrationModel = new ResidentRegistration($mysqli);

$row = $registrationModel->findById((int)$flow['registration_id']);
if (!$row || ($row['status'] ?? '') !== 'pending_otp') {
    $_SESSION['error'] = 'Registration OTP is no longer valid.';
    header('Location: /BIS/views/register.php');
    exit;
}

if ((int)($row['otp_attempts'] ?? 0) >= 5) {
    $_SESSION['error'] = 'Too many OTP attempts. Please restart registration.';
    header('Location: /BIS/views/register.php');
    exit;
}

if (!empty($row['otp_expires_at']) && strtotime((string)$row['otp_expires_at']) < time()) {
    $_SESSION['error'] = 'OTP expired. Please resend OTP.';
    header('Location: /BIS/views/register_otp.php');
    exit;
}

if (!hash_equals((string)$row['otp_hash'], hash('sha256', $otp))) {
    $registrationModel->incrementOtpAttempts((int)$row['id']);
    $_SESSION['error'] = 'Invalid OTP.';
    header('Location: /BIS/views/register_otp.php');
    exit;
}

//  OTP verified → move to pending_id (needs ID upload)
$ok = $registrationModel->verifyOtpAndMoveToPendingId((int)$row['id']);
if (!$ok) {
    $_SESSION['error'] = 'Unable to verify OTP. Please retry.';
    header('Location: /BIS/views/register_otp.php');
    exit;
}

//  store for guarding + reference number for success
$_SESSION['register_flow']['otp_verified'] = true;
$_SESSION['register_flow']['registration_id'] = (int)$row['id'];
$_SESSION['register_flow']['ref_no'] = (string)($row['ref_no'] ?? '');

$_SESSION['success'] = 'OTP verified. Upload your Valid ID to continue.';
header('Location: /BIS/views/resident/register_id_upload.php');
exit;
