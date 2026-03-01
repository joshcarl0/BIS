<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DocumentRequest.php';
require_once __DIR__ . '/../models/Official.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid request.");

// SUPPORT BOTH $db and $conn
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) die("Database connection not found. Check database.php variable name (\$db or \$conn).");

$docReq = new DocumentRequest($mysqli);
$doc = $docReq->findById($id);
if (!$doc) die("Request not found.");

// allow print only if approved/released
if (!in_array($doc['status'] ?? '', ['Approved', 'Released'], true)) {
    die("This request is not approved yet.");
}

/* ===== Variables from DB ===== */
$resident_name    = (string)($doc['resident_name'] ?? '');
$resident_address = (string)($doc['resident_address'] ?? '');
$document_name =    (string)($doc['document_name'] ?? '');

$cert_no   = (string)($doc['cert_no'] ?? '');
$or_no     = (string)($doc['or_no'] ?? '');
$amount    = isset($doc['amount_paid']) ? number_format((float)$doc['amount_paid'], 2) : '';
$date_paid = !empty($doc['date_paid']) ? date('F d, Y', strtotime($doc['date_paid'])) : '';

/* ===== Photo + Thumb (DOMPDF-safe NO leading slash) ===== */
$photo = trim((string)($doc['resident_photo_url'] ?? ''));
$thumb = trim((string)($doc['resident_thumb_url'] ?? ''));

if ($photo === '' && !empty($doc['resident_photo'])) {
    $photo = 'uploads/residents/' . rawurlencode((string)$doc['resident_photo']);
}
if ($thumb === '' && !empty($doc['resident_thumbmark'])) {
    $thumb = 'uploads/thumbmarks/' . rawurlencode((string)$doc['resident_thumbmark']);
}

$photo_src = trim((string)($doc['clearance_photo'] ?? ''));
$thumb_src = trim((string)$thumb);

/* ===== extra vars ===== */
$captain_name = 'MARILYN F. BURGOS';

$issuedDate = $doc['requested_at'] ?? date('Y-m-d');

$day   = date('j', strtotime($issuedDate));
$month = date('F', strtotime($issuedDate));
$year  = date('Y', strtotime($issuedDate));

// aliases (in case templates use these)
$name    = $resident_name;
$address = $resident_address;

/* ===== officials (optional) ===== */
$officials_list = [];
try {
    $officialsModel = new Official($mysqli);
    $rows = $officialsModel->all();
    foreach ($rows as $row) {
        if (isset($row['status']) && $row['status'] !== 'Active') continue;

        $fullName = trim((string)($row['full_name'] ?? ''));
        $pos      = trim((string)($row['position'] ?? ''));
        $comm     = trim((string)($row['committee'] ?? ''));
    

        $officials_list[] = [
            'name'      => ($fullName !== '' ? 'Hon. ' . $fullName : ''),
            'position'  => $pos,
            'committee' => $comm,
        ];
    }
} catch (\Throwable $e) {
    $officials_list = [];
}

/* ===== Template selection ===== */
$type = strtolower(trim((string)($doc['document_name'] ?? '')));

if (strpos($type, 'clearance') !== false) {
    $file    = 'cert_clearance.php';
    $layout  = 'layout_clearance.php';
    $pdfName = 'barangay_clearance.pdf';
} elseif (strpos($type, 'residency') !== false) {
    $file    = 'cert_residency.php';
    $layout  = 'layout_certificate.php';
    $pdfName = 'cert_residency.pdf';
} elseif (strpos($type, 'guardian') !== false) {
    $file    = 'cert_guardian.php';
    $layout  = 'layout_certificate.php';
    $pdfName = 'cert_guardian.pdf';
} elseif (strpos($type, 'cohabitation') !== false || strpos($type, 'live in') !== false) {
    $file    = 'cert_livein.php';
    $layout  = 'layout_certificate.php';
    $pdfName = 'cert_livein.pdf';
} else {
    $file    = 'certification.php';
    $layout  = 'layout_certificate.php';
    $pdfName = 'certificate.pdf';
}

/* ===== Validate paths ===== */
$viewFilePath   = __DIR__ . '/../views/print/' . $file;
$layoutFilePath = __DIR__ . '/../views/print/' . $layout;

if (!is_file($viewFilePath))   die("Template missing: views/print/" . htmlspecialchars($file));
if (!is_file($layoutFilePath)) die("Layout missing: views/print/" . htmlspecialchars($layout));

/* ===== DOMPDF render ===== */
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$title     = (string)($doc['document_name'] ?? 'Document');
$doc_title = strtoupper(trim((string)($doc['document_name'] ?? 'CERTIFICATE')));


$imgBarangay   =  'assets/images/barangay_logo.png';
$imgCity       = 'assets/images/city_logo.png';
$imgBagong     = 'assets/images/bagong_pilipinas.png';
$watermark_src = 'assets/images/barangay_logo.png';

// 1) content
ob_start();
require $viewFilePath;
$content = ob_get_clean();

// 2) layout wrapper (must echo <?= $content )
ob_start();
require $layoutFilePath;
$html = ob_get_clean();

$chroot = realpath(__DIR__ . '/..'); // BIS root

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot($chroot);

$dompdf = new Dompdf($options);

// IMPORTANT LINE
$dompdf->setBasePath($chroot);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream($pdfName, ['Attachment' => false]);
exit;
?>