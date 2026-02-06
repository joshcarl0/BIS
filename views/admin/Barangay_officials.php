


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Officials</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">

</head>
<body style="background:#D6D5D7;">
    
<?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>

 <div class="main-content" id="mainContent">
    <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h3 class="mb-0">Barangay Officials</h3>
          <div class="text-muted">Add, edit, and manage barangay officials</div>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="bi bi-plus-circle me-1"></i> Add Official
        </button>
      </div>

      <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Position</th>
            <th>Photo</th> <!-- ADDED -->
            <th>Name</th>
            <th>Committee</th>
            <th>Term</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php foreach ($officials as $o): ?>
          <tr>

            <td><?= htmlspecialchars($o['position']) ?></td>

            <!-- PHOTO DISPLAY -->
            <td>
              <?php if (!empty($o['photo'])): ?>
                <img
                  src="/BIS/uploads/officials/<?= htmlspecialchars($o['photo']) ?>"
                  width="50"
                  height="50"
                  style="border-radius:50%; object-fit:cover;">
              <?php else: ?>
                —
              <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($o['full_name']) ?></td>
            <td><?= htmlspecialchars($o['committee'] ?? '') ?></td>

            <td>
              <?= htmlspecialchars($o['term_start'] ?? '') ?>
              -
              <?= htmlspecialchars($o['term_end'] ?? '') ?>
            </td>

            <td>
              <span class="badge <?= ($o['status'] ?? 'Active') === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                <?= htmlspecialchars($o['status'] ?? 'Active') ?>
              </span>
            </td>

            <td class="text-end">
              <button
                class="btn btn-sm btn-outline-primary btn-edit"
                data-bs-toggle="modal"
                data-bs-target="#editModal"
                data-official='<?= htmlspecialchars(json_encode($o), ENT_QUOTES, "UTF-8") ?>'
              >
                <i class="bi bi-pencil"></i>
              </button>

              <form method="POST" action="/BIS/controller/officials.php"
                    class="d-inline"
                    onsubmit="return confirm('Delete this official?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>

          </tr>
        <?php endforeach; ?>

        <?php if (empty($officials)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted">No officials yet.</td>
          </tr>
        <?php endif; ?>

        </tbody>
      </table>
    </div>
  </div>
</div>


    </div>
  </div>

  <!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    
    <form class="modal-content"
          method="POST"
          action="/BIS/controller/officials.php"
          enctype="multipart/form-data">

      <input type="hidden" name="action" value="store">

      <div class="modal-header">
        <h5 class="modal-title">Add Official</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body row g-3">

          <div class="col-md-6">
            <label for="add_position" class="form-label">Position *</label>
            <input id="add_position" class="form-control" name="position" required autocomplete="organization-title">
          </div>

          <div class="col-md-6">
            <label for="add_full_name" class="form-label">Full Name *</label>
            <input id="add_full_name" class="form-control" name="full_name" required autocomplete="name">
          </div>

          <div class="col-md-6">
            <label for="add_committee" class="form-label">Committee</label>
            <input id="add_committee" class="form-control" name="committee">
          </div>

          <div class="col-md-3">
            <label for="add_term_start" class="form-label">Term Start</label>
            <input id="add_term_start" type="date" class="form-control" name="term_start">
          </div>

          <div class="col-md-3">
            <label for="add_term_end" class="form-label">Term End</label>
            <input id="add_term_end" type="date" class="form-control" name="term_end">
          </div>

          <div class="col-md-6">
            <label for="add_contact" class="form-label">Contact</label>
            <input id="add_contact" class="form-control" name="contact" autocomplete="tel">
          </div>

          <div class="col-md-6">
            <label for="add_email" class="form-label">Email</label>
            <input id="add_email" type="email" name="email" class="form-control" autocomplete="email">
          </div>

          <div class="col-md-4">
            <label for="add_status" class="form-label">Status</label>
            <select id="add_status" class="form-select" name="status">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-8">
            <label for="add_photo" class="form-label">Photo</label>
            <input id="add_photo" type="file"
                  class="form-control"
                  name="photo"
                  accept="image/*"
                  onchange="previewAddPhoto(event)">
          </div>

          <div class="col-md-4 text-center">
            <img id="addPhotoPreview"
                src="/BIS/assets/images/default-avatar.png"
                width="120"
                height="120"
                style="object-fit:cover; border-radius:50%;">
          </div>

        </div>


      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>

    </form>
  </div>
</div>

  <!-- EDIT MODAL -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="POST" action="/BIS/controller/officials.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="old_photo" id="edit_old_photo">

        <div class="modal-header">
          <h5 class="modal-title">Edit Official</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

      <div class="modal-body row g-3">

        <div class="col-md-6">
          <label for="edit_position" class="form-label">Position *</label>
          <input class="form-control" name="position" id="edit_position" required autocomplete="organization-title">
        </div>

        <div class="col-md-6">
          <label for="edit_full_name" class="form-label">Full Name *</label>
          <input class="form-control" name="full_name" id="edit_full_name" required autocomplete="name">
        </div>

        <div class="col-md-6">
          <label for="edit_committee" class="form-label">Committee</label>
          <input class="form-control" name="committee" id="edit_committee">
        </div>

        <div class="col-md-3">
          <label for="edit_term_start" class="form-label">Term Start</label>
          <input type="date" class="form-control" name="term_start" id="edit_term_start">
        </div>

        <div class="col-md-3">
          <label for="edit_term_end" class="form-label">Term End</label>
          <input type="date" class="form-control" name="term_end" id="edit_term_end">
        </div>

        <!-- PHOTO (EDIT) -->
        <div class="col-md-8">
          <label for="edit_photo" class="form-label">Photo</label>
          <input type="file"
                id="edit_photo"
                class="form-control"
                name="photo"
                accept="image/*"
                onchange="previewEditPhoto(event)">
          <div class="form-text">Leave blank if you don’t want to change the photo.</div>
        </div>

        <div class="col-md-4 text-center">
          <img id="editPhotoPreview"
              src="/BIS/assets/images/default-avatar.png"
              width="120"
              height="120"
              style="object-fit:cover; border-radius:50%;">
        </div>

        <div class="col-md-6">
          <label for="edit_contact" class="form-label">Contact</label>
          <input class="form-control" name="contact" id="edit_contact" autocomplete="tel">
        </div>

        <div class="col-md-6">
          <label for="edit_email" class="form-label">Email</label>
          <input type="email" name="email" id="edit_email" class="form-control" autocomplete="email">
        </div>

        <div class="col-md-4">
          <label for="edit_status" class="form-label">Status</label>
          <select class="form-select" name="status" id="edit_status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

      </div>


        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Update</button>
        </div>

      </form>
    </div>
  </div>

                         <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const o = JSON.parse(btn.getAttribute('data-official') || '{}');

      document.getElementById('edit_id').value = o.id || '';
      document.getElementById('edit_position').value = o.position || '';
      document.getElementById('edit_full_name').value = o.full_name || '';
      document.getElementById('edit_committee').value = o.committee || '';
      document.getElementById('edit_term_start').value = o.term_start || '';
      document.getElementById('edit_term_end').value = o.term_end || '';
      document.getElementById('edit_contact').value = o.contact || '';
      document.getElementById('edit_email').value = o.email || '';
      document.getElementById('edit_status').value = o.status || 'Active';
      document.getElementById('edit_old_photo').value = o.photo || '';



      document.getElementById('edit_old_photo').value = o.photo || '';
      const preview = document.getElementById('editPhotoPreview');
      preview.src = o.photo
        ? '/BIS/uploads/officials/' + o.photo
        : '/BIS/assets/images/default-avatar.png';
    });
  });

  function previewEditPhoto(event) {
    const img = document.getElementById('editPhotoPreview');
    if (event.target.files && event.target.files[0]) {
      img.src = URL.createObjectURL(event.target.files[0]);
    }
  }

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