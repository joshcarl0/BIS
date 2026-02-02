<nav class="navbar navbar-expand-lg navbar-dark px-3" style="background:#1C2E81;">
    <button id="toggleSidebar" class="btn btn-light me-3" type="button">
        <i id="toggleIcon" class="bi bi-list"></i>
    </button>

    <span class="navbar-brand mb-0 h1">Admin Dashboard</span>

    <div class="ms-auto text-white">
        <i class="bi bi-person-circle me-1"></i>
        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
    </div>
</nav>
