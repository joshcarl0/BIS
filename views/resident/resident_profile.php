<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    header("Location: /BIS/views/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

$mysqli = $conn ?? $db ?? null;
if (!$mysqli) die("Database connection not found.");

$userModel = new User($mysqli);
$userId = (int)$_SESSION['user_id'];

$user = $userModel->getUserById($userId);
if (!$user) die("User not found.");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$status = strtolower(trim($user['status'] ?? 'active'));
$isActive = ($status === 'active');
$displayName = $user['full_name'] ?? $user['username'] ?? 'Resident';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  ->
  <link rel="stylesheet" href="/BIS/assets/css/navbaruserleft.css">
  <link rel="stylesheet" href="/BIS/assets/css/resident_dashboard.css">
  <link rel="stylesheet" href="/BIS/assets/css/resident_profile.css">
</head>
<body>

<?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

<div id="mainContent" class="main-content p-0">

<?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

<div class="container-fluid py-4 px-4">
    <div class="profile-shell">

        <div class="section-title">
        <h3><i class="bi bi-person-circle"></i> Update Profile</h3>
        <a href="/BIS/views/resident/manage_account.php" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-gear me-1"></i> Manage Account
        </a>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> rounded-4 border-0 shadow-sm">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <div class="card-soft">

        <!-- HERO -->
        <div class="profile-hero">
            <div class="hero-row">
            <div class="hero-left">

            <div class="avatar-box">
            <?php
            $avatar = $_SESSION['avatar'] ?? ($user['avatar'] ?? '');
            $avatarPath = $avatar ? "/BIS/" . ltrim($avatar, "/") : "/BIS/assets/images/default-avatar.png";
            ?>
            <img src="<?= htmlspecialchars($avatarPath) ?>?v=<?= time() ?>" class="avatar-img" alt="avatar">
                <form action="/BIS/controller/upload_avatar.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="avatar" accept="image/*" hidden id="avatarUpload">
                <label for="avatarUpload" class="avatar-edit">
                    <i class="bi bi-camera"></i>
                </label>
                </form>
            </div>

            <div class="hero-meta">
                <p class="name"><?= htmlspecialchars($displayName) ?></p>
                <p class="sub"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>
            </div>

            <div class="badge-status">
                <span class="badge-dot <?= $isActive ? 'dot-active' : 'dot-inactive' ?>"></span>
                <span><?= $isActive ? 'Active' : 'Inactive' ?></span>
            </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="profile-body">
            <form action="/BIS/controller/update_profile.php" method="POST" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control"
                    value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                <div class="help-mini">This will appear on certificates/requests.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                <div class="help-mini">We’ll use this for OTP/notifications.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                    value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                <div class="help-mini">Must be unique.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($status) ?>" disabled>
                <div class="help-mini">Only admin can change status.</div>
            </div>

            <hr class="hr-soft">

            <!-- sticky action bar -->
            <div class="col-12">
                <div class="action-bar">
                <div class="action-left">
                    <i class="bi bi-info-circle me-1"></i>
                    Make sure details match your valid ID.
                </div>

                <div class="d-flex gap-2">
                    <a href="/BIS/views/user_dashboard.php" class="btn btn-light btn-pill">
                    Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-pill">
                    <i class="bi bi-check2-square me-1"></i> Save Changes
                    </button>
                </div>
                </div>
            </div>

            </form>
        </div>
        </div>

    </div>
    </div>

<script src="/BIS/assets/js/resident_navbar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-submit avatar form on file select
document.getElementById("avatarUpload").onchange = function(){
this.form.submit();
};
</script>

</body>
</html>