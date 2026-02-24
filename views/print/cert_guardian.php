<?php
$extra = json_decode($doc['extra_data_json'] ?? '{}', true) ?: [];

$child_name  = $extra['child_name'] ?? '';
$child_dob   = $extra['child_dob'] ?? '';
$child_pob   = $extra['child_pob'] ?? '';
$mother_name = $extra['mother_name'] ?? '';
$father_name = $extra['father_name'] ?? '';

if (!function_exists('esc')) {
    function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>

<p>
This is to certify that <strong><?= esc($resident_name) ?></strong>
is a bona fide resident of this barangay with postal address at
<strong><?= esc($resident_address) ?></strong>, Barangay Don Galo, City of Parañaque.
</p>

<p>
Further certifies that as per our Records of Barangay Inhabitants the above-mentioned name is the legal guardian/parent of the child below.
</p>

<div>
<table>
  <tr><td><strong>Name of Child:</strong></td><td><?= esc($child_name) ?></td></tr>
  <tr><td><strong>Date of Birth:</strong></td><td><?= esc($child_dob) ?></td></tr>
  <tr><td><strong>Place of Birth:</strong></td><td><?= esc($child_pob) ?></td></tr>
  <tr><td><strong>Name of Mother:</strong></td><td><?= esc($mother_name) ?></td></tr>
  <tr><td><strong>Name of Father:</strong></td><td><?= esc($father_name) ?></td></tr>
</table>
</div>

<p>
This certification is being issued upon the request of the above-named person for whatever legal purpose it may serve.
</p>

<p>
Issued this <strong><?= date('j') ?></strong> day of 
<strong><?= date('F Y') ?></strong> in Barangay Don Galo, City of Parañaque.
</p>