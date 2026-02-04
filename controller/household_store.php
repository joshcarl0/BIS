<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';

/* =========================
   ADMIN GUARD
========================= */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

/* =========================
   POST ONLY
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/admin/household_add.php");
    exit();
}

/* =========================
   CSRF CHECK
========================= */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error'] = "Invalid request (CSRF). Please try again.";
    header("Location: /BIS/views/admin/household_add.php");
    exit();
}

/* =========================
   REQUIRED: PUROK VALIDATION
========================= */
$purok_id = (int)($_POST['purok_id'] ?? 0);
if ($purok_id <= 0) {
    $_SESSION['error'] = "Please select a valid Purok.";
    $_SESSION['old'] = $_POST;
    header("Location: /BIS/views/admin/household_add.php");
    exit();
}

// Ensure purok exists (prevents FK error)
$chk = $conn->prepare("SELECT id FROM puroks WHERE id = ? LIMIT 1");
if (!$chk) {
    $_SESSION['error'] = "Database error. (puroks check failed)";
    $_SESSION['old'] = $_POST;
    header("Location: /BIS/views/admin/household_add.php");
    exit();
}
$chk->bind_param("i", $purok_id);
$chk->execute();
$res = $chk->get_result();
$exists = $res && $res->num_rows > 0;
$chk->close();

if (!$exists) {
    $_SESSION['error'] = "Selected Purok does not exist.";
    $_SESSION['old'] = $_POST;
    header("Location: /BIS/views/admin/household_add.php");
    exit();
}

$_POST['purok_id'] = $purok_id;

/* =========================
   OPTIONAL: head_resident_id normalize
========================= */
$head_id = (int)($_POST['head_resident_id'] ?? 0);
$_POST['head_resident_id'] = ($head_id > 0) ? $head_id : null;

/* =========================
   NORMALIZE CHECKBOXES (0/1)
========================= */
$checkboxes = [
    'is_4ps_beneficiary',
    'is_social_pension',
    'is_tupad_beneficiary',
    'is_akap_beneficiary',
    'is_solo_parent_assistance',
    'has_vehicle',
    'has_motorcycle',
    'has_refrigerator',
    'has_tv',
    'has_washing_machine',
    'has_aircon',
    'has_computer',
    'has_smartphone',
];
foreach ($checkboxes as $cb) {
    $_POST[$cb] = isset($_POST[$cb]) ? 1 : 0;
}

/* =========================
   CREATED BY
========================= */
$_POST['created_by'] = (int)$_SESSION['user_id'];

/* =========================
   CREATE
========================= */
$householdModel = new Household($conn);

// IMPORTANT: always generate code on save (avoid duplicate / null)
$_POST['household_code'] = $householdModel->generateHouseholdCode();

$ok = $householdModel->create($_POST);

if ($ok) {
    $_SESSION['success'] = "Household added successfully: " . $_POST['household_code'];
    header("Location: /BIS/controller/households_manage.php");
    exit();
}

$_SESSION['error'] = "Failed to add household.";
$_SESSION['old'] = $_POST;
header("Location: /BIS/views/admin/household_add.php");
exit();
