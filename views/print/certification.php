<?php ob_start(); ?>

This is to certify that <b><?= $resident_name ?></b>, of legal age,
Filipino, with present address at <b><?= $resident_address ?></b>,
Barangay Don Galo, City of Parañaque.

<br><br>

This certification is being issued upon the request of the above-named
person for whatever legal purpose it may serve.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "CERTIFICATION";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
