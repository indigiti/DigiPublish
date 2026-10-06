# DigiPublish 1.0 Modernization Status

This document distinguishes **Caards feature parity** from the separate **DigiPublish 1.0 architecture modernization** target.

## Completed foundations

| Area | Status | Current state |
| --- | --- | --- |
| Block theme / Site Editor | Complete | `theme.json` v3, HTML templates, template parts, patterns and Global Styles are the primary presentation system. |
| Runtime dependency removal | Complete | No Canvas, Powerkit, Flickity, Colcade, Magnific Popup, Classic Widgets, jQuery theme runtime or remote Google Fonts dependency. |
| Classic Widget replacement | Complete | Masonry inserted content uses synced Gutenberg patterns. |
| Query architecture | Complete foundation | Posts, Featured, Editorial Feed, Sidebar Feed and REST pagination share canonical `digipublish_core_query_*` services. |
| Block registration | Complete | All block metadata is registered through WordPress' metadata collection API and guarded against stale manifests. |
| Structural layout modernization | Complete for new content | New layouts use Core Group/Columns/Heading. Four historical Section blocks remain registered and non-insertable for saved-content compatibility. |
| Visibility modernization | Complete for new visibility rules | New decisions use WordPress block visibility. Historical viewport flags remain only for saved-content compatibility. |
| Design-system normalization | Complete active layer | Active theme/block styling consumes canonical `--dp-*` tokens. Compatibility color/preset aliases remain for old content. |
| Header/footer visual ownership | Complete primary path | Site Editor template parts/patterns own visual composition; historical variant options are fallback-only. |
| Homepage namespace | Complete primary path | New templates use `digipublish/editorial-home`; the old Caards pattern slug remains hidden for saved references. |
| Version consistency | Complete | Theme, Core and README are aligned at 0.11.1. |

## Partially complete

| Area | Why it is not finished |
| --- | --- |
| Block consolidation | 32 block types are still registered. Four structural blocks are compatibility-only, gallery-slide is a child block, and several query/presentation blocks still exist as separate public block types even though internals are increasingly shared. |
| Editor modularization | Query, native block support and shared design controls are now extracted; `digipublish-core/assets/editor.js` is ~126 KB. Further block-scoped editor modules/build outputs are still required before 1.0 architecture completion. |
| Responsive architecture | Core visibility is native, but many blocks still store legacy `hideDesktop/hideLaptop/hideTablet/hideMobile` attributes and custom desktop/laptop/tablet/mobile column/gap settings. |
| Legacy namespace cleanup | Active runtime classes are canonical `dp-*` and CI now forbids `dp-caards-*` output from active templates, parts, canonical patterns and block renderers. Historical selectors remain only in compatibility CSS/JS, the hidden legacy homepage pattern and compatibility PHP. Callable APIs and author/attribution/metric meta are canonical with compatibility aliases. Dictionary CPT/taxonomy IDs remain intentionally deferred. |

## Not yet complete

| Area | Required work |
| --- | --- |
| WordPress Interactivity API | Current interactions are first-party/browser-native but not yet implemented through the WordPress Interactivity API. |
| Real automated test harness | CI has extensive syntax/static regression guards, but there is no PHPUnit/WP integration, Playwright browser/editor, accessibility, WPCS/PHPCS, ESLint/Stylelint or visual-regression harness yet. |
| Accessibility certification | Existing code includes accessibility considerations, but there is no automated/manual release gate proving WCAG behavior across editor/frontend interactions. |
| Core Web Vitals/performance budget | Architecture is performance-oriented, but there is no repeatable Lighthouse/CWV release budget in CI. |
| Dictionary identifier migration | `tech_term` and `tech_topic` remain stored database identifiers and need a dedicated post-type/taxonomy/rewrite migration with rollback tests. |
| Full compatibility retirement | Historical meta keys, `dp-caards-*` selector aliases, callable aliases and option fallbacks remain supported during the 1.x compatibility window even though active code uses canonical identifiers. |

## Release rule

DigiPublish should not be labelled **1.0 architecture complete** until:

1. production callable code is canonical DigiPublish namespace with legacy aliases isolated;
2. new content no longer depends on legacy block/layout/visibility identifiers;
3. the editor bundle is modularized further;
4. interaction modules have one documented architecture;
5. PHP/browser/accessibility/performance tests are release gates;
6. stored-data and CSS-class migrations have an explicit compatibility policy.

Caards parity can remain documented as complete for supported capabilities while these modernization tasks continue.
