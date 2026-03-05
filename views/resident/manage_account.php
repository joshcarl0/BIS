<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    header("Location: /BIS/views/login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Resident';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Account</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- same dashboard css (for navbar + layout) -->
  <link rel="stylesheet" href="/BIS/assets/css/navbaruserleft.css">
  <link rel="stylesheet" href="/BIS/assets/css/resident_dashboard.css">

  <!-- page css -->
  <link rel="stylesheet" href="/BIS/assets/css/manage_account.css">
</head>
<body>

<?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

<div id="mainContent" class="main-content p-0">
  <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

  <div class="container-fluid py-4 px-4">
    <div class="page-wrap">
      <div class="shell">

        <div class="section-title">
          <h3><i class="bi bi-gear"></i> Manage Account</h3>
          <a href="/BIS/views/resident/resident_profile.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
          </a>
        </div>

        <?php if ($flash): ?>
          <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> rounded-4 border-0 shadow-sm">
            <?= htmlspecialchars($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <div class="card-soft">
          <!-- HERO -->
          <div class="hero">
            <div class="left">
              <div class="ic"><i class="bi bi-shield-lock"></i></div>
              <div>
                <p class="title mb-0">Security Settings</p>
                <p class="sub mb-0">Hi, <?= htmlspecialchars($displayName) ?> — update your password and protect your account.</p>
              </div>
            </div>

            <div class="badge-pill">
              <span class="badge-dot"></span>
              <span>Secure</span>
            </div>
          </div>

          <!-- BODY -->
          <div class="body">
            <div class="row g-3">

              <!-- Change Password -->
              <div class="col-lg-7">
                <div class="card-soft">
                  <div class="body">
                    <div class="block-title">
                      <i class="bi bi-key"></i> Change Password
                    </div>

                    <form action="/BIS/controller/change_password.php" method="POST" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                      <div class="col-12">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="help-mini">Minimum 8 characters.</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                        <div class="help-mini">Must match the new password.</div>
                      </div>

                      <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary btn-pill" type="submit">
                          <i class="bi bi-check2-circle me-1"></i> Update Password
                        </button>
                      </div>
                    </form>

                    <hr class="hr-soft">

                    <div class="tip">
                      <i class="bi bi-info-circle me-1"></i>
                      Tip: Use a strong password (letters + numbers). Don’t share your OTP.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Deactivate -->
              <div class="col-lg-5">
                <div class="card-soft">
                  <div class="body">
                    <div class="block-title text-danger">
                      <i class="bi bi-exclamation-triangle"></i> Deactivate Account
                    </div>

                    <div class="danger-box">
                      <p class="text-muted small mb-2">
                        This will set your account to <b>inactive</b> and log you out.
                        You may request reactivation from the barangay/admin.
                      </p>

                      <form action="/BIS/controllers/resident/deactivate_account.php" method="POST"
                            onsubmit="return confirm('Are you sure you want to deactivate your account?');">
                        <button class="btn btn-outline-danger btn-pill w-100" type="submit">
                          <i class="bi bi-person-x me-1"></i> Deactivate My Account
                        </button>
                      </form>
                    </div>

                  </div>
                </div>
              </div>

            </div><!-- row -->
          </div><!-- body -->
        </div><!-- card-soft -->

      </div><!-- shell -->
    </div><!-- page-wrap -->
  </div><!-- container -->
</div><!-- mainContent -->

<script src="/BIS/assets/js/resident_navbar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>