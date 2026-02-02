<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // Redirect to the login view (not the controller). The controller expects POST
    // and will redirect again; sending users straight to the login page avoids
    // unnecessary internal redirects and clearer URLs.
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
</head>
<body>
    <!-- Sidebar Navigation -->
    <h1>Welcome to the User Dashboard</h1>

    <?php require_once __DIR__ . '/BIS/views/navbaruser_side.php'; ?>
    <!-- Top Navigation Bar -->
    <?php include 'navbar_top.php'; ?>

    




    
</body>
</html>

