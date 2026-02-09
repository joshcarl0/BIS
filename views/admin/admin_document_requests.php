<?php
$rows  = $rows ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$search = trim($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Document Request</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">
  <?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>

  <div id="mainContent" class="main-content p-0">
    <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Document Requests</h3>
        <span class="text-muted"><?= count($rows) ?> total</span>
      </div>

      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <!-- ✅ SEARCH BAR -->
      <form class="row g-2 mb-3" method="GET" action="/BIS/controller/admin_document_requests.php">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
              type="text"
              class="form-control"
              name="search"
              value="<?= htmlspecialchars($search) ?>"
              placeholder="Search by Resident Name or Ref No (e.g. Juan / REF-2026-0006)"
            >
          </div>
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100" type="submit">Search</button>
        </div>
        <div class="col-md-2">
          <a class="btn btn-outline-secondary w-100" href="/BIS/controller/admin_document_requests.php">Reset</a>
        </div>
      </form>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref No</th>
                  <th>Resident</th>
                  <th>Document</th>
                  <th>Category</th>
                  <th>Purpose</th>
                  <th>Fee</th>
                  <th>Status</th>
                  <th>Requested At</th>
                  <th style="width: 360px;">Action</th>
                </tr>
              </thead>

              <tbody>
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">No requests found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $r): ?>
                    <?php
                      $status = $r['status'] ?? 'Pending';
                      $badge = 'secondary';
                      if ($status === 'Pending')  $badge = 'warning';
                      if ($status === 'Approved') $badge = 'success';
                      if ($status === 'Rejected') $badge = 'danger';
                      if ($status === 'Released') $badge = 'primary';
                    ?>
                    <tr>
                      <td class="fw-semibold"><?= htmlspecialchars($r['ref_no'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['resident_name'] ?? 'Unknown') ?></td>
                      <td><?= htmlspecialchars($r['document_name'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['document_category'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['purpose'] ?? '') ?></td>
                      <td>₱<?= number_format((float)($r['fee_snapshot'] ?? 0), 2) ?></td>
                      <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span></td>
                      <td><?= htmlspecialchars($r['requested_at'] ?? $r['created_at'] ?? '') ?></td>

                      <td>
                        <form class="d-flex gap-2" method="POST" action="/BIS/controller/admin_document_requests.php">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                          <!-- ✅ preserve search when you click approve/reject/release -->
                          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

                          <input class="form-control form-control-sm" name="remarks" placeholder="Remarks (optional)">

                          <button class="btn btn-sm btn-success" name="action" value="approve"
                            <?= ($status !== 'Pending') ? 'disabled' : '' ?>>
                            Approve
                          </button>

                          <button class="btn btn-sm btn-danger" name="action" value="reject"
                            <?= ($status !== 'Pending') ? 'disabled' : '' ?>>
                            Reject
                          </button>

                          <button class="btn btn-sm btn-primary" name="action" value="release"
                            <?= ($status !== 'Approved') ? 'disabled' : '' ?>>
                            Release
                          </button>

                          <?php if (in_array($status, ['Approved','Released'], true)): ?>
                            <a class="btn btn-sm btn-dark"
                              target="_blank"
                              href="/BIS/controller/print_document.php?id=<?= (int)$r['id'] ?>">
                              🖨 Print
                            </a>
                          <?php endif; ?>
                        </form>
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

  <script src="/BIS/assets/js/sidebar_toggle.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
