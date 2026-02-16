<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ResidentRegistration.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$flow = $_SESSION['register_flow'] ?? null;
if (!$flow || empty($flow['registration_id']) || empty($flow['otp_verified'])) {
    $_SESSION['error'] = 'Please complete OTP verification first.';
    header('Location: /BIS/views/register.php');
    exit;
}

$registrationModel = new ResidentRegistration($conn);
$row = $registrationModel->findById((int) $flow['registration_id']);
if (!$row || $row['status'] !== 'pending_id') {
    $_SESSION['error'] = 'Registration is not ready for ID upload.';
    header('Location: /BIS/views/register.php');
    exit;
}

if (empty($_FILES['valid_id']) || (int)$_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Please upload a valid ID file.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$file = $_FILES['valid_id'];
$maxBytes = 5 * 1024 * 1024;
if ((int)$file['size'] > $maxBytes) {
    $_SESSION['error'] = 'File too large. Maximum is 5MB.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
$originalName = (string)($file['name'] ?? '');
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    $_SESSION['error'] = 'Only JPG, PNG, and PDF files are allowed.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$mime = mime_content_type($file['tmp_name']) ?: '';
$allowedMime = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($mime, $allowedMime, true)) {
    $_SESSION['error'] = 'Invalid file type.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$dir = __DIR__ . '/../uploads/valid_ids';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$uniqueName = 'validid_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$dest = $dir . '/' . $uniqueName;
$relativePath = '/BIS/uploads/valid_ids/' . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    $_SESSION['error'] = 'Failed to upload file.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

if (!$registrationModel->saveValidIdAndSubmit((int) $row['id'], $relativePath, $originalName)) {
    @unlink($dest);
    $_SESSION['error'] = 'Unable to submit registration after upload.';
    header('Location: /BIS/views/register_id_upload.php');
    exit;
}

$_SESSION['registration_submitted'] = [
    'ref_no' => $row['ref_no'],
    'status' => 'Pending Approval',
];
unset($_SESSION['register_flow']);

header('Location: /BIS/views/register_submitted.php');
exit;
