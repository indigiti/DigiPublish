# Editorial Query Architecture

DigiPublish query-heavy blocks share one server-side query service in `digipublish-core/includes/query.php`.

## Canonical APIs

- `digipublish_core_query_post_args()` — flexible Posts/Featured query contract, including pagination, post type, taxonomy filters, formats, related-mode exclusions and duplicate avoidance.
- `digipublish_core_query_feed_args()` — editorial feed contract for latest/category/current/manual sources, period filters and single or multiple public post types.
- `digipublish_core_query_feed_posts()` — cached request/object-cache feed pool with de-duplication and resilient fill behavior.
- `digipublish_core_query_view_all_url()` — canonical context-aware destination resolver.
- `digipublish_core_rendered_post_ids()` — request-scoped duplicate registry.

Legacy `techpress_editorial_*` query function names remain as thin wrappers in `digipublish-core/includes/compatibility.php` only, so existing integrations are not broken. New block code must use the canonical `digipublish_core_query_*` API.

## Current consumers

- Posts
- Featured Stories
- Editorial Feed
- Sidebar Feed
- Posts REST pagination endpoint

Archive Feed intentionally reuses the WordPress main query when possible. Related Posts keeps its specialist category/tag scoring layer. Gallery Archive keeps gallery-specific cover/category fallback semantics. These can consume more of the shared primitives later without forcing unlike ranking behavior into one generic query.

## Editor query UI

Reusable post type, taxonomy, term, post, format, order and de-duplication controls live in `digipublish-core/assets/editor-query.js` and expose a frozen `window.DigiPublishEditorQuery` API.

The main `assets/editor.js` owns block registration and block-specific presentation controls but must not reimplement shared query controls.

## Compatibility rule

Saved block attributes remain unchanged. The refactor changes ownership of query behavior, not serialized block markup or existing content.

## Architectural budgets

CI currently enforces:

- core bootstrap below 60 KB;
- main editor bundle below 138 KB;
- query-heavy renderers use canonical query functions;
- extracted editor/query modules remain present.

Budgets should decrease as further modules are extracted.
