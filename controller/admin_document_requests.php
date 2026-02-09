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
$rows = [];

if ($search !== '') {
    $sql = "SELECT dr.*, dt.name AS document_name,
                   CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
                   dt.category AS document_category
            FROM document_requests dr
            LEFT JOIN residents r ON r.id = dr.resident_id
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            WHERE CONCAT(r.first_name, ' ', r.last_name) LIKE CONCAT('%', ?, '%')
               OR dr.ref_no LIKE CONCAT('%', ?, '%')
            ORDER BY dr.requested_at DESC, dr.id DESC";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $search, $search);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
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
