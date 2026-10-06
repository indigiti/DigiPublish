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

### Classic compatibility boundary

`digipublish/assets/js/site-interactions.js` is restricted to Auto Load Next Post and is enqueued only when Auto Load Next is active on a singular post.

Search, menu, scheme, Escape handling and sticky-header behavior must not return to that classic controller.

## Remaining migration slices

1. Posts Load More/infinite pagination.
2. Auto Load Next Post with history/REST lifecycle coverage.
3. Social/gallery interactions where classic DOM lifecycle code still exists.
4. Client-navigation compatibility declarations only after each migrated flow passes runtime tests.
