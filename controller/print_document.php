<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DocumentRequest.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid request.");
}

$docReq = new DocumentRequest($db);
$doc = $docReq->findById($id);

if (!$doc) {
    die("Request not found.");
}

// Optional: allow print only if approved/released
if (!in_array($doc['status'] ?? '', ['Approved', 'Released'], true)) {
    die("This request is not approved yet.");
}

/* ======= VARIABLES FOR PRINT TEMPLATE ======= */
$resident_name    = $doc['resident_name'] ?? '';
$resident_address = $doc['resident_address'] ?? '';

$cert_no   = $doc['cert_no'] ?? '';
$or_no     = $doc['or_no'] ?? '';
$amount    = isset($doc['amount_paid']) ? number_format((float)$doc['amount_paid'], 2) : '';
$date_paid = !empty($doc['date_paid']) ? date('F d, Y', strtotime($doc['date_paid'])) : '';

/* ======= EXTRA DETAILS (dynamic fields) ======= */
$details = [];
$sqlD = "SELECT field_name, field_value
         FROM document_request_details
         WHERE request_id = ?";
$stmtD = $db->prepare($sqlD);
if ($stmtD) {
    $stmtD->bind_param("i", $id);
    $stmtD->execute();
    $resD = $stmtD->get_result();
    while ($r = $resD->fetch_assoc()) {
        $details[$r['field_name']] = $r['field_value'];
    }
    $stmtD->close();
}

/* ======= MAP DETAILS TO VARIABLES (templates use these) ======= */
// Cohabitation / Live-in
$partner_name = $details['partner_name'] ?? '';
$since        = $details['since'] ?? '';

// Guardianship
$child_name   = $details['child_name'] ?? '';
$child_dob    = $details['child_dob'] ?? '';
$child_pob    = $details['child_pob'] ?? '';
$mother_name  = $details['mother_name'] ?? '';
$father_name  = $details['father_name'] ?? '';

/* ======= TEMPLATE SELECTION ======= */
$type = strtolower(trim($doc['document_name'] ?? ''));

if (strpos($type, 'cohabitation') !== false || strpos($type, 'live in') !== false || strpos($type, 'live-in') !== false) {
    $file = 'cert_livein.php';

} elseif (strpos($type, 'guardian') !== false) {
    $file = 'cert_guardian.php';

} elseif (strpos($type, 'residency') !== false) {
    $file = 'cert_residency.php';

} elseif ($type === 'certification' || strpos($type, 'general') !== false) {
    $file = 'cert_general.php';

} else {
    $file = 'cert_residency.php'; // fallback
}

require_once __DIR__ . '/../views/print/' . $file;
exit;
