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

/* =========================
   FLASH MESSAGE
========================= */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* =========================
   REF MODAL (ONE-TIME)
========================= */
$ref = $_SESSION['last_ref_no'] ?? '';
if ($ref !== '') {
    unset($_SESSION['last_ref_no']);
}

require_once __DIR__ . '/../../config/database.php';

// SUPPORT BOTH $db and $conn
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

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- SAME CSS AS RESIDENT DASHBOARD -->
  <link rel="stylesheet" href="/BIS/assets/css/navbaruserleft.css">
  <link rel="stylesheet" href="/BIS/assets/css/resident_dashboard.css">
</head>

<body class="bis-body">

  <!-- SIDEBAR -->
  <?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

  <!-- MAIN CONTENT -->
  <div id="mainContent" class="main-content p-0">

    <!-- TOP NAVBAR -->
    <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

    <!-- PAGE CONTENT -->
    <div class="container-fluid py-4 px-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Request a Document</h3>
      </div>

      <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show rounded-3" role="alert">
          <?= htmlspecialchars($flash['msg'] ?? '') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

          <form method="POST" action="/BIS/controller/document_requests.php" enctype="multipart/form-data">
            
            <!-- CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <!-- DOCUMENT TYPE -->
            <div class="mb-3">
              <label class="form-label">Select Service <span class="text-danger">*</span></label>

              <select name="document_type_id" id="documentSelect" class="form-select" required>
                <option value="" selected disabled>Select document</option>

                <?php while ($row = $docsRes->fetch_assoc()): ?>
                  <?php
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
              <textarea id="purpose" name="purpose" class="form-control" rows="3" required></textarea>
            </div>

            <!-- EXTRA FIELDS -->
            <div id="extraWrap" class="mt-3" style="display:none;">
              <div class="card border-0 bg-white">
                <div class="card-body p-3">
                  <div class="fw-semibold mb-2">Additional Information</div>
                  <div id="extraFields"></div>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary rounded-pill px-4">
                Submit Request
              </button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>

  <!-- REF MODAL -->
  <?php if ($ref !== ''): ?>
  <div class="modal fade" id="refModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title">Reference Number</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Please save your reference number:</p>
          <div class="p-3 border rounded bg-light fw-bold fs-4 text-center">
            <?= htmlspecialchars($ref) ?>
          </div>
          <small class="text-muted d-block mt-2">
            You can also view this in <b>Transaction</b>.
          </small>
        </div>
        <div class="modal-footer">
          <a href="/BIS/views/resident/transaction.php" class="btn btn-primary">Go to Transactions</a>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/BIS/assets/js/sidebar_toggle.js"></script>

  <?php if ($ref !== ''): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const el = document.getElementById('refModal');
      if (el) {
        new bootstrap.Modal(el).show();
      }
    });
  </script>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sel = document.getElementById('documentSelect');
      const feeTxt = document.getElementById('feeTxt');
      const timeTxt = document.getElementById('timeTxt');
      const reqTxt = document.getElementById('reqTxt');
      const purposeField = document.getElementById('purpose');
      const extraWrap = document.getElementById('extraWrap');
      const extraFields = document.getElementById('extraFields');

      async function loadExtraForm(docTypeId) {
        extraFields.innerHTML = '';
        extraWrap.style.display = 'none';

        if (!docTypeId) return;

        try {
          const res = await fetch(`/BIS/controller/document_form.php?document_type_id=${encodeURIComponent(docTypeId)}`);
          const html = await res.text();

          if (html.trim() !== '') {
            extraFields.innerHTML = html;
            extraWrap.style.display = 'block';
          }
        } catch (error) {
          console.error('Failed to load extra form:', error);
        }
      }

      function updateInfo() {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.dataset) return;

        feeTxt.textContent = opt.dataset.fee || '-';
        timeTxt.textContent = opt.dataset.time || '-';

        let req = '-';
        try {
          req = JSON.parse(opt.dataset.req || '""') || '-';
        } catch (e) {
          req = '-';
        }

        reqTxt.innerHTML = (req && req !== '-') ? String(req).replace(/\n/g, '<br>') : '-';

        // AUTO PURPOSE
        const fullText = opt.text || '';
        const parts = fullText.split(' - ');
        const docName = parts.length > 1 ? parts[parts.length - 1] : fullText;

        if (purposeField && purposeField.value.trim() === '') {
          purposeField.value = docName;
        } else {
          purposeField.value = docName;
        }

        loadExtraForm(sel.value);
      }

      sel.addEventListener('change', updateInfo);
    });
  </script>

</body>
</html>