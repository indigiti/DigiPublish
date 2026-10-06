# Interactivity Architecture

DigiPublish is migrating frontend behavior to the WordPress Interactivity API in tested slices.

## Phase 1: Posts carousel

The Posts block carousel is the first migrated interaction.

### Server markup

`digipublish-core/blocks/post-feed/render.php` declares:

- `data-wp-interactive="digipublish/post-feed"`
- local carousel context with current index, total slides, wrap and autoplay settings;
- `data-wp-init` lifecycle setup;
- `data-wp-on--click` navigation actions;
- `data-wp-on--scroll` synchronization;
- `data-wp-text` reactive current-slide output.

### Client module

`blocks/post-feed/view-interactivity.js` is loaded through `viewScriptModule` and depends on the WordPress Core `@wordpress/interactivity` module.

It owns:

- previous/next/dot navigation;
- scroll-snap synchronization;
- autoplay;
- pause on pointer/focus;
- prefers-reduced-motion handling;
- reactive current-slide counter and ARIA selection state.

`blocks/post-feed/view.js` remains a classic view script only for AJAX Load More and infinite pagination. It must not initialize carousel behavior.

## Compatibility

Historical `data-dp-carousel-*` attributes remain in markup as stable compatibility/test hooks. The runtime owner is the WordPress Interactivity API.

No saved block attributes or serialized block markup are migrated.

## Release gate

The Playwright release fixture at `/digipublish-carousel-test/` verifies:

- the Interactivity namespace is present;
- next and previous controls update the carousel;
- dot navigation updates the current slide;
- counter state changes;
- active-dot `aria-selected` state stays coherent;
- no browser errors occur.

## Next slices

After this phase is stable:

1. migrate Posts Load More/infinite pagination;
2. consolidate Instagram/Twitter carousel behavior on the same store pattern where semantics align;
3. migrate shell overlays/dark-mode state;
4. migrate Auto Load Next after dedicated history/REST browser coverage.
