<?php
session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'resident') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../config/database.php';

// SUPPORT BOTH $db and $conn (para di ka na malito)
$mysqli = $db ?? $conn ?? null;
if (!$mysqli) {
    die("Database connection not found. Check database.php variable name (\$db or \$conn).");
}

$sql = "SELECT id, category, name, fee, processing_minutes, requirements
        FROM document_types
        WHERE is_active = 1
        ORDER BY category, name";

$docsRes = $mysqli->query($sql);
if (!$docsRes) {
    die("Query failed: " . htmlspecialchars($mysqli->error));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="/BIS/assets/css/sidebar.css">

</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

<div id="mainContent" class="main-content p-0">
  <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

  <div class="container-fluid p-3">
    <div class="p-4">


        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="mb-0">Request a Document</h3>
        </div>

        <div class="card shadow-sm">
          <div class="card-body">

            <form method="POST" action="/BIS/controller/document_requests.php">
              <!-- CSRF -->
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

              <!-- DOCUMENT TYPE -->
              <div class="mb-3">
                <label class="form-label">Select Service <span class="text-danger">*</span></label>

                <select name="document_type_id" id="documentSelect" class="form-select" required>
                  <option value="" selected disabled>Select document</option>

                  <?php while ($row = $docsRes->fetch_assoc()): ?>
                    <?php
                      // JSON encode for safe dataset (handles quotes/newlines)
                      $reqJson = json_encode($row['requirements'] ?? '');
                      $fee = $row['fee'] ?? '';
                      $mins = (int)($row['processing_minutes'] ?? 0);
                    ?>
                    <option
                      value="<?= (int)$row['id'] ?>"
                      data-fee="<?= htmlspecialchars((string)$fee, ENT_QUOTES) ?>"
                      data-time="<?= $mins ?>"
                      data-req='<?= htmlspecialchars($reqJson, ENT_QUOTES) ?>'
                    >
                      <?= htmlspecialchars($row['category'] . ' - ' . $row['name']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <!-- AUTO INFO -->
              <div class="mb-3 p-3 border rounded bg-white">
                <div class="row g-2">
                  <div class="col-md-4">
                    <div class="text-muted small">Fee</div>
                    <div class="fw-semibold">₱ <span id="feeTxt">-</span></div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-muted small">Processing Time</div>
                    <div class="fw-semibold"><span id="timeTxt">-</span> minutes</div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-muted small">Requirements</div>
                    <div id="reqTxt" class="small text-muted">-</div>
                  </div>
                </div>
              </div>

              <!-- PURPOSE -->
              <div class="mb-3">
                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                <textarea name="purpose" class="form-control" rows="3" required></textarea>
              </div>

              <button class="btn btn-primary">Submit Request</button>
            </form>

          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<script src="/BIS/assets/js/sidebar_toggle.js"></script>
<script>
const sel = document.getElementById('documentSelect');

function updateInfo() {
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.dataset) return;

  // fee/time
  document.getElementById('feeTxt').textContent  = opt.dataset.fee ? opt.dataset.fee : '-';
  document.getElementById('timeTxt').textContent = opt.dataset.time ? opt.dataset.time : '-';

  // requirements (JSON safe)
  let req = '-';
  try {
    req = JSON.parse(opt.dataset.req || '""') || '-';
  } catch(e) {
    req = '-';
  }

  // show newlines properly
  const reqBox = document.getElementById('reqTxt');
  reqBox.innerHTML = (req && req !== '-') 
    ? String(req).replace(/\n/g, "<br>")
    : "-";
}

sel.addEventListener('change', updateInfo);
</script>

</body>
</html>
