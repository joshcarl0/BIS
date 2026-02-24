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
header("Content-Disposition: attachment; filename=residents.csv");

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit;
}

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
    'created_at',
];

fputcsv($output, $columns);

$sql = "SELECT id, resident_code, last_name, first_name, middle_name, suffix, sex, birthdate, contact_number, email, occupation, voter_status, is_head_of_household, is_active, created_at FROM residents ORDER BY id ASC";
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
