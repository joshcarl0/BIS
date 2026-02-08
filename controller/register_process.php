<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';


/* ===========================
   REQUEST CHECK
=========================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/register.php");
    exit;
}

/* ===========================
   CSRF VALIDATION
=========================== */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header("Location: /BIS/views/register.php");
    exit;
}

/* ===========================
   INPUT SANITIZATION
=========================== */
$fullname  = trim($_POST['fullname'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
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

/* ===========================
   PASSWORD RULES
=========================== */
if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[!@#$%^&*]/', $password)
) {
    $_SESSION['error'] =
        'Password must be 8+ chars, with uppercase, number, and special character';
    header("Location: /BIS/views/register.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match';
    header("Location: /BIS/views/register.php");
    exit;
}

/* ===========================
   USER MODEL
=========================== */
$userModel = new User($conn);

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

/* ===========================
   REGISTER USER
=========================== */
// DEFAULT VALUES
$role_id = 3;   // resident
$status  = 'active';

$success = $userModel->register(
    $username,
    $email,
    $password,
    $fullname,
    $role_id,
    $status
);


if ($success) {
    unset($_SESSION['csrf_token']); // regenerate later
    $_SESSION['success'] = 'Registration successful! You can now log in.';
    header("Location: /BIS/views/login.php");
    exit;
}

$_SESSION['error'] = 'Registration failed. Please try again.';
header("Location: /BIS/views/register.php");
exit;
