<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/register.php");
    exit;
}

/* CSRF */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header("Location: /BIS/views/register.php");
    exit;
}

/* Inputs */
$fullname  = trim($_POST['fullname'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
$contact   = trim($_POST['contact_number'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

if (!$fullname || !$username || !$email || !$password || !$confirm) {
    $_SESSION['error'] = 'All fields are required';
    header("Location: /BIS/views/register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format';
    header("Location: /BIS/views/register.php");
    exit;
}

if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[!@#$%^&*]/', $password)
) {
    $_SESSION['error'] = 'Password must be 8+ chars, with uppercase, number, and special character';
    header("Location: /BIS/views/register.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match';
    header("Location: /BIS/views/register.php");
    exit;
}

/* DB handle: support $db or $conn */
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    $_SESSION['error'] = 'Database connection not found.';
    header("Location: /BIS/views/register.php");
    exit;
}

/* Validate against existing users */
$userModel = new User($mysqli);

if ($userModel->isUsernameTaken($username)) {
    $_SESSION['error'] = 'Username is already taken';
    header("Location: /BIS/views/register.php");
    exit;
}

if ($userModel->isEmailTaken($email)) {
    $_SESSION['error'] = 'Email is already registered';
    header("Location: /BIS/views/register.php");
    exit;
}

/* Create pending registration (NOT user yet) */
$regModel = new ResidentRegistration($mysqli);

try {
    $result = $regModel->createPendingRegistration([
        'full_name'       => $fullname,
        'username'        => $username,
        'password'        => $password, // model should hash
        'email'           => $email,
        'contact_number'  => $contact ?: null,
    ]);

    // expected return: ['id' => ..., 'ref_no' => ..., 'otp' => ...]
    $regId  = (int)($result['id'] ?? 0);
    $refNo  = (string)($result['ref_no'] ?? '');
    $otp    = (string)($result['otp'] ?? '');

    if ($regId <= 0 || $refNo === '' || $otp === '') {
        throw new Exception('Registration init failed.');
    }

    // IMPORTANT: match your OTP controllers
    $_SESSION['register_flow'] = [
        'registration_id' => $regId,
        'ref_no'          => $refNo,
        'email'           => $email,
        'otp_verified'    => false,
    ];

    unset($_SESSION['csrf_token']); // regenerate later

    // Send OTP email
    if (function_exists('sendOtpMail')) {
        sendOtpMail($email, $otp, $refNo);
    }

    header("Location: /BIS/views/register_otp.php");
    exit;

} catch (Throwable $e) {
    $dbErr = ($mysqli instanceof mysqli) ? $mysqli->error : '';
    error_log("register_process error: " . $e->getMessage() . " | DB: " . $dbErr);

    // DEV: show exact message
    $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
    if ($dbErr) $_SESSION['error'] .= ' | DB: ' . $dbErr;

    header("Location: /BIS/views/register.php");
    exit;
}

