# DigiPublish

DigiPublish is a Gutenberg-first publishing framework for WordPress, built as a block theme plus a companion editorial plugin.

## Repository layout

- `digipublish/` — DigiPublish block theme
- `digipublish-core/` — DigiPublish Core companion plugin

## Current version

**0.11.2**

## Capabilities

- Reusable Gutenberg editorial layouts and patterns
- Featured stories and configurable editorial feed engine
- Category, tag, taxonomy, author, search, date, and archive experiences
- Dictionary content type and term index
- Article bylines with optional **Fact Checked by**, **Verified by**, or **Reported by** attribution
- Related stories, author cards, article TOC, ad slots, and popular categories
- Mobile-first swipe layouts and publication navigation
- Performance-oriented per-block assets, query reuse, responsive images, and locally managed fonts

## Backward compatibility

DigiPublish was initially developed under the internal TechPress name. Version 0.10.1 uses the canonical `digipublish/*` Gutenberg namespace throughout the theme and plugin. Existing saved content from earlier releases is automatically migrated on upgrade.

## Installation

1. Install and activate `digipublish-core/`.
2. Install and activate `digipublish/`.
3. If a Site Editor template was previously customized, reset only that template when you want the filesystem version to take over.
4. Purge page, object, Varnish, and CDN caches after an upgrade.

## Validation

GitHub Actions validates PHP syntax, JSON syntax, JavaScript syntax, and prevents the obsolete `theme:"techpress-theme"` template-part reference from returning.


## Homepage module inventory

The default homepage includes Latest Features, Tech Dictionary, Business / Markets, Top Weekly, Science / Space, Travels, Wearables, Latest Posts, Technology, Popular Categories, Newsletter, header navigation, and footer.

The seven major Editorial Feed presets are exposed as Gutenberg block variations so editors can insert them directly while they continue to share one optimized `digipublish/editorial-feed` implementation.


## Post sidebar modules

DigiPublish 0.9.0 adds a reusable **Post Sidebar Feed** block with three layouts:

- Recent Stories — author/date/title list
- Top Stories — numbered ranking list
- Visual Stories — featured-image mosaic

The default single-post sidebar includes all three as a reusable theme pattern. Editors can remove, reorder or configure them independently.


## Photo galleries

DigiPublish 0.10.0 adds a first-class `Photo Gallery` content type.

- Archive: `/photo-gallery/`
- Category archive: `/photo-gallery/category/<category-slug>/`
- Gallery single: `/photo-gallery/<category-slug>/<gallery-slug>/`
- Editor blocks: `digipublish/gallery` + nested `digipublish/gallery-slide`
- Presentation modes: Story, Swipe and Grid
- Optional counters, captions, credits, thumbnails, fullscreen, sharing and Story-mode ad intervals
- Archive/related-gallery cards expose photo counts
- Sidebar feeds can source Articles, Galleries or Mixed content

New galleries start with the Gallery block already inserted. Editors can bulk-select images, reorder generated slides, and edit each slide's heading, caption, credit and alt text.

All slides are server-rendered under one canonical URL. JavaScript only enhances swipe/counter/fullscreen/share behavior.


## Read Next

DigiPublish 0.10.1 adds a Read Next mode to `digipublish/related-posts`.

The default article template shows four cards below the article. Candidates are ranked using all shared categories and tags, then recent posts fill any remaining positions without repeating the current article. Editors can switch between Read Next and the previous Related Features layout.


## Dependency-free runtime

DigiPublish preserves Caards-inspired publishing capabilities without requiring the original runtime frameworks. Production code does not require Canvas, Powerkit, Flickity, Colcade, Magnific Popup, Classic Widgets, or remote Google Fonts.

Interactive layouts use browser-native APIs and first-party DigiPublish controllers. Masonry feed insertions use synced Gutenberg patterns, and typography defaults to resilient system stacks with optional locally hosted fonts managed through WordPress Font Library.


## Architecture direction

DigiPublish is progressively separating presentation, editorial querying and editor controls without changing saved block content. Query-heavy blocks now consume the canonical service in `digipublish-core/includes/query.php`, while reusable editor query controls live in `digipublish-core/assets/editor-query.js`.

Archive and related-content ranking remain specialist layers where their semantics differ from a normal editorial feed. See `docs/editorial-query-architecture.md` for the ownership rules.


## Block registration

DigiPublish registers its custom blocks through WordPress' native block metadata collection API. `digipublish-core/blocks-manifest.php` is the registration source of truth and CI verifies that every manifest entry exactly matches its corresponding `block.json`.

This removes the previous hand-maintained 32-block PHP registration list and lets WordPress serve block metadata from opcode-cache-friendly PHP rather than repeatedly decoding individual JSON files.


## Core-first layout composition

New structural layouts use WordPress Core Group, Columns, Column and Heading blocks with DigiPublish styles and patterns. The old DigiPublish Section block family remains registered only for saved-content compatibility and is hidden from the inserter.

See `docs/core-layout-architecture.md`.


## Native responsive visibility

DigiPublish now uses WordPress Core block visibility for new responsive visibility settings. Historical desktop/laptop/tablet/mobile hide attributes remain only as a saved-content compatibility layer and appear in the editor only when a block is already using them.

See `docs/native-visibility-transition.md`.


## Design system

DigiPublish uses `theme.json` and Global Styles as the primary visual configuration layer. Canonical theme CSS consumes `--dp-*` design tokens; historical `--dp-caards-*` variables remain aliases only for saved custom CSS compatibility.

Visual configuration such as colors, typography, templates, headers and footers belongs in the Site Editor. The active palette exposes only canonical DigiPublish colors; historical color slugs remain CSS compatibility aliases instead of duplicate editor choices. The Appearance → DigiPublish Publishing page is limited to non-visual publishing behavior. Native `DigiPublish Dark`, `DigiPublish Editorial`, and `DigiPublish High Contrast` Global Style variations are available under `digipublish/styles/`.

The default front page and posts home now use the canonical `digipublish/editorial-home` pattern. The former `digipublish/caards-home` slug remains hidden from the inserter for saved-content compatibility.


## 1.0 modernization status

Caards capability parity and DigiPublish architecture modernization are tracked separately. The dependency-free Gutenberg/Site Editor foundation is in place, while namespace/data compatibility, editor modularization, Interactivity API adoption and full automated release testing remain active 1.0 work.

See `docs/modernization-status.md`.


## Publishing configuration

Only non-visual publishing behavior remains under Appearance → DigiPublish Publishing. Header/footer composition, templates, colors, typography, spacing and style presets are owned by the Site Editor and Global Styles. Legacy visual option keys are isolated in the compatibility layer and are not active design settings.


## Runtime class namespace

Active DigiPublish templates, dynamic presentation blocks and browser-created UI now use the canonical `dp-*` class namespace. Historical `dp-caards-*` selectors remain only as saved-content/runtime compatibility aliases; no database rewrite is performed.

See `docs/runtime-class-namespace.md`.


## Stored data namespace

Author profile, editorial attribution and editorial metric data now use canonical DigiPublish meta keys with dual-read/write compatibility for historical TechPress records. Dictionary CPT/taxonomy identifiers remain intentionally frozen until a dedicated database/rewrite migration exists.

See `docs/stored-data-namespace.md`.
