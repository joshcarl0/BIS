<?php
$title = $title ?? 'Barangay Clearance';
$doc_title = $doc_title ?? 'BARANGAY CLEARANCE';

$watermark_src = $watermark_src ?? 'assets/images/barangay_logo.png';
$imgBarangay = $imgBarangay ?? 'assets/images/barangay_logo.png';
$imgCity = $imgCity ?? 'assets/images/city_logo.png';
$imgBagong = $imgBagong ?? 'assets/images/bagong_pilipinas.png';

if (!function_exists('h')) {
  function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= h($title) ?></title>
  <style>
    @page { size: A4 portrait; margin: 0; }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 0;
      font-family: "Times New Roman", serif;
      color: #111;
    }

    .page {
      width: 210mm;
      height: 297mm;
      padding: 5mm;
    }

    .border-outer {
      width: 100%;
      height: 100%;
      border: 1mm solid #c79a2b;
      padding: 2mm;
    }

    .border-inner {
      width: 100%;
      height: 100%;
      border: 0.35mm solid #d7b356;
      padding: 3mm;
    }

    .frame-table {
      width: 100%;
      height: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .frame-table td {
      padding: 0;
      vertical-align: top;
    }

    .header-cell {
      height: 43mm;
      padding-bottom: 1.5mm;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .header-table td {
      vertical-align: middle;
      padding: 0;
    }

    .logo-left,
    .logo-center,
    .logo-right {
      text-align: center;
    }

    .logo-left img,
    .logo-center img {
      width: 19mm;
      height: 19mm;
    }

    .logo-right img {
      width: 28mm;
      height: 18mm;
    }

    .header-text {
      text-align: center;
      line-height: 1.1;
      padding-top: 1mm;
    }

    .hdr-rp { font-size: 12.5pt; }
    .hdr-city { font-size: 13pt; }
    .hdr-brgy {
      font-size: 20pt;
      color: #163f86;
      font-weight: bold;
    }
    .hdr-addr {
      font-size: 9.5pt;
      margin-top: 0.5mm;
    }

    .header-line {
      border-top: 0.8mm solid #1f5ea8;
      margin-top: 2mm;
    }

    .body-cell {
      height: 230mm;
    }

    .body-table {
      width: 100%;
      height: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .left-panel {
      width: 50mm;
      padding: 5mm 2.2mm 2mm 1.2mm;
      font-size: 9pt;
      text-align: center;
    }

    .divider {
      width: 1mm;
      background: #1f5ea8;
    }

    .content-panel {
      padding: 4mm 3mm 1mm 4mm;
      position: relative;
    }

    .official-item {
      margin: 0 0 2.5mm 0;
      line-height: 1.22;
    }

    .official-name {
      font-weight: bold;
      font-style: italic;
      font-size: 9.6pt;
    }

    .official-position,
    .official-committee {
      font-style: italic;
      font-size: 8.7pt;
    }

    .doc-title {
      text-align: center;
      font-size: 20pt;
      font-weight: bold;
      text-decoration: underline;
      margin: 0 0 4mm 0;
      color: #122f66;
      letter-spacing: 0.2pt;
    }

    .doc-subtitle {
      margin: 0 0 3mm 0;
      font-size: 14pt;
      font-weight: bold;
    }

    .watermark {
      position: absolute;
      top: 45mm;
      left: 12mm;
      right: 12mm;
      text-align: center;
      z-index: 0;
      opacity: 0.08;
    }

    .watermark img {
      width: 120mm;
      height: auto;
    }

    .content-wrap {
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="border-outer">
      <div class="border-inner">
        <table class="frame-table">
          <tr>
            <td class="header-cell">
              <table class="header-table">
                <tr>
                  <td class="logo-left" style="width:22mm;"><img src="<?= h($imgBarangay) ?>" alt="Barangay Logo"></td>
                  <td>
                    <div class="header-text">
                      <div class="hdr-rp">Republic of the Philippines</div>
                      <div class="hdr-city">City of Parañaque</div>
                      <div class="hdr-brgy">Barangay Don Galo</div>
                      <div class="hdr-addr">Dimatimbangan St., Barangay Don Galo, Parañaque City<br>Tel. No. (02) 8512-6512</div>
                    </div>
                  </td>
                  <td class="logo-center" style="width:22mm;"><img src="<?= h($imgCity) ?>" alt="City Logo"></td>
                  <td class="logo-right" style="width:30mm;"><img src="<?= h($imgBagong) ?>" alt="Bagong Pilipinas"></td>
                </tr>
              </table>
              <div class="header-line"></div>
            </td>
          </tr>
          <tr>
            <td class="body-cell">
              <table class="body-table">
                <tr>
                  <td class="left-panel">
                    <?php if (!empty($officials_list) && is_array($officials_list)): ?>
                      <?php foreach ($officials_list as $official): ?>
                        <div class="official-item">
                          <div class="official-name"><?= h($official['name'] ?? '') ?></div>
                          <?php if (!empty($official['position'])): ?>
                            <div class="official-position"><?= h($official['position']) ?></div>
                          <?php endif; ?>
                          <?php if (!empty($official['committee'])): ?>
                            <div class="official-committee"><?= h($official['committee']) ?></div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </td>
                  <td class="divider"></td>
                  <td class="content-panel">
                    <div class="watermark">
                      <img src="<?= h($watermark_src) ?>" alt="Watermark">
                    </div>
                    <div class="content-wrap">
                      <div class="doc-title"><?= h($doc_title) ?></div>
                      <div class="doc-subtitle">TO WHOM IT MAY CONCERN:</div>
                      <?= $content ?>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
