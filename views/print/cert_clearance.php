<?php ob_start(); ?>

<!-- TO WHOM IT MAY CONCERN -->
<p class="p-head"><strong>TO WHOM IT MAY CONCERN:</strong></p>

<p class="p-justify">
  This is to certify that
  <strong><?= htmlspecialchars($resident_name ?? '(name)') ?></strong>,
  whose photograph, signature and right thumb mark appears below, is a bonafide resident of
  <strong><?= htmlspecialchars($resident_address ?? '(address)') ?></strong>,
  <strong>DON GALO, PARAÑAQUE CITY</strong>.
</p>

<p class="p-justify">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <strong><?= htmlspecialchars($purpose ?? 'LOCAL EMPLOYMENT') ?></strong>
  and valid only for three (3) months from date issued.
</p>

<p class="p-issued">
  Issued this ____ day of <?= htmlspecialchars($month ?? '____') ?>, <?= htmlspecialchars($year ?? date('Y')) ?>
  in Barangay Don Galo, City of Parañaque.
</p>

<!-- SIGNATURE NAME (right aligned like sample) -->
<div class="captain-block">
  <div class="captain-name"><?= htmlspecialchars($captain_name ?? 'MARILYN F. BURGOS') ?></div>
  <div class="captain-role">Punong Barangay</div>
</div>

<!-- PHOTO + THUMB + SIGNATURE LINE -->
<table class="proof-table" cellspacing="0" cellpadding="0">
  <tr>
    <td class="box-td">
      <div class="proof-box">
        <?php if (!empty($photo_src)) : ?>
          <img class="proof-img" src="<?= htmlspecialchars($photo_src) ?>" alt="Photo">
        <?php else: ?>
          <div class="box-label">PICTURE</div>
        <?php endif; ?>
      </div>
    </td>

    <td class="box-td" style="text-align:right;">
      <div class="proof-box">
        <?php if (!empty($thumb_src)) : ?>
          <img class="proof-img" src="<?= htmlspecialchars($thumb_src) ?>" alt="Thumbmark">
        <?php else: ?>
          <div class="box-label">RIGHT THUMBMARK</div>
        <?php endif; ?>
      </div>
    </td>
  </tr>

  <tr>
    <td colspan="2" class="sig-td">
      <div class="sig-line"></div>
      <div class="sig-caption">Signature over Printed Name</div>
    </td>
  </tr>
</table>

<!-- RECEIPT (bottom area like sample) -->
<div class="receipt">
  <div class="r-row">
    <div class="r-label">BARANGAY CERT. NO:</div>
    <div class="r-line"><?= htmlspecialchars($cert_no ?? '') ?></div>
  </div>
  <div class="r-row">
    <div class="r-label">OFFICIAL RECEIPT:</div>
    <div class="r-line"><?= htmlspecialchars($or_no ?? '') ?></div>
  </div>
  <div class="r-row">
    <div class="r-label">AMOUNT:</div>
    <div class="r-line"><?= htmlspecialchars($amount ?? '') ?></div>
  </div>
  <div class="r-row">
    <div class="r-label">DATED PAID:</div>
    <div class="r-line"><?= htmlspecialchars($date_paid ?? '') ?></div>
  </div>

  <div class="r-note">OR not valid without OFFICIAL SEAL.</div>
</div>

<?php $content = ob_get_clean(); ?>