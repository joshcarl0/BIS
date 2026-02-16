<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

/* =========================
   ADMIN GUARD
========================= */
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

/* =========================
   CSRF TOKEN
========================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* SUPPORT BOTH $db and $conn */
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found. Check database.php variable name (\$db or \$conn).");
}

$userModel = new User($mysqli);

// LOAD ROLES for dropdown
$roles = [];
$res = $mysqli->query("SELECT id, role_name FROM roles ORDER BY id ASC");
if ($res) {
    $roles = $res->fetch_all(MYSQLI_ASSOC);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = [];

/* =========================
   ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request (CSRF).'];
        header("Location: admin_usermanagement.php");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD USER
    if ($action === 'add') {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = (string)($_POST['password'] ?? '');
        $role_id   = (int)($_POST['role_id'] ?? 3); // default resident
        $status    = trim($_POST['status'] ?? 'active');

        if ($full_name === '' || $username === '' || $email === '' || $password === '') {
            $errors[] = "All fields are required.";
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (!$errors) {
            $ok = $userModel->adminCreateUser($username, $email, $password, $full_name, $role_id, $status);
            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'User added successfully.']
                : ['type' => 'danger', 'msg' => 'Failed to add user (username/email may already exist).'];

            header("Location: admin_usermanagement.php");
            exit;
        }
    }

    // EDIT USER
    if ($action === 'edit') {
        $id        = (int)($_POST['id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role_id   = (int)($_POST['role_id'] ?? 3);
        $status    = trim($_POST['status'] ?? 'active');
        $password  = (string)($_POST['password'] ?? ''); // optional

        if ($id <= 0) $errors[] = "Invalid user ID.";
        if ($full_name === '' || $username === '' || $email === '') $errors[] = "Full name, username, and email are required.";
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";

        if (!$errors) {
            $ok = $userModel->adminUpdateUser($id, $username, $email, $full_name, $role_id, $status, $password);
            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'User updated successfully.']
                : ['type' => 'danger', 'msg' => 'Failed to update user (username/email may already exist).'];

            header("Location: admin_usermanagement.php");
            exit;
        }
    }

    // DELETE USER (DISABLED)
    if ($action === 'delete') {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Delete is disabled. Please use Deactivate instead.'];
        header("Location: admin_usermanagement.php");
        exit;
    }

    // TOGGLE STATUS (Deactivate / Activate)
    if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $next_status = ($_POST['next_status'] ?? 'inactive') === 'active' ? 'active' : 'inactive';

        // prevent deactivating your own account
        if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id && $next_status === 'inactive') {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => "You can't deactivate your own account."];
            header("Location: admin_usermanagement.php");
            exit;
        }

        $ok = ($id > 0) ? $userModel->updateUserStatus($id, $next_status) : false;

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => "Status changed to {$next_status}."]
            : ['type' => 'danger', 'msg' => 'Failed to change status.'];

        header("Location: admin_usermanagement.php");
        exit;
    }
}

/* =========================
   LOAD USERS
========================= */
$search = trim($_GET['search'] ?? '');
$users = $userModel->getAllUsers($search);

/* =========================
   EDIT MODE
========================= */
$editId = (int)($_GET['edit'] ?? 0);
$editUser = $editId > 0 ? $userModel->getUserByIdAdmin($editId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - User Management</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

  <?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

  <div class="main-content" id="mainContent">

    <?php require_once __DIR__ . '/navbar_top.php'; ?>

    <div class="container-fluid mt-4">

      <h4 class="mb-3">Manage Users</h4>

      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="row g-3">

        <!-- ADD USER -->
        <div class="col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">Add Resident and Admin</h5>

              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">

                <div class="mb-2">
                  <label class="form-label" for="full_name">Full Name</label>
                  <input class="form-control" id="full_name" name="full_name" required autocomplete="name">
                </div>

                <div class="mb-2">
                  <label class="form-label" for="username">Username</label>
                  <input class="form-control" id="username" name="username" required autocomplete="username">
                </div>

                <div class="mb-2">
                  <label class="form-label" for="email">Email</label>
                  <input class="form-control" id="email" name="email" type="email" required autocomplete="email">
                </div>

                <div class="mb-2">
                  <label class="form-label" for="password">Password</label>
                  <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
                </div>

                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label" for="role_id">Role</label>
                    <select class="form-select" id="role_id" name="role_id" autocomplete="off" required>
                      <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['id'] ?>">
                          <?= htmlspecialchars($r['role_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status" autocomplete="off">
                      <option value="active">active</option>
                      <option value="inactive">inactive</option>
                    </select>
                  </div>
                </div>

                <button class="btn btn-primary w-100 mt-3">Add</button>
              </form>
            </div>
          </div>
        </div>

        <!-- USERS TABLE -->
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-body">

              <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Resident List</h5>

                <form class="d-flex gap-2" method="GET">
                  <input class="form-control" name="search" placeholder="Search name, username, email"
                         value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                  <button class="btn btn-primary">Search</button>
                </form>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Username</th>
                      <th>Email</th>
                      <th>Full Name</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Created</th>
                      <th style="width: 230px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!$users): ?>
                      <tr><td colspan="8" class="text-center text-muted py-4">No residents found.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($users as $u): ?>
                      <?php $isActive = (($u['status'] ?? '') === 'active'); ?>
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

                          <!-- Only toggle status (Deactivate/Activate) -->
                          <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="next_status" value="<?= $isActive ? 'inactive' : 'active' ?>">
                            <button class="btn btn-sm btn-outline-secondary">
                              <?= $isActive ? 'Deactivate' : 'Reactivate' ?>
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            </div>
          </div>

          <!-- EDIT FORM -->
          <?php if ($editUser): ?>
            <div class="card shadow-sm mt-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Edit Resident #<?= (int)$editUser['id'] ?></h5>
                  <a class="btn btn-sm btn-outline-dark" href="admin_usermanagement.php?search=<?= urlencode($search) ?>">Close</a>
                </div>

                <form method="POST" class="mt-3">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">

                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label" for="edit_full_name">Full Name</label>
                      <input class="form-control" id="edit_full_name" name="full_name"
                             value="<?= htmlspecialchars($editUser['full_name']) ?>" required autocomplete="name">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="edit_username">Username</label>
                      <input class="form-control" id="edit_username" name="username"
                             value="<?= htmlspecialchars($editUser['username']) ?>" required autocomplete="username">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="edit_email">Email</label>
                      <input class="form-control" id="edit_email" name="email" type="email"
                             value="<?= htmlspecialchars($editUser['email']) ?>" required autocomplete="email">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="edit_password">New Password (optional)</label>
                      <input class="form-control" id="edit_password" name="password" type="password"
                             placeholder="Leave blank to keep current" autocomplete="new-password">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="edit_role_id">Role</label>
                      <select class="form-select" id="edit_role_id" name="role_id" autocomplete="off" required>
                        <?php foreach ($roles as $r): ?>
                          <option value="<?= (int)$r['id'] ?>" <?= ((int)($editUser['role_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['role_name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="edit_status">Status</label>
                      <select class="form-select" id="edit_status" name="status" autocomplete="off">
                        <option value="active" <?= ($editUser['status'] ?? '')==='active'?'selected':'' ?>>active</option>
                        <option value="inactive" <?= ($editUser['status'] ?? '')==='inactive'?'selected':'' ?>>inactive</option>
                      </select>
                    </div>
                  </div>

                  <button class="btn btn-primary mt-3">Save Changes</button>
                </form>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  const toggleBtn = document.getElementById("toggleSidebar");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const sidebar = document.getElementById("sidebar");
      const main = document.getElementById("mainContent");
      const icon = document.getElementById("toggleIcon");
      if (!sidebar || !main || !icon) return;

      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");

      if (sidebar.classList.contains("collapsed")) {
        icon.classList.remove("bi-list");
        icon.classList.add("bi-x-lg");
      } else {
        icon.classList.remove("bi-x-lg");
        icon.classList.add("bi-list");
      }
    });
  }
  </script>

</body>
</html>
