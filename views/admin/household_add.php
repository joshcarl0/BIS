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
    <label for="housing_type" class="form-label">Housing Type</label>
    <select id="housing_type" name="housing_type" class="form-select">
  <option value="">Select</option>
  <option value="Single Detached House">Single Detached House</option>
  <option value="Apartment">Apartment</option>
  <option value="Boarding House">Boarding House</option>
  <option value="Room for Rent">Room for Rent</option>
  <option value="Condominium">Condominium</option>
  <option value="Others">Others</option>
</select>

</div>

<div class="col-md-3">
    <label for="household_type" class="form-label">Household Type</label>
    <select id="household_type" name="household_type" class="form-select">
        <option value="">Select</option>
        <option value="Nuclear">Nuclear</option>
        <option value="Single Parent">Single Parent</option>
        <option value="Extended">Extended</option>
        <option value="Others">Others</option>
    </select>
</div>

<div class="col-md-3">
    <label for="tenure_status" class="form-label">Tenure Status</label>
    <select id="tenure_status" name="tenure_status" class="form-select">
        <option value="">Select</option>
        <option value="Owner">Owner</option>
        <option value="Renter">Renter</option>
        <option value="Others">Others</option>
    </select>
</div>

<div class="col-md-4">
    <label for="housing_status" class="form-label">Housing Status</label>
    <select id="housing_status" name="housing_status" class="form-select">
        <option value="">Select</option>
        <option value="Owned">Owned</option>
        <option value="Rented">Rented</option>
        <option value="Informal Settler">Informal Settler</option>
        <option value="Living with Relatives">Living with Relatives</option>
    </select>
</div>

<!-- ===================== SOCIO-ECONOMIC ===================== -->
<div class="col-12 mt-2">
    <h5 class="mb-0">Socio-Economic</h5>
    <hr class="mt-2">
</div>

<div class="col-md-4">
    <label for="monthly_income_range" class="form-label">Monthly Income Range</label>
    <select id="monthly_income_range" name="monthly_income_range" class="form-select" required>
        <option value="">-- Select Income Range --</option>
        <option value="Below 10,000">Below ₱10,000</option>
        <option value="10,000 - 20,000">₱10,000 – ₱20,000</option>
        <option value="20,001 - 30,000">₱20,001 – ₱30,000</option>
        <option value="30,001 - 50,000">₱30,001 – ₱50,000</option>
        <option value="Above 50,000">Above ₱50,000</option>
    </select>
</div>

<div class="col-md-4">
    <label for="income_source" class="form-label">Income Source</label>
    <select id="income_source" name="income_source" class="form-select" required>
        <option value="">-- Select Income Source --</option>
        <option value="Salary / Wages">Salary / Wages</option>
        <option value="Business / Self-Employed">Business / Self-Employed</option>
        <option value="Daily Wage / Labor">Daily Wage / Labor</option>
        <option value="Fishing / Aquaculture">Fishing / Aquaculture</option>
        <option value="Transportation Services">Transportation Services</option>
        <option value="OFW / Remittance">OFW / Remittance</option>
        <option value="Pension">Pension</option>
        <option value="Financial Assistance">Financial Assistance</option>
        <option value="Freelance / Online Work">Freelance / Online Work</option>
        <option value="No Regular Income">No Regular Income</option>
    </select>
</div>

<div class="col-md-4">
    <label for="employment_type" class="form-label">Employment Type</label>
    <select id="employment_type" name="employment_type" class="form-select" required>
        <option value="">-- Select Employment Type --</option>
        <option value="Regular / Permanent">Regular / Permanent</option>
        <option value="Contractual">Contractual</option>
        <option value="Casual / Seasonal">Casual / Seasonal</option>
        <option value="Self-Employed">Self-Employed</option>
        <option value="Freelancer / Gig Worker">Freelancer / Gig Worker</option>
        <option value="Daily Wage Earner">Daily Wage Earner</option>
        <option value="Government Employee">Government Employee</option>
        <option value="Private Employee">Private Employee</option>
        <option value="Unemployed">Unemployed</option>
        <option value="Student">Student</option>
        <option value="Retired / Pensioner">Retired / Pensioner</option>
    </select>
</div>

<div class="col-md-4">
    <label for="socio_economic_class" class="form-label">Socio-Economic Class</label>
    <select id="socio_economic_class" name="socio_economic_class" class="form-select">
        <option value="">Select</option>
        <option value="Poverty">Poverty</option>
        <option value="Low Income">Low Income</option>
        <option value="Middle Income">Middle Income</option>
        <option value="High Income">High Income</option>
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
  <label for="house_material" class="form-label">House Material</label>
  <select name="house_material" id="house_material" class="form-select">
    <option value="">Select</option>
    <option value="Concrete">Concrete</option>
    <option value="Wood">Wood</option>
    <option value="Mixed">Mixed</option>
    <option value="Light Materials">Light Materials</option>
  </select>
</div>

<div class="col-md-4">
  <label for="water_source" class="form-label">Water Source</label>
  <select name="water_source" id="water_source" class="form-select">
    <option value="">Select</option>
    <option value="Maynilad">Maynilad</option>
    <option value="Manila Water">Manila Water</option>
    <option value="Deepwell">Deepwell</option>
    <option value="Communal Faucet">Communal Faucet</option>
    <option value="Refilling Station">Refilling Station</option>
    <option value="Others">Others</option>
  </select>
</div>


<div class="col-md-4">
  <label for="electricity_access" class="form-label">Electricity Access</label>
  <select name="electricity_access" id="electricity_access" class="form-select">
    <option value="">Select</option>
    <option value="Meralco">Meralco</option>
    <option value="Solar Panel">Solar Panel</option>
    <option value="Generator">Generator</option>
    <option value="Shared Connection">Shared Connection</option>
    <option value="None">None</option>
  </select>
</div>

<div class="col-md-4">
  <label for="toilet_facility" class="form-label">Toilet Facility</label>
  <select name="toilet_facility" id="toilet_facility" class="form-select">
    <option value="">Select</option>
    <option value="Own Toilet">Own Toilet</option>
    <option value="Shared Toilet">Shared Toilet</option>
    <option value="Public Toilet">Public Toilet</option>
    <option value="None">None</option>
  </select>
</div>


<div class="col-md-4">
  <label for="internet_access" class="form-label">Internet Access</label>
  <select name="internet_access" id="internet_access" class="form-select">
    <option value="">Select</option>
    <option value="Fiber">Fiber</option>
    <option value="DSL">DSL</option>
    <option value="Mobile Data">Mobile Data</option>
    <option value="WiFi (Shared)">WiFi (Shared)</option>
    <option value="None">None</option>
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
  <label for="land_ownership" class="form-label">Land Ownership</label>
  <select name="land_ownership" id="land_ownership" class="form-select">
    <option value="">Select</option>
    <option value="Owned">Owned</option>
    <option value="Leased">Leased</option>
    <option value="Government Owned">Government Owned</option>
    <option value="Informal Settler">Informal Settler</option>
    <option value="Others">Others</option>
  </select>
</div>


<div class="col-md-4">
  <label for="business_ownership" class="form-label">Business Ownership</label>
  <select name="business_ownership" id="business_ownership" class="form-select">
    <option value="">Select</option>
    <option value="None">None</option>
    <option value="Sari-sari Store">Sari-sari Store</option>
    <option value="Small Business">Small Business</option>
    <option value="Registered Business">Registered Business</option>
    <option value="Others">Others</option>
  </select>
</div>


<div class="col-md-4">
  <label for="highest_education" class="form-label">Highest Education</label>
  <select name="highest_education" id="highest_education" class="form-select">
    <option value="">Select</option>
    <option value="No Formal Education">No Formal Education</option>
    <option value="Elementary Level">Elementary Level</option>
    <option value="Elementary Graduate">Elementary Graduate</option>
    <option value="High School Level">High School Level</option>
    <option value="High School Graduate">High School Graduate</option>
    <option value="College Level">College Level</option>
    <option value="College Graduate">College Graduate</option>
    <option value="Post Graduate">Post Graduate</option>
  </select>
</div>


<div class="col-md-4">
  <label for="health_insurance" class="form-label">Health Insurance</label>
  <select name="health_insurance" id="health_insurance" class="form-select">
    <option value="">Select</option>
    <option value="PhilHealth">PhilHealth</option>
    <option value="HMO">HMO</option>
    <option value="Both">PhilHealth & HMO</option>
    <option value="None">None</option>
  </select>
</div>


<div class="col-md-4">
  <label for="malnutrition_cases" class="form-label">Malnutrition Cases</label>
  <select name="malnutrition_cases" id="malnutrition_cases" class="form-select">
    <option value="">Select</option>
    <option value="None">None</option>
    <option value="At Risk">At Risk</option>
    <option value="Moderate">Moderate</option>
    <option value="Severe">Severe</option>
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