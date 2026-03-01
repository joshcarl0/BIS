<?php
$imgBarangay   = $imgBarangay   ?? 'assets/images/barangay_logo.png';
$imgCity       = $imgCity       ?? 'assets/images/city_logo.png';
$imgBagong     = $imgBagong     ?? 'assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? 'assets/images/barangay_logo.png';

$fontOldEnglish = '../../BIS/assets/fonts/UnifrakturCook-Bold.ttf';

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { size: A4; margin: 0; }
* { box-sizing: border-box; }
html, body { width:210mm; height:297mm; margin:0; padding:0; }
body { font-family:"Times New Roman", serif; color:#111; }

@font-face{
  font-family: "OldEnglishLocal";
  src: url("<?= htmlspecialchars($fontOldEnglish, ENT_QUOTES, 'UTF-8') ?>") format("truetype");
  font-weight: normal;
  font-style: normal;
}

.page { position:relative; width:210mm; height:297mm; background:#fff; overflow:hidden; }

/* Borders */
.border-outer { position:absolute; left:6mm; top:6mm; right:6mm; bottom:6mm; border:1mm solid #caa33a; }
.border-inner { position:absolute; left:9mm; top:9mm; right:9mm; bottom:9mm; border:0.35mm solid #caa33a; }

/* Inside padding */
.sheet { position:relative; padding: 10mm 18mm; z-index:2; }

/* Header */

.hdr-republic{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size: 14pt;
  font-weight: normal;
  text-align: center;
  line-height: 1.1;
  margin: 0;
}

.hdr-city{
  font-family: "OldEnglishLocal", "Times New Roman", serif;
  font-size: 12pt;
  font-weight: normal;
  text-align: center;
  line-height: 1.1;
  margin: 1mm 0 0 0;
}

.hdr-table { width:100%; border-collapse:collapse; }
.hdr-brgy { font-size:22pt; font-weight:900; color:#1b4f9c; text-transform:uppercase; line-height:1; }
.line-blue { border-top:2.5pt solid #1b4f9c; margin-top:2mm; }
.line-gold { border-top:1.2pt solid #caa33a; margin-top:1mm; }

/* Watermark */
.watermark{
  position:absolute;
  top:65mm;
  left:0; right:0;
  text-align:center;
  z-index:1;
}
.watermark img{ width:125mm; opacity:0.10; }

/* FULL WIDTH CONTENT */
.content{
  margin-top:6mm;
  position:relative;
  z-index:2;
}

table, tr, td { page-break-inside:avoid; break-inside:avoid; }
</style>
</head>

<body>
<div class="page">
  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <div class="watermark">
    <img src="<?= htmlspecialchars($watermark_src) ?>">
  </div>

  <div class="sheet">
    <table class="hdr-table">
      <tr>
        <td width="75">
          <img src="<?= htmlspecialchars($imgBarangay) ?>" width="70">
        </td>

        <td align="center">
          <div class="hdr-republic">Republic of the Philippines</div>
          <div class="hdr-city">City of Parañaque</div>
          <div class="hdr-brgy">Barangay Don Galo</div>
          <div style="font-size:8.5pt;">Dimatimbang St., Barangay Don Galo, Parañaque City</div>
          <div style="font-size:8.5pt;">Tel. No.: (02) 8531-6612</div>
        </td>

        <td width="140" align="right">
          <img src="<?= htmlspecialchars($imgCity) ?>" width="60" style="margin-right:6px;">
          <img src="<?= htmlspecialchars($imgBagong) ?>" width="70">
        </td>
      </tr>
    </table>

    <div class="line-blue"></div>
    <div class="line-gold"></div>

    <!-- FULL WIDTH CLEARANCE CONTENT -->
    <div class="content">
      <?= $content ?>
    </div>

  </div>
</div>
</body>
</html>