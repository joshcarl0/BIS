<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';



/* ==========================
   HELPER
========================== */
function countQuery(mysqli $conn, string $sql): int {
    $res = $conn->query($sql);
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    return (int)($row['total'] ?? 0);
}

/* ==========================
   RESIDENTS
========================== */
$totalResidents = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active = 1
");

$maleResidents = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active = 1 AND (sex='Male' OR sex='M')
");

$femaleResidents = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active = 1 AND (sex='Female' OR sex='F')
");

/* ==========================
   AGE GROUPS
========================== */
$ageMinors = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active=1
      AND birthdate IS NOT NULL
      AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 17
");

$ageAdults = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active=1
      AND birthdate IS NOT NULL
      AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59
");

$ageSeniors = countQuery($conn, "
    SELECT COUNT(*) AS total
    FROM residents
    WHERE is_active=1
      AND birthdate IS NOT NULL
      AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60
");

/* ==========================
   SPECIAL GROUPS
   tables:
   - special_groups(id, name)
   - resident_special_groups(resident_id, group_id)
========================== */
$specialGroups = [];

$sqlSG = "
    SELECT sg.name, COUNT(*) AS total
    FROM resident_special_groups rsg
    JOIN special_groups sg ON sg.id = rsg.group_id
    JOIN residents r ON r.id = rsg.resident_id
    WHERE r.is_active = 1
    GROUP BY sg.name
    ORDER BY total DESC
";

$sgRes = $conn->query($sqlSG);
if ($sgRes) {
    while ($row = $sgRes->fetch_assoc()) {
        $name = (string)($row['name'] ?? '');
        if ($name !== '') {
            $specialGroups[$name] = (int)($row['total'] ?? 0);
        }
    }
}

/* ==========================
   HOUSEHOLDS
========================== */
$totalHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households");
$activeHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status='Active'");
$inactiveHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status='Inactive'");
$dissolvedHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status='Dissolved'");

/* ==========================
   PROGRAMS
========================== */
$prog4ps = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_4ps_beneficiary=1");
$progTupad = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_tupad_beneficiary=1");
$progAkap = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_akap_beneficiary=1");
$progSolo = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_solo_parent_assistance=1");
$progPension = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_social_pension=1");

/* ==========================
   SES
========================== */
$ses = [
    'Poverty' => 0,
    'Low Income' => 0,
    'Middle Income' => 0,
    'High Income' => 0
];

$sesRes = $conn->query("
    SELECT socio_economic_class, COUNT(*) AS total
    FROM households
    WHERE socio_economic_class IS NOT NULL
      AND socio_economic_class <> ''
    GROUP BY socio_economic_class
");

if ($sesRes) {
    while ($row = $sesRes->fetch_assoc()) {
        $k = trim((string)($row['socio_economic_class'] ?? ''));
        if (isset($ses[$k])) $ses[$k] = (int)($row['total'] ?? 0);
    }
}

$sesTotal = array_sum($ses);
function pct(int $part, int $whole): string {
    if ($whole <= 0) return "0%";
    return round(($part / $whole) * 100, 1) . "%";
}

/* ==========================
   LOAD VIEW
========================== */
require_once __DIR__ . '/../views/admin/population_dashboard.php';
