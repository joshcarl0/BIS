<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /BIS/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// NOTE: support both $conn and $db (depende sa database.php mo)
$conn = $conn ?? $db ?? null;
if (!$conn) {
    http_response_code(500);
    exit('Database connection not found.');
}

header("Content-Type: text/csv; charset=utf-8"); // important for Excel to recognize UTF-8
header("Content-Disposition: attachment; filename=households_export_" . date('Y-m-d') . ".csv"); // filename with date for uniqueness

$output = fopen('php://output', 'w'); // Check if output stream opened successfully
if ($output === false) {
    http_response_code(500);
    exit;
}

// UTF-8 BOM for Excel
fwrite($output, "\xEF\xBB\xBF");

// Columns (dapat match sa households table mo)
$columns = [
    'id',
    'household_code',
    'purok_id',
    'address_line',
    'housing_type',
    'years_residing',
    'landmark',
    'household_type',
    'tenure_status',
    'monthly_income_range',
    'income_source',
    'employment_type',
    'socio_economic_class',
    'is_4ps_beneficiary',
    'is_social_pension',
    'is_tupad_beneficiary',
    'is_akap_beneficiary',
    'is_solo_parent_assistance',
    'housing_status',
    'house_material',
    'water_source',
    'electricity_access',
    'toilet_facility',
    'internet_access',
    'has_vehicle',
    'has_motorcycle',
    'has_refrigerator',
    'has_tv',
    'has_washing_machine',
    'has_aircon',
    'has_computer',
    'has_smartphone',
    'land_ownership',
    'business_ownership',
    'highest_education',
    'health_insurance',
    'malnutrition_cases',
    'registration_date',
    'remarks',
    'status',
    'created_at'
];

// header ONCE
fputcsv($output, $columns);

$sql = "SELECT " . implode(", ", $columns) . " FROM households ORDER BY id ASC"; // adjust table/column names as needed
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    exit('Query failed: ' . $conn->error);
}

while ($row = $result->fetch_assoc()) {

    $data = [];

    foreach ($columns as $col) {
        $value = $row[$col] ?? '';

        // Format date fields
        if (in_array($col, ['registration_date', 'created_at'], true) && !empty($value)) {
            $ts = strtotime($value);
            if ($ts !== false) $value = date('Y-m-d', $ts);
        }

        // OPTIONAL: if you have phone fields in household (uncomment if meron)
        // if ($col === 'contact_number') {
        //     $value = "'" . $value; // force text in Excel
        // }

        // Protect against CSV/Excel formula injection
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            $value = "'" . $value;
        }

        $data[] = $value; // add to row data
    }

    fputcsv($output, $data); // write row to CSV 
}

exit;