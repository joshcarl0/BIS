<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// USER / RESIDENT GUARD
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'resident') {
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
        <small>Resident Panel</small>
    </div>

    <ul class="sidebar-menu">

        <li class="<?= activeMenu('Home.php', $current) ?>">
            <a href="/BIS/views/Home.php">
                <i class="bi bi-house-fill"></i>
                <span>Home</span>
            </a>
        </li>

        <li class="<?= activeMenu('officials_public.php', $current) ?>">
            <a href="/BIS/controller/officials_public.php">
                <i class="bi bi-people-fill"></i>
                <span>Barangay Officials</span>
            </a>
        </li>

        <li class="<?= activeMenu('Documentrequest.php', $current) ?>">
            <a href="/BIS/views/Documentrequest.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Document Request</span>
            </a>
        </li>

        <li class="<?= activeMenu('Transaction.php', $current) ?>">
            <a href="/BIS/views/Transaction.php">
                <i class="bi bi-receipt"></i>
                <span>Transaction</span>
            </a>
        </li>

           <li class="<?= activeMenu('user_announcements.php', $current) ?>">
    <a href="/BIS/controller/user_announcements.php">
        <i class="bi bi-newspaper"></i>
        <span>Announcements</span>
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
