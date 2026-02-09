<?php ob_start(); ?>

This is to certify that <b><?= $resident_name ?></b>
is a bonafide resident of this barangay with postal address at
<b><?= $resident_address ?></b>.

<br><br>

Further certifies that as per our records, the above-mentioned
name is the legal guardian/parent of the child below.

<br><br>

Name of Child: ___________________________<br>
Date of Birth: ___________________________<br>
Place of Birth: ___________________________<br>
Name of Mother: ___________________________<br>
Name of Father: ___________________________<br>

<br>

This certification is being issued upon the request of the above-named
person for whatever legal purpose it may serve.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "CERTIFICATION";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
