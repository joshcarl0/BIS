<?php
// fallback safety
$resident_name    = $resident_name ?? $name ?? '(name)';
$resident_address = $resident_address ?? $address ?? '(address)';
$purpose          = $purpose ?? 'LOCAL EMPLOYMENT';

$captain_name = $captain_name ?? 'MARILYN F. BURGOS';
$month = $month ?? date('F');
$year  = $year  ?? date('Y');
$day   = $day   ?? '_____';

$cert_no   = $cert_no ?? '';
$or_no     = $or_no ?? '';
$amount    = $amount ?? '';
$date_paid = $date_paid ?? '';

$imgBarangay   = $imgBarangay   ?? '/assets/images/barangay_logo.png';
$imgCity       = $imgCity       ?? '/assets/images/city_logo.png';
$imgBagong     = $imgBagong     ?? '/assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? '/assets/images/barangay_logo.png';

// dompdf-safe photos (relative to chroot)
$photo_src = $photo_src ?? '';
$thumb_src = $thumb_src ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { size:A4; margin:0; }
*{ box-sizing:border-box; }
html,body{ width:210mm; height:297mm; margin:0; padding:0; }
body{ font-family:"Times New Roman", serif; color:#111; }

.page{ position:relative; width:210mm; height:297mm; overflow:hidden; background:#fff; }

/* gold borders */
.border-outer{ position:absolute; left:6mm; top:6mm; right:6mm; bottom:6mm; border:1mm solid #caa33a; }
.border-inner{ position:absolute; left:9mm; top:9mm; right:9mm; bottom:9mm; border:.35mm solid #caa33a; }

/* inner sheet padding */
.sheet{ position:relative; padding:10mm 16mm 10mm 16mm; z-index:2; }

/* header */
.hdr{ width:100%; border-collapse:collapse; }
.hdr-mid{ text-align:center; }
.hdr-title{ font-weight:900; font-size:22pt; color:#1b4f9c; text-transform:uppercase; line-height:1; }
.hdr-sub{ font-weight:bold; font-size:11pt; }
.hdr-small{ font-size:8.5pt; }
.line-blue{ border-top:2.5pt solid #1b4f9c; margin-top:2mm; }
.line-gold{ border-top:1.2pt solid #caa33a; margin-top:1mm; }

/* watermark */
.watermark{ position:absolute; left:0; right:0; top:62mm; text-align:center; z-index:1; }
.watermark img{ width:130mm; opacity:.10; }

/* CONTENT AREA */
.content{ position:relative; margin-top:6mm; height:190mm; }

/* VERTICAL BLUE LINE (EASY MOVE) */
.vline{
  position:absolute;
  top:0;
  bottom:0;
  left:8mm;             /* <<< ITO yung galawin mo to move left/right */
  width:0;
  border-left:2pt solid #1b4f9c;
}

/* body block */
.body{
  position:relative;
  margin-left:18mm;     /* <<< distance from left to start of text */
  padding-left:10mm;    /* inner spacing from the blue line */
}

/* titles */
.doc-title{
  text-align:center;
  font-size:20pt;
  font-weight:bold;
  text-decoration:underline;
  letter-spacing:.5px;
  margin:0 0 3mm 0;
}
.to-whom{ font-weight:bold; font-size:11pt; margin:0 0 4mm 0; }

.para{
  text-align:justify;
  text-indent:10mm;
  font-size:11pt;
  line-height:1.45;
  margin:0 0 4mm 0;
}

/* signature block */
.sig-block{ margin-top:6mm; font-size:11pt; text-align:right; padding-right:6mm; }
.sig-name{ font-weight:bold; text-decoration:underline; text-transform:uppercase; }
.sig-pos{ font-style:italic; }

/* bottom area like reference */
.bottom{
  position:absolute;
  left:18mm;
  right:16mm;
  bottom:16mm;
}

.box-row{ width:100%; border-collapse:collapse; }
.picbox, .thumbbox{
  width:45mm;
  height:45mm;
  border:1pt solid #000;
  text-align:center;
  vertical-align:middle;
  font-size:8pt;
}
.thumbcap{ font-size:7pt; margin-top:1mm; text-align:right; padding-right:2mm; }

.sigline{
  margin-top:4mm;
  border-top:1pt solid #000;
  width:95mm;
  margin-left:auto;
  text-align:center;
  font-size:9pt;
  padding-top:2mm;
}

/* receipt */
.receipt{
  margin-top:6mm;
  font-size:9pt;
  line-height:1.35;
}
.rtable{ border-collapse:collapse; }
.rtable td{ padding:.6mm 0; }
.rlabel{ width:45mm; font-weight:bold; }
.rcolon{ width:4mm; text-align:center; font-weight:bold; }
.rline{ border-bottom:.6pt solid #000; width:65mm; height:4mm; }

.note{ margin-top:2mm; font-size:8.5pt; font-style:italic; }

img{ max-width:100%; }
</style>
</head>

<body>
<div class="page">
  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <div class="watermark">
    <img src="<?= htmlspecialchars($watermark_src) ?>" alt="">
  </div>

  <div class="sheet">
    <table class="hdr">
      <tr>
        <td width="80">
          <img src="<?= htmlspecialchars($imgBarangay) ?>" width="70" alt="">
        </td>

        <td class="hdr-mid">
          <div class="hdr-sub">Republic of the Philippines</div>
          <div class="hdr-sub">City of Parañaque</div>
          <div class="hdr-title">Barangay Don Galo</div>
          <div class="hdr-small">Dimatimbang St., Barangay Don Galo, Parañaque City</div>
          <div class="hdr-small">Tel. No.: (02) 8531-6612</div>
        </td>

        <td width="150" align="right">
          <img src="<?= htmlspecialchars($imgCity) ?>" width="60" style="margin-right:6px;" alt="">
          <img src="<?= htmlspecialchars($imgBagong) ?>" width="70" alt="">
        </td>
      </tr>
    </table>

    <div class="line-blue"></div>
    <div class="line-gold"></div>

    <div class="content">
      <div class="vline"></div>

      <div class="body">
        <div class="doc-title">BARANGAY CLEARANCE</div>
        <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

        <p class="para">
          This is to certify that <b><?= htmlspecialchars($resident_name) ?></b>, whose photograph,
          signature and right thumb mark appears below, is a bonafide resident
          <b><?= htmlspecialchars($resident_address) ?></b>, <b>DON GALO, PARAÑAQUE CITY</b>.
        </p>

        <p class="para">
          This certification is issued upon the request of the above-mentioned individual for the purpose of
          <b><?= htmlspecialchars($purpose) ?></b> and valid only for three (3) months from date issued.
        </p>

        <p class="para">
          Issued this <?= htmlspecialchars($day) ?> day of <?= htmlspecialchars($month) ?>, <?= htmlspecialchars($year) ?>
          in Barangay Don Galo City of Parañaque.
        </p>

        <div class="sig-block">
          <div class="sig-name"><?= htmlspecialchars($captain_name) ?></div>
          <div class="sig-pos">Punong Barangay</div>
        </div>
      </div>

      <div class="bottom">
        <table class="box-row">
          <tr>
            <td class="picbox">
              <?php if (!empty($photo_src)): ?>
                <img src="<?= htmlspecialchars($photo_src) ?>" style="width:100%; height:100%; object-fit:cover;" alt="">
              <?php else: ?>
                PICTURE
              <?php endif; ?>
            </td>

            <td></td>

            <td class="thumbbox">
              <?php if (!empty($thumb_src)): ?>
                <img src="<?= htmlspecialchars($thumb_src) ?>" style="width:100%; height:100%; object-fit:cover;" alt="">
              <?php endif; ?>
            </td>
          </tr>
        </table>

        <div class="thumbcap">RIGHT THUMBMARK</div>

        <div class="sigline">Signature over Printed Name</div>

        <div class="receipt">
          <table class="rtable">
            <tr>
              <td class="rlabel">BARANGAY CERT. NO.</td><td class="rcolon">:</td>
              <td><div class="rline"><?= htmlspecialchars($cert_no) ?></div></td>
            </tr>
            <tr>
              <td class="rlabel">OFFICIAL RECEIPT</td><td class="rcolon">:</td>
              <td><div class="rline"><?= htmlspecialchars($or_no) ?></div></td>
            </tr>
            <tr>
              <td class="rlabel">AMOUNT</td><td class="rcolon">:</td>
              <td><div class="rline"><?= htmlspecialchars($amount) ?></div></td>
            </tr>
            <tr>
              <td class="rlabel">DATED PAID</td><td class="rcolon">:</td>
              <td><div class="rline"><?= htmlspecialchars($date_paid) ?></div></td>
            </tr>
          </table>

          <div class="note">OR not valid without OFFICIAL SEAL.</div>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>