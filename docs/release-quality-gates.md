# Release Quality Gates

DigiPublish keeps production runtime dependency-free while using development-only tooling to validate releases.

## WordPress integration environment

`.wp-env.json` provisions WordPress 7.1 on PHP 8.2 with the local DigiPublish theme and DigiPublish Core plugin mounted directly from the repository.

The deterministic seeder in `tests/e2e/seed.mjs` activates both packages, configures pretty permalinks and publishes representative editorial content.

## Integration assertions

`tests/integration/assert-wordpress.sh` executes inside the real WordPress runtime and verifies:

- canonical theme/Core APIs are loaded;
- representative DigiPublish blocks are registered;
- legacy stored post-meta remains readable through the compatibility layer;
- canonical stored-data writes work;
- canonical query normalization works;
- a server-rendered Posts block produces output without fatal errors;
- canonical header/footer template parts resolve.

## Browser gates

Playwright runs desktop and mobile Chromium projects against the disposable WordPress site.

Coverage includes:

- homepage smoke rendering;
- article rendering;
- search overlay state;
- mobile navigation state;
- Site Editor load;
- post editor load;
- serious/critical accessibility violations;
- frontend script/CSS transfer budgets;
- frontend request count budgets;
- DOMContentLoaded budget;
- zero unexpected third-party frontend network requests.

## Performance budgets

The initial budgets are intentionally conservative enough to establish a stable baseline:

- JavaScript transfer < 550 KB;
- CSS transfer < 650 KB;
- fewer than 25 script resources;
- fewer than 30 stylesheet resources;
- DOMContentLoaded < 5 seconds in the CI integration environment;
- no frontend requests to external origins.

Budgets should tighten after the baseline is proven stable.

## Production dependency policy

`package.json` contains development-only tooling. Nothing from `node_modules` is shipped or required by the WordPress theme/plugin at runtime.
