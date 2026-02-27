<?php ob_start(); ?>

<style>
  .cl-paragraph {
    margin: 0 0 4mm 0;
    font-size: 12pt;
    line-height: 1.55;
    text-align: justify;
    text-indent: 12mm;
  }

  .cl-issued {
    margin: 2mm 0 0 0;
    font-size: 12pt;
    line-height: 1.4;
    font-style: italic;
    text-indent: 12mm;
    text-align: justify;
  }

  .cl-signature-wrap {
    margin-top: 6mm;
    width: 100%;
  }

  .cl-captain-name {
    text-align: right;
    font-size: 13pt;
    font-weight: bold;
    text-decoration: underline;
    margin: 0;
  }

  .cl-captain-pos {
    text-align: right;
    font-size: 12pt;
    margin: 0;
  }

  .cl-identity-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6mm;
  }

  .cl-identity-table td {
    vertical-align: top;
    padding: 0;
  }

  .cl-photo-box,
  .cl-thumb-box {
    width: 34mm;
    height: 34mm;
    border: 0.3mm solid #222;
    text-align: center;
    vertical-align: middle;
    font-size: 10pt;
    font-weight: bold;
  }

  .cl-photo-box img,
  .cl-thumb-box img {
    width: 34mm;
    height: 34mm;
    display: block;
  }

  .cl-thumb-caption {
    margin-top: 1.2mm;
    text-align: center;
    font-size: 8.8pt;
    font-weight: bold;
  }

  .cl-sign-line {
    margin-top: 3mm;
    border-top: 0.3mm solid #111;
    width: 60mm;
    margin-left: auto;
    text-align: center;
    padding-top: 1.2mm;
    font-size: 10pt;
  }

  .cl-receipt-table {
    margin-top: 5mm;
    width: 100%;
    border-collapse: collapse;
    font-size: 11pt;
  }

  .cl-receipt-table td {
    padding: 0.6mm 0;
    vertical-align: middle;
  }

  .cl-receipt-label {
    width: 41mm;
    font-weight: bold;
  }

  .cl-receipt-sep {
    width: 4mm;
    text-align: center;
    font-weight: bold;
  }

  .cl-receipt-line {
    border-bottom: 0.3mm solid #000;
    height: 5mm;
    font-weight: bold;
  }

  .cl-receipt-note {
    margin-top: 2mm;
    font-size: 9.5pt;
    font-style: italic;
  }
</style>

<p class="cl-paragraph">
  This is to certify that <strong><?= htmlspecialchars($resident_name ?? '(name)') ?></strong>, whose photograph,
  signature and right thumb mark appears below, is a bonafide resident of
  <strong><?= htmlspecialchars($resident_address ?? '(address)') ?></strong>, <strong>DON GALO, PARAÑAQUE CITY</strong>.
</p>

<p class="cl-paragraph">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <strong><?= htmlspecialchars($purpose ?? 'LOCAL EMPLOYMENT') ?></strong> and valid only for three (3) months from date issued.
</p>

<p class="cl-issued">
  Issued this _____ day of __________, <?= date('Y') ?> in Barangay Don Galo, City of Parañaque.
</p>

<div class="cl-signature-wrap">
  <p class="cl-captain-name">MARILYN F. BURGOS</p>
  <p class="cl-captain-pos">Punong Barangay</p>
</div>

<table class="cl-identity-table">
  <tr>
    <td style="width:34mm;">
      <div class="cl-photo-box">
        <?php if (!empty($resident_photo_url)): ?>
          <img src="<?= htmlspecialchars($resident_photo_url) ?>" alt="Resident Photo">
        <?php else: ?>
          PICTURE
        <?php endif; ?>
      </div>
    </td>
    <td style="width:16mm;"></td>
    <td style="width:34mm;">
      <div class="cl-thumb-box">
        <?php if (!empty($resident_thumb_url)): ?>
          <img src="<?= htmlspecialchars($resident_thumb_url) ?>" alt="Right Thumbmark">
        <?php else: ?>
          RIGHT<br>THUMB<br>MARK
        <?php endif; ?>
      </div>
      <div class="cl-thumb-caption">RIGHT THUMBMARK</div>
    </td>
    <td></td>
  </tr>
</table>

<div class="cl-sign-line">Signature over Printed Name</div>

<table class="cl-receipt-table">
  <tr>
    <td class="cl-receipt-label">BARANGAY CERT. NO.</td>
    <td class="cl-receipt-sep">:</td>
    <td class="cl-receipt-line"><?= htmlspecialchars($cert_no ?? '') ?></td>
  </tr>
  <tr>
    <td class="cl-receipt-label">OFFICIAL RECEIPT</td>
    <td class="cl-receipt-sep">:</td>
    <td class="cl-receipt-line"><?= htmlspecialchars($or_no ?? '') ?></td>
  </tr>
  <tr>
    <td class="cl-receipt-label">AMOUNT</td>
    <td class="cl-receipt-sep">:</td>
    <td class="cl-receipt-line"><?= htmlspecialchars($amount ?? '') ?></td>
  </tr>
  <tr>
    <td class="cl-receipt-label">DATED PAID</td>
    <td class="cl-receipt-sep">:</td>
    <td class="cl-receipt-line"><?= htmlspecialchars($date_paid ?? '') ?></td>
  </tr>
</table>

<div class="cl-receipt-note">OR: not valid without <strong>OFFICIAL SEAL.</strong></div>

<?php $content = ob_get_clean(); ?>
