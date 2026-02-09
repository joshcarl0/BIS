<?php
require_once __DIR__ . '/../config/database.php';

// delete logs older than 1 day
$sql = "DELETE FROM activity_logs WHERE created_at < NOW() - INTERVAL 1 DAY";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->execute();
echo "Deleted rows: " . $stmt->affected_rows;
