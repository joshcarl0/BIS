<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Announcement.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function check_csrf(): void {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("Invalid CSRF token");
    }
}


$ann = new Announcement($db);

$action = $_POST['action'] ?? '';
$adminId = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

$uploadDirAbs = __DIR__ . '/../uploads/announcements/';
$uploadDirWeb = '/BIS/uploads/announcements/';

// make sure directory exists
if (!is_dir($uploadDirAbs)) {
    mkdir($uploadDirAbs, 0777, true);
}

function upload_multiple_files(string $inputName, string $uploadDirAbs, string $uploadDirWeb): array
{
    $saved = [];

    if (empty($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) return $saved;

    $names = $_FILES[$inputName]['name'];
    $tmp   = $_FILES[$inputName]['tmp_name'];
    $err   = $_FILES[$inputName]['error'];
    $size  = $_FILES[$inputName]['size'];
    $type  = $_FILES[$inputName]['type'];

    // allowlist (safe)
    $allowed = [
        'image/jpeg','image/png','image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $maxSize = 5 * 1024 * 1024; // 5MB each

    for ($i = 0; $i < count($names); $i++) {
        if ($err[$i] !== UPLOAD_ERR_OK) continue;
        if ($size[$i] > $maxSize) continue;
        if (!in_array($type[$i], $allowed, true)) continue;

        $orig = basename($names[$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(10)) . ($ext ? ".".$ext : "");
        $absPath = $uploadDirAbs . $safeName;

        if (move_uploaded_file($tmp[$i], $absPath)) {
            $saved[] = [
                'file_name' => $orig,
                'file_path' => $uploadDirWeb . $safeName, // stored in DB
                'file_type' => $type[$i],
                'file_size' => (int)$size[$i],
            ];
        }
    }

    return $saved;
}

try {

    if ($action === 'add') {
        check_csrf();

        $title = trim($_POST['title'] ?? '');
        $details = trim($_POST['details'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $status = in_array($status,['Active', 'Archived'], true) ? $status: 'Active';

        if ($title === '' || $details === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Title and Details are required.'];
            header("Location: /BIS/views/admin/admin_announcements.php");
            exit;
        }

        $newId = $ann->create($title, $details, $adminId, $status);

        // attachments
        $files = upload_multiple_files('attachments', $uploadDirAbs, $uploadDirWeb);
        foreach ($files as $f) {
            $ann->addAttachments($newId, $f['file_name'], $f['file_path'], $f['file_type'], $f['file_size']);
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Announcement added successfully.'];
       header("Location: /BIS/views/admin/admin_announcements.php");
        exit;
    }

    if ($action === 'update') {
        check_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $details = trim($_POST['details'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        if ($id <= 0 || $title === '' || $details === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid update request.'];
            header("Location: /BIS/views/admin/admin_announcements.php");
            exit;
        }

        $ann->update($id, $title, $details, $status);

        // optional: add more attachments on edit
        $files = upload_multiple_files('attachments', $uploadDirAbs, $uploadDirWeb);
        foreach ($files as $f) {
            $ann->addAttachments($id, $f['file_name'], $f['file_path'], $f['file_type'], $f['file_size']);
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Announcement updated successfully.'];
        header("Location: /BIS/views/admin/admin_announcements.php");
        exit;
    }

    if ($action === 'delete') {
        check_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid delete request.'];
            header("Location: /BIS/views/admin/admin_announcements.php");
            exit;
        }

        // delete physical files first
        $atts = $ann->attachments($id);
        $webPrefix = $a['file_path'];

        foreach ($atts as $a) {
            $webPath = $a['file_path']; // e.g. /BIS/uploads/announcements/xxx.png
            if (strpos($webpath, $webPrefix) === 0) {
                $fileName = substr($webPath, strlen($webPrefix));
                $absPath = $uploadDirAbs . $fileName;
                if (is_file($absPath)) @unlink($absPath);

            }
        }

        // DB delete (attachments rows cascade)
        $ann->delete($id);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Announcement deleted successfully.'];
        header("Location: /BIS/views/admin/admin_announcements.php");
        exit;
    }

    // fallback
    header("Location: /BIS/views/admin_announcements.php");
    exit;

} catch (Throwable $e) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error: ' . $e->getMessage()];
    header("Location: /BIS/views/admin/admin_announcements.php");
    exit;
}
