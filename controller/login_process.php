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

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header("Location: /BIS/views/admin_dashboard.php");
    } else {
        header("Location: /BIS/views/user_dashboard.php");
    }
    exit();
}

$_SESSION['error'] = 'Invalid username or password';
header("Location: /BIS/views/login.php");
exit();
