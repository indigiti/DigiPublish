/**
 * DigiPublish Posts async pagination controller.
 *
 * Carousel behavior is owned by view-interactivity.js through the WordPress
 * Interactivity API. This classic view script remains only for Load More and
 * infinite pagination while those flows are migrated separately.
 */
(function () {
  'use strict';

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
      }
      if (status) status.textContent = '';
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

  document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-dp-load-more]');
    if (!button) return;
    loadNext(button.closest('.tp-post-feed'));
  });

  function initAll() {
    document.querySelectorAll('.tp-post-feed[data-dp-pagination="infinite"]').forEach(initInfinite);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
