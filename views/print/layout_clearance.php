<?php
$title            = $title ?? 'Barangay Document';
$doc_title        = $doc_title ?? 'BARANGAY DOCUMENT';
$doc_subtitle     = $doc_subtitle ?? '';
$content          = $content ?? '';
$watermark_src    = $watermark_src ?? '/BIS/assets/images/barangay_logo.png';
$show_left_panel  = $show_left_panel ?? true;

$officials = $officials ?? [
  ['name'=>'Hon. Marilyn F. Burgos', 'role'=>'Punong Barangay'],

  ['heading'=>'Barangay Kagawad'],
  ['name'=>'Hon. Rafael Barry B. Cura III', 'committee'=>'Committee on Finance & Appropriation on Traffic Management'],
  ['name'=>'Hon. Rodluck V. Lacsina', 'committee'=>'Committee on Health and Social Services'],
  ['name'=>'Hon. Pastor S. Rodriguez', 'committee'=>'Committee on Peace and Order'],
  ['name'=>'Hon. Reynaldo O. Bumagat', 'committee'=>'Committee on Education and Culture / Cooperative'],
  ['name'=>'Hon. Eduardo R. Giron Jr.', 'committee'=>'Committee on Environment'],
  ['name'=>'Hon. Editha U. Jimenez', 'role'=>'Barangay Councilor'],
  ['name'=>'Hon. Louisse Gabrielle D. Omaña', 'role'=>'Barangay Councilor'],
  ['name'=>'Hon. Dryn Allison Medina', 'role'=>'SK Chairman'],

  ['heading'=>'Barangay Staff'],
  ['name'=>'Maria Leticia N. Busa', 'role'=>'Barangay Secretary'],
  ['name'=>'Luzviminda DG. Aquino', 'role'=>'Barangay Treasurer'],
];

if (!function_exists('renderOfficials')) {
  function renderOfficials(array $officials): string {
    $out = '';
    foreach ($officials as $o) {
      if (isset($o['heading'])) {
        $out .= '<div class="cap">'.htmlspecialchars($o['heading']).'</div>';
        continue;
      }
      $out .= '<div class="person">';
      $out .= '<div class="name">'.htmlspecialchars($o['name'] ?? '').'</div>';
      if (!empty($o['role'])) $out .= '<div class="role">'.htmlspecialchars($o['role']).'</div>';
      if (!empty($o['committee'])) $out .= '<div class="committee">'.htmlspecialchars($o['committee']).'</div>';
      $out .= '</div>';
    }
    return $out;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>

<style>
  @page { size: A4; margin: 12mm; }
  * { box-sizing: border-box; }
  body { margin:0; font-family: "Times New Roman", serif; color:#111; background:#fff; }

  /* =========================
     TUNING KNOBS (EDIT HERE)
  ========================= */
  :root{
    --pad-x: 10mm;            /* left/right inner padding */
    --pad-y: 6mm;             /* body top padding (after header) */
    --left-col: 55mm;         /* officials width */
    --gap: 8mm;               /* EXACT GAP between columns */
    --blue: #1b4f9c;
    --line-w: 0.8mm;          /* KAPAL ng vertical blue line */
    --hdr-line-w: 0.5mm;      /* KAPAL ng horizontal header line */
    --header-h: 55mm;         /* fixed header height para exact alignment */
    --wm-opacity: 0.10;

    --photo-box: 40mm;        /* picture/thumb box size */
    --photo-gap: 22mm;        /* gap between picture & thumb box */
    --sigline-w: 120mm;       /* signature line width */
  }

  .page{
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    position: relative;
    background: #fff;
  }

  /* borders */
  .border-outer{ position:absolute; inset:0; border:1.2mm solid #caa33a; pointer-events:none; }
  .border-inner{ position:absolute; inset:4mm; border:0.25mm solid #caa33a; pointer-events:none; }

  /* remove bottom arc */
  .page::after{ content:none !important; display:none !important; }

  /* =========================
        HEADER (fixed)
  ========================= */
  .header{
    position: relative;
    height: var(--header-h);
    padding: 18mm var(--pad-x) 0 var(--pad-x);
    text-align: center;
  }

  .hdr-logos{
    position:absolute;
    left: var(--pad-x);
    right: var(--pad-x);
    top: 6mm;
    display:flex;
    justify-content:space-between;
    align-items:center;
    pointer-events:none;
  }

  .logos-left img{ height: 18mm; width:auto; }
  .logos-right{ display:flex; gap: 7mm; align-items:center; }
  .logos-right img{ height: 17mm; width:auto; }

  .hdr-top{ font-size:13pt; line-height:1.1; margin:0; }
  .hdr-sub{ font-size:11pt; line-height:1.1; margin:1mm 0 0 0; }
  .hdr-barangay{
    font-size:22pt;
    font-weight:bold;
    color: var(--blue);
    margin: 2mm 0 0 0;
    line-height:1.1;
  }
  .hdr-address{ font-size:9.5pt; margin-top:1mm; line-height:1.2; }

  /* horizontal line at exact bottom of header */
  .hdr-line{
    position:absolute;
    left: var(--pad-x);
    right: var(--pad-x);
    bottom: 0;
    border-top: var(--hdr-line-w) solid var(--blue);
  }

  /* =========================
      BODY GRID (exact gap)
  ========================= */
  .body{
    position: relative;
    padding: var(--pad-y) var(--pad-x) 0 var(--pad-x);
    display:grid;
    grid-template-columns: <?= $show_left_panel ? 'var(--left-col) 1fr' : '1fr' ?>;
    column-gap: var(--gap);
    height: calc(297mm - var(--header-h) - (var(--pad-y) * 2));
  }

  /* MAIN vertical divider (CONNECTED to hdr-line) */
  .v-divider{
    position:absolute;
    width: var(--line-w);
    background: var(--blue);
    /* left edge = page inner padding + left col + half of gap */
    left: calc(var(--pad-x) + var(--left-col) + (var(--gap) / 2));
    top: var(--header-h);      /* EXACTLY where hdr-line is */
    bottom: 18mm;              /* stop before bottom border area */
    pointer-events:none;
  }

  .left{
    font-size: 9pt;
    padding-right: 0; /* gap already handled by grid */
  }
  .cap{ font-weight:bold; margin-top:4mm; }
  .person{ margin:2mm 0; }
  .name{ font-weight:bold; }
  .role{ font-style:italic; }
  .committee{ font-size:8pt; }

  .right{ position:relative; }

  /* watermark */
  .watermark{
    position:absolute;
    inset:0;
    display:flex;
    justify-content:center;
    align-items:center;
    opacity: var(--wm-opacity);
    pointer-events:none;
    z-index: 0;
  }
  .watermark img{ width: 150mm; height:auto; }

  .content-wrap{ position:relative; z-index: 2; }

  /* titles */
  .doc-title{
    text-align:center;
    font-size:24pt;
    font-weight:800;
    text-decoration: underline;
    margin-top: 4mm;
  }
  .doc-subtitle{
    margin-top: 8mm;
    font-size: 13pt;
    font-weight: bold;
  }

  /* =========================
     SHARED COMPONENTS
     (Use these in ALL docs)
  ========================= */

  /* picture + thumb row */
  .photo-thumb-row{
    display:flex;
    gap: var(--photo-gap);
    margin-top: 18mm;
    margin-bottom: 6mm;
  }
  .photo-box{
    width: var(--photo-box);
    height: var(--photo-box);
    border: 0.25mm solid #666;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 10pt;
    letter-spacing: 0.5pt;
  }

  /* signature line centered */
  .sigline{
    width: var(--sigline-w);
    margin: 8mm 0 0 0;
    border-top: 0.25mm solid #333;
    padding-top: 2mm;
    text-align:center;
    font-size: 10pt;
  }

  /* receipt/meta block bottom-left look */
  .receipt-meta{
    margin-top: 10mm;
    font-size: 10pt;
    font-weight: 700;
  }
  .receipt-meta .row{ margin: 1mm 0; font-weight:700; }
  .receipt-meta .row span{ font-weight:400; }

  .note{
    margin-top: 4mm;
    font-size: 9pt;
    font-style: italic;
  }

  @media print{
    .no-print{ display:none !important; }
  }
</style>
</head>

<body>
<div class="page">
  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <!-- HEADER -->
  <div class="header">
    <div class="hdr-logos">
      <div class="logos-left">
        <img src="/BIS/assets/images/barangay_logo.png" alt="Barangay Logo">
      </div>

      <div class="logos-right">
        <img src="/BIS/assets/images/city_logo.png" alt="City Logo">
        <img src="/BIS/assets/images/bagong_pilipinas.png" alt="Bagong Pilipinas">
      </div>
    </div>

    <div class="hdr-top">Republic of the Philippines</div>
    <div class="hdr-sub">City of Parañaque</div>
    <div class="hdr-barangay">Barangay Don Galo</div>
    <div class="hdr-address">
      Dimatimbangan St., Barangay Don Galo, Parañaque City<br>
      Tel. No. (02) 8812-6383
    </div>

    <div class="hdr-line"></div>
  </div>

  <!-- CONNECTED DIVIDER -->
  <?php if ($show_left_panel): ?>
    <div class="v-divider"></div>
  <?php endif; ?>

  <!-- BODY -->
  <div class="body">
    <?php if($show_left_panel): ?>
      <div class="left">
        <?= renderOfficials($officials) ?>
      </div>
    <?php endif; ?>

    <div class="right">
      <div class="watermark">
        <img src="<?= htmlspecialchars($watermark_src) ?>" alt="Watermark">
      </div>

      <div class="content-wrap">
        <div class="doc-title"><?= htmlspecialchars($doc_title) ?></div>

        <?php if(!empty($doc_subtitle)): ?>
          <div class="doc-subtitle"><?= htmlspecialchars($doc_subtitle) ?></div>
        <?php endif; ?>

        <?= $content ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
