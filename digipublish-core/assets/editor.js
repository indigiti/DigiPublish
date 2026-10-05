(function (wp) {
  'use strict';
  if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const Fragment = wp.element.Fragment;
  const { registerBlockType, registerBlockVariation, getBlockType, createBlock } = wp.blocks;
  const { InspectorControls, useBlockProps, InnerBlocks, MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor;
  const { PanelBody, TextControl, RangeControl, SelectControl, ToggleControl, Notice, Button } = wp.components;
  const { __ } = wp.i18n;
  const useSelect = wp.data.useSelect;
  const SSRPackage = wp.serverSideRender;
  const ServerSideRender = SSRPackage && (SSRPackage.ServerSideRender || SSRPackage.default || SSRPackage);

  function Preview(props) {
    const blockProps = useBlockProps();
    if (!ServerSideRender) {
      return el('div', blockProps, el(Notice, { status: 'warning', isDismissible: false }, __('Preview unavailable. The block will render on the front end.', 'digipublish-core')));
    }
    return el('div', blockProps, el(ServerSideRender, { block: props.name, attributes: props.attributes }));
  }

  function useCategoryOptions() {
    const categories = useSelect(function (select) {
      return select('core').getEntityRecords('taxonomy', 'category', { per_page: 100, orderby: 'name', order: 'asc' });
    }, []);
    const options = [{ label: __('All categories', 'digipublish-core'), value: 0 }];
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

    children.push(el(TextControl, { label: __('Section heading', 'digipublish-core'), value: a.heading || '', onChange: function (value) { set({ heading: value }); } }));
    if (config.category !== false) {
      children.push(el(SelectControl, { label: __('Content source', 'digipublish-core'), value: a.categoryId || 0, options: categoryOptions, onChange: function (value) { set({ categoryId: parseInt(value, 10) || 0 }); } }));
    }
    if (Object.prototype.hasOwnProperty.call(a, 'postsToShow')) {
      children.push(el(RangeControl, { label: __('Number of stories', 'digipublish-core'), value: a.postsToShow, min: config.min || 1, max: config.max || 12, onChange: function (value) { set({ postsToShow: value }); } }));
    }
    if (config.layouts) {
      children.push(el(SelectControl, { label: __('Layout', 'digipublish-core'), value: a.layout, options: config.layouts, onChange: function (value) { set({ layout: value }); } }));
    }
    if (config.orderBy) {
      children.push(el(SelectControl, {
        label: __('Order by', 'digipublish-core'), value: a.orderBy,
        options: [
          { label: __('Newest', 'digipublish-core'), value: 'date' },
          { label: __('Recently updated', 'digipublish-core'), value: 'modified' },
          { label: __('Most discussed', 'digipublish-core'), value: 'comment_count' },
          { label: __('Alphabetical', 'digipublish-core'), value: 'title' }
        ],
        onChange: function (value) { set({ orderBy: value }); }
      }));
    }
    ['showImage', 'showExcerpt', 'showAuthor', 'showDate'].forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(a, key)) return;
      const labels = { showImage: __('Show image', 'digipublish-core'), showExcerpt: __('Show excerpt', 'digipublish-core'), showAuthor: __('Show author', 'digipublish-core'), showDate: __('Show date', 'digipublish-core') };
      children.push(el(ToggleControl, { label: labels[key], checked: !!a[key], onChange: function (value) { const o = {}; o[key] = value; set(o); } }));
    });
    return el(InspectorControls, {}, el(PanelBody, { title: __('Editorial settings', 'digipublish-core'), initialOpen: true }, children));
  }

  registerBlockVariation('core/query', {
    name: 'digipublish/story-grid', title: __('DigiPublish Story Grid', 'digipublish-core'), description: __('Native Query Loop variation with the publication card hierarchy.', 'digipublish-core'), icon: 'grid-view', scope: ['inserter'],
    isActive: function (attributes) { return attributes.namespace === 'digipublish/story-grid'; },
    attributes: { namespace: 'digipublish/story-grid', align: 'wide', className: 'tp-query-grid', query: { perPage: 6, pages: 0, offset: 0, postType: 'post', order: 'desc', orderBy: 'date', author: '', search: '', exclude: [], sticky: '', inherit: false }, displayLayout: { type: 'flex', columns: 3 } },
    allowedControls: ['order', 'taxQuery', 'author', 'search'],
    innerBlocks: [['core/post-template', { className: 'tp-query-card' }, [['core/post-featured-image', { isLink: true, aspectRatio: '16/9' }], ['core/post-terms', { term: 'category', className: 'tp-category' }], ['core/post-title', { isLink: true, level: 3, fontSize: 'lg' }], ['core/post-date', { className: 'tp-meta' }]]]]
  });

  registerBlockType('digipublish/featured-posts', {
    apiVersion: 3, title: __('Featured Stories', 'digipublish-core'), category: 'digipublish-editorial', icon: 'star-filled',
    attributes: { heading: { type: 'string', default: 'Latest Features' }, categoryId: { type: 'integer', default: 0 }, postsToShow: { type: 'integer', default: 7 }, layout: { type: 'string', default: 'magazine' }, showExcerpt: { type: 'boolean', default: true }, showAuthor: { type: 'boolean', default: true }, showDate: { type: 'boolean', default: true }, showFilters: { type: 'boolean', default: true }, filterLimit: { type: 'integer', default: 6 } },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes;
      return el(Fragment, {}, editorialControls(props, { layouts: [{ label: __('Magazine / Tech publication', 'digipublish-core'), value: 'magazine' }, { label: __('Lead + supporting list', 'digipublish-core'), value: 'lead-list' }, { label: __('3-column grid', 'digipublish-core'), value: 'grid-3' }, { label: __('4-column grid', 'digipublish-core'), value: 'grid-4' }] }),
        el(InspectorControls, {}, el(PanelBody, { title: __('Feature navigation', 'digipublish-core'), initialOpen: false },
          el(ToggleControl, { label: __('Show category filters', 'digipublish-core'), checked: !!a.showFilters, onChange: function (v) { set({ showFilters: v }); } }),
          el(RangeControl, { label: __('Filter links', 'digipublish-core'), value: a.filterLimit || 6, min: 3, max: 8, onChange: function (v) { set({ filterLimit: v }); } })
        )), el(Preview, { name: 'digipublish/featured-posts', attributes: a }));
    }, save: function () { return null; }
  });

  registerBlockType('digipublish/post-feed', {
    apiVersion: 3, title: __('Editorial Post Feed', 'digipublish-core'), category: 'digipublish-editorial', icon: 'screenoptions',
    attributes: { heading: { type: 'string', default: 'Latest' }, categoryId: { type: 'integer', default: 0 }, postsToShow: { type: 'integer', default: 6 }, layout: { type: 'string', default: 'grid-3' }, orderBy: { type: 'string', default: 'date' }, showImage: { type: 'boolean', default: true }, showExcerpt: { type: 'boolean', default: false }, showAuthor: { type: 'boolean', default: true }, showDate: { type: 'boolean', default: true } },
    edit: function (props) { return el(Fragment, {}, editorialControls(props, { orderBy: true, layouts: [{ label: __('List', 'digipublish-core'), value: 'list' }, { label: __('2-column grid', 'digipublish-core'), value: 'grid-2' }, { label: __('3-column grid', 'digipublish-core'), value: 'grid-3' }, { label: __('4-column grid', 'digipublish-core'), value: 'grid-4' }, { label: __('5-column grid', 'digipublish-core'), value: 'grid-5' }] }), el(Preview, { name: 'digipublish/post-feed', attributes: props.attributes })); }, save: function () { return null; }
  });

  registerBlockType('digipublish/editorial-feed', {
    apiVersion: 3, title: __('Editorial Feed Engine', 'digipublish-core'), category: 'digipublish-editorial', icon: 'layout',
    attributes: {
      heading: { type: 'string', default: 'Editorial Feed' }, description: { type: 'string', default: '' }, sourceMode: { type: 'string', default: 'latest' }, categoryId: { type: 'integer', default: 0 }, categorySlug: { type: 'string', default: '' }, fillFromLatest: { type: 'boolean', default: false }, manualPostIds: { type: 'string', default: '' }, postsToShow: { type: 'integer', default: 8 }, layout: { type: 'string', default: 'cards-4' }, orderBy: { type: 'string', default: 'date' }, period: { type: 'string', default: 'all' }, avoidDuplicates: { type: 'boolean', default: false }, fallbackRandom: { type: 'boolean', default: false }, showCategory: { type: 'boolean', default: true }, showExcerpt: { type: 'boolean', default: false }, showAuthor: { type: 'boolean', default: false }, showDate: { type: 'boolean', default: false }, showReadTime: { type: 'boolean', default: true }, showViews: { type: 'boolean', default: true }, showShares: { type: 'boolean', default: true }, showViewAll: { type: 'boolean', default: true }, viewAllLabel: { type: 'string', default: 'View All' }, viewAllUrl: { type: 'string', default: '' }
    },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes, categoryOptions = useCategoryOptions();
      const layoutOptions = [
        { label: __('4-card section', 'digipublish-core'), value: 'cards-4' },
        { label: __('Top Weekly mosaic', 'digipublish-core'), value: 'weekly-mosaic' },
        { label: __('Overlay carousel', 'digipublish-core'), value: 'carousel-overlay' },
        { label: __('Featured lead + two', 'digipublish-core'), value: 'featured-trio' },
        { label: __('Compact topic matrix', 'digipublish-core'), value: 'compact-grid' },
        { label: __('Latest posts cards', 'digipublish-core'), value: 'latest-cards' }
      ];
      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Editorial Feed', 'digipublish-core'), initialOpen: true },
            el(TextControl, { label: __('Section heading', 'digipublish-core'), value: a.heading || '', onChange: function(v){ set({ heading:v }); } }),
            el(TextControl, { label: __('Section description', 'digipublish-core'), value: a.description || '', onChange: function(v){ set({ description:v }); } }),
            el(SelectControl, { label: __('Layout', 'digipublish-core'), value: a.layout, options: layoutOptions, onChange: function(v){ set({ layout:v }); } }),
            el(SelectControl, { label: __('Content source', 'digipublish-core'), value: a.sourceMode || 'latest', options: [
              { label: __('Latest posts', 'digipublish-core'), value:'latest' },
              { label: __('Selected category', 'digipublish-core'), value:'category' },
              { label: __('Current page/category context', 'digipublish-core'), value:'current' },
              { label: __('Manual post IDs', 'digipublish-core'), value:'manual' }
            ], onChange: function(v){ set({ sourceMode:v }); } }),
            a.sourceMode === 'category' ? el(SelectControl, { label: __('Category', 'digipublish-core'), value: a.categoryId || 0, options: categoryOptions, onChange: function(v){ set({ categoryId:parseInt(v,10)||0 }); } }) : null,
            a.sourceMode === 'category' ? el(TextControl, { label: __('Portable category slug fallback', 'digipublish-core'), help: __('Used when no category ID is selected. This keeps reusable patterns portable between sites.', 'digipublish-core'), value: a.categorySlug || '', onChange: function(v){ set({ categorySlug:v.toLowerCase().replace(/[^a-z0-9-]/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'') }); } }) : null,
            a.sourceMode === 'category' ? el(ToggleControl, { label: __('Fill sparse category from Latest', 'digipublish-core'), help: __('Keep the selected category first, then fill empty visual slots with non-duplicate latest stories.', 'digipublish-core'), checked: !!a.fillFromLatest, onChange: function(v){ set({ fillFromLatest:v }); } }) : null,
            a.sourceMode === 'manual' ? el(TextControl, { label: __('Post IDs', 'digipublish-core'), help: __('Comma-separated WordPress post IDs. Order is preserved.', 'digipublish-core'), value: a.manualPostIds || '', onChange: function(v){ set({ manualPostIds:v.replace(/[^0-9,\s]/g,'') }); } }) : null,
            el(RangeControl, { label: __('Stories', 'digipublish-core'), value: a.postsToShow || 8, min: 3, max: 16, onChange: function(v){ set({ postsToShow:v }); } }),
            el(SelectControl, { label: __('Order by', 'digipublish-core'), value: a.orderBy || 'date', options: [
              { label: __('Newest', 'digipublish-core'), value:'date' },
              { label: __('Recently updated', 'digipublish-core'), value:'modified' },
              { label: __('Most discussed', 'digipublish-core'), value:'comment_count' },
              { label: __('Alphabetical', 'digipublish-core'), value:'title' }
            ], onChange: function(v){ set({ orderBy:v }); } }),
            el(SelectControl, { label: __('Time period', 'digipublish-core'), value: a.period || 'all', options: [
              { label: __('All time', 'digipublish-core'), value:'all' },
              { label: __('Past 24 hours', 'digipublish-core'), value:'day' },
              { label: __('Past 7 days', 'digipublish-core'), value:'week' },
              { label: __('Past 30 days', 'digipublish-core'), value:'month' }
            ], onChange: function(v){ set({ period:v }); } }),
            el(ToggleControl, { label: __('Avoid stories already rendered earlier on this page', 'digipublish-core'), checked: !!a.avoidDuplicates, onChange: function(v){ set({ avoidDuplicates:v }); } }),
            el(ToggleControl, { label: __('Use random posts if this feed is sparse', 'digipublish-core'), help: __('Fills remaining visual slots from a cached all-time pool without using SQL ORDER BY RAND().', 'digipublish-core'), checked: !!a.fallbackRandom, onChange: function(v){ set({ fallbackRandom:v }); } })
          ),
          el(PanelBody, { title: __('Story metadata', 'digipublish-core'), initialOpen: false },
            el(ToggleControl, { label: __('Show category', 'digipublish-core'), checked: !!a.showCategory, onChange: function(v){ set({ showCategory:v }); } }),
            el(ToggleControl, { label: __('Show excerpt', 'digipublish-core'), checked: !!a.showExcerpt, onChange: function(v){ set({ showExcerpt:v }); } }),
            el(ToggleControl, { label: __('Show author', 'digipublish-core'), checked: !!a.showAuthor, onChange: function(v){ set({ showAuthor:v }); } }),
            el(ToggleControl, { label: __('Show date', 'digipublish-core'), checked: !!a.showDate, onChange: function(v){ set({ showDate:v }); } }),
            el(ToggleControl, { label: __('Show read time', 'digipublish-core'), checked: !!a.showReadTime, onChange: function(v){ set({ showReadTime:v }); } }),
            el(ToggleControl, { label: __('Show views when available', 'digipublish-core'), checked: !!a.showViews, onChange: function(v){ set({ showViews:v }); } }),
            el(ToggleControl, { label: __('Show shares when available', 'digipublish-core'), checked: !!a.showShares, onChange: function(v){ set({ showShares:v }); } })
          ),
          el(PanelBody, { title: __('View All link', 'digipublish-core'), initialOpen: false },
            el(ToggleControl, { label: __('Show View All', 'digipublish-core'), checked: !!a.showViewAll, onChange: function(v){ set({ showViewAll:v }); } }),
            el(TextControl, { label: __('Label', 'digipublish-core'), value: a.viewAllLabel || 'View All', onChange: function(v){ set({ viewAllLabel:v }); } }),
            el(TextControl, { label: __('Custom URL (optional)', 'digipublish-core'), help: __('Leave blank to use the selected/current category or Posts page automatically.', 'digipublish-core'), value: a.viewAllUrl || '', onChange: function(v){ set({ viewAllUrl:v }); } })
          )
        ),
        el(Preview, { name:'digipublish/editorial-feed', attributes:a })
      );
    }, save: function(){ return null; }
  });


  [
    {
      name: 'business-markets',
      title: __('Business / Markets', 'digipublish-core'),
      description: __('Four-card Business / Markets editorial section.', 'digipublish-core'),
      icon: 'chart-line',
      attributes: { heading:'Business / Markets', sourceMode:'category', categorySlug:'business', fillFromLatest:true, avoidDuplicates:true, postsToShow:4, layout:'cards-4', showCategory:true, showExcerpt:false, showAuthor:false, showDate:false, showReadTime:false, showViews:false, showShares:false, showViewAll:true, viewAllLabel:'View All' }
    },
    {
      name: 'top-weekly',
      title: __('Top Weekly', 'digipublish-core'),
      description: __('Seven-day most-discussed editorial mosaic.', 'digipublish-core'),
      icon: 'awards',
      attributes: { heading:'Top Weekly', sourceMode:'latest', period:'week', avoidDuplicates:true, fallbackRandom:true, postsToShow:6, layout:'weekly-mosaic', orderBy:'comment_count', showCategory:true, showExcerpt:true, showAuthor:false, showDate:false, showReadTime:true, showViews:true, showShares:true, showViewAll:true, viewAllLabel:'View All' }
    },
    {
      name: 'science-space',
      title: __('Science / Space', 'digipublish-core'),
      description: __('Swipeable overlay carousel with Science-first sourcing.', 'digipublish-core'),
      icon: 'slides',
      attributes: { heading:'Science / Space', sourceMode:'category', categorySlug:'science', fillFromLatest:true, avoidDuplicates:true, postsToShow:8, layout:'carousel-overlay', showCategory:true, showExcerpt:true, showAuthor:false, showDate:false, showReadTime:true, showViews:false, showShares:false, showViewAll:true, viewAllLabel:'See More' }
    },
    {
      name: 'travels',
      title: __('Travels', 'digipublish-core'),
      description: __('Featured lead story with two supporting travel cards.', 'digipublish-core'),
      icon: 'location-alt',
      attributes: { heading:'Travels', description:'Become a traveler with guides to destinations, booking tips, and ideas for finding the best things to do wherever you go.', sourceMode:'category', categorySlug:'travel', fillFromLatest:true, avoidDuplicates:true, postsToShow:3, layout:'featured-trio', showCategory:true, showExcerpt:true, showAuthor:false, showDate:false, showReadTime:true, showViews:false, showShares:false, showViewAll:true, viewAllLabel:'See More' }
    },
    {
      name: 'wearables',
      title: __('Wearables', 'digipublish-core'),
      description: __('Dense compact feed for wearable technology.', 'digipublish-core'),
      icon: 'smartphone',
      attributes: { heading:'Wearables', description:'Exploring the latest in earbuds, headphones, audio and wearable technology.', sourceMode:'category', categorySlug:'wearables', fillFromLatest:true, avoidDuplicates:true, postsToShow:12, layout:'compact-grid', showCategory:false, showExcerpt:false, showAuthor:false, showDate:false, showReadTime:true, showViews:true, showShares:false, showViewAll:true, viewAllLabel:'See More Wearables' }
    },
    {
      name: 'latest-posts',
      title: __('Latest Posts', 'digipublish-core'),
      description: __('Latest article cards with excerpt and metadata.', 'digipublish-core'),
      icon: 'list-view',
      attributes: { heading:'Latest Posts', sourceMode:'latest', avoidDuplicates:true, postsToShow:8, layout:'latest-cards', showCategory:false, showExcerpt:true, showAuthor:false, showDate:true, showReadTime:true, showViews:true, showShares:true, showViewAll:false }
    },
    {
      name: 'technology',
      title: __('Technology', 'digipublish-core'),
      description: __('Compact technology topic matrix.', 'digipublish-core'),
      icon: 'desktop',
      attributes: { heading:'Technology', description:'Exploring the latest in mobiles, technology, gadgets, apps and software.', sourceMode:'category', categorySlug:'technology', fillFromLatest:true, avoidDuplicates:true, postsToShow:12, layout:'compact-grid', showCategory:false, showExcerpt:false, showAuthor:false, showDate:false, showReadTime:true, showViews:false, showShares:false, showViewAll:true, viewAllLabel:'See More Technology' }
    }
  ].forEach(function (variation) {
    registerBlockVariation('digipublish/editorial-feed', Object.assign({
      scope: ['inserter'],
      keywords: [__('DigiPublish', 'digipublish-core'), __('editorial', 'digipublish-core')]
    }, variation));
  });

  registerBlockType('digipublish/ad-slot', {
    apiVersion: 3, title: __('Ad Slot', 'digipublish-core'), category: 'digipublish-editorial', icon: 'megaphone',
    attributes: { slotName: { type: 'string', default: 'content-slot' }, label: { type: 'string', default: 'Advertisement' }, minHeight: { type: 'integer', default: 90 }, collapseEmpty: { type: 'boolean', default: true } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Ad slot settings', 'digipublish-core'), initialOpen: true }, el(TextControl, { label: __('Slot name', 'digipublish-core'), value: a.slotName, onChange: function (v) { set({ slotName: v.replace(/[^a-zA-Z0-9_-]/g, '-').toLowerCase() }); } }), el(TextControl, { label: __('Label', 'digipublish-core'), value: a.label, onChange: function (v) { set({ label: v }); } }), el(RangeControl, { label: __('Reserved height (px)', 'digipublish-core'), value: a.minHeight, min: 0, max: 600, step: 10, onChange: function (v) { set({ minHeight: v }); } }), el(ToggleControl, { label: __('Collapse when empty', 'digipublish-core'), checked: !!a.collapseEmpty, onChange: function (v) { set({ collapseEmpty: v }); } }))), el(Preview, { name: 'digipublish/ad-slot', attributes: a })); }, save: function () { return null; }
  });

  registerBlockType('digipublish/category-nav', {
    apiVersion: 3, title: __('Editorial Category Navigation', 'digipublish-core'), category: 'digipublish-editorial', icon: 'menu-alt3',
    attributes: { limit: { type: 'integer', default: 5 }, showDictionary: { type: 'boolean', default: true }, showSearch: { type: 'boolean', default: true } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Navigation settings', 'digipublish-core'), initialOpen: true }, el(RangeControl, { label: __('Category links', 'digipublish-core'), value: a.limit, min: 2, max: 8, onChange: function (v) { set({ limit: v }); } }), el(ToggleControl, { label: __('Show Dictionary', 'digipublish-core'), checked: !!a.showDictionary, onChange: function (v) { set({ showDictionary: v }); } }), el(ToggleControl, { label: __('Show search', 'digipublish-core'), checked: !!a.showSearch, onChange: function (v) { set({ showSearch: v }); } }))), el(Preview, { name: 'digipublish/category-nav', attributes: a })); }, save: function () { return null; }
  });

  registerBlockType('digipublish/term-index', {
    apiVersion: 3, title: __('Dictionary Index', 'digipublish-core'), category: 'digipublish-editorial', icon: 'book-alt',
    attributes: { heading: { type: 'string', default: 'Tech Dictionary' }, postsToShow: { type: 'integer', default: 16 }, showSearch: { type: 'boolean', default: true }, showAlphabet: { type: 'boolean', default: true }, showPopular: { type: 'boolean', default: true }, popularHeading: { type: 'string', default: '' } },
    edit: function (props) { const a = props.attributes, set = props.setAttributes; return el(Fragment, {}, el(InspectorControls, {}, el(PanelBody, { title: __('Dictionary settings', 'digipublish-core'), initialOpen: true }, el(TextControl, { label: __('Section heading', 'digipublish-core'), value: a.heading, onChange: function (v) { set({ heading: v }); } }), el(RangeControl, { label: __('Terms to show', 'digipublish-core'), value: a.postsToShow, min: 4, max: 60, onChange: function (v) { set({ postsToShow: v }); } }), el(ToggleControl, { label: __('Show search', 'digipublish-core'), checked: !!a.showSearch, onChange: function (v) { set({ showSearch: v }); } }), el(ToggleControl, { label: __('Show A–Z navigation', 'digipublish-core'), checked: !!a.showAlphabet, onChange: function (v) { set({ showAlphabet: v }); } }), el(ToggleControl, { label: __('Show featured definition cards', 'digipublish-core'), checked: !!a.showPopular, onChange: function (v) { set({ showPopular: v }); } }), el(TextControl, { label: __('Featured section heading (blank = default)', 'digipublish-core'), value: a.popularHeading || '', onChange: function (v) { set({ popularHeading: v }); } }))), el(Preview, { name: 'digipublish/term-index', attributes: a })); }, save: function () { return null; }
  });

  function registerContextBlock(name, title, icon, controls) {
    if (getBlockType(name)) return;
    registerBlockType(name, {
      apiVersion: 3, title: title, category: 'digipublish-editorial', icon: icon,
      edit: function (props) { return el(Fragment, {}, controls ? controls(props) : null, el(Preview, { name: name, attributes: props.attributes })); }, save: function () { return null; }
    });
  }

  registerBlockType('digipublish/gallery', {
    apiVersion: 3,
    title: __('Photo Gallery', 'digipublish-core'),
    category: 'digipublish-editorial',
    icon: 'format-gallery',
    attributes: {
      displayMode: { type:'string', default:'story' },
      showCounter: { type:'boolean', default:true },
      showCaptions: { type:'boolean', default:true },
      showCredits: { type:'boolean', default:true },
      showThumbnails: { type:'boolean', default:false },
      allowFullscreen: { type:'boolean', default:true },
      showSharing: { type:'boolean', default:true },
      adInterval: { type:'integer', default:0 }
    },
    edit: function(props){
      const a=props.attributes, set=props.setAttributes;
      const blockProps=useBlockProps({ className:'tp-gallery-editor' });

      function addImages(selection){
        const images=Array.isArray(selection)?selection:[selection];
        const blocks=images.filter(Boolean).map(function(image){
          return createBlock('digipublish/gallery-slide',{
            imageId: parseInt(image.id||0,10)||0,
            imageUrl: image.url||'',
            alt: image.alt||'',
            caption: image.caption||'',
            credit: image.caption && image.caption.indexOf('Photo:')===0 ? image.caption.replace(/^Photo:\s*/,'') : ''
          });
        });
        if(blocks.length){
          wp.data.dispatch('core/block-editor').insertBlocks(blocks,undefined,props.clientId);
        }
      }

      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Gallery settings','digipublish-core'),initialOpen:true},
            el(SelectControl,{label:__('Display mode','digipublish-core'),value:a.displayMode||'story',options:[
              {label:__('Story — image + caption stack','digipublish-core'),value:'story'},
              {label:__('Swipe — one photo at a time','digipublish-core'),value:'swipe'},
              {label:__('Grid — visual overview','digipublish-core'),value:'grid'}
            ],onChange:function(v){set({displayMode:v});}}),
            el(ToggleControl,{label:__('Show photo counter','digipublish-core'),checked:a.showCounter!==false,onChange:function(v){set({showCounter:v});}}),
            el(ToggleControl,{label:__('Show captions','digipublish-core'),checked:a.showCaptions!==false,onChange:function(v){set({showCaptions:v});}}),
            el(ToggleControl,{label:__('Show photo credits','digipublish-core'),checked:a.showCredits!==false,onChange:function(v){set({showCredits:v});}}),
            el(ToggleControl,{label:__('Show thumbnail strip','digipublish-core'),checked:!!a.showThumbnails,onChange:function(v){set({showThumbnails:v});}}),
            el(ToggleControl,{label:__('Allow fullscreen','digipublish-core'),checked:a.allowFullscreen!==false,onChange:function(v){set({allowFullscreen:v});}}),
            el(ToggleControl,{label:__('Show share control','digipublish-core'),checked:a.showSharing!==false,onChange:function(v){set({showSharing:v});}}),
            el(RangeControl,{label:__('Insert advertisement every N photos','digipublish-core'),help:__('Set to 0 to disable inline gallery ads.','digipublish-core'),value:a.adInterval||0,min:0,max:10,onChange:function(v){set({adInterval:v||0});}})
          )
        ),
        el('div',blockProps,
          el('div',{className:'tp-gallery-editor__toolbar'},
            el(MediaUploadCheck,{},
              el(MediaUpload,{
                onSelect:addImages,
                allowedTypes:['image'],
                multiple:true,
                gallery:true,
                render:function(mediaProps){
                  return el(Button,{variant:'primary',onClick:mediaProps.open},__('Add gallery images','digipublish-core'));
                }
              })
            )
          ),
          el(InnerBlocks,{
            allowedBlocks:['digipublish/gallery-slide'],
            templateLock:false,
            renderAppender:InnerBlocks.ButtonBlockAppender
          })
        )
      );
    },
    save:function(){return el(InnerBlocks.Content);}
  });

  registerBlockType('digipublish/gallery-slide', {
    apiVersion:3,
    title:__('Gallery Slide','digipublish-core'),
    category:'digipublish-editorial',
    icon:'format-image',
    parent:['digipublish/gallery'],
    attributes:{
      imageId:{type:'integer',default:0},
      imageUrl:{type:'string',default:''},
      alt:{type:'string',default:''},
      heading:{type:'string',default:''},
      caption:{type:'string',default:''},
      credit:{type:'string',default:''}
    },
    edit:function(props){
      const a=props.attributes,set=props.setAttributes;
      const blockProps=useBlockProps({className:'tp-gallery-slide-editor'});
      function setImage(image){
        set({
          imageId:parseInt(image.id||0,10)||0,
          imageUrl:image.url||'',
          alt:image.alt||a.alt||''
        });
      }
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Photo details','digipublish-core'),initialOpen:true},
            el(TextControl,{label:__('Alt text','digipublish-core'),value:a.alt||'',onChange:function(v){set({alt:v});}}),
            el(TextControl,{label:__('Photo credit','digipublish-core'),value:a.credit||'',onChange:function(v){set({credit:v});}})
          )
        ),
        el('article',blockProps,
          el(MediaUploadCheck,{},
            el(MediaUpload,{
              onSelect:setImage,
              allowedTypes:['image'],
              value:a.imageId||0,
              render:function(mediaProps){
                return a.imageUrl
                  ? el('button',{type:'button',className:'tp-gallery-slide-editor__image',onClick:mediaProps.open},
                      el('img',{src:a.imageUrl,alt:a.alt||''}))
                  : el(Button,{variant:'secondary',onClick:mediaProps.open},__('Choose image','digipublish-core'));
              }
            })
          ),
          el(RichText,{tagName:'h3',placeholder:__('Optional slide heading…','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
          el(RichText,{tagName:'p',placeholder:__('Write the caption or explanation for this photo…','digipublish-core'),value:a.caption||'',onChange:function(v){set({caption:v});}}),
          el(TextControl,{label:__('Credit','digipublish-core'),value:a.credit||'',onChange:function(v){set({credit:v});}})
        )
      );
    },
    save:function(){return null;}
  });

  registerBlockType('digipublish/gallery-archive', {
    apiVersion:3,
    title:__('Gallery Archive','digipublish-core'),
    category:'digipublish-editorial',
    icon:'images-alt2',
    attributes:{
      heading:{type:'string',default:'Photo Galleries'},
      sourceMode:{type:'string',default:'archive'},
      postsPerPage:{type:'integer',default:12},
      columns:{type:'integer',default:4},
      showFilters:{type:'boolean',default:true},
      showExcerpt:{type:'boolean',default:false},
      showPagination:{type:'boolean',default:true}
    },
    edit:function(props){
      const a=props.attributes,set=props.setAttributes;
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Gallery archive','digipublish-core'),initialOpen:true},
            el(TextControl,{label:__('Heading','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
            el(SelectControl,{label:__('Source','digipublish-core'),value:a.sourceMode||'archive',options:[
              {label:__('Current gallery archive','digipublish-core'),value:'archive'},
              {label:__('Related to current gallery','digipublish-core'),value:'related'},
              {label:__('Latest galleries','digipublish-core'),value:'latest'}
            ],onChange:function(v){set({sourceMode:v});}}),
            el(RangeControl,{label:__('Galleries','digipublish-core'),value:a.postsPerPage||12,min:3,max:24,onChange:function(v){set({postsPerPage:v});}}),
            el(RangeControl,{label:__('Columns','digipublish-core'),value:a.columns||4,min:2,max:5,onChange:function(v){set({columns:v});}}),
            el(ToggleControl,{label:__('Show category filters','digipublish-core'),checked:a.showFilters!==false,onChange:function(v){set({showFilters:v});}}),
            el(ToggleControl,{label:__('Show excerpts','digipublish-core'),checked:!!a.showExcerpt,onChange:function(v){set({showExcerpt:v});}}),
            el(ToggleControl,{label:__('Show pagination','digipublish-core'),checked:a.showPagination!==false,onChange:function(v){set({showPagination:v});}})
          )
        ),
        el(Preview,{name:'digipublish/gallery-archive',attributes:a})
      );
    },
    save:function(){return null;}
  });

  registerBlockVariation('digipublish/gallery',{
    name:'gallery-story',
    title:__('Gallery: Story','digipublish-core'),
    description:__('Server-rendered image + caption sequence.','digipublish-core'),
    icon:'format-gallery',
    scope:['inserter'],
    attributes:{displayMode:'story',showCounter:true,showCaptions:true,showCredits:true,showThumbnails:false,allowFullscreen:true,showSharing:true,adInterval:0}
  });
  registerBlockVariation('digipublish/gallery',{
    name:'gallery-swipe',
    title:__('Gallery: Swipe','digipublish-core'),
    description:__('One-photo-at-a-time swipe gallery with controls.','digipublish-core'),
    icon:'slides',
    scope:['inserter'],
    attributes:{displayMode:'swipe',showCounter:true,showCaptions:true,showCredits:true,showThumbnails:true,allowFullscreen:true,showSharing:true,adInterval:0}
  });
  registerBlockVariation('digipublish/gallery-archive',{
    name:'related-galleries',
    title:__('Related Galleries','digipublish-core'),
    description:__('Gallery cards related to the current photo gallery.','digipublish-core'),
    icon:'images-alt2',
    scope:['inserter'],
    attributes:{heading:'Related Galleries',sourceMode:'related',postsPerPage:4,columns:4,showFilters:false,showExcerpt:false,showPagination:false}
  });

  registerBlockType('digipublish/sidebar-feed', {
    apiVersion: 3,
    title: __('Post Sidebar Feed', 'digipublish-core'),
    category: 'digipublish-editorial',
    icon: 'columns',
    attributes: {
      heading: { type: 'string', default: 'Recent Stories' },
      layout: { type: 'string', default: 'meta-list' },
      sourceMode: { type: 'string', default: 'current' },
      categoryId: { type: 'integer', default: 0 },
      postsToShow: { type: 'integer', default: 5 },
      orderBy: { type: 'string', default: 'date' },
      period: { type: 'string', default: 'all' },
      showHeading: { type: 'boolean', default: true }
    },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes, categoryOptions = useCategoryOptions();
      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Sidebar feed', 'digipublish-core'), initialOpen: true },
            el(TextControl, { label: __('Heading', 'digipublish-core'), value: a.heading || '', onChange: function(v){ set({ heading:v }); } }),
            el(SelectControl, { label: __('Layout', 'digipublish-core'), value: a.layout || 'meta-list', options: [
              { label: __('Recent stories / metadata list', 'digipublish-core'), value:'meta-list' },
              { label: __('Numbered top stories', 'digipublish-core'), value:'ranked-list' },
              { label: __('Featured image mosaic', 'digipublish-core'), value:'image-grid' }
            ], onChange: function(v){ set({ layout:v }); } }),
            el(SelectControl, { label: __('Content source', 'digipublish-core'), value: a.sourceMode || 'current', options: [
              { label: __('Current article category', 'digipublish-core'), value:'current' },
              { label: __('Latest posts', 'digipublish-core'), value:'latest' },
              { label: __('Selected category', 'digipublish-core'), value:'category' }
            ], onChange: function(v){ set({ sourceMode:v }); } }),
            a.sourceMode === 'category' ? el(SelectControl, { label: __('Category', 'digipublish-core'), value: a.categoryId || 0, options: categoryOptions, onChange: function(v){ set({ categoryId:parseInt(v,10)||0 }); } }) : null,
            el(RangeControl, { label: __('Stories', 'digipublish-core'), value: a.postsToShow || 5, min:3, max:12, onChange: function(v){ set({ postsToShow:v }); } }),
            el(SelectControl, { label: __('Order by', 'digipublish-core'), value: a.orderBy || 'date', options: [
              { label: __('Newest', 'digipublish-core'), value:'date' },
              { label: __('Recently updated', 'digipublish-core'), value:'modified' },
              { label: __('Most discussed', 'digipublish-core'), value:'comment_count' },
              { label: __('Alphabetical', 'digipublish-core'), value:'title' }
            ], onChange: function(v){ set({ orderBy:v }); } }),
            el(SelectControl, { label: __('Time period', 'digipublish-core'), value: a.period || 'all', options: [
              { label: __('All time', 'digipublish-core'), value:'all' },
              { label: __('Past 24 hours', 'digipublish-core'), value:'day' },
              { label: __('Past 7 days', 'digipublish-core'), value:'week' },
              { label: __('Past 30 days', 'digipublish-core'), value:'month' }
            ], onChange: function(v){ set({ period:v }); } }),
            el(ToggleControl, { label: __('Show heading', 'digipublish-core'), checked: a.showHeading !== false, onChange: function(v){ set({ showHeading:v }); } })
          )
        ),
        el(Preview, { name:'digipublish/sidebar-feed', attributes:a })
      );
    },
    save: function(){ return null; }
  });

  [
    {
      name:'sidebar-recent-stories',
      title:__('Sidebar: Recent Stories', 'digipublish-core'),
      description:__('Author/date/title list for the post sidebar.', 'digipublish-core'),
      icon:'list-view',
      attributes:{ heading:'Recent Stories', layout:'meta-list', sourceMode:'current', postsToShow:5, orderBy:'date', period:'all', showHeading:true }
    },
    {
      name:'sidebar-top-stories',
      title:__('Sidebar: Top Stories', 'digipublish-core'),
      description:__('Numbered ranking list for the post sidebar.', 'digipublish-core'),
      icon:'editor-ol',
      attributes:{ heading:'Top Stories', layout:'ranked-list', sourceMode:'latest', postsToShow:5, orderBy:'comment_count', period:'week', showHeading:true }
    },
    {
      name:'sidebar-visual-stories',
      title:__('Sidebar: Visual Stories', 'digipublish-core'),
      description:__('Featured-image mosaic for the post sidebar.', 'digipublish-core'),
      icon:'format-gallery',
      attributes:{ heading:'Visual Stories', layout:'image-grid', sourceMode:'latest', postsToShow:12, orderBy:'date', period:'all', showHeading:true }
    }
  ].forEach(function(variation){
    registerBlockVariation('digipublish/sidebar-feed', Object.assign({ scope:['inserter'] }, variation));
  });

  registerContextBlock('digipublish/archive-hero', __('Archive Hero', 'digipublish-core'), 'welcome-widgets-menus', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive hero', 'digipublish-core') }, el(ToggleControl, { label: __('Show search', 'digipublish-core'), checked: !!p.attributes.showSearch, onChange: function (v) { p.setAttributes({ showSearch: v }); } }), el(ToggleControl, { label: __('Show featured terms', 'digipublish-core'), checked: !!p.attributes.showFeaturedTerms, onChange: function (v) { p.setAttributes({ showFeaturedTerms: v }); } }))); });
  registerContextBlock('digipublish/archive-feed', __('Archive Story Feed', 'digipublish-core'), 'grid-view', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive feed', 'digipublish-core') }, el(RangeControl, { label: __('Posts per page', 'digipublish-core'), min: 5, max: 20, value: p.attributes.postsPerPage || 10, onChange: function (v) { p.setAttributes({ postsPerPage: v }); } }), el(RangeControl, { label: __('Columns', 'digipublish-core'), min: 2, max: 5, value: p.attributes.columns || 5, onChange: function (v) { p.setAttributes({ columns: v }); } }), el(ToggleControl, { label: __('Show category top picks', 'digipublish-core'), checked: !!p.attributes.showTopPicks, onChange: function (v) { p.setAttributes({ showTopPicks: v }); } }))); });
  registerContextBlock('digipublish/popular-categories', __('Popular Categories', 'digipublish-core'), 'category', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Popular categories', 'digipublish-core') }, el(TextControl, { label: __('Heading', 'digipublish-core'), value: p.attributes.heading || '', onChange: function (v) { p.setAttributes({ heading: v }); } }), el(RangeControl, { label: __('Categories', 'digipublish-core'), min: 4, max: 12, value: p.attributes.limit || 8, onChange: function (v) { p.setAttributes({ limit: v }); } }))); });
  ['digipublish/author-profile', 'digipublish/category-experts', 'digipublish/related-posts', 'digipublish/post-author-card', 'digipublish/article-toc', 'digipublish/article-byline'].forEach(function (name) { registerContextBlock(name, name.split('/')[1].replace(/-/g, ' '), 'admin-post'); });
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
    if (state.postType !== 'post' && state.postType !== 'digipublish_gallery') return null;

    const type = state.meta._techpress_attribution_type || '';
    const userId = parseInt(state.meta._techpress_attribution_user || 0, 10);
    const userOptions = [{ label: __('Select person', 'digipublish-core'), value: 0 }];
    if (Array.isArray(users)) users.forEach(function (user) { userOptions.push({ label: user.name, value: user.id }); });

    function updateMeta(patch) {
      wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, state.meta, patch) });
    }

    return el(PluginDocumentSettingPanel, { name: 'digipublish-editorial-attribution', title: __('Editorial Attribution', 'digipublish-core'), className: 'digipublish-editorial-attribution' },
      el(SelectControl, {
        label: __('Secondary credit', 'digipublish-core'), value: type,
        options: [
          { label: __('None', 'digipublish-core'), value: '' },
          { label: __('Fact Checked by', 'digipublish-core'), value: 'fact_checked' },
          { label: __('Verified by', 'digipublish-core'), value: 'verified' },
          { label: __('Reported by', 'digipublish-core'), value: 'reported' }
        ],
        onChange: function (value) { updateMeta({ _techpress_attribution_type: value, _techpress_attribution_user: value ? userId : 0 }); }
      }),
      type ? el(SelectControl, { label: __('Person', 'digipublish-core'), value: userId, options: userOptions, onChange: function (value) { updateMeta({ _techpress_attribution_user: parseInt(value, 10) || 0 }); } }) : null,
      type && !userId ? el(Notice, { status: 'warning', isDismissible: false }, __('Select the person who should receive this credit.', 'digipublish-core')) : null
    );
  }

  registerPlugin('digipublish-editorial-attribution', { render: EditorialAttributionPanel, icon: 'admin-users' });
})(window.wp);
