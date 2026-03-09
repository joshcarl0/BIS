<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found.");
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request ID.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT 
        dr.id,
        dr.ref_no,
        dr.status,
        dr.clearance_photo,
        dr.requested_at,
        dr.purpose,
        dt.name AS document_name,
        dt.category AS document_category,
        dt.template_key,
        CONCAT(
            COALESCE(r.first_name, ''), ' ',
            COALESCE(r.middle_name, ''), ' ',
            COALESCE(r.last_name, '')
        ) AS resident_name
    FROM document_requests dr
    LEFT JOIN document_types dt ON dt.id = dr.document_type_id
    LEFT JOIN residents r ON r.id = dr.resident_id
    WHERE dr.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Request not found.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$docCategory = strtolower(trim((string)($data['document_category'] ?? '')));
$docName     = strtolower(trim((string)($data['document_name'] ?? '')));
$templateKey = strtolower(trim((string)($data['template_key'] ?? '')));

$isClearance =
    strpos($docCategory, 'clearance') !== false ||
    strpos($docName, 'clearance') !== false ||
    $templateKey === 'clearance';

if (!$isClearance) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Photo upload is only allowed for Barangay Clearance.'];
    header("Location: /BIS/controller/admin_document_requests.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Clearance Photo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>
<body style="background:#D6D5D7;">
    <?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>

    <div id="mainContent" class="main-content p-0">
        <?php require_once __DIR__ . '/../navbar_top.php'; ?>

        <div class="container py-4">
            <div class="mb-3">
                <a href="/BIS/controller/admin_document_requests.php" class="btn btn-outline-secondary btn-sm">
                    ← Back to Requests
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Upload Barangay Clearance Photo</h4>
                </div>

                <div class="card-body">
                    <?php if ($flash): ?>
                        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                            <?= htmlspecialchars($flash['msg']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 180px;">Reference No</th>
                                    <td><?= htmlspecialchars($data['ref_no'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Resident</th>
                                    <td><?= htmlspecialchars(trim($data['resident_name'] ?? 'Unknown')) ?></td>
                                </tr>
                                <tr>
                                    <th>Document</th>
                                    <td><?= htmlspecialchars($data['document_name'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><?= htmlspecialchars($data['status'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Purpose</th>
                                    <td><?= htmlspecialchars($data['purpose'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Requested At</th>
                                    <td><?= htmlspecialchars($data['requested_at'] ?? '') ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light text-center">
                                <h6 class="mb-3">Current Photo</h6>

                                <?php if (!empty($data['clearance_photo'])): ?>
                                    <img
                                        src="/BIS/<?= htmlspecialchars($data['clearance_photo']) ?>"
                                        alt="Current Photo"
                                        class="img-fluid rounded border"
                                        style="max-height: 280px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <div class="text-muted py-5">No photo uploaded yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <form action="/BIS/controller/admin_upload_clearance_photo.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

                        <div class="mb-3">
                            <label for="clearance_photo" class="form-label fw-semibold">Select Photo</label>
                            <input
                                type="file"
                                class="form-control"
                                id="clearance_photo"
                                name="clearance_photo"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >
                            <div class="form-text">Allowed: JPG, PNG, WEBP. Max size: 2MB.</div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <?= !empty($data['clearance_photo']) ? 'Update Photo' : 'Upload Photo' ?>
                        </button>

                        <a href="/BIS/controller/admin_document_requests.php" class="btn btn-secondary">
                            Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>