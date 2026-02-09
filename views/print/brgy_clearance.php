<?php ob_start();

$rn = htmlspecialchars($resident_name ?? '');
$ra = htmlspecialchars($resident_address ?? '');

// optional / dynamic
$purpose = htmlspecialchars($purpose ?? 'LOCAL EMPLOYMENT'); // pwede galing sa request->purpose
$issued_day = date('j');
$issued_month = date('F');
$issued_year = date('Y');

// placeholders (pwede later gawin image)
$photo_url = $photo_url ?? null;
$thumb_url = $thumb_url ?? null;

?>
<style>
  /* A4 area assumed by layout.php */
  .clearance-wrap{ position:relative; }

  /* overall grid */
  .clearance-grid{
    display:grid;
    grid-template-columns: 58mm 1fr;
    gap: 10mm;
    margin-top: 6mm;
  }

  /* left officials column */
  .officials{
    font-size: 10pt;
    line-height: 1.25;
  }
  .officials .name{ font-weight: bold; }
  .officials .role{ color:#333; font-style: italic; font-size: 9pt; }

  /* right content */
  .main h1{
    text-align:center;
    font-size: 22pt;
    margin: 4mm 0 6mm;
    letter-spacing: 1px;
    text-decoration: underline;
  }
  .main .to{
    font-weight:bold;
    margin: 2mm 0 5mm;
  }
  .main .body{
    font-size: 12.5pt;
    line-height: 1.7;
    text-align: justify;
  }

  /* boxes row */
  .boxes{
    display:flex;
    gap: 10mm;
    margin-top: 14mm;
    align-items:flex-start;
  }
  .box{
    border: 1px solid #555;
    width: 45mm;
    height: 45mm;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 10pt;
    color:#444;
  }
  .box-label{
    text-align:center;
    font-size:10pt;
    margin-top:2mm;
  }

  .sigline{
    margin-top: 10mm;
    width: 80mm;
    border-top: 1px solid #000;
    text-align:center;
    font-size: 10pt;
    padding-top: 2mm;
  }

  /* captain sign area */
  .captain{
    position:absolute;
    right: 0;
    bottom: 55mm;
    text-align:center;
    width: 80mm;
  }
  .captain .capname{ font-weight:bold; text-transform:uppercase; }
  .captain .caprole{ font-size:10pt; }

  /* receipt box */
  .receipt{
    position:absolute;
    right: 0;
    bottom: 18mm;
    border:1px solid #333;
    width: 70mm;
    padding: 5mm;
    font-size: 10pt;
  }
  .receipt .row{
    display:flex;
    justify-content:space-between;
    margin: 1.5mm 0;
  }

  .note{
    position:absolute;
    left: 0;
    bottom: 18mm;
    font-size: 9.5pt;
  }

  /* watermark (uses your existing .watermark if you want) */
  .wm{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    opacity:0.07;
    z-index:0;
    pointer-events:none;
  }
  .wm img{ width: 140mm; }
  .front{ position:relative; z-index:2; }
</style>

<div class="clearance-wrap">
  <!-- watermark -->
  <div class="wm">
    <img src="/BIS/assets/img/barangay_seal.png" alt="Watermark">
  </div>

  <div class="front">
    <h1 style="text-align:center; margin:0;">BARANGAY CLEARANCE</h1>

    <div class="clearance-grid">
      <!-- LEFT OFFICIALS -->
      <div class="officials">
        <div class="name">Hon. Marilyn F. Burgos</div>
        <div class="role">Barangay Captain</div>
        <br>

        <div class="name">Hon. Rafael Barry B. Cura III</div>
        <div class="role">Barangay Councilor</div>
        <div class="role">Committee on Finance & Appropriation</div>
        <br>

        <div class="name">Hon. Rodluck V. Lacsina</div>
        <div class="role">Barangay Councilor</div>
        <div class="role">Committee on Health & Social Services</div>
        <br>

        <!-- dagdagan mo nalang ibang officials dito -->
      </div>

      <!-- RIGHT MAIN CONTENT -->
      <div class="main">
        <div class="to">TO WHOM IT MAY CONCERN:</div>

        <div class="body">
          This is to certify that <b><?= $rn ?></b>, whose photograph, signature and right thumb mark appears below,
          is a bonafide resident of <b><?= $ra ?></b>, Don Galo, Parañaque City.
          <br><br>

          This certification is issued upon the request of the above-mentioned individual for the purpose of
          <b><?= $purpose ?></b> and valid only for three (3) months from date issued.
          <br><br>

          Issued this <b><?= $issued_day ?></b> day of <b><?= $issued_month ?></b>, <b><?= $issued_year ?></b>
          in Barangay Don Galo, City of Parañaque.
        </div>

        <div class="boxes">
          <div>
            <div class="box">
              <?php if ($photo_url): ?>
                <img src="<?= htmlspecialchars($photo_url) ?>" style="width:100%; height:100%; object-fit:cover;">
              <?php else: ?>
                PICTURE
              <?php endif; ?>
            </div>
            <div class="box-label">PICTURE</div>
          </div>

          <div>
            <div class="box">
              <?php if ($thumb_url): ?>
                <img src="<?= htmlspecialchars($thumb_url) ?>" style="width:100%; height:100%; object-fit:contain;">
              <?php else: ?>
                RIGHT THUMBMARK
              <?php endif; ?>
            </div>
            <div class="box-label">RIGHT THUMBMARK</div>

            <div class="sigline">Signature over Printed Name</div>
          </div>
        </div>
      </div>
    </div>

    <!-- captain sign -->
    <div class="captain">
      <div class="capname">MARILYN F. BURGOS</div>
      <div class="caprole">Punong Barangay</div>
    </div>

    <!-- receipt -->
    <div class="receipt">
      <div class="row"><span>Brgy. Cert. No:</span><span><?= htmlspecialchars($ref_no ?? '') ?></span></div>
      <div class="row"><span>Official Receipt:</span><span><?= htmlspecialchars($or_no ?? '') ?></span></div>
      <div class="row"><span>Amount:</span><span><?= number_format((float)($amount_paid ?? 0), 2) ?></span></div>
      <div class="row"><span>Date Paid:</span><span><?= htmlspecialchars($date_paid ?? '') ?></span></div>
    </div>

    <div class="note">
      <b>NOTE:</b> Not valid without official seal.
    </div>
  </div>
</div>

<?php
$title = "BARANGAY CLEARANCE";
$content = ob_get_clean();
require __DIR__ . "/layout.php";
