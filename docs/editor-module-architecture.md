# Editor Module Architecture

DigiPublish is progressively decomposing the historical all-in-one editor registration bundle into focused modules without changing saved block markup or attributes.

## Current modules

- `assets/editor-native.js` — WordPress-native support filters such as Core visibility.
- `assets/editor-query.js` — reusable post type, taxonomy, term, post, format and query controls.
- `assets/editor-document.js` — post/page document sidebar panels:
  - Editorial Attribution
  - DigiPublish Layout Options
- `assets/editor.js` — block registrations and block-specific presentation controls that have not yet moved into block-scoped modules.

## Document module boundary

`editor-document.js` is the only DigiPublish editor module that currently uses:

- `wp.plugins`
- `wp.editor`
- `PluginDocumentSettingPanel`

The main `editor.js` bundle no longer depends on those WordPress packages.

The document module uses canonical plugin IDs:

- `digipublish-editorial-attribution`
- `digipublish-layout`

Plugin IDs are editor-runtime identifiers and are not serialized into post content.

## Size budgets

CI currently limits:

- main `editor.js` to less than 130 KB;
- `editor-document.js` to less than 7 KB.

These budgets should be reduced as additional block families move into focused modules.

## Compatibility

The attribution panel writes canonical `_digipublish_*` meta and retains historical TechPress attribution fields only as read fallback during the 1.x stored-data compatibility window.

No block serialization, template markup or frontend renderer behavior is changed by this extraction.
