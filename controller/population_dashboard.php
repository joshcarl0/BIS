<?php
session_start() ;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php") ;
    exit ;
}

require_once __DIR__ . '/../config/database.php' ;



//*** run counting  COUNT(*) query */

function countQuery(mysqli $conn, string $sql): int {
    $res = $conn->query($sql);
    if (!$res) return 0;

    $row = $res->fetch_assoc();
    return (int)($row['total'] ?? 0);
}



//****==========================    People Overview (RESIDENT)       ==================****///

// total active residents

$totalResidents = countQuery($conn, "
    SELECT COUNT(*) AS total 
    FROM residents 
    WHERE is_active = 1 AND (sex = 'Male' OR sex = 'Female')
") ;

// male / female (adjust if your data has other values)

$maleResidents = countQuery($conn, "
    SELECT COUNT(*) AS total 
    FROM residents 
    WHERE is_active = 1 AND (sex = 'Male' OR sex = 'M')
") ;

$femaleResidents = countQuery($conn, "
    SELECT COUNT(*) AS total 
    FROM residents 
    WHERE is_active = 1 AND (sex = 'Female' OR sex = 'F')
") ;

// Age groups (computed from birthdate) - requires valid birthdate values and birthdate not null

$ageMinors = (int)($conn->query("
  SELECT COUNT(*) c FROM residents
  WHERE is_active=1 AND birthdate IS NOT NULL
    AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 17
")->fetch_assoc()['c'] ?? 0);

$ageAdults = (int)($conn->query("
  SELECT COUNT(*) c FROM residents
  WHERE is_active=1 AND birthdate IS NOT NULL
    AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59
")->fetch_assoc()['c'] ?? 0);

$ageSeniors = (int)($conn->query("
  SELECT COUNT(*) c FROM residents
  WHERE is_active=1 AND birthdate IS NOT NULL
    AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60
")->fetch_assoc()['c'] ?? 0);


/* ==========================    
SPECIAL GROUPS If you have special groups defined, e.g., Persons with Disability (PWD), Senior Citizens (SC), etc.,
you can count them here. This assumes you have a 'special_groups' table and a many-to-many relationship table 'resident_special_groups'.
Adjust the table and column names as per your database schema.
       ==================****///


$specialGroups = [
    'PWD' => 0,
    'Senior Citizen' => 0,
    'Solo Parent' => 0,
    'Pregnant' => 0,
    'Lactating Mother' => 0,
];

$sqlSG = "
    SELECT sg.name, COUNT(*) AS total
    FROM resident_special_groups rsg
    JOIN special_groups sg ON sg.id = rsg.group_id
    JOIN residents r ON r.id = rsg.resident_id
    WHERE r.is_active = 1
    GROUP BY sg.name
";

$sgRes = $conn->query($sqlSG);

if ($sgRes) {
    while ($row = $sgRes->fetch_assoc()) {
        $name = $row['name'] ?? '';
        if ($name !== '') {
            $specialGroups[$name] = (int)($row['total'] ?? 0);
        }
    }
} else {
    // optional: for debugging
    // die("SG query error: " . $conn->error);
}






/*==========================    
Household Overview
       ==================****/// 

$totalHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households") ;
$activeHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status = 'Active'") ;
$inactiveHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status = 'Inactive'") ;
$dissolvedHouseholds = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE status = 'Dissolved'") ;

/*==========================
Program Counts (households)
       ==================****///

$prog4ps = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_4ps_beneficiary=1 ");
$progTupad = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_tupad_beneficiary=1 ");
$progAkap = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_akap_beneficiary=1 ");
$progSolo = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_solo_parent_assistance=1 ");
$progPension = countQuery($conn, "SELECT COUNT(*) AS total FROM households WHERE is_social_pension=1 ");

/* ===============  
SES: socio economic class distribution
===============*/

$ses = [
    'Poverty' => 0,
    'Low Income' => 0,
    'Middle Income' => 0,
    'High Income' => 0,
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
        $k = (string)($row['socio_economic_class'] ?? '');
        if (isset($ses[$k])) $ses[$k] = (int)$row['total'];
    }
}

$sesTotal = array_sum($ses);
function pct(int $part, int $whole): string {
    if ($whole <=0) return "0%";
    return round(($part / $whole) * 100, 1) . "%";
}

/*=================
VIEW Pass
=================*/

require_once __DIR__ . '/../views/admin/population_dashboard.php';

/*===============  
Done for a while
============*/

