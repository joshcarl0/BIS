<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// security guard
if (empty($_SESSION['role'])) {
    http_response_code(403);
    exit('Forbidden');
}

$filename = $_GET['file'] ?? '';
$mode = $_GET['mode'] ?? 'inline'; // inline or download

$filename = basename($filename); // protection
if (!$filename) {
    http_response_code(400);
    exit('Bad request');
}

$path = __DIR__ . '/../uploads/announcements/' . $filename;

if (!file_exists($path)) {
    http_response_code(404);
    exit('File not found');
}

// headers
$type = mime_content_type($path);
header("Content-Type: $type");
header("Content-Length: " . filesize($path));

if ($mode === 'download') {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
} else {
    header('Content-Disposition: inline; filename="' . $filename . '"');
}

readfile($path);
exit;
