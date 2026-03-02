<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (empty($_GET['ref'])) {
    exit('<div class="alert alert-danger">Invalid reference.</div>');
}

$ref = trim($_GET['ref']);

$stmt = $conn->prepare("
    SELECT 
        dr.ref_no,
        dt.name AS document,
        dr.purpose,
        dr.fee_snapshot AS fee,
        dr.status,
        dr.requested_at
    FROM document_requests dr
    LEFT JOIN document_types dt ON dt.id = dr.document_type_id
    WHERE TRIM(dr.ref_no) = TRIM(?)
    LIMIT 1
");
$stmt->bind_param("s", $ref);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    exit('<div class="alert alert-warning">Request not found.</div>');
}
?>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Reference No:</div>
    <div class="col-8"><?= htmlspecialchars($data['ref_no']) ?></div>
    </div>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Document:</div>
    <div class="col-8"><?= htmlspecialchars($data['document']) ?></div>
    </div>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Purpose:</div>
    <div class="col-8"><?= htmlspecialchars($data['purpose']) ?></div>
    </div>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Fee:</div>
    <div class="col-8">₱<?= number_format((float)$data['fee'], 2) ?></div>
    </div>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Status:</div>
    <div class="col-8"><?= htmlspecialchars($data['status']) ?></div>
    </div>

    <div class="row mb-2">
    <div class="col-4 fw-bold">Requested At:</div>
    <div class="col-8"><?= htmlspecialchars($data['requested_at']) ?></div>
    </div>