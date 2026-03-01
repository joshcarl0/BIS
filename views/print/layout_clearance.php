<?php
/**
 * Layout template for clearance-type documents
 */

$imgBarangay = $imgBarangay ?? '/assets/images/barangay_logo.png';
$imgCity     = $imgCity     ?? '/assets/images/city_logo.png';
$imgBagong   = $imgBagong   ?? '/assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? '/assets/images/barangay_logo.png';

if (empty($officials_list)) {
    $officials_list = [
        ['name' => 'Hon. Marilyn F. Burgos', 'position' => 'Barangay Captain'],
        ['name' => 'Hon. Rafael Barry B. Cura III', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Finance & Appropriations & Budget / Committee on Traffic Management'],
        ['name' => 'Hon. Rodluck V. Locsina', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Health and Social Services'],
        ['name' => 'Hon. Pastor S. Rodriguez', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Peace and Order'],
        ['name' => 'Hon. Reynaldo O. Bumagat', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Education and Culture / Committee on Cooperative'],
        ['name' => 'Hon. Eduardo R. Giron Jr.', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Environment'],
        ['name' => 'Hon. Editha U. Jimenez', 'position' => 'Barangay Councilor'],
        ['name' => 'Hon. Louisse Gabrielle D. Omaña', 'position' => 'Barangay Councilor'],
        ['name' => 'Hon. Dryn Allison Medina', 'position' => 'SK Chairman', 'committee' => 'Committee on Sports and Youth Development'],
        ['name' => 'Maria Leticia N. Basa', 'position' => 'Barangay Secretary'],
        ['name' => 'Luzviminda DG. Aquino', 'position' => 'Barangay Treasurer'], // excluded below
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { size: A4; margin: 0; }
* { box-sizing: border-box; }

html, body { width: 210mm; height: 297mm; margin: 0; padding: 0; }
body { font-family: "Times New Roman", serif; color: #111; }

.page { position: relative; width: 210mm; height: 297mm; background: #fff; }

.border-outer {
    position: absolute; left: 6mm; top: 6mm; right: 6mm; bottom: 6mm;
    border: 1.2mm solid #caa33a;
}
.border-inner {
    position: absolute; left: 9.5mm; top: 9.5mm; right: 9.5mm; bottom: 9.5mm;
    border: 0.3mm solid #caa33a;
}

.sheet { position: relative; padding: 12mm; }

.hdr-table { width: 100%; border-collapse: collapse; }
.hdr-brgy {
    font-size: 24pt;
    font-weight: 900;
    color: #1b4f9c;
    text-transform: uppercase;
}
.line-blue { border-top: 2.5pt solid #1b4f9c; margin-top: 2mm; }
.line-gold { border-top: 1.2pt solid #caa33a; margin-top: 1mm; }

.main-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5mm;
    table-layout: fixed;
}

.sidebar {
    width: 42mm;
    vertical-align: top;
    position: relative;
    border-right: none;
    padding-right: 2mm;
    text-align: center;
    padding-bottom: 25mm; /* makes it visually shorter */
}

.sidebar::after {
    content: "";
    position: absolute;
    display: block;
    top: 20mm;
    right: 0;
    width: 2px;
    height: 150mm;
    background: #1b4f9c;
}

.person { margin-bottom: 2mm; line-height: 1.1; }
.off-name { font-weight: bold; font-size: 7.5pt; text-transform: uppercase; display: block; }
.off-pos { font-style: italic; font-size: 6.5pt; display: block; }
.off-comm { font-size: 6pt; font-style: italic; display: block; color: #444; }

.content-area {
    vertical-align: top;
    padding-left: 8mm;
    position: relative;
}

.watermark {
    position: absolute;
    top: 55mm;
    left: 0;
    right: 0;
    text-align: center;
    z-index: 0;
}
.watermark img { width: 115mm; opacity: 0.08; }

.doc-title {
    text-align: center;
    font-size: 19pt;
    font-weight: bold;
    text-decoration: underline;
    color: #1b4f9c;
    margin: 3mm 0 5mm 0;
}

.to-whom { font-weight: bold; margin-bottom: 4mm; font-size: 11pt; }
.para { text-align: justify; text-indent: 10mm; font-size: 11pt; line-height: 1.4; }

.bottom-area { margin-top: 8mm; }

.box { border: 1pt solid #000; width: 30mm; height: 30mm; position: relative; }
.box-label { position: absolute; bottom: 1mm; width: 100%; text-align: center; font-size: 6pt; }

.sig-underline {
    border-top: 1pt solid #000;
    width: 55mm;
    margin: 0 auto;
    text-align: center;
    font-size: 9pt;
    padding-top: 3px;
}
</style>
</head>

<body>
<div class="page">
<div class="border-outer"></div>
<div class="border-inner"></div>

<div class="sheet">

<table class="hdr-table">
<tr>
<td style="width:25mm;"><img src="<?= htmlspecialchars($imgBarangay) ?>" style="width:22mm;"></td>
<td style="text-align:center;">
<div style="font-weight:bold; font-size:12pt;">Republic of the Philippines</div>
<div style="font-weight:bold; font-size:12pt;">City of Parañaque</div>
<div class="hdr-brgy">Barangay Don Galo</div>
<div style="font-size:8.5pt;">Dimatalang St., Barangay Don Galo, Parañaque City</div>
<div style="font-size:8.5pt;">Tel. No.: (02) 8531-6612</div>
</td>
<td style="width:55mm; text-align:right;">
<img src="<?= htmlspecialchars($imgCity) ?>" style="width:20mm; margin-right:2mm;">
<img src="<?= htmlspecialchars($imgBagong) ?>" style="width:26mm;">
</td>
</tr>
</table>

<div class="line-blue"></div>
<div class="line-gold"></div>

<table class="main-table">
<tr>
<td class="sidebar">

<?php foreach (array_slice($officials_list, 0, 10) as $o): ?>
<div class="person">
<span class="off-name"><?= htmlspecialchars($o['name']) ?></span>
<span class="off-pos"><?= htmlspecialchars($o['position']) ?></span>
<?php if (!empty($o['committee'])): ?>
<span class="off-comm"><?= htmlspecialchars($o['committee']) ?></span>
<?php endif; ?>
</div>
<?php endforeach; ?>

</td>

<td class="content-area">

<div class="watermark">
<img src="<?= htmlspecialchars($watermark_src) ?>">
</div>

<?= $content ?>

<div class="bottom-area">
<table width="100%">
<tr>
<td style="width:35mm;">
<div class="box"><div class="box-label">PICTURE</div></div>
</td>
<td style="width:35mm; padding-left:4mm;">
<div class="box"><div class="box-label">RIGHT THUMBMARK</div></div>
</td>
<td style="vertical-align:bottom;">
<div class="sig-underline">Signature over Printed Name</div>
</td>
</tr>
</table>

<div style="margin-top:4mm; font-size:10pt;">
<b>BARANGAY CERT. NO.:</b> <?= htmlspecialchars($cert_no ?? '') ?><br>
<b>OFFICIAL RECEIPT:</b> <?= htmlspecialchars($or_no ?? '') ?><br>
<b>AMOUNT:</b> <?= htmlspecialchars($amount ?? '') ?><br>
<b>DATE PAID:</b> <?= htmlspecialchars($date_paid ?? '') ?><br>
<i>OR not valid without OFFICIAL SEAL.</i>
</div>

</div>

</td>
</tr>
</table>

</div>
</div>
</body>
</html>
