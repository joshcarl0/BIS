<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DocumentRequest.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$docReq = new DocumentRequest($db);

/* ========= SEARCH (GET) ========= */
$search = trim($_GET['search'] ?? '');  // search by name / ref no

/* ========= ACTIONS (POST) ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid CSRF token'];
        header("Location: /BIS/controller/admin_document_requests.php");
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');

    $adminId = $_SESSION['user_id'] ?? null;

    $map = [
        'approve' => 'Approved',
        'reject'  => 'Rejected',
        'release' => 'Released'
    ];

    if ($id > 0 && isset($map[$action])) {
        $ok = $docReq->updateStatus($id, $map[$action], $adminId, $remarks ?: null);
        $_SESSION['flash'] = $ok
            ? ['type'=>'success','msg'=>"Request updated: {$map[$action]}"]
            : ['type'=>'danger','msg'=>"Failed to update request."];
    }

    // Preserve search after action (optional but recommended)
    $qs = '';
    if (!empty($_POST['search'])) {
        $qs = '?search=' . urlencode(trim($_POST['search']));
    }

    header("Location: /BIS/controller/admin_document_requests.php{$qs}");
    exit;
}

/* ========= LIST ========= */
if ($search !== '') {
    $rows = $docReq->search($search);   // search() returns array
} else {
    $rows = $docReq->all();
}

// Safety: normalize to array before view uses count()/foreach
if ($rows instanceof mysqli_result) {
    $rows = $rows->fetch_all(MYSQLI_ASSOC);
} elseif (!is_array($rows)) {
    $rows = [];
}

// VIEW
require_once __DIR__ . '/../views/admin/admin_document_requests.php';
