(function (wp, root) {
  'use strict';
  if (!wp || !wp.element || !wp.components || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const { InspectorControls } = wp.blockEditor;
  const { PanelBody, RangeControl, SelectControl, ToggleControl, TextControl, Notice } = wp.components;
  const { __ } = wp.i18n;

  function hasAttribute(attributes, key) {
    return Object.prototype.hasOwnProperty.call(attributes || {}, key);
  }

  function setAttribute(set, key, value) {
    const next = {};
    next[key] = value;
    set(next);
  }

  function legacyVisibilityInUse(a) {
    return !!(a && (a.hideDesktop || a.hideLaptop || a.hideTablet || a.hideMobile));
  }

  function sharedDesignControls(props, config) {
    const a = props.attributes;
    const set = props.setAttributes;
    const panels = [];
    const layoutChildren = [];

    if (config.columns && hasAttribute(a, 'columnsDesktop')) {
      layoutChildren.push(
        el(RangeControl, {
          label: __('Columns — desktop', 'digipublish-core'),
          value: a.columnsDesktop || config.defaultColumns || 3,
          min: 1, max: config.maxColumns || 6,
          onChange: function (v) { set({ columnsDesktop: v || 1 }); }
        }),
        el(RangeControl, {
          label: __('Columns — tablet', 'digipublish-core'),
          value: a.columnsTablet || Math.min(2, a.columnsDesktop || config.defaultColumns || 3),
          min: 1, max: config.maxColumns || 6,
          onChange: function (v) { set({ columnsTablet: v || 1 }); }
        }),
        el(RangeControl, {
          label: __('Columns — mobile', 'digipublish-core'),
          value: a.columnsMobile || 1,
          min: 1, max: Math.min(3, config.maxColumns || 6),
          onChange: function (v) { set({ columnsMobile: v || 1 }); }
        })
      );
    }
    if (hasAttribute(a, 'columnGap')) {
      layoutChildren.push(el(TextControl, {
        label: __('Gap between columns', 'digipublish-core'),
        help: __('CSS length, e.g. 24px, 1.5rem.', 'digipublish-core'),
        value: a.columnGap || '',
        placeholder: config.defaultColumnGap || '16px',
        onChange: function (v) { set({ columnGap: v }); }
      }));
    }
    if (hasAttribute(a, 'rowGap')) {
      layoutChildren.push(el(TextControl, {
        label: __('Gap between rows', 'digipublish-core'),
        value: a.rowGap || '',
        placeholder: config.defaultRowGap || '16px',
        onChange: function (v) { set({ rowGap: v }); }
      }));
    }
    if (hasAttribute(a, 'cardRadius')) {
      layoutChildren.push(el(TextControl, {
        label: __('Card border radius', 'digipublish-core'),
        value: a.cardRadius || '',
        placeholder: config.defaultRadius || '4px',
        onChange: function (v) { set({ cardRadius: v }); }
      }));
    }
    if (hasAttribute(a, 'cardMinHeight')) {
      layoutChildren.push(el(TextControl, {
        label: __('Card minimum height', 'digipublish-core'),
        value: a.cardMinHeight || '',
        placeholder: __('Auto', 'digipublish-core'),
        onChange: function (v) { set({ cardMinHeight: v }); }
      }));
    }
    if (hasAttribute(a, 'paginationType')) {
      layoutChildren.unshift(el(SelectControl, {
        label: __('Pagination type', 'digipublish-core'),
        value: a.paginationType || 'none',
        options: [
          { label: __('None', 'digipublish-core'), value: 'none' },
          { label: __('Page numbers', 'digipublish-core'), value: 'numbers' }
        ],
        onChange: function (v) { set({ paginationType: v }); }
      }));
    }
    if (layoutChildren.length) {
      panels.push(el(PanelBody, { title: __('Layout', 'digipublish-core'), initialOpen: true }, layoutChildren));
    }

    const metaLabels = {
      showCategory: __('Category', 'digipublish-core'),
      showAuthor: __('Author', 'digipublish-core'),
      showDate: __('Date', 'digipublish-core'),
      showComments: __('Comments', 'digipublish-core'),
      showViews: __('Views', 'digipublish-core'),
      showReadTime: __('Reading time', 'digipublish-core'),
      showShares: __('Shares', 'digipublish-core'),
      showExcerpt: __('Display post excerpt', 'digipublish-core'),
      showReadMore: __('Display read more button', 'digipublish-core'),
      showImage: __('Display thumbnail', 'digipublish-core')
    };
    const metaKeys = config.metaKeys || Object.keys(metaLabels);
    const metaChildren = [];
    metaKeys.forEach(function (key) {
      if (!hasAttribute(a, key)) return;
      metaChildren.push(el(ToggleControl, {
        label: metaLabels[key] || key,
        checked: !!a[key],
        onChange: function (v) { setAttribute(set, key, v); }
      }));
    });
    if (hasAttribute(a, 'readMoreLabel') && a.showReadMore) {
      metaChildren.push(el(TextControl, {
        label: __('Read more label', 'digipublish-core'),
        value: a.readMoreLabel || __('Read more', 'digipublish-core'),
        onChange: function (v) { set({ readMoreLabel: v }); }
      }));
    }
    if (metaChildren.length && config.meta !== false) {
      panels.push(el(PanelBody, { title: __('Meta Settings', 'digipublish-core'), initialOpen: false }, metaChildren));
    }

    const typographyChildren = [];
    if (hasAttribute(a, 'headingFontSize')) {
      typographyChildren.push(el(TextControl, {
        label: __('Heading font size', 'digipublish-core'),
        help: __('CSS length, e.g. 1rem, 22px.', 'digipublish-core'),
        value: a.headingFontSize || '',
        placeholder: config.defaultHeadingSize || '',
        onChange: function (v) { set({ headingFontSize: v }); }
      }));
    }
    if (hasAttribute(a, 'headingTag')) {
      typographyChildren.push(el(SelectControl, {
        label: __('Heading tag', 'digipublish-core'),
        value: a.headingTag || 'h2',
        options: ['h2','h3','h4','h5','h6'].map(function (tag) { return { label: tag.toUpperCase(), value: tag }; }),
        onChange: function (v) { set({ headingTag: v }); }
      }));
    }
    if (typographyChildren.length) {
      panels.push(el(PanelBody, { title: __('Typography Settings', 'digipublish-core'), initialOpen: false }, typographyChildren));
    }

    const thumbnailChildren = [];
    if (hasAttribute(a, 'imageSize')) {
      thumbnailChildren.push(el(SelectControl, {
        label: __('Image size', 'digipublish-core'),
        value: a.imageSize || '',
        options: [
          { label: __('Automatic / layout default', 'digipublish-core'), value: '' },
          { label: __('Thumbnail', 'digipublish-core'), value: 'thumbnail' },
          { label: __('Medium', 'digipublish-core'), value: 'medium' },
          { label: __('Medium Large', 'digipublish-core'), value: 'medium_large' },
          { label: __('Large', 'digipublish-core'), value: 'large' },
          { label: __('Full', 'digipublish-core'), value: 'full' }
        ],
        onChange: function (v) { set({ imageSize: v }); }
      }));
    }
    if (hasAttribute(a, 'imageAspect')) {
      thumbnailChildren.push(el(SelectControl, {
        label: __('Image aspect ratio', 'digipublish-core'),
        value: a.imageAspect || '',
        options: [
          { label: __('Automatic / layout default', 'digipublish-core'), value: '' },
          { label: '16:9', value: '16/9' },
          { label: '4:3', value: '4/3' },
          { label: '3:2', value: '3/2' },
          { label: '1:1', value: '1/1' }
        ],
        onChange: function (v) { set({ imageAspect: v }); }
      }));
    }
    if (thumbnailChildren.length) {
      panels.push(el(PanelBody, { title: __('Thumbnail Settings', 'digipublish-core'), initialOpen: false }, thumbnailChildren));
    }

    const responsiveChildren = [];
    [
      ['hideDesktop', __('Hide on desktop', 'digipublish-core')],
      ['hideLaptop', __('Hide on laptop', 'digipublish-core')],
      ['hideTablet', __('Hide on tablet', 'digipublish-core')],
      ['hideMobile', __('Hide on mobile', 'digipublish-core')]
    ].forEach(function (item) {
      if (!hasAttribute(a, item[0])) return;
      responsiveChildren.push(el(ToggleControl, {
        label: item[1],
        checked: !!a[item[0]],
        onChange: function (v) { setAttribute(set, item[0], v); }
      }));
    });
    if (responsiveChildren.length && legacyVisibilityInUse(a)) {
      panels.push(
        el(PanelBody, { title: __('Legacy Visibility', 'digipublish-core'), initialOpen: false },
          el(Notice, { status: 'warning', isDismissible: false }, __('This block still has DigiPublish legacy viewport rules saved. Clear them here, then use WordPress Visibility for future responsive visibility changes.', 'digipublish-core')),
          responsiveChildren
        )
      );
    }

    return panels.length ? el(InspectorControls, {}, panels) : null;
  }

  root.DigiPublishEditorDesign = Object.freeze({
    sharedDesignControls: sharedDesignControls
  });
})(window.wp, window);
