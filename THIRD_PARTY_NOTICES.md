# Third-Party Notices

## Caards WordPress Theme

Portions of DigiPublish are adapted from **Caards 1.0.4** by **Code Supply Co.**

- Original theme: Caards
- Original author: Code Supply Co.
- Original license: GNU General Public License version 3.0 (GPL-3.0)
- Original theme URI: http://codesupply.co/themes/caards

The DigiPublish adaptation preserves the useful publishing behavior and visual system while rewriting it around WordPress Full Site Editing, DigiPublish namespaces, native dynamic blocks, REST endpoints, and the existing DigiPublish editorial framework.

Adapted areas include:

- Posts block layout/control system, pagination and carousel behavior.
- Header 1–4 and Footer 1–4 structural concepts.
- Standard, Large, Full, Title-only and hidden singular/page header modes.
- Archive, search, author, page, post, homepage and 404 presentation concepts.
- Off-canvas/fullscreen navigation, site search and color-scheme interaction patterns.
- Category Navigation, Current Date and Custom Link utility blocks and their Caards Inspector controls.
- Canvas Section, Section Content, Section Sidebar and Section Heading extensions, including responsive gaps/sidebar widths and Caards color controls.
- Instagram Default/Carousel/Diagonal Carousel and X/Twitter Default/Carousel layouts.
- Powerkit-derived Opt-In Form color controls and Featured Categories "Vertical List Alt" behavior, reimplemented without requiring Powerkit.
- Meet Team presentation and DigiPublish-native mega-menu compatibility.
- Auto Load Next Post behavior, including same-category and reverse-direction options.
- Global spacing, typography, card, surface, border-radius and responsive visual language.

The adaptation intentionally does **not** bundle or copy Caards-specific third-party/runtime dependencies such as Canvas, Powerkit, Flickity, demo importer code, or the Caards Customizer framework. Equivalent behavior is implemented with DigiPublish/WordPress APIs.

Primary adapted implementation locations include:

- `digipublish/theme.json`
- `digipublish/assets/css/site.css`
- `digipublish/assets/js/site-interactivity.js`
- `digipublish/parts/header*.html`
- `digipublish/parts/footer*.html`
- `digipublish/templates/*.html`
- `digipublish/patterns/caards-home.php`
- `digipublish-core/blocks/post-feed/*`
- `digipublish-core/blocks/entry-hero/*`
- `digipublish-core/blocks/current-date/*`
- `digipublish-core/blocks/custom-link/*`
- `digipublish-core/blocks/instagram-carousel/*`
- `digipublish-core/blocks/twitter-carousel/*`
- `digipublish-core/blocks/team-grid/*`
- `digipublish-core/blocks/section*/*`
- `digipublish-core/blocks/opt-in-form/*`
- `digipublish-core/blocks/featured-categories/*`
- relevant helpers and editor controls in `digipublish-core/techpress-editorial.php` and `digipublish-core/assets/editor.js`

The DigiPublish theme and DigiPublish Core are distributed under **GPL-3.0-or-later** for compatibility with these GPL-3.0 adaptations.
