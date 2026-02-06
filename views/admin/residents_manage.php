<?php
// VIEW ONLY
// Expected: $data from controller, plus $flash and $errors
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Resident Management</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

  <!-- LEFT SIDEBAR -->
  <?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>

  <!-- MAIN CONTENT WRAPPER -->
  <div class="main-content" id="mainContent">

    <!-- TOP NAVBAR -->
    <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container-fluid mt-4">

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
          <h4 class="mb-0">Resident Information</h4>
          <div class="text-muted">Manage personal details & demographics.</div>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResidentModal">
          <i class="bi bi-plus-circle"></i> Add Resident
        </button>
      </div>

      <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php
        // build lookup maps (id => name)
        $civilMap = [];
        foreach (($data['civil_statuses'] ?? []) as $cs) {
          if (isset($cs['id'], $cs['name'])) $civilMap[(int)$cs['id']] = $cs['name'];
        }

        $purokMap = [];
        foreach (($data['puroks'] ?? []) as $p) {
          if (isset($p['id'], $p['name'])) $purokMap[(int)$p['id']] = $p['name'];
        }

        $resTypeMap = [];
        foreach (($data['residency_types'] ?? []) as $rt) {
          if (isset($rt['id'], $rt['name'])) $resTypeMap[(int)$rt['id']] = $rt['name'];
        }
      ?>

      <!-- Search -->
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <form class="row g-2" method="GET" action="/BIS/controller/residents_manage.php">
            <div class="col-md-8">
              <input class="form-control" name="q" value="<?= htmlspecialchars($data['q'] ?? '') ?>"
                     placeholder="Search ID, name, email, contact...">
            </div>
            <div class="col-md-2">
              <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
            </div>
            <div class="col-md-2">
              <a class="btn btn-outline-secondary w-100" href="/BIS/controller/residents_manage.php">Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Sex</th>
                  <th>Birthdate</th>
                  <th>Purok</th>
                  <th>Residency Type</th>
                  <th>Household</th>
                  <th>Special Groups</th>
                  <th>Active</th>
                  <th style="width: 240px;">Actions</th>
                </tr>
              </thead>

              <tbody>
                <?php if (empty($data['list']['rows'])): ?>
                  <tr>
                    <td colspan="10" class="text-center text-muted py-4">No residents found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($data['list']['rows'] as $r): ?>
                    <?php
                      $fullname = trim(
                        ($r['last_name'] ?? '') . ', ' .
                        ($r['first_name'] ?? '') . ' ' .
                        ($r['middle_name'] ?? '') . ' ' .
                        ($r['suffix'] ?? '')
                      );

                      $isActive = (int)($r['is_active'] ?? 0) === 1;

                      $purokName = $purokMap[(int)($r['purok_id'] ?? 0)] ?? ($r['purok_id'] ?? '');
                      $resTypeName = $resTypeMap[(int)($r['residency_type_id'] ?? 0)] ?? ($r['residency_type_id'] ?? '');
                      $groupsText = $r['special_groups'] ?? '';
                      $groupsCsv  = $r['special_group_ids_csv'] ?? '';
                    ?>
                    <tr>
                      <td><?= (int)($r['id'] ?? 0) ?></td>
                      <td><?= htmlspecialchars($fullname) ?></td>
                      <td><?= htmlspecialchars($r['sex'] ?? '') ?></td>
                      <td><?= htmlspecialchars($r['birthdate'] ?? '') ?></td>
                      <td><?= htmlspecialchars($purokName) ?></td>
                      <td><?= htmlspecialchars($resTypeName) ?></td>
                      <td><?= htmlspecialchars($r['household_id'] ?? '') ?></td>
                      <td><?= htmlspecialchars($groupsText) ?></td>
                      <td>
                        <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                          <?= $isActive ? 'Active' : 'Inactive' ?>
                        </span>
                      </td>
                      <td class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary btn-edit"
                          data-bs-toggle="modal"
                          data-bs-target="#editResidentModal"
                          data-groups="<?= htmlspecialchars($groupsCsv) ?>"
                          data-resident='<?= htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8") ?>'>
                          <i class="bi bi-pencil"></i> Edit
                        </button>

                        <form method="POST" action="/BIS/controller/residents_manage.php"
                              onsubmit="return confirm('Deactivate this resident?');">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                          <input type="hidden" name="action" value="deactivate">
                          <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
                          <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-person-x"></i> Deactivate
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php
            $pages = (int)($data['list']['pages'] ?? 1);
            $page  = (int)($data['list']['page'] ?? 1);
            $q     = trim($data['q'] ?? '');
            $base  = "/BIS/controller/residents_manage.php";
            $queryBase = $q !== '' ? "&q=" . urlencode($q) : "";
          ?>
          <?php if ($pages > 1): ?>
            <nav>
              <ul class="pagination justify-content-end mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base ?>?page=<?= max(1, $page-1) ?><?= $queryBase ?>">Prev</a>
                </li>
                <?php for ($p=1; $p <= $pages; $p++): ?>
                  <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $base ?>?page=<?= $p ?><?= $queryBase ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base ?>?page=<?= min($pages, $page+1) ?><?= $queryBase ?>">Next</a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

 <!-- ADD MODAL -->
<div class="modal fade" id="addResidentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" action="/BIS/controller/residents_manage.php">
      <div class="modal-header">
        <h5 class="modal-title">Add Resident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="action" value="add">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label" for="add_last_name">Last Name *</label>
            <input class="form-control" id="add_last_name" name="last_name" required autocomplete="family-name">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_first_name">First Name *</label>
            <input class="form-control" id="add_first_name" name="first_name" required autocomplete="given-name">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_middle_name">Middle Name</label>
            <input class="form-control" id="add_middle_name" name="middle_name" autocomplete="additional-name">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="add_suffix">Suffix</label>
            <input class="form-control" id="add_suffix" name="suffix" placeholder="Jr., III" autocomplete="honorific-suffix">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="add_birthdate">Birthdate *</label>
            <input type="date" class="form-control" id="add_birthdate" name="birthdate" required >
          </div>

          <div class="col-md-3">
            <label class="form-label" for="add_sex">Sex</label>
            <select class="form-select" id="add_sex" name="sex" autocomplete="sex">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label" for="add_civil_status_id">Civil Status</label>
            <select class="form-select" id="add_civil_status_id" name="civil_status_id" required>
              <option value="">Select</option>
              <?php foreach (($data['civil_statuses'] ?? []) as $cs): ?>
                <option value="<?= (int)$cs['id'] ?>"><?= htmlspecialchars($cs['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="add_contact_number">Contact Number</label>
            <input class="form-control" id="add_contact_number" name="contact_number" autocomplete="tel">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="add_email">Email</label>
            <input type="email" class="form-control" id="add_email" name="email" autocomplete="email">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_purok_id">Purok *</label>
            <select class="form-select" id="add_purok_id" name="purok_id" required>
              <option value="">Select</option>
              <?php foreach (($data['puroks'] ?? []) as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_residency_type_id">Residency Type *</label>
            <select class="form-select" id="add_residency_type_id" name="residency_type_id" required>
              <option value="">Select</option>
              <?php foreach (($data['residency_types'] ?? []) as $rt): ?>
                <option value="<?= (int)$rt['id'] ?>"><?= htmlspecialchars($rt['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_household_id">Household ID</label>
            <input class="form-control" id="add_household_id" name="household_id" placeholder="numeric id (optional)" autocomplete="off">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="add_is_active">Active</label>
            <select class="form-select" id="add_is_active" name="is_active">
              <option value="1" selected>Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="col-md-8">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" name="is_head_of_household" id="add_hoh" value="1">
              <label class="form-check-label" for="add_hoh">Household Head</label>
            </div>
          </div>

          <!--  SPECIAL GROUPS (ADD) -->
          <div class="col-12">
            <div class="form-label fw-bold">Special Groups</div>
            <div class="row">
              <?php foreach (($data['special_groups'] ?? []) as $g): ?>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="special_groups[]" value="<?= (int)$g['id'] ?>"
                           id="add_sg_<?= (int)$g['id'] ?>">
                    <label class="form-check-label" for="add_sg_<?= (int)$g['id'] ?>">
                      <?= htmlspecialchars($g['name']) ?>
                    </label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>


  <!-- EDIT MODAL -->
<div class="modal fade" id="editResidentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" action="/BIS/controller/residents_manage.php">
      <div class="modal-header">
        <h5 class="modal-title">Edit Resident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label" for="edit_last_name">Last Name *</label>
            <input class="form-control" name="last_name" id="edit_last_name" required autocomplete="family-name">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_first_name">First Name *</label>
            <input class="form-control" name="first_name" id="edit_first_name" required autocomplete="given-name">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_middle_name">Middle Name</label>
            <input class="form-control" name="middle_name" id="edit_middle_name" autocomplete="additional-name">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="edit_suffix">Suffix</label>
            <input class="form-control" name="suffix" id="edit_suffix" autocomplete="honorific-suffix">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="edit_birthdate">Birthdate *</label>
            <input type="date" class="form-control" name="birthdate" id="edit_birthdate" required >
          </div>

          <div class="col-md-3">
            <label class="form-label" for="edit_sex">Sex</label>
            <select class="form-select" name="sex" id="edit_sex" autocomplete="sex">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label" for="edit_civil_status_id">Civil Status</label>
            <select class="form-select" name="civil_status_id" id="edit_civil_status_id" required>
              <option value="">Select</option>
              <?php foreach (($data['civil_statuses'] ?? []) as $cs): ?>
                <option value="<?= (int)$cs['id'] ?>"><?= htmlspecialchars($cs['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="edit_contact">Contact Number</label>
            <input class="form-control" name="contact_number" id="edit_contact" autocomplete="tel">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="edit_email">Email</label>
            <input type="email" class="form-control" name="email" id="edit_email" autocomplete="email">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_purok_id">Purok *</label>
            <select class="form-select" name="purok_id" id="edit_purok_id" required>
              <option value="">Select</option>
              <?php foreach (($data['puroks'] ?? []) as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_residency_type_id">Residency Type *</label>
            <select class="form-select" name="residency_type_id" id="edit_residency_type_id" required>
              <option value="">Select</option>
              <?php foreach (($data['residency_types'] ?? []) as $rt): ?>
                <option value="<?= (int)$rt['id'] ?>"><?= htmlspecialchars($rt['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_household_id">Household ID</label>
            <input class="form-control" name="household_id" id="edit_household_id" autocomplete="off">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="edit_is_active">Active</label>
            <select class="form-select" name="is_active" id="edit_is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="col-md-8">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" name="is_head_of_household" id="edit_hoh" value="1">
              <label class="form-check-label" for="edit_hoh">Household Head</label>
            </div>
          </div>

          <!--  SPECIAL GROUPS (EDIT) -->
          <div class="col-12">
            <div class="form-label fw-bold">Special Groups</div>
            <div class="row">
              <?php foreach (($data['special_groups'] ?? []) as $g): ?>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input edit-special-group"
                           type="checkbox" name="special_groups[]"
                           value="<?= (int)$g['id'] ?>"
                           id="edit_sg_<?= (int)$g['id'] ?>">
                    <label class="form-check-label" for="edit_sg_<?= (int)$g['id'] ?>">
                      <?= htmlspecialchars($g['name']) ?>
                    </label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>
    </form>
  </div>
</div>


        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const r = JSON.parse(btn.getAttribute('data-resident'));

        document.getElementById('edit_id').value = r.id || '';
        document.getElementById('edit_last_name').value = r.last_name || '';
        document.getElementById('edit_first_name').value = r.first_name || '';
        document.getElementById('edit_middle_name').value = r.middle_name || '';
        document.getElementById('edit_suffix').value = r.suffix || '';
        document.getElementById('edit_birthdate').value = r.birthdate || '';

        document.getElementById('edit_sex').value = r.sex || '';
        document.getElementById('edit_civil_status_id').value = r.civil_status_id || '';

        document.getElementById('edit_contact').value = r.contact_number || '';
        document.getElementById('edit_email').value = r.email || '';

        document.getElementById('edit_purok_id').value = r.purok_id || '';
        document.getElementById('edit_residency_type_id').value = r.residency_type_id || '';

        document.getElementById('edit_household_id').value = r.household_id || '';
        document.getElementById('edit_is_active').value = (parseInt(r.is_active || 0) === 1) ? '1' : '0';
        document.getElementById('edit_hoh').checked = (parseInt(r.is_head_of_household || 0) === 1);

        //  Special groups auto-check
        document.querySelectorAll('.edit-special-group').forEach(cb => cb.checked = false);

        const csv = btn.getAttribute('data-groups') || '';
        const ids = csv ? csv.split(',').map(x => parseInt(x.trim())).filter(Boolean) : [];
        ids.forEach(id => {
          const el = document.getElementById('edit_sg_' + id);
          if (el) el.checked = true;
        });
      });
    });

    const toggleBtn = document.getElementById("toggleSidebar");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", function () {
        const sidebar = document.getElementById("sidebar");
        const main = document.getElementById("mainContent");
        const icon = document.getElementById("toggleIcon");

        if (!sidebar || !main || !icon) return;

        sidebar.classList.toggle("collapsed");
        main.classList.toggle("expanded");

        if (sidebar.classList.contains("collapsed")) {
          icon.classList.remove("bi-list");
          icon.classList.add("bi-x-lg");
        } else {
          icon.classList.remove("bi-x-lg");
          icon.classList.add("bi-list");
        }
      });
    }
  </script>
</body>
</html>
