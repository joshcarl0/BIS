<?php
// =======================
// VARIABLES (from controller)
// =======================
$title   = $title ?? 'CERTIFICATION';
$content = $content ?? '';

$doc_title = $doc_title ?? 'CERTIFICATION';

$cert_no   = $cert_no ?? '';
$or_no     = $or_no ?? '';
$amount    = $amount ?? '';
$date_paid = $date_paid ?? '';

/**
 * DOMPDF + CHROOT SAFE:
 * Use paths relative to chroot root.
 */
$imgBarangay = '../../BIS/assets/images/barangay_logo.png';
$imgCity     = '../../BIS/assets/images/city_logo.png';
$imgBagong   = '../../BIS/assets/images/bagong_pilipinas.png';

// Optional font (place file under /assets/fonts/)
$fontOldEnglish = '../../BIS/assets/fonts/UnifrakturCook-Bold.ttf';

$allowedTags = '<p><br><b><strong><i><em><u><span><small><div><table><thead><tbody><tr><td><th><ul><ol><li>';
$safeContent = strip_tags((string)$content, $allowedTags);

$plainLength = function_exists('mb_strlen') ? mb_strlen(trim(strip_tags((string)$safeContent))) : strlen(trim(strip_tags((string)$safeContent)));

$docTitleLc = strtolower((string)$doc_title);
$isLivein = strpos($docTitleLc, 'cohabitation') !== false || strpos($docTitleLc, 'live') !== false;
$isGuardian = strpos($docTitleLc, 'guardian') !== false;
$compactClass = '';

if ($isLivein || $plainLength > 530) {
    $compactClass = ' compact';
}
if ($plainLength > 760 || ($isGuardian && $plainLength > 620)) {
    $compactClass .= ' ultra-compact';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

<style>
@page { size: A4; margin: 14mm 14mm 12mm; }
*{ box-sizing:border-box; }
html, body { margin:0; padding:0; }

body.cert-layout{
  font-family: "Times New Roman", serif;
  font-size: 11pt;
  color:#111;
}

@font-face{
  font-family: "OldEnglishLocal";
  src: url("<?= htmlspecialchars($fontOldEnglish, ENT_QUOTES, 'UTF-8') ?>") format("truetype");
  font-weight: normal;
  font-style: normal;
}

.page{
  width:100%;
}

.header{
  text-align:center;
  margin-top:0;
}

.hdr{
  width:100%;
  table-layout: fixed;
  border-collapse: collapse;
}
.hdr td{ padding:0; vertical-align: middle; }
.hdr tr{ height: 27mm; }

.td-seal{ width:22mm; text-align:center; }
.td-text{ text-align:center; }
.td-right-logos{ width:44mm; text-align:center; }

.right-logos{ display:inline-block; white-space:nowrap; }
.right-logos img{ display:inline-block; vertical-align:middle; }

.logo-seal{ width:18mm; height:auto; display:block; margin:0 auto; }
.logo-bagong{ width:22mm; height:auto; display:block; margin:0 auto; }

.rp{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size: 14pt;
  letter-spacing: 0.7px;
  line-height:1;
}
.city{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size:12pt;
  margin-top:0.5mm;
  line-height:1;
}
.brgy{
  font-size:23pt;
  font-weight:800;
  color:#1b4f9c;
  margin:0.7mm 0;
  line-height:1;
}
.addr{
  font-size:8.5pt;
  line-height:1.1;
}

.header-line{
  margin-top:2.2mm;
  border-top: 1.6px solid #1e4fa8;
  border-bottom: 1.2px solid #d8b100;
  height:0;
}

.watermark{
  position: fixed;
  top: 54%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 148mm;
  opacity: 0.07;
  z-index: -1;
}

.doc-title{
  text-align:center;
  margin-top:8mm;
  font-size:17.5pt;
  letter-spacing:1px;
  font-weight:800;
}

.content{
  margin-top:7mm;
  padding:0 7mm;
  line-height:1.55;
  text-align:left;
}
.content p{
  text-indent:10mm;
  margin:0 0 4.3mm 0;
}
.content table{
  width:100%;
  border-collapse:collapse;
  margin:0 0 4.1mm 0;
}
.content td,
.content th{
  padding:0.8mm 0;
  vertical-align:top;
}
.content td:first-child,
.content th:first-child{
  width:44mm;
  white-space:nowrap;
}
.content ul,
.content ol{
  margin:0 0 4mm 14mm;
}

.signature{
  text-align:right;
  margin-top:8mm;
  padding-right:16mm;
  line-height:1.22;
}
.signature .name{
  font-weight:800;
  text-decoration:underline;
  font-size:13pt;
}

.receipt{
  margin-top:5.2mm;
  width:72mm;
  margin-left:auto;
  font-size:10.2pt;
  line-height:1.08;
}
.receipt table{
  width:100%;
  border-collapse:collapse;
}
.receipt td{
  padding:1.1mm 0;
  vertical-align:bottom;
}
.receipt .label{
  width:33mm;
  white-space:nowrap;
}
.receipt .value-cell{
  border-bottom:1px solid #111;
  text-align:right;
  min-width:34mm;
}

.bottom-left{
  margin-top:4.2mm;
  padding-left:7mm;
  font-size:10pt;
  line-height:1.2;
}

/* Compact mode for long content: keep single page without overlap */
.page.compact{
  font-size:10.4pt;
}
.page.compact .doc-title{
  margin-top:6mm;
  font-size:16pt;
}
.page.compact .content{
  margin-top:5.2mm;
  line-height:1.43;
}
.page.compact .content p{
  margin-bottom:3mm;
}
.page.compact .content table{
  margin-bottom:3mm;
}
.page.compact .signature{
  margin-top:6mm;
}
.page.compact .receipt{
  margin-top:4.2mm;
  width:69mm;
}
.page.compact .bottom-left{
  margin-top:3.6mm;
  font-size:9.5pt;
}

.page.ultra-compact{
  font-size:9.9pt;
}
.page.ultra-compact .doc-title{
  margin-top:5mm;
  font-size:15pt;
}
.page.ultra-compact .content{
  margin-top:4.3mm;
  line-height:1.34;
}
.page.ultra-compact .content p{
  margin-bottom:2.3mm;
}
.page.ultra-compact .content td,
.page.ultra-compact .content th{
  padding:0.45mm 0;
}
.page.ultra-compact .signature{
  margin-top:4.8mm;
  padding-right:14mm;
}
.page.ultra-compact .receipt{
  margin-top:3.5mm;
  width:67mm;
  font-size:9.6pt;
}
.page.ultra-compact .bottom-left{
  margin-top:3.1mm;
  font-size:9pt;
}
</style>
</head>

<body class="cert-layout">
<div class="page<?= htmlspecialchars($compactClass, ENT_QUOTES, 'UTF-8') ?>">

  <img src="<?= htmlspecialchars($imgBarangay, ENT_QUOTES, 'UTF-8') ?>" class="watermark" alt="">

  <div class="header">
    <table class="hdr" cellspacing="0" cellpadding="0">
      <tr>
        <td class="td-seal">
          <img src="<?= htmlspecialchars($imgBarangay, ENT_QUOTES, 'UTF-8') ?>" class="logo-seal" alt="">
        </td>

        <td class="td-text">
          <div class="rp">Republic of the Philippines</div>
          <div class="city">City of Paranaque</div>
          <div class="brgy">Barangay Don Galo</div>
          <div class="addr">Dimatimbangan St., Barangay Don Galo, Paranaque City</div>
          <div class="addr">Tel. No. (02) 852-9869</div>
        </td>

        <td class="td-right-logos">
          <div class="right-logos">
            <img src="<?= htmlspecialchars($imgCity, ENT_QUOTES, 'UTF-8') ?>" class="logo-seal" alt="">
            <img src="<?= htmlspecialchars($imgBagong, ENT_QUOTES, 'UTF-8') ?>" class="logo-bagong" alt="">
          </div>
        </td>
      </tr>
    </table>

    <div class="header-line"></div>
  </div>

  <div class="doc-title"><?= htmlspecialchars($doc_title, ENT_QUOTES, 'UTF-8') ?></div>

  <div class="content">
    <?= $safeContent ?>
  </div>

  <div class="signature">
    <div class="name">MARILYN F. BURGOS</div>
    <div>Punong Barangay</div>
  </div>

  <div class="receipt">
    <table>
      <tr>
        <td class="label">Brgy. Cert. No:</td>
        <td class="value-cell"><?= htmlspecialchars($cert_no, ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
      <tr>
        <td class="label">Official Receipt:</td>
        <td class="value-cell"><?= htmlspecialchars($or_no, ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
      <tr>
        <td class="label">Amount:</td>
        <td class="value-cell"><?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
      <tr>
        <td class="label">Date Paid:</td>
        <td class="value-cell"><?= htmlspecialchars($date_paid, ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
    </table>
  </div>

  <div class="bottom-left">
    <div><b>NOTE:</b> Not valid without official seal.</div>
    <div>This Certificate is only valid for ninety (90) days from the date of issuance.</div>
  </div>

</div>
</body>
</html>
