<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Print Document</title>
  <style>
    body { font-family: Arial, sans-serif; color:#000; }
    .paper { width: 210mm; min-height: 297mm; padding: 18mm; margin: auto; }
    .header { text-align:center; margin-bottom: 18px; }
    .header h2, .header h3 { margin: 2px 0; }
    .title { text-align:center; font-size: 22px; font-weight: bold; margin: 18px 0; letter-spacing: 1px; }
    .content { font-size: 14px; line-height: 1.8; margin-top: 18px; }
    .sig { margin-top: 60px; text-align:right; }
    .sig .name { font-weight:bold; text-decoration: underline; }

    @media print {
      .no-print { display:none; }
      body { margin:0; }
      .paper { box-shadow:none; }
    }

    .no-print { text-align:center; margin: 10px 0; }
    .btn { padding:10px 14px; border:1px solid #333; background:#fff; cursor:pointer; }
  </style>
</head>
<body>

<div class="no-print">
  <button class="btn" onclick="window.print()">🖨 Print Now</button>
</div>

<div class="paper">
  <div class="header">
    <h3>Republic of the Philippines</h3>
    <h3>City/Municipality: Parañaque</h3>
    <h2>Barangay Don Galo</h2>
  </div>

  <div class="title">CERTIFICATION</div>

  <div class="content">
    This is to certify that <b><?= htmlspecialchars($row['resident_name'] ?? '') ?></b>
    is a bona fide resident of this barangay with address at
    <b><?= htmlspecialchars($row['resident_address'] ?? '') ?></b>.

    <br><br>
    This certification is issued upon the request of the above-named person for the purpose of:
    <b><?= htmlspecialchars($row['purpose'] ?? '') ?></b>.

    <br><br>
    Issued this <b><?= date('F d, Y') ?></b> in Barangay Don Galo.
  </div>

  <div class="sig">
    <div class="name">MARILYN F. BURGOS</div>
    <div>Punong Barangay</div>
    <br>
    <div style="font-size:12px;">
      Ref No: <b><?= htmlspecialchars($row['ref_no'] ?? '') ?></b><br>
      Fee: <b><?= number_format((float)($row['fee_snapshot'] ?? 0), 2) ?></b>
    </div>
  </div>
</div>

<script>
  // auto open print dialog
  window.onload = () => window.print();
</script>
</body>
</html>
