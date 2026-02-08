<?php
// $rows galing controller

function isImageFile(string $name): bool {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  return in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
}

function isNewPost(?string $dateStr, int $days = 3): bool {
  if (!$dateStr) return false;
  $t = strtotime($dateStr);
  if ($t === false) return false;
  return (time() - $t) <= ($days * 86400);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Announcements</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">

  <?php require_once __DIR__ . '/../navbaruser_side.php'; ?>

  <div id="mainContent" class="main-content p-0">
    <?php require_once __DIR__ . '/../navbaruser_top.php'; ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Barangay Announcements</h3>
        <span class="text-muted"><?= count($rows) ?> active</span>
      </div>

      <?php if (empty($rows)): ?>
        <div class="alert alert-secondary">No announcements yet.</div>
      <?php else: ?>
        <div class="mx-auto" style="max-width: 820px;">
  <?php foreach ($rows as $i => $row): ?>
    <?php
      $postedAt = $row['date_posted'] ?? ($row['created_at'] ?? null);

      // attachment fields from your JOIN
      $attachmentPath = $row['attachment_path'] ?? '';
      $attachmentName = $row['attachment_name'] ?? '';
      $file = $attachmentPath ? basename($attachmentPath) : '';

      $previewUrl  = $file ? ("/BIS/controller/announcement_file.php?mode=inline&file=" . rawurlencode($file)) : '';
      $downloadUrl = $file ? ("/BIS/controller/announcement_file.php?mode=download&file=" . rawurlencode($file)) : '';

      $isImg = $file && isImageFile($file);

      $timeLabel = $postedAt ? date("M j, Y g:i A", strtotime($postedAt)) : '';
      $details = $row['details'] ?? ($row['description'] ?? '');

        $aid = (int)($row['id'] ?? 0);
        $attachments = $attMap[$aid] ?? [];
    ?>

    <div class="card shadow-sm mb-3">
      <div class="card-body">

        <!-- HEADER -->
        <div class="d-flex align-items-start gap-3">
          <!-- avatar (placeholder) -->
          <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center"
               style="width:44px;height:44px; flex: 0 0 44px;">
            <span class="fw-bold text-secondary">B</span>
          </div>

          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="fw-semibold">
                <?= htmlspecialchars($row['posted_by'] ?? 'Barangay Admin') ?>
              </div>

              <?php if ($i === 0): ?>
                <span class="badge text-bg-primary">Newest</span>
              <?php elseif (isNewPost($postedAt, 3)): ?>
                <span class="badge text-bg-success">New</span>
              <?php endif; ?>
            </div>

            <div class="text-muted small">
              <?= htmlspecialchars($timeLabel) ?>
            </div>
          </div>

          <!-- menu dots (optional look) -->
          <div class="text-muted">⋯</div>
        </div>

        <!-- TITLE -->
        <h5 class="mt-3 mb-2"><?= htmlspecialchars($row['title'] ?? '') ?></h5>

        <!-- BODY TEXT -->
        <?php if (!empty($details)): ?>
          <div class="mb-3">
            <?= nl2br(htmlspecialchars($details)) ?>
          </div>
        <?php endif; ?>

<!-- MEDIA -->
<?php if (!empty($attachments)): ?>
  <?php
    $postId = $aid;                  // unique per announcement
    $modalId = "attModal_" . $postId;
    $carId   = "attCarousel_" . $postId;

    // build urls list
    $urls = [];
    foreach ($attachments as $a) {
      $f = basename($a['file_path'] ?? '');
      if (!$f) continue;
      $urls[] = "/BIS/controller/announcement_file.php?mode=inline&file=" . rawurlencode($f);
    }

    $total = count($urls);
    $mainUrl = $urls[0] ?? '';
    $thumbs = array_slice($urls, 0, 4); // show max 4 thumbs (1 big + 3 small), last can be +N
    $moreCount = max(0, $total - 4);
  ?>

  <?php if ($mainUrl): ?>
    <!-- BIG IMAGE -->
    <a href="#" class="d-block mb-2"
       data-bs-toggle="modal"
       data-bs-target="#<?= $modalId ?>"
       data-start="0">
      <img src="<?= $mainUrl ?>" class="img-fluid rounded border"
           style="width:100%; max-height:460px; object-fit:cover;">
    </a>
  <?php endif; ?>

  <!-- THUMB ROW -->
  <?php if ($total > 1): ?>
    <div class="d-flex gap-2 flex-wrap">
      <?php foreach (array_slice($urls, 1, 3) as $idx => $u): ?>
        <?php $realIndex = $idx + 1; ?>
        <a href="#"
           class="d-block position-relative"
           data-bs-toggle="modal"
           data-bs-target="#<?= $modalId ?>"
           data-start="<?= $realIndex ?>">
          <img src="<?= $u ?>" class="rounded border"
               style="width:120px;height:120px;object-fit:cover;">
        </a>
      <?php endforeach; ?>

      <!-- +N MORE overlay on the last thumb (if more) -->
      <?php if ($moreCount > 0 && isset($urls[3])): ?>
        <a href="#"
           class="d-block position-relative"
           data-bs-toggle="modal"
           data-bs-target="#<?= $modalId ?>"
           data-start="3">
          <img src="<?= $urls[3] ?>" class="rounded border"
               style="width:120px;height:120px;object-fit:cover; filter: brightness(0.65);">
          <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
            <div class="text-white fw-bold" style="font-size: 20px;">+<?= (int)$moreCount ?> more</div>
          </div>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- MODAL SLIDESHOW -->
  <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark">
        <div class="modal-header border-0">
          <h6 class="modal-title text-white mb-0">Attachments (<?= (int)$total ?>)</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-0">
          <div id="<?= $carId ?>" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner">
              <?php foreach ($urls as $k => $u): ?>
                <div class="carousel-item <?= $k === 0 ? 'active' : '' ?>">
                  <img src="<?= $u ?>" class="d-block w-100"
                       style="max-height: 80vh; object-fit: contain; background:#000;">
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ($total > 1): ?>
              <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carId ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#<?= $carId ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Next</span>
              </button>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>

<?php endif; ?>



      </div>
    </div>

            <?php endforeach; ?>
        </div> <!-- /mx-auto -->
      <?php endif; ?>

    </div> <!-- /container -->
  </div> <!-- /mainContent -->

  <script src="/BIS/assets/js/sidebar_toggle.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script src="/BIS/assets/js/announcement_slideshow.js"></script>

</body>
</html>

