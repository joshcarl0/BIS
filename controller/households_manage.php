<?php
/**
 * /BIS/controller/households_manage.php
 * Manage Households (List + Search + Status Filter)
 */
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';

$householdModel = new Household($conn);

// Filters
$status = $_GET['status'] ?? 'Active';
$q = trim($_GET['q'] ?? '');

$allowed = ['Active', 'Inactive', 'Dissolved', 'All'];
if (!in_array($status, $allowed, true)) $status = 'Active';

// Fetch
$households = $householdModel->getAll($status === 'All' ? '' : $status, $q);

// Flash
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Households</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Your admin theme -->
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">



<!-- LEFT SIDEBAR -->
<?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

<div class="main-content" id="mainContent">

<!-- TOP NAVBAR -->
<?php require_once __DIR__ . '/../views/navbar_top.php'; ?>

    <div class="container-fluid py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h3 class="mb-1">Manage Households</h3>
                <div class="text-muted">Household registry & socio-economic profile</div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="/BIS/controller/export_households_csv.php" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Download Households CSV
                </a>

                            <a class="btn btn-outline-danger"
                href="/BIS/controller/export_household_pdf.php">
                <i class="bi bi-file-earmark-pdf"></i> Download Households PDF
                </a>

                <a href="/BIS/views/admin/household_add.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Household
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            name="q"
                            value="<?= htmlspecialchars($q) ?>"
                            placeholder="Search household code, head name, address, purok..."
                        />
                    </div>

                    <div class="col-8 col-md-3">
                        <select class="form-select form-select-sm" name="status">
                            <option value="Active"   <?= $status==='Active'?'selected':'' ?>>Active</option>
                            <option value="Inactive" <?= $status==='Inactive'?'selected':'' ?>>Inactive</option>
                            <option value="Dissolved"<?= $status==='Dissolved'?'selected':'' ?>>Dissolved</option>
                            <option value="All"      <?= $status==='All'?'selected':'' ?>>All</option>
                        </select>
                    </div>

                    <div class="col-4 col-md-2 d-grid">
                        <button class="btn btn-outline-dark btn-sm" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>

                    <div class="col-12 col-md-2 d-grid">
                        <a class="btn btn-outline-secondary btn-sm" href="/BIS/controller/households_manage.php?status=Active">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:140px;">Household Code</th>
                                <th style="min-width:120px;">Purok</th>
                                <th style="min-width:220px;">Address</th>
                                <th style="min-width:180px;">Head of Household</th>
                                <th style="min-width:140px;">Income Range</th>
                                <th style="min-width:130px;">Class</th>
                                <th style="min-width:110px;">Status</th>
                                <th class="text-end" style="min-width:240px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!empty($households)): ?>
                            <?php foreach ($households as $h): ?>
                                <?php
                                    $hStatus = $h['status'] ?? '';
                                    $badge = 'bg-secondary';
                                    if ($hStatus === 'Active') $badge = 'bg-success';
                                    elseif ($hStatus === 'Inactive') $badge = 'bg-warning text-dark';
                                    elseif ($hStatus === 'Dissolved') $badge = 'bg-dark';
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($h['household_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($h['purok_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($h['address_line'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(trim((string)($h['head_name'] ?? '')) ?: '—') ?></td>
                                    <td><?= htmlspecialchars($h['monthly_income_range'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($h['socio_economic_class'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= $badge ?>">
                                            <?= htmlspecialchars($hStatus ?: '—') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="/BIS/views/admin/household_view.php?id=<?= (int)$h['id'] ?>">
                                            <i class="bi bi-eye"></i> View
                                        </a>

                                        <a class="btn btn-sm btn-outline-secondary"
                                           href="/BIS/views/admin/household_edit.php?id=<?= (int)$h['id'] ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <?php if (($h['status'] ?? '') === 'Active'): ?>
                                            <a class="btn btn-sm btn-outline-danger"
                                               href="/BIS/controller/household_deactivate.php?id=<?= (int)$h['id'] ?>"
                                               onclick="return confirm('Deactivate this household?');">
                                                <i class="bi bi-slash-circle"></i>
                                            </a>
                                        <?php elseif (($h['status'] ?? '') === 'Inactive'): ?>
                                            <a class="btn btn-sm btn-outline-success"
                                               href="/BIS/controller/household_activate.php?id=<?= (int)$h['id'] ?>"
                                               onclick="return confirm('Activate this household?');">
                                                <i class="bi bi-check2-circle"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (($h['status'] ?? '') !== 'Dissolved'): ?>
                                            <a class="btn btn-sm btn-outline-dark"
                                               href="/BIS/controller/household_dissolve.php?id=<?= (int)$h['id'] ?>"
                                               onclick="return confirm('Dissolve this household?');">
                                                <i class="bi bi-house-x"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <div class="mb-2"><i class="bi bi-inbox fs-3"></i></div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  const toggleBtn = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("mainContent");
  const icon = document.getElementById("toggleIcon");

  if (!toggleBtn || !sidebar || !main || !icon) return;

  toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    main.classList.toggle("expanded");

    icon.classList.toggle("bi-list");
    icon.classList.toggle("bi-x-lg");
  });
})();
</script>

</body>
</html>
