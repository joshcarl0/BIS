<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /BIS/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=households.csv");

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit;
}

fwrite($output, "\xEF\xBB\xBF");

$columns = [
    'id',
    'household_code',
    'address_line',
    'housing_type',
    'monthly_income_range',
    'socio_economic_class',
    'housing_status',
    'water_source',
    'electricity_access',
    'toilet_facility',
    'internet_access',
    'registration_date',
    'status',
    'created_at',
];

fputcsv($output, $columns);

$sql = "SELECT id, household_code, address_line, housing_type, monthly_income_range, socio_economic_class, housing_status, water_source, electricity_access, toilet_facility, internet_access, registration_date, status, created_at FROM households ORDER BY id ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    // Protect against CSV/Excel formula injection
    foreach ($row as $k => $v) {
        if (is_string($v) && preg_match('/^[=+\-@]/', $v)) {
            $row[$k] = "'" . $v;
        }
    }

    fputcsv($output, $row);
}
exit;
