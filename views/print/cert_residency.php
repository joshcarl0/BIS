<?php ob_start();

$rn = htmlspecialchars($resident_name ?? '');
$ra = htmlspecialchars($resident_address ?? '');
$purpose = htmlspecialchars($purpose ?? 'RESIDENCY');

?>

This is to certify that <b><?= $rn ?></b>, of legal age,
is a bona fide resident of this barangay with postal address at
<b><?= $ra ?></b>, Barangay Don Galo, City of Parañaque.

<br><br>

This certification is being issued upon the request of the above-named
person for the purpose of <b><?= $purpose ?></b>.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "CERTIFICATE OF RESIDENCY";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
