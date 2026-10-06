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

Existing preset slugs are retained because saved blocks and user Global Styles can reference those identifiers. Their user-facing labels are normalized to DigiPublish concepts such as Canvas, Surface, Text, Text Muted, Brand and Border.

This means visual naming can evolve without invalidating saved preset classes or CSS variables.

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

Historical header/footer/default-layout option keys are still read for compatibility with existing installations but are no longer presented as the primary visual configuration UI.

## Dark design

`styles/dark.json` provides a native **DigiPublish Dark** Global Style variation for site-wide editorial use.

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
