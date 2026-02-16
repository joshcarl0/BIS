<?php
ob_start();

$resident_name = $resident_name ?? '________________';
$resident_address = $resident_address ?? '________________';
$purpose_text = trim((string)($purpose ?? 'LOCAL EMPLOYMENT'));
if ($purpose_text === '') {
    $purpose_text = 'LOCAL EMPLOYMENT';
}
$issued_day = $issued_day ?? date('jS');
$issued_month_year = $issued_month_year ?? date('F, Y');
?>

This is to certify that <b><?= htmlspecialchars($resident_name) ?></b>, whose photograph,
signature and right thumb mark appears below, is a bonafide resident of
<b><?= htmlspecialchars($resident_address) ?></b>, DON GALO, PARAÑAQUE CITY.

<br><br>

This certification is issued upon the request of the above-mentioned
individual for the purpose of <b><?= htmlspecialchars(strtoupper($purpose_text)) ?></b>
and valid only for three (3) months from date issued.

<div class="issue-line">
  Issued this ____ day of <?= htmlspecialchars($issued_month_year) ?> in Barangay Don Galo City of Parañaque.
</div>

<?php
$content = ob_get_clean();
$title = 'BARANGAY CLEARANCE';
require __DIR__ . '/layout_clearance.php';
