<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /BIS/views/login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Household.php';


// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$householdModel = new Household($conn);
$householdCode = method_exists($householdModel, 'generateCode')
    ? $householdModel->generateCode()
    : '';

// Get puroks
$puroks = [];
$res = $conn->query("SELECT id, name FROM puroks ORDER BY name ASC");
while ($row = $res->fetch_assoc()) $puroks[] = $row;

// Get residents
$residents = [];
$res = $conn->query("SELECT id, first_name, last_name FROM residents WHERE is_active = 1");
while ($row = $res->fetch_assoc()) $residents[] = $row;

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Household</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BIS/assets/css/sidebar.css">
</head>

<body style="background:#D6D5D7;">
<?php require_once __DIR__ . '/../../views/navbaradmin_leftside.php'; ?>

<div class="main-content p-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>Add Household</h3>
        <a href="/BIS/controller/households_manage.php" class="btn btn-secondary btn-sm">
            ← Back
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/BIS/controller/household_store.php" class="card shadow">
        <div class="card-body">

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Household Code</label>
                    <input type="text" name="household_code" class="form-control"
                           value="<?= htmlspecialchars($householdCode) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purok</label>
                    <select name="purok_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Head of Household</label>
                    <select name="head_resident_id" class="form-select">
                        <option value="">Optional</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?= $r['id'] ?>">
                                <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="address_line" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Landmark</label>
                    <input type="text" name="landmark" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Household Type</label>
                    <select name="household_type" class="form-select">
                        <option value="">Select</option>
                        <option>Nuclear</option>
                        <option>Single Parent</option>
                        <option>Extended</option>
                        <option>Others</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tenure Status</label>
                    <select name="tenure_status" class="form-select">
                        <option value="">Select</option>
                        <option>Owner</option>
                        <option>Renter</option>
                        <option>Others</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Monthly Income</label>
                    <input type="text" name="monthly_income_range" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">4Ps Beneficiary</label><br>
                    <input type="checkbox" name="is_4ps_beneficiary" value="1"> Yes
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>
            </div>

        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Save Household</button>
        </div>
    </form>
</div>

</body>
</html>
