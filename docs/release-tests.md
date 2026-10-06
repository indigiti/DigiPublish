# Release Test Architecture

DigiPublish uses two CI layers.

## Static validation

`.github/workflows/validate.yml` runs fast syntax, architecture, namespace, Caards parity and regression guards.

## Runtime release tests

`.github/workflows/release-tests.yml` creates a disposable WordPress 7.1 environment with `@wordpress/env` and runs:

- WordPress PHPUnit integration tests against DigiPublish Core.
- Playwright frontend interaction smoke tests.
- Playwright block-editor registration/module smoke tests.
- Axe accessibility analysis; serious and critical violations fail the job.
- Lighthouse budgets for performance, accessibility and best practices.

The environment mounts and activates both `digipublish-core` and the `digipublish` block theme. Deterministic fixture posts/categories are seeded before browser tests.

## Current budgets

- Lighthouse Performance: >= 0.80
- Lighthouse Accessibility: >= 0.90
- Lighthouse Best Practices: >= 0.90
- Axe serious/critical violations: 0
- PHP warnings/risky PHPUnit tests: fail

## Local commands

Requires Docker, Node.js 24+, PHP 8.2+ and Composer.

```bash
npm install
composer install --working-dir=digipublish-core
npx wp-env start
npx wp-env run tests-cli --env-cwd=wp-content/plugins/digipublish-core phpunit -c phpunit.xml.dist
npx wp-env run cli wp theme activate digipublish
npx wp-env run cli --env-cwd=wp-content/plugins/digipublish-core wp eval-file tests/fixtures/seed.php
npx playwright install chromium
npm run test:e2e
npm run test:performance
npx wp-env stop
```

Test-only npm packages are development dependencies. They are not bundled or loaded by the production theme/plugin.
