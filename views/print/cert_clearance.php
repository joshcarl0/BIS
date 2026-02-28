<?php
// views/print/cert_clearance.php
// CONTENT ONLY — controller will capture this into $content

$resident_name    = $resident_name ?? '(NAME)';
$resident_address = $resident_address ?? '(ADDRESS)';
$purpose          = $purpose ?? 'LOCAL EMPLOYMENT';
?>

<div class="doc-title">BARANGAY CLEARANCE</div>
<div class="to-whom">TO WHOM IT MAY CONCERN:</div>

<p class="para">
  This is to certify that <b><?= htmlspecialchars(strtoupper($resident_name)) ?></b>,
  whose photograph, signature and right thumb mark appears below, is a bonafide resident
  of <b><?= htmlspecialchars(strtoupper($resident_address)) ?></b>, <b>DON GALO, PARAÑAQUE CITY</b>.
</p>

<p class="para">
  This certification is issued upon the request of the above-mentioned individual for the purpose of
  <b><?= htmlspecialchars(strtoupper($purpose)) ?></b> and valid only for three (3) months from date issued.
</p>

<p class="para">
  Issued this ____ day of <?= htmlspecialchars($month ?? '(MONTH)') ?>, <?= htmlspecialchars($year ?? date('Y')) ?>
  in Barangay Don Galo City of Parañaque.
</p>

<div style="text-align:right; margin-top:8mm; font-size:11pt; padding-right:5mm;">
  <b style="text-transform:uppercase; text-decoration:underline;"><?= htmlspecialchars($captain_name ?? 'MARILYN F. BURGOS') ?></b><br>
  <span>Punong Barangay</span>
</div>