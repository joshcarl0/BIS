<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Resident.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit;
}

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) exit;

// Get search query and status filter
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'all';
$status = in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'all';

// Initialize Resident model
$residentModel = new Resident($mysqli);

// Get search results (page 1, 100 items to get all matches)
$results = $residentModel->getPaginatedWithGroups($search, 1, 100, $status);

// Get lookup maps
$civil_statuses = [];
$res = $mysqli->query("SELECT id, name FROM civil_statuses ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $civil_statuses[(int)$row['id']] = $row['name'];
    }
}

$puroks = [];
$res = $mysqli->query("SELECT id, name FROM puroks ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $puroks[(int)$row['id']] = $row['name'];
    }
}

$residency_types = [];
$res = $mysqli->query("SELECT id, name FROM residency_types ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $residency_types[(int)$row['id']] = $row['name'];
    }
}

// Get special group IDs for each resident
$residentIds = [];

foreach ($results['rows'] as $row) {
    $residentIds[] = (int)$row['id'];
}

$specialGroupsMap = [];

if (!empty($residentIds)) {
    $in = implode(',', $residentIds);
    $sql = "SELECT resident_id, GROUP_CONCAT(group_id ORDER BY group_id SEPARATOR ',') AS ids
            FROM resident_special_groups
            WHERE resident_id IN ($in)
            GROUP BY resident_id";
    $res = $mysqli->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $specialGroupsMap[(int)$row['resident_id']] = $row['ids'] ?? '';
        }
    }
}

// Generate HTML for table rows
if (empty($results['rows'])) {
    echo '<tr><td colspan="10" class="text-center text-muted py-4">No residents found.</td></tr>';
    return;
}

foreach ($results['rows'] as $r) {
    $fullname = trim(
        ($r['last_name'] ?? '') . ', ' .
        ($r['first_name'] ?? '') . ' ' .
        ($r['middle_name'] ?? '') . ' ' .
        ($r['suffix'] ?? '')
    );

    $isActive = (int)($r['is_active'] ?? 0) === 1;

    $purokName = $puroks[(int)($r['purok_id'] ?? 0)] ?? '';
    $resTypeName = $residency_types[(int)($r['residency_type_id'] ?? 0)] ?? '';
    $groupsText = $r['special_groups'] ?? '';
    $groupsCsv = $specialGroupsMap[(int)$r['id']] ?? '';
    $csrfToken = $_SESSION['csrf_token'] ?? '';
    ?>
    <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($fullname) ?></td>
        <td><?= htmlspecialchars($r['sex'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['birthdate'] ?? '') ?></td>
        <td><?= htmlspecialchars($purokName) ?></td>
        <td><?= htmlspecialchars($resTypeName) ?></td>
        <td><?= htmlspecialchars($r['household_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($groupsText) ?></td>
        <td>
            <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                <?= $isActive ? 'Active' : 'Inactive' ?>
            </span>
        </td>
        <td class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary btn-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#editResidentModal"
                    data-groups="<?= htmlspecialchars($groupsCsv) ?>"
                    data-resident='<?= htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8") ?>'>
                <i class="bi bi-pencil"></i> Edit
            </button>

            <?php if ($isActive): ?>
                <form method="POST"
                      action="/BIS/controller/residents_manage.php"
                      onsubmit="return confirm('Deactivate this resident?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-person-x"></i> Deactivate
                    </button>
                </form>
            <?php else: ?>
                <form method="POST"
                      action="/BIS/controller/residents_manage.php"
                      onsubmit="return confirm('Activate this resident?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-success">
                        <i class="bi bi-person-check"></i> Activate
                    </button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

