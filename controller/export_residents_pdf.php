<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /BIS/views/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php'; // for TCPDF

use Dompdf\Dompdf;
use Dompdf\Options; 

//conn = connection variable from database.php (adjust if yours is different)
$conn = $conn ?? $db ?? null;
if (!$conn) {
    http_response_code(500);
    exit('Database connection not found.');
}

// Fetch residents data
$sql = "SELECT id, resident_code, last_name, first_name, middle_name, suffix
               sex, birthdate, contact_number, email, occupation, voter_status, is_head_of_household, is_active, created_at
        FROM residents
        ORDER BY id ASC";
        
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    exit('Failed to fetch residents data.' . $conn->error);
}
        
$rows = [];

while ($row = $result->fetch_assoc()) {

    // Convert booleans
    $row['is_head_of_household'] = ($row['is_head_of_household'] == 1) ? 'Yes' : 'No';
    $row['is_active'] = ($row['is_active'] == 1) ? 'Yes' : 'No';

    // Format dates
    if (!empty($row['birthdate'])) {
        $row['birthdate'] = date('Y-m-d', strtotime($row['birthdate']));
    }

    if (!empty($row['created_at'])) {
        $row['created_at'] = date('Y-m-d', strtotime($row['created_at']));
    }

    $rows[] = $row;
}

// build HTML for PDF
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { size: A4 landscape; margin: 10mm; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
    }

    h2 {
        margin: 0 0 5px;
    }

    .meta {
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }

    th {
        background: #f0f0f0;
    }
</style>
</head>
<body>
    <h2>Residents List</h2>
    <div class="meta">Generated on: <?= date('Y-m-d H:i:s') ?></div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Resident Code</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Suffix</th>
                <th>Sex</th>
                <th>Birthdate</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Occupation</th>
                <th>Head</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['resident_code'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['last_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['first_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['middle_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['suffix'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['sex'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['birthdate'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['contact_number'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['occupation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['is_head_of_household'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['is_active'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>

<?php
$html = ob_get_clean();

// Generate PDF using Dompdf

$options = new Options();
$options->set('isRemoteEnabled', true); // if you have images or external resources

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output the generated PDF (force download)
$filename = "residents_export_" . date('Y-m-d') . ".pdf";
$dompdf->stream($filename, ['Attachment' => true]);

exit;

