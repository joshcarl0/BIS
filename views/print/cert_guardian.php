<?php ob_start();

$rn = htmlspecialchars($resident_name ?? '');
$ra = htmlspecialchars($resident_address ?? '');

$child_name   = htmlspecialchars($child_name ?? '');
$dob          = htmlspecialchars($child_dob ?? '');
$pob          = htmlspecialchars($child_pob ?? '');
$mother_name  = htmlspecialchars($mother_name ?? '');
$father_name  = htmlspecialchars($father_name ?? '');

function lineOrBlank(string $v): string {
  return $v !== '' ? "<b>{$v}</b>" : "___________________________";
}
?>

This is to certify that <b><?= $rn ?></b>
is a bona fide resident of this barangay with postal address at
<b><?= $ra ?></b>.

<br><br>

This is to further certify that as per our records, the above-mentioned
person is the legal guardian/parent of the child below.

<br><br>

Name of Child: <?= lineOrBlank($child_name) ?><br>
Date of Birth: <?= lineOrBlank($dob) ?><br>
Place of Birth: <?= lineOrBlank($pob) ?><br>
Name of Mother: <?= lineOrBlank($mother_name) ?><br>
Name of Father: <?= lineOrBlank($father_name) ?><br>

<br>

This certification is being issued upon the request of the above-named
person for whatever legal purpose it may serve.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "CERTIFICATE OF GUARDIANSHIP";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
