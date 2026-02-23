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
$resident_name = $doc['resident_name'] ?? '';
$resident_address = $doc['resident_address'] ?? '';
$purpose = $doc['purpose'] ?? '';

$cert_no = $doc['cert_no'] ?? '';
$or_no = $doc['or_no'] ?? '';
$amount = isset($doc['amount_paid']) ? number_format((float)$doc['amount_paid'], 2) : '';
$date_paid = !empty($doc['date_paid']) ? date('F d, Y', strtotime($doc['date_paid'])) : '';

$resident_photo_url = trim((string)($doc['resident_photo_url'] ?? ''));
$resident_thumb_url = trim((string)($doc['resident_thumb_url'] ?? ''));

if (empty($resident_photo_url) && !empty($doc['resident_photo'])) {
    $resident_photo_url = '/BIS/uploads/residents/' . rawurlencode((string)$doc['resident_photo']);
}

if (empty($resident_thumb_url) && !empty($doc['resident_thumbmark'])) {
    $resident_thumb_url = '/BIS/uploads/thumbmarks/' . rawurlencode((string)$doc['resident_thumbmark']);
}

$officialsModel = new Official($db);
$officialRows = $officialsModel->all();
$officials_list = [];

foreach ($officialRows as $row) {
    if (($row['status'] ?? 'Active') !== 'Active') {
        continue;
    }

    $officials_list[] = [
        'name' => 'Hon. ' . trim((string)($row['full_name'] ?? '')),
        'position' => trim((string)($row['position'] ?? '')),
        'committee' => trim((string)($row['committee'] ?? '')),
    ];
}

/* ======= TEMPLATE SELECTION ======= */
$type = strtolower(trim($doc['document_name'] ?? ''));

if (strpos($type, 'clearance') !== false) {
    $file = 'cert_clearance.php';
    $layout = 'layout_clearance.php';
    $pdfName = 'barangay_clearance.pdf';
} elseif (strpos($type, 'cohabitation') !== false || strpos($type, 'live in') !== false) {
    $file = 'cert_livein.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_livein.pdf';
} elseif (strpos($type, 'guardian') !== false) {
    $file = 'cert_guardian.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_guardian.pdf';
} elseif (strpos($type, 'residency') !== false) {
    $file = 'cert_residency.php';
    $layout = 'layout_certificate.php';
    $pdfName = 'cert_residency.pdf';
} else {
    $file = 'cert_residency.php';
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
$title = $doc['document_name'] ?? 'Document';

ob_start();
require __DIR__ . '/../views/print/' . $layout;
$html = ob_get_clean();

// 3) dompdf
$chroot = realpath(__DIR__ . '/..'); // BIS root folder

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot($chroot); // IMPORTANT: allow local images

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream($pdfName, ['Attachment' => false]);
exit;