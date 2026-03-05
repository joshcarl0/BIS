<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
  header("Location: /BIS/views/login.php");
  exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

$mysqli = $conn ?? $db ?? null;
if (!$mysqli) die("Database connection not found.");

$userModel = new User($mysqli);
$userId = (int)$_SESSION['user_id'];

$current = (string)($_POST['current_password'] ?? '');
$new     = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');

if ($new !== $confirm) {
  $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'New passwords do not match.'];
  header("Location: /BIS/views/resident/manage_account.php");
  exit;
}
if (strlen($new) < 8) {
  $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Password must be at least 8 characters.'];
  header("Location: /BIS/views/resident/manage_account.php");
  exit;
}

if (!$userModel->verifyCurrentPassword($userId, $current)) {
  $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Current password is incorrect.'];
  header("Location: /BIS/views/resident/manage_account.php");
  exit;
}

$ok = $userModel->updatePassword($userId, $new);

$_SESSION['flash'] = $ok
  ? ['type' => 'success', 'msg' => 'Password updated successfully.']
  : ['type' => 'danger', 'msg' => 'Failed to update password.'];

header("Location: /BIS/views/resident/manage_account.php");
exit;