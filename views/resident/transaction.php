<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

$userId = (int)$_SESSION['user_id'];

// Get requests linked to the logged-in account:
// 1) direct resident.user_id match
// 2) fallback by same email for legacy/unlinked resident rows
$rows = [];
$sql = "
    SELECT
        dr.ref_no,
        dt.name AS document,
        dr.purpose,
        dr.fee_snapshot AS fee,
        dr.status,
        dr.requested_at
    FROM users u
    INNER JOIN residents r
        ON (r.user_id = u.id
            OR (r.email IS NOT NULL AND r.email <> '' AND u.email IS NOT NULL AND r.email = u.email))
    INNER JOIN document_requests dr ON dr.resident_id = r.id
    LEFT JOIN document_types dt ON dt.id = dr.document_type_id
    WHERE u.id = ?
    ORDER BY dr.requested_at DESC
";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$rows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);



// RESIDENT GUARD (adjust role name if 'resident' / 'user')
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'resident' && ($_SESSION['role'] ?? '') !== 'user')) {
    header("Location: /BIS/views/login.php");
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Transactions</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- SAME SIDEBAR CSS -->
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

  <!-- LEFT SIDEBAR (Resident) -->
  <?php require_once __DIR__ . '/../navbaruser_side.php'; ?>
  <!-- or: require_once __DIR__ . '/../views/navbaruser_side.php';  (depende sa path mo) -->

  <!-- MAIN CONTENT -->
  <div class="main-content" id="mainContent">

    <!-- TOP NAVBAR -->
    <?php include __DIR__ . '/../navbar_top.php'; ?>
    <!-- (palitan path kung iba file mo) -->

    <div class="container-fluid mt-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h3 class="mb-1">My Transactions</h3>
          <div class="text-muted">Your document requests and status</div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
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
                    <tr>
                      <td class="text-nowrap"><?= htmlspecialchars($r['ref_no']) ?></td>
                      <td><?= htmlspecialchars($r['document']) ?></td>
                      <td><?= htmlspecialchars($r['purpose']) ?></td>
                      <td class="text-nowrap">₱<?= number_format((float)$r['fee'], 2) ?></td>
                      <td class="text-nowrap">
                        <span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span>
                      </td>
                      <td class="text-nowrap"><?= htmlspecialchars($r['requested_at']) ?></td>
                      <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="#">View</a>
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

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Sidebar Toggle (if you have toggle button id=toggleSidebar) -->
  <script>
  const btn = document.getElementById("toggleSidebar");
  if (btn) {
    btn.addEventListener("click", function () {
      const sidebar = document.getElementById("sidebar");
      const main = document.getElementById("mainContent");
      const icon = document.getElementById("toggleIcon");

      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");

      if (icon) {
        if (sidebar.classList.contains("collapsed")) {
          icon.classList.remove("bi-list");
          icon.classList.add("bi-x-lg");
        } else {
          icon.classList.remove("bi-x-lg");
          icon.classList.add("bi-list");
        }
      }
    });
  }
  </script>
</body>
</html>
