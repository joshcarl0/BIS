<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

function activeMenu($file, $current) {
    return ($file === $current) ? 'active' : '';
}
?>

<div class="sidebar-left" id="sidebar">
    <div class="sidebar-header">
        <h4>Barangay Don Galo</h4>
        <small>Admin Panel</small>
    </div>

    <ul class="sidebar-menu">

            <li class="<?= activeMenu('admin_dashboard.php', $current) ?>">
        <a href="/BIS/controller/admin_dashboard.php">
            <i class="bi bi-house-door-fill"></i>
            <span>Home Dashboard</span>
        </a>
        </li>


        <li class="<?= activeMenu('population_dashboard.php', $current) ?>">
            <a href="/BIS/controller/population_dashboard.php">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Population Overview</span>
            </a>
        </li>

        <li class="<?= activeMenu('officials.php', $current) ?>">
            <a href="/BIS/controller/officials.php">
                <i class="bi bi-building"></i>
                <span>Officials</span>
            </a>
        </li>

            <li class="<?= activeMenu('admin_document_requests.php', $current) ?>">
            <a href="/BIS/controller/admin_document_requests.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Document Requests</span>
            </a>
            </li>


        <li class="<?= activeMenu('residents_manage.php', $current) ?>">
            <a href="/BIS/controller/residents_manage.php">
                <i class="bi bi-person-vcard"></i>
                <span>Resident Information</span>
            </a>
        </li>

        <li class="<?= activeMenu('households_manage.php', $current) ?>">
            <a href="/BIS/controller/households_manage.php">
                <i class="bi bi-house-fill"></i>
                <span>Household Information</span>
            </a>
        </li>

        <li class="<?= activeMenu('admin_announcements.php', $current) ?>">
            <a href="/BIS/views/admin/admin_announcements.php">
                <i class="bi bi-newspaper"></i>
                <span>Announcement</span>
            </a>
        </li>

                <li class="<?= activeMenu('admin_usermanagement.php', $current) ?>">
            <a href="/BIS/views/admin_usermanagement.php">
                <i class="bi bi-people-fill"></i>
                <span>User Management</span>
            </a>
        </li>

        <li class="logout">
            <a href="/BIS/views/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>
</div>
