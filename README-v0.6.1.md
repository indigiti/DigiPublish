# TechPress Gutenberg Framework v0.6.1 — Mobile UX

This release is a mobile-focused refinement of v0.6.0. Desktop layouts and Gutenberg reusability are preserved.

## Mobile changes

- Real mobile publication navigation below 680px using a native `<details>` hamburger menu; no additional JavaScript bundle.
- Search and menu tap targets increased to 44×44px.
- Article H1 reduced to 29px on phones (27px on very narrow screens).
- Article byline, avatar, attribution, updated date, and share control tightened for narrow screens.
- `Why Trust Us` and a collapsible Table of Contents are moved directly below the article hero on tablet/mobile.
- The desktop article sidebar is suppressed below 1120px so TOC/trust content no longer appears after the article.
- TOC script now populates every rendered TOC instance safely.
- Latest Features secondary stories use a native horizontal scroll-snap rail on phones.
- Business/Markets editorial card grids become swipe rails on phones.
- Top Weekly becomes a swipe-first story rail instead of a very long vertical stack.
- Featured Trio/Travel layouts become swipe rails on phones.
- Compact topic grids (Wearables/Technology style) use three compact rows per horizontal page.
- Latest Posts switches to compact image-right mobile list cards.
- Existing overlay carousel uses larger 44px navigation controls on touch screens.
- Dictionary trending terms remain a compact two-column list; popular definition cards become a swipe rail.
- Popular Categories becomes a horizontal icon rail instead of a tall 2×4 block.
- Category Experts becomes a horizontal people rail.
- Related Features becomes a horizontal story rail.
- Category/Tag Top Picks becomes a horizontal rail.
- Archive featured-topic pills become horizontally scrollable.
- Author portrait reduced to 138px / 124px on phones.
- Footer remains two columns on most phones to reduce vertical length, falling to one column only on very narrow screens.

## Upgrade

1. Replace/upgrade **TechPress Editorial Blocks** with `techpress-editorial-v0.6.1.zip`.
2. Replace/upgrade **TechPress Editorial theme** with `techpress-theme-v0.6.1.zip`.
3. Purge WordPress, Cloudways/Varnish, Redis/object, and CDN caches as applicable.
4. If the Single template was previously customized in the Site Editor, reset **Appearance → Editor → Design → Templates → Single** so the new mobile article tools are loaded.

No homepage template reset is required solely for these mobile CSS improvements unless an older database customization overrides the current theme files.

## Performance notes

The mobile menu uses native HTML disclosure behavior. Swipe sections use CSS overflow and scroll snapping. No slider library or new global JavaScript dependency was added.
