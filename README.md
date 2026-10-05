# DigiPublish

DigiPublish is a Gutenberg-first publishing framework for WordPress, built as a block theme plus a companion editorial plugin.

## Repository layout

- `digipublish/` — DigiPublish block theme
- `digipublish-core/` — DigiPublish Core companion plugin

## Current version

**0.10.0**

## Capabilities

- Reusable Gutenberg editorial layouts and patterns
- Featured stories and configurable editorial feed engine
- Category, tag, taxonomy, author, search, date, and archive experiences
- Dictionary content type and term index
- Article bylines with optional **Fact Checked by**, **Verified by**, or **Reported by** attribution
- Related stories, author cards, article TOC, ad slots, and popular categories
- Mobile-first swipe layouts and publication navigation
- Performance-oriented per-block assets, query reuse, responsive images, and optional Google Fonts

## Backward compatibility

DigiPublish was initially developed under the internal TechPress name. Version 0.10.0 uses the canonical `digipublish/*` Gutenberg namespace throughout the theme and plugin. Existing saved content from earlier releases is automatically migrated on upgrade.

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
