<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Official.php';

$officialModel = new Official($conn);

$action = $_POST['action'] ?? ($_GET['action'] ?? 'list');

/* ==========================
   HELPERS
========================== */
function uploadOfficialPhoto(string $fieldName, ?string $oldPhoto = null): array
{
    // returns [ok(bool), photo(?string), msg(?string)]
    if (empty($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return ['ok' => true, 'photo' => $oldPhoto, 'msg' => null];
    }

    // basic upload error handling
    if (!empty($_FILES[$fieldName]['error']) && $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'photo' => $oldPhoto, 'msg' => 'Upload failed.'];
    }

    $uploadDir = __DIR__ . '/../uploads/officials/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'photo' => $oldPhoto, 'msg' => 'Invalid image type. Use JPG, PNG, GIF, or WEBP.'];
    }

    // (optional) file size limit 2MB
    $maxSize = 2 * 1024 * 1024;
    if (!empty($_FILES[$fieldName]['size']) && $_FILES[$fieldName]['size'] > $maxSize) {
        return ['ok' => false, 'photo' => $oldPhoto, 'msg' => 'Image too large. Max 2MB.'];
    }

    // delete old photo if replacing
    if (!empty($oldPhoto)) {
        $oldPath = $uploadDir . $oldPhoto;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    $newName = uniqid('official_', true) . '.' . $ext;
    $dest = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $dest)) {
        return ['ok' => false, 'photo' => $oldPhoto, 'msg' => 'Failed to save uploaded file.'];
    }

    return ['ok' => true, 'photo' => $newName, 'msg' => null];
}

/* ==========================
   STORE
========================== */
if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $up = uploadOfficialPhoto('photo', null);
    if (!$up['ok']) {
        $_SESSION['error'] = $up['msg'] ?? 'Photo upload error.';
        header("Location: /BIS/controller/officials.php");
        exit;
    }

    $_POST['photo'] = $up['photo']; // pass filename to model
    $result = $officialModel->create($_POST);

    $_SESSION[$result['ok'] ? 'success' : 'error'] = $result['ok'] ? 'Official added.' : ($result['msg'] ?? 'Failed.');
    header("Location: /BIS/controller/officials.php");
    exit;
}

/* ==========================
   UPDATE
========================== */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = 'Invalid ID.';
        header("Location: /BIS/controller/officials.php");
        exit;
    }

    // old photo from hidden input
    $oldPhoto = $_POST['old_photo'] ?? null;

    $up = uploadOfficialPhoto('photo', $oldPhoto);
    if (!$up['ok']) {
        $_SESSION['error'] = $up['msg'] ?? 'Photo upload error.';
        header("Location: /BIS/controller/officials.php");
        exit;
    }

    $_POST['photo'] = $up['photo']; // new filename or old if not replaced
    $result = $officialModel->update($id, $_POST);

    $_SESSION[$result['ok'] ? 'success' : 'error'] = $result['ok'] ? 'Official updated.' : ($result['msg'] ?? 'Failed.');
    header("Location: /BIS/controller/officials.php");
    exit;
}

/* ==========================
   DELETE
========================== */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    // delete photo file too
    $row = $officialModel->find($id);
    if ($row && !empty($row['photo'])) {
        $path = __DIR__ . '/../uploads/officials/' . $row['photo'];
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    $ok = $officialModel->delete($id);
    $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Official deleted.' : 'Delete failed.';
    header("Location: /BIS/controller/officials.php");
    exit;
}

/* ==========================
   LIST
========================== */
$officials = $officialModel->all();
require_once __DIR__ . '/../views/admin/Barangay_officials.php';
