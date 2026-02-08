<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Announcement.php';

// ADMIN GUARD
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// FLASH
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);


$ann = new Announcement($db);
$rows = $ann->all();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Announcements</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>
<body class="bg-light">

<!-- LEFT SIDEBAR -->
<?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>


<div class="main-content" id="mainContent">

<!-- TOP NAVBAR -->
<?php require_once __DIR__ . '/../navbar_top.php'; ?>

  <div class="container-fluid py-4">



  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Announcements</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-megaphone"></i> Add Announcement
    </button>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

    
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Title</th>
              <th>Status</th>
              <th>Date Posted</th>
              <th>Posted By</th>
              <th>Attachments</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($r['title']) ?></div>
                <div class="text-muted small text-truncate" style="max-width:520px;">
                  <?= htmlspecialchars($r['details']) ?>
                </div>
              </td>
              <td>
                <span class="badge <?= $r['status']==='Active' ? 'bg-success' : 'bg-secondary' ?>">
                  <?= htmlspecialchars($r['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($r['date_posted']) ?></td>
              <td><?= htmlspecialchars($r['posted_by_name']) ?></td>
              <td><?= (int)$r['attachments_count'] ?></td>

              <td class="text-end">

                <?php if ((int)$r['attachments_count'] > 0 ): ?>
                  <a class="btn btn-sm btn-outline-dark"
                   href="/BIS/views/admin/announcement_attachments.php?id=<?= (int)$r['id'] ?>">
                    <i class="bi bi-paperclip"></i>
                  </a>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-dark" disabled>
                    <i class="bi bi-paperclip"></i>
                  </button>
                <?php endif; ?>

                <button
                  class="btn btn-sm btn-outline-primary btn-edit"
                  data-bs-toggle="modal"
                  data-bs-target="#editModal"
                  data-ann='<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>'
                >
                  <i class="bi bi-pencil"></i>
                </button>

                <form class="d-inline" method="POST" action="/BIS/controller/announcements_manage.php"
                      onsubmit="return confirm('Delete this announcement? This will also remove its attachments.');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  </div> <!-- /container-fluid -->
</div> <!-- /main-content -->

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="/BIS/controller/announcements_manage.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="action" value="add">

      <div class="modal-header">
        <h5 class="modal-title">Add Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

<div class="col-12">
  <label for="add_title" class="form-label">Title *</label>
  <input id="add_title" name="title" class="form-control" required>
</div>

<div class="col-12">
  <label for="add_details" class="form-label">Details *</label>
  <textarea id="add_details" name="details" class="form-control" rows="5" required></textarea>
</div>

<div class="col-md-4">
  <label for="add_status" class="form-label">Status</label>
  <select id="add_status" name="status" class="form-select">
    <option value="Active">Active</option>
    <option value="Archived">Archived</option>
  </select>
</div>

<div class="col-md-8">
  <label for="add_attachments" class="form-label">Attachments (Images/Files)</label>
  <input id="add_attachments" type="file" name="attachments[]" class="form-control" multiple>
  <div class="form-text">Allowed: JPG/PNG/WEBP, PDF, DOC/DOCX (max 5MB each)</div>
</div>


      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>

    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="/BIS/controller/announcements_manage.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id">

      <div class="modal-header">
        <h5 class="modal-title">Edit Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body row g-3">
        <div class="col-12">
          <label for="edit_title" class="form-label">Title *</label>
        <input name="title" id="edit_title" class="form-control" required>
      </div>

        <div class="col-12">
          <label for="edit_details" class="form-label">Details *</label>
          <textarea name="details" id="edit_details" class="form-control" rows="5" required></textarea>
        </div>

        <div class="col-md-4">
          <label for="edit_status" class="form-label">Status</label>
          <select name="status" id="edit_status" class="form-select">
            <option value="Active">Active</option>
            <option value="Archived">Archived</option>
          </select>
        </div>

        <div class="col-md-8">
          <label for="edit_attachments" class="form-label">Add More Attachments (optional)</label>
          <input type="file" id="edit_attachments" name="attachments[]" class="form-control" multiple>
          <div class="form-text">Adding files here will append; it won’t remove old ones.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>

    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    const r = JSON.parse(btn.getAttribute('data-ann'));
    document.getElementById('edit_id').value = r.id || '';
    document.getElementById('edit_title').value = r.title || '';
    document.getElementById('edit_details').value = r.details || '';
    document.getElementById('edit_status').value = r.status || 'Active';
  });
});


 const toggleBtn = document.getElementById("toggleSidebar");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const sidebar = document.getElementById("sidebar");
      const main = document.querySelector(".main-content");
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
