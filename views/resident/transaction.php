<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ RESIDENT GUARD FIRST
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    header("Location: /BIS/views/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// ✅ SUPPORT BOTH $conn and $db
$mysqli = $conn ?? $db ?? null;
if (!$mysqli) {
    die("Database connection not found. Check database.php variable name (\$conn or \$db).");
}

$userId = (int)($_SESSION['user_id'] ?? 0);

/* =========================
   1) Get resident_id (user_id with email fallback)
========================= */
$residentId = 0;

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
    die("Prepare failed (resident lookup): " . htmlspecialchars($mysqli->error));
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$residentId = (int)($res['id'] ?? 0);

/* =========================
   2) Get requests
========================= */
$rows = [];
if ($residentId > 0) {
    $sql = "
        SELECT 
            dr.ref_no,
            dt.name AS document,
            dr.purpose,
            dr.fee_snapshot AS fee,
            dr.status,
            dr.requested_at
        FROM document_requests dr
        LEFT JOIN document_types dt ON dt.id = dr.document_type_id
        WHERE dr.resident_id = ?
        ORDER BY dr.requested_at DESC
    ";
    $stmt2 = $mysqli->prepare($sql);
    if (!$stmt2) {
        die("Prepare failed (transactions): " . htmlspecialchars($mysqli->error));
    }
    $stmt2->bind_param("i", $residentId);
    $stmt2->execute();
    $rows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Transactions</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- ✅ SAME CSS AS RESIDENT DASHBOARD -->
  <link rel="stylesheet" href="/BIS/assets/css/navbaruserleft.css">
  <link rel="stylesheet" href="/BIS/assets/css/resident_dashboard.css">
</head>

<body class="bis-body">

  <!-- LEFT SIDEBAR -->
  <?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-content p-0" id="mainContent">

    <!-- TOP NAVBAR (resident) -->
    <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

    <div class="container-fluid py-4 px-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h3 class="mb-1 fw-bold">My Transactions</h3>
          <div class="text-muted">Your document requests and status</div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Ref No</th>
                  <th>Document</th>
                  <th>Purpose</th>
                  <th>Fee</th>
                  <th>Status</th>
                  <th>Requested At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">No requests yet.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $r): ?>
                    <?php
                      $status = (string)($r['status'] ?? 'Pending');
                      $badge = 'bg-secondary';

                      if ($status === 'Pending')  $badge = 'badge-soft-warning';
                      if ($status === 'Approved') $badge = 'badge-soft-success';
                      if ($status === 'Released') $badge = 'badge-soft-dark';
                      if ($status === 'Rejected') $badge = 'badge-soft-danger';

                      $fee = (float)($r['fee'] ?? 0);
                      $reqAt = $r['requested_at'] ?? '';
                      $reqAtFmt = $reqAt ? date('M d, Y h:i A', strtotime($reqAt)) : '-';
                    ?>
                    <tr>
                      <td class="text-nowrap fw-semibold"><?= htmlspecialchars($r['ref_no'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($r['document'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($r['purpose'] ?? '-') ?></td>
                      <td class="text-nowrap">₱<?= number_format($fee, 2) ?></td>
                      <td class="text-nowrap">
                        <span class="badge <?= $badge ?> rounded-pill px-3 py-2">
                          <?= htmlspecialchars($status) ?>
                        </span>
                      </td>
                      <td class="text-nowrap"><?= htmlspecialchars($reqAtFmt) ?></td>
                      <td class="text-nowrap">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-view"
                                data-ref_no="<?= htmlspecialchars($r['ref_no'] ?? '') ?>">
                          View
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- VIEW MODAL -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Request Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewModalBody">Loading...</div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/BIS/assets/js/sidebar_toggle.js"></script>

  <script>
  // View details
  document.addEventListener('click', async (e) => {
    const viewBtn = e.target.closest('.btn-view');
    if (!viewBtn) return;

    const refNo = viewBtn.dataset.ref_no || '';
    if (!refNo) return;

    const modalBody = document.getElementById('viewModalBody');
    modalBody.textContent = 'Loading...';

    const modalEl = document.getElementById('viewModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
      // ✅ Use one param name consistently
      const res = await fetch('/BIS/controller/resident_transaction_view.php?ref_no=' + encodeURIComponent(refNo));
      modalBody.innerHTML = await res.text();
    } catch (err) {
      modalBody.innerHTML = '<div class="alert alert-danger">Failed to load details.</div>';
    }
  });
  </script>

</body>
</html>