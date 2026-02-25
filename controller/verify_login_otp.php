<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';


function applyNewUserCookieAndFlag(mysqli $conn, int $userId, int $isFirstLogin): void
{
    if ($isFirstLogin !== 1) {
        return;
    }

    setcookie('bis_new_user', '1', [
        'expires' => time() + (7 * 24 * 60 * 60),
        'path' => '/BIS',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $userModel = new User($conn);
    $userModel->markFirstLoginComplete($userId);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BIS/views/login_otp.php');
    exit();
}

$pending = $_SESSION['pending_login'] ?? null;
if (!$pending) {
    $_SESSION['error'] = 'Your login session expired. Please sign in again.';
    header('Location: /BIS/views/login.php');
    exit();
}

$otp = trim($_POST['otp'] ?? '');
if (!preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['error'] = 'OTP must be 6 digits.';
    header('Location: /BIS/views/login_otp.php');
    exit();
}

if (time() > (int) ($pending['otp_expires'] ?? 0)) {
    unset($_SESSION['pending_login']);
    $_SESSION['error'] = 'OTP expired. Please sign in again.';
    header('Location: /BIS/views/login.php');
    exit();
}

$attempts = (int) ($pending['otp_attempts'] ?? 0);
if ($attempts >= 5) {
    unset($_SESSION['pending_login']);
    $_SESSION['error'] = 'Too many incorrect OTP attempts. Please sign in again.';
    header('Location: /BIS/views/login.php');
    exit();
}

if (!hash_equals((string) ($pending['otp_hash'] ?? ''), hash('sha256', $otp))) {
    $_SESSION['pending_login']['otp_attempts'] = $attempts + 1;
    $_SESSION['error'] = 'Invalid OTP.';
    header('Location: /BIS/views/login_otp.php');
    exit();
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int) $pending['user_id'];
$_SESSION['username'] = (string) $pending['username'];
$_SESSION['role'] = (string) $pending['role'];
$_SESSION['full_name'] = (string) $pending['full_name'];

unset($_SESSION['pending_login'], $_SESSION['error']);

applyNewUserCookieAndFlag($conn, (int) $pending['user_id'], (int) ($pending['is_first_login'] ?? 0));

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
