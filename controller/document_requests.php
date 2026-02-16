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

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

// CSRF check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request (CSRF).'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

/* =========================
   BASIC REQUIRED INPUTS
========================= */
$document_type_id = (int)($_POST['document_type_id'] ?? 0);
$purpose          = trim((string)($_POST['purpose'] ?? ''));

if ($document_type_id <= 0 || $purpose === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Please complete required fields.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

/* =========================
   FIND RESIDENT ID
   (user_id or email fallback)
========================= */
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found. Check database.php variable name (\$db or \$conn).");
}

$sql = "SELECT r.id
        FROM users u
        INNER JOIN residents r
            ON (r.user_id = u.id
                OR (r.user_id IS NULL AND r.email IS NOT NULL AND r.email <> '' AND r.email = u.email))
        WHERE u.id = ?
        ORDER BY (r.user_id = u.id) DESC, r.id DESC
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Database error (prepare failed).'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$row = null;
if (method_exists($stmt, 'get_result')) {
    $row = $stmt->get_result()->fetch_assoc();
} else {
    // fallback (rare)
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
}
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Resident profile not found.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$residentId = (int)$row['id'];

/* =========================
   EXTRA FIELDS (DYNAMIC)
   Supports:
   1) extra[field] format
   2) direct POST fields fallback
========================= */

// Prefer "extra" array (recommended)
$extra = $_POST['extra'] ?? [];
if (!is_array($extra)) $extra = [];

// If you used direct inputs (name="child_name"), auto-pick them up too
$directKeys = [
    'child_name', 'child_dob', 'child_pob',
    'mother_name', 'father_name',
    'partner_name', 'living_since', 'since'
];

foreach ($directKeys as $k) {
    if (!isset($extra[$k]) && isset($_POST[$k])) {
        $extra[$k] = $_POST[$k];
    }
}

// Normalize + trim values
foreach ($extra as $k => $v) {
    if (is_array($v)) continue;
    $extra[$k] = trim((string)$v);
}

// Normalize date key: if form uses "since", map to "living_since"
if (!empty($extra['since']) && empty($extra['living_since'])) {
    $extra['living_since'] = $extra['since'];
}

/* =========================
   CREATE REQUEST
========================= */
$model = new DocumentRequest($mysqli);

$result = $model->createResidentRequest($residentId, $document_type_id, $purpose, $extra);

if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to submit request.'];
    header("Location: /BIS/views/resident/document_request.php");
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Request submitted! Ref No: ' . $result['ref_no']];
header("Location: /BIS/views/resident/transaction.php");
exit;
