<?php
// expected vars:
// $title (e.g., "CERTIFICATION")
// $content (HTML string)
// optional: $cert_no, $or_no, $amount, $date_paid
$docTitle = $title ?? 'CERTIFICATION';

$cert_no   = $cert_no ?? '';
$or_no     = $or_no ?? '';
$amount    = $amount ?? '';
$date_paid = $date_paid ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($docTitle) ?></title>
<style>
  @page { size: A4; margin: 18mm; }
  * { box-sizing: border-box; }
  body { margin:0; padding:0; font-family:"Times New Roman", serif; color:#111; }

  .page { width: 100%; }

  /* ===== HEADER ===== */
  .hdr{
    position: relative;
    text-align: center;
    padding-top: 2mm;
  }

  .hdr-row{
    position: relative;
    height: 22mm;
  }

  .logo-left{
    position:absolute; left:0; top:0;
    width: 22mm; height:auto;
  }

  .logo-right1{
    position:absolute; right:38mm; top:0;
    width: 22mm; height:auto;
  }

  .logo-right2{
    position:absolute; right:0; top:1mm;
    width: 34mm; height:auto;
  }

  .hdr-text{
    padding: 0 60mm; /* para hindi tamaan ng logos */
    line-height: 1.15;
    font-size: 11pt;
  }

  .hdr-text .bgy{
    font-size: 18pt;
    font-weight: 800;
    color: #1e4fa8;
    margin: 1mm 0 0 0;
  }
  .hdr-text .addr{ font-size: 10pt; }

  /* blue + gold lines like photo */
  .lines{
    margin-top: 3mm;
    height: 0;
    border-top: 2.5px solid #1e4fa8;
    border-bottom: 1.6px solid #d8b100;
  }

  /* ===== WATERMARK (big seal in middle) ===== */
  .wm{
    position: absolute;
    left: 50%;
    top: 55%;
    transform: translate(-50%, -50%);
    width: 150mm;
    opacity: 0.10;
    z-index: 0;
  }

  /* ===== BODY ===== */
  .body{
    position: relative;
    z-index: 1;
    margin-top: 14mm;
    padding: 0 4mm;
  }

  .title{
    text-align:center;
    font-size: 20pt;
    font-weight: 800;
    letter-spacing: 0.6px;
    margin: 0 0 10mm 0;
  }

  .content{
    font-size: 12pt;
    line-height: 1.8;
    text-align: justify;
  }

  /* ===== SIGNATURE + RECEIPT (bottom-right) ===== */
  .footer{
    margin-top: 18mm;
    position: relative;
    min-height: 60mm;
  }

  .sig{
    position:absolute;
    right: 0;
    top: 0;
    width: 75mm;
    text-align: center;
    font-size: 12pt;
  }
  .sig .name{ font-weight: 800; text-decoration: underline; }
  .sig .role{ font-size: 11pt; }

  .receipt{
    position:absolute;
    right: 0;
    top: 24mm;
    width: 75mm;
    font-size: 10.5pt;
  }
  .r-row{
    display:flex;
    gap: 4mm;
    margin: 1.6mm 0;
  }
  .r-lbl{ width: 30mm; }
  .r-val{
    flex: 1;
    border-bottom: 1px solid #111;
    min-height: 4mm;
    text-align: left;
    padding-left: 1mm;
  }

  /* bottom note like photo */
  .note{
    margin-top: 55mm;
    font-size: 10.5pt;
  }
  .note b{ font-weight: 800; }

</style>
</head>
<body>
  <div class="page">
    <div class="hdr">
      <div class="hdr-row">
        <img class="logo-left" src="/BIS/assets/images/barangay_logo.png" alt="Barangay Logo">
        <img class="logo-right1" src="/BIS/assets/images/city_logo.png" alt="City Logo">
        <img class="logo-right2" src="/BIS/assets/images/bagong_pilipinas.png" alt="Bagong Pilipinas">

        <div class="hdr-text">
          <div>Republic of the Philippines</div>
          <div>City of Parañaque</div>
          <div class="bgy">Barangay Don Galo</div>
          <div class="addr">Dimatimbangan St., Barangay Don Galo, Parañaque City</div>
          <div class="addr">Tel. No. (02) 852-9869</div>
        </div>
      </div>

      <div class="lines"></div>
    </div>

    <!-- watermark seal -->
    <img class="wm" src="/BIS/assets/images/barangay_logo.png" alt="Watermark">

    <div class="body">
      <div class="title"><?= htmlspecialchars($docTitle) ?></div>

      <div class="content">
        <?= $content ?>
      </div>

      <div class="footer">
        <div class="sig">
          <div class="name">MARILYN F. BURGOS</div>
          <div class="role">Punong Barangay</div>
        </div>

        <div class="receipt">
          <div class="r-row"><div class="r-lbl">Brgy. Cert. No:</div><div class="r-val"><?= htmlspecialchars($cert_no) ?></div></div>
          <div class="r-row"><div class="r-lbl">Official Receipt:</div><div class="r-val"><?= htmlspecialchars($or_no) ?></div></div>
          <div class="r-row"><div class="r-lbl">Amount:</div><div class="r-val"><?= htmlspecialchars($amount) ?></div></div>
          <div class="r-row"><div class="r-lbl">Date Paid:</div><div class="r-val"><?= htmlspecialchars($date_paid) ?></div></div>
        </div>

        <div class="note">
          <b>NOTE:</b> Not valid without official seal.<br><br>
          <b>This Certificate is valid for ninety (90) days</b> from the date of issuance.
        </div>
      </div>
    </div>
  </div>
</body>
</html>