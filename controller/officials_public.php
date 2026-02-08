<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Official.php';

$model = new Official($conn);   // <- base sa model mo mysqli $conn
$officials = $model->all();     // reuse existing function

require_once __DIR__ . '/../views/resident/barangayofficial_list.php';
