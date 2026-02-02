<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Clear remember-me cookie if exists
if (isset($_COOKIE['username'])) {
    // Attempt to clear cookie with same params as set earlier
    setcookie('username', '', time() - 3600, '/');
}

// Redirect back to login
header('Location: ../views/login.php');
exit();
