/**
 * DigiPublish Posts carousel controller.
 * Async pagination is owned by the WordPress Interactivity API module.
 * Carousel behavior remains first-party/browser-native during the staged migration.
 */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initCarousel(section) {
    if (!section || section.dataset.dpCarouselInit === '1') return;
    const track = section.querySelector('[data-dp-post-carousel-track]');
    if (!track) return;

    section.dataset.dpCarouselInit = '1';
    const cards = Array.prototype.slice.call(track.querySelectorAll('.tp-card'));
    if (!cards.length) return;

    const prev = section.querySelector('[data-dp-carousel-prev]');
    const next = section.querySelector('[data-dp-carousel-next]');
    const counter = section.querySelector('[data-dp-carousel-current]');
    const dots = Array.prototype.slice.call(section.querySelectorAll('[data-dp-carousel-dot]'));
    const wrap = section.getAttribute('data-dp-carousel-wrap') !== '0';
    const autoplay = section.getAttribute('data-dp-carousel-autoplay') === '1' && !reduceMotion;
    let current = 0;
    let timer = null;
    let scrollFrame = 0;

    function update(index) {
      current = Math.max(0, Math.min(cards.length - 1, index));
      if (counter) counter.textContent = String(current + 1);
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
        dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
      });
    }

    function scrollToIndex(index) {
      if (wrap) {
        if (index < 0) index = cards.length - 1;
        if (index >= cards.length) index = 0;
      } else {
        index = Math.max(0, Math.min(cards.length - 1, index));
      }
      const card = cards[index];
      if (!card) return;
      const left = card.offsetLeft - track.offsetLeft;
      track.scrollTo({ left: left, behavior: reduceMotion ? 'auto' : 'smooth' });
      update(index);
    }

    function closestIndex() {
      const scrollLeft = Math.abs(track.scrollLeft);
      let best = 0;
      let distance = Infinity;
      cards.forEach(function (card, index) {
        const nextDistance = Math.abs((card.offsetLeft - track.offsetLeft) - scrollLeft);
        if (nextDistance < distance) {
          distance = nextDistance;
          best = index;
        }
      });
      update(best);
    }

    function stopAutoplay() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function startAutoplay() {
      if (!autoplay || timer) return;
      timer = window.setInterval(function () { scrollToIndex(current + 1); }, 5000);
    }

    if (prev) prev.addEventListener('click', function () { scrollToIndex(current - 1); });
    if (next) next.addEventListener('click', function () { scrollToIndex(current + 1); });
    dots.forEach(function (dot, index) {
      dot.addEventListener('click', function () { scrollToIndex(index); });
    });

    track.addEventListener('scroll', function () {
      if (scrollFrame) return;
      scrollFrame = window.requestAnimationFrame(function () {
        scrollFrame = 0;
        closestIndex();
      });
    }, { passive: true });

    section.addEventListener('pointerenter', stopAutoplay);
    section.addEventListener('pointerleave', startAutoplay);
    section.addEventListener('focusin', stopAutoplay);
    section.addEventListener('focusout', startAutoplay);

    update(0);
    startAutoplay();
  }

  function initSection(section) {
    if (section.classList.contains('tp-post-feed--carousel')) initCarousel(section);
  }

  function initAll() {
    document.querySelectorAll('.tp-post-feed').forEach(initSection);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();