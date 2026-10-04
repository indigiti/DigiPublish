(function (wp) {
  'use strict';
  if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const Fragment = wp.element.Fragment;
  const { registerBlockType, registerBlockVariation, getBlockType } = wp.blocks;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const { PanelBody, TextControl, RangeControl, SelectControl, ToggleControl, Notice } = wp.components;
  const { __ } = wp.i18n;
  const useSelect = wp.data.useSelect;
  const SSRPackage = wp.serverSideRender;
  const ServerSideRender = SSRPackage && (SSRPackage.ServerSideRender || SSRPackage.default || SSRPackage);

  function Preview(props) {
    const blockProps = useBlockProps();
    if (!ServerSideRender) {
      return el('div', blockProps, el(Notice, { status: 'warning', isDismissible: false }, __('Preview unavailable. The block will render on the front end.', 'techpress-editorial')));
    }
    return el('div', blockProps, el(ServerSideRender, { block: props.name, attributes: props.attributes }));
  }

  function useCategoryOptions() {
    const categories = useSelect(function (select) {
      return select('core').getEntityRecords('taxonomy', 'category', { per_page: 100, orderby: 'name', order: 'asc' });
    }, []);
    const options = [{ label: __('All categories', 'techpress-editorial'), value: 0 }];
    if (Array.isArray(categories)) {
      categories.forEach(function (category) { options.push({ label: category.name, value: category.id }); });
    }
    return options;
  }

  function editorialControls(props, config) {
    const a = props.attributes;
    const set = props.setAttributes;
    const categoryOptions = useCategoryOptions();
    const children = [];

    children.push(el(TextControl, { label: __('Section heading', 'techpress-editorial'), value: a.heading || '', onChange: function (value) { set({ heading: value }); } }));
    if (config.category !== false) {
      children.push(el(SelectControl, { label: __('Content source', 'techpress-editorial'), value: a.categoryId || 0, options: categoryOptions, onChange: function (value) { set({ categoryId: parseInt(value, 10) || 0 }); } }));
    }
    if (Object.prototype.hasOwnProperty.call(a, 'postsToShow')) {
      children.push(el(RangeControl, { label: __('Number of stories', 'techpress-editorial'), value: a.postsToShow, min: config.min || 1, max: config.max || 12, onChange: function (value) { set({ postsToShow: value }); } }));
    }
    if (config.layouts) {
      children.push(el(SelectControl, { label: __('Layout', 'techpress-editorial'), value: a.layout, options: config.layouts, onChange: function (value) { set({ layout: value }); } }));
    }
    if (config.orderBy) {
      children.push(el(SelectControl, {
        label: __('Order by', 'techpress-editorial'), value: a.orderBy,
        options: [
          { label: __('Newest', 'techpress-editorial'), value: 'date' },
          { label: __('Recently updated', 'techpress-editorial'), value: 'modified' },
          { label: __('Most discussed', 'techpress-editorial'), value: 'comment_count' },
          { label: __('Alphabetical', 'techpress-editorial'), value: 'title' }
        ],
        onChange: function (value) { set({ orderBy: value }); }
      }));
    }
    ['showImage', 'showExcerpt', 'showAuthor', 'showDate'].forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(a, key)) return;
      const labels = { showImage: __('Show image', 'techpress-editorial'), showExcerpt: __('Show excerpt', 'techpress-editorial'), showAuthor: __('Show author', 'techpress-editorial'), showDate: __('Show date', 'techpress-editorial') };
      children.push(el(ToggleControl, { label: labels[key], checked: !!a[key], onChange: function (value) { const o = {}; o[key] = value; set(o); } }));
    });
    return el(InspectorControls, {}, el(PanelBody, { title: __('Editorial settings', 'techpress-editorial'), initialOpen: true }, children));
  }

  registerBlockVariation('core/query', {
    name: 'techpress/story-grid', title: __('TechPress Story Grid', 'techpress-editorial'), description: __('Native Query Loop variation with the publication card hierarchy.', 'techpress-editorial'), icon: 'grid-view', scope: ['inserter'],
    isActive: function (attributes) { return attributes.namespace === 'techpress/story-grid'; },
    attributes: { namespace: 'techpress/story-grid', align: 'wide', className: 'tp-query-grid', query: { perPage: 6, pages: 0, offset: 0, postType: 'post', order: 'desc', orderBy: 'date', author: '', search: '', exclude: [], sticky: '', inherit: false }, displayLayout: { type: 'flex', columns: 3 } },
    allowedControls: ['order', 'taxQuery', 'author', 'search'],
    innerBlocks: [['core/post-template', { className: 'tp-query-card' }, [['core/post-featured-image', { isLink: true, aspectRatio: '16/9' }], ['core/post-terms', { term: 'category', className: 'tp-category' }], ['core/post-title', { isLink: true, level: 3, fontSize: 'lg' }], ['core/post-date', { className: 'tp-meta' }]]]]
  });

  registerBlockType('techpress/featured-posts', {
    apiVersion: 3, title: __('Featured Stories', 'techpress-editorial'), category: 'techpress-editorial', icon: 'star-filled',
    attributes: { heading: { type: 'string', default: 'Latest Features' }, categoryId: { type: 'integer', default: 0 }, postsToShow: { type: 'integer', default: 7 }, layout: { type: 'string', default: 'magazine' }, showExcerpt: { type: 'boolean', default: true }, showAuthor: { type: 'boolean', default: true }, showDate: { type: 'boolean', default: true }, showFilters: { type: 'boolean', default: true }, filterLimit: { type: 'integer', default: 6 } },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes;
      return el(Fragment, {}, editorialControls(props, { layouts: [{ label: __('Magazine / Tech publication', 'techpress-editorial'), value: 'magazine' }, { label: __('Lead + supporting list', 'techpress-editorial'), value: 'lead-list' }, { label: __('3-column grid', 'techpress-editorial'), value: 'grid-3' }, { label: __('4-column grid', 'techpress-editorial'), value: 'grid-4' }] }),
        el(InspectorControls, {}, el(PanelBody, { title: __('Feature navigation', 'techpress-editorial'), initialOpen: false },
          el(ToggleControl, { label: __('Show category filters', 'techpress-editorial'), checked: !!a.showFilters, onChange: function (v) { set({ showFilters: v }); } }),
          el(RangeControl, { label: __('Filter links', 'techpress-editorial'), value: a.filterLimit || 6, min: 3, max: 8, onChange: function (v) { set({ filterLimit: v }); } })
        )), el(Preview, { name: 'techpress/featured-posts', attributes: a }));
    }, save: function () { return null; }
  });

  registerBlockType('techpress/post-feed', {
    apiVersion: 3, title: __('Editorial Post Feed', 'techpress-editorial'), category: 'techpress-editorial', icon: 'screenoptions',
    attributes: { heading: { type: 'string', default: 'Latest' }, categoryId: { type: 'integer', default: 0 }, postsToShow: { type: 'integer', default: 6 }, layout: { type: 'string', default: 'grid-3' }, orderBy: { type: 'string', default: 'date' }, showImage: { type: 'boolean', default: true }, showExcerpt: { type: 'boolean', default: false }, showAuthor: { type: 'boolean', default: true }, showDate: { type: 'boolean', default: true } },
    edit: function (props) { return el(Fragment, {}, editorialControls(props, { orderBy: true, layouts: [{ label: __('List', 'techpress-editorial'), value: 'list' }, { label: __('2-column grid', 'techpress-editorial'), value: 'grid-2' }, { label: __('3-column grid', 'techpress-editorial'), value: 'grid-3' }, { label: __('4-column grid', 'techpress-editorial'), value: 'grid-4' }, { label: __('5-column grid', 'techpress-editorial'), value: 'grid-5' }] }), el(Preview, { name: 'techpress/post-feed', attributes: props.attributes })); }, save: function () { return null; }
  });

  registerBlockType('techpress/editorial-feed', {
    apiVersion: 3, title: __('Editorial Feed Engine', 'techpress-editorial'), category: 'techpress-editorial', icon: 'layout',
    attributes: {
      heading: { type: 'string', default: 'Editorial Feed' }, description: { type: 'string', default: '' }, sourceMode: { type: 'string', default: 'latest' }, categoryId: { type: 'integer', default: 0 }, manualPostIds: { type: 'string', default: '' }, postsToShow: { type: 'integer', default: 8 }, layout: { type: 'string', default: 'cards-4' }, orderBy: { type: 'string', default: 'date' }, showCategory: { type: 'boolean', default: true }, showExcerpt: { type: 'boolean', default: false }, showAuthor: { type: 'boolean', default: false }, showDate: { type: 'boolean', default: false }, showReadTime: { type: 'boolean', default: true }, showViews: { type: 'boolean', default: true }, showShares: { type: 'boolean', default: true }, showViewAll: { type: 'boolean', default: true }, viewAllLabel: { type: 'string', default: 'View All' }, viewAllUrl: { type: 'string', default: '' }
    },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes, categoryOptions = useCategoryOptions();
      const layoutOptions = [
        { label: __('4-card section', 'techpress-editorial'), value: 'cards-4' },
        { label: __('Top Weekly mosaic', 'techpress-editorial'), value: 'weekly-mosaic' },
        { label: __('Overlay carousel', 'techpress-editorial'), value: 'carousel-overlay' },
        { label: __('Featured lead + two', 'techpress-editorial'), value: 'featured-trio' },
        { label: __('Compact topic matrix', 'techpress-editorial'), value: 'compact-grid' },
        { label: __('Latest posts cards', 'techpress-editorial'), value: 'latest-cards' }
      ];
      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Editorial Feed', 'techpress-editorial'), initialOpen: true },
            el(TextControl, { label: __('Section heading', 'techpress-editorial'), value: a.heading || '', onChange: function(v){ set({ heading:v }); } }),
            el(TextControl, { label: __('Section description', 'techpress-editorial'), value: a.description || '', onChange: function(v){ set({ description:v }); } }),
            el(SelectControl, { label: __('Layout', 'techpress-editorial'), value: a.layout, options: layoutOptions, onChange: function(v){ set({ layout:v }); } }),
            el(SelectControl, { label: __('Content source', 'techpress-editorial'), value: a.sourceMode || 'latest', options: [
              { label: __('Latest posts', 'techpress-editorial'), value:'latest' },
              { label: __('Selected category', 'techpress-editorial'), value:'category' },
              { label: __('Current page/category context', 'techpress-editorial'), value:'current' },
              { label: __('Manual post IDs', 'techpress-editorial'), value:'manual' }
            ], onChange: function(v){ set({ sourceMode:v }); } }),
            a.sourceMode === 'category' ? el(SelectControl, { label: __('Category', 'techpress-editorial'), value: a.categoryId || 0, options: categoryOptions, onChange: function(v){ set({ categoryId:parseInt(v,10)||0 }); } }) : null,
            a.sourceMode === 'manual' ? el(TextControl, { label: __('Post IDs', 'techpress-editorial'), help: __('Comma-separated WordPress post IDs. Order is preserved.', 'techpress-editorial'), value: a.manualPostIds || '', onChange: function(v){ set({ manualPostIds:v.replace(/[^0-9,\s]/g,'') }); } }) : null,
            el(RangeControl, { label: __('Stories', 'techpress-editorial'), value: a.postsToShow || 8, min: 3, max: 16, onChange: function(v){ set({ postsToShow:v }); } }),
            el(SelectControl, { label: __('Order by', 'techpress-editorial'), value: a.orderBy || 'date', options: [
              { label: __('Newest', 'techpress-editorial'), value:'date' },
              { label: __('Recently updated', 'techpress-editorial'), value:'modified' },
              { label: __('Most discussed', 'techpress-editorial'), value:'comment_count' },
              { label: __('Alphabetical', 'techpress-editorial'), value:'title' }
            ], onChange: function(v){ set({ orderBy:v }); } })
          ),
          el(PanelBody, { title: __('Story metadata', 'techpress-editorial'), initialOpen: false },
            el(ToggleControl, { label: __('Show category', 'techpress-editorial'), checked: !!a.showCategory, onChange: function(v){ set({ showCategory:v }); } }),
            el(ToggleControl, { label: __('Show excerpt', 'techpress-editorial'), checked: !!a.showExcerpt, onChange: function(v){ set({ showExcerpt:v }); } }),
            el(ToggleControl, { label: __('Show author', 'techpress-editorial'), checked: !!a.showAuthor, onChange: function(v){ set({ showAuthor:v }); } }),
            el(ToggleControl, { label: __('Show date', 'techpress-editorial'), checked: !!a.showDate, onChange: function(v){ set({ showDate:v }); } }),
            el(ToggleControl, { label: __('Show read time', 'techpress-editorial'), checked: !!a.showReadTime, onChange: function(v){ set({ showReadTime:v }); } }),
            el(ToggleControl, { label: __('Show views when available', 'techpress-editorial'), checked: !!a.showViews, onChange: function(v){ set({ showViews:v }); } }),
            el(ToggleControl, { label: __('Show shares when available', 'techpress-editorial'), checked: !!a.showShares, onChange: function(v){ set({ showShares:v }); } })
          ),
          el(PanelBody, { title: __('View All link', 'techpress-editorial'), initialOpen: false },
            el(ToggleControl, { label: __('Show View All', 'techpress-editorial'), checked: !!a.showViewAll, onChange: function(v){ set({ showViewAll:v }); } }),
            el(TextControl, { label: __('Label', 'techpress-editorial'), value: a.viewAllLabel || 'View All', onChange: function(v){ set({ viewAllLabel:v }); } }),
            el(TextControl, { label: __('Custom URL (optional)', 'techpress-editorial'), help: __('Leave blank to use the selected/current category or Posts page automatically.', 'techpress-editorial'), value: a.viewAllUrl || '', onChange: function(v){ set({ viewAllUrl:v }); } })
          )
        ),
        el(Preview, { name:'techpress/editorial-feed', attributes:a })
      );
    }, save: function(){ return null; }
  });

  registerBlockType('techpress/ad-slot', {
    apiVersion: 3, title: __('Ad Slot', 'techpress-editorial'), category: 'techpress-editorial', icon: 'megaphone',
    attributes: { slotName: { type: 'string', default: 'content-slot' }, label: { type: 'string', default: 'Advertisement' }, minHeight: { type: 'integer', default: 90 }, collapseEmpty: { type: 'boolean', default: true } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Ad slot settings', 'techpress-editorial'), initialOpen: true }, el(TextControl, { label: __('Slot name', 'techpress-editorial'), value: a.slotName, onChange: function (v) { set({ slotName: v.replace(/[^a-zA-Z0-9_-]/g, '-').toLowerCase() }); } }), el(TextControl, { label: __('Label', 'techpress-editorial'), value: a.label, onChange: function (v) { set({ label: v }); } }), el(RangeControl, { label: __('Reserved height (px)', 'techpress-editorial'), value: a.minHeight, min: 0, max: 600, step: 10, onChange: function (v) { set({ minHeight: v }); } }), el(ToggleControl, { label: __('Collapse when empty', 'techpress-editorial'), checked: !!a.collapseEmpty, onChange: function (v) { set({ collapseEmpty: v }); } }))), el(Preview, { name: 'techpress/ad-slot', attributes: a })); }, save: function () { return null; }
  });

  registerBlockType('techpress/category-nav', {
    apiVersion: 3, title: __('Editorial Category Navigation', 'techpress-editorial'), category: 'techpress-editorial', icon: 'menu-alt3',
    attributes: { limit: { type: 'integer', default: 5 }, showDictionary: { type: 'boolean', default: true }, showSearch: { type: 'boolean', default: true } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Navigation settings', 'techpress-editorial'), initialOpen: true }, el(RangeControl, { label: __('Category links', 'techpress-editorial'), value: a.limit, min: 2, max: 8, onChange: function (v) { set({ limit: v }); } }), el(ToggleControl, { label: __('Show Dictionary', 'techpress-editorial'), checked: !!a.showDictionary, onChange: function (v) { set({ showDictionary: v }); } }), el(ToggleControl, { label: __('Show search', 'techpress-editorial'), checked: !!a.showSearch, onChange: function (v) { set({ showSearch: v }); } }))), el(Preview, { name: 'techpress/category-nav', attributes: a })); }, save: function () { return null; }
  });

  registerBlockType('techpress/term-index', {
    apiVersion: 3, title: __('Dictionary Index', 'techpress-editorial'), category: 'techpress-editorial', icon: 'book-alt',
    attributes: { heading: { type: 'string', default: 'Tech Dictionary' }, postsToShow: { type: 'integer', default: 16 }, showSearch: { type: 'boolean', default: true }, showAlphabet: { type: 'boolean', default: true }, showPopular: { type: 'boolean', default: true }, popularHeading: { type: 'string', default: '' } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Dictionary settings', 'techpress-editorial'), initialOpen: true }, el(TextControl, { label: __('Section heading', 'techpress-editorial'), value: a.heading, onChange: function (v) { set({ heading: v }); } }), el(RangeControl, { label: __('Terms to show', 'techpress-editorial'), value: a.postsToShow, min: 4, max: 60, onChange: function (v) { set({ postsToShow: v }); } }), el(ToggleControl, { label: __('Show search', 'techpress-editorial'), checked: !!a.showSearch, onChange: function (v) { set({ showSearch: v }); } }), el(ToggleControl, { label: __('Show A–Z navigation', 'techpress-editorial'), checked: !!a.showAlphabet, onChange: function (v) { set({ showAlphabet: v }); } }), el(ToggleControl, { label: __('Show popular cards', 'techpress-editorial'), checked: !!a.showPopular, onChange: function (v) { set({ showPopular: v }); } }), el(TextControl, { label: __('Popular section heading (blank = site name)', 'techpress-editorial'), value: a.popularHeading || '', onChange: function (v) { set({ popularHeading: v }); } }))), el(Preview, { name: 'techpress/term-index', attributes: a })); }, save: function () { return null; }
  });

  function registerContextBlock(name, title, icon, controls) {
    if (getBlockType(name)) return;
    registerBlockType(name, {
      apiVersion: 3, title: title, category: 'techpress-editorial', icon: icon,
      edit: function (props) { return el(Fragment, {}, controls ? controls(props) : null, el(Preview, { name: name, attributes: props.attributes })); }, save: function () { return null; }
    });
  }

  registerContextBlock('techpress/archive-hero', __('Archive Hero', 'techpress-editorial'), 'welcome-widgets-menus', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive hero', 'techpress-editorial') }, el(ToggleControl, { label: __('Show search', 'techpress-editorial'), checked: !!p.attributes.showSearch, onChange: function (v) { p.setAttributes({ showSearch: v }); } }), el(ToggleControl, { label: __('Show featured terms', 'techpress-editorial'), checked: !!p.attributes.showFeaturedTerms, onChange: function (v) { p.setAttributes({ showFeaturedTerms: v }); } }))); });
  registerContextBlock('techpress/archive-feed', __('Archive Story Feed', 'techpress-editorial'), 'grid-view', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive feed', 'techpress-editorial') }, el(RangeControl, { label: __('Posts per page', 'techpress-editorial'), min: 5, max: 20, value: p.attributes.postsPerPage || 10, onChange: function (v) { p.setAttributes({ postsPerPage: v }); } }), el(RangeControl, { label: __('Columns', 'techpress-editorial'), min: 2, max: 5, value: p.attributes.columns || 5, onChange: function (v) { p.setAttributes({ columns: v }); } }), el(ToggleControl, { label: __('Show category top picks', 'techpress-editorial'), checked: !!p.attributes.showTopPicks, onChange: function (v) { p.setAttributes({ showTopPicks: v }); } }))); });
  registerContextBlock('techpress/popular-categories', __('Popular Categories', 'techpress-editorial'), 'category', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Popular categories', 'techpress-editorial') }, el(TextControl, { label: __('Heading', 'techpress-editorial'), value: p.attributes.heading || '', onChange: function (v) { p.setAttributes({ heading: v }); } }), el(RangeControl, { label: __('Categories', 'techpress-editorial'), min: 4, max: 12, value: p.attributes.limit || 8, onChange: function (v) { p.setAttributes({ limit: v }); } }))); });
  ['techpress/author-profile', 'techpress/category-experts', 'techpress/related-posts', 'techpress/post-author-card', 'techpress/article-toc', 'techpress/article-byline'].forEach(function (name) { registerContextBlock(name, name.split('/')[1].replace(/-/g, ' '), 'admin-post'); });
})(window.wp);

(function (wp) {
  'use strict';
  if (!wp || !wp.plugins || !wp.editor || !wp.data || !wp.element || !wp.components) return;
  const el = wp.element.createElement;
  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editor;
  const { SelectControl, Notice } = wp.components;
  const useSelect = wp.data.useSelect;
  const { __ } = wp.i18n;

  function EditorialAttributionPanel() {
    const state = useSelect(function (select) {
      const editor = select('core/editor');
      return { postType: editor.getCurrentPostType(), meta: editor.getEditedPostAttribute('meta') || {} };
    }, []);
    const users = useSelect(function (select) {
      return select('core').getEntityRecords('root', 'user', { per_page: 100, context: 'view', orderby: 'name', order: 'asc' });
    }, []);
    if (state.postType !== 'post') return null;

    const type = state.meta._techpress_attribution_type || '';
    const userId = parseInt(state.meta._techpress_attribution_user || 0, 10);
    const userOptions = [{ label: __('Select person', 'techpress-editorial'), value: 0 }];
    if (Array.isArray(users)) users.forEach(function (user) { userOptions.push({ label: user.name, value: user.id }); });

    function updateMeta(patch) {
      wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, state.meta, patch) });
    }

    return el(PluginDocumentSettingPanel, { name: 'techpress-editorial-attribution', title: __('Editorial Attribution', 'techpress-editorial'), className: 'techpress-editorial-attribution' },
      el(SelectControl, {
        label: __('Secondary credit', 'techpress-editorial'), value: type,
        options: [
          { label: __('None', 'techpress-editorial'), value: '' },
          { label: __('Fact Checked by', 'techpress-editorial'), value: 'fact_checked' },
          { label: __('Verified by', 'techpress-editorial'), value: 'verified' },
          { label: __('Reported by', 'techpress-editorial'), value: 'reported' }
        ],
        onChange: function (value) { updateMeta({ _techpress_attribution_type: value, _techpress_attribution_user: value ? userId : 0 }); }
      }),
      type ? el(SelectControl, { label: __('Person', 'techpress-editorial'), value: userId, options: userOptions, onChange: function (value) { updateMeta({ _techpress_attribution_user: parseInt(value, 10) || 0 }); } }) : null,
      type && !userId ? el(Notice, { status: 'warning', isDismissible: false }, __('Select the person who should receive this credit.', 'techpress-editorial')) : null
    );
  }

  registerPlugin('techpress-editorial-attribution', { render: EditorialAttributionPanel, icon: 'admin-users' });
})(window.wp);
