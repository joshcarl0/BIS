<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "bis_db";

try {
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset('utf8mb4');

    //  ADD THIS LINE:
    $db = $conn;

} catch (mysqli_sql_exception $e) {
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}
