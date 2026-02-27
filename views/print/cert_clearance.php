<?php ob_start(); ?>

<style>
  .para{
    margin-top:5mm;
    font-size:12pt;
    line-height:1.55;
    text-align:justify;
    text-indent:12mm;
  }

  .issued{
    margin-top:6mm;
    font-size:12pt;
    font-style: italic;
  }

  .captain{
    margin-top:8mm;
    text-align:right;
    font-size:12pt;
  }
  .captain .name{ font-weight:900; font-size:13pt; }
  .captain .pos{ font-weight:700; }

  .photoRow{
    margin-top:10mm;
    width:100%;
  }
  .box{
    width:40mm; height:40mm;
    border:0.3mm solid #333;
    text-align:center;
    font-size:10pt;
    line-height:40mm;
  }
  .thumb-label{
    text-align:center;
    font-size:9pt;
    font-weight:800;
    margin-top:1mm;
  }

  .sigline{
    width:120mm;
    margin-left:auto;
    margin-top:5mm;
    border-top:0.3mm solid #333;
    padding-top:2mm;
    text-align:center;
    font-size:10pt;
  }

  .receipt{
    margin-top:6mm;
    font-size:10pt;
    font-weight:800;
  }
  .receipt td{ padding:1mm 2mm 1mm 0; }
  .line{ width:45mm; border-bottom:1px solid #000; }
  .note{ margin-top:2mm; font-style:italic; font-size:9.5pt; }
</style>

<div class="para">
  This is to certify that <b><?= htmlspecialchars($resident_name ?? '(name)') ?></b>, whose photograph,
  signature and right thumb mark appears below, is a bonafide resident
  <b><?= htmlspecialchars($resident_address ?? '(address)') ?></b>, <b>DON GALO, PARAÑAQUE CITY</b>.
</div>

<div class="para">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <b><?= htmlspecialchars($purpose ?: 'LOCAL EMPLOYMENT') ?></b> and valid only for three (3) months from date issued.
</div>

<div class="issued">
  Issued this _____ day of (month), <?= date('Y') ?> in Barangay Don Galo City of Parañaque.
</div>

<div class="captain">
  <div class="name">MARILYN F. BURGOS</div>
  <div class="pos">Punong Barangay</div>
</div>

<!-- PHOTO + THUMB (table-safe) -->
<table class="photoRow" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
  <tr>
    <td style="width:40mm;">
      <?php if(!empty($resident_photo_url)): ?>
        <img src="<?= htmlspecialchars($resident_photo_url) ?>" style="width:40mm;height:40mm;object-fit:cover;border:0.3mm solid #333;">
      <?php else: ?>
        <div class="box">PICTURE</div>
      <?php endif; ?>
    </td>
    <td style="width:28mm;"></td>
    <td style="width:40mm;">
      <?php if(!empty($resident_thumb_url)): ?>
        <img src="<?= htmlspecialchars($resident_thumb_url) ?>" style="width:40mm;height:40mm;object-fit:cover;border:0.3mm solid #333;">
      <?php else: ?>
        <div class="box"></div>
      <?php endif; ?>
      <div class="thumb-label">RIGHT THUMBMARK</div>
    </td>
  </tr>
</table>

<div class="sigline">Signature over Printed Name</div>

<div class="receipt">
  <table style="border-collapse:collapse;">
    <tr><td>BARANGAY CERT. NO.:</td><td class="line"><?= htmlspecialchars($cert_no ?? '') ?></td></tr>
    <tr><td>OFFICIAL RECEIPT:</td><td class="line"><?= htmlspecialchars($or_no ?? '') ?></td></tr>
    <tr><td>AMOUNT:</td><td class="line"><?= htmlspecialchars($amount ?? '') ?></td></tr>
    <tr><td>DATED PAID:</td><td class="line"><?= htmlspecialchars($date_paid ?? '') ?></td></tr>
  </table>
  <div class="note">OR: not valid without <b>OFFICIAL SEAL.</b></div>
</div>

<?php $content = ob_get_clean(); ?>