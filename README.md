# DigiPublish

DigiPublish is a Gutenberg-first publishing framework for WordPress, built as a block theme plus a companion editorial plugin.

## Repository layout

- `digipublish/` — DigiPublish block theme
- `digipublish-core/` — DigiPublish Core companion plugin

## Current version

**0.7.0**

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

DigiPublish was initially developed under the internal TechPress name. Version 0.7.0 intentionally retains the legacy `techpress/*` Gutenberg block namespace and selected `techpress_*` / `TECHPRESS_*` identifiers so existing saved Gutenberg content, metadata, settings, custom CSS, and integrations keep working.

The public product identity is now **DigiPublish**. New integrations can use the canonical `DIGIPUBLISH_CORE_*` constants while the legacy aliases remain available.

## Installation

1. Install and activate `digipublish-core/`.
2. Install and activate `digipublish/`.
3. If a Site Editor template was previously customized, reset only that template when you want the filesystem version to take over.
4. Purge page, object, Varnish, and CDN caches after an upgrade.

## Validation

GitHub Actions validates PHP syntax, JSON syntax, JavaScript syntax, and prevents the obsolete `theme:"techpress-theme"` template-part reference from returning.
