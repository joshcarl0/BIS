<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DocumentRequest.php';

// RESIDENT GUARD
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident')) {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF token create
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

// CSRF check (IMPORTANT)
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request (CSRF).'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$document_type_id = (int)($_POST['document_type_id'] ?? 0);
$purpose          = trim($_POST['purpose'] ?? '');

if ($document_type_id <= 0 || $purpose === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Please complete required fields.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

// GET resident_id using logged-in user_id (with email fallback for legacy accounts)
$sql = "SELECT r.id
        FROM users u
        INNER JOIN residents r
            ON (r.user_id = u.id
                OR (r.user_id IS NULL AND r.email IS NOT NULL AND r.email <> '' AND r.email = u.email))
        WHERE u.id = ?
        ORDER BY (r.user_id = u.id) DESC, r.id DESC
        LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Resident profile not found.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$residentId = (int)$row['id'];


$model = new DocumentRequest($db);

$extra = $_POST['extra'] ?? [];
$result = $model->createResidentRequest($residentId, $document_type_id, $purpose, $extra);


if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to submit request.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Request submitted! Ref No: ' . $result['ref_no']];
header("Location: /BIS/views/resident/transaction.php");
exit;
