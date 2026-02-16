<?php
// Expected variables:
// $title, $content, $officials_list,
// $cert_no, $or_no, $amount, $date_paid,
// $resident_photo_url, $resident_thumb_url,
// $resident_signatory_name
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Barangay Clearance') ?></title>
<style>
  @page { size: A4; margin: 9mm; }
  html, body { margin: 0; padding: 0; font-family: "Times New Roman", serif; color: #1b1b1b; }
  * { box-sizing: border-box; }

  .page {
    position: relative;
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    padding: 12mm;
    overflow: hidden;
  }

  .gold-outer,
  .gold-inner {
    position: absolute;
    inset: 6mm;
    border: 2px solid #c89c2d;
    pointer-events: none;
  }

  .gold-inner {
    inset: 8.2mm;
    border-width: 1px;
  }

  .bottom-curve {
    position: absolute;
    left: 9mm;
    right: 9mm;
    bottom: 8mm;
    height: 21mm;
    border-top: 2px solid #c89c2d;
    border-radius: 50% 50% 0 0;
    pointer-events: none;
  }

  .watermark {
    position: absolute;
    width: 145mm;
    top: 52%;
    left: 54%;
    transform: translate(-50%, -50%);
    opacity: 0.09;
    z-index: 0;
  }

  .header {
    position: relative;
    z-index: 2;
    margin-bottom: 5mm;
  }

  .header-main {
    display: grid;
    grid-template-columns: 23mm 1fr 23mm 34mm;
    align-items: center;
    gap: 3mm;
  }

  .header-logo { width: 22mm; height: 22mm; object-fit: contain; margin: 0 auto; display: block; }
  .header-bagong { width: 32mm; height: auto; object-fit: contain; margin: 0 auto; display: block; }

  .head-text { text-align: center; line-height: 1.1; }
  .head-rp { font-size: 6.3mm; letter-spacing: 0.2px; }
  .head-city { font-size: 5mm; }
  .head-brgy { font-size: 9.5mm; color: #20458c; font-weight: 700; margin-top: 0.5mm; }
  .head-sub { font-size: 3.9mm; margin-top: 0.3mm; }

  .head-rule {
    margin-top: 2.2mm;
    border-top: 2px solid #1e4fa8;
    border-bottom: 1.4px solid #c89c2d;
    height: 0;
  }

  .body-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 55mm 1fr;
    min-height: 230mm;
    border-top: 0;
  }

  .left-col {
    border-right: 2px solid #1e4fa8;
    padding: 8mm 4mm 6mm 2mm;
    font-size: 4.6mm;
    text-align: center;
  }

  .official { margin-bottom: 6mm; }
  .official-name { font-style: italic; font-weight: 700; display: block; }
  .official-position { font-weight: 700; display: block; margin-top: 0.2mm; }
  .official-committee { font-style: italic; display: block; margin-top: 0.4mm; font-size: 4.2mm; }

  .right-col { padding: 8mm 8mm 0 8mm; position: relative; }
  .clearance-title {
    text-align: center;
    font-size: 13mm;
    font-weight: 700;
    color: #2e3f57;
    text-decoration: underline;
    margin: 0 0 7mm;
    letter-spacing: 0.4px;
  }

  .concern { font-size: 8.2mm; font-weight: 700; margin-bottom: 5mm; }

  .clearance-content {
    font-size: 6.2mm;
    line-height: 1.55;
    text-align: justify;
    padding-right: 4mm;
  }

  .issue-line { margin-top: 5mm; font-style: italic; }

  .signatory {
    margin-top: 12mm;
    text-align: right;
    font-size: 5.1mm;
    padding-right: 8mm;
  }
  .signatory-name { font-weight: 700; text-decoration: underline; letter-spacing: 0.2px; }
  .signatory-title { font-weight: 700; }

  .id-and-sign {
    margin-top: 10mm;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 17mm;
    align-items: end;
  }

  .box-wrap { text-align: center; }
  .id-box,
  .thumb-box {
    width: 38mm;
    height: 47mm;
    border: 1px solid #444;
    margin: 0 auto;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4.2mm;
    overflow: hidden;
  }
  .thumb-box { width: 30mm; }
  .id-box img,
  .thumb-box img { width: 100%; height: 100%; object-fit: cover; }

  .box-label { margin-top: 1.8mm; font-size: 4.2mm; }

  .signature-line {
    margin-top: 9mm;
    margin-left: auto;
    width: 78mm;
    border-top: 1.2px solid #222;
    text-align: center;
    font-size: 4.6mm;
    padding-top: 1.5mm;
  }

  .cert-meta {
    margin-top: 8mm;
    font-size: 5.2mm;
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: 0.1px;
  }

  .note {
    margin-top: 3mm;
    font-size: 4.2mm;
    font-style: italic;
    font-weight: 700;
  }

  .dry-seal {
    position: absolute;
    right: 11mm;
    bottom: 13mm;
    width: 33mm;
    height: 33mm;
    border: 1.3px dashed #505050;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4.3mm;
    text-align: center;
    background: rgba(255, 255, 255, 0.4);
    z-index: 3;
  }

  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body onload="window.print()">
  <div class="page">
    <div class="gold-outer"></div>
    <div class="gold-inner"></div>
    <div class="bottom-curve"></div>

    <img class="watermark" src="/BIS/assets/images/barangay_logo.png" alt="Barangay watermark">

    <header class="header">
      <div class="header-main">
        <img src="/BIS/assets/images/barangay_logo.png" class="header-logo" alt="Barangay logo">
        <div class="head-text">
          <div class="head-rp">Republic of the Philippines</div>
          <div class="head-city">City of Parañaque</div>
          <div class="head-brgy">Barangay Don Galo</div>
          <div class="head-sub">Dimatimbang St., Barangay Don Galo, Parañaque City</div>
          <div class="head-sub">Tel. No. (02) 8812-6383</div>
        </div>
        <img src="/BIS/assets/images/city_logo.png" class="header-logo" alt="City logo">
        <img src="/BIS/assets/images/bagong_pilipinas.png" class="header-bagong" alt="Bagong Pilipinas">
      </div>
      <div class="head-rule"></div>
    </header>

    <section class="body-grid">
      <aside class="left-col">
        <?php foreach (($officials_list ?? []) as $official): ?>
          <div class="official">
            <span class="official-name"><?= htmlspecialchars($official['name'] ?? '') ?></span>
            <span class="official-position"><?= htmlspecialchars($official['position'] ?? '') ?></span>
            <?php if (!empty($official['committee'])): ?>
              <span class="official-committee"><?= htmlspecialchars($official['committee']) ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </aside>

      <main class="right-col">
        <h1 class="clearance-title"><?= htmlspecialchars($title ?? 'BARANGAY CLEARANCE') ?></h1>
        <div class="concern">TO WHOM IT MAY CONCERN:</div>

        <div class="clearance-content">
          <?= $content ?? '' ?>
        </div>

        <div class="signatory">
          <div class="signatory-name">MARILYN F. BURGOS</div>
          <div class="signatory-title">Punong Barangay</div>
        </div>

        <div class="id-and-sign">
          <div class="box-wrap">
            <div class="id-box">
              <?php if (!empty($resident_photo_url)): ?>
                <img src="<?= htmlspecialchars($resident_photo_url) ?>" alt="Resident photo">
              <?php else: ?>
                <span>PICTURE</span>
              <?php endif; ?>
            </div>
            <div class="box-label">PICTURE</div>
          </div>

          <div class="box-wrap">
            <div class="thumb-box">
              <?php if (!empty($resident_thumb_url)): ?>
                <img src="<?= htmlspecialchars($resident_thumb_url) ?>" alt="Right thumb mark">
              <?php else: ?>
                <span>RIGHT THUMBMARK</span>
              <?php endif; ?>
            </div>
            <div class="box-label">RIGHT THUMBMARK</div>
          </div>
        </div>

        <div class="signature-line">Signature over Printed Name</div>

        <div class="cert-meta">
          BARANGAY CERT. NO.: <?= htmlspecialchars($cert_no ?? '') ?><br>
          OFFICIAL RECEIPT : <?= htmlspecialchars($or_no ?? '') ?><br>
          AMOUNT : <?= htmlspecialchars($amount ?? '') ?><br>
          DATE PAID : <?= htmlspecialchars($date_paid ?? '') ?>
        </div>

        <div class="note">NOTE: Not valid without OFFICIAL SEAL.</div>
      </main>
    </section>

    <div class="dry-seal">DRY<br>SEAL</div>
  </div>
</body>
</html>
