<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

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

if ($user) {
    session_regenerate_id(true);

    unset($_SESSION['error']);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];

    switch ($user['role']) {
        case 'admin':
            header("Location: /BIS/views/admin_dashboard.php");
            break;

        case 'official':
            header("Location: /BIS/views/official_dashboard.php");
            break;

        default:
            header("Location: /BIS/views/user_dashboard.php");
            break;
    }
    exit();
}

$_SESSION['error'] = 'Invalid username or password';
header("Location: /BIS/views/login.php");
exit();
