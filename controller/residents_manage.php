<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Resident.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$residentModel = new Resident($conn);

// FLASH
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$errors = [];

/* =========================
   LOOKUPS (for dropdowns)
========================= */
function fetchLookup(mysqli $conn, string $table, string $idCol = 'id', string $nameCol = 'name'): array
{
    // very simple safe lookup (table names are hardcoded below)
    $sql = "SELECT {$idCol} AS id, {$nameCol} AS name FROM {$table} ORDER BY {$nameCol} ASC";
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

// IMPORTANT: change table names here if yours are different
$civil_statuses  = fetchLookup($conn, 'civil_statuses', 'id', 'name');
$puroks          = fetchLookup($conn, 'puroks', 'id', 'name');
$residency_types = fetchLookup($conn, 'residency_types', 'id', 'name');


/* =========================
   ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request (CSRF).'];
        header("Location: /BIS/controller/residents_manage.php");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // normalize checkbox + numeric inputs
    $_POST['is_head_of_household'] = !empty($_POST['is_head_of_household']) ? 1 : 0;
    $_POST['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    $_POST['purok_id'] = isset($_POST['purok_id']) && $_POST['purok_id'] !== '' ? (int)$_POST['purok_id'] : 0;
    $_POST['residency_type_id'] = isset($_POST['residency_type_id']) && $_POST['residency_type_id'] !== '' ? (int)$_POST['residency_type_id'] : 0;
    $_POST['civil_status_id'] = isset($_POST['civil_status_id']) && $_POST['civil_status_id'] !== '' ? (int)$_POST['civil_status_id'] : 0;

    $_POST['household_id'] = isset($_POST['household_id']) && $_POST['household_id'] !== ''
        ? (int)$_POST['household_id']
        : null;

    // ADD
    if ($action === 'add') {

        // Required checks (based on your DB NOT NULL)
        if (trim($_POST['last_name'] ?? '') === '' || trim($_POST['first_name'] ?? '') === '' || trim($_POST['birthdate'] ?? '') === '') {
            $errors[] = "Last name, first name, and birthdate are required.";
        }
        if (($_POST['purok_id'] ?? 0) <= 0) {
            $errors[] = "Purok is required.";
        }
        if (($_POST['residency_type_id'] ?? 0) <= 0) {
            $errors[] = "Residency type is required.";
        }
        if (($_POST['civil_status_id'] ?? 0) <= 0) {
            $errors[] = "Civil status is required.";
        }

        if (!$errors) {
            // NOTE: new Resident model create() should match your DB columns
            $ok = $residentModel->create($_POST);

            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'Resident added successfully.']
                : ['type' => 'danger', 'msg' => 'Failed to add resident.'];

            header("Location: /BIS/controller/residents_manage.php");
            exit;
        }
    }

    // EDIT
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $errors[] = "Invalid resident ID.";

        if (trim($_POST['last_name'] ?? '') === '' || trim($_POST['first_name'] ?? '') === '' || trim($_POST['birthdate'] ?? '') === '') {
            $errors[] = "Last name, first name, and birthdate are required.";
        }
        if (($_POST['purok_id'] ?? 0) <= 0) {
            $errors[] = "Purok is required.";
        }
        if (($_POST['residency_type_id'] ?? 0) <= 0) {
            $errors[] = "Residency type is required.";
        }
        if (($_POST['civil_status_id'] ?? 0) <= 0) {
            $errors[] = "Civil status is required.";
        }

        if (!$errors) {
            $ok = $residentModel->update($id, $_POST);

            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'Resident updated successfully.']
                : ['type' => 'danger', 'msg' => 'Failed to update resident.'];

            header("Location: /BIS/controller/residents_manage.php");
            exit;
        }
    }

    // DEACTIVATE
    if ($action === 'deactivate') {
        $id = (int)($_POST['id'] ?? 0);

        $ok = ($id > 0) ? $residentModel->deactivate($id) : false;

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Resident deactivated.']
            : ['type' => 'danger', 'msg' => 'Failed to deactivate resident.'];

        header("Location: /BIS/controller/residents_manage.php");
        exit;
    }
}

/* =========================
   LIST
========================= */
$q = trim($_GET['q'] ?? '');
$page = (int)($_GET['page'] ?? 1);

$data = [];
$data['q'] = $q;
$data['list'] = $residentModel->getPaginated($q, $page, 10);

// pass lookups to view
$data['civil_statuses'] = $civil_statuses;
$data['puroks'] = $puroks;
$data['residency_types'] = $residency_types;

// LOAD VIEW
require __DIR__ . '/../views/admin/residents_manage.php';
