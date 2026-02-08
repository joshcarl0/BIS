<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Household.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$householdModel = new Household($conn);


// for display only
$householdCode = $householdModel->generateHouseholdCode();



// Get puroks
$puroks = [];
$res = $conn->query("SELECT id, name FROM puroks ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $puroks[] = $row;
    }
}

// Get residents
$residents = [];
$res = $conn->query("SELECT id, first_name, last_name FROM residents WHERE is_active = 1 ORDER BY last_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $residents[] = $row;
    }
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);


function getDropdownOptions(mysqli $conn, string $category): array {
  $items = [];
  $stmt = $conn->prepare("SELECT value FROM dropdown_options WHERE category = ? AND is_active = 1 ORDER BY sort_order, value");
  $stmt->bind_param("s", $category);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) $items[] = $row['value'];
  $stmt->close();
  return $items;
}

$opt_housing_type        = getDropdownOptions($conn, 'housing_type');
$opt_household_type      = getDropdownOptions($conn, 'household_type');
$opt_tenure_status       = getDropdownOptions($conn, 'tenure_status');
$opt_housing_status      = getDropdownOptions($conn, 'housing_status');

$opt_monthly_income      = getDropdownOptions($conn, 'monthly_income_range');
$opt_income_source       = getDropdownOptions($conn, 'income_source');
$opt_employment_type     = getDropdownOptions($conn, 'employment_type');
$opt_ses_class           = getDropdownOptions($conn, 'socio_economic_class');

$opt_house_material      = getDropdownOptions($conn, 'house_material');
$opt_water_source        = getDropdownOptions($conn, 'water_source');
$opt_electricity_access  = getDropdownOptions($conn, 'electricity_access');
$opt_toilet_facility     = getDropdownOptions($conn, 'toilet_facility');
$opt_internet_access     = getDropdownOptions($conn, 'internet_access');

$opt_land_ownership      = getDropdownOptions($conn, 'land_ownership');
$opt_business_ownership  = getDropdownOptions($conn, 'business_ownership');
$opt_highest_education   = getDropdownOptions($conn, 'highest_education');
$opt_health_insurance    = getDropdownOptions($conn, 'health_insurance');
$opt_malnutrition_cases  = getDropdownOptions($conn, 'malnutrition_cases');




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Household</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">
<?php require_once __DIR__ . '/../../views/navbaradmin_leftside.php'; ?>

<div class="main-content p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Add Household</h3>
            <small class="text-muted">Complete household profile & socio-economic info</small>
        </div>
        <a href="/BIS/controller/households_manage.php" class="btn btn-secondary btn-sm">
            ← Back
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/BIS/controller/household_store.php" class="card shadow">
        <div class="card-body">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row g-3">

<!-- ===================== BASIC INFO ===================== -->
<div class="col-12">
    <h5 class="mb-0">Basic Information</h5>
    <hr class="mt-2">
</div>


<div class="col-md-4">
  <label for="purok_id" class="form-label">
    Purok <span class="text-danger">*</span>
  </label>
  <select id="purok_id" name="purok_id" class="form-select" required>
    <option value="" selected disabled>Select Purok</option>
    <?php foreach ($puroks as $p): ?>
      <option value="<?= (int)$p['id'] ?>">
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
            <option value="<?= (int)$r['id'] ?>">
                <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-8">
    <label for="address_line" class="form-label">
        Address <span class="text-danger">*</span>
    </label>
    <input type="text" id="address_line" name="address_line" class="form-control" required>
</div>

<div class="col-md-4">
    <label for="landmark" class="form-label">Landmark</label>
    <input type="text" id="landmark" name="landmark" class="form-control">
</div>

<div class="col-md-3">
    <label for="years_residing" class="form-label">Years Residing</label>
    <input type="number" id="years_residing" name="years_residing" class="form-control" min="0">
</div>

<div class="col-md-3">
   <select id="housing_type" name="housing_type" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_housing_type as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>


</div>

<div class="col-md-3">
    <select id="household_type" name="household_type" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_household_type as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-3">
   <select id="tenure_status" name="tenure_status" class="form-select">
    <option value="">Select</option>
  <?php foreach ($opt_tenure_status as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
   </select>
</div>

<div class="col-md-4">
    <select id="housing_status" name="housing_status" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_housing_status as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>

<!-- ===================== SOCIO-ECONOMIC ===================== -->
<div class="col-12 mt-2">
    <h5 class="mb-0">Socio-Economic</h5>
    <hr class="mt-2">
</div>

<div class="col-md-4">
    <select id="monthly_income_range" name="monthly_income_range" class="form-select" required>
  <option value="">-- Select Income Range --</option>
  <?php foreach ($opt_monthly_income as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
    <select id="income_source" name="income_source" class="form-select" required>
  <option value="">-- Select Income Source --</option>
  <?php foreach ($opt_income_source as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
    <select id="employment_type" name="employment_type" class="form-select" required>
  <option value="">-- Select Employment Type --</option>
  <?php foreach ($opt_employment_type as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
    <select id="socio_economic_class" name="socio_economic_class" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_ses_class as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
    <label for="registration_date" class="form-label">Registration Date</label>
    <input type="date" id="registration_date" name="registration_date" class="form-control">
</div>

<div class="col-md-4">
    <label for="status" class="form-label">Status</label>
    <select id="status" name="status" class="form-select">
        <option value="Active" selected>Active</option>
        <option value="Inactive">Inactive</option>
        <option value="Dissolved">Dissolved</option>
    </select>
</div>

<!-- ===================== UTILITIES ===================== -->
<div class="col-12 mt-2">
    <h5 class="mb-0">Utilities</h5>
    <hr class="mt-2">
</div>

<div class="col-md-4">
 <select name="house_material" id="house_material" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_house_material as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
  <select name="water_source" id="water_source" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_water_source as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-4">
  <select name="electricity_access" id="electricity_access" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_electricity_access as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<div class="col-md-4">
  <select name="toilet_facility" id="toilet_facility" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_toilet_facility as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>


<div class="col-md-4">
  <select name="internet_access" id="internet_access" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_internet_access as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>

</div>

<!-- ===================== GOVERNMENT PROGRAMS ===================== -->
<div class="col-12 mt-2">
  <h5 class="mb-0">Government Programs</h5>
  <hr class="mt-2">
</div>

<div class="col-md-3">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="is_4ps_beneficiary" name="is_4ps_beneficiary" value="1">
    <label class="form-check-label" for="is_4ps_beneficiary">4Ps Beneficiary</label>
  </div>
</div>

<div class="col-md-3">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="is_social_pension" name="is_social_pension" value="1">
    <label class="form-check-label" for="is_social_pension">Social Pension</label>
  </div>
</div>

<div class="col-md-3">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="is_tupad_beneficiary" name="is_tupad_beneficiary" value="1">
    <label class="form-check-label" for="is_tupad_beneficiary">TUPAD Beneficiary</label>
  </div>
</div>

<div class="col-md-3">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="is_akap_beneficiary" name="is_akap_beneficiary" value="1">
    <label class="form-check-label" for="is_akap_beneficiary">AKAP Beneficiary</label>
  </div>
</div>

<div class="col-md-4">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="is_solo_parent_assistance" name="is_solo_parent_assistance" value="1">
    <label class="form-check-label" for="is_solo_parent_assistance">Solo Parent Assistance</label>
  </div>
</div>

<!-- ===================== HOUSEHOLD ASSETS ===================== -->
<div class="col-12 mt-2">
  <h5 class="mb-0">Household Assets</h5>
  <hr class="mt-2">
</div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_vehicle" name="has_vehicle" value="1">
  <label class="form-check-label" for="has_vehicle">Has Vehicle</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_motorcycle" name="has_motorcycle" value="1">
  <label class="form-check-label" for="has_motorcycle">Has Motorcycle</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_refrigerator" name="has_refrigerator" value="1">
  <label class="form-check-label" for="has_refrigerator">Has Refrigerator</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_tv" name="has_tv" value="1">
  <label class="form-check-label" for="has_tv">Has TV</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_washing_machine" name="has_washing_machine" value="1">
  <label class="form-check-label" for="has_washing_machine">Has Washing Machine</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_aircon" name="has_aircon" value="1">
  <label class="form-check-label" for="has_aircon">Has Aircon</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_computer" name="has_computer" value="1">
  <label class="form-check-label" for="has_computer">Has Computer</label>
</div></div>

<div class="col-md-3"><div class="form-check">
  <input class="form-check-input" type="checkbox" id="has_smartphone" name="has_smartphone" value="1">
  <label class="form-check-label" for="has_smartphone">Has Smartphone</label>
</div></div>



<!-- ===================== OTHER INFO ===================== -->
<div class="col-12 mt-2">
    <h5 class="mb-0">Other Information</h5>
    <hr class="mt-2">
</div>

<div class="col-md-4">
  <select name="land_ownership" id="land_ownership" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_land_ownership as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-4">
  <select name="business_ownership" id="business_ownership" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_business_ownership as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-4">
 <select name="highest_education" id="highest_education" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_highest_education as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-4">
  <select name="health_insurance" id="health_insurance" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_health_insurance as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-4">
 <select name="malnutrition_cases" id="malnutrition_cases" class="form-select">
  <option value="">Select</option>
  <?php foreach ($opt_malnutrition_cases as $v): ?>
    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
  <?php endforeach; ?>
</select>
</div>


<div class="col-md-12">
    <label for="remarks" class="form-label">Remarks</label>
    <textarea id="remarks" name="remarks" class="form-control" rows="3"></textarea>
</div>


        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Save Household</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>