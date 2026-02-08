<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'resident') {
    header("Location: /BIS/views/login.php");
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

    <?php require_once __DIR__ . '/navbaruser_side.php';?>
    <!-- Top Navigation Bar -->
    <?php include 'navbar_top.php'; ?>

    




    
</body>
</html>

