# Interactivity Architecture

DigiPublish is migrating frontend behavior to the WordPress Interactivity API in small, tested stages.

## Phase 1: Posts async pagination

The `digipublish/post-feed` block uses the Interactivity API for:

- Load More click handling.
- Infinite-scroll observer lifecycle.
- Per-block loading/ended/status context.
- Reactive `aria-busy`, `disabled`, `hidden`, and status text.

The store is `digipublish/post-feed` and its script module lives at:

`digipublish-core/assets/post-feed-interactivity.js`

The module is registered with WordPress Core's `@wordpress/interactivity` Script Module as its only dependency. No production npm/runtime package is bundled.

Async actions use generator functions so WordPress can preserve the correct interaction scope across fetch operations.

## Compatibility boundary

Historical `data-dp-*` pagination attributes remain on the wrapper during the 1.x compatibility window. Active Load More / Infinite behavior is owned by `data-wp-*` directives.

Posts carousel behavior remains in the existing first-party `blocks/post-feed/view.js` during this phase and will move separately after pagination is proven.

## Release gate

The WordPress runtime test suite creates a real page containing an AJAX Posts block and verifies:

1. Interactivity API directives are present.
2. Three initial cards render.
3. Clicking Load More appends the next three cards.
4. The block returns to `aria-busy="false"`.
5. The live status region clears after success.

This migration must continue to pass PHPUnit, Playwright, Axe and Lighthouse.
