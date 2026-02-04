<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: /BIS/views/login.php"); exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Household.php';

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['error'] = "Invalid household id.";
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

$householdModel = new Household($conn);
$h = $householdModel->getById($id);

if (!$h) {
  $_SESSION['error'] = "Household not found.";
  header("Location: /BIS/views/admin/households_manage.php"); exit();
}

// Get puroks
$puroks = [];
$res = $conn->query("SELECT id, name FROM puroks ORDER BY name ASC");
if ($res) while ($row = $res->fetch_assoc()) $puroks[] = $row;

// Get residents
$residents = [];
$res = $conn->query("SELECT id, first_name, last_name FROM residents WHERE is_active = 1 ORDER BY last_name ASC");
if ($res) while ($row = $res->fetch_assoc()) $residents[] = $row;

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Household</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">
<?php require_once __DIR__ . '/../../views/navbaradmin_leftside.php'; ?>

<div class="main-content p-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Edit Household</h3>
      <small class="text-muted">Update household profile & socio-economic info</small>
    </div>
    <a href="/BIS/controller/households_manage.php" class="btn btn-secondary btn-sm">← Back</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="/BIS/controller/household_update.php" class="card shadow">
    <div class="card-body">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">

      <div class="row g-3">

        <div class="col-12">
          <h5 class="mb-0">Basic Information</h5>
          <hr class="mt-2">
        </div>

        <div class="col-md-4">
          <label class="form-label">Household Code</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($h['household_code'] ?? '') ?>" disabled>
        </div>

        <div class="col-md-4">
          <label for="purok_id" class="form-label">
            Purok <span class="text-danger">*</span>
          </label>
          <select id="purok_id" name="purok_id" class="form-select" required>
            <option value="" disabled>Select Purok</option>
            <?php foreach ($puroks as $p): ?>
              <option value="<?= (int)$p['id'] ?>" <?= ((int)$h['purok_id'] === (int)$p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label for="head_resident_id" class="form-label">Head of Household (Resident)</label>
          <select id="head_resident_id" name="head_resident_id" class="form-select">
            <option value="">Optional</option>
            <?php foreach ($residents as $r): ?>
              <option value="<?= (int)$r['id'] ?>" <?= ((int)($h['head_resident_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-8">
          <label for="address_line" class="form-label">
            Address <span class="text-danger">*</span>
          </label>
          <input type="text" id="address_line" name="address_line" class="form-control"
                 value="<?= htmlspecialchars($h['address_line'] ?? '') ?>" required>
        </div>

        <div class="col-md-4">
          <label for="landmark" class="form-label">Landmark</label>
          <input type="text" id="landmark" name="landmark" class="form-control"
                 value="<?= htmlspecialchars($h['landmark'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label for="years_residing" class="form-label">Years Residing</label>
          <input type="number" id="years_residing" name="years_residing" class="form-control" min="0"
                 value="<?= htmlspecialchars((string)($h['years_residing'] ?? '')) ?>">
        </div>

        <div class="col-md-3">
          <label for="housing_type" class="form-label">Housing Type</label>
          <input type="text" id="housing_type" name="housing_type" class="form-control"
                 value="<?= htmlspecialchars($h['housing_type'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label for="household_type" class="form-label">Household Type</label>
          <input type="text" id="household_type" name="household_type" class="form-control"
                 value="<?= htmlspecialchars($h['household_type'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label for="tenure_status" class="form-label">Tenure Status</label>
          <input type="text" id="tenure_status" name="tenure_status" class="form-control"
                 value="<?= htmlspecialchars($h['tenure_status'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="housing_status" class="form-label">Housing Status</label>
          <input type="text" id="housing_status" name="housing_status" class="form-control"
                 value="<?= htmlspecialchars($h['housing_status'] ?? '') ?>">
        </div>

        <div class="col-12 mt-2">
          <h5 class="mb-0">Socio-Economic</h5>
          <hr class="mt-2">
        </div>

        <div class="col-md-4">
          <label for="monthly_income_range" class="form-label">Monthly Income Range</label>
          <input type="text" id="monthly_income_range" name="monthly_income_range" class="form-control"
                 value="<?= htmlspecialchars($h['monthly_income_range'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="income_source" class="form-label">Income Source</label>
          <input type="text" id="income_source" name="income_source" class="form-control"
                 value="<?= htmlspecialchars($h['income_source'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="employment_type" class="form-label">Employment Type</label>
          <input type="text" id="employment_type" name="employment_type" class="form-control"
                 value="<?= htmlspecialchars($h['employment_type'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="socio_economic_class" class="form-label">Socio-Economic Class</label>
          <input type="text" id="socio_economic_class" name="socio_economic_class" class="form-control"
                 value="<?= htmlspecialchars($h['socio_economic_class'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="registration_date" class="form-label">Registration Date</label>
          <input type="date" id="registration_date" name="registration_date" class="form-control"
                 value="<?= htmlspecialchars($h['registration_date'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label for="status" class="form-label">Status</label>
          <select id="status" name="status" class="form-select">
            <?php $st = $h['status'] ?? 'Active'; ?>
            <option value="Active" <?= $st==='Active'?'selected':'' ?>>Active</option>
            <option value="Inactive" <?= $st==='Inactive'?'selected':'' ?>>Inactive</option>
            <option value="Dissolved" <?= $st==='Dissolved'?'selected':'' ?>>Dissolved</option>
          </select>
        </div>

        <div class="col-md-12">
          <label for="remarks" class="form-label">Remarks</label>
          <textarea id="remarks" name="remarks" class="form-control" rows="3"><?= htmlspecialchars($h['remarks'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <div class="card-footer text-end">
      <button type="submit" class="btn btn-primary">Update Household</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
