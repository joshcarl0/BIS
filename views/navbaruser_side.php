<?php
require_once __DIR__ . '/../includes/resident_guard.php';

$current = basename($_SERVER['PHP_SELF']);

function activeMenu($file, $current) {
    return ($file === $current) ? 'active' : '';
}
?>

<div class="sidebar-left" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <img src="/BIS/assets/images/barangay_logo.png" alt="Barangay Don Galo Logo">
        </div>
        <h4>Barangay Don Galo</h4>
        <small>Resident Panel</small>
    </div>

    <ul class="sidebar-menu">

        <li class="<?= activeMenu('user_dashboard.php', $current) ?>">
            <a href="/BIS/views/user_dashboard.php">
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

        <li class="<?= activeMenu('document_request.php', $current) ?>">
            <a href="/BIS/views/resident/document_request.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Document Request</span>
            </a>
        </li>

        <li class="<?= activeMenu('transaction.php', $current) ?>">
            <a href="/BIS/views/resident/transaction.php">
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