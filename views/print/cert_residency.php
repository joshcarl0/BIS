<?php ob_start(); ?>

This is to certify that <b><?= $resident_name ?></b>,
of legal age, is a bonafide resident of this barangay
with postal address at
<b><?= $resident_address ?></b>,
Barangay Don Galo, City of Parañaque.

<br><br>

This certification is being issued upon the request of the above-named
person for the purpose of <b>RESIDENCY</b>.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "CERTIFICATION";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
