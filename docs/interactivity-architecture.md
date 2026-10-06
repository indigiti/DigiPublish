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

`blocks/post-feed/view-interactivity.js` is now the sole Posts frontend controller. It owns carousel, AJAX Load More and infinite pagination; the former classic `view.js` is no longer registered.

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

1. consolidate Instagram/Twitter carousel behavior on the same store pattern where semantics align;
2. migrate Auto Load Next after dedicated history/REST browser coverage.


## Phase 2: Site shell

The filesystem/Site Editor header remains a Core template part. DigiPublish injects directives at render time with `WP_HTML_Tag_Processor`, so no replacement shell block is required.

### Store

`digipublish/assets/js/site-interactivity.js` owns the `digipublish/site` namespace and controls:

- light/dark scheme state and localStorage compatibility;
- search overlay state;
- fullscreen menu state;
- Escape-key handling;
- search-input focus;
- sticky-header state;
- compatibility body/root classes for historical custom CSS.

The header template part is marked as an interactive root through `register_block_type_args`. Core then processes the complete template-part tree exactly once, including any nested interactive blocks. DigiPublish intentionally does not call `wp_interactivity_process_directives()` manually.

### Unified site interaction runtime

`digipublish/assets/js/site-interactivity.js` now owns both shell behavior and Auto Load Next. The former classic `site-interactions.js` controller is retired; no separate frontend script is required for singular-post loading.

## Phase 3: Posts async pagination

The existing `digipublish/post-feed` store now also owns Load More and Infinite Scroll. Server-rendered context tracks page, maximum pages, REST URL, query attributes, loading state, end state and accessible status text. The Load More button and infinite sentinel use Core directives, while `withScope()` preserves the Interactivity API context inside `IntersectionObserver` callbacks.

The historical `data-dp-*` pagination hooks remain in markup during the 1.x compatibility window, but no classic Posts frontend controller is registered.

## Phase 4: Auto Load Next

Auto Load Next now uses the existing `digipublish/site` Interactivity store. A server-rendered runtime root carries the current post ID, loaded IDs, REST endpoint, loading/end state and accessible status. An Interactivity callback owns the near-viewport observer; the generator action fetches and inserts the next article fragment and preserves the existing URL/title history behavior.

The REST route, adjacent-post semantics and per-post/global publishing settings are unchanged.

## Remaining migration slices

1. Social/gallery interactions where classic DOM lifecycle code still exists.
2. Client-navigation compatibility declarations only after each migrated flow passes runtime tests.
