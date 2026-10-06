(function (wp) {
  'use strict';

  if (!wp || !wp.hooks) return;

  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'digipublish/native-visibility-support',
    function (settings, name) {
      if (!name || name.indexOf('digipublish/') !== 0) {
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
