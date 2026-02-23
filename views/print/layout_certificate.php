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

// allow basic html for paragraphs
$allowedTags = '<p><br><b><strong><i><em><u><span><small><div><table><thead><tbody><tr><td><th><ul><ol><li>';
$safeContent = strip_tags((string)$content, $allowedTags);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

<style>
@page { size: A4; margin: 18mm; }
*{ box-sizing:border-box; }
html, body { margin:0; padding:0; }

body.cert-layout{
  font-family: "Times New Roman", serif;
  font-size: 12pt;
  color:#111;
}

/* Chroot-safe font face (no file://) */
@font-face{
  font-family: "OldEnglishLocal";
  src: url("<?= htmlspecialchars($fontOldEnglish, ENT_QUOTES, 'UTF-8') ?>") format("truetype");
  font-weight: normal;
  font-style: normal;
}

/* Page wrapper (FLOW BASED) */
.page{ width: 100%; }

/* ===== HEADER ===== */
.header{
  text-align:center;
  margin-top:2mm;
}

.hdr{
  width:100%;
  table-layout: fixed;
  border-collapse: collapse;
}
.hdr td{ padding:0; vertical-align: middle; }
.hdr tr{ height: 36mm; }

.td-seal{ width:26mm; text-align:center; }
.td-text{ text-align:center; }
.td-right-logos{ width:52mm; text-align:center; }

.right-logos{ display:inline-block; white-space:nowrap; }
.right-logos img{ display:inline-block; vertical-align:middle; }

/* logos */
.logo-seal{ width:20mm; height:auto; display:block; margin:0 auto; }
.logo-bagong{ width:26mm; height:auto; display:block; margin:0 auto; }

/* header text */
.rp{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size: 16pt;
  font-weight: normal;
  letter-spacing: 1px;
}
.city{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size:14pt;
  font-weight: normal;
  margin-top:1mm;
  line-height:1.1;
  letter-spacing: 0.5px;
}
.brgy{
  font-size:26pt;
  font-weight:800;
  color:#1b4f9c;
  margin:1mm 0;
  white-space: nowrap;
  line-height:1.05;
}
.addr{
  font-size:9.5pt;
  line-height:1.2;
}

/* dual line like sample */
.header-line{
  margin-top:4mm;
  border-top: 2px solid #1e4fa8;
  border-bottom: 1.5px solid #d8b100;
  height:0;
}

/* watermark */
.watermark{
  position: fixed;
  top: 55%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 160mm;
  opacity: 0.08;
  z-index: -1;
}

/* title */
.doc-title{
  text-align:center;
  margin-top:14mm;
  font-size:20pt;
  font-weight:800;
}

/* content */
.content{
  margin-top:12mm;
  padding:0 12mm;
  line-height:1.8;
  text-align:left;
}
.content p{
  text-indent:14mm;
  margin:0 0 7mm 0;
}
.content table{
  width:100%;
  border-collapse:collapse;
  margin:0 0 7mm 0;
}
.content td,
.content th{
  padding:1.2mm 0;
  vertical-align:top;
}
.content ul,
.content ol{
  margin: 0 0 7mm 20mm;
}

/* signature */
.signature{
  text-align:right;
  margin-top:20mm;
  padding-right:25mm;
}
.signature .name{
  font-weight:800;
  text-decoration:underline;
}

/* receipt (FLOW BASED - NO OVERLAP) */
.receipt{
  margin-top: 12mm;
  width: 85mm;
  margin-left: auto;
  font-size:10.5pt;
}
.receipt .row{
  display:flex;
  align-items:flex-end;
  gap:4mm;
  margin: 3mm 0;
}
.receipt .label{ width: 30mm; white-space: nowrap; }
.receipt .line{
  flex: 1;
  min-height: 6mm;
  border-bottom: 1px solid #111;
  text-align: right;
  padding-top: 1mm;
}
.receipt .value{
  display: inline-block;
  font-weight:700;
}

/* bottom note (FLOW BASED) */
.bottom-left{
  margin-top: 12mm;
  padding-left: 12mm;
  font-size:10.5pt;
  line-height:1.4;
}
</style>
</head>

<body class="cert-layout">
<div class="page">

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
    <div class="row">
      <div class="label">Brgy. Cert. No:</div>
      <div class="line"><span class="value"><?= htmlspecialchars($cert_no, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>
    <div class="row">
      <div class="label">Official Receipt:</div>
      <div class="line"><span class="value"><?= htmlspecialchars($or_no, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>
    <div class="row">
      <div class="label">Amount:</div>
      <div class="line"><span class="value"><?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>
    <div class="row">
      <div class="label">Date Paid:</div>
      <div class="line"><span class="value"><?= htmlspecialchars($date_paid, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>
  </div>

  <div class="bottom-left">
    <div><b>NOTE:</b> Not valid without official seal.</div>
    <div><b>This Certificate is valid for ninety (90) days</b> from the date of issuance.</div>
  </div>

</div>
</body>
</html>
