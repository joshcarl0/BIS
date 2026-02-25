<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$showNewUserWelcome = isset($_COOKIE['bis_new_user']) && $_COOKIE['bis_new_user'] === '1';
if ($showNewUserWelcome) {
    setcookie('bis_new_user', '', [
        'expires' => time() - 3600,
        'path' => '/BIS',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>

  <!-- Bootstrap + Icons (kung gumagamit ka) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Sidebar CSS -->
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body>

  <!-- LEFT SIDEBAR (USER) -->
  <?php require_once __DIR__ . '/navbaruser_side.php'; ?>

  <!-- MAIN CONTENT WRAPPER -->
  <div id="mainContent" class="main-content p-0">

    <!-- TOP NAVBAR (USER) -->
    <?php require_once __DIR__ . '/navbaruser_top.php'; ?>

    <!-- PAGE CONTENT -->
    <div class="p-3">
      <h1 class="h4 mb-3">Welcome to the User Dashboard</h1>

      <?php if ($showNewUserWelcome): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Welcome to BIS! Your account setup is complete.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <script src="/BIS/assets/js/sidebar_toggle.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
