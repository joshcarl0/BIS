document.addEventListener( 'click', function (e) {
const a = e.target.closest('a[data-bs-target][data-start]');
  if (!a) return;

  const modalSel = a.getAttribute('data-bs-target');
  const start = parseInt(a.getAttribute('data-start') || '0', 10);

  const modal = document.querySelector(modalSel);
  if (!modal) return;

  modal.addEventListener('shown.bs.modal', function handler() {
    modal.removeEventListener('shown.bs.modal', handler);

    const carouselEl = modal.querySelector('.carousel');
    if (!carouselEl) return;

    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
    carousel.to(start);
  });
});