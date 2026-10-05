# Changelog

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
