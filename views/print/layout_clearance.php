<?php
/**
 * FINAL LAYOUT: BARANGAY DON GALO
 */
$imgBarangay   = $imgBarangay   ?? '../../BIS/assets/images/barangay_logo.png';
$imgCity       = $imgCity       ?? '../../BIS/assets/images/city_logo.png';
$imgBagong     = $imgBagong     ?? '../../BIS/assets/images/bagong_pilipinas.png';
$watermark_src = $watermark_src ?? $imgBarangay;

$officials_list = $officials_list ?? [
    ['name' => 'Hon. Marilyn F. Burgos', 'position' => 'Barangay Captain'],
    ['name' => 'Hon. Rafael Barry B. Cura III', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Finance & Appropriation on Traffic Management'],
    ['name' => 'Hon. Rodluck V. Locsina', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Health and Social Services'],
    ['name' => 'Hon. Pastor S. Rodriguez', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Peace and Order'],
    ['name' => 'Hon. Reynaldo O. Bumagat', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Education and Culture / Committee on Cooperative'],
    ['name' => 'Hon. Eduardo R. Giron Jr.', 'position' => 'Barangay Councilor', 'committee' => 'Committee on Environment'],
    ['name' => 'Hon. Editha U. Jimenez', 'position' => 'Barangay Councilor'],
    ['name' => 'Hon. Louisse Gabrielle D. Omaña', 'position' => 'Barangay Councilor'],
    ['name' => 'Hon. Dryn Allison Medina', 'position' => 'SK Chairman', 'committee' => 'Committee on Sports and Youth Development'],
    ['name' => 'Maria Leticia N. Basa', 'position' => 'Barangay Secretary'],
    ['name' => 'Luzviminda DG. Aquino', 'position' => 'Barangay Treasurer'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: "Times New Roman", serif; margin: 0; padding: 0; color: #111; }
        .page { position: relative; width: 210mm; height: 297mm; background: #fff; overflow: hidden; }
        
        /* Gold Borders */
        .border-outer { position: absolute; left: 6mm; top: 6mm; right: 6mm; bottom: 6mm; border: 1.2mm solid #caa33a; }
        .border-inner { position: absolute; left: 9.5mm; top: 9.5mm; right: 9.5mm; bottom: 9.5mm; border: 0.3mm solid #caa33a; }

        .sheet { position: relative; padding: 12mm 15mm; height: 100%; z-index: 10; }

        /* Header */
        .hdr-table { width: 100%; border-collapse: collapse; }
        .hdr-brgy { font-size: 24pt; font-weight: 900; color: #1b4f9c; text-transform: uppercase; line-height: 1; }
        .line-blue { border-top: 2.5pt solid #1b4f9c; margin-top: 2mm; }
        .line-gold { border-top: 1.2pt solid #caa33a; margin-top: 1mm; }

        /* Main Body */
        .main-table { width: 100%; border-collapse: collapse; margin-top: 5mm; table-layout: fixed; }
        .sidebar { width: 48mm; vertical-align: top; border-right: 2pt solid #1b4f9c; padding-right: 3mm; text-align: center; }
        .content { vertical-align: top; padding-left: 6mm; position: relative; }

        /* Officials Styling */
        .person { margin-bottom: 2.2mm; line-height: 1.1; }
        .off-name { font-weight: bold; font-size: 8pt; text-transform: uppercase; display: block; }
        .off-pos { font-style: italic; font-size: 7.2pt; display: block; }
        .off-comm { font-size: 6.2pt; font-style: italic; display: block; line-height: 0.9; color: #444; }

        /* Watermark */
        .watermark { position: absolute; top: 150px; left: 0; width: 100%; text-align: center; opacity: 0.08; z-index: -1; }
        .watermark img { width: 110mm; }

        /* Shared Content Classes */
        .doc-title { text-align: center; font-size: 20pt; font-weight: bold; text-decoration: underline; color: #1b4f9c; margin: 8mm 0; }
        .to-whom { font-weight: bold; margin-bottom: 5mm; font-size: 11.5pt; }
        .para { text-align: justify; text-indent: 10mm; font-size: 11.5pt; line-height: 1.6; margin-bottom: 4mm; }

        /* Footer/Receipt Area */
        .bottom-area { position: absolute; bottom: 10mm; left: 0; width: 100%; }
        .box-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        .box { border: 1pt solid #000; width: 32mm; height: 32mm; position: relative; background: #fff; }
        .box-label { position: absolute; bottom: 1mm; width: 100%; text-align: center; font-size: 7pt; font-weight: bold; color: #555; }
        .sig-underline { border-top: 1pt solid #000; width: 60mm; margin: 0 auto; text-align: center; font-size: 9pt; padding-top: 3px; }
        .receipt-info { font-size: 9.5pt; line-height: 1.4; }
        .fill { border-bottom: 0.5pt solid #000; display: inline-block; min-width: 45mm; }
    </style>
</head>
<body>
    <div class="page">
        <div class="border-outer"></div>
        <div class="border-inner"></div>
        <div class="sheet">
            <table class="hdr-table">
                <tr>
                    <td style="width:25mm;"><img src="<?= $imgBarangay ?>" style="width:22mm;"></td>
                    <td style="text-align:center;">
                        <div style="font-weight:bold; font-size:12pt;">Republic of the Philippines</div>
                        <div style="font-weight:bold; font-size:12pt;">City of Parañaque</div>
                        <div class="hdr-brgy">Barangay Don Galo</div>
                        <div style="font-size:8pt;">Dimatalang St., Barangay Don Galo, Parañaque City</div>
                    </td>
                    <td style="width:55mm; text-align:right;">
                        <img src="<?= $imgCity ?>" style="width:20mm; margin-right:2mm;">
                        <img src="<?= $imgBagong ?>" style="width:26mm;">
                    </td>
                </tr>
            </table>
            <div class="line-blue"></div>
            <div class="line-gold"></div>

            <table class="main-table">
                <tr>
                    <td class="sidebar">
                        <?php foreach ($officials_list as $o): ?>
                            <div class="person">
                                <span class="off-name"><?= htmlspecialchars($o['name']) ?></span>
                                <span class="off-pos"><?= htmlspecialchars($o['position']) ?></span>
                                <?php if (!empty($o['committee'])): ?>
                                    <span class="off-comm"><?= htmlspecialchars($o['committee']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    <td class="content">
                        <div class="watermark"><img src="<?= $watermark_src ?>"></div>
                        
                        <?= $content ?>

                        <div class="bottom-area">
                            <table class="box-table">
                                <tr>
                                    <td style="width:35mm;"><div class="box"><div class="box-label">PICTURE</div></div></td>
                                    <td style="width:35mm; padding-left:4mm;"><div class="box"><div style="padding-top:22mm;" class="box-label">RIGHT THUMBMARK</div></div></td>
                                    <td style="vertical-align:bottom;"><div class="sig-underline">Signature over Printed Name</div></td>
                                </tr>
                            </table>
                            <div class="receipt-info">
                                <b>BARANGAY CERT. NO.:</b> <span class="fill"><?= $cert_no ?? '_________' ?></span><br>
                                <b>OFFICIAL RECEIPT:</b> <span class="fill"><?= $or_no ?? '_________' ?></span><br>
                                <b>AMOUNT:</b> <span class="fill"><?= $amount ?? '_________' ?></span><br>
                                <b>DATE PAID:</b> <span class="fill"><?= $date_paid ?? '_________' ?></span><br>
                                <div style="margin-top:2mm; font-size:8.5pt;"><i>NOTE: Not valid without OFFICIAL SEAL.</i></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>