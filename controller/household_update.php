<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: /BIS/views/login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

if (
  empty($_POST['csrf_token']) ||
  empty($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  $_SESSION['error'] = "Invalid request (CSRF).";
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['error'] = "Invalid household id.";
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

$purok_id = (int)($_POST['purok_id'] ?? 0);
if ($purok_id <= 0) {
  $_SESSION['error'] = "Please select a valid Purok.";
  header("Location: /BIS/views/admin/household_edit.php?id=" . $id); exit();
}

// optional head
$head_id = (int)($_POST['head_resident_id'] ?? 0);
$_POST['head_resident_id'] = $head_id > 0 ? $head_id : null;

// required address
$addr = trim($_POST['address_line'] ?? '');
if ($addr === '') {
  $_SESSION['error'] = "Address is required.";
  header("Location: /BIS/views/admin/household_edit.php?id=" . $id); exit();
}
$_POST['address_line'] = $addr;

$householdModel = new Household($conn);
$ok = $householdModel->update($id, $_POST);

if ($ok) {
  $_SESSION['success'] = "Household updated successfully.";
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

$_SESSION['error'] = "Failed to update household.";
header("Location: /BIS/views/admin/household_edit.php?id=" . $id); exit();
