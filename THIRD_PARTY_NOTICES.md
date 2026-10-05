# Third-Party Notices

## Caards WordPress Theme

Portions of the DigiPublish Posts block layout structure, control model, and pagination/carousel behavior are adapted from **Caards 1.0.4** by **Code Supply Co.**

- Original theme: Caards
- Original author: Code Supply Co.
- Original license: GNU General Public License version 3.0 (GPL-3.0)
- Original theme URI: http://codesupply.co/themes/caards

The adapted DigiPublish implementation is namespaced, removes dependencies on the Canvas page builder, Powerkit, Flickity, and Caards-specific Customizer/runtime helpers, and uses DigiPublish's own Gutenberg block, query, metadata, and frontend systems.

Files containing directly adapted implementation include:

- `digipublish-core/blocks/post-feed/render.php`
- `digipublish-core/blocks/post-feed/style.css`
- `digipublish-core/blocks/post-feed/view.js`
- relevant Posts helpers in `digipublish-core/techpress-editorial.php`

DigiPublish Core is distributed under GPL-3.0-or-later for compatibility with these adapted GPL-3.0 portions.
