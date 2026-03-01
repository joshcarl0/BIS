<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DocumentRequest.php';
require_once __DIR__ . '/../models/Official.php';

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
$resident_name    = (string)($doc['resident_name'] ?? '');
$resident_address = (string)($doc['resident_address'] ?? '');
$purpose          = (string)($doc['purpose'] ?? 'LOCAL EMPLOYMENT');

$cert_no   = (string)($doc['cert_no'] ?? '');
$or_no     = (string)($doc['or_no'] ?? '');
$amount    = isset($doc['amount_paid']) ? number_format((float)$doc['amount_paid'], 2) : '';
$date_paid = !empty($doc['date_paid']) ? date('F d, Y', strtotime($doc['date_paid'])) : '';

/**
 * Photo + thumb sources (DOMPDF-safe relative paths)
 * NOTE: dompdf chroot is BIS root (../)
 */
$resident_photo_url = trim((string)($doc['resident_photo_url'] ?? ''));
$resident_thumb_url = trim((string)($doc['resident_thumb_url'] ?? ''));

if ($resident_photo_url === '' && !empty($doc['resident_photo'])) {
    $resident_photo_url = 'uploads/residents/' . rawurlencode((string)$doc['resident_photo']);
}
if ($resident_thumb_url === '' && !empty($doc['resident_thumbmark'])) {
    $resident_thumb_url = 'uploads/thumbmarks/' . rawurlencode((string)$doc['resident_thumbmark']);
}

/**
 * Map to template variable names (IMPORTANT)
 * Your cert_clearance.php uses $photo_src and $thumb_src
 */
$photo_src = $resident_photo_url;
$thumb_src = $resident_thumb_url;

/* ======= EXTRA CLEARANCE VARIABLES ======= */
$captain_name = 'MARILYN F. BURGOS'; // change if you make dynamic
$month = date('F');
$year  = date('Y');

/**
 * Optional aliases (if your template uses $name / $address)
 */
$name    = $resident_name;
$address = $resident_address;

/* ======= OFFICIALS LIST (LEFT PANEL) ======= */
$officialsModel = new Official($db);
$officialRows = $officialsModel->all();

$officials_list = [];
foreach ($officialRows as $row) {
    if (($row['status'] ?? 'Active') !== 'Active') continue;

    $fullName = trim((string)($row['full_name'] ?? ''));
    $pos      = trim((string)($row['position'] ?? ''));
    $comm     = trim((string)($row['committee'] ?? ''));

    $officials_list[] = [
        'name' => ($fullName !== '' ? 'Hon. ' . $fullName : ''),
        'position' => $pos,
        'committee' => $comm,
    ];
}

/* ======= TEMPLATE SELECTION ======= */
$type = strtolower(trim((string)($doc['document_name'] ?? '')));

if (strpos($type, 'clearance') !== false) {
    $file   = 'cert_clearance.php';
    $layout = 'layout_clearance.php';
    $pdfName = 'barangay_clearance.pdf';
} elseif (strpos($type, 'cohabitation') !== false || strpos($type, 'live in') !== false) {
    $file   = 'cert_livein.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_livein.pdf';
} elseif (strpos($type, 'guardian') !== false) {
    $file   = 'cert_guardian.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_guardian.pdf';
} elseif (strpos($type, 'residency') !== false) {
    $file   = 'cert_residency.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_residency.pdf';
} elseif (strpos($type, 'certification') !== false) {
    $file   = 'certification.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'certification.pdf';
} else {
    $file   = 'certification.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'certificate.pdf';
}

/* ======= RENDER HTML THEN PDF ======= */
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1) render content template -> $content
ob_start();
require __DIR__ . '/../views/print/' . $file;
$content = ob_get_clean();

// 2) wrap with layout -> $html
$title = (string)($doc['document_name'] ?? 'Document');
$doc_title = strtoupper(trim((string)($doc['document_name'] ?? 'CERTIFICATION')));

// watermark (DOMPDF-safe path relative to chroot)
$watermark_src = $watermark_src ?? '/assets/images/barangay_logo.png';

ob_start();
require __DIR__ . '/../views/print/' . $layout;
$html = ob_get_clean();

// 3) dompdf options
$chroot = realpath(__DIR__ . '/..'); // BIS root

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot($chroot);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// safe cleanup (single level only)
if (ob_get_length()) { ob_end_clean(); }

$dompdf->stream($pdfName, ['Attachment' => false]);
exit;