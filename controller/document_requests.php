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
if (!$stmt) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Server error: failed to prepare resident lookup.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}
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

/* ==============================
   RATE LIMIT: 3 requests / 5 mins
============================== */
$limit = 3;

$stmt = $db->prepare("
  SELECT COUNT(*) AS c
  FROM document_requests
  WHERE resident_id = ?
    AND requested_at >= (NOW() - INTERVAL 5 MINUTE)
");
if ($stmt) {
    $stmt->bind_param("i", $residentId);
    $stmt->execute();
    $count = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();

    if ($count >= $limit) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => "Too many requests. Max {$limit} requests per 5 minutes. Please try again later."
        ];
        header("Location: /BIS/views/resident/document_request.php");
        exit;
    }
}

/* ==========================================
   OPTIONAL: DUPLICATE CHECK (recommended)
   Same doc + same purpose within 10 minutes
========================================== */
$stmt = $db->prepare("
  SELECT id
  FROM document_requests
  WHERE resident_id = ?
    AND document_type_id = ?
    AND purpose = ?
    AND requested_at >= (NOW() - INTERVAL 10 MINUTE)
  LIMIT 1
");
if ($stmt) {
    $stmt->bind_param("iis", $residentId, $document_type_id, $purpose);
    $stmt->execute();
    $dup = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dup) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'msg'  => 'Duplicate request detected. Please wait before submitting again.'
        ];
        header("Location: /BIS/views/resident/document_request.php");
        exit;
    }
}

/* ==========================
   SUBMIT REQUEST
========================== */
$model = new DocumentRequest($db);

$extra  = $_POST['extra'] ?? [];
$result = $model->createResidentRequest($residentId, $document_type_id, $purpose, $extra);

if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to submit request.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Request submitted! Ref No: ' . ($result['ref_no'] ?? '')];
header("Location: /BIS/views/resident/transaction.php");
exit;
