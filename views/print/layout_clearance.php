<?php
/**
 * LAYOUT: BARANGAY CLEARANCE (DOMPDF SAFE) - MATCH SAMPLE STYLE
 * - Gold outer/inner border
 * - Left officials panel + fixed blue divider
 * - Header with 3 logos + Barangay Don Galo styling
 * - Right content with watermark
 * - Footer receipt anchored near bottom
 */

$title     = $title ?? 'Barangay Clearance';
$doc_title = $doc_title ?? 'BARANGAY CLEARANCE';
$content   = $content ?? '';

$imgBarangay = $imgBarangay ?? '../../BIS/assets/images/barangay_logo.png';
$imgCity     = $imgCity ?? '../../BIS/assets/images/city_logo.png';
$imgBagong   = $imgBagong ?? '../../BIS/assets/images/bagong_pilipinas.png';

$watermark_src = $watermark_src ?? $imgBarangay;

$left_officials_html = $left_officials_html ?? '
  <div class="person">
    <div class="name">Hon. Marilyn F. Burgos</div>
    <div class="role">Barangay Captain</div>
  </div>

  <div class="person">
    <div class="name">Hon. Rafael Barry E. Cura II</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Finance &amp; Appropriation on Traffic Management</div>
  </div>

  <div class="person">
    <div class="name">Hon. Rodluck V. Lacsina</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Health and Social Services</div>
  </div>

  <div class="person">
    <div class="name">Hon. Pastor S. Rodriguez</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Peace and Order</div>
  </div>

  <div class="person">
    <div class="name">Hon. Reynaldo O. Bumagat</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Education and Culture / Committee on Cooperative</div>
  </div>

  <div class="person">
    <div class="name">Hon. Eduardo R. Giron Jr.</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Environment</div>
  </div>

  <div class="person">
    <div class="name">Hon. Editha U. Jimenez</div>
    <div class="role">Barangay Councilor</div>
  </div>

  <div class="person">
    <div class="name">Hon. Louisse Gabrielle O. Grenada</div>
    <div class="role">Barangay Councilor</div>
    <div class="committee">Committee on Health and Sanitation</div>
  </div>

  <div class="person">
    <div class="name">Hon. Dryn Allison Medina</div>
    <div class="role">SK Chairman</div>
    <div class="committee">Committee on Sports and Youth Development</div>
  </div>

  <div class="spacer"></div>

  <div class="person">
    <div class="name">Maria Leticia N. Basa</div>
    <div class="role">Barangay Secretary</div>
  </div>

  <div class="person">
    <div class="name">Lourdes Linda DG. Aquino</div>
    <div class="role">Barangay Treasurer</div>
  </div>
';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>

<style>
  @page { size: A4; margin: 0; }
  * { box-sizing: border-box; }
  html, body { margin:0; padding:0; }
  body { font-family:"Times New Roman", serif; color:#111; font-size: 12pt; }

  /* Full page */
  .page{
    position: relative;
    width: 210mm;
    height: 297mm;
    background:#fff;
    padding: 10mm;
  }

  /* GOLD BORDER like sample */
  .border-outer{
    position:absolute; left:6mm; top:6mm; right:6mm; bottom:6mm;
    border: 1.4mm solid #caa33a;
    pointer-events:none;
  }
  .border-inner{
    position:absolute; left:10mm; top:10mm; right:10mm; bottom:10mm;
    border: 0.3mm solid #caa33a;
    pointer-events:none;
  }

  /* CONTENT AREA inside borders */
  .sheet{
    position:absolute;
    left:12mm; top:12mm; right:12mm; bottom:12mm;
  }

  /* HEADER */
  .header{
    padding: 0 6mm;
    text-align:center;
  }

  .hdr-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
  }

  .hdr-left, .hdr-right{ width:34mm; vertical-align:top; }
  .hdr-center{ vertical-align:top; padding: 0 3mm; }

  .hdr-logo{
    width:26mm; height:auto; display:block; margin:0 auto;
  }

  .hdr-top{ font-size:12pt; line-height:1.1; margin:0; font-weight:700; }
  .hdr-sub{ font-size:11pt; line-height:1.1; margin:1mm 0 0 0; font-weight:700; }
  .hdr-barangay{ font-size:22pt; font-weight:800; color:#1b4f9c; margin:2mm 0 0 0; line-height:1.0; }
  .hdr-address{ font-size:9.3pt; margin-top:1mm; line-height:1.2; }

  .hdr-line-wrap{ margin-top:3mm; }
  .hdr-line-blue{ border-top:2.2px solid #1b4f9c; }
  .hdr-line-gold{ border-top:1.2px solid #caa33a; margin-top:1mm; }

  /* BODY layout */
  .body{
    position:absolute;
    left:0; right:0;
    top: 43mm;   /* under header lines */
    bottom: 0;
    padding: 0 6mm 6mm 6mm;
  }

  table.layout{
    width:100%;
    height:100%;
    border-collapse:collapse;
    table-layout:fixed;
  }

  td.leftcol{
    vertical-align:top;
    padding: 2mm 4mm 0 0;
    font-size: 8.6pt;
  }

  td.divider{
    width: 2mm;
    border-left: 2.2px solid #1b4f9c; /* blue divider */
  }

  td.rightcol{
    vertical-align:top;
    padding: 2mm 0 0 6mm;
    position:relative;
  }

  /* LEFT panel styling */
  .person{ margin: 1.3mm 0; }
  .name{ font-weight:700; }
  .role{ font-style:italic; font-size:8.1pt; margin-top:0.2mm; }
  .committee{ font-size:7.6pt; font-style:italic; margin-top:0.3mm; }
  .spacer{ height: 3mm; }

  /* RIGHT watermark */
  .watermark{
    position:absolute;
    left:0; right:0;
    top: 15mm;
    text-align:center;
    opacity:0.08;
    z-index:0;
  }
  .watermark img{
    width: 125mm;
    height:auto;
  }

  .content-wrap{
    position:relative;
    z-index:2;
    min-height: 205mm;
  }

  /* Doc title (match sample) */
  .doc-title{
    text-align:center;
    font-size:18pt;
    font-weight:800;
    text-decoration: underline;
    margin: 2mm 0 3mm 0;
    letter-spacing: 0.3px;
  }

  /* Footer receipt anchored near bottom */
  .receipt-box{
    position:absolute;
    left: 6mm;
    right: 6mm;
    bottom: 6mm;
    font-size: 10.5pt;
    z-index:2;
  }

  /* Make sure nothing breaks awkwardly */
  .header, table.layout { page-break-inside: avoid; }
</style>
</head>

<body>
  <div class="page">
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <div class="sheet">
      <!-- HEADER -->
      <div class="header">
        <table class="hdr-table" cellspacing="0" cellpadding="0">
          <tr>
            <td class="hdr-left">
              <img class="hdr-logo" src="<?= htmlspecialchars($imgBarangay) ?>" alt="Barangay Logo">
            </td>

            <td class="hdr-center">
              <p class="hdr-top">Republic of the Philippines</p>
              <p class="hdr-sub">City of Parañaque</p>
              <div class="hdr-barangay">Barangay<br>Don Galo</div>
              <div class="hdr-address">
                Dimatimbangan St., Barangay Don Galo, Parañaque City<br>
                Tel. No. (02) 8812-6512
              </div>
            </td>

            <td class="hdr-right">
              <img class="hdr-logo" src="<?= htmlspecialchars($imgCity) ?>" alt="City Logo">
              <div style="height:2mm;"></div>
              <img class="hdr-logo" src="<?= htmlspecialchars($imgBagong) ?>" alt="Bagong Pilipinas">
            </td>
          </tr>
        </table>

        <div class="hdr-line-wrap">
          <div class="hdr-line-blue"></div>
          <div class="hdr-line-gold"></div>
        </div>
      </div>

      <!-- BODY -->
      <div class="body">
        <table class="layout">
          <colgroup>
            <col style="width:48mm;">
            <col style="width:2mm;">
            <col>
          </colgroup>
          <tr>
            <td class="leftcol">
              <?= $left_officials_html ?>
            </td>

            <td class="divider"></td>

            <td class="rightcol">
              <div class="watermark">
                <img src="<?= htmlspecialchars($watermark_src) ?>" alt="Watermark">
              </div>

              <div class="content-wrap">
                <div class="doc-title"><?= htmlspecialchars($doc_title) ?></div>

                <!-- MAIN BODY CONTENT from cert_clearance.php -->
                <?= $content ?>
              </div>

              <!-- RECEIPT FIXED AT BOTTOM (like sample)
                   NOTE: Kung may receipt na rin sa $content mo, alisin mo doon para di magdoble. -->
              <div class="receipt-box">
                <!-- optional: kung gusto mo dito mismo naka-render, sabihin mo para i-Dynamic natin -->
              </div>
            </td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</body>
</html>