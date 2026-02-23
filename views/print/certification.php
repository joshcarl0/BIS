<?php
if (!function_exists('esc')) {
    function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>

<p>
This is to certify that <strong><?= esc($resident_name) ?></strong>, of legal age,
Filipino, with present address at <strong><?= esc($resident_address) ?></strong>,
Barangay Don Galo, City of Parañaque.
</p>

<p>
This certification is being issued upon the request of the above-named
person for whatever legal purpose it may serve.
</p>

<p>
Issued this <strong><?= date('j') ?></strong> day of <strong><?= date('F Y') ?></strong>
in Barangay Don Galo, City of Parañaque.
</p>
