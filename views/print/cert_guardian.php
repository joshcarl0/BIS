<?php ob_start(); ?>

<?php
// READ extra fields saved from request form
$extra = json_decode($doc['extra_json'] ?? '{}', true) ?: [];

$child_name  = $extra['child_name'] ?? '';
$child_dob   = $extra['child_dob'] ?? '';
$child_pob   = $extra['child_pob'] ?? '';
$mother_name = $extra['mother_name'] ?? '';
$father_name = $extra['father_name'] ?? '';

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function lineOrBlank($v, $minWidth = '80mm'){
    $v = trim((string)$v);
    return '<span class="line-fill" style="min-width:'.$minWidth.'">'.($v !== '' ? esc($v) : '&nbsp;').'</span>';
}
?>

<style>
  .line-fill{
    display:inline-block;
    border-bottom:1px solid #111;
    padding:0 2mm 1mm 2mm;
    line-height:1.2;
    vertical-align:baseline;
  }
  .field-row{ margin: 2mm 0; }
  .field-label{ display:inline-block; width: 35mm; }
</style>

<p>
This is to certify that <b><?= esc($resident_name) ?></b>
is a bonafide resident of this barangay with postal address at
<b><?= esc($resident_address) ?></b>.
</p>

<p>
Further certifies that as per our records, the above-mentioned
name is the legal guardian/parent of the child below.
</p>

<div class="field-row"><span class="field-label">Name of Child:</span> <?= lineOrBlank($child_name) ?></div>
<div class="field-row"><span class="field-label">Date of Birth:</span> <?= lineOrBlank($child_dob) ?></div>
<div class="field-row"><span class="field-label">Place of Birth:</span> <?= lineOrBlank($child_pob) ?></div>
<div class="field-row"><span class="field-label">Name of Mother:</span> <?= lineOrBlank($mother_name) ?></div>
<div class="field-row"><span class="field-label">Name of Father:</span> <?= lineOrBlank($father_name) ?></div>

<p>
This certification is being issued upon the request of the above-named
person for whatever legal purpose it may serve.
</p>

<p>
Issued this <b><?= date('j') ?></b> day of <b><?= date('F Y') ?></b>
in Barangay Don Galo, City of Parañaque.
</p>

<?php
$title = "CERTIFICATION";
$doc_title = "CERTIFICATION"; // if your new layout uses doc_title
$content = ob_get_clean();

// use your master layout (change to layout_clearance.php if that's your new one)
require __DIR__ . "/layout.php";
