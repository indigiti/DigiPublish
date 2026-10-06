# Runtime Class Namespace

DigiPublish uses the canonical `dp-*` class namespace for active theme markup, block renderers and first-party browser interactions.

## Canonical output

Filesystem templates, template parts, the canonical homepage pattern and dynamic DigiPublish presentation blocks emit classes such as:

- `dp-header`
- `dp-footer`
- `dp-entry-hero`
- `dp-article-layout`
- `dp-social-carousel`
- `dp-demo-mosaic`
- `dp-nextpost-section`

New runtime-created JavaScript elements also use canonical names such as `dp-header-marker`, `dp-nextpost-sentinel` and `dp-nextpost-status`.

## Saved-content compatibility

Older Site Editor saves and the historical hidden homepage pattern may still contain `dp-caards-*` classes. DigiPublish does not rewrite those database records automatically.

The theme stylesheet pairs canonical and historical selectors with `:is()`, for example:

```css
:is(.dp-header,.dp-caards-header) { ... }
```

This preserves the visual result of saved template parts while letting all new/theme-owned markup use the canonical namespace.

The browser interaction controller also recognizes the small set of historical classes required to operate previously saved header/search/fullscreen/singular markup.

## Compatibility-only output

The historical `dp-caards-shell` body class remains through `inc/compatibility.php` so site-specific CSS written against the old shell does not fail immediately. Active theme code emits `dp-shell`.

The historical dark/menu state classes remain as temporary runtime aliases because site-specific CSS may depend on them. Canonical state classes are `dp-theme-dark` and `dp-menu-open`.

## Migration policy

- No automatic database rewrite.
- No block serialization rewrite.
- New filesystem markup uses `dp-*`.
- Dynamic renderers use `dp-*`.
- CSS continues to support saved `dp-caards-*` markup during the 1.x compatibility window.
- The hidden `digipublish/caards-home` pattern remains a compatibility fixture; `digipublish/editorial-home` is canonical.


## CI enforcement

The release workflow rejects any `dp-caards-*` class emitted by active filesystem templates, template parts, the canonical homepage pattern or dynamic block renderers. Historical class names are permitted only in compatibility CSS/JS, `inc/compatibility.php` and the hidden legacy homepage pattern.
