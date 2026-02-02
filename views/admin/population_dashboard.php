<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Population Overview</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class = "bg-light">

<?php require_once __DIR__ . '/../views/navbaradmin_leftside.php'; ?>

<div class="container py-4" style="margin-left:250px;">
<h3>Population Overview</h3>
<p class="text-muted">Real-time data from residents & household tables.</p>

 <div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><div class="fw-bold">Population</div><div class="fs-3"><?= (int)$data['total_population'] ?></div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="fw-bold">Households</div><div class="fs-3"><?= (int)$data['total_households'] ?></div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="fw-bold">Avg Age</div><div class="fs-3"><?= htmlspecialchars($data['avg_age']) ?></div></div></div>
  </div>

  <div class="card p-3 mb-3">
    <h5>Population by Purok</h5>
    <table class="table table-hover">
      <thead class="table-secondary"><tr><th>Purok</th><th>Population</th><th>Households</th></tr></thead>
      <tbody>
        <?php foreach ($data['by_purok'] as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['purok']) ?></td>
            <td><?= (int)$r['population'] ?></td>
            <td><?= (int)$r['households'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card p-3">
    <h5>Gender</h5>
    <ul class="mb-0">
      <?php foreach ($data['gender'] as $g): ?>
        <li><?= htmlspecialchars($g['sex']) ?>: <?= (int)$g['total'] ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
</body>
</html>
    

    
</body>
</html>