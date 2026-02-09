<?php
// layout variables expected:
// $title
// $content
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Document') ?></title>

<style>
  /* ===== A4 EXACT SETUP ===== */
  @page{
    size: A4;
    margin: 18mm 18mm 18mm 18mm;
  }

  body{
    font-family: "Times New Roman", serif;
    margin:0;
    padding:0;
  }

  .page{
  position: relative;
  width: 210mm;
  height: 297mm; /* instead of min-height */
  margin: 0 auto;
}


  /* ===== HEADER ===== */
  .header{
    margin-top: 8mm;
    margin-bottom: 6mm;
  }

  .top-logos{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
  }

  .logo{
    width:22mm;
    height:auto;
  }

  .right-group{
    display:flex;
    align-items:center;
    gap:6mm;
  }

  .logo-bagong{
    width:34mm;
    height:auto;
  }

  .header-text{
    text-align:center;
    flex:1;
    font-size:11pt;
    line-height:1.15;
  }

  .header-text h2{
    margin:2mm 0 1mm;
    font-size:16pt;
    letter-spacing:0.3px;
  }

  .header-line{
    margin-top: 3mm;
    border-top: 2.5px solid #1e4fa8;   /* blue */
    border-bottom: 1.8px solid #d8b100;/* gold */
    height: 0;
  }

  /* ===== WATERMARK ===== */
  .watermark{
    position: absolute;
    top: 110mm;   /* adjust para nasa gitna */
    left: 105mm;  /* center of A4 width */
    transform: translate(-50%, -50%);
    opacity: 0.07;
    width: 130mm;
    z-index: 0;
    pointer-events: none;
  }

  /* ===== TITLE & CONTENT ===== */
  .cert-title{
    text-align:center;
    font-size:18pt;
    margin: 12mm 0 8mm;
    font-weight:bold;
    letter-spacing:0.5px;
    z-index: 2;
    position: relative;
  }

  .content{
    font-size:12.5pt;
    line-height:1.8;
    text-align:justify;
    z-index: 2;
    position: relative;

    padding-bottom: 75mm;
  }

  /* ===== SIGNATURE ===== */
  .signature{
    margin-top: 18mm;
    text-align:right;
    font-size:12pt;
    z-index: 2;
    position: relative;
  }

  /* ===== LOWER SECTION (FOOTER AREA) ===== */
  .bottom-area{
    position: absolute;
    left: 0;
    right: 0;
    bottom: 16mm; /* spacing from bottom margin */
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap: 10mm;
    z-index: 2;
  }

  /* Dry seal placement (left side) */
  .dry-seal{
    width: 65mm;
    font-size: 10pt;
    line-height:1.3;
  }

  .seal-circle{
    width: 45mm;
    height: 45mm;
    border: 1px dashed #777;
    border-radius: 50%;
    margin-top: 4mm;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    font-size: 9pt;
    color:#555;
  }

  /* Receipt box (lower-right) */
  .receipt-box{
    width: 70mm;
    border: 1px solid #111;
    padding: 5mm;
    font-size: 10.5pt;
  }

  .receipt-row{
    display:flex;
    justify-content:space-between;
    gap: 5mm;
    margin: 2.8mm 0;
  }

  .receipt-label{
    white-space: nowrap;
  }

  .receipt-line{
    flex:1;
    border-bottom: 1px solid #111;
    margin-left: 4mm;
    height: 14px;
  }

  /* Footer validity note (bottom-left text style) */
  .footer-note{
    margin-top: 10mm;
    font-size: 10.5pt;
  }

  @media print{
    .page{ box-shadow:none; }
  }

.receipt-value{
  flex:1;
  text-align:right;
  font-weight:bold;
  min-height: 14px;
}



</style>
</head>

<body onload="window.print()">
<div class="page">

  <!-- WATERMARK (BARANGAY SEAL) -->
  <img src="/BIS/assets/images/barangay_logo.png" class="watermark" alt="Watermark">

  <!-- HEADER -->
  <div class="header">
    <div class="top-logos">
      <img src="/BIS/assets/images/barangay_logo.png" class="logo" alt="Barangay Logo">

      <div class="header-text">
        <div>Republic of the Philippines</div>
        <div>City of Parañaque</div>
        <h2>Barangay Don Galo</h2>
        <div>Dimatimbang St., Barangay Don Galo, Parañaque City</div>
        <div>Tel. No. (02) 852-9869</div>
      </div>

      <div class="right-group">
        <img src="/BIS/assets/images/city_logo.png" class="logo" alt="City Logo">
        <img src="/BIS/assets/images/bagong_pilipinas.png" class="logo-bagong" alt="Bagong Pilipinas">
      </div>
    </div>

    <div class="header-line"></div>
  </div>

  <!-- TITLE -->
  <div class="cert-title"><?= htmlspecialchars($title ?? '') ?></div>

  <!-- CONTENT -->
  <div class="content">
    <?= $content ?? '' ?>

    <!-- optional: footer note inside content area -->
    <div class="footer-note">
      <b>NOTE:</b> Not valid without official seal.<br><br>
      <b>This Certificate is valid for ninety (90) days</b> from the date of issuance.
    </div>
  </div>

  <!-- SIGNATURE -->
  <div class="signature">
    <b>MARILYN F. BURGOS</b><br>
    Punong Barangay
  </div>

  <!-- BOTTOM AREA: DRY SEAL + RECEIPT BOX -->
  <div class="bottom-area">

    <!-- DRY SEAL PLACEMENT -->
    <div class="dry-seal">
      <div><b>Dry Seal / Official Seal</b></div>
      <div class="seal-circle">
        PLACE<br>OFFICIAL SEAL<br>HERE
      </div>
    </div>

 <div class="receipt-box">
  <div class="receipt-row">
    <div class="receipt-label">Brgy. Cert. No:</div>
    <div class="receipt-value"><?= htmlspecialchars($cert_no ?? '') ?></div>
  </div>

  <div class="receipt-row">
    <div class="receipt-label">Official Receipt:</div>
    <div class="receipt-value"><?= htmlspecialchars($or_no ?? '') ?></div>
  </div>

  <div class="receipt-row">
    <div class="receipt-label">Amount:</div>
    <div class="receipt-value"><?= htmlspecialchars($amount ?? '') ?></div>
  </div>

  <div class="receipt-row">
    <div class="receipt-label">Date Paid:</div>
    <div class="receipt-value"><?= htmlspecialchars($date_paid ?? '') ?></div>
  </div>
</div>


  </div>

</div>
</body>
</html>
