<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Announcement.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit;
}

$announcement = new Announcement($conn);
$rows = $announcement->active(); // announcements list

// attachments map: [announcement_id => attachments array]
$attMap = [];
foreach ($rows as $r) {
    $aid = (int)$r['id'];
    $attMap[$aid] = $announcement->attachments($aid); // uses your existing method
}

require_once __DIR__ . '/../views/resident/user_announcements.php';
