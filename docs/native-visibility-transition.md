# Native Visibility Transition

DigiPublish uses WordPress Core block visibility for new responsive visibility decisions.

## Native path

Active DigiPublish blocks that historically stored `hideDesktop`, `hideLaptop`, `hideTablet` and `hideMobile` now opt into the WordPress `visibility` block support.

The editor loads a small pre-registration filter in `assets/editor-native.js` so manually registered DigiPublish block types expose the same `supports.visibility=true` capability as their server-side `block.json` metadata.

## Saved-content compatibility

The historical hide attributes remain in block schemas and renderers. Removing them immediately would change existing serialized content and could expose content that publishers intentionally hid.

Editor behavior is transitional:

- New blocks use WordPress Visibility.
- Blocks with all legacy hide flags disabled do not show DigiPublish viewport controls.
- A saved block with one or more legacy hide flags enabled shows a **Legacy Visibility** panel.
- Editors can clear the legacy flags and use WordPress Visibility for future changes.
- Legacy frontend CSS remains until the compatibility attributes can be removed in a future major migration.

This is a non-destructive migration. DigiPublish does not rewrite saved posts or templates automatically.
