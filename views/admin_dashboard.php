<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

    <!-- LEFT SIDEBAR -->
    <?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-content" id="mainContent">

        <!-- TOP NAVBAR -->
        <?php include 'navbar_top.php'; ?>

        <!-- PAGE CONTENT -->
        <div class="container-fluid mt-4">
            <?php
                $displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
                $firstName = explode(' ', trim($displayName))[0];
                $firstName = ucfirst(strtolower($firstName));
                ?>
                <h3>Welcome <?= htmlspecialchars($firstName) ?>, Admin</h3>

            <p class="text-muted">
            The Barangay Information System (BIS) is a web-based solution designed to digitize and centralize barangay records. 
            It enables efficient management of residents, households, socio-economic data, and community programs while supporting faster services and informed decision-making.
            </p>
        </div>
    </div>

    <!-- Bootstrap JS (optional but ok) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle JS -->
    <script>
    document.getElementById("toggleSidebar").addEventListener("click", function () {
        const sidebar = document.getElementById("sidebar");
        const main = document.getElementById("mainContent");
        const icon = document.getElementById("toggleIcon");

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
    </script>
</body>
</html>
