# DigiPublish Design System

DigiPublish uses WordPress `theme.json`, Global Styles, template parts and patterns as the primary design system.

## Canonical CSS tokens

The shared theme shell exposes first-party tokens:

- `--dp-canvas`
- `--dp-surface`
- `--dp-surface-raised`
- `--dp-text`
- `--dp-text-muted`
- `--dp-brand`
- `--dp-brand-hover`
- `--dp-border`
- `--dp-surface-dark`
- `--dp-canvas-dark`
- `--dp-radius-card`
- `--dp-radius-control`
- `--dp-layout-wide`
- `--dp-layout-content`
- `--dp-layout-sidebar`
- `--dp-shadow-card`

Historical `--dp-caards-*` variables remain defined as aliases only. New DigiPublish CSS must not consume them.

## theme.json compatibility

The active Global Styles palette exposes only the canonical DigiPublish choices: Canvas, Surface, Surface Raised, Text, Text Muted, Brand, Brand Hover, Border, Surface Dark and Canvas Dark.

Historical preset slugs (`ink`, `body`, `muted`, `brand`, `brand-dark`, `surface-soft`, `footer`) are no longer shown as duplicate editor choices. Their `--wp--preset--color--*` variables and utility classes remain in compatibility CSS so previously saved blocks continue to render correctly.

## Site Editor ownership

The Site Editor owns:

- colors and palettes;
- typography and locally managed fonts;
- spacing, borders and shadows;
- Global Style variations;
- templates;
- header/footer template parts;
- block styles and patterns.

The Appearance → DigiPublish Publishing page intentionally contains only non-visual behavior such as Auto Load Next Post.

Historical header/footer/default-layout option keys are isolated in `digipublish/inc/compatibility.php`. Active theme code does not read them directly. The compatibility router is attached only when an older installation has a non-default header/footer variant saved.

## Dark design

Native Global Style variations are shipped in `styles/`: **DigiPublish Dark**, **DigiPublish Editorial**, and **DigiPublish High Contrast**. They provide site-wide visual presets without introducing a separate theme-options framework.

The frontend scheme toggle remains a separate per-visitor enhancement. Its canonical storage key is `digipublish-scheme`; the older key and CSS class are read/toggled only for upgrade compatibility.

## Interaction runtime

The first-party browser controller is `assets/js/site-interactions.js`. It owns theme interactions such as:

- dark/light scheme toggle;
- search overlay;
- fullscreen navigation;
- sticky header enhancement;
- Auto Load Next Post.

It has no jQuery or third-party runtime dependency.


## Header and footer ownership

Header and footer variants are exposed as semantic block patterns for `core/template-part/header` and `core/template-part/footer`. This makes all four DigiPublish variants available from the Site Editor replacement flow.

Historical header/footer variant options remain a fallback for older installations. They are bypassed automatically when the canonical `header` or `footer` template part has a custom Site Editor save, so explicit Site Editor edits always take precedence.


## Homepage pattern ownership

The canonical homepage composition is `digipublish/editorial-home`, used by both `front-page.html` and `home.html`.

The historical `digipublish/caards-home` pattern remains registered with `Inserter: false` solely so saved pattern references continue to resolve. New templates and new editor insertion use the DigiPublish-native slug.


## Publishing settings ownership

The Appearance → DigiPublish Publishing screen contains only non-visual behavior. Active option keys are:

- `digipublish_load_next_enabled`
- `digipublish_load_next_same_category`
- `digipublish_load_next_reverse`

Older Caards-era Auto Load Next option names are migrated once to the canonical keys and retained only as historical data. The migration runs on `init` so frontend behavior is preserved before an administrator visits wp-admin.

Visual options are not migrated into replacement theme settings. Existing non-default header/footer choices continue through the compatibility layer until the canonical template part is saved in the Site Editor.


## Runtime class namespace

Active theme/template/block presentation markup uses the canonical `dp-*` class namespace. Historical `dp-caards-*` class selectors remain paired in `assets/css/site.css` only for previously saved Site Editor/template markup. Interaction compatibility for old saved headers and singular layouts is explicit in `site-interactions.js`.

See `docs/runtime-class-namespace.md`.
