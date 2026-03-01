<?php
// expected vars: $doc_title, $name, $address, $purpose, $day,$month,$year,
// $captain_name, $photo_src, $thumb_src, $cert_no,$or_no,$amount,$date_paid
?>
<style>
  .title{ text-align:center; font-size:18pt; font-weight:900; text-decoration:underline; margin:2mm 0 3mm 0; }
  .sub{ font-weight:700; font-size:10.5pt; margin: 0 0 3mm 0; }
  .p{ font-size:10.5pt; line-height:1.35; text-align:justify; margin: 0 0 3mm 0; }

  .sign-name{ margin-top:8mm; text-align:right; font-weight:bold; }
  .sign-pos{ text-align:right; font-style:italic; margin-top:1mm; }

  .box-row{ width:100%; border-collapse:collapse; margin-top:6mm; }
  .box{ width:42mm; height:42mm; border:1pt solid #000; text-align:center; vertical-align:middle; font-size:8pt; }
  .thumb-cap{ font-size:7.5pt; margin-top:1mm; text-align:center; }

  .sigline{ margin-top:4mm; border-top:1pt solid #000; width:90mm; margin-left:auto; text-align:center; font-size:9pt; padding-top:2mm; }

  .receipt{ margin-top:5mm; font-size:9pt; }
  .receipt td{ padding:0.5mm 0; }
  .rlabel{ width:40mm; font-weight:bold; }
  .rcolon{ width:4mm; text-align:center; font-weight:bold; }
  .rline{ border-bottom:0.6pt solid #000; width:55mm; height:4mm; }
</style>

<div class="title">BARANGAY CLEARANCE</div>
<div class="sub">TO WHOM IT MAY CONCERN:</div>

<p class="p">
  This is to certify that <b><?= htmlspecialchars($name) ?></b>, whose photograph, signature and right thumb mark appears below,
  is a bonafide resident of <b><?= htmlspecialchars($address) ?></b>, <b>DON GALO, PARAÑAQUE CITY</b>.
</p>

<p class="p">
 This certification is issued upon the request of the above-mentioned individual 
for the purpose of 
<?php
$purposeText = $document_name ?? '';
$parts = array_map('trim', explode(' - ', $purposeText));
$last = end($parts);
if ($last !== false && $last !== '') {
  $purposeText = $last;
}
?>
<b><?= htmlspecialchars($purposeText) ?></b>
and valid only for three (3) months from date issued.
</p>

<p class="p">
  Issued this <?= htmlspecialchars($day) ?> day of <?= htmlspecialchars($month) ?>, <?= htmlspecialchars($year) ?>
  in Barangay Don Galo City of Parañaque.
</p>

<div class="sign-name"><?= htmlspecialchars($captain_name) ?></div>
<div class="sign-pos">Punong Barangay</div>

<table class="box-row">
  <tr>
    <td class="box">
      <?php if (!empty($photo_src)): ?>
        <img src="<?= htmlspecialchars($photo_src) ?>" style="width:100%; height:100%; object-fit:cover;">
      <?php else: ?>
        PICTURE
      <?php endif; ?>
    </td>

    <td class="box">
      <?php if (!empty($thumb_src)): ?>
        <img src="<?= htmlspecialchars($thumb_src) ?>" style="width:100%; height:100%; object-fit:contain;">
      <?php endif; ?>
    </td>
  </tr>

  <tr>
    <td style="text-align:center; font-size:7.5pt;">PICTURE</td>
    <td></td>
    <td style="text-align:center; font-size:7.5pt;">RIGHT THUMBMARK</td>
  </tr>

</table>

<div class="sigline">Signature over Printed Name</div>

<table class="receipt">
  <tr><td class="rlabel">BARANGAY CERT. NO.</td><td class="rcolon">:</td><td><div class="rline"><?= htmlspecialchars($cert_no) ?></div></td></tr>
  <tr><td class="rlabel">OFFICIAL RECEIPT</td><td class="rcolon">:</td><td><div class="rline"><?= htmlspecialchars($or_no) ?></div></td></tr>
  <tr><td class="rlabel">AMOUNT</td><td class="rcolon">:</td><td><div class="rline"><?= htmlspecialchars($amount) ?></div></td></tr>
  <tr><td class="rlabel">DATED PAID</td><td class="rcolon">:</td><td><div class="rline"><?= htmlspecialchars($date_paid) ?></div></td></tr>
</table>

<div style="margin-top:2mm; font-size:8.5pt;"><i>OR not valid without OFFICIAL SEAL.</i></div>