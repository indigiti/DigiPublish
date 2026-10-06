(function (wp) {
  'use strict';

  if (!wp || !wp.hooks) return;

  const legacyStructuralBlocks = new Set([
    'digipublish/section',
    'digipublish/section-content',
    'digipublish/section-sidebar',
    'digipublish/section-heading'
  ]);

  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'digipublish/native-visibility-support',
    function (settings, name) {
      if (!name || name.indexOf('digipublish/') !== 0 || legacyStructuralBlocks.has(name)) {
        return settings;
      }

      const attributes = settings.attributes || {};
      const hasLegacyVisibilityAttributes = [
        'hideDesktop',
        'hideLaptop',
        'hideTablet',
        'hideMobile'
      ].some(function (key) {
        return Object.prototype.hasOwnProperty.call(attributes, key);
      });

      if (!hasLegacyVisibilityAttributes) {
        return settings;
      }

      return Object.assign({}, settings, {
        supports: Object.assign({}, settings.supports || {}, {
          visibility: true
        })
      });
    }
  );
})(window.wp);
