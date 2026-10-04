DigiPublish Core 0.8.0

Core Gutenberg publishing features for DigiPublish.

0.8.0 namespace migration
- Every custom Gutenberg block now uses the canonical digipublish/* namespace.
- Editor registrations, server-side block metadata, templates and patterns use the same namespace.
- Existing saved Gutenberg content is migrated automatically on the first admin request after upgrade.
- A render-time fallback preserves frontend output before the one-time migration has completed.
- Pattern and Query Loop variation namespaces are canonical DigiPublish identifiers.
- Theme/plugin performance, editorial attribution, dictionary, feeds, archives, author tools, TOC and ad slots remain included.

Legacy metadata/settings keys that store editorial attribution or configuration are preserved where changing them would risk data loss. They are not Gutenberg block IDs.

Use with DigiPublish theme 0.8.0.
