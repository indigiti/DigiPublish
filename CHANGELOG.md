# Changelog

## 0.11.2

- Completed the first major DigiPublish 1.0 frontend modernization checkpoint.
- Migrated Posts carousel, Load More and Infinite Scroll to the WordPress Interactivity API under one `digipublish/post-feed` runtime.
- Migrated the site shell (search, fullscreen navigation, dark/light scheme, Escape handling and sticky header) to the `digipublish/site` Interactivity store.
- Migrated Auto Load Next Post into the same site Interactivity runtime while preserving the existing REST contract, same-category/reverse-direction settings and browser URL/title updates.
- Removed the retired classic Posts and site interaction controllers.
- Added WordPress 7.1 runtime regression coverage for carousel navigation, AJAX pagination, Auto Load Next multi-article loading and history behavior.
- Retained dependency-free production runtime and saved-content compatibility boundaries.
- Kept PHPUnit, Playwright, Axe and Lighthouse as release gates.
- Bumped the theme, Core plugin and all 32 custom block manifests to 0.11.2.

## 0.11.2

- Re-audited DigiPublish against the actual Caards 1.0.4 source and demo reference.
- Replaced the conflicting legacy DigiPublish visual shell with a Caards-native theme shell and rebuilt the homepage/header composition for closer visual fidelity.
- Completed all Caards-defined block/settings parity across Posts, Section, Section Content, Section Sidebar, Section Heading, Current Date, Category Navigation, Custom Link, Instagram, X/Twitter, Opt-In Form and Featured Categories.
- Completed Posts source defaults and all 16 layout-specific controls, including responsive Desktop/Laptop/Tablet/Mobile columns, gaps, content spacing, card heights and typography.
- Added source-scoped post-format icons, supported video backgrounds and controls, layout-specific colors, Masonry widget insertion/repeat behavior, and fixed Horizontal 5 thumbnail semantics.
- Matched Caards responsive breakpoints and prevented hidden layout settings from leaking into unsupported layouts.
- Changed DigiPublish-only Category Navigation extras (Dictionary, Photo Galleries and Search) to explicit opt-ins so Caards behavior remains the default.
- Added and expanded CI parity/visual-fidelity guards to protect the migration.
- Bumped the theme, Core plugin and all 32 custom block manifests to 0.11.2.


## 0.11.0

- Ported the GPL-3.0 Caards 1.0.4 visual/theme system into DigiPublish while preserving the Gutenberg/FSE architecture.
- Added four selectable Caards-style header layouts with desktop/mobile navigation, search panel, fullscreen menu and dark/light scheme control.
- Added four selectable Caards-style footer layouts.
- Added global Appearance controls for header variant, footer variant, default singular header style and default sidebar position.
- Added Standard, Large Hero, Full Hero, Title Only and No Header post/page presentation modes with per-entry overrides.
- Added Left, Right and Disabled sidebar modes with global defaults and per-entry overrides.
- Added Caards-style page, archive, category, tag, date, search, author, 404 and front-page shells.
- Added Caards Meet Team, Current Date, Custom Link, Instagram Carousel, X/Twitter Carousel and Mega Menu blocks.
- Added a dynamic Entry Hero block with breadcrumbs, category, subtitle, author/date/comments/views/shares/read-time meta and optional video background.
- Added Caards-inspired homepage pattern and visual foundation, including palette, typography, spacing, cards, shell, article and dark-mode styling.
- Added Auto Load Next Post with same-category and reverse-direction options, REST rendering and browser-history updates.
- Preserved DigiPublish Posts, Gallery, Read Next, Ads, Archive, Sidebar and existing editorial systems.
- Added full-theme regression guards covering all Caards-derived template parts, templates, blocks, selectors and shell behavior.
- Documented expanded Caards GPL attribution in THIRD_PARTY_NOTICES.md.
- Bumped theme, Core and all custom blocks to 0.11.0.


## 0.10.2

- Added a shared Gutenberg Inspector framework for configurable DigiPublish editorial blocks.
- Added responsive column controls, column/row gaps, card radius and minimum-height controls where layouts support them.
- Added heading typography controls, configurable heading tags, image size and image aspect-ratio controls.
- Added responsive visibility controls for desktop, laptop, tablet and mobile.
- Added native Gutenberg spacing and border support to configurable editorial blocks.
- Extended Post Feed with category/tag include and exclude filters, selected-post filtering, offset, sort order, duplicate avoidance and numbered pagination.
- Extended Featured Stories with richer query controls while preserving its existing magazine, lead-list and grid layouts.
- Extended Editorial Feed, Related / Read Next, Archive Story Feed, Sidebar Feed and Gallery Archive with block-specific design and metadata controls.
- Preserved existing responsive defaults and mobile swipe/rail behavior unless an editor explicitly changes responsive column settings.
- Added safe frontend CSS-variable sanitization and shared rendering helpers instead of duplicating block-specific settings logic.
- All release changes passed PHP, JSON, JavaScript, homepage, mobile/carousel, sidebar, gallery and Read Next regression checks.
- Bumped theme, Core and all 19 custom blocks to 0.10.2.


## 0.10.1

- Added a new Read Next presentation mode to the existing Related Posts block.
- Default single-post template now shows a four-card Read Next section below the article.
- Related stories are ranked by overlap with all current article categories and tags.
- Category matches receive stronger relevance weight than tag matches; recency breaks ties.
- Falls back to recent non-duplicate posts only when the taxonomy-related pool is too small.
- Read Next cards show headline, publish date, excerpt, responsive image, reading time, and real views/shares when available.
- Added responsive four-column desktop / two-column tablet / horizontal-swipe mobile layouts.
- Added Gutenberg controls for layout, taxonomy relation mode, story count and excerpt visibility.
- Added a direct Read Next block variation while preserving the existing Related Features layout.
- Added CI guards for the Read Next query, template placement, variation and styling.
- Bumped theme, Core and all custom blocks to 0.10.1.


## 0.10.0

- Added first-class Photo Gallery posts with the public `/photo-gallery/` archive.
- Added category-aware gallery permalinks: `/photo-gallery/category-slug/gallery-title/`.
- Added pretty gallery category archives under `/photo-gallery/category/category-slug/`.
- Added nested Gutenberg `digipublish/gallery` and `digipublish/gallery-slide` blocks.
- Added bulk image selection that creates reorderable gallery slides in the editor.
- Added Story, Swipe and Grid gallery display modes without duplicating stored content.
- Added per-slide heading, caption, alt text and photo credit.
- Added counters, thumbnail navigation, keyboard arrows, native fullscreen and Web Share/clipboard sharing.
- Added server-rendered HTML with progressive enhancement so gallery content remains indexable without JavaScript.
- Added responsive image delivery: first photo eager/high-priority, later photos lazy/async.
- Added optional inline gallery ads in Story mode through the existing DigiPublish ad-slot system.
- Added `ImageGallery` structured data while retaining one canonical gallery URL and slide anchors.
- Added gallery photo-count caching and photo-count badges on archive/sidebar cards.
- Added dynamic gallery covers: explicit Featured Image first, otherwise the current first gallery slide.
- Added `digipublish/gallery-archive` for archive/latest/related gallery grids and category filters.
- Added dedicated single and archive block-theme templates.
- Added Related Galleries with sparse-category fallback.
- Added Photo Galleries to desktop/mobile publication navigation.
- Extended editorial attribution/byline support to gallery posts.
- Extended Post Sidebar Feed to Articles, Galleries or Mixed content and added a Latest Galleries variation.
- Added gallery-specific body/template styling and CI guards.
- Bumped theme, plugin and all 19 custom blocks to 0.10.0.


## 0.9.0

- Added the new dynamic `digipublish/sidebar-feed` block for article sidebars.
- Added three sidebar layouts: author/date/title list, numbered Top Stories, and featured-image mosaic.
- Added direct Gutenberg variations for Recent Stories, Top Stories, and Visual Stories.
- Added a reusable `Post Sidebar Widgets` pattern and inserted it into the default article sidebar.
- Default sidebar modules use a clean heading-free dark editorial style inspired by the supplied references.
- Sidebar widgets use current-category/latest/category sources, period and ordering controls, and sparse-content fallback.
- Visual Stories filters for posts with featured images and falls back to latest thumbnail posts when the current source is sparse.
- On tablet/mobile the editorial sidebar modules move below the article while duplicate desktop Trust/TOC components remain hidden.
- Added CI guards for the sidebar block, pattern, variations and default sidebar placement.
- Bumped theme, plugin and all custom blocks to 0.9.0.


## 0.8.4

- Keeps Top Weekly visible when the genuine 7-day pool is sparse.
- Fills missing Top Weekly slots from a cached all-time pool shuffled in PHP, avoiding SQL `ORDER BY RAND()`.
- Prefers unused random posts first, then relaxes cross-section de-duplication only as a last resort while never duplicating a story inside Top Weekly.
- Keeps Popular Categories visible on fresh sites by falling back to existing zero-count categories.
- Keeps Tech Dictionary useful on fresh sites with Explore Topics or a styled empty state instead of silently collapsing.
- Added CI guards for all homepage resilience fallbacks.
- Bumped theme, plugin and all custom blocks to 0.8.4.


## 0.8.3

- Fixed the remaining mobile left-edge clipping caused by the Featured block's `alignwide` behavior inside the homepage constrained group.
- Reworked Latest Features mobile composition to match a clean editorial-card reference.
- Hid the top-right More link on mobile so the section title remains visually dominant.
- Kept category chips in a 44px touch-friendly horizontal swipe rail with no native scrollbar.
- Contained the lead image to the mobile content width with a stable 16:9 ratio.
- Reordered mobile lead-story content to category, headline, excerpt, then author/date.
- Tightened secondary story swipe-card sizing.
- Added CI guards for Featured mobile alignment and safe-area regressions.
- Bumped theme, plugin and all custom blocks to 0.8.3.


## 0.8.2

- Fixed category fallback so sparse homepage categories fill from Latest instead of querying the same sparse category again.
- Preserved cross-section de-duplication during fallback fill.
- Added stable item-count layout classes for sparse feeds.
- Prevented single-card desktop carousels from stretching across the full section.
- Hidden carousel controls when only one story exists.
- Fixed mobile full-width/root-padding clipping and Featured filter scrollbar leakage.
- Added direct Gutenberg variations for all major homepage Editorial Feed presets.
- Added CI checks for block registration, homepage pattern availability, required sections, mobile clipping and carousel geometry regressions.
- Normalized remaining mobile touch targets to 44px where applicable.
- Made sparse-category Latest fill an explicit opt-in used by the homepage category presets.
- Bumped theme, plugin and block metadata to 0.8.2.


## 0.8.1

- Fixed stale generated block CSS selectors after the `digipublish/*` namespace migration.
- Added category-slug feed sources with graceful fallback and real time-window filters.
- Added request-level story de-duplication across homepage editorial sections.
- Split content and popularity cache invalidation so comments do not flush unrelated feeds.
- Added layout-specific responsive image sizing hints.
- Removed category Top Picks from the first Latest grid to avoid duplicate stories.
- Renamed Dictionary ranking labels to match actual recently-updated/featured behavior.
- Limited the legacy block render fallback to sites that have not completed migration.
- Normalized card radius, homepage background rhythm, inserter category and translation domains.
- Tuned `content-visibility` intrinsic sizes to reduce layout-shift risk.
- Added CI guards for stale generated block classes and inserter-category regressions.


## 0.8.0

- Replaced every custom Gutenberg block ID with the canonical `digipublish/*` namespace.
- Updated block metadata, editor registration, Query Loop variation, theme templates, template parts, and reusable patterns.
- Added automatic migration of previously saved Gutenberg block markup.
- Added a render-time compatibility fallback while the one-time migration is pending.
- Updated pattern slugs/categories and block category identifiers.
- Bumped theme, plugin, and custom block metadata to 0.8.0.


## 0.7.0

- Rebranded the public framework from TechPress to **DigiPublish**.
- Theme name is now **DigiPublish**.
- Companion plugin name is now **DigiPublish Core**.
- Fixed template-part references to use the actual `digipublish` theme slug.
- Added canonical `DIGIPUBLISH_CORE_VERSION`, `DIGIPUBLISH_CORE_DIR`, and `DIGIPUBLISH_CORE_URL` constants.
- Added canonical `digipublish-*` body classes and performance support marker while retaining legacy aliases.
- - Updated public documentation and admin-facing labels.
- Added repository validation workflow.

## 0.6.1

- Mobile UX refinement, reusable editorial feeds, responsive article tools, and swipe layouts.
