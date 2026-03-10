<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit;
}

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) exit;

// Get search query
$search = trim($_GET['search'] ?? '');

// Initialize User model and get users
$userModel = new User($mysqli);
$users = $userModel->getAllUsers($search);

// Generate HTML for table rows
if (!$users) {
    echo '<tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>';
    return;
}

foreach ($users as $u) {
    $isActive = (($u['status'] ?? '') === 'active');
    $csrfToken = $_SESSION['csrf_token'] ?? '';
    ?>
    <tr>
        <td><?= (int)$u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['full_name']) ?></td>
        <td><span class="badge text-bg-primary"><?= htmlspecialchars($u['role']) ?></span></td>
        <td>
            <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                <?= htmlspecialchars($u['status']) ?>
            </span>
        </td>
        <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
        <td class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary"
                href="admin_usermanagement.php?search=<?= urlencode($search) ?>&edit=<?= (int)$u['id'] ?>">
                Edit
            </a>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <input type="hidden" name="next_status" value="<?= $isActive ? 'inactive' : 'active' ?>">
                <button class="btn btn-sm btn-outline-secondary">
                    <?= $isActive ? 'Deactivate' : 'Reactivate' ?>
                </button>
            </form>
        </td>
    </tr>
    <?php
}


