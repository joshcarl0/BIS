<?php
// expects: $officials (array)
// columns used: full_name, position, committee, term_start, term_end, photo
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Officials</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

<?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

<div id="mainContent" class="main-content p-0">

  <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

  <div class="container-fluid p-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h3 class="mb-0">Barangay Officials</h3>
        <div class="text-muted">View barangay leaders (read-only)</div>
      </div>

      <!-- FILTERS -->
      <div class="d-flex flex-wrap gap-2">
        <select id="roleFilter" class="form-select" style="max-width: 220px;">
          <option value="all">All</option>
          <option value="captain">Captain / Punong Barangay</option>
          <option value="kagawad">Kagawad</option>
        </select>

        <input id="searchBox" type="text" class="form-control" placeholder="Search name/position..."
               style="max-width: 260px;">
      </div>
    </div>

    <?php if (empty($officials)): ?>
      <div class="alert alert-warning">No officials found.</div>
    <?php else: ?>

      <div class="row g-3" id="officialGrid">
        <?php foreach ($officials as $o): ?>
          <?php
            $fullName  = $o['full_name'] ?? '';
            $position  = $o['position'] ?? '';
            $committee = $o['committee'] ?? '';
            $termStart = $o['term_start'] ?? '';
            $termEnd   = $o['term_end'] ?? '';
            $photoFile = $o['photo'] ?? '';

            // photo path (adjust folder if needed)
            $photoUrl = ($photoFile !== '')
              ? "/BIS/uploads/officials/" . rawurlencode($photoFile)
              : "/BIS/assets/images/default-user.png";

            // Categorize role for filtering (edit keywords if needed)
            $posLower = strtolower($position);
            $roleTag = 'other';
            if (strpos($posLower, 'kagawad') !== false) $roleTag = 'kagawad';
            if (strpos($posLower, 'captain') !== false || strpos($posLower, 'punong') !== false) $roleTag = 'captain';

            // For JS search
            $searchHaystack = strtolower(trim($fullName . ' ' . $position . ' ' . $committee));
          ?>

          <div class="col-12 col-sm-6 col-md-4 col-lg-3 official-card"
               data-role="<?= htmlspecialchars($roleTag) ?>"
               data-search="<?= htmlspecialchars($searchHaystack) ?>">

            <!-- Clickable card opens modal -->
            <button type="button"
                    class="card shadow-sm h-100 text-start w-100 p-0 border-0 bg-white officialBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#officialModal"
                    data-name="<?= htmlspecialchars($fullName) ?>"
                    data-position="<?= htmlspecialchars($position) ?>"
                    data-committee="<?= htmlspecialchars($committee) ?>"
                    data-termstart="<?= htmlspecialchars($termStart) ?>"
                    data-termend="<?= htmlspecialchars($termEnd) ?>"
                    data-photo="<?= htmlspecialchars($photoUrl) ?>">
              <img src="<?= htmlspecialchars($photoUrl) ?>"
                   class="card-img-top"
                   alt="Official Photo"
                   style="height:200px; object-fit:cover;"
                   onerror="this.src='/BIS/assets/images/default-user.png';">

              <div class="card-body">
                <div class="text-muted small mb-1">
                  <i class="bi bi-briefcase-fill me-1"></i>
                  <?= htmlspecialchars($position) ?>
                </div>
                <div class="fw-bold"><?= htmlspecialchars($fullName) ?></div>

                <?php if ($committee !== ''): ?>
                  <div class="small text-muted mt-1">Committee: <?= htmlspecialchars($committee) ?></div>
                <?php endif; ?>

                <div class="small text-primary mt-2">
                  <i class="bi bi-person-badge me-1"></i> View profile
                </div>
              </div>
            </button>
          </div>

        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- ✅ PROFILE MODAL -->
<div class="modal fade" id="officialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mName">Official Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <img id="mPhoto" src="" alt="Photo" class="w-100 rounded"
                 style="height:260px; object-fit:cover;"
                 onerror="this.src='/BIS/assets/images/default-user.png';">
          </div>
          <div class="col-md-8">
            <div class="mb-2">
              <div class="text-muted small">Position</div>
              <div class="fs-5 fw-semibold" id="mPosition">-</div>
            </div>

            <div class="mb-2">
              <div class="text-muted small">Committee</div>
              <div id="mCommittee">-</div>
            </div>

            <div class="mb-2">
              <div class="text-muted small">Term</div>
              <div id="mTerm">-</div>
            </div>

            <div class="alert alert-light border mt-3 mb-0">
              <i class="bi bi-info-circle me-1"></i>
              This page is read-only for residents.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="/BIS/assets/js/sidebar_toggle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// FILTER + SEARCH
document.addEventListener("DOMContentLoaded", () => {
  const roleFilter = document.getElementById("roleFilter");
  const searchBox  = document.getElementById("searchBox");
  const cards      = document.querySelectorAll(".official-card");

  function applyFilters() {
    const role = (roleFilter?.value || "all").toLowerCase();
    const q    = (searchBox?.value || "").trim().toLowerCase();

    cards.forEach(card => {
      const cardRole = (card.dataset.role || "other").toLowerCase();
      const hay      = (card.dataset.search || "").toLowerCase();

      const passRole = (role === "all") ? true : (cardRole === role);
      const passText = (q === "") ? true : hay.includes(q);

      card.style.display = (passRole && passText) ? "" : "none";
    });
  }

  roleFilter?.addEventListener("change", applyFilters);
  searchBox?.addEventListener("input", applyFilters);

  // MODAL POPULATE
  const modal = document.getElementById("officialModal");
  modal?.addEventListener("show.bs.modal", (event) => {
    const btn = event.relatedTarget;
    if (!btn) return;

    const name      = btn.getAttribute("data-name") || "Official";
    const position  = btn.getAttribute("data-position") || "-";
    const committee = btn.getAttribute("data-committee") || "-";
    const termStart = btn.getAttribute("data-termstart") || "";
    const termEnd   = btn.getAttribute("data-termend") || "";
    const photo     = btn.getAttribute("data-photo") || "/BIS/assets/images/default-user.png";

    document.getElementById("mName").textContent = name;
    document.getElementById("mPosition").textContent = position;
    document.getElementById("mCommittee").textContent = committee;

    const termText = (termStart || termEnd) ? `${termStart || "-"} — ${termEnd || "-"}` : "-";
    document.getElementById("mTerm").textContent = termText;

    const img = document.getElementById("mPhoto");
    img.src = photo;
  });

  applyFilters();
});
</script>

</body>
</html>
