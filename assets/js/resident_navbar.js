// assets/js/resident_navbar.js
(function () {
  const sidebar = document.getElementById("sidebar") || document.querySelector(".sidebar-left");
  const toggleBtn = document.getElementById("toggleSidebar");

  // Sidebar collapse (desktop)
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
    });
  }

  // Optional: close sidebar when clicking outside (mobile)
  document.addEventListener("click", function (e) {
    if (!sidebar) return;

    // only if using mobile "show" class
    if (!sidebar.classList.contains("show")) return;

    const clickedInsideSidebar = sidebar.contains(e.target);
    const clickedToggle = toggleBtn && toggleBtn.contains(e.target);

    if (!clickedInsideSidebar && !clickedToggle) {
      sidebar.classList.remove("show");
    }
  });

  // Optional: ESC closes mobile sidebar
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && sidebar) {
      sidebar.classList.remove("show");
    }
  });
})();