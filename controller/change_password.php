<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

/* RESIDENT GUARD */
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    header("Location: /BIS/views/login.php");
    exit;
}

/* CSRF (recommended) */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request. Please try again.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

/* Inputs */
$current = (string)($_POST['current_password'] ?? '');
$new     = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');

if ($current === '' || $new === '' || $confirm === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'All fields are required.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

if ($new !== $confirm) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'New passwords do not match.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

/* Password policy (same as register) */
if (
    strlen($new) < 8 ||
    !preg_match('/[A-Z]/', $new) ||
    !preg_match('/[0-9]/', $new) ||
    !preg_match('/[!@#$%^&*]/', $new)
) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'msg'  => 'Password must be 8+ chars, with uppercase, number, and special character (!@#$%^&*).'
    ];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

/* DB handle: support $db or $conn */
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Database connection not found.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

$userModel = new User($mysqli);
$userId = (int)($_SESSION['user_id'] ?? 0);

/* Verify current password */
if (!$userModel->verifyCurrentPassword($userId, $current)) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Current password is incorrect.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}

/* Update password */
try {
    $ok = $userModel->updatePassword($userId, $new);

    if (!$ok) {
        throw new Exception('Failed to update password.');
    }

    unset($_SESSION['csrf_token']); // Invalidate token after successful use (optional but recommended)

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password updated successfully.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;

} catch (Throwable $e) {
    $dbErr = ($mysqli instanceof mysqli) ? $mysqli->error : '';
    error_log("change_password error: " . $e->getMessage() . " | DB: " . $dbErr);

    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Password change failed. Please try again.'];
    header("Location: /BIS/views/resident/manage_account.php");
    exit;
}