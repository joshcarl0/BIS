<?php ob_start(); ?>

This is to certify that <b><?= htmlspecialchars($resident_name) ?></b>
is a bona fide resident of this barangay with postal address at
<b><?= htmlspecialchars($resident_address) ?></b>.

<br><br>

This certification is issued upon the request of the above-mentioned
individual for the purpose of
<b><?= htmlspecialchars($doc['purpose'] ?? '') ?></b>.

<br><br>

Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.

<?php
$title = "BARANGAY CLEARANCE";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
