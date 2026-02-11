<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

    <!-- LEFT SIDEBAR -->
    <?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-content" id="mainContent">

        <!-- TOP NAVBAR -->
        <?php include 'navbar_top.php'; ?>

        <!-- PAGE CONTENT -->
        <div class="container-fluid mt-4">
            <?php
                $displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
                $firstName = explode(' ', trim($displayName))[0];
                $firstName = ucfirst(strtolower($firstName));
            ?>
            <h3>Welcome <?= htmlspecialchars($firstName) ?>, Admin</h3>

            <p class="text-muted">
                The Barangay Information System (BIS) is a web-based solution designed to digitize and centralize barangay records.
                It enables efficient management of residents, households, socio-economic data, and community programs while supporting faster services and informed decision-making.
            </p>

            <!-- DASHBOARD CARDS (INSIDE main-content) -->
            <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted">Total Residents</div>
                            <div class="fs-2 fw-bold"><?= (int)($totalResidents ?? 0) ?></div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted">Total Households</div>
                            <div class="fs-2 fw-bold"><?= (int)($totalHouseholds ?? 0) ?></div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted">Pending</div>
                            <div class="fs-2 fw-bold"><?= (int)($pendingCount ?? 0) ?></div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted">Approved</div>
                            <div class="fs-2 fw-bold"><?= (int)($approvedCount ?? 0) ?></div>
                        </div>
                        </div>
                    </div>



            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Today’s Transactions (Released)</h5>
                    <small class="text-muted"><?= date('Y-m-d') ?></small>
                    </div>

                    <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Ref No</th>
                            <th>Resident</th>
                            <th>Document</th>
                            <th class="text-end">Amount</th>
                            <th>Released At</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($todayRows)): ?>
                            <tr><td colspan="5" class="text-muted">No released transactions today.</td></tr>
                        <?php else: foreach ($todayRows as $r): ?>
                            <tr>
                            <td><?= htmlspecialchars($r['ref_no']) ?></td>
                            <td><?= htmlspecialchars($r['resident_name']) ?></td>
                            <td><?= htmlspecialchars($r['document_type']) ?></td>
                            <td class="text-end">
                            ₱<?= number_format((float)($r['amount_paid'] ?? 0), 2) ?>
                            </td>
                            <td><?= htmlspecialchars($r['released_at']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                </div>


            <!-- RECENT ACTIVITY / LOGS -->
                <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Recent Activity</h5>
                    <small class="text-muted">Latest 10 updates</small>
                    </div>

                    <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Date/Time</th>
                            <th class="text-nowrap">Action</th>
                            <th>Description</th>
                            <th class="text-nowrap">By</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr><td colspan="4" class="text-muted">No activity yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars($log['actor_role'] ?? 'system') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                </div>
        </div><!-- /container-fluid -->
    </div><!-- /main-content -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            const statusLabels = <?= json_encode($statusLabels ?? []) ?>;
            const statusCounts = <?= json_encode($statusCounts ?? []) ?>;

            const incomeLabels = <?= json_encode($incomeLabels ?? []) ?>;
            const incomeValues = <?= json_encode($incomeValues ?? []) ?>;

            new Chart(document.getElementById('statusChart'), {
                type: 'pie',
                data: { labels: statusLabels, datasets: [{ data: statusCounts }] },
                options: { responsive: true }
            });

            new Chart(document.getElementById('incomeChart'), {
                type: 'bar',
                data: { labels: incomeLabels, datasets: [{ label: 'Income', data: incomeValues }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
            </script>

</body>
</html>
