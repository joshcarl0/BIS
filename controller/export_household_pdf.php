<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /BIS/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// support $conn or $db
$conn = $conn ?? $db ?? null;
if (!$conn) {
    http_response_code(500);
    exit('Database connection not found.');
}

// dompdf
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ✅ pili muna ng readable fields (pwede pa dagdagan)
$sql = "SELECT
            h.id,
            h.household_code,
            h.purok_id,
            h.address_line,
            h.housing_type,
            h.household_type,
            h.tenure_status,
            h.monthly_income_range,
            h.socio_economic_class,
            h.status,
            h.created_at
        FROM households h
        ORDER BY h.id ASC";

$res = $conn->query($sql);
if (!$res) {
    http_response_code(500);
    exit('Query failed: ' . $conn->error);
}

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

// ===== HTML for PDF =====
$generatedAt = date('Y-m-d H:i:s');

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { size: A4 landscape; margin: 14mm; }
  body { font-family: Arial, sans-serif; font-size: 10px; color:#111; }
  h2 { margin:0 0 6px 0; }
  .meta { margin:0 0 10px 0; font-size: 9px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #333; padding:4px; vertical-align: top; }
  th { background:#f2f2f2; }
</style>
</head>
<body>
  <h2>Households List</h2>
  <div class="meta">Generated on: '.$generatedAt.'</div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Household Code</th>
        <th>Purok</th>
        <th>Address</th>
        <th>Housing Type</th>
        <th>Household Type</th>
        <th>Tenure</th>
        <th>Income Range</th>
        <th>Socio Econ</th>
        <th>Status</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody>
';

foreach ($rows as $r) {
    $created = !empty($r['created_at']) ? date('Y-m-d', strtotime($r['created_at'])) : '';
    $html .= '
      <tr>
        <td>'.htmlspecialchars($r['id'] ?? '').'</td>
        <td>'.htmlspecialchars($r['household_code'] ?? '').'</td>
        <td>'.htmlspecialchars($r['purok_id'] ?? '').'</td>
        <td>'.htmlspecialchars($r['address_line'] ?? '').'</td>
        <td>'.htmlspecialchars($r['housing_type'] ?? '').'</td>
        <td>'.htmlspecialchars($r['household_type'] ?? '').'</td>
        <td>'.htmlspecialchars($r['tenure_status'] ?? '').'</td>
        <td>'.htmlspecialchars($r['monthly_income_range'] ?? '').'</td>
        <td>'.htmlspecialchars($r['socio_economic_class'] ?? '').'</td>
        <td>'.htmlspecialchars($r['status'] ?? '').'</td>
        <td>'.htmlspecialchars($created).'</td>
      </tr>
    ';
}

$html .= '
    </tbody>
  </table>
</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true); // if you load images
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "households_export_" . date('Y-m-d') . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;