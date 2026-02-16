<?php
// build your $content (HTML) then:
ob_start();
?>
<style>
  .para{ margin-top:6mm; font-size:12pt; line-height:1.6; text-align:justify; text-indent:10mm; }
  .issued{ margin-top:8mm; font-size:12pt; font-style:italic; }
  .sign-area{ margin-top:10mm; display:flex; justify-content:flex-end; }
  .sign-box{ width:70mm; text-align:center; font-size:12pt; }
  .sign-name{ font-weight:800; text-decoration:underline; margin-bottom:1mm; }

  .boxes{ margin-top:14mm; display:flex; gap:18mm; align-items:flex-end; }
  .box{ width:38mm; height:38mm; border:0.3mm solid #333; display:flex; align-items:center; justify-content:center; font-size:9pt; }
  .sigline{ margin-top:6mm; width:95mm; border-top:0.3mm solid #333; font-size:9pt; text-align:center; padding-top:1mm; }

  .meta{ margin-top:8mm; font-size:9.5pt; line-height:1.25; }
  .note{ margin-top:3mm; font-size:8.5pt; font-style:italic; }
</style>

<div class="para">
  This is to certify that <b>(name)</b>, whose photograph, signature and right thumb mark appears below,
  is a bonafide resident <b>(address)</b>, DON GALO, PARAÑAQUE CITY.
</div>

<div class="para">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <b>LOCAL EMPLOYMENT</b> and valid only for three (3) months from date issued.
</div>

<div class="issued">
  Issued this ____ day of (month), <?= date('Y') ?> in Barangay Don Galo City of Parañaque.
</div>

<div class="sign-area">
  <div class="sign-box">
    <div class="sign-name">MARILYN F. BURGOS</div>
    Punong Barangay
  </div>
</div>

<div class="photo-thumb-row">
  <div class="photo-box">PICTURE</div>
  <div class="photo-box">RIGHT THUMBMARK</div>
</div>

<div class="sigline">Signature over Printed Name</div>

<div class="receipt-meta">
  <div class="row">BARANGAY CERT. NO.: <span>_______</span></div>
  <div class="row">OFFICIAL RECEIPT: <span>_______</span></div>
  <div class="row">AMOUNT: <span>_______</span></div>
  <div class="row">DATE PAID: <span>_______</span></div>
</div>

<div class="note">NOTE: Not Valid without OFFICIAL SEAL.</div>


<?php
$content = ob_get_clean();

$title = "Barangay Clearance";
$doc_title = "BARANGAY CLEARANCE";
$doc_subtitle = "TO WHOM IT MAY CONCERN:";
$watermark_src = "/BIS/assets/images/barangay_logo.png"; // or your seal watermark png

include __DIR__ . "/layout_clearance.php";
