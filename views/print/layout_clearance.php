<?php
// expects variables from controller:
// $resident_name, $resident_address, $purpose, $day, $month, $year
// $captain_name, $cert_no, $or_no, $amount, $date_paid
// $officials_list (array)
// $imgBarangay, $imgCity, $imgBagong, $watermark_src

$resident_name    = $resident_name ?? $name ?? '(name)';
$resident_address = $resident_address ?? $address ?? '(address)';
$purpose          = $purpose ?? 'LOCAL EMPLOYMENT';

$captain_name = $captain_name ?? 'MARILYN F. BURGOS';
$day   = $day ?? '_____';
$month = $month ?? date('F');
$year  = $year ?? date('Y');

$cert_no   = $cert_no ?? '';
$or_no     = $or_no ?? '';
$amount    = $amount ?? '';
$date_paid = $date_paid ?? '';

$imgBarangay   = $imgBarangay   ?? 'assets/images/barangay_logo.png';
$imgCity       = $imgCity       ?? 'assets/images/city_logo.png';
$imgBagong     = $imgBagong     ?? 'assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? 'assets/images/barangay_logo.png';

$officials_list = $officials_list ?? [];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: A4; margin: 0; }
*{ box-sizing:border-box; }
html,body{ margin:0; padding:0; font-family:"Times New Roman", serif; color:#111; }

.page{
  position:relative;
  width:210mm;
  height:297mm;
  overflow:hidden;
  background:#fff;
}

/* borders (like reference) */
.border-outer{ position:absolute; left:6mm; top:6mm; right:6mm; bottom:6mm; border:1mm solid #caa33a; }
.border-inner{ position:absolute; left:9mm; top:9mm; right:9mm; bottom:9mm; border:.35mm solid #caa33a; }

/* content padding inside borders */
.sheet{
  position:relative;
  z-index:5;
  padding: 12mm 12mm 12mm 12mm;
}

/* header */
.hdr-table{ width:100%; border-collapse:collapse; }
.hdr-brgy{ font-size:22pt; font-weight:900; color:#1b4f9c; text-transform:uppercase; line-height:1; }
.hdr-sub{ font-size:8.5pt; }
.line-blue{ border-top:2.5pt solid #1b4f9c; margin-top:2mm; }
.line-gold{ border-top:1.2pt solid #caa33a; margin-top:1mm; }

/* main */
.main-table{ width:100%; border-collapse:collapse; table-layout:fixed; margin-top:4mm; }

/* left officials column + divider */
.left{
  width:42mm; /* move divider left */
  vertical-align:top;
  padding-right:2mm;
  border-right:2pt solid #1b4f9c;
}
.right{
  vertical-align:top;
  padding-left:7mm;
  position:relative;
}

/* watermark */
.watermark{
  position:absolute;
  top:40mm;
  left:0; right:0;
  text-align:center;
  z-index:0;
}
.watermark img{ width:120mm; opacity:.08; }

/* officials styling */
.off-item{ margin:0 0 5mm 0; }
.off-name{ display:block; font-weight:bold; font-size:7.2pt; text-transform:uppercase; }
.off-pos{ display:block; font-style:italic; font-size:6.4pt; }
.off-comm{ display:block; font-style:italic; font-size:5.8pt; color:#444; margin-top:1px; line-height:1.1; }

/* doc text */
.doc-title{
  text-align:center;
  font-size:19pt;
  font-weight:bold;
  text-decoration:underline;
  color:#1b4f9c;
  margin:0 0 3mm 0;
}
.to-whom{ font-weight:bold; font-size:11pt; margin:0 0 3mm 0; }
.para{ text-align:justify; font-size:11pt; line-height:1.35; margin:0 0 3mm 0; text-indent:10mm; }

.sig-block{ margin-top:5mm; font-size:11pt; }
.sig-name{ font-weight:bold; text-decoration:underline; }
.sig-pos{ font-style:italic; }

/* bottom section (NO absolute to avoid overflow) */
.bottom{ margin-top:8mm; }

.box-row{ width:100%; border-collapse:collapse; }
.box{
  width:36mm; height:36mm; border:1pt solid #000;
  position:relative; background:#fff;
}
.box-label{
  position:absolute; left:0; right:0; bottom:1mm;
  text-align:center; font-size:6pt; font-weight:bold;
}
.sigline{
  border-top:1pt solid #000;
  width:75mm;
  margin: 6mm auto 0 auto;
  text-align:center;
  font-size:9pt;
  padding-top:2mm;
}

/* receipt */
.receipt{ margin-top:5mm; font-size:9pt; line-height:1.35; }
.rtable{ border-collapse:collapse; }
.rtable td{ padding:.6mm 0; vertical-align:bottom; }
.rlabel{ width:42mm; font-weight:bold; }
.rcolon{ width:4mm; text-align:center; font-weight:bold; }
.rline{
  display:inline-block;
  border-bottom:.6pt solid #000;
  width:55mm;
  height:4mm;
}

/* safety */
table,tr,td{ page-break-inside:avoid; break-inside:avoid; }
</style>
</head>
<body>
<div class="page">
  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <div class="sheet">
    <table class="hdr-table">
      <tr>
        <td width="75">
          <img src="<?= htmlspecialchars($imgBarangay) ?>" width="70">
        </td>

        <td align="center">
          <div style="font-weight:bold; font-size:11pt;">Republic of the Philippines</div>
          <div style="font-weight:bold; font-size:11pt;">City of Parañaque</div>
          <div class="hdr-brgy">Barangay Don Galo</div>
          <div class="hdr-sub">Dimatimbang St., Barangay Don Galo, Parañaque City</div>
          <div class="hdr-sub">Tel. No.: (02) 8531-6612</div>
        </td>

        <td width="140" align="right">
          <img src="<?= htmlspecialchars($imgCity) ?>" width="60" style="margin-right:6px;">
          <img src="<?= htmlspecialchars($imgBagong) ?>" width="70">
        </td>
      </tr>
    </table>

    <div class="line-blue"></div>
    <div class="line-gold"></div>

    <table class="main-table">
      <tr>
        <td class="left">
          <?php foreach ($officials_list as $o): ?>
            <div class="off-item">
              <span class="off-name"><?= htmlspecialchars($o['name'] ?? '') ?></span>
              <span class="off-pos"><?= htmlspecialchars($o['position'] ?? '') ?></span>
              <?php if (!empty($o['committee'])): ?>
                <span class="off-comm"><?= nl2br(htmlspecialchars($o['committee'])) ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </td>

        <td class="right">
          <div class="watermark">
            <img src="<?= htmlspecialchars($watermark_src) ?>">
          </div>

          <div style="position:relative; z-index:1;">
            <div class="doc-title">BARANGAY CLEARANCE</div>
            <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

            <p class="para">
              This is to certify that <b><?= htmlspecialchars(strtoupper($resident_name)) ?></b>,
              whose photograph, signature and right thumb mark appears below, is a bonafide resident of
              <b><?= htmlspecialchars($resident_address) ?></b>, <b>DON GALO, PARAÑAQUE CITY</b>.
            </p>

            <p class="para">
              This certification is issued upon the request of the above-mentioned individual for the purpose of
              <b><?= htmlspecialchars(strtoupper($purpose)) ?></b> and valid only for three (3) months from date issued.
            </p>

            <p class="para">
              Issued this <?= htmlspecialchars($day) ?> day of <?= htmlspecialchars($month) ?>, <?= htmlspecialchars($year) ?>
              in Barangay Don Galo City of Parañaque.
            </p>

            <div class="sig-block">
              <div class="sig-name"><?= htmlspecialchars(strtoupper($captain_name)) ?></div>
              <div class="sig-pos">Punong Barangay</div>
            </div>

            <div class="bottom">
              <table class="box-row">
                <tr>
                  <td style="width:45mm; vertical-align:top;">
                    <div class="box"><div class="box-label">PICTURE</div></div>
                  </td>
                  <td></td>
                  <td style="width:45mm; vertical-align:top;">
                    <div class="box"><div class="box-label">RIGHT THUMBMARK</div></div>
                  </td>
                </tr>
              </table>

              <div class="sigline">Signature over Printed Name</div>

              <div class="receipt">
                <table class="rtable">
                  <tr>
                    <td class="rlabel">BARANGAY CERT. NO.</td><td class="rcolon">:</td>
                    <td><span class="rline"><?= htmlspecialchars($cert_no) ?></span></td>
                  </tr>
                  <tr>
                    <td class="rlabel">OFFICIAL RECEIPT</td><td class="rcolon">:</td>
                    <td><span class="rline"><?= htmlspecialchars($or_no) ?></span></td>
                  </tr>
                  <tr>
                    <td class="rlabel">AMOUNT</td><td class="rcolon">:</td>
                    <td><span class="rline"><?= htmlspecialchars($amount) ?></span></td>
                  </tr>
                  <tr>
                    <td class="rlabel">DATED PAID</td><td class="rcolon">:</td>
                    <td><span class="rline"><?= htmlspecialchars($date_paid) ?></span></td>
                  </tr>
                </table>

                <div style="margin-top:2mm; font-size:8.5pt;">
                  <i>OR not valid without OFFICIAL SEAL.</i>
                </div>
              </div>
            </div>

          </div>
        </td>
      </tr>
    </table>

  </div>
</div>
</body>
</html>