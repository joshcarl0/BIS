<?php
session_start();

// Optional: admin guard (same style as your admin pages)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';

$householdModel = new Household($conn);

// filter (optional)
$status = $_GET['status'] ?? 'Active'; // Active | Inactive | (blank for all)

// Get households
$households = $householdModel->getAll($status === 'All' ? '' : $status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Households</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

<!-- LEFT SIDEBAR (same as your admin pages) -->
<?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

<div class="main-content" id="mainContent">
    <!-- TOP NAVBAR (if you have one) -->
    <?php if (file_exists(__DIR__ . '/../views/navbar_top.php')) include __DIR__ . '/../views/navbar_top.php'; ?>

    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Households</h3>
                <div class="text-muted">Manage household registry and head-of-household</div>
            </div>

            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All</option>
                    </select>
                    <button class="btn btn-sm btn-outline-dark">Filter</button>
                </form>

                <!-- Change this link later when you create add form -->
                <a href="../views/admin/household_add.php" class="btn btn-sm btn-primary">
                    + Add Household
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Household Code</th>
                                <th>Purok</th>
                                <th>Address</th>
                                <th>Head</th>
                                <th>Type</th>
                                <th>Tenure</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($households)): ?>
                            <?php foreach ($households as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['household_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($h['purok_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($h['address_line'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($h['head_name'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($h['household_type'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($h['tenure_status'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= ($h['status'] ?? '') === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($h['status'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <!-- Create these pages later -->
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="/BIS/views/household_view.php?id=<?= (int)$h['id'] ?>">
                                           View
                                        </a>
                                        <a class="btn btn-sm btn-outline-secondary"
                                           href="/BIS/views/household_edit.php?id=<?= (int)$h['id'] ?>">
                                           Edit
                                        </a>

                                        <?php if (($h['status'] ?? '') === 'Active'): ?>
                                            <a class="btn btn-sm btn-outline-danger"
                                               href="/BIS/controller/household_deactivate.php?id=<?= (int)$h['id'] ?>"
                                               onclick="return confirm('Deactivate this household?');">
                                               Deactivate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No households found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggleBtn = document.getElementById("toggleSidebar");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const sidebar = document.getElementById("sidebar");
      const main = document.getElementById("mainContent");
      const icon = document.getElementById("toggleIcon");

      if (!sidebar || !main || !icon) return;

      sidebar.classList.toggle("collapsed");
      main.classList.toggle("expanded");

      if (sidebar.classList.contains("collapsed")) {
        icon.classList.remove("bi-list");
        icon.classList.add("bi-x-lg");
      } else {
        icon.classList.remove("bi-x-lg");
        icon.classList.add("bi-list");
      }
    });
  }
  </script>
</body>
</html>
