<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

function finalizeLogin(array $user): void
{
    session_regenerate_id(true);

    unset($_SESSION['error']);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['role'] = (string) $user['role'];
    $_SESSION['full_name'] = (string) $user['full_name'];

    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: /BIS/views/admin_dashboard.php');
            break;
        case 'official':
            header('Location: /BIS/views/official_dashboard.php');
            break;
        default:
            header('Location: /BIS/views/user_dashboard.php');
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = 'Username and password required';
    header("Location: /BIS/views/login.php");
    exit();
}

$userModel = new User($conn);
$user = $userModel->login($username, $password);

if (!$user) {
    $registrationModel = new ResidentRegistration($conn);
    $registration = $registrationModel->findLatestByLoginIdentifier($username);

    if ($registration && in_array($registration['status'], ['pending_otp', 'pending_id', 'pending_approval'], true)) {
        $_SESSION['error'] = 'Your registration is pending approval. Ref: ' . $registration['ref_no'];
    } else {
        $_SESSION['error'] = 'Invalid username or password';
    }

    header("Location: /BIS/views/login.php");
    exit();
}

if (($user['role'] ?? '') === 'resident' && ($user['status'] ?? 'inactive') !== 'active') {
    $_SESSION['error'] = 'Resident account is not yet approved.';
    header('Location: /BIS/views/login.php');
    exit();
}

if (empty($user['email']) || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
    // Keep admin access available even if legacy admin accounts have no email.
    if (($user['role'] ?? '') === 'admin') {
        finalizeLogin($user);
    }

    $_SESSION['error'] = 'Your account does not have a valid email for OTP login.';
    header('Location: /BIS/views/login.php');
    exit();
}

$otp = (string) random_int(100000, 999999);

$_SESSION['pending_login'] = [
    'user_id' => (int) $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'otp_hash' => hash('sha256', $otp),
    'otp_expires' => time() + 300,
    'otp_attempts' => 0,
];

try {
    $mail = getMailer();
    $mail->addAddress($user['email']);
    $mail->Subject = 'Your Login OTP';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6'>
            <h2>Barangay Don Galo - Login Verification</h2>
            <p>Use this one-time password (OTP) to finish logging in:</p>
            <p style='font-size: 18px;'>
                <b>OTP:</b>
                <span style='font-size: 22px; letter-spacing: 3px;'>$otp</span>
            </p>
            <p>This OTP expires in <b>5 minutes</b>.</p>
            <p>If this wasn't you, please change your password immediately.</p>
        </div>
    ";
    $mail->AltBody = "Your login OTP is: $otp (expires in 5 minutes).";
    $mail->send();

    unset($_SESSION['error']);
    $_SESSION['success'] = 'OTP sent to your email. Please verify to continue.';
    header("Location: /BIS/views/login_otp.php");
    exit();
} catch (Exception $e) {
    error_log('Login OTP mail error: ' . $e->getMessage());

    // Do not lock out admins when SMTP has transient issues.
    if (($user['role'] ?? '') === 'admin') {
        unset($_SESSION['pending_login']);
        finalizeLogin($user);
    }

    unset($_SESSION['pending_login']);
    $_SESSION['error'] = 'Could not send OTP right now. Please try again.';
    header('Location: /BIS/views/login.php');
    exit();
}
