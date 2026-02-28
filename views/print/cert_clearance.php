<?php
$resident_name = $resident_name ?? '(name)';
$resident_address = $resident_address ?? '(address)';
$purpose = $purpose ?? 'LOCAL EMPLOYMENT';
$month = $month ?? '(month)';
$year = $year ?? date('Y');
$captain_name = $captain_name ?? 'MARILYN F. BURGOS';
$photo_src = $photo_src ?? '';
$thumb_src = $thumb_src ?? '';
$cert_no = $cert_no ?? '';
$or_no = $or_no ?? '';
$amount = $amount ?? '';
$date_paid = $date_paid ?? '';
?>

<p class="concern">TO WHOM IT MAY CONCERN:</p>

<p class="body-paragraph indent">
  This is to certify that <strong><?= htmlspecialchars($resident_name) ?></strong>, whose photograph, signature and right thumb mark appears below,
  is a bonafide resident <strong><?= htmlspecialchars($resident_address) ?></strong>, <strong>DON GALO, PARAÑAQUE CITY.</strong>
</p>

<p class="body-paragraph indent">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <strong><?= htmlspecialchars($purpose) ?></strong> and valid only for three (3) months from date issued.
</p>

<p class="body-paragraph indent issued-line">
  Issued this ____ day of <em><?= htmlspecialchars($month) ?></em>, <?= htmlspecialchars($year) ?> in Barangay Don Galo City of Parañaque.
</p>

<table class="signature-table" cellpadding="0" cellspacing="0">
  <tr>
    <td></td>
    <td class="captain-cell">
      <div class="captain-name"><?= htmlspecialchars($captain_name) ?></div>
      <div class="captain-title">Punong Barangay</div>
    </td>
  </tr>
</table>

<table class="proof-table" cellpadding="0" cellspacing="0">
  <tr>
    <td style="width:45%;">
      <div class="proof-box">
        <?php if ($photo_src !== ''): ?>
          <img class="proof-image" src="<?= htmlspecialchars($photo_src) ?>" alt="PICTURE">
        <?php else: ?>
          <div class="proof-label">PICTURE</div>
        <?php endif; ?>
      </div>
    </td>
    <td style="width:55%; text-align:right;">
      <div class="proof-box thumb-box">
        <?php if ($thumb_src !== ''): ?>
          <img class="proof-image" src="<?= htmlspecialchars($thumb_src) ?>" alt="RIGHT THUMBMARK">
        <?php else: ?>
          <div class="proof-label thumb-label">RIGHT THUMBMARK</div>
        <?php endif; ?>
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="sig-row">
      <div class="sig-line"></div>
      <div class="sig-caption">Signature over Printed Name</div>
    </td>
  </tr>
</table>

<table class="receipt-table" cellpadding="0" cellspacing="0">
  <tr>
    <td class="receipt-label">BARANGAY CERT. NO.:</td>
    <td class="receipt-colon">:</td>
    <td class="receipt-value"><?= htmlspecialchars($cert_no) ?></td>
  </tr>
  <tr>
    <td class="receipt-label">OFFICIAL RECEIPT:</td>
    <td class="receipt-colon">:</td>
    <td class="receipt-value"><?= htmlspecialchars($or_no) ?></td>
  </tr>
  <tr>
    <td class="receipt-label">AMOUNT:</td>
    <td class="receipt-colon">:</td>
    <td class="receipt-value"><?= htmlspecialchars($amount) ?></td>
  </tr>
  <tr>
    <td class="receipt-label">DATED PAID</td>
    <td class="receipt-colon">:</td>
    <td class="receipt-value"><?= htmlspecialchars($date_paid) ?></td>
  </tr>
</table>

<p class="receipt-note">OR not valid without OFFICIAL SEAL.</p>
