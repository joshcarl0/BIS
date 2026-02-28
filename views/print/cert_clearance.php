<?php
// fallback safety
$resident_name    = $resident_name ?? $name ?? '(name)';
$resident_address = $resident_address ?? $address ?? '(address)';
$purpose          = $purpose ?? 'LOCAL EMPLOYMENT';

$captain_name = $captain_name ?? 'MARILYN F. BURGOS';
$month = $month ?? date('F');
$year  = $year ?? date('Y');
$day   = $day ?? '_____';
?>

<div class="content-inner">
  <div class="doc-title">BARANGAY CLEARANCE</div>
  <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

  <p class="para">
    This is to certify that <b><?= htmlspecialchars(strtoupper($resident_name)) ?></b>,
    whose photograph, signature and right thumb mark appears below, is a bonafide resident
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
</div>