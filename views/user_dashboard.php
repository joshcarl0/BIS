<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit();
}

/* Display name fallback */
$displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Resident';

require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'];

/* =========================
  RECENT REQUESTS (Top 5)
========================= */
  $sql = "SELECT dr.requested_at,
                dr.purpose,
                dr.status,
                dt.name AS document_name
          FROM document_requests dr
          JOIN document_types dt 
              ON dt.id = dr.document_type_id
          WHERE dr.resident_id = ?
          ORDER BY dr.requested_at DESC
          LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);


/* =========================
  ANNOUNCEMENTS (Active only)
========================= */
$sql2 = "SELECT title, details, date_posted
          FROM announcements
          WHERE status = 'Active'
          ORDER BY date_posted DESC
          LIMIT 3";

$res2 = $conn->query($sql2);
$announcements = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resident Dashboard</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Sidebar CSS -->
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">

  <!-- Dashboard CSS (ADD THIS FILE) -->
  <link rel="stylesheet" href="/BIS/assets/css/resident_dashboard.css">
</head>

<body>

  <!-- LEFT SIDEBAR (USER) -->
  <?php require_once __DIR__ . '/navbaruser_side.php'; ?>

  <!-- MAIN CONTENT WRAPPER -->
  <div id="mainContent" class="main-content p-0">

    <!-- TOP NAVBAR (USER) -->
    <?php require_once __DIR__ . '/navbaruser_top.php'; ?>

    <!-- PAGE CONTENT -->
    <div class="container-fluid py-4 px-4">

      <!-- WELCOME CARD -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 welcome-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div>
            <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($displayName) ?> <span class="wave">👋</span></h2>
            <div class="text-muted fw-semibold">Barangay Don Galo, Parañaque City</div>
            <div class="text-muted small mt-1">Manage your requests and stay updated with barangay services.</div>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- optional search -->
            <div class="searchbox d-none d-md-flex">
              <i class="bi bi-search"></i>
              <input type="text" class="form-control form-control-sm" placeholder="Search">
            </div>

            <!-- avatar -->
            <img
              src="/BIS/assets/images/default-avatar.png"
              onerror="this.src='https://via.placeholder.com/96'"
              alt="avatar"
              class="avatar shadow-sm"
              width="96" height="96"
            >
          </div>
        </div>

        <!-- subtle watermark -->
        <div class="welcome-watermark"></div>
      </div>

      <!-- QUICK ACTIONS -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0">Quick Actions</h5>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
          <a class="quick-card card border-0 shadow-sm rounded-4 p-3 text-decoration-none" href="/BIS/views/resident_document_request.php">
            <div class="d-flex align-items-center gap-3">
              <div class="quick-ic ic-blue"><i class="bi bi-file-earmark-text"></i></div>
              <div>
                <div class="fw-bold text-dark">Request Document</div>
                <div class="small text-muted">Start a new request</div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <a class="quick-card card border-0 shadow-sm rounded-4 p-3 text-decoration-none" href="/BIS/views/resident_transactions.php">
            <div class="d-flex align-items-center gap-3">
              <div class="quick-ic ic-gold"><i class="bi bi-folder2-open"></i></div>
              <div>
                <div class="fw-bold text-dark">View My Requests</div>
                <div class="small text-muted">Track statuses</div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <a class="quick-card card border-0 shadow-sm rounded-4 p-3 text-decoration-none" href="/BIS/views/resident_announcements.php">
            <div class="d-flex align-items-center gap-3">
              <div class="quick-ic ic-green"><i class="bi bi-megaphone"></i></div>
              <div>
                <div class="fw-bold text-dark">Announcements</div>
                <div class="small text-muted">Barangay updates</div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <a class="quick-card card border-0 shadow-sm rounded-4 p-3 text-decoration-none" href="/BIS/views/resident_profile.php">
            <div class="d-flex align-items-center gap-3">
              <div class="quick-ic ic-sky"><i class="bi bi-person"></i></div>
              <div>
                <div class="fw-bold text-dark">Update Profile</div>
                <div class="small text-muted">Manage account</div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- MY RECENT REQUESTS -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h5 class="fw-bold mb-0">My Recent Requests</h5>
            <a class="btn btn-sm btn-outline-primary rounded-pill" href="/BIS/views/resident_transactions.php">
              View all
            </a>
          </div>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="min-width:130px;">Date</th>
                  <th style="min-width:190px;">Document Type</th>
                  <th style="min-width:180px;">Purpose</th>
                  <th style="min-width:120px;">Status</th>
                  <th style="min-width:140px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                      <!-- Date -->
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($r['requested_at']))) ?></td>

                      <!-- Document Type -->
                      <td class="fw-semibold"><?= htmlspecialchars($r['document_name']) ?></td>

                      <!-- Purpose -->
                      <td><?= htmlspecialchars($r['purpose']) ?></td>

                      <!-- Status -->
                      <td>
                        <?php
                          $status = $r['status'];
                          $badgeClass = 'bg-secondary';
                          if ($status === 'Pending')  $badgeClass = 'badge-soft-warning';
                          if ($status === 'Approved') $badgeClass = 'badge-soft-success';
                          if ($status === 'Rejected') $badgeClass = 'badge-soft-danger';
                        ?>
                        <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2">
                          <?= htmlspecialchars($status) ?>
                        </span>
                      </td>

                      <!-- Action -->
                      <td>
                        <?php if ($r['status'] === 'Approved'): ?>
                          <a class="btn btn-sm btn-primary rounded-pill px-3"
                            href="/BIS/views/download_document.php?id=<?= (int)$r['id'] ?>">
                            <i class="bi bi-download me-1"></i> Download
                          </a>
                        <?php else: ?>
                          <a class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                            href="/BIS/views/resident_transactions.php">
                            View
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>

      <!-- ANNOUNCEMENTS -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0">Barangay Announcements</h5>
        <a class="btn btn-sm btn-outline-primary rounded-pill" href="/BIS/views/resident/user_announcements.php">
          View all
        </a>
      </div>

      <div class="row g-3">
        <?php foreach ($announcements as $a): ?>
          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 announcement-card">
              <div class="card-body p-4">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                    <?= date('M d', strtotime($a['date_posted'])) ?>
                </span>

                <h6 class="fw-bold mt-3 mb-2">
                    <?= htmlspecialchars($a['title']) ?>
                </h6>

                <p class="text-muted small mb-3">
                    <?= htmlspecialchars($a['details']) ?>
                </p>

                <a href="/BIS/views/admin/admin_announcements.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                  Read more
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

  <script src="/BIS/assets/js/sidebar_toggle.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>