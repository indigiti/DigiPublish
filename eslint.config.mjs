export default [
  {
    files: ['digipublish/assets/js/**/*.js', 'digipublish-core/assets/**/*.js', 'tests/e2e/**/*.{js,mjs}'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        navigator: 'readonly',
        IntersectionObserver: 'readonly',
        ResizeObserver: 'readonly',
        requestAnimationFrame: 'readonly',
        fetch: 'readonly',
        URL: 'readonly',
        console: 'readonly',
        process: 'readonly'
      }
    },
    rules: {
      'no-undef': 'error',
      'no-unreachable': 'error',
      'no-dupe-keys': 'error'
    }
  }
];
