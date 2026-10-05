(function () {
  'use strict';

  if (window.digiPublishPostsCarouselBound) return;
  window.digiPublishPostsCarouselBound = true;

  document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-dp-carousel-prev],[data-dp-carousel-next]');
    if (!button) return;

    const section = button.closest('.tp-post-feed');
    const track = section && section.querySelector('[data-dp-post-carousel-track]');
    if (!track) return;

    const forward = button.hasAttribute('data-dp-carousel-next');
    const rtl = getComputedStyle(track).direction === 'rtl';
    const direction = (forward ? 1 : -1) * (rtl ? -1 : 1);
    const amount = Math.max(240, Math.round(track.clientWidth * 0.86));
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    track.scrollBy({
      left: direction * amount,
      behavior: reduceMotion ? 'auto' : 'smooth'
    });
  });
})();