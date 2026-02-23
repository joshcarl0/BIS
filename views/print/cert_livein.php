<?php
// cert_livein.php (CONTENT ONLY)

$extra = json_decode($doc['extra_json'] ?? '{}', true) ?: [];

$partner_name = $extra['partner_name'] ?? '';
$since        = $extra['since'] ?? '';

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<p>
This is to certify that <strong><?= esc($resident_name) ?></strong> and
<strong><?= esc($partner_name) ?></strong>, both of legal age, Filipino,
with present address at <strong><?= esc($resident_address) ?></strong>,
Barangay Don Galo, City of Parañaque, are living together since
<strong><?= esc($since) ?></strong>.
</p>

<p>
This certification is being issued upon the request of the above-named person
for whatever legal purpose it may serve.
</p>

<p>
Issued this <strong><?= date('j') ?></strong> day of
<strong><?= date('F Y') ?></strong> in Barangay Don Galo, City of Parañaque.
</p>