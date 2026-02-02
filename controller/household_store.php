<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Household.php';

// Admin guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /BIS/views/household_add.php");
    exit();
}

// CSRF check
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error'] = "Invalid request (CSRF). Please try again.";
    header("Location: /BIS/views/household_add.php");
    exit();
}

// Helper to safely get trimmed strings
$val = function (string $key): ?string {
    if (!isset($_POST[$key])) return null;
    $v = trim((string)$_POST[$key]);
    return $v === '' ? null : $v;
};

// Helper to get integer
$intVal = function (string $key): ?int {
    if (!isset($_POST[$key])) return null;
    $v = trim((string)$_POST[$key]);
    if ($v === '') return null;
    return is_numeric($v) ? (int)$v : null;
};

// Helper to get checkbox (tinyint)
$boolVal = function (string $key): int {
    return !empty($_POST[$key]) ? 1 : 0;
};

try {
    $householdModel = new Household($conn);

    // REQUIRED fields
    $household_code = $val('household_code');
    $purok_id = $intVal('purok_id');
    $address_line = $val('address_line');

    if (!$household_code || !$purok_id || !$address_line) {
        $_SESSION['error'] = "Please fill in Household Code, Purok, and Address.";
        header("Location: /BIS/views/household_add.php");
        exit();
    }

    // Build data array for model
    $data = [
        'household_code' => $household_code,
        'purok_id' => $purok_id,
        'address_line' => $address_line,

        // Optional basic fields
        'housing_type' => $val('housing_type'),
        'head_resident_id' => $intVal('head_resident_id'),
        'years_residing' => $intVal('years_residing'),
        'landmark' => $val('landmark'),

        // Household details
        'household_type' => $val('household_type'),
        'tenure_status' => $val('tenure_status'),
        'housing_status' => $val('housing_status'),

        // Socio-economic
        'monthly_income_range' => $val('monthly_income_range'),
        'income_source' => $val('income_source'),
        'employment_type' => $val('employment_type'),
        'socio_economic_class' => $val('socio_economic_class'),
        'registration_date' => $val('registration_date'),

        // Programs (checkbox)
        'is_4ps_beneficiary' => $boolVal('is_4ps_beneficiary'),
        'is_social_pension' => $boolVal('is_social_pension'),
        'is_tupad_beneficiary' => $boolVal('is_tupad_beneficiary'),
        'is_akap_beneficiary' => $boolVal('is_akap_beneficiary'),
        'is_solo_parent_assistance' => $boolVal('is_solo_parent_assistance'),

        // Utilities
        'house_material' => $val('house_material'),
        'water_source' => $val('water_source'),
        'electricity_access' => $val('electricity_access'),
        'toilet_facility' => $val('toilet_facility'),
        'internet_access' => $val('internet_access'),

        // Assets (checkbox)
        'has_vehicle' => $boolVal('has_vehicle'),
        'has_motorcycle' => $boolVal('has_motorcycle'),
        'has_refrigerator' => $boolVal('has_refrigerator'),
        'has_tv' => $boolVal('has_tv'),
        'has_washing_machine' => $boolVal('has_washing_machine'),
        'has_aircon' => $boolVal('has_aircon'),
        'has_computer' => $boolVal('has_computer'),
        'has_smartphone' => $boolVal('has_smartphone'),

        // Other
        'land_ownership' => $val('land_ownership'),
        'business_ownership' => $val('business_ownership'),
        'highest_education' => $val('highest_education'),
        'health_insurance' => $val('health_insurance'),
        'malnutrition_cases' => $val('malnutrition_cases'),
        'remarks' => $val('remarks'),

        // System
        'status' => $val('status') ?? 'Active',
        'created_by' => (int)$_SESSION['user_id'],
    ];

    // Save
    $ok = $householdModel->create($data);

    if ($ok) {
        $_SESSION['success'] = "Household saved successfully!";
        header("Location: /BIS/controller/households_manage.php");
        exit();
    }

    $_SESSION['error'] = "Failed to save household. Please check logs or try again.";
    header("Location: /BIS/views/household_add.php");
    exit();

} catch (Throwable $e) {
    error_log("household_store error: " . $e->getMessage());
    $_SESSION['error'] = "Something went wrong while saving. Please try again.";
    header("Location: /BIS/views/household_add.php");
    exit();
}
