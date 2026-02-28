<?php
$title = $title ?? 'Barangay Clearance';
$content = $content ?? '';

$imgBarangay = $imgBarangay ?? '../../BIS/assets/images/barangay_logo.png';
$imgCity = $imgCity ?? '../../BIS/assets/images/city_logo.png';
$imgBagong = $imgBagong ?? '../../BIS/assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? $imgBarangay;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>
<style>
  @page { size: A4; margin: 12mm; }
  html, body { margin: 0; padding: 0; }
  body { font-family: "Times New Roman", serif; color: #111; font-size: 12pt; }

  .page {
    border: 1.1mm solid #c9a227;
    padding: 1.6mm;
  }

  .inner-page {
    border: 0.3mm solid #c9a227;
    padding: 1.8mm 2.2mm 2mm 2.2mm;
  }

  .header-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .header-table td {
    vertical-align: middle;
    text-align: center;
  }

  .logo-col { width: 24mm; }
  .logo-col-right { width: 29mm; }

  .logo { width: 21mm; height: auto; }
  .logo-small-gap { height: 1.2mm; }

  .header-top { font-size: 7.8mm; line-height: 1.05; font-weight: bold; }
  .header-mid { font-size: 7.1mm; line-height: 1.05; font-weight: bold; }
  .header-brgy {
    font-size: 10.5mm;
    line-height: 1;
    margin-top: 0.8mm;
    font-weight: bold;
    color: #1f3f7a;
  }
  .header-address {
    margin-top: 0.8mm;
    font-size: 3.9mm;
    line-height: 1.15;
    font-weight: bold;
  }

  .line-blue { border-top: 0.55mm solid #1f4f95; margin-top: 1.8mm; }
  .line-gold { border-top: 0.2mm solid #c9a227; margin-top: 0.8mm; }

  .main-layout {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 1.2mm;
  }

  .left-panel {
    width: 41mm;
    vertical-align: top;
    padding: 4.2mm 2.6mm 1mm 0.5mm;
    font-size: 3.8mm;
    line-height: 1.15;
    text-align: center;
  }

  .left-name {
    font-weight: bold;
    margin-bottom: 0.4mm;
  }

  .left-role {
    font-style: italic;
    font-weight: bold;
    margin-bottom: 2.2mm;
  }

  .left-committee {
    font-style: italic;
    margin-top: -1.5mm;
    margin-bottom: 2.5mm;
  }

  .divider-col {
    width: 1.7mm;
    border-left: 0.55mm solid #1f4f95;
    border-right: 0.2mm solid #c9a227;
  }

  .right-panel {
    vertical-align: top;
    padding: 3.2mm 3.2mm 2.5mm 3.4mm;
    position: relative;
  }

  .watermark {
    position: absolute;
    left: 4mm;
    top: 22mm;
    right: 4mm;
    text-align: center;
    z-index: 0;
    opacity: 0.10;
  }

  .watermark img {
    width: 118mm;
    height: auto;
  }

  .content {
    position: relative;
    z-index: 2;
    min-height: 223mm;
  }

  .doc-title {
    text-align: center;
    color: #1f3f7a;
    font-size: 14.6mm;
    font-weight: bold;
    text-decoration: underline;
    margin: 0.5mm 0 6mm 0;
    line-height: 1;
  }

  .left-item { margin-bottom: 1.2mm; }


  .concern {
    font-size: 8.3mm;
    font-weight: bold;
    margin: 0 0 4mm 0;
  }

  .body-paragraph {
    font-size: 7.8mm;
    line-height: 1.35;
    text-align: justify;
    margin: 0 0 4.3mm 0;
  }

  .indent { text-indent: 8mm; }

  .issued-line { margin-bottom: 8.5mm; }

  .signature-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 8.5mm 0;
  }

  .captain-cell {
    width: 55mm;
    text-align: center;
    vertical-align: top;
  }

  .captain-name {
    font-size: 9.2mm;
    font-weight: bold;
    text-decoration: underline;
    line-height: 1;
  }

  .captain-title {
    font-size: 8.7mm;
    font-weight: bold;
    margin-top: 0.8mm;
  }

  .proof-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 7mm;
  }

  .proof-box {
    width: 33mm;
    height: 33mm;
    border: 0.3mm solid #666;
    text-align: center;
    vertical-align: middle;
    display: table;
  }

  .thumb-box { margin-left: auto; }

  .proof-label {
    display: table-cell;
    vertical-align: middle;
    font-size: 6.4mm;
  }

  .thumb-label {
    vertical-align: bottom;
    padding-bottom: 1.2mm;
    font-size: 5.1mm;
  }

  .proof-image {
    width: 33mm;
    height: 33mm;
  }

  .sig-row {
    text-align: right;
    padding-top: 5.5mm;
  }

  .sig-line {
    width: 62mm;
    border-top: 0.3mm solid #333;
    margin-left: auto;
  }

  .sig-caption {
    width: 62mm;
    text-align: center;
    font-size: 6.3mm;
    margin-left: auto;
    margin-top: 1mm;
  }

  .receipt-table {
    width: 72mm;
    border-collapse: collapse;
    margin-top: 3.5mm;
    font-size: 7.8mm;
    font-weight: bold;
  }

  .receipt-label { width: 45mm; }
  .receipt-colon { width: 4mm; }

  .receipt-value {
    border-bottom: 0.3mm solid #333;
    width: 23mm;
  }

  .receipt-note {
    margin-top: 3mm;
    font-size: 6.2mm;
    font-style: italic;
    font-weight: bold;
  }

  p { margin: 0; }
</style>
</head>
<body>
<div class="page">
  <div class="inner-page">
    <table class="header-table" cellpadding="0" cellspacing="0">
      <tr>
        <td class="logo-col"><img class="logo" src="<?= htmlspecialchars($imgBarangay) ?>" alt="Barangay Logo"></td>
        <td>
          <div class="header-top">Republic of the Philippines</div>
          <div class="header-mid">City of Parañaque</div>
          <div class="header-brgy">Barangay Don Galo</div>
          <div class="header-address">Dimatimbangan St., Barangay Don Galo, Parañaque City<br>Tel. No. (02) 8812-6383</div>
        </td>
        <td class="logo-col-right">
          <img class="logo" src="<?= htmlspecialchars($imgCity) ?>" alt="City Seal">
          <div class="logo-small-gap"></div>
          <img class="logo" src="<?= htmlspecialchars($imgBagong) ?>" alt="Bagong Pilipinas">
        </td>
      </tr>
    </table>

    <div class="line-blue"></div>
    <div class="line-gold"></div>

    <table class="main-layout" cellpadding="0" cellspacing="0">
      <colgroup>
        <col style="width:41mm;">
        <col style="width:1.7mm;">
        <col>
      </colgroup>
      <tr>
        <td class="left-panel">
          <div class="left-item">
            <div class="left-name">Hon. Marilyn F. Burgos</div>
            <div class="left-role">Barangay Captain</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Rabel Bary E. Cura II</div>
            <div class="left-role">Barangay Councilor</div>
            <div class="left-committee">Chairperson on Peace &amp; Order<br>Appropriations &amp; Budget Management</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Rodauc L. Velasco</div>
            <div class="left-role">Barangay Councilor</div>
            <div class="left-committee">Committee on Agricultural Fisheries</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Pastor S. Rodriguez</div>
            <div class="left-role">Barangay Councilor</div>
            <div class="left-committee">Committee on Infrastructural Fisheries</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Reynoldo D. Bumaniag</div>
            <div class="left-role">Barangay Councilor</div>
            <div class="left-committee">Chairperson on Social and Avedeperence</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Edicardo E. Giano Jr.</div>
            <div class="left-role">Barangay Councilor</div>
            <div class="left-committee">Committee on Cooperative</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Edith Q. Jimenez</div>
            <div class="left-role">Barangay Councilor</div>
          </div>

          <div class="left-item">
            <div class="left-name">Hon. Louisa Gabriel O. Grenada</div>
            <div class="left-role">Barangay Councilor</div>
          </div>

          <div class="left-item">
            <div class="left-name">Maria Leticia N. Basa</div>
            <div class="left-role">Barangay Secretary</div>
          </div>

          <div class="left-item">
            <div class="left-name">Lourdes Linda DG. Aquino</div>
            <div class="left-role">Barangay Treasurer</div>
          </div>
        </td>

        <td class="divider-col"></td>

        <td class="right-panel">
          <div class="watermark"><img src="<?= htmlspecialchars($watermark_src) ?>" alt="Watermark"></div>
          <div class="content">
            <div class="doc-title">BARANGAY CLEARANCE</div>
            <?= $content ?>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>
</body>
</html>
