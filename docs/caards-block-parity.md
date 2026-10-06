# Caards Block Parity

This matrix tracks the Caards 1.0.4 block-related extensions that are present in the user-supplied GPL-3.0 source package and their DigiPublish equivalents.

| Caards source surface | DigiPublish implementation | Status |
| --- | --- | --- |
| Canvas Posts layouts: Standard 1–4, Masonry 1, Horizontal 1–5, Tile 1–4, Carousel 1–2 | `digipublish/post-feed` | Complete |
| Posts query/meta/pagination/spacing/border/responsive controls | `digipublish/post-feed` | Complete |
| Posts per-layout margin/alignment/image-width/post-format/video/color controls | `digipublish/post-feed` | Complete |
| Masonry inserted content/repeat | `digipublish/post-feed` + synced Gutenberg patterns (`wp_block`) | Complete — Classic Widgets removed |
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

Powerkit-only social-provider integrations remain optional provider concerns rather than runtime dependencies. The Caards Masonry widget-loop capability is implemented with synced Gutenberg patterns (`wp_block`), so editors can insert any block composition between Masonry cards without registering or rendering a Classic Widget area.

## Compatibility

Existing DigiPublish blocks and legacy saved attributes remain supported. New Caards defaults are applied to newly configured source-parity attributes while legacy aliases such as old Posts grid/list layouts and Category Navigation `limit` continue to render.


## Native dependency-replacement policy

DigiPublish preserves useful Caards capabilities while replacing the original runtime dependencies:

- Caards Customizer → Site Editor, `theme.json`, Global Styles, templates, template parts and block settings.
- Canvas → native Gutenberg blocks, patterns and variations.
- Powerkit → focused DigiPublish publishing modules and native WordPress APIs.
- Flickity → first-party, browser-native carousel behavior (scroll snap/observers) loaded only by blocks that need it.
- Colcade → CSS Grid/masonry behavior plus minimal first-party enhancement where required.
- Magnific Popup → native modal/lightbox behavior, favoring browser and WordPress APIs.
- Classic Widgets → Gutenberg patterns/blocks.
- Remote Google Fonts → dependency-free system stacks; optional locally hosted fonts are managed through WordPress Font Library.

The objective is feature parity without third-party runtime coupling.
