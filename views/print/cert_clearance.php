<?php
// expected vars: $doc_title, $name, $address, $purpose, $day,$month,$year,
// $captain_name, $photo_src, $thumb_src, $cert_no,$or_no,$amount,$date_paid

function bis_to_data_uri($src) {
    if (empty($src)) return '';

    $candidate = str_replace('\\', '/', (string)$src);

    if (preg_match('/^[A-Za-z]:[\/\\\\]/', $candidate)) {
        $absPath = $candidate;
    } elseif (strpos($candidate, '/BIS/') === 0) {
        $absPath = $_SERVER['DOCUMENT_ROOT'] . $candidate;
    } else {
        $absPath = $_SERVER['DOCUMENT_ROOT'] . '/BIS/' . ltrim($candidate, '/');
    }

    if (!file_exists($absPath)) {
        return '';
    }

    $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

    if ($ext === 'png') {
        $mime = 'image/png';
    } elseif ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
    } else {
        return '';
    }

    $data = @file_get_contents($absPath);
    if ($data === false) return '';

    return 'data:' . $mime . ';base64,' . base64_encode($data);
}

$photoFile = bis_to_data_uri($photo_src ?? '');
$thumbFile = bis_to_data_uri($thumb_src ?? '');

$rawAddress = trim((string)($address ?? ''));


if (strcasecmp($rawAddress, 'Purok') === 0) {
    $rawAddress = '';
}


$cleanAddress = preg_replace('/\bBarangay Don Galo\b/i', '', $rawAddress);
$cleanAddress = preg_replace('/\bDon Galo\b/i', '', $cleanAddress);
$cleanAddress = preg_replace('/\bParañaque City\b/i', '', $cleanAddress);
$cleanAddress = preg_replace('/\bParanaque City\b/i', '', $cleanAddress);


$cleanAddress = preg_replace('/\s*,\s*/', ', ', $cleanAddress);
$cleanAddress = preg_replace('/,+/', ',', $cleanAddress);
$cleanAddress = trim($cleanAddress, " ,");

$fullAddress = $cleanAddress !== ''
    ? $cleanAddress . ', Barangay Don Galo, Parañaque City'
    : 'Barangay Don Galo, Parañaque City';
?>
<style>
  .title { text-align:center; font-size:18pt; font-weight:900; text-decoration:underline; margin:2mm 0 3mm 0; }
  .sub { font-weight:700; font-size:10.5pt; margin:0 0 3mm 0; }
  .p { font-size:10.5pt; line-height:1.35; text-align:justify; margin:0 0 3mm 0; }

  .sign-name { margin-top:8mm; text-align:right; font-weight:bold; }
  .sign-pos { text-align:right; font-style:italic; margin-top:1mm; }

  .box-wrap { width:76mm; margin-top:6mm; }
  .box-row { table-layout:fixed; width:76mm; border-collapse:collapse; }
  .box { width:38mm; height:38mm; overflow:hidden; border:1pt solid #000; vertical-align:middle; }
  .box img {
    width:38mm;
    height:38mm;
    display:block;
    object-fit:cover;
  }

  .sigline {
    margin-top:4mm;
    border-top:1pt solid #000;
    width:90mm;
    margin-left:auto;
    text-align:center;
    font-size:9pt;
    padding-top:2mm;
  }

  .receipt { margin-top:5mm; font-size:9pt; }
  .receipt td { padding:0.5mm 0; }
  .rlabel { width:40mm; font-weight:bold; }
  .rcolon { width:4mm; text-align:center; font-weight:bold; }
  .rline { border-bottom:0.6pt solid #000; width:55mm; height:4mm; }
</style>

<div class="title">BARANGAY CLEARANCE</div>
<div class="sub">TO WHOM IT MAY CONCERN:</div>

<p class="p">
  This is to certify that <b><?= htmlspecialchars($name ?? '') ?></b>,
  whose photograph, signature and right thumb mark appears below,
  is a bonafide resident of <b><?= htmlspecialchars($fullAddress) ?></b>.
</p>

<p class="p">
  This certification is issued upon the request of the above-mentioned individual
  for the purpose of <b><?= htmlspecialchars($purpose ?? '') ?></b>
  and valid only for three (3) months from date issued.
</p>

<p class="p">
  Issued this <?= htmlspecialchars($day ?? '') ?> day of <?= htmlspecialchars($month ?? '') ?>, <?= htmlspecialchars($year ?? '') ?>
  in Barangay Don Galo City of Parañaque.
</p>

<div class="sign-name"><?= htmlspecialchars($captain_name ?? '') ?></div>
<div class="sign-pos">Punong Barangay</div>

<table class="box-row">
  <tr>
    <td class="box">
      <?php if (!empty($photoFile)): ?>
        <img src="<?= htmlspecialchars($photoFile, ENT_QUOTES, 'UTF-8') ?>" alt="Photo">
      <?php else: ?>
        <div style="width:38mm; height:38mm; text-align:center; line-height:38mm; font-size:10pt;">
          PICTURE
        </div>
      <?php endif; ?>
    </td>

    <td class="box">
      <?php if ($thumbFile !== ''): ?>
        <img src="<?= htmlspecialchars($thumbFile, ENT_QUOTES, 'UTF-8') ?>" alt="Thumbmark">
      <?php else: ?>
        <div style="width:38mm; height:38mm;"></div>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-size:7.5pt;">PICTURE</td>
    <td style="text-align:center; font-size:7.5pt;">RIGHT THUMBMARK</td>
  </tr>
</table>

<div class="sigline">Signature over Printed Name</div>

<table class="receipt">
  <tr>
    <td class="rlabel">BARANGAY CERT. NO.</td>
    <td class="rcolon">:</td>
    <td><div class="rline"><?= htmlspecialchars($cert_no ?? '') ?></div></td>
  </tr>
  <tr>
    <td class="rlabel">OFFICIAL RECEIPT</td>
    <td class="rcolon">:</td>
    <td><div class="rline"><?= htmlspecialchars($or_no ?? '') ?></div></td>
  </tr>
  <tr>
    <td class="rlabel">AMOUNT</td>
    <td class="rcolon">:</td>
    <td><div class="rline"><?= htmlspecialchars($amount ?? '') ?></div></td>
  </tr>
  <tr>
    <td class="rlabel">DATED PAID</td>
    <td class="rcolon">:</td>
    <td><div class="rline"><?= htmlspecialchars($date_paid ?? '') ?></div></td>
  </tr>
</table>

<div style="margin-top:2mm; font-size:8.5pt;"><i>OR not valid without OFFICIAL SEAL.</i></div>