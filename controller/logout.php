<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ActivityLog.php';

// LOGOUT ACTIVITY
if (!empty($_SESSION['user_id'])) {

    $logModel = new ActivityLog($conn);

    $logModel->log(
        'logout',
        'User logged out',
        $_SESSION['user_id'],
        $_SESSION['role'],
        'user',
        $_SESSION['user_id']
    );
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember-me cookie if exists
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/');
}

// Redirect back to login
header('Location: ../views/login.php');
exit();