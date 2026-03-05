<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /BIS/views/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

if (!isset($_FILES['avatar'])) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'No file uploaded.'];
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>"Upload error code: {$file['error']}"];
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

//  allow types
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];
if (!in_array($ext, $allowed, true)) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid image type. Use JPG/PNG/WEBP.'];
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

// size limit 2MB
if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Image too large. Max 2MB.'];
  header("Location: /BIS/views/resident/resident_profile.php");
  exit;
}

$userId = (int)$_SESSION['user_id'];
$filename = "user_{$userId}_" . time() . "." . $ext;

//  THIS is the REAL folder path in your project
$uploadDir = __DIR__ . "/../uploads/avatars/";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Cannot create avatars folder.'];
        header("Location: /BIS/views/resident/resident_profile.php");
        exit;
    }
    }

$targetPath = $uploadDir . $filename;

//  MOVE FILE
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Failed to save file. Check folder permissions/path.'];
    header("Location: /BIS/views/resident/resident_profile.php");
    exit;
    }

// DB UPDATE (save RELATIVE path)
$relativePath = "uploads/avatars/" . $filename;

    $mysqli = $db ?? $conn ?? null;
    if (!$mysqli) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Database connection not found.'];
    header("Location: /BIS/views/resident/resident_profile.php");
    exit;
    }

$stmt = $mysqli->prepare("UPDATE users SET avatar=? WHERE id=? LIMIT 1");
$stmt->bind_param("si", $relativePath, $userId);
$stmt->execute();
$stmt->close();

//  update session
$_SESSION['avatar'] = $relativePath;

$_SESSION['flash'] = ['type'=>'success','msg'=>'Avatar updated'];
header("Location: /BIS/views/resident/resident_profile.php");
exit;