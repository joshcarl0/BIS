<?php
$extra = json_decode($doc['extra_json'] ?? '{}', true) ?: [];

$child_name  = $extra['child_name'] ?? '';
$child_dob   = $extra['child_dob'] ?? '';
$child_pob   = $extra['child_pob'] ?? '';
$mother_name = $extra['mother_name'] ?? '';
$father_name = $extra['father_name'] ?? '';

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<p>
This is to certify that <strong><?= esc($resident_name) ?></strong>
is a bona fide resident of this barangay with postal address at
<strong><?= esc($resident_address) ?></strong>, Barangay Don Galo, City of Parañaque.
</p>

<p>
Further certifies that as per our Records of Barangay Inhabitants the above-mentioned name is the legal guardian/parent of the child below.
</p>

<p>
<strong>Name of Child:</strong> <?= esc($child_name) ?><br>
<strong>Date of Birth:</strong> <?= esc($child_dob) ?><br>
<strong>Place of Birth:</strong> <?= esc($child_pob) ?><br>
<strong>Name of Mother:</strong> <?= esc($mother_name) ?><br>
<strong>Name of Father:</strong> <?= esc($father_name) ?>
</p>

<p>
This certification is being issued upon the request of the above-named person for whatever legal purpose it may serve.
</p>

<p>
Issued this <strong><?= date('j') ?></strong> day of 
<strong><?= date('F Y') ?></strong> in Barangay Don Galo, City of Parañaque.
</p>