/**
 * DigiPublish Posts frontend controller.
 * Behavior adapted from the GPL-3.0 Caards posts/carousel system by
 * Code Supply Co., rewritten without Canvas/Flickity dependencies.
 */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function postIds(section) {
    return Array.prototype.map.call(section.querySelectorAll('.tp-card[data-post-id]'), function (card) {
      return parseInt(card.getAttribute('data-post-id'), 10) || 0;
    }).filter(Boolean);
  }

  function attributes(section) {
    try {
      return JSON.parse(section.getAttribute('data-dp-attributes') || '{}');
    } catch (error) {
      return {};
    }
  }

  async function loadNext(section) {
    if (!section || section.dataset.dpLoading === '1') return;

    const page = parseInt(section.getAttribute('data-dp-page') || '1', 10);
    const maxPages = parseInt(section.getAttribute('data-dp-max-pages') || '1', 10);
    if (page >= maxPages) return;

    const track = section.querySelector('.tp-feed');
    const button = section.querySelector('[data-dp-load-more]');
    const sentinel = section.querySelector('[data-dp-infinite-sentinel]');
    const status = section.querySelector('[data-dp-load-status]');
    const endpoint = section.getAttribute('data-dp-rest-url');
    const attrs = attributes(section);

    if (!track || !endpoint) return;

    section.dataset.dpLoading = '1';
    if (button) button.disabled = true;
    if (status) status.textContent = 'Loading…';

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page: page + 1,
          attributes: attrs,
          exclude: postIds(section),
          relatedPostId: parseInt(attrs._relatedPostId || 0, 10) || 0
        })
      });
      if (!response.ok) throw new Error('HTTP ' + response.status);

      const data = await response.json();
      if (data && data.content) {
        track.insertAdjacentHTML('beforeend', data.content);
      }

      const nextPage = data && data.page ? parseInt(data.page, 10) : page + 1;
      section.setAttribute('data-dp-page', String(nextPage));

      const ended = !data || data.postsEnd || nextPage >= maxPages || !data.content;
      if (ended) {
        if (button) button.hidden = true;
        if (sentinel) sentinel.hidden = true;
        if (status) status.textContent = '';
      } else {
        if (status) status.textContent = '';
      }
    } catch (error) {
      if (status) status.textContent = 'Could not load more posts.';
    } finally {
      section.dataset.dpLoading = '0';
      if (button && !button.hidden) button.disabled = false;
    }
  }

  function initInfinite(section) {
    const sentinel = section.querySelector('[data-dp-infinite-sentinel]');
    if (!sentinel || sentinel.dataset.dpObserverBound === '1') return;
    sentinel.dataset.dpObserverBound = '1';

    if (!('IntersectionObserver' in window)) {
      sentinel.hidden = true;
      return;
    }

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !sentinel.hidden) loadNext(section);
      });
    }, { rootMargin: '600px 0px' });

    observer.observe(sentinel);
  }

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
    if (section.getAttribute('data-dp-pagination') === 'infinite') initInfinite(section);
  }

  document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-dp-load-more]');
    if (!button) return;
    loadNext(button.closest('.tp-post-feed'));
  });

  function initAll() {
    document.querySelectorAll('.tp-post-feed').forEach(initSection);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();