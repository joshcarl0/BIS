<?php
$title     = $title ?? 'Barangay Clearance';
$doc_title = $doc_title ?? 'BARANGAY CLEARANCE';
$doc_subtitle = $doc_subtitle ?? 'TO WHOM IT MAY CONCERN:';
$show_left_panel = $show_left_panel ?? true;

/**
 * DOMPDF + CHROOT SAFE (NO /BIS prefix)
 * your chroot is BIS root already.
 */
$watermark_src = $watermark_src ?? 'assets/images/barangay_logo.png';
$imgBarangay   = $imgBarangay   ?? 'assets/images/barangay_logo.png';
$imgCity       = $imgCity       ?? 'assets/images/city_logo.png';
$imgBagong     = $imgBagong     ?? 'assets/images/bagong_pilipinas.png';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= h($title) ?></title>

<style>
  @page { size: A4; margin: 0; }

  *{ box-sizing:border-box; }
  body{ margin:0; padding:0; font-family:"Times New Roman", serif; color:#111; }

  :root{
    --gold:#caa33a;
    --blue:#1b4f9c;

    --padX: 12mm;
    --padTop: 10mm;

    --headerH: 40mm;

    --leftW: 54mm;
    --gap: 6mm;

    --dividerW: 1.2mm; /* like sample thick blue */
  }

  .page{
    position:relative;
    width:210mm;
    height:297mm;
    background:#fff;
  }

  /* double gold border */
  .border-outer{ position:absolute; inset:6mm; border:1.3mm solid var(--gold); }
  .border-inner{ position:absolute; inset:10mm; border:0.35mm solid var(--gold); }

  /* HEADER */
  .header{
    position:absolute;
    left:10mm; right:10mm;
    top:10mm;
    height: var(--headerH);
    text-align:center;
  }

  .logos{
    position:absolute;
    left:0; right:0;
    top:0;
    height:18mm;
  }
  .logo-left{ position:absolute; left:0; top:0; width:20mm; height:20mm; }
  .logo-mid { position:absolute; left:50%; top:0; transform:translateX(-50%); width:20mm; height:20mm; }
  .logo-right{ position:absolute; right:0; top:1mm; width:26mm; height:18mm; }

  .logos img{ width:100%; height:100%; object-fit:contain; }

  .hdr-top{
    margin-top: 16mm;
    font-size: 13pt;
    letter-spacing: 0.2pt;
  }
  .hdr-sub{ font-size: 12pt; margin-top: 0.5mm; }
  .hdr-brgy{
    font-size: 23pt;
    font-weight: 800;
    color: var(--blue);
    margin-top: 1mm;
  }
  .hdr-addr{ font-size: 10pt; margin-top: 0.5mm; line-height:1.2; }

  /* blue horizontal line below header */
  .hdr-line{
    position:absolute;
    left:0; right:0;
    bottom: 0;
    height:0;
    border-top: 0.8mm solid var(--blue);
  }

  /* BODY WRAP */
  .body{
    position:absolute;
    left:10mm; right:10mm;
    top: calc(10mm + var(--headerH));
    bottom: 10mm;
    padding: 0;
  }

  /* vertical blue divider (ABSOLUTE) */
  .v-divider{
    position:absolute;
    top: 0;
    bottom: 0;
    left: calc(var(--leftW) + (var(--gap) / 2));
    width: var(--dividerW);
    background: var(--blue);
  }

  .left{
    position:absolute;
    top: 0;
    left: 0;
    width: var(--leftW);
    bottom: 0;
    padding-top: 8mm;
    font-size: 9.2pt;
  }
  .right{
    position:absolute;
    top: 0;
    left: calc(var(--leftW) + var(--gap));
    right: 0;
    bottom: 0;
    padding-top: 8mm;
    padding-left: 2mm;
    padding-right: 2mm;
  }

  .person{ margin: 2.4mm 0; }
  .name{ font-weight: 800; }
  .role{ font-style: italic; }
  .committee{ font-size: 8.2pt; font-style: italic; }

  /* watermark behind content */
  .watermark{
    position:absolute;
    inset: 0;
    text-align:center;
    opacity: 0.10;
    z-index: 0;
  }
  .watermark img{
    margin-top: 18mm;
    width: 150mm;
    height:auto;
  }
  .content{ position:relative; z-index: 2; }

  /* title look same */
  .doc-title{
    text-align:center;
    font-size: 26pt;
    font-weight: 900;
    text-decoration: underline;
    margin-top: 2mm;
  }
  .doc-subtitle{
    margin-top: 4mm;
    font-size: 12.5pt;
    font-weight: 800;
  }
</style>
</head>

<body>
<div class="page">
  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <div class="header">
    <div class="logos">
      <div class="logo-left"><img src="<?= h($imgBarangay) ?>" alt="Barangay Logo"></div>
      <div class="logo-mid"><img src="<?= h($imgCity) ?>" alt="City Logo"></div>
      <div class="logo-right"><img src="<?= h($imgBagong) ?>" alt="Bagong Pilipinas"></div>
    </div>

    <div class="hdr-top">Republic of the Philippines</div>
    <div class="hdr-sub">City of Parañaque</div>
    <div class="hdr-brgy">Barangay Don Galo</div>
    <div class="hdr-addr">
      Dimatimbangan St., Barangay Don Galo, Parañaque City<br>
      Tel. No. (02) 8812-6512
    </div>

    <div class="hdr-line"></div>
  </div>

  <div class="body">
    <?php if($show_left_panel): ?>
      <div class="v-divider"></div>
      <div class="left">
        <?php
          // Use controller-provided officials_list if available
          if (!empty($officials_list) && is_array($officials_list)) {
            foreach ($officials_list as $o) {
              echo '<div class="person">';
              echo '<div class="name">'.h($o['name'] ?? '').'</div>';
              if (!empty($o['position'])) echo '<div class="role">'.h($o['position']).'</div>';
              if (!empty($o['committee'])) echo '<div class="committee">'.h($o['committee']).'</div>';
              echo '</div>';
            }
          } else if (!empty($officials) && is_array($officials)) {
            foreach ($officials as $o) {
              echo '<div class="person">';
              echo '<div class="name">'.h($o['name'] ?? '').'</div>';
              if (!empty($o['role'])) echo '<div class="role">'.h($o['role']).'</div>';
              if (!empty($o['committee'])) echo '<div class="committee">'.h($o['committee']).'</div>';
              echo '</div>';
            }
          }
        ?>
      </div>
    <?php endif; ?>

    <div class="right">
      <div class="watermark"><img src="<?= h($watermark_src) ?>" alt="Watermark"></div>

      <div class="content">
        <div class="doc-title"><?= h($doc_title) ?></div>
        <div class="doc-subtitle"><?= h($doc_subtitle) ?></div>

        <!-- body content from cert_clearance.php -->
        <?= $content ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>