# Caards Block Parity

This matrix tracks the Caards 1.0.4 block-related extensions that are present in the user-supplied GPL-3.0 source package and their DigiPublish equivalents.

| Caards source surface | DigiPublish implementation | Status |
| --- | --- | --- |
| Canvas Posts layouts: Standard 1–4, Masonry 1, Horizontal 1–5, Tile 1–4, Carousel 1–2 | `digipublish/post-feed` | Complete |
| Posts query/meta/pagination/spacing/border/responsive controls | `digipublish/post-feed` | Complete |
| Posts per-layout margin/alignment/image-width/post-format/video/color controls | `digipublish/post-feed` | Complete |
| Masonry widget insertion/repeat | `digipublish/post-feed` + `sidebar-archive` widget area | Complete |
| Canvas Section Heading Caards color fields | `digipublish/section-heading` | Complete |
| Canvas Section responsive gap/sidebar width | `digipublish/section` | Complete |
| Canvas Section Content text/background colors | `digipublish/section-content` | Complete |
| Canvas Section Sidebar text/background colors | `digipublish/section-sidebar` | Complete |
| Current Date | `digipublish/current-date` | Complete |
| Category Navigation | `digipublish/category-nav` | Complete |
| Custom Link + Styled variation | `digipublish/custom-link` | Complete |
| Powerkit Instagram Default / Carousel / Diagonal Carousel | `digipublish/instagram-carousel` | Complete |
| Powerkit Twitter Default / Carousel | `digipublish/twitter-carousel` | Complete |
| Powerkit Opt-In Form color extensions | `digipublish/opt-in-form` | Complete |
| Powerkit Featured Categories Vertical List Alt | `digipublish/featured-categories` | Complete |

## Intentional architecture substitutions

Caards 1.0.4 extends the Canvas and Powerkit plugin ecosystems. DigiPublish does not load those plugins internally. The Caards-defined extensions above are implemented natively with Gutenberg, dynamic blocks, WordPress REST APIs, block supports and DigiPublish helpers.

Powerkit-only classic widgets and social-provider integrations remain external integration concerns rather than Gutenberg blocks. The Caards Masonry widget-loop behavior is supported through the registered `sidebar-archive` widget area so existing WordPress widgets can be inserted between Masonry cards.

## Compatibility

Existing DigiPublish blocks and legacy saved attributes remain supported. New Caards defaults are applied to newly configured source-parity attributes while legacy aliases such as old Posts grid/list layouts and Category Navigation `limit` continue to render.
