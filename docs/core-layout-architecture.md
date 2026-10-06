# Core Layout Architecture

DigiPublish uses WordPress Core layout blocks for new structural composition.

## Native building blocks

- `core/group` — full-width and constrained editorial sections.
- `core/columns` — content/sidebar editorial sections.
- `core/column` — content and sidebar containers.
- `core/heading` — section headings with DigiPublish block styles.

Theme-owned patterns provide ready-to-insert compositions while the Site Editor retains native controls for spacing, colors, borders, typography and visibility.

## DigiPublish styles

- Columns: `digipublish-editorial-section`
- Heading: `digipublish-accent`
- Heading: `digipublish-accent-box`

The styles are registered with `register_block_style()`. Their CSS is attached through `wp_enqueue_block_style()` so it participates in WordPress block-style loading rather than becoming another global framework.

## Legacy compatibility

The following blocks are legacy compatibility blocks:

- `digipublish/section`
- `digipublish/section-content`
- `digipublish/section-sidebar`
- `digipublish/section-heading`

They remain registered because removing them would make saved content invalid. Their metadata and editor registrations set `supports.inserter=false`, which prevents new usage while preserving editing/rendering of existing content.

No automatic database rewrite is performed. This avoids destructive migrations and lets existing sites transition naturally as pages/templates are edited or rebuilt.

## Migration policy

1. Existing content renders unchanged.
2. New content uses Core patterns/styles.
3. When an existing legacy section is intentionally redesigned, replace it with the corresponding Core pattern.
4. A future explicit migration utility may transform simple legacy sections after serialization tests exist; it should never run automatically on upgrade.
