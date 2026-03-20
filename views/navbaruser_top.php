<div class="topbar d-flex align-items-center justify-content-between px-4">

    <!-- LEFT SIDE -->
    <div class="d-flex align-items-center gap-3">
        <!-- Sidebar Toggle -->
        <button class="btn btn-link text-dark fs-4 p-0" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>

        <h5 class="mb-0 fw-bold text-dark">
            Resident Dashboard
        </h5>
    </div>

    <!-- RIGHT SIDE -->
    <div class="d-flex align-items-center gap-3">

        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold">
                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </span>
        </div>

<?php
$avatar = $_SESSION['avatar'] ?? '';
$avatarPath = $avatar ? "/BIS/" . $avatar : "/BIS/assets/images/default-avatar.png";
?>

<img 
    src="<?= htmlspecialchars($avatarPath) ?>" 
    onerror="this.src='https://via.placeholder.com/40'"
    class="rounded-circle border"
    width="36" 
    height="36"
    alt="avatar">
    </div>
</div>