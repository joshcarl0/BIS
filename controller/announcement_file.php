<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// allow resident & admin only
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['resident','admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$filename = basename($_GET['file'] ?? '');
$mode = $_GET['mode'] ?? 'inline';

if ($filename === '') {
    http_response_code(400);
    exit('Bad request');
}

$path = __DIR__ . '/../uploads/announcements/' . $filename;

if (!is_file($path)) {
    http_response_code(404);
    exit('File not found');
}

$type = mime_content_type($path) ?: 'application/octet-stream';
header("Content-Type: $type");
header("Content-Length: " . filesize($path));

$disposition = ($mode === 'download') ? 'attachment' : 'inline';
header("Content-Disposition: $disposition; filename=\"$filename\"");

readfile($path);
exit;