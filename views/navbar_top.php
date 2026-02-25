<nav class="navbar navbar-expand-lg navbar-dark px-3" style="background:#dcb611;">
    <button id="toggleSidebar"
            class="btn p-0 me-3"
            type="button"
            aria-label="Toggle sidebar">
        <i id="toggleIcon" class="bi bi-list fs-2 text-dark"></i>
    </button>

    <span class="navbar-brand mb-0 h1 text-dark">Admin Dashboard</span>

    <div class="ms-auto text-dark fw-semibold">
        <i class="bi bi-person-circle me-1"></i>
        <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
    </div>
</nav>

<?php include_once __DIR__ . '/../includes/cookie_consent_modal.php'; ?>
