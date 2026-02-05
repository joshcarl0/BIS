<?php
// View expects variables from controller/population_dashboard_controller.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Population Overview</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
 
  <link rel="stylesheet" href="/BIS/assets/css/dashboard.css">
</head>

<body style="background: #D6D5D7;">

  <!-- LEFT SIDEBAR (✅ inside body) -->
  <?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>

  <div class="main-content" id="mainContent">

    <!-- TOP NAVBAR -->
    <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container-fluid py-4">

      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h3 class="mb-1">Population Overview</h3>
          <div class="text-muted">People & Household Summary + SES</div>
        </div>
      </div>

      <!-- PEOPLE OVERVIEW -->
      <div class="row g-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Total Residents (Active)</div>
              <div class="fs-2 fw-bold"><?= (int)$totalResidents ?></div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Male</div>
              <div class="fs-2 fw-bold"><?= (int)$maleResidents ?></div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Female</div>
              <div class="fs-2 fw-bold"><?= (int)$femaleResidents ?></div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Senior Citizens (60+)</div>
              <div class="fs-2 fw-bold"><?= (int)$ageSeniors ?></div>
            </div>
          </div>
        </div>
      </div><!-- ✅ close row -->

      <div class="row g-3 mt-0">
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Minors (0 - 17)</div>
              <div class="fs-2 fw-bold"><?= (int)$ageMinors ?></div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Adults (18 - 59)</div>
              <div class="fs-2 fw-bold"><?= (int)$ageAdults ?></div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="text-muted">Special Groups (Total)</div>
              <div class="fs-2 fw-bold"><?= (int)array_sum($specialGroups ?? []) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Households -->
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h5 class="mb-3">Households</h5>
          <div class="row g-3">
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Total</div><div class="fs-4 fw-bold"><?= (int)$totalHouseholds ?></div></div></div>
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Active</div><div class="fs-4 fw-bold"><?= (int)$activeHouseholds ?></div></div></div>
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Inactive</div><div class="fs-4 fw-bold"><?= (int)$inactiveHouseholds ?></div></div></div>
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Dissolved</div><div class="fs-4 fw-bold"><?= (int)$dissolvedHouseholds ?></div></div></div>
          </div>
        </div>
      </div>

      <!-- PROGRAMS -->
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h5 class="mb-3">Programs / Assistance (Households)</h5>
          <div class="row g-3">
            <div class="col-md-2"><div class="border rounded p-3 bg-white"><div class="text-muted">4Ps</div><div class="fs-4 fw-bold"><?= (int)$prog4ps ?></div></div></div>
            <div class="col-md-2"><div class="border rounded p-3 bg-white"><div class="text-muted">TUPAD</div><div class="fs-4 fw-bold"><?= (int)$progTupad ?></div></div></div>
            <div class="col-md-2"><div class="border rounded p-3 bg-white"><div class="text-muted">AKAP</div><div class="fs-4 fw-bold"><?= (int)$progAkap ?></div></div></div>
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Solo Parent Assist</div><div class="fs-4 fw-bold"><?= (int)$progSolo ?></div></div></div>
            <div class="col-md-3"><div class="border rounded p-3 bg-white"><div class="text-muted">Social Pension</div><div class="fs-4 fw-bold"><?= (int)$progPension ?></div></div></div>
          </div>
        </div>
      </div>

      <!-- SES -->
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h5 class="mb-3">Socio-Economic Status (SES)</h5>

          <div class="row g-3">
            <?php foreach (($ses ?? []) as $label => $val): ?>
              <div class="col-6 col-md-3">
                <div class="border rounded p-3 bg-white">
                  <div class="text-muted"><?= htmlspecialchars($label) ?></div>
                  <div class="d-flex justify-content-between align-items-end">
                    <div class="fs-4 fw-bold"><?= (int)$val ?></div>
                    <div class="text-muted"><?= htmlspecialchars(pct((int)$val, (int)$sesTotal)) ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- ✅ CHARTS in cards (with design) -->
      <div class="row g-3 mt-3">
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="mb-2">Age Distribution</h6>
              <div class="chart-box"><canvas id="agePie"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="mb-2">Gender Distribution</h6>
              <div class="chart-box"><canvas id="genderBar"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="mb-2">Special Groups</h6>
              <div class="chart-box"><canvas id="specialChart"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="mb-2">SES Distribution</h6>
              <div class="chart-box"><canvas id="sesPie"></canvas></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ✅ PASS DATA (fixed variables) -->
  <script>
    window.DASHBOARD_DATA = {
      age: {
        minors: <?= (int)$ageMinors ?>,
        adults: <?= (int)$ageAdults ?>,
        seniors: <?= (int)$ageSeniors ?>
      },
      gender: {
        male: <?= (int)$maleResidents ?>,
        female: <?= (int)$femaleResidents ?>
      },
      special: {
        labels: <?= json_encode(array_keys($specialGroups ?? [])) ?>,
        data: <?= json_encode(array_values($specialGroups ?? [])) ?>
      },
      ses: {
        labels: <?= json_encode(array_keys($ses ?? [])) ?>,
        data: <?= json_encode(array_values($ses ?? [])) ?>
      }
    };
  </script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
  <script src="/BIS/assets/js/dashboard_chart.js"></script>


</body>
</html>
