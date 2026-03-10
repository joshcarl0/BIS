<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    exit('<div class="alert alert-danger">Unauthorized.</div>');
}

require_once __DIR__ . '/../config/database.php';

$mysqli = $conn ?? $db ?? null;
if (!$mysqli) {
    exit('<div class="alert alert-danger">Database connection not found.</div>');
}

/*  MUST match the JS param name: ref_no */
$refNo = trim((string)($_GET['ref_no'] ?? ''));
if ($refNo === '') {
    exit('<div class="alert alert-danger">Invalid reference.</div>');
}

$userId = (int)($_SESSION['user_id'] ?? 0);

/*  Get resident_id (same logic as transaction.php) */
$stmt = $mysqli->prepare("
    SELECT r.id
    FROM users u
    INNER JOIN residents r
        ON (
            r.user_id = u.id
            OR (r.user_id IS NULL AND r.email IS NOT NULL AND r.email <> '' AND r.email = u.email)
        )
    WHERE u.id = ?
    ORDER BY (r.user_id = u.id) DESC, r.id DESC
    LIMIT 1
");
if (!$stmt) {
    exit('<div class="alert alert-danger">Prepare failed (resident lookup).</div>');
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$residentId = (int)($res['id'] ?? 0);
if ($residentId <= 0) {
    exit('<div class="alert alert-danger">Resident profile not found.</div>');
}

/* ✅ Fetch request details (secure: ensure belongs to this resident) */
$stmt2 = $mysqli->prepare("
    SELECT 
        dr.ref_no,
        dt.category,
        dt.name AS document,
        dr.purpose,
        dr.fee_snapshot AS fee,
        dr.status,
        dr.requested_at,
        dr.extra_json,
        dr.clearance_photo
    FROM document_requests dr
    LEFT JOIN document_types dt ON dt.id = dr.document_type_id
    WHERE dr.resident_id = ?
      AND TRIM(dr.ref_no) = TRIM(?)
    LIMIT 1
");
if (!$stmt2) {
    exit('<div class="alert alert-danger">Prepare failed (details lookup).</div>');
}
$stmt2->bind_param("is", $residentId, $refNo);
$stmt2->execute();
$data = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$data) {
    exit('<div class="alert alert-danger">Request not found.</div>');
}

/* Format */
$fee = number_format((float)($data['fee'] ?? 0), 2);
$reqAt = $data['requested_at'] ? date('M d, Y h:i A', strtotime($data['requested_at'])) : '-';

/* Extra JSON */
$extra = [];
if (!empty($data['extra_json'])) {
    $decoded = json_decode($data['extra_json'], true);
    if (is_array($decoded)) $extra = $decoded;
}
?>

<div class="row g-3">
  <div class="col-md-6">
    <div class="fw-bold">Reference No</div>
    <div><?= htmlspecialchars($data['ref_no']) ?></div>
  </div>
  <div class="col-md-6">
    <div class="fw-bold">Status</div>
    <div><?= htmlspecialchars($data['status'] ?? '-') ?></div>
  </div>

  <div class="col-md-6">
    <div class="fw-bold">Document</div>
    <div><?= htmlspecialchars(($data['category'] ?? '') . ' - ' . ($data['document'] ?? '')) ?></div>
  </div>
  <div class="col-md-6">
    <div class="fw-bold">Fee</div>
    <div>₱<?= $fee ?></div>
  </div>

  <div class="col-12">
    <div class="fw-bold">Purpose</div>
    <div><?= htmlspecialchars($data['purpose'] ?? '-') ?></div>
  </div>

  <div class="col-md-6">
    <div class="fw-bold">Requested At</div>
    <div><?= htmlspecialchars($reqAt) ?></div>
  </div>

  <?php if (!empty($data['clearance_photo'])): ?>
    <div class="col-md-6">
      <div class="fw-bold">Uploaded Photo</div>
      <div class="small text-muted"><?= htmlspecialchars($data['clearance_photo']) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($extra)): ?>
    <div class="col-12">
      <div class="fw-bold mb-1">Additional Information</div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <tbody>
          <?php foreach ($extra as $k => $v): ?>
            <tr>
              <th style="width:35%"><?= htmlspecialchars((string)$k) ?></th>
              <td><?= htmlspecialchars((string)$v) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>