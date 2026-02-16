<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ResidentRegistration.php';
require_once __DIR__ . '/../../models/User.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /BIS/views/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$registrationModel = new ResidentRegistration($conn);
$userModel = new User($conn);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request token.'];
        header('Location: /BIS/controller/admin_resident_registrations.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['registration_id'] ?? 0);
    $notes = trim($_POST['admin_notes'] ?? '');

    if ($id <= 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid registration selected.'];
        header('Location: /BIS/controller/admin_resident_registrations.php');
        exit;
    }

    if ($action === 'approve') {
        $row = $registrationModel->findById($id);
        if (!$row || $row['status'] !== 'pending_approval') {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Only pending approvals can be approved.'];
            header('Location: /BIS/controller/admin_resident_registrations.php');
            exit;
        }

        $roleId = 3; // resident role_id


        $conn->begin_transaction();
        try {
            $newUserId = $userModel->createUserFromRegistration(
                (string)$row['username'],
                (string)$row['email'],
                (string)$row['password_hash'],
                (string)$row['full_name'],
                (int)$roleId,
                'active'
            );

            if (!$newUserId) {
                throw new Exception('Failed to create resident user. Username/email may already exist.');
            }

            if (!$registrationModel->approve($id, (int)$_SESSION['user_id'], (int)$newUserId, $notes ?: null)) {
                throw new Exception('Failed to mark registration approved.');
            }

            $conn->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Registration approved and resident account activated.'];
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        header('Location: /BIS/controller/admin_resident_registrations.php');
        exit;
    }

    if ($action === 'reject') {
        $ok = $registrationModel->reject($id, (int)$_SESSION['user_id'], $notes ?: null);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Registration rejected.']
            : ['type' => 'danger', 'msg' => 'Failed to reject registration.'];

        header('Location: /BIS/controller/admin_resident_registrations.php');
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$rows = $registrationModel->getPendingApprovalList($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Resident Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>
<body style="background:#D6D5D7;">
<?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>
<div class="main-content" id="mainContent">
    <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container-fluid mt-4">
        <h4 class="mb-3">Resident Registrations</h4>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

        <form class="row g-2 mb-3" method="GET">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" placeholder="Search by ref no, name, email" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>ID Attachment</th>
                        <th style="min-width: 260px;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-muted">No registrations found.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['ref_no']) ?></td>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td><?= htmlspecialchars($r['created_at']) ?></td>
                            <td>
                                <?php if (!empty($r['id_file_path'])): ?>
                                    <a href="<?= htmlspecialchars($r['id_file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info">View ID</a>
                                <?php else: ?>
                                    <span class="text-muted">No ID yet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending_approval'): ?>
                                    <form method="POST" class="d-flex gap-1 flex-wrap">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="registration_id" value="<?= (int)$r['id'] ?>">
                                        <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
