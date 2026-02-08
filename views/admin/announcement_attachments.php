<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Announcement.php';

//ADMIN GUARD FOR ADMIN ONLY CAN ACCESS THIS

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
        header("Location: /BIS/views/login.php");
        exit;

}


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: /BIS/views/admin/admin_announcements.php");
        exit;

}


$ann = new Announcement($db);
$announcement = $ann->find($id);
$attachments = $ann->attachments($id);

if (!$announcement) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Announcemnt not found.'];
            header("Location: /BIS/views/admin/admin_announcements.php");
        exit;

}

function is_image(string $path = '', string $type = ''): bool {
    $p = strtolower($path);
    $t = strtolower($type);
    if (strpos($t, 'image/') === 0) return true;
    return (bool)preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $p);
}

// URL to ensure it start on BIS

function normalize_url(string $url): string {
$url = $url  ?: '';
if ($url !== '' && strpos($url, '/BIS/') !== 0) {

    }
return $url;

} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Attachements</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body class="bg-light" style="background:#D6D5D7;">

<!--- Left side bar -->
    <?php require_once __DIR__ . '/../navbaradmin_leftside.php'; ?>
    
    <div class="main-content" id="mainContent">

        <!-- TOP NAVBAR -->
  <?php require_once __DIR__ . '/../navbar_top.php'; ?>

    <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1">Attachments</h4>
        <div class="text-muted small">
          Announcement: <strong><?= htmlspecialchars($announcement['title']) ?></strong>
        </div>
      </div>

      <a href="/BIS/views/admin/admin_announcements.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
    
<?php if (empty($attachments)): ?>
      <div class="alert alert-info">No attachments uploaded for this announcement.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($attachments as $att): ?>
          <?php
            $name = $att['file_name'] ?? 'file';
            $type = $att['file_type'] ?? '';
            $url  = normalize_url($att['file_path'] ?? '');
          ?>

          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card shadow-sm h-100">

              <?php if (is_image($url, $type)): ?>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-decoration-none">
                  <img src="<?= htmlspecialchars($url) ?>"
                       class="card-img-top"
                       style="height: 180px; object-fit: cover;"
                       alt="<?= htmlspecialchars($name) ?>">
                </a>
              <?php else: ?>
                <div class="card-body d-flex align-items-center gap-2">
                  <i class="bi bi-file-earmark-text fs-1"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($name) ?></div>
                    <div class="text-muted small text-truncate"><?= htmlspecialchars($type ?: 'File') ?></div>
                  </div>
                </div>
              <?php endif; ?>

              <div class="card-body pt-2">
                <div class="small text-truncate"><?= htmlspecialchars($name) ?></div>

                <div class="d-flex gap-2 mt-2">
                  <a class="btn btn-sm btn-outline-primary w-100" href="<?= htmlspecialchars($url) ?>" target="_blank">
                    <i class="bi bi-eye"></i> Open
                  </a>
                  <a class="btn btn-sm btn-outline-success w-100" href="<?= htmlspecialchars($url) ?>" download>
                    <i class="bi bi-download"></i> Download
                  </a>
                </div>

              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

