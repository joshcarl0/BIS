<?php
/**
 * /BIS/views/admin/household_view.php
 * View Household Details
 */
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Household.php';

$householdModel = new Household($conn);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = "Invalid household ID.";
    header("Location: /BIS/controller/households_manage.php");
    exit();
}

$h = $householdModel->getById($id);
if (!$h) {
    $_SESSION['error'] = "Household not found.";
    header("Location: /BIS/controller/households_manage.php");
    exit();
}

// keep querystring for back
$status = $_GET['status'] ?? '';
$q = $_GET['q'] ?? '';
$backQS = http_build_query(array_filter([
    'status' => $status,
    'q' => $q,
], fn($v) => $v !== '' && $v !== null));
$backUrl = "/BIS/controller/households_manage.php" . ($backQS ? "?{$backQS}" : "");

function esc($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Household</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

<?php require_once __DIR__ . '/../../views/navbaradmin_leftside.php'; ?>

<div class="main-content" id="mainContent">
    <div class="container-fluid py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h3 class="mb-1">Household Details</h3>
                <div class="text-muted">View household registry & socio-economic profile</div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= esc($backUrl) ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>

                <a href="/BIS/views/admin/household_edit.php?id=<?= (int)$id ?>&<?= esc($backQS) ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
            </div>
        </div>

        <!-- TOP SUMMARY -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Household Code</div>
                        <div class="fw-semibold"><?= esc($h['household_code'] ?? '—') ?></div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">Purok</div>
                        <div class="fw-semibold"><?= esc($h['purok_name'] ?? '—') ?></div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <?php
                            $st = $h['status'] ?? '';
                            $badge = 'bg-secondary';
                            if ($st === 'Active') $badge = 'bg-success';
                            elseif ($st === 'Inactive') $badge = 'bg-warning text-dark';
                            elseif ($st === 'Dissolved') $badge = 'bg-dark';
                        ?>
                        <span class="badge <?= $badge ?>"><?= esc($st ?: '—') ?></span>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">Registration Date</div>
                        <div class="fw-semibold"><?= esc($h['registration_date'] ?? '—') ?></div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted small">Address</div>
                        <div class="fw-semibold"><?= esc($h['address_line'] ?? '—') ?></div>
                        <?php if (!empty($h['landmark'])): ?>
                            <div class="text-muted small mt-1">Landmark: <?= esc($h['landmark']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="row g-3">

            <!-- BASIC INFO -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Basic Information</h5>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="text-muted small">Head of Household</div>
                                <div class="fw-semibold"><?= esc($h['head_name'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Years Residing</div>
                                <div class="fw-semibold"><?= esc($h['years_residing'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Housing Type</div>
                                <div class="fw-semibold"><?= esc($h['housing_type'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Household Type</div>
                                <div class="fw-semibold"><?= esc($h['household_type'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Tenure Status</div>
                                <div class="fw-semibold"><?= esc($h['tenure_status'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Housing Status</div>
                                <div class="fw-semibold"><?= esc($h['housing_status'] ?? '—') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SOCIO-ECONOMIC -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Socio-Economic</h5>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="text-muted small">Monthly Income Range</div>
                                <div class="fw-semibold"><?= esc($h['monthly_income_range'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Socio-Economic Class</div>
                                <div class="fw-semibold"><?= esc($h['socio_economic_class'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Income Source</div>
                                <div class="fw-semibold"><?= esc($h['income_source'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Employment Type</div>
                                <div class="fw-semibold"><?= esc($h['employment_type'] ?? '—') ?></div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-2">Government Programs</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                              $programs = [
                                '4Ps' => (int)($h['is_4ps_beneficiary'] ?? 0),
                                'Social Pension' => (int)($h['is_social_pension'] ?? 0),
                                'TUPAD' => (int)($h['is_tupad_beneficiary'] ?? 0),
                                'AKAP' => (int)($h['is_akap_beneficiary'] ?? 0),
                                'Solo Parent' => (int)($h['is_solo_parent_assistance'] ?? 0),
                              ];
                              $hasAny = false;
                              foreach ($programs as $label => $val) {
                                  if ($val === 1) {
                                      $hasAny = true;
                                      echo '<span class="badge bg-primary">'.esc($label).'</span>';
                                  }
                              }
                              if (!$hasAny) echo '<span class="text-muted">—</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UTILITIES -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Utilities</h5>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="text-muted small">House Material</div>
                                <div class="fw-semibold"><?= esc($h['house_material'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Water Source</div>
                                <div class="fw-semibold"><?= esc($h['water_source'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Electricity Access</div>
                                <div class="fw-semibold"><?= esc($h['electricity_access'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Toilet Facility</div>
                                <div class="fw-semibold"><?= esc($h['toilet_facility'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Internet Access</div>
                                <div class="fw-semibold"><?= esc($h['internet_access'] ?? '—') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ASSETS + OTHER -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Assets & Other</h5>

                        <h6 class="mb-2">Assets</h6>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php
                              $assets = [
                                'Vehicle' => (int)($h['has_vehicle'] ?? 0),
                                'Motorcycle' => (int)($h['has_motorcycle'] ?? 0),
                                'Refrigerator' => (int)($h['has_refrigerator'] ?? 0),
                                'TV' => (int)($h['has_tv'] ?? 0),
                                'Washing Machine' => (int)($h['has_washing_machine'] ?? 0),
                                'Aircon' => (int)($h['has_aircon'] ?? 0),
                                'Computer' => (int)($h['has_computer'] ?? 0),
                                'Smartphone' => (int)($h['has_smartphone'] ?? 0),
                              ];
                              $hasAsset = false;
                              foreach ($assets as $label => $val) {
                                  if ($val === 1) {
                                      $hasAsset = true;
                                      echo '<span class="badge bg-success">'.esc($label).'</span>';
                                  }
                              }
                              if (!$hasAsset) echo '<span class="text-muted">—</span>';
                            ?>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="text-muted small">Land Ownership</div>
                                <div class="fw-semibold"><?= esc($h['land_ownership'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Business Ownership</div>
                                <div class="fw-semibold"><?= esc($h['business_ownership'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Highest Education</div>
                                <div class="fw-semibold"><?= esc($h['highest_education'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Health Insurance</div>
                                <div class="fw-semibold"><?= esc($h['health_insurance'] ?? '—') ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Malnutrition Cases</div>
                                <div class="fw-semibold"><?= esc($h['malnutrition_cases'] ?? '—') ?></div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-muted small">Remarks</div>
                        <div class="fw-semibold"><?= esc($h['remarks'] ?? '—') ?></div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
