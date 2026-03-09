<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// POST ONLY
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

// CSRF CHECK
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid CSRF token.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found.");
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request ID.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT 
        dr.id,
        dr.clearance_photo,
        dt.name AS document_name,
        dt.category AS document_category,
        dt.template_key
    FROM document_requests dr
    LEFT JOIN document_types dt ON dt.id = dr.document_type_id
    WHERE dr.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Request not found.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$docCategory = strtolower(trim((string)($data['document_category'] ?? '')));
$docName     = strtolower(trim((string)($data['document_name'] ?? '')));
$templateKey = strtolower(trim((string)($data['template_key'] ?? '')));

$isClearance =
    strpos($docCategory, 'clearance') !== false ||
    strpos($docName, 'clearance') !== false ||
    $templateKey === 'clearance';

if (!$isClearance) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Photo upload is only allowed for Barangay Clearance.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

if (empty($_FILES['clearance_photo']) || $_FILES['clearance_photo']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Please select a photo.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$file = $_FILES['clearance_photo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Photo upload failed.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Photo must be 2MB or below.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$info = @getimagesize($file['tmp_name']);
if ($info === false) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid image file.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$extMap = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

$mime = $info['mime'] ?? '';
if (!isset($extMap[$mime])) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Only JPG, PNG, and WEBP are allowed.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/clearance_photos';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$ext = $extMap[$mime];
$filename = 'clr_req_' . $id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$fullPath = $uploadDir . '/' . $filename;
$dbPath   = 'uploads/clearance_photos/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to save uploaded photo.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$oldPath = trim((string)($data['clearance_photo'] ?? ''));
if ($oldPath !== '') {
    $oldFullPath = __DIR__ . '/../' . ltrim($oldPath, '/');
    if (is_file($oldFullPath)) {
        @unlink($oldFullPath);
    }
}

$upd = $mysqli->prepare("UPDATE document_requests SET clearance_photo = ? WHERE id = ? LIMIT 1");
$upd->bind_param("si", $dbPath, $id);
$ok = $upd->execute();
$upd->close();

if (!$ok) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to update photo in database.'];
    header("Location: /BIS/views/admin/upload_clearance_photo.php?id=" . $id);
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Clearance photo uploaded successfully.'];
header("Location: /BIS/controller/admin_document_requests.php");
exit;