# Stored Data Namespace

DigiPublish 1.x uses canonical stored-data identifiers while preserving historical TechPress data without a destructive bulk migration.

## Canonical keys

### Post meta
- `_digipublish_attribution_type`
- `_digipublish_attribution_user`
- `_digipublish_views`
- `_digipublish_shares`

### User meta
- `digipublish_role`
- `digipublish_linkedin`
- `digipublish_x`
- `digipublish_instagram`
- `digipublish_youtube`

## Compatibility policy

`digipublish-core/includes/data-compatibility.php` owns the historical mapping.

Reads use the canonical value when it exists and fall back to the historical key only when the canonical key has never been stored.

Writes are mirrored in both directions during the 1.x compatibility window. This means:

- new DigiPublish code writes canonical keys;
- legacy integrations writing historical keys still update canonical data;
- old integrations reading historical keys continue to see changes made by DigiPublish;
- deleting either mapped key removes its counterpart.

The block editor writes canonical attribution fields and reads the historical attribution fields only as an upgrade fallback.

## Metrics filters

Canonical filters are:

- `digipublish_post_views`
- `digipublish_post_shares`
- `digipublish_post_metric`

Historical `techpress_post_*` filters are still invoked after the canonical filter so existing analytics integrations remain functional.

## Cache group

New request/object-cache traffic uses the `digipublish_core` cache group. Historical cache entries under `techpress_editorial` are not migrated because they are ephemeral and can expire naturally.

## Deferred identifiers

The dictionary identifiers `tech_term` and `tech_topic` are intentionally unchanged.

Unlike meta keys, a post-type/taxonomy rename affects stored `post_type` / taxonomy rows, rewrite rules, REST routes, template resolution and integrations. They require a dedicated migration with rollback and URL/template compatibility tests. They must not be renamed casually as part of namespace cleanup.
