<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// RESIDENT GUARD
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit;
}