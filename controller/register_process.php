<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BIS/views/register.php');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: /BIS/views/register.php');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$fullName || !$username || !$email || !$password || !$confirm) {
    $_SESSION['error'] = 'All required fields must be filled in.';
    header('Location: /BIS/views/register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'A valid email is required.';
    header('Location: /BIS/views/register.php');
    exit;
}

if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[!@#$%^&*]/', $password)
) {
    $_SESSION['error'] = 'Password must be 8+ chars, with uppercase, number, and special character';
    header('Location: /BIS/views/register.php');
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match';
    header('Location: /BIS/views/register.php');
    exit;
}

$userModel = new User($conn);
$registrationModel = new ResidentRegistration($conn);

if ($userModel->isUsernameTaken($username) || $registrationModel->hasUsernameOrEmail($username, $email)) {
    $_SESSION['error'] = 'Username is already in use.';
    header('Location: /BIS/views/register.php');
    exit;
}

if ($userModel->isEmailTaken($email) || $registrationModel->hasUsernameOrEmail($username, $email)) {
    $_SESSION['error'] = 'Email is already in use.';
    header('Location: /BIS/views/register.php');
    exit;
}

$otp = (string) random_int(100000, 999999);
$otpHash = hash('sha256', $otp);
$otpExpiresAt = date('Y-m-d H:i:s', time() + 600);
$refNo = $registrationModel->generateReferenceNumber();
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$registrationId = $registrationModel->createPendingOtp(
    $refNo,
    $fullName,
    $username,
    $email,
    $contactNumber,
    $passwordHash,
    $otpHash,
    $otpExpiresAt
);

if (!$registrationId) {
    $_SESSION['error'] = 'Unable to start registration. Please try again.';
    header('Location: /BIS/views/register.php');
    exit;
}

try {
    $mail = getMailer();
    $mail->addAddress($email);
    $mail->Subject = 'Your Resident Registration OTP';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height:1.6'>
            <h2>Barangay Don Galo - Resident Registration</h2>
            <p>Your registration reference is <b>{$refNo}</b>.</p>
            <p>Enter this OTP to continue your resident registration:</p>
            <p style='font-size: 22px; letter-spacing: 3px;'><b>{$otp}</b></p>
            <p>This OTP will expire in 10 minutes.</p>
        </div>
    ";
    $mail->AltBody = "Reference: {$refNo}. OTP: {$otp}. Expires in 10 minutes.";
    $mail->send();

    $_SESSION['register_flow'] = [
        'registration_id' => (int) $registrationId,
        'ref_no' => $refNo,
        'email' => $email,
    ];

    $_SESSION['success'] = 'OTP sent to your email. Verify to continue registration.';
    header('Location: /BIS/views/register_otp.php');
    exit;
} catch (Exception $e) {
    error_log('Registration OTP mail error: ' . $e->getMessage());
    $_SESSION['error'] = 'Could not send OTP now. Please try again.';
    header('Location: /BIS/views/register.php');
    exit;
}
