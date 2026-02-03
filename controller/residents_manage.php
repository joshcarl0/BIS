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
   LOOKUPS
========================= */
function fetchLookup(mysqli $conn, string $table, string $idCol = 'id', string $nameCol = 'name'): array
{
    $sql = "SELECT {$idCol} AS id, {$nameCol} AS name FROM {$table} ORDER BY {$nameCol} ASC";
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

$civil_statuses  = fetchLookup($conn, 'civil_statuses', 'id', 'name');
$puroks          = fetchLookup($conn, 'puroks', 'id', 'name');
$residency_types = fetchLookup($conn, 'residency_types', 'id', 'name');

// ✅ Special groups list
$special_groups = $residentModel->getAllSpecialGroups();

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

    // normalize
    $_POST['is_head_of_household'] = !empty($_POST['is_head_of_household']) ? 1 : 0;
    $_POST['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    $_POST['voter_status'] = !empty($_POST['voter_status']) ? 1 : 0;

    $_POST['purok_id'] = ($_POST['purok_id'] ?? '') !== '' ? (int)$_POST['purok_id'] : 0;
    $_POST['residency_type_id'] = ($_POST['residency_type_id'] ?? '') !== '' ? (int)$_POST['residency_type_id'] : 0;
    $_POST['civil_status_id'] = ($_POST['civil_status_id'] ?? '') !== '' ? (int)$_POST['civil_status_id'] : 0;

    $_POST['household_id'] = ($_POST['household_id'] ?? '') !== '' ? (int)$_POST['household_id'] : null;

    $selectedGroups = $_POST['special_groups'] ?? [];
    if (!is_array($selectedGroups)) $selectedGroups = [];

    // ADD
    if ($action === 'add') {

        if (trim($_POST['last_name'] ?? '') === '' || trim($_POST['first_name'] ?? '') === '' || trim($_POST['birthdate'] ?? '') === '') {
            $errors[] = "Last name, first name, and birthdate are required.";
        }
        if (($_POST['purok_id'] ?? 0) <= 0) $errors[] = "Purok is required.";
        if (($_POST['residency_type_id'] ?? 0) <= 0) $errors[] = "Residency type is required.";
        if (($_POST['civil_status_id'] ?? 0) <= 0) $errors[] = "Civil status is required.";

        if (!$errors) {
            $newId = $residentModel->createReturnId($_POST);

            if ($newId !== false && $newId > 0) {
                // ✅ save special groups
                $residentModel->updateSpecialGroups((int)$newId, $selectedGroups);

                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Resident added successfully.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to add resident.'];
            }

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
        if (($_POST['purok_id'] ?? 0) <= 0) $errors[] = "Purok is required.";
        if (($_POST['residency_type_id'] ?? 0) <= 0) $errors[] = "Residency type is required.";
        if (($_POST['civil_status_id'] ?? 0) <= 0) $errors[] = "Civil status is required.";

        if (!$errors) {
            $ok = $residentModel->update($id, $_POST);

            if ($ok) {
                // ✅ update special groups
                $residentModel->updateSpecialGroups($id, $selectedGroups);

                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Resident updated successfully.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to update resident.'];
            }

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

// ✅ list with group names (optional display)
$list = $residentModel->getPaginatedWithGroups($q, $page, 10);

// ✅ attach group ids CSV per resident (for edit modal auto-check)
$ids = array_map(fn($row) => (int)$row['id'], $list['rows']);
$map = [];
if (!empty($ids)) {
    $in = implode(',', $ids);
    $sql = "SELECT resident_id, GROUP_CONCAT(group_id ORDER BY group_id SEPARATOR ',') AS ids
            FROM resident_special_groups
            WHERE resident_id IN ($in)
            GROUP BY resident_id";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $map[(int)$row['resident_id']] = $row['ids'] ?? '';
        }
    }
    foreach ($list['rows'] as &$row) {
        $rid = (int)$row['id'];
        $row['special_group_ids_csv'] = $map[$rid] ?? '';
    }
    unset($row);
}

$data = [];
$data['q'] = $q;
$data['list'] = $list;

// lookups
$data['civil_statuses'] = $civil_statuses;
$data['puroks'] = $puroks;
$data['residency_types'] = $residency_types;

// ✅ for checkboxes
$data['special_groups'] = $special_groups;

// LOAD VIEW
require __DIR__ . '/../views/admin/residents_manage.php';
