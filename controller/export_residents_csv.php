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
header("Content-Disposition: attachment; filename=residents_export_" . date('Y-m-d') . ".csv");

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit;
}

// UTF-8 BOM (para di masira special characters sa Excel)
fwrite($output, "\xEF\xBB\xBF");

$columns = [
    'id',
    'resident_code',
    'last_name',
    'first_name',
    'middle_name',
    'suffix',
    'sex',
    'birthdate',
    'contact_number',
    'email',
    'occupation',
    'voter_status',
    'is_head_of_household',
    'is_active',
    'created_at'
];

// Print header ONCE
fputcsv($output, $columns);

$sql = "SELECT id, resident_code, last_name, first_name, middle_name, suffix, sex, birthdate, contact_number, email, occupation, voter_status, is_head_of_household, is_active, created_at 
        FROM residents 
        ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    exit;
}

while ($row = $result->fetch_assoc()) {

    $data = [];

    foreach ($columns as $col) {

        $value = $row[$col] ?? '';

        // Convert boolean fields to Yes/No
        if (in_array($col, ['is_head_of_household', 'is_active'])) {
            $value = $value == 1 ? 'Yes' : 'No';
        }

        // Format dates properly
        if (in_array($col, ['birthdate', 'created_at']) && !empty($value)) {
            $value = date('Y-m-d', strtotime($value));
        }

        // Prevent scientific notation for phone numbers
        if ($col === 'contact_number') {
            $value = "'" . $value;
        }

        // Protect against CSV/Excel formula injection
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            $value = "'" . $value;
        }

        $data[] = $value;
    }

    fputcsv($output, $data);
}

fclose($output);
exit;