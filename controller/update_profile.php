<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
  header("Location: /BIS/views/login.php");
  exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$mysqli = $conn ?? $db ?? null;
if (!$mysqli) die("Database connection not found.");

$userModel = new User($mysqli);
$userId = (int)$_SESSION['user_id'];

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$username  = trim($_POST['username'] ?? '');

if ($full_name === '' || $email === '' || $username === '') {
  $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'All fields are required.'];
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

$ok = $userModel->updateProfile($userId, $username, $email, $full_name);

if ($ok) {
  $_SESSION['full_name'] = $full_name;
  $_SESSION['username']  = $username;
  $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profile updated successfully.'];
} else {
  $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Update failed. Username/Email may already be taken.'];
}

header("Location: /BIS/views/resident/resident_profile.php");
exit;