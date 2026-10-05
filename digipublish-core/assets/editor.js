(function (wp) {
  'use strict';
  if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const Fragment = wp.element.Fragment;
  const { registerBlockType, registerBlockVariation, getBlockType, createBlock } = wp.blocks;
  const { InspectorControls, useBlockProps, InnerBlocks, MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor;
  const { PanelBody, TextControl, TextareaControl, RangeControl, SelectControl, ToggleControl, Notice, Button, FormTokenField } = wp.components;
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


  function useTagOptions() {
    const tags = useSelect(function (select) {
      return select('core').getEntityRecords('taxonomy', 'post_tag', { per_page: 100, orderby: 'name', order: 'asc' });
    }, []);
    const options = [];
    if (Array.isArray(tags)) {
      tags.forEach(function (tag) { options.push({ label: tag.name, value: tag.id }); });
    }
    return options;
  }

  function usePostTypeOptions() {
    const types = useSelect(function (select) {
      return select('core').getPostTypes({ per_page: -1 });
    }, []);
    const options = [{ label: __('Posts', 'digipublish-core'), value: 'post' }];
    if (Array.isArray(types)) {
      types.forEach(function (type) {
        if (!type || !type.slug || type.slug === 'post' || type.slug === 'attachment' || type.slug === 'wp_block' || type.viewable === false) return;
        options.push({ label: type.name || type.slug, value: type.slug });
      });
    }
    return options;
  }

  function useTaxonomyOptions(postType) {
    const taxonomies = useSelect(function (select) {
      return select('core').getTaxonomies({ per_page: -1 });
    }, []);
    const options = [{ label: __('Select...', 'digipublish-core'), value: '' }];
    if (Array.isArray(taxonomies)) {
      taxonomies.forEach(function (taxonomy) {
        if (!taxonomy || !taxonomy.slug || taxonomy.slug === 'category' || taxonomy.slug === 'post_tag') return;
        if (Array.isArray(taxonomy.types) && postType && taxonomy.types.indexOf(postType) === -1) return;
        options.push({ label: taxonomy.name || taxonomy.slug, value: taxonomy.slug });
      });
    }
    return options;
  }

  function useTermOptions(taxonomy) {
    const terms = useSelect(function (select) {
      if (!taxonomy) return [];
      return select('core').getEntityRecords('taxonomy', taxonomy, { per_page: 100, orderby: 'name', order: 'asc' });
    }, [taxonomy]);
    const options = [];
    if (Array.isArray(terms)) {
      terms.forEach(function (term) { options.push({ label: term.name, value: term.id }); });
    }
    return options;
  }

  function usePostOptions(postType) {
    const type = postType || 'post';
    const posts = useSelect(function (select) {
      return select('core').getEntityRecords('postType', type, { per_page: 100, orderby: 'date', order: 'desc', _fields: 'id,title' });
    }, [type]);
    const options = [];
    if (Array.isArray(posts)) {
      posts.forEach(function (post) {
        const title = post && post.title && post.title.rendered ? post.title.rendered.replace(/<[^>]+>/g, '') : __('Untitled', 'digipublish-core');
        options.push({ label: title + ' (#' + post.id + ')', value: post.id });
      });
    }
    return options;
  }

  function hasAttribute(attributes, key) {
    return Object.prototype.hasOwnProperty.call(attributes, key);
  }

  function setAttribute(set, key, value) {
    const next = {};
    next[key] = value;
    set(next);
  }

  function tokenIdsControl(label, selectedIds, options, onChange, help) {
    const normalized = (Array.isArray(selectedIds) ? selectedIds : []).map(function (id) { return parseInt(id, 10) || 0; }).filter(Boolean);
    const byId = {};
    const byLabel = {};
    (options || []).forEach(function (option) {
      const id = parseInt(option.value, 10) || 0;
      if (!id) return;
      byId[id] = option.label;
      byLabel[option.label] = id;
    });
    const selected = normalized.map(function (id) { return byId[id]; }).filter(Boolean);
    return el(FormTokenField, {
      label: label,
      value: selected,
      suggestions: Object.keys(byLabel),
      help: help || undefined,
      onChange: function (tokens) {
        const ids = (tokens || []).map(function (token) { return byLabel[token] || 0; }).filter(Boolean);
        onChange(Array.from(new Set(ids)));
      }
    });
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
    if (responsiveChildren.length) {
      panels.push(el(PanelBody, { title: __('Responsive Settings', 'digipublish-core'), initialOpen: false }, responsiveChildren));
    }

    return panels.length ? el(InspectorControls, {}, panels) : null;
  }

  function postFormatControl(selectedFormats, onChange) {
    const formats = [
      { label: __('Standard', 'digipublish-core'), value: 'standard' },
      { label: __('Aside', 'digipublish-core'), value: 'aside' },
      { label: __('Gallery', 'digipublish-core'), value: 'gallery' },
      { label: __('Link', 'digipublish-core'), value: 'link' },
      { label: __('Image', 'digipublish-core'), value: 'image' },
      { label: __('Quote', 'digipublish-core'), value: 'quote' },
      { label: __('Status', 'digipublish-core'), value: 'status' },
      { label: __('Video', 'digipublish-core'), value: 'video' },
      { label: __('Audio', 'digipublish-core'), value: 'audio' },
      { label: __('Chat', 'digipublish-core'), value: 'chat' }
    ];
    const byLabel = {}, byValue = {};
    formats.forEach(function (format) { byLabel[format.label] = format.value; byValue[format.value] = format.label; });
    const value = (Array.isArray(selectedFormats) ? selectedFormats : []).map(function (format) { return byValue[format]; }).filter(Boolean);
    return el(FormTokenField, {
      label: __('Filter by Formats', 'digipublish-core'),
      value: value,
      suggestions: formats.map(function (format) { return format.label; }),
      onChange: function (tokens) {
        onChange(Array.from(new Set((tokens || []).map(function (token) { return byLabel[token] || ''; }).filter(Boolean))));
      }
    });
  }

  function postQueryPanelChildren(props) {
    const a = props.attributes;
    const set = props.setAttributes;
    const postType = a.postType || 'post';
    const categoryOptions = useCategoryOptions().filter(function (option) { return parseInt(option.value, 10) > 0; });
    const tagOptions = useTagOptions();
    const postTypeOptions = usePostTypeOptions();
    const taxonomyOptions = useTaxonomyOptions(postType);
    const termOptions = useTermOptions(a.filterTaxonomy || '');
    const postOptions = usePostOptions(postType);
    const selectedCategories = Array.isArray(a.filterCategoryIds) && a.filterCategoryIds.length
      ? a.filterCategoryIds
      : (a.categoryId ? [a.categoryId] : []);
    const children = [];

    if (hasAttribute(a, 'postType')) {
      children.push(el(SelectControl, {
        label: __('Post Type', 'digipublish-core'),
        value: postType,
        options: postTypeOptions,
        onChange: function (v) { set({ postType: v || 'post', filterPostIds: [], filterTaxonomy: '', filterTermIds: [] }); }
      }));
    }
    children.push(
      tokenIdsControl(__('Filter by Categories', 'digipublish-core'), selectedCategories, categoryOptions, function (ids) {
        set({ filterCategoryIds: ids, categoryId: ids.length === 1 ? ids[0] : 0 });
      }),
      tokenIdsControl(__('Filter by Tags', 'digipublish-core'), a.filterTagIds || [], tagOptions, function (ids) { set({ filterTagIds: ids }); }),
      tokenIdsControl(__('Exclude Categories', 'digipublish-core'), a.excludeCategoryIds || [], categoryOptions, function (ids) { set({ excludeCategoryIds: ids }); }),
      tokenIdsControl(__('Exclude Tags', 'digipublish-core'), a.excludeTagIds || [], tagOptions, function (ids) { set({ excludeTagIds: ids }); })
    );
    if (hasAttribute(a, 'postFormats') && postType === 'post') {
      children.push(postFormatControl(a.postFormats || [], function (formats) { set({ postFormats: formats }); }));
    }
    children.push(
      tokenIdsControl(__('Filter by Posts', 'digipublish-core'), a.filterPostIds || [], postOptions, function (ids) { set({ filterPostIds: ids }); }, __('Choose from the latest 100 items.', 'digipublish-core')),
      el(RangeControl, { label: __('Offset', 'digipublish-core'), value: a.offset || 0, min: 0, max: 100, onChange: function (v) { set({ offset: v || 0 }); } }),
      el(SelectControl, {
        label: __('Order by', 'digipublish-core'),
        value: a.orderBy || 'date',
        options: [
          { label: __('Published Date', 'digipublish-core'), value: 'date' },
          { label: __('Modified Date', 'digipublish-core'), value: 'modified' },
          { label: __('Comment Count', 'digipublish-core'), value: 'comment_count' },
          { label: __('Title', 'digipublish-core'), value: 'title' }
        ],
        onChange: function (v) { set({ orderBy: v }); }
      }),
      el(SelectControl, {
        label: __('Order', 'digipublish-core'),
        value: a.order || 'DESC',
        options: [
          { label: __('Descending', 'digipublish-core'), value: 'DESC' },
          { label: __('Ascending', 'digipublish-core'), value: 'ASC' }
        ],
        onChange: function (v) { set({ order: v }); }
      })
    );
    if (hasAttribute(a, 'filterTaxonomy')) {
      children.push(
        el(SelectControl, {
          label: __('Filter by Taxonomy', 'digipublish-core'),
          value: a.filterTaxonomy || '',
          options: taxonomyOptions,
          onChange: function (v) { set({ filterTaxonomy: v || '', filterTermIds: [] }); }
        }),
        a.filterTaxonomy ? tokenIdsControl(__('Filter by Terms', 'digipublish-core'), a.filterTermIds || [], termOptions, function (ids) { set({ filterTermIds: ids }); }) : null
      );
    }
    if (hasAttribute(a, 'relatedPosts')) {
      children.push(el(ToggleControl, {
        label: __('Display Related Posts', 'digipublish-core'),
        help: __('Changes will be visible on frontend only. When enabled on a single post, results are filtered by the current post categories.', 'digipublish-core'),
        checked: !!a.relatedPosts,
        onChange: function (v) { set({ relatedPosts: v }); }
      }));
    }
    children.push(el(ToggleControl, {
      label: __('Avoid Duplicate Posts', 'digipublish-core'),
      help: __('Changes will be visible on frontend only. Avoid items already emitted by compatible DigiPublish feed blocks earlier on the page.', 'digipublish-core'),
      checked: !!a.avoidDuplicates,
      onChange: function (v) { set({ avoidDuplicates: v }); }
    }));
    return children;
  }

  function postQueryControls(props) {
    return el(InspectorControls, {},
      el(PanelBody, { title: __('Query Settings', 'digipublish-core'), initialOpen: false }, postQueryPanelChildren(props))
    );
  }

  function normalizedPostLayout(layout) {
    if (layout === 'list') return 'horizontal-1';
    if (/^grid-[2-5]$/.test(layout || '')) return 'standard-1';
    return layout || 'standard-1';
  }

  function postFeedLayoutOptions() {
    return [
      { label:'Standard 1', value:'standard-1', group:'Standard' },
      { label:'Standard 2', value:'standard-2', group:'Standard' },
      { label:'Standard 3', value:'standard-3', group:'Standard' },
      { label:'Standard 4', value:'standard-4', group:'Standard' },
      { label:'Masonry 1', value:'masonry-1', group:'Masonry' },
      { label:'Horizontal 1', value:'horizontal-1', group:'Horizontal' },
      { label:'Horizontal 2', value:'horizontal-2', group:'Horizontal' },
      { label:'Horizontal 3', value:'horizontal-3', group:'Horizontal' },
      { label:'Horizontal 4', value:'horizontal-4', group:'Horizontal' },
      { label:'Horizontal 5', value:'horizontal-5', group:'Horizontal' },
      { label:'Tile 1', value:'tile-1', group:'Tile' },
      { label:'Tile 2', value:'tile-2', group:'Tile' },
      { label:'Tile 3', value:'tile-3', group:'Tile' },
      { label:'Tile 4', value:'tile-4', group:'Tile' },
      { label:'Carousel 1', value:'carousel-1', group:'Carousel' },
      { label:'Carousel 2', value:'carousel-2', group:'Carousel' }
    ];
  }

  function postFeedLayoutPicker(props) {
    const active = normalizedPostLayout(props.attributes.layout);
    const groups = ['Standard','Masonry','Horizontal','Tile','Carousel'];
    const options = postFeedLayoutOptions();
    return el('div', {},
      groups.map(function (group) {
        const items = options.filter(function (item) { return item.group === group; });
        return el('div', { key:group, style:{ marginBottom:'14px' } },
          el('div', { style:{ fontSize:'11px', fontWeight:'600', marginBottom:'6px', textTransform:'uppercase', opacity:0.72 } }, group),
          el('div', { style:{ display:'grid', gridTemplateColumns:'repeat(2,minmax(0,1fr))', gap:'6px' } },
            items.map(function (item) {
              return el(Button, {
                key:item.value,
                variant:active === item.value ? 'primary' : 'secondary',
                isPressed:active === item.value,
                style:{ justifyContent:'center', minHeight:'36px' },
                onClick:function(){ props.setAttributes({ layout:item.value }); }
              }, item.label);
            })
          )
        );
      })
    );
  }

  function lengthControl(label, value, onChange, placeholder) {
    return el(TextControl, {
      label:label,
      value:value || '',
      placeholder:placeholder || '0px',
      help:__('Use a CSS length such as 12px, 1rem, 5%.', 'digipublish-core'),
      onChange:onChange
    });
  }

  function colorControl(label, value, onChange) {
    return el(TextControl, {
      label: label,
      value: value || '',
      placeholder: '#2D5DE0',
      help: __('Hex color, e.g. #2D5DE0.', 'digipublish-core'),
      onChange: onChange
    });
  }

  function responsiveVisibilityControls(a, set) {
    return [
      el(ToggleControl,{label:__('Hide On Desktop','digipublish-core'),checked:!!a.hideDesktop,onChange:function(v){set({hideDesktop:v});}}),
      el(ToggleControl,{label:__('Hide On Laptop','digipublish-core'),checked:!!a.hideLaptop,onChange:function(v){set({hideLaptop:v});}}),
      el(ToggleControl,{label:__('Hide On Tablet','digipublish-core'),checked:!!a.hideTablet,onChange:function(v){set({hideTablet:v});}}),
      el(ToggleControl,{label:__('Hide On Mobile','digipublish-core'),checked:!!a.hideMobile,onChange:function(v){set({hideMobile:v});}})
    ];
  }

  function postFeedInspectorControls(props) {
    const a = props.attributes, set = props.setAttributes;
    const defaultColumns = /^grid-([2-5])$/.test(a.layout || '') ? parseInt((a.layout || '').replace('grid-',''),10) : 4;
    return el(InspectorControls, {},
      el(PanelBody, { title:__('Layout', 'digipublish-core'), initialOpen:true }, postFeedLayoutPicker(props)),
      el(PanelBody, { title:__('Block Settings', 'digipublish-core'), initialOpen:true },
        el(TextControl, { label:__('Section Heading', 'digipublish-core'), value:a.heading || '', onChange:function(v){ set({heading:v}); } }),
        el(SelectControl, {
          label:__('Pagination Type', 'digipublish-core'),
          value:a.paginationType || 'none',
          options:[
            { label:__('None', 'digipublish-core'), value:'none' },
            { label:__('Standard', 'digipublish-core'), value:'numbers' },
            { label:__('Load More', 'digipublish-core'), value:'ajax' },
            { label:__('Infinite Load', 'digipublish-core'), value:'infinite' }
          ],
          onChange:function(v){ set({paginationType:v}); }
        }),
        el(RangeControl, { label:__('Posts Count', 'digipublish-core'), value:a.postsToShow || 6, min:1, max:24, onChange:function(v){ set({postsToShow:v || 1}); } }),
        el(RangeControl, { label:__('Number of Columns', 'digipublish-core'), value:a.columnsDesktop || defaultColumns, min:1, max:6, onChange:function(v){ set({columnsDesktop:v || 1}); } }),
        el(RangeControl, { label:__('Columns — Tablet', 'digipublish-core'), value:a.columnsTablet || Math.min(2,a.columnsDesktop || defaultColumns), min:1, max:6, onChange:function(v){ set({columnsTablet:v || 1}); } }),
        el(RangeControl, { label:__('Columns — Mobile', 'digipublish-core'), value:a.columnsMobile || 1, min:1, max:3, onChange:function(v){ set({columnsMobile:v || 1}); } }),
        lengthControl(__('Gap between Columns', 'digipublish-core'), a.columnGap, function(v){ set({columnGap:v}); }, '40px'),
        lengthControl(__('Gap between Rows', 'digipublish-core'), a.rowGap, function(v){ set({rowGap:v}); }, '40px'),
        lengthControl(__('Border Radius', 'digipublish-core'), a.cardRadius, function(v){ set({cardRadius:v}); }, '12px'),
        lengthControl(__('Card Min Height', 'digipublish-core'), a.cardMinHeight, function(v){ set({cardMinHeight:v}); }, __('Auto', 'digipublish-core')),
        /^(standard-[1-4]|horizontal-[1-3])$/.test(normalizedPostLayout(a.layout)) ? lengthControl(__('Margin Content', 'digipublish-core'), a.contentGap, function(v){ set({contentGap:v}); }, '16px') : null,
        /^horizontal-[1-3]$/.test(normalizedPostLayout(a.layout)) ? el(SelectControl,{label:__('Vertical Align Content','digipublish-core'),value:a.contentAlign||'space-between',options:[{label:__('Top','digipublish-core'),value:'flex-start'},{label:__('Center','digipublish-core'),value:'center'},{label:__('Bottom','digipublish-core'),value:'flex-end'},{label:__('Space Between','digipublish-core'),value:'space-between'}],onChange:function(v){set({contentAlign:v});}}) : null,
        /^horizontal-1$/.test(normalizedPostLayout(a.layout)) ? el(SelectControl,{label:__('Vertical Align Image','digipublish-core'),value:a.imageAlign||'flex-start',options:[{label:__('Top','digipublish-core'),value:'flex-start'},{label:__('Center','digipublish-core'),value:'center'},{label:__('Bottom','digipublish-core'),value:'flex-end'},{label:__('Stretch','digipublish-core'),value:'stretch'}],onChange:function(v){set({imageAlign:v});}}) : null,
        /^horizontal-[1-3]$/.test(normalizedPostLayout(a.layout)) ? el(SelectControl,{label:__('Image Width','digipublish-core'),value:a.imageWidth||(normalizedPostLayout(a.layout)==='horizontal-3'?'half':'one-third'),options:[{label:__('One Fourth','digipublish-core'),value:'one-fourth'},{label:__('One Third','digipublish-core'),value:'one-third'},{label:__('Half','digipublish-core'),value:'half'}],onChange:function(v){set({imageWidth:v});}}) : null,
        /^(standard-[1-4]|horizontal-[1-3]|masonry-1)$/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl,{label:__('Enable post format','digipublish-core'),checked:a.showPostFormat!==false,onChange:function(v){set({showPostFormat:v});}}) : null,
        /^(standard-[1-4]|horizontal-3|masonry-1|tile-[12])$/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl,{label:__('Enable video backgrounds','digipublish-core'),checked:!!a.enableVideoBackgrounds,onChange:function(v){set({enableVideoBackgrounds:v});}}) : null,
        a.enableVideoBackgrounds && /^(standard-[1-4]|horizontal-3|masonry-1|tile-[12])$/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl,{label:__('Enable video controls','digipublish-core'),checked:!!a.enableVideoControls,onChange:function(v){set({enableVideoControls:v});}}) : null,
        /^carousel-/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl, { label:__('Enable autoplay', 'digipublish-core'), checked:a.carouselAutoplay !== false, onChange:function(v){ set({carouselAutoplay:v}); } }) : null,
        /^carousel-/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl, { label:__('Enable bullets', 'digipublish-core'), checked:a.carouselDots !== false, onChange:function(v){ set({carouselDots:v}); } }) : null,
        /^carousel-/.test(normalizedPostLayout(a.layout)) ? el(ToggleControl, { label:__('Enable wrap-around', 'digipublish-core'), checked:a.carouselWrap !== false, help:__('At the end of items, wrap around to the other end.', 'digipublish-core'), onChange:function(v){ set({carouselWrap:v}); } }) : null
      ),
      el(PanelBody, { title:__('Meta Settings', 'digipublish-core'), initialOpen:false },
        /^(standard-4|tile-[1-4]|carousel-[12])$/.test(normalizedPostLayout(a.layout)) ? el(SelectControl, {
          label:__('Top Meta Type', 'digipublish-core'),
          value:a.topMetaType || 'none',
          options:[
            { label:__('None', 'digipublish-core'), value:'none' },
            { label:__('Author', 'digipublish-core'), value:'author' },
            { label:__('Category', 'digipublish-core'), value:'category' },
            { label:__('Count', 'digipublish-core'), value:'count' }
          ],
          onChange:function(v){ set({topMetaType:v}); }
        }) : null,
        el(ToggleControl, { label:__('Category', 'digipublish-core'), checked:!!a.showCategory, onChange:function(v){ set({showCategory:v}); } }),
        el(ToggleControl, { label:__('Author', 'digipublish-core'), checked:!!a.showAuthor, onChange:function(v){ set({showAuthor:v}); } }),
        el(ToggleControl, { label:__('Date', 'digipublish-core'), checked:!!a.showDate, onChange:function(v){ set({showDate:v}); } }),
        el(ToggleControl, { label:__('Comments', 'digipublish-core'), checked:!!a.showComments, onChange:function(v){ set({showComments:v}); } }),
        el(ToggleControl, { label:__('Views', 'digipublish-core'), checked:!!a.showViews, onChange:function(v){ set({showViews:v}); } }),
        el(ToggleControl, { label:__('Reading Time', 'digipublish-core'), checked:!!a.showReadTime, onChange:function(v){ set({showReadTime:v}); } }),
        el(ToggleControl, { label:__('Shares', 'digipublish-core'), checked:!!a.showShares, onChange:function(v){ set({showShares:v}); } }),
        el(ToggleControl, { label:__('Display compact post meta', 'digipublish-core'), checked:!!a.compactMeta, onChange:function(v){ set({compactMeta:v}); } }),
        el(ToggleControl, { label:__('Display post excerpt', 'digipublish-core'), checked:!!a.showExcerpt, onChange:function(v){ set({showExcerpt:v}); } }),
        a.showExcerpt ? el(RangeControl, { label:__('Excerpt length', 'digipublish-core'), value:a.excerptLength || 100, min:1, max:1000, onChange:function(v){ set({excerptLength:v || 100}); } }) : null,
        el(ToggleControl, { label:__('Display read more button', 'digipublish-core'), checked:!!a.showReadMore, onChange:function(v){ set({showReadMore:v}); } }),
        a.showReadMore ? el(TextControl, { label:__('More Button Label', 'digipublish-core'), value:a.readMoreLabel || __('Read more','digipublish-core'), onChange:function(v){ set({readMoreLabel:v}); } }) : null
      ),
      el(PanelBody, { title:__('Typography Settings', 'digipublish-core'), initialOpen:false },
        lengthControl(__('Heading Font Size', 'digipublish-core'), a.cardHeadingFontSize, function(v){ set({cardHeadingFontSize:v}); }, '1rem'),
        el(SelectControl, {
          label:__('Heading Tag', 'digipublish-core'), value:a.cardHeadingTag || 'h2',
          options:['h1','h2','h3','h4','h5','h6','p','div'].map(function(tag){ return {label:tag.toUpperCase(),value:tag}; }),
          onChange:function(v){ set({cardHeadingTag:v}); }
        }),
        a.showExcerpt ? lengthControl(__('Excerpt Font Size', 'digipublish-core'), a.excerptFontSize, function(v){ set({excerptFontSize:v}); }, '0.875rem') : null
      ),
      el(PanelBody, { title:__('Thumbnail Settings', 'digipublish-core'), initialOpen:false },
        el(ToggleControl, { label:__('Display thumbnail', 'digipublish-core'), checked:a.showImage !== false, onChange:function(v){ set({showImage:v}); } }),
        el(SelectControl, {
          label:__('Images Size', 'digipublish-core'), value:a.imageSize || 'medium_large',
          options:[
            {label:__('Thumbnail', 'digipublish-core'),value:'thumbnail'},
            {label:__('Medium', 'digipublish-core'),value:'medium'},
            {label:__('Medium Large', 'digipublish-core')+' [800px, ~]',value:'medium_large'},
            {label:__('Large', 'digipublish-core'),value:'large'},
            {label:__('Full', 'digipublish-core'),value:'full'}
          ], onChange:function(v){ set({imageSize:v}); }
        }),
        el(SelectControl, {
          label:__('Image Orientation', 'digipublish-core'), value:a.imageOrientation || 'original',
          options:[
            {label:__('Original', 'digipublish-core'),value:'original'},
            {label:__('Stretch', 'digipublish-core'),value:'stretch'},
            {label:__('Landscape 4:3', 'digipublish-core'),value:'landscape'},
            {label:__('Landscape 3:2', 'digipublish-core'),value:'landscape-3-2'},
            {label:__('Landscape 16:9', 'digipublish-core'),value:'landscape-16-9'},
            {label:__('Landscape 21:10', 'digipublish-core'),value:'landscape-21-10'},
            {label:__('Portrait 3:4', 'digipublish-core'),value:'portrait'},
            {label:__('Portrait 2:3', 'digipublish-core'),value:'portrait-2-3'},
            {label:__('Square', 'digipublish-core'),value:'square'}
          ], onChange:function(v){ set({imageOrientation:v}); }
        }),
        lengthControl(__('Image Border Radius', 'digipublish-core'), a.imageBorderRadius, function(v){ set({imageBorderRadius:v}); }, '12px')
      ),
      el(PanelBody, { title:__('Color Settings', 'digipublish-core'), initialOpen:false },
        colorControl(__('Heading Color','digipublish-core'),a.headingColor,function(v){set({headingColor:v});}),
        colorControl(__('Heading Color Hover','digipublish-core'),a.headingHoverColor,function(v){set({headingHoverColor:v});}),
        colorControl(__('Excerpt','digipublish-core'),a.excerptColor,function(v){set({excerptColor:v});}),
        colorControl(__('Post Meta','digipublish-core'),a.metaColor,function(v){set({metaColor:v});}),
        colorControl(__('Post Meta Links','digipublish-core'),a.metaLinksColor,function(v){set({metaLinksColor:v});}),
        colorControl(__('Post Meta Links Hover','digipublish-core'),a.metaLinksHoverColor,function(v){set({metaLinksHoverColor:v});}),
        colorControl(__('Category Color','digipublish-core'),a.categoryColor,function(v){set({categoryColor:v});}),
        colorControl(__('Category Hover Color','digipublish-core'),a.categoryHoverColor,function(v){set({categoryHoverColor:v});}),
        colorControl(__('Read More Text Color','digipublish-core'),a.readMoreColor,function(v){set({readMoreColor:v});}),
        colorControl(__('Read More Text Color Hover','digipublish-core'),a.readMoreHoverColor,function(v){set({readMoreHoverColor:v});}),
        /^horizontal-[45]$/.test(normalizedPostLayout(a.layout)) ? colorControl(__('Border Color','digipublish-core'),a.borderColor,function(v){set({borderColor:v});}) : null
      ),
      normalizedPostLayout(a.layout)==='masonry-1' ? el(PanelBody,{title:__('Masonry Widgets','digipublish-core'),initialOpen:false},
        el(ToggleControl,{label:__('Display widgets in archive','digipublish-core'),checked:!!a.masonryWidgets,onChange:function(v){set({masonryWidgets:v});}}),
        a.masonryWidgets ? el(TextControl,{label:__('Widget Area','digipublish-core'),value:a.masonryWidgetArea||'sidebar-archive',onChange:function(v){set({masonryWidgetArea:v});}}) : null,
        a.masonryWidgets ? el(RangeControl,{label:__('Display widgets after N-th post','digipublish-core'),value:a.masonryWidgetsAfter||3,min:1,max:20,onChange:function(v){set({masonryWidgetsAfter:v||1});}}) : null,
        a.masonryWidgets ? el(ToggleControl,{label:__('Repeat widgets','digipublish-core'),checked:!!a.masonryWidgetsRepeat,onChange:function(v){set({masonryWidgetsRepeat:v});}}) : null
      ) : null,
      el(PanelBody, { title:__('Query Settings', 'digipublish-core'), initialOpen:false }, postQueryPanelChildren(props)),
      el(PanelBody, { title:__('Spacings', 'digipublish-core'), initialOpen:false },
        el('strong', {}, __('Margins', 'digipublish-core')),
        lengthControl(__('Top', 'digipublish-core'), a.marginTop, function(v){ set({marginTop:v}); }),
        lengthControl(__('Bottom', 'digipublish-core'), a.marginBottom, function(v){ set({marginBottom:v}); }),
        lengthControl(__('Left', 'digipublish-core'), a.marginLeft, function(v){ set({marginLeft:v}); }),
        lengthControl(__('Right', 'digipublish-core'), a.marginRight, function(v){ set({marginRight:v}); }),
        el('strong', {}, __('Paddings', 'digipublish-core')),
        lengthControl(__('Top', 'digipublish-core'), a.paddingTop, function(v){ set({paddingTop:v}); }),
        lengthControl(__('Bottom', 'digipublish-core'), a.paddingBottom, function(v){ set({paddingBottom:v}); }),
        lengthControl(__('Left', 'digipublish-core'), a.paddingLeft, function(v){ set({paddingLeft:v}); }),
        lengthControl(__('Right', 'digipublish-core'), a.paddingRight, function(v){ set({paddingRight:v}); })
      ),
      el(PanelBody, { title:__('Borders', 'digipublish-core'), initialOpen:false },
        lengthControl(__('Radius', 'digipublish-core'), a.blockBorderRadius, function(v){ set({blockBorderRadius:v}); }, '0px'),
        el(SelectControl, {
          label:__('Border', 'digipublish-core'), value:a.blockBorderStyle || 'none',
          options:[
            {label:__('None', 'digipublish-core'),value:'none'},{label:__('Solid', 'digipublish-core'),value:'solid'},
            {label:__('Dashed', 'digipublish-core'),value:'dashed'},{label:__('Dotted', 'digipublish-core'),value:'dotted'},
            {label:__('Double', 'digipublish-core'),value:'double'}
          ], onChange:function(v){ set({blockBorderStyle:v}); }
        }),
        a.blockBorderStyle && a.blockBorderStyle !== 'none' ? lengthControl(__('Border Width', 'digipublish-core'), a.blockBorderWidth, function(v){ set({blockBorderWidth:v}); }, '1px') : null
      ),
      el(PanelBody, { title:__('Responsive Settings', 'digipublish-core'), initialOpen:false },
        el(ToggleControl, { label:__('Hide On Desktop', 'digipublish-core'), checked:!!a.hideDesktop, onChange:function(v){ set({hideDesktop:v}); } }),
        el(ToggleControl, { label:__('Hide On Laptop', 'digipublish-core'), checked:!!a.hideLaptop, onChange:function(v){ set({hideLaptop:v}); } }),
        el(ToggleControl, { label:__('Hide On Tablet', 'digipublish-core'), checked:!!a.hideTablet, onChange:function(v){ set({hideTablet:v}); } }),
        el(ToggleControl, { label:__('Hide On Mobile', 'digipublish-core'), checked:!!a.hideMobile, onChange:function(v){ set({hideMobile:v}); } })
      ),
      el(PanelBody, { title:__('Advanced', 'digipublish-core'), initialOpen:false },
        el(Notice, { status:'info', isDismissible:false }, __('HTML anchor and Additional CSS class(es) are available in WordPress block Advanced settings. The field below adds safe inline CSS declarations to this Posts block only.', 'digipublish-core')),
        el(TextareaControl, {
          label:__('Additional CSS', 'digipublish-core'),
          help:__('Add declarations only, for example: color: red; background: #fff;', 'digipublish-core'),
          value:a.customCss || '',
          onChange:function(v){ set({customCss:v}); }
        })
      )
    );
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
    if (config.meta !== false) ['showImage', 'showExcerpt', 'showAuthor', 'showDate'].forEach(function (key) {
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
    attributes: { heading: { type: 'string', default: 'Latest Features' }, categoryId: { type: 'integer', default: 0 }, postsToShow: { type: 'integer', default: 7 }, layout: { type: 'string', default: 'magazine' }, showExcerpt: { type: 'boolean', default: true }, showAuthor: { type: 'boolean', default: true }, showDate: { type: 'boolean', default: true }, showCategory: { type: 'boolean', default: true }, showComments: { type: 'boolean', default: false }, showReadTime: { type: 'boolean', default: false }, showViews: { type: 'boolean', default: false }, showShares: { type: 'boolean', default: false }, showFilters: { type: 'boolean', default: true }, filterLimit: { type: 'integer', default: 6 }, orderBy: { type:'string', default:'date' }, order: { type:'string', default:'DESC' }, offset:{type:'integer',default:0}, filterCategoryIds:{type:'array',default:[]}, filterTagIds:{type:'array',default:[]}, excludeCategoryIds:{type:'array',default:[]}, excludeTagIds:{type:'array',default:[]}, filterPostIds:{type:'array',default:[]}, avoidDuplicates:{type:'boolean',default:false}, columnGap:{type:'string',default:''}, rowGap:{type:'string',default:''}, cardRadius:{type:'string',default:''}, cardMinHeight:{type:'string',default:''}, headingFontSize:{type:'string',default:''}, headingTag:{type:'string',default:'h2'}, imageSize:{type:'string',default:''}, imageAspect:{type:'string',default:''}, hideDesktop:{type:'boolean',default:false}, hideLaptop:{type:'boolean',default:false}, hideTablet:{type:'boolean',default:false}, hideMobile:{type:'boolean',default:false} },
    edit: function (props) {
      const a = props.attributes, set = props.setAttributes;
      return el(Fragment, {}, editorialControls(props, { meta:false, layouts: [{ label: __('Magazine / Tech publication', 'digipublish-core'), value: 'magazine' }, { label: __('Lead + supporting list', 'digipublish-core'), value: 'lead-list' }, { label: __('3-column grid', 'digipublish-core'), value: 'grid-3' }, { label: __('4-column grid', 'digipublish-core'), value: 'grid-4' }] }),
        el(InspectorControls, {}, el(PanelBody, { title: __('Feature navigation', 'digipublish-core'), initialOpen: false },
          el(ToggleControl, { label: __('Show category filters', 'digipublish-core'), checked: !!a.showFilters, onChange: function (v) { set({ showFilters: v }); } }),
          el(RangeControl, { label: __('Filter links', 'digipublish-core'), value: a.filterLimit || 6, min: 3, max: 8, onChange: function (v) { set({ filterLimit: v }); } })
        )),
        sharedDesignControls(props, { columns:false, metaKeys:['showCategory','showExcerpt','showAuthor','showDate'], defaultColumnGap:'20px', defaultRowGap:'20px', defaultRadius:'4px', defaultHeadingSize:'22px' }),
        postQueryControls(props),
        el(Preview, { name: 'digipublish/featured-posts', attributes: a }));
    }, save: function () { return null; }
  });

  registerBlockType('digipublish/post-feed', {
    apiVersion: 3, title: __('Posts', 'digipublish-core'), category: 'digipublish-editorial', icon: 'screenoptions',
    supports: { align: ['wide','full'], html: false, anchor: true, customClassName: true, spacing: { margin: true, padding: true }, border: { radius: true, color: true, width: true, style: true } },
    attributes: {
      heading: { type: 'string', default: 'Latest' },
      categoryId: { type: 'integer', default: 0 },
      postsToShow: { type: 'integer', default: 6 },
      layout: { type: 'string', default: 'grid-3' },
      paginationType: { type: 'string', default: 'none' },
      postType: { type:'string', default:'post' },
      orderBy: { type: 'string', default: 'date' },
      order: { type: 'string', default: 'DESC' },
      offset: { type: 'integer', default: 0 },
      filterCategoryIds: { type: 'array', default: [], items: { type: 'integer' } },
      filterTagIds: { type: 'array', default: [], items: { type: 'integer' } },
      excludeCategoryIds: { type: 'array', default: [], items: { type: 'integer' } },
      excludeTagIds: { type: 'array', default: [], items: { type: 'integer' } },
      filterPostIds: { type: 'array', default: [], items: { type: 'integer' } },
      postFormats: { type:'array', default:[], items:{type:'string'} },
      filterTaxonomy: { type:'string', default:'' },
      filterTermIds: { type:'array', default:[], items:{type:'integer'} },
      relatedPosts: { type:'boolean', default:false },
      avoidDuplicates: { type: 'boolean', default: false },
      showImage: { type: 'boolean', default: true },
      showCategory: { type: 'boolean', default: true },
      showExcerpt: { type: 'boolean', default: false },
      showAuthor: { type: 'boolean', default: true },
      showDate: { type: 'boolean', default: true },
      showComments: { type: 'boolean', default: false },
      showReadTime: { type: 'boolean', default: false },
      showViews: { type: 'boolean', default: false },
      showShares: { type: 'boolean', default: false },
      showReadMore: { type: 'boolean', default: false },
      readMoreLabel: { type: 'string', default: 'Read more' },
      topMetaType: { type:'string', default:'none' },
      compactMeta: { type:'boolean', default:false },
      columnsDesktop: { type: 'integer', default: 0 },
      columnsTablet: { type: 'integer', default: 0 },
      columnsMobile: { type: 'integer', default: 0 },
      columnGap: { type: 'string', default: '' },
      rowGap: { type: 'string', default: '' },
      cardRadius: { type: 'string', default: '' },
      cardMinHeight: { type: 'string', default: '' },
      headingFontSize: { type: 'string', default: '' },
      headingTag: { type: 'string', default: 'h2' },
      imageSize: { type: 'string', default: 'medium_large' },
      imageAspect: { type: 'string', default: '' },
      imageOrientation: { type:'string', default:'original' },
      imageBorderRadius: { type:'string', default:'' },
      cardHeadingFontSize: { type:'string', default:'' },
      cardHeadingTag: { type:'string', default:'h2' },
      excerptLength: { type:'integer', default:100 },
      excerptFontSize: { type:'string', default:'' },
      carouselAutoplay: { type:'boolean', default:true },
      carouselDots: { type:'boolean', default:true },
      carouselWrap: { type:'boolean', default:true },
      contentGap:{type:'string',default:''},contentAlign:{type:'string',default:'space-between'},imageAlign:{type:'string',default:'flex-start'},imageWidth:{type:'string',default:'one-third'},
      showPostFormat:{type:'boolean',default:true},enableVideoBackgrounds:{type:'boolean',default:false},enableVideoControls:{type:'boolean',default:false},
      headingColor:{type:'string',default:''},headingHoverColor:{type:'string',default:''},excerptColor:{type:'string',default:''},metaColor:{type:'string',default:''},metaLinksColor:{type:'string',default:''},metaLinksHoverColor:{type:'string',default:''},categoryColor:{type:'string',default:''},categoryHoverColor:{type:'string',default:''},readMoreColor:{type:'string',default:''},readMoreHoverColor:{type:'string',default:''},borderColor:{type:'string',default:''},
      masonryWidgets:{type:'boolean',default:false},masonryWidgetArea:{type:'string',default:'sidebar-archive'},masonryWidgetsAfter:{type:'integer',default:3},masonryWidgetsRepeat:{type:'boolean',default:false},
      marginTop:{type:'string',default:''}, marginBottom:{type:'string',default:''}, marginLeft:{type:'string',default:''}, marginRight:{type:'string',default:''},
      paddingTop:{type:'string',default:''}, paddingBottom:{type:'string',default:''}, paddingLeft:{type:'string',default:''}, paddingRight:{type:'string',default:''},
      blockBorderRadius:{type:'string',default:''}, blockBorderStyle:{type:'string',default:'none'}, blockBorderWidth:{type:'string',default:''},
      customCss:{type:'string',default:''},
      hideDesktop: { type: 'boolean', default: false },
      hideLaptop: { type: 'boolean', default: false },
      hideTablet: { type: 'boolean', default: false },
      hideMobile: { type: 'boolean', default: false }
    },
    edit: function (props) {
      return el(Fragment, {},
        postFeedInspectorControls(props),
        el(Preview, { name: 'digipublish/post-feed', attributes: props.attributes })
      );
    },
    save: function () { return null; }
  });

  registerBlockType('digipublish/editorial-feed', {
    apiVersion: 3, title: __('Editorial Feed Engine', 'digipublish-core'), category: 'digipublish-editorial', icon: 'layout',
    supports: { align: ['wide','full'], html: false, anchor: true, spacing: { margin: true, padding: true }, border: { radius: true, color: true, width: true, style: true } },
    attributes: {
      heading: { type: 'string', default: 'Editorial Feed' }, description: { type: 'string', default: '' }, sourceMode: { type: 'string', default: 'latest' }, categoryId: { type: 'integer', default: 0 }, categorySlug: { type: 'string', default: '' }, fillFromLatest: { type: 'boolean', default: false }, manualPostIds: { type: 'string', default: '' }, postsToShow: { type: 'integer', default: 8 }, layout: { type: 'string', default: 'cards-4' }, orderBy: { type: 'string', default: 'date' }, period: { type: 'string', default: 'all' }, avoidDuplicates: { type: 'boolean', default: false }, fallbackRandom: { type: 'boolean', default: false }, showCategory: { type: 'boolean', default: true }, showExcerpt: { type: 'boolean', default: false }, showAuthor: { type: 'boolean', default: false }, showDate: { type: 'boolean', default: false }, showReadTime: { type: 'boolean', default: true }, showViews: { type: 'boolean', default: true }, showShares: { type: 'boolean', default: true }, showViewAll: { type: 'boolean', default: true }, viewAllLabel: { type: 'string', default: 'View All' }, viewAllUrl: { type: 'string', default: '' }, columnGap: { type: 'string', default: '' }, rowGap: { type: 'string', default: '' }, cardRadius: { type: 'string', default: '' }, cardMinHeight: { type: 'string', default: '' }, headingFontSize: { type: 'string', default: '' }, headingTag: { type: 'string', default: 'h2' }, imageSize: { type: 'string', default: '' }, imageAspect: { type: 'string', default: '' }, hideDesktop: { type: 'boolean', default: false }, hideLaptop: { type: 'boolean', default: false }, hideTablet: { type: 'boolean', default: false }, hideMobile: { type: 'boolean', default: false }
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
        sharedDesignControls(props, {
          columns: false,
          meta: false,
          defaultColumnGap: '28px',
          defaultRowGap: '28px',
          defaultRadius: '4px',
          defaultHeadingSize: '28px'
        }),
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
    apiVersion:3,title:__('Category Navigation','digipublish-core'),category:'digipublish-editorial',icon:'menu-alt3',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{
      limit:{type:'integer',default:5},showDictionary:{type:'boolean',default:true},showSearch:{type:'boolean',default:true},filterSlugs:{type:'string',default:''},
      filterCategoryIds:{type:'array',default:[]},orderBy:{type:'string',default:'name'},order:{type:'string',default:'ASC'},maximum:{type:'integer',default:0},alignment:{type:'string',default:'center'},
      hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}
    },
    edit:function(props){
      const a=props.attributes,set=props.setAttributes;
      const categoryOptions=useCategoryOptions().filter(function(option){return parseInt(option.value,10)>0;});
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
            tokenIdsControl(__('Filter by Categories','digipublish-core'),a.filterCategoryIds||[],categoryOptions,function(ids){set({filterCategoryIds:ids});}),
            el(SelectControl,{label:__('Order By','digipublish-core'),value:a.orderBy||'name',options:[
              {label:__('Name','digipublish-core'),value:'name'},{label:__('Posts count','digipublish-core'),value:'count'},
              {label:__('Filter include','digipublish-core'),value:'slug__in'},{label:__('ID','digipublish-core'),value:'id'}
            ],onChange:function(v){set({orderBy:v});}}),
            el(SelectControl,{label:__('Order','digipublish-core'),value:a.order||'ASC',options:[{label:'ASC',value:'ASC'},{label:'DESC',value:'DESC'}],onChange:function(v){set({order:v});}}),
            el(RangeControl,{label:__('Maximum count','digipublish-core'),value:a.maximum||0,min:0,max:1000,onChange:function(v){set({maximum:v||0});}}),
            el(SelectControl,{label:__('Alignment','digipublish-core'),value:a.alignment||'center',options:[{label:__('Left','digipublish-core'),value:'flex-start'},{label:__('Right','digipublish-core'),value:'flex-end'},{label:__('Center','digipublish-core'),value:'center'}],onChange:function(v){set({alignment:v});}}),
            el(ToggleControl,{label:__('Show Dictionary','digipublish-core'),checked:!!a.showDictionary,onChange:function(v){set({showDictionary:v});}}),
            el(ToggleControl,{label:__('Show search','digipublish-core'),checked:!!a.showSearch,onChange:function(v){set({showSearch:v});}})
          ),
          el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
        ),
        el(Preview,{name:'digipublish/category-nav',attributes:a})
      );
    },save:function(){return null;}
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
      edit: function (props) {
        return el(Fragment, {},
          controls ? controls(props) : null,
          sharedDesignControls(props, {
            columns: hasAttribute(props.attributes, 'columnsDesktop'),
            defaultColumns: props.attributes.columns || 4,
            maxColumns: 6,
            defaultColumnGap: '20px',
            defaultRowGap: '20px',
            defaultRadius: '4px',
            defaultHeadingSize: '22px'
          }),
          el(Preview, { name: name, attributes: props.attributes })
        );
      }, save: function () { return null; }
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
            el(RangeControl,{label:__('Insert advertisement every N photos','digipublish-core'),help:__('Story mode only. Set to 0 to disable inline gallery ads.','digipublish-core'),value:a.adInterval||0,min:0,max:10,onChange:function(v){set({adInterval:v||0});}})
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
      showPagination:{type:'boolean',default:true},
      showCategory:{type:'boolean',default:true},showDate:{type:'boolean',default:true},
      columnsDesktop:{type:'integer',default:0},columnsTablet:{type:'integer',default:0},columnsMobile:{type:'integer',default:0},
      columnGap:{type:'string',default:''},rowGap:{type:'string',default:''},cardRadius:{type:'string',default:''},cardMinHeight:{type:'string',default:''},headingFontSize:{type:'string',default:''},headingTag:{type:'string',default:'h2'},imageSize:{type:'string',default:''},imageAspect:{type:'string',default:''},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}
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
        sharedDesignControls(props,{columns:true,defaultColumns:a.columns||4,maxColumns:6,defaultColumnGap:'20px',defaultRowGap:'20px',defaultRadius:'4px',defaultHeadingSize:'22px'}),
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
      contentType: { type: 'string', default: 'post' },
      categoryId: { type: 'integer', default: 0 },
      postsToShow: { type: 'integer', default: 5 },
      orderBy: { type: 'string', default: 'date' },
      period: { type: 'string', default: 'all' },
      showHeading: { type: 'boolean', default: true },
      showAuthor:{type:'boolean',default:true},showDate:{type:'boolean',default:true},
      columnGap:{type:'string',default:''},rowGap:{type:'string',default:''},cardRadius:{type:'string',default:''},cardMinHeight:{type:'string',default:''},headingFontSize:{type:'string',default:''},headingTag:{type:'string',default:'h2'},imageSize:{type:'string',default:''},imageAspect:{type:'string',default:''},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}
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
            el(SelectControl, { label: __('Content type', 'digipublish-core'), value: a.contentType || 'post', options: [
              { label: __('Articles', 'digipublish-core'), value:'post' },
              { label: __('Photo galleries', 'digipublish-core'), value:'gallery' },
              { label: __('Articles + galleries', 'digipublish-core'), value:'mixed' }
            ], onChange: function(v){ set({ contentType:v }); } }),
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
        sharedDesignControls(props,{columns:false,metaKeys:['showAuthor','showDate'],defaultColumnGap:'12px',defaultRowGap:'12px',defaultRadius:'4px',defaultHeadingSize:'18px'}),
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
      attributes:{ heading:'Visual Stories', layout:'image-grid', sourceMode:'latest', contentType:'mixed', postsToShow:12, orderBy:'date', period:'all', showHeading:true }
    },
    {
      name:'sidebar-latest-galleries',
      title:__('Sidebar: Latest Galleries', 'digipublish-core'),
      description:__('Recent photo galleries with photo-count metadata.', 'digipublish-core'),
      icon:'images-alt2',
      attributes:{ heading:'Latest Galleries', layout:'meta-list', sourceMode:'latest', contentType:'gallery', postsToShow:5, orderBy:'date', period:'all', showHeading:true }
    }
  ].forEach(function(variation){
    registerBlockVariation('digipublish/sidebar-feed', Object.assign({ scope:['inserter'] }, variation));
  });

  registerContextBlock('digipublish/archive-hero', __('Archive Hero', 'digipublish-core'), 'welcome-widgets-menus', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive hero', 'digipublish-core') }, el(ToggleControl, { label: __('Show search', 'digipublish-core'), checked: !!p.attributes.showSearch, onChange: function (v) { p.setAttributes({ showSearch: v }); } }), el(ToggleControl, { label: __('Show featured terms', 'digipublish-core'), checked: !!p.attributes.showFeaturedTerms, onChange: function (v) { p.setAttributes({ showFeaturedTerms: v }); } }))); });
  registerContextBlock('digipublish/archive-feed', __('Archive Story Feed', 'digipublish-core'), 'grid-view', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Archive feed', 'digipublish-core') }, el(RangeControl, { label: __('Posts per page', 'digipublish-core'), min: 5, max: 20, value: p.attributes.postsPerPage || 10, onChange: function (v) { p.setAttributes({ postsPerPage: v }); } }), el(RangeControl, { label: __('Columns', 'digipublish-core'), min: 2, max: 5, value: p.attributes.columns || 5, onChange: function (v) { p.setAttributes({ columns: v }); } }), el(ToggleControl, { label: __('Show category top picks', 'digipublish-core'), checked: !!p.attributes.showTopPicks, onChange: function (v) { p.setAttributes({ showTopPicks: v }); } }))); });
  registerContextBlock('digipublish/popular-categories', __('Popular Categories', 'digipublish-core'), 'category', function (p) { return el(InspectorControls, {}, el(PanelBody, { title: __('Popular categories', 'digipublish-core') }, el(TextControl, { label: __('Heading', 'digipublish-core'), value: p.attributes.heading || '', onChange: function (v) { p.setAttributes({ heading: v }); } }), el(RangeControl, { label: __('Categories', 'digipublish-core'), min: 4, max: 12, value: p.attributes.limit || 8, onChange: function (v) { p.setAttributes({ limit: v }); } }))); });
  ['digipublish/author-profile', 'digipublish/category-experts', 'digipublish/post-author-card', 'digipublish/article-toc', 'digipublish/article-byline'].forEach(function (name) { registerContextBlock(name, name.split('/')[1].replace(/-/g, ' '), 'admin-post'); });
  registerContextBlock('digipublish/related-posts', __('Related / Read Next', 'digipublish-core'), 'images-alt2', function (p) {
    return el(InspectorControls, {}, el(PanelBody, { title: __('Related stories', 'digipublish-core'), initialOpen: true },
      el(TextControl, { label: __('Heading', 'digipublish-core'), value: p.attributes.heading || '', onChange: function(v){ p.setAttributes({ heading:v }); } }),
      el(SelectControl, { label: __('Layout', 'digipublish-core'), value: p.attributes.layout || 'features', options: [
        { label: __('Read Next cards', 'digipublish-core'), value:'read-next' },
        { label: __('Related Features', 'digipublish-core'), value:'features' }
      ], onChange: function(v){ p.setAttributes({ layout:v }); } }),
      el(SelectControl, { label: __('Related by', 'digipublish-core'), value: p.attributes.relationMode || 'category-tags', options: [
        { label: __('Categories + tags', 'digipublish-core'), value:'category-tags' },
        { label: __('Categories only', 'digipublish-core'), value:'category' }
      ], onChange: function(v){ p.setAttributes({ relationMode:v }); } }),
      el(RangeControl, { label: __('Stories', 'digipublish-core'), min:3, max:8, value:p.attributes.postsToShow || 4, onChange:function(v){ p.setAttributes({ postsToShow:v }); } }),
      el(ToggleControl, { label: __('Show excerpt', 'digipublish-core'), checked:p.attributes.showExcerpt !== false, onChange:function(v){ p.setAttributes({ showExcerpt:v }); } })
    ));
  });
  registerBlockVariation('digipublish/related-posts', {
    name:'read-next',
    title:__('Read Next', 'digipublish-core'),
    description:__('Four-card category-and-tag related story row for below articles.', 'digipublish-core'),
    icon:'excerpt-view',
    scope:['inserter'],
    attributes:{ heading:'Read next', postsToShow:4, layout:'read-next', relationMode:'category-tags', showExcerpt:true }
  });


  registerBlockType('digipublish/entry-hero', {
    apiVersion:3, title:__('Entry Hero', 'digipublish-core'), category:'digipublish-editorial', icon:'cover-image',
    attributes:{
      layout:{type:'string',default:'auto'},showBreadcrumbs:{type:'boolean',default:true},showCategory:{type:'boolean',default:true},
      showSubtitle:{type:'boolean',default:true},showAuthor:{type:'boolean',default:true},showDate:{type:'boolean',default:true},
      showComments:{type:'boolean',default:true},showViews:{type:'boolean',default:false},showShares:{type:'boolean',default:false},showReadTime:{type:'boolean',default:false}
    },
    edit:function(props){
      const a=props.attributes,set=props.setAttributes;
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Entry Header', 'digipublish-core'),initialOpen:true},
            el(SelectControl,{label:__('Header Type','digipublish-core'),value:a.layout||'auto',options:[
              {label:__('Use post/page setting','digipublish-core'),value:'auto'},{label:__('Standard','digipublish-core'),value:'standard'},
              {label:__('Large','digipublish-core'),value:'large'},{label:__('Full','digipublish-core'),value:'full'},
              {label:__('Page Title Only','digipublish-core'),value:'title'},{label:__('None','digipublish-core'),value:'none'}
            ],onChange:function(v){set({layout:v});}}),
            el(ToggleControl,{label:__('Breadcrumbs','digipublish-core'),checked:a.showBreadcrumbs!==false,onChange:function(v){set({showBreadcrumbs:v});}}),
            el(ToggleControl,{label:__('Category','digipublish-core'),checked:a.showCategory!==false,onChange:function(v){set({showCategory:v});}}),
            el(ToggleControl,{label:__('Excerpt as subtitle','digipublish-core'),checked:a.showSubtitle!==false,onChange:function(v){set({showSubtitle:v});}}),
            el(ToggleControl,{label:__('Author','digipublish-core'),checked:a.showAuthor!==false,onChange:function(v){set({showAuthor:v});}}),
            el(ToggleControl,{label:__('Date','digipublish-core'),checked:a.showDate!==false,onChange:function(v){set({showDate:v});}}),
            el(ToggleControl,{label:__('Comments','digipublish-core'),checked:a.showComments!==false,onChange:function(v){set({showComments:v});}}),
            el(ToggleControl,{label:__('Views','digipublish-core'),checked:!!a.showViews,onChange:function(v){set({showViews:v});}}),
            el(ToggleControl,{label:__('Shares','digipublish-core'),checked:!!a.showShares,onChange:function(v){set({showShares:v});}}),
            el(ToggleControl,{label:__('Reading Time','digipublish-core'),checked:!!a.showReadTime,onChange:function(v){set({showReadTime:v});}})
          )
        ),
        el(Preview,{name:'digipublish/entry-hero',attributes:a})
      );
    },save:function(){return null;}
  });

  registerBlockType('digipublish/current-date', {
    apiVersion:3,title:__('Current Date','digipublish-core'),category:'digipublish-editorial',icon:'calendar-alt',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{format:{type:'string',default:'F d, Y'},textAlign:{type:'string',default:'left'},textColor:{type:'string',default:''},fontSizeDesktop:{type:'string',default:'0.75rem'},fontSizeTablet:{type:'string',default:''},fontSizeMobile:{type:'string',default:''},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}},
    edit:function(props){const a=props.attributes,set=props.setAttributes;return el(Fragment,{},
      el(InspectorControls,{},
        el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
          el(TextControl,{label:__('Format','digipublish-core'),value:a.format||'F d, Y',onChange:function(v){set({format:v});}}),
          el(SelectControl,{label:__('Text Align','digipublish-core'),value:a.textAlign||'left',options:[{label:__('Left','digipublish-core'),value:'left'},{label:__('Right','digipublish-core'),value:'right'},{label:__('Center','digipublish-core'),value:'center'}],onChange:function(v){set({textAlign:v});}})
        ),
        el(PanelBody,{title:__('Color Settings','digipublish-core'),initialOpen:false},colorControl(__('Color','digipublish-core'),a.textColor,function(v){set({textColor:v});})),
        el(PanelBody,{title:__('Typography Settings','digipublish-core'),initialOpen:false},
          lengthControl(__('Font Size — Desktop','digipublish-core'),a.fontSizeDesktop,function(v){set({fontSizeDesktop:v});},'0.75rem'),
          lengthControl(__('Font Size — Tablet','digipublish-core'),a.fontSizeTablet,function(v){set({fontSizeTablet:v});},'0.75rem'),
          lengthControl(__('Font Size — Mobile','digipublish-core'),a.fontSizeMobile,function(v){set({fontSizeMobile:v});},'0.75rem')
        ),
        el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
      ),el(Preview,{name:'digipublish/current-date',attributes:a}));},
    save:function(){return null;}
  });

  registerBlockType('digipublish/custom-link', {
    apiVersion:3,title:__('Custom Link','digipublish-core'),category:'digipublish-editorial',icon:'admin-links',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{
      label:{type:'string',default:'View All'},url:{type:'string',default:'/'},target:{type:'string',default:'_self'},buttonStyle:{type:'boolean',default:false},styleVariant:{type:'string',default:'default'},
      textAlign:{type:'string',default:'left'},disableLabelMobile:{type:'boolean',default:false},textColor:{type:'string',default:''},textHoverColor:{type:'string',default:''},
      circleBackground:{type:'string',default:''},circleColor:{type:'string',default:''},circleHoverBackground:{type:'string',default:''},circleHoverColor:{type:'string',default:''},
      fontSizeDesktop:{type:'string',default:''},fontSizeTablet:{type:'string',default:''},fontSizeMobile:{type:'string',default:''},
      hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}
    },
    edit:function(props){const a=props.attributes,set=props.setAttributes;return el(Fragment,{},
      el(InspectorControls,{},
        el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
          el(TextControl,{label:__('Label','digipublish-core'),value:a.label||'',onChange:function(v){set({label:v});}}),
          el(TextControl,{label:__('URL','digipublish-core'),value:a.url||'',onChange:function(v){set({url:v});}}),
          el(SelectControl,{label:__('Target URL','digipublish-core'),value:a.target||'_self',options:[{label:__('Self','digipublish-core'),value:'_self'},{label:__('Blank','digipublish-core'),value:'_blank'}],onChange:function(v){set({target:v});}}),
          el(SelectControl,{label:__('Style','digipublish-core'),value:a.styleVariant||'default',options:[{label:__('Default','digipublish-core'),value:'default'},{label:__('Styled','digipublish-core'),value:'styled'}],onChange:function(v){set({styleVariant:v,buttonStyle:v==='styled'});}}),
          el(SelectControl,{label:__('Align','digipublish-core'),value:a.textAlign||'left',options:[{label:__('Left','digipublish-core'),value:'left'},{label:__('Center','digipublish-core'),value:'center'},{label:__('Right','digipublish-core'),value:'right'}],onChange:function(v){set({textAlign:v});}}),
          el(ToggleControl,{label:__('Disable Label on Mobile','digipublish-core'),checked:!!a.disableLabelMobile,onChange:function(v){set({disableLabelMobile:v});}})
        ),
        el(PanelBody,{title:__('Color Settings','digipublish-core'),initialOpen:false},
          colorControl(__('Color','digipublish-core'),a.textColor,function(v){set({textColor:v});}),
          colorControl(__('Hover Color','digipublish-core'),a.textHoverColor,function(v){set({textHoverColor:v});}),
          colorControl(__('Circle Background','digipublish-core'),a.circleBackground,function(v){set({circleBackground:v});}),
          colorControl(__('Circle Color','digipublish-core'),a.circleColor,function(v){set({circleColor:v});}),
          colorControl(__('Circle Hover Background Color','digipublish-core'),a.circleHoverBackground,function(v){set({circleHoverBackground:v});}),
          colorControl(__('Circle Hover Color','digipublish-core'),a.circleHoverColor,function(v){set({circleHoverColor:v});})
        ),
        el(PanelBody,{title:__('Typography Settings','digipublish-core'),initialOpen:false},
          lengthControl(__('Font Size — Desktop','digipublish-core'),a.fontSizeDesktop,function(v){set({fontSizeDesktop:v});},'0.875rem'),
          lengthControl(__('Font Size — Tablet','digipublish-core'),a.fontSizeTablet,function(v){set({fontSizeTablet:v});},'0.875rem'),
          lengthControl(__('Font Size — Mobile','digipublish-core'),a.fontSizeMobile,function(v){set({fontSizeMobile:v});},'0.875rem')
        ),
        el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
      ),el(Preview,{name:'digipublish/custom-link',attributes:a}));},save:function(){return null;}
  });

  function socialCarouselEdit(props,type){
    const a=props.attributes,set=props.setAttributes,isInstagram=type==='instagram';
    const layouts=isInstagram
      ? [{label:__('Default','digipublish-core'),value:'default'},{label:__('Carousel','digipublish-core'),value:'carousel'},{label:__('Diagonal Carousel','digipublish-core'),value:'carousel-full'}]
      : [{label:__('Default','digipublish-core'),value:'default'},{label:__('Carousel','digipublish-core'),value:'carousel'}];
    return el(Fragment,{},
      el(InspectorControls,{},
        el(PanelBody,{title:isInstagram?__('Instagram Settings','digipublish-core'):__('Twitter Settings','digipublish-core'),initialOpen:true},
          el(SelectControl,{label:__('Layout','digipublish-core'),value:a.layout||'default',options:layouts,onChange:function(v){set({layout:v});}}),
          el(TextControl,{label:__('Heading','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
          el(TextControl,{label:__('Profile URL','digipublish-core'),value:a.profileUrl||'',onChange:function(v){set({profileUrl:v});}}),
          el(RangeControl,{label:__('Number','digipublish-core'),value:a.number||(isInstagram?6:5),min:1,max:30,onChange:function(v){set({number:v||1});}}),
          a.layout==='default' ? el(RangeControl,{label:__('Visible columns','digipublish-core'),value:a.columns||(isInstagram?5:3),min:1,max:isInstagram?8:5,onChange:function(v){set({columns:v});}}) : null,
          isInstagram ? el(SelectControl,{label:__('Images Size','digipublish-core'),value:a.imageSize||'medium',options:[{label:__('Thumbnail','digipublish-core'),value:'thumbnail'},{label:__('Medium','digipublish-core'),value:'medium'},{label:__('Large','digipublish-core'),value:'large'},{label:__('Full','digipublish-core'),value:'full'}],onChange:function(v){set({imageSize:v});}}) : null,
          isInstagram ? el(SelectControl,{label:__('Target URL','digipublish-core'),value:a.target||'_blank',options:[{label:__('Self','digipublish-core'),value:'_self'},{label:__('Blank','digipublish-core'),value:'_blank'}],onChange:function(v){set({target:v});}}) : null,
          isInstagram && a.layout==='carousel-full' ? lengthControl(__('Card Min Height','digipublish-core'),a.diagonalCardMinHeight,function(v){set({diagonalCardMinHeight:v});},'480px') : null,
          el(ToggleControl,{label:__('Show header','digipublish-core'),checked:a.showHeader!==false,onChange:function(v){set({showHeader:v});}}),
          el(ToggleControl,{label:__('Show follow button','digipublish-core'),checked:a.showFollowButton!==false,onChange:function(v){set({showFollowButton:v});}}),
          el(TextareaControl,{label:isInstagram?__('Items: image URL | post URL | alt text','digipublish-core'):__('Items: text | URL | author | @handle','digipublish-core'),value:a.items||'',rows:8,onChange:function(v){set({items:v});}})
        ),
        el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
      ),
      el(Preview,{name:isInstagram?'digipublish/instagram-carousel':'digipublish/twitter-carousel',attributes:a})
    );
  }
  registerBlockType('digipublish/instagram-carousel',{apiVersion:3,title:__('Instagram','digipublish-core'),category:'digipublish-editorial',icon:'format-gallery',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{heading:{type:'string',default:'Instagram'},profileUrl:{type:'string',default:''},items:{type:'string',default:''},showHeader:{type:'boolean',default:true},showFollowButton:{type:'boolean',default:true},columns:{type:'integer',default:5},layout:{type:'string',default:'default'},number:{type:'integer',default:6},imageSize:{type:'string',default:'medium'},target:{type:'string',default:'_blank'},diagonalCardMinHeight:{type:'string',default:'480px'},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}},
    edit:function(p){return socialCarouselEdit(p,'instagram');},save:function(){return null;}});
  registerBlockType('digipublish/twitter-carousel',{apiVersion:3,title:__('Twitter / X','digipublish-core'),category:'digipublish-editorial',icon:'format-chat',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{heading:{type:'string',default:'Twitter Feed'},profileUrl:{type:'string',default:''},items:{type:'string',default:''},showHeader:{type:'boolean',default:true},showFollowButton:{type:'boolean',default:true},columns:{type:'integer',default:3},layout:{type:'string',default:'default'},number:{type:'integer',default:5},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}},
    edit:function(p){return socialCarouselEdit(p,'twitter');},save:function(){return null;}});
  registerBlockType('digipublish/section-heading',{
    apiVersion:3,title:__('Section Heading','digipublish-core'),category:'digipublish-editorial',icon:'heading',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{text:{type:'string',default:'Section Heading'},level:{type:'integer',default:2},borderColor:{type:'string',default:''},accentColor:{type:'string',default:''},accentContrastColor:{type:'string',default:''},textColor:{type:'string',default:''},styleVariant:{type:'string',default:'style-1'}},
    edit:function(props){
      const a=props.attributes,set=props.setAttributes;
      const styles={};
      if(a.borderColor)styles['--dp-section-heading-border']=a.borderColor;
      if(a.accentColor)styles['--dp-section-heading-accent']=a.accentColor;
      if(a.accentContrastColor)styles['--dp-section-heading-accent-contrast']=a.accentContrastColor;
      if(a.textColor)styles['--dp-section-heading-color']=a.textColor;
      const bp=useBlockProps({className:'is-style-'+(a.styleVariant||'style-1'),style:styles});
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
            el(SelectControl,{label:__('Style','digipublish-core'),value:a.styleVariant||'style-1',options:[{label:__('Style 1','digipublish-core'),value:'style-1'},{label:__('Style 2','digipublish-core'),value:'style-2'}],onChange:function(v){set({styleVariant:v});}}),
            el(SelectControl,{label:__('Heading Level','digipublish-core'),value:a.level||2,options:[2,3,4,5,6].map(function(v){return{label:'H'+v,value:v};}),onChange:function(v){set({level:parseInt(v,10)||2});}})
          ),
          el(PanelBody,{title:__('Color Settings','digipublish-core'),initialOpen:false},
            colorControl(__('Border Color','digipublish-core'),a.borderColor,function(v){set({borderColor:v});}),
            colorControl(__('Accent Color','digipublish-core'),a.accentColor,function(v){set({accentColor:v});}),
            colorControl(__('Accent Contrast Color','digipublish-core'),a.accentContrastColor,function(v){set({accentContrastColor:v});}),
            colorControl(__('Text Color','digipublish-core'),a.textColor,function(v){set({textColor:v});})
          )
        ),
        el('div',bp,el(RichText,{tagName:'h'+(a.level||2),className:'wp-block-digipublish-section-heading__text',value:a.text,onChange:function(v){set({text:v});},placeholder:__('Section Heading','digipublish-core')}))
      );
    },
    save:function(props){
      const a=props.attributes,styles={};
      if(a.borderColor)styles['--dp-section-heading-border']=a.borderColor;
      if(a.accentColor)styles['--dp-section-heading-accent']=a.accentColor;
      if(a.accentContrastColor)styles['--dp-section-heading-accent-contrast']=a.accentContrastColor;
      if(a.textColor)styles['--dp-section-heading-color']=a.textColor;
      const bp=useBlockProps.save({className:'is-style-'+(a.styleVariant||'style-1'),style:styles});
      return el('div',bp,el(RichText.Content,{tagName:'h'+(a.level||2),className:'wp-block-digipublish-section-heading__text',value:a.text}));
    }
  });

  function sectionStyle(a){
    const s={};
    [['gapDesktop','--dp-section-gap-d'],['gapLaptop','--dp-section-gap-l'],['gapTablet','--dp-section-gap-t'],['gapMobile','--dp-section-gap-m'],['sidebarWidthDesktop','--dp-section-sidebar-d'],['sidebarWidthLaptop','--dp-section-sidebar-l'],['sidebarWidthTablet','--dp-section-sidebar-t'],['sidebarWidthMobile','--dp-section-sidebar-m']].forEach(function(pair){if(a[pair[0]])s[pair[1]]=a[pair[0]];});
    return s;
  }
  function sectionClasses(a){
    const list=['wp-block-digipublish-section--'+(a.layout==='left-sidebar'?'left':a.layout==='full'?'full':'right')];
    if(a.hideDesktop)list.push('dp-hide-desktop');if(a.hideLaptop)list.push('dp-hide-laptop');if(a.hideTablet)list.push('dp-hide-tablet');if(a.hideMobile)list.push('dp-hide-mobile');
    return list.join(' ');
  }
  registerBlockType('digipublish/section',{
    apiVersion:3,title:__('Section','digipublish-core'),category:'digipublish-editorial',icon:'columns',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{layout:{type:'string',default:'right-sidebar'},gapDesktop:{type:'string',default:'40px'},gapLaptop:{type:'string',default:'40px'},gapTablet:{type:'string',default:'40px'},gapMobile:{type:'string',default:'40px'},sidebarWidthDesktop:{type:'string',default:'390px'},sidebarWidthLaptop:{type:'string',default:'390px'},sidebarWidthTablet:{type:'string',default:'300px'},sidebarWidthMobile:{type:'string',default:'300px'},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}},
    edit:function(props){const a=props.attributes,set=props.setAttributes,bp=useBlockProps({className:sectionClasses(a),style:sectionStyle(a)});return el(Fragment,{},
      el(InspectorControls,{},
        el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
          el(SelectControl,{label:__('Layout','digipublish-core'),value:a.layout||'right-sidebar',options:[{label:__('Right Sidebar','digipublish-core'),value:'right-sidebar'},{label:__('Left Sidebar','digipublish-core'),value:'left-sidebar'},{label:__('Full Width','digipublish-core'),value:'full'}],onChange:function(v){set({layout:v});}}),
          lengthControl(__('Gap — Desktop','digipublish-core'),a.gapDesktop,function(v){set({gapDesktop:v});},'40px'),
          lengthControl(__('Gap — Laptop','digipublish-core'),a.gapLaptop,function(v){set({gapLaptop:v});},'40px'),
          lengthControl(__('Gap — Tablet','digipublish-core'),a.gapTablet,function(v){set({gapTablet:v});},'40px'),
          lengthControl(__('Gap — Mobile','digipublish-core'),a.gapMobile,function(v){set({gapMobile:v});},'40px'),
          a.layout!=='full'?lengthControl(__('Sidebar Width — Desktop','digipublish-core'),a.sidebarWidthDesktop,function(v){set({sidebarWidthDesktop:v});},'390px'):null,
          a.layout!=='full'?lengthControl(__('Sidebar Width — Laptop','digipublish-core'),a.sidebarWidthLaptop,function(v){set({sidebarWidthLaptop:v});},'390px'):null,
          a.layout!=='full'?lengthControl(__('Sidebar Width — Tablet','digipublish-core'),a.sidebarWidthTablet,function(v){set({sidebarWidthTablet:v});},'300px'):null,
          a.layout!=='full'?lengthControl(__('Sidebar Width — Mobile','digipublish-core'),a.sidebarWidthMobile,function(v){set({sidebarWidthMobile:v});},'300px'):null
        ),
        el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
      ),
      el('div',bp,el('div',{className:'wp-block-digipublish-section__inner'},el(InnerBlocks,{allowedBlocks:['digipublish/section-content','digipublish/section-sidebar'],template:a.layout==='full'?[['digipublish/section-content']]:[['digipublish/section-content'],['digipublish/section-sidebar']],templateLock:false})))
    );},
    save:function(props){const a=props.attributes,bp=useBlockProps.save({className:sectionClasses(a),style:sectionStyle(a)});return el('div',bp,el('div',{className:'wp-block-digipublish-section__inner'},el(InnerBlocks.Content))); }
  });

  function sectionColumnBlock(name,title,icon,className){
    registerBlockType(name,{
      apiVersion:3,title:title,category:'digipublish-editorial',icon:icon,parent:['digipublish/section'],
      supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
      attributes:{textColor:{type:'string',default:''},backgroundColor:{type:'string',default:''}},
      edit:function(props){const a=props.attributes,set=props.setAttributes,styles={};if(a.textColor)styles['--dp-section-'+className+'-color']=a.textColor;if(a.backgroundColor)styles['--dp-section-'+className+'-bg']=a.backgroundColor;const bp=useBlockProps({style:styles});return el(Fragment,{},
        el(InspectorControls,{},el(PanelBody,{title:__('Color Settings','digipublish-core'),initialOpen:true},
          colorControl(__('Text Color','digipublish-core'),a.textColor,function(v){set({textColor:v});}),
          colorControl(__('Background Color','digipublish-core'),a.backgroundColor,function(v){set({backgroundColor:v});})
        )),
        el('div',bp,el(InnerBlocks,{}))
      );},
      save:function(props){const a=props.attributes,styles={};if(a.textColor)styles['--dp-section-'+className+'-color']=a.textColor;if(a.backgroundColor)styles['--dp-section-'+className+'-bg']=a.backgroundColor;return el('div',useBlockProps.save({style:styles}),el(InnerBlocks.Content));}
    });
  }
  sectionColumnBlock('digipublish/section-content',__('Section Content','digipublish-core'),'align-wide','content');
  sectionColumnBlock('digipublish/section-sidebar',__('Section Sidebar','digipublish-core'),'align-pull-right','sidebar');

  registerBlockType('digipublish/opt-in-form',{
    apiVersion:3,title:__('Opt-In Form','digipublish-core'),category:'digipublish-editorial',icon:'email',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{heading:{type:'string',default:'Stay in the loop'},description:{type:'string',default:'Get the latest stories in your inbox.'},buttonLabel:{type:'string',default:'Subscribe'},actionUrl:{type:'string',default:''},emailFieldName:{type:'string',default:'email'},inputBackground:{type:'string',default:''},inputColor:{type:'string',default:''},buttonBackground:{type:'string',default:''},buttonColor:{type:'string',default:''},buttonHoverBackground:{type:'string',default:''},buttonHoverColor:{type:'string',default:''},hideDesktop:{type:'boolean',default:false},hideLaptop:{type:'boolean',default:false},hideTablet:{type:'boolean',default:false},hideMobile:{type:'boolean',default:false}},
    edit:function(props){const a=props.attributes,set=props.setAttributes;return el(Fragment,{},
      el(InspectorControls,{},
        el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
          el(TextControl,{label:__('Heading','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
          el(TextareaControl,{label:__('Description','digipublish-core'),value:a.description||'',onChange:function(v){set({description:v});}}),
          el(TextControl,{label:__('Button Label','digipublish-core'),value:a.buttonLabel||'',onChange:function(v){set({buttonLabel:v});}}),
          el(TextControl,{label:__('Form Action URL','digipublish-core'),value:a.actionUrl||'',onChange:function(v){set({actionUrl:v});}}),
          el(TextControl,{label:__('Email Field Name','digipublish-core'),value:a.emailFieldName||'email',onChange:function(v){set({emailFieldName:v});}})
        ),
        el(PanelBody,{title:__('Color Settings','digipublish-core'),initialOpen:false},
          colorControl(__('Input Background','digipublish-core'),a.inputBackground,function(v){set({inputBackground:v});}),
          colorControl(__('Input Color','digipublish-core'),a.inputColor,function(v){set({inputColor:v});}),
          colorControl(__('Button Background','digipublish-core'),a.buttonBackground,function(v){set({buttonBackground:v});}),
          colorControl(__('Button Color','digipublish-core'),a.buttonColor,function(v){set({buttonColor:v});}),
          colorControl(__('Button Background Hover','digipublish-core'),a.buttonHoverBackground,function(v){set({buttonHoverBackground:v});}),
          colorControl(__('Button Color Hover','digipublish-core'),a.buttonHoverColor,function(v){set({buttonHoverColor:v});})
        ),
        el(PanelBody,{title:__('Responsive Settings','digipublish-core'),initialOpen:false},responsiveVisibilityControls(a,set))
      ),el(Preview,{name:'digipublish/opt-in-form',attributes:a}));},save:function(){return null;}
  });

  registerBlockType('digipublish/featured-categories',{
    apiVersion:3,title:__('Featured Categories','digipublish-core'),category:'digipublish-editorial',icon:'category',
    supports:{html:false,anchor:true,customClassName:true,spacing:{margin:true,padding:true},border:{radius:true,color:true,width:true,style:true}},
    attributes:{heading:{type:'string',default:'Featured Categories'},categoryIds:{type:'array',default:[]},limit:{type:'integer',default:6},layout:{type:'string',default:'vertical-list-alt'},showCount:{type:'boolean',default:true}},
    edit:function(props){const a=props.attributes,set=props.setAttributes,categoryOptions=useCategoryOptions().filter(function(o){return parseInt(o.value,10)>0;});return el(Fragment,{},
      el(InspectorControls,{},el(PanelBody,{title:__('Block Settings','digipublish-core'),initialOpen:true},
        el(TextControl,{label:__('Heading','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
        tokenIdsControl(__('Categories','digipublish-core'),a.categoryIds||[],categoryOptions,function(ids){set({categoryIds:ids});}),
        el(SelectControl,{label:__('Layout','digipublish-core'),value:a.layout||'vertical-list-alt',options:[{label:__('Vertical List Alt','digipublish-core'),value:'vertical-list-alt'},{label:__('Default','digipublish-core'),value:'default'}],onChange:function(v){set({layout:v});}}),
        el(RangeControl,{label:__('Maximum count','digipublish-core'),value:a.limit||6,min:1,max:50,onChange:function(v){set({limit:v||1});}}),
        el(ToggleControl,{label:__('Show count','digipublish-core'),checked:a.showCount!==false,onChange:function(v){set({showCount:v});}})
      )),el(Preview,{name:'digipublish/featured-categories',attributes:a}));},save:function(){return null;}
  });

  registerBlockType('digipublish/team-grid',{apiVersion:3,title:__('Meet Team','digipublish-core'),category:'digipublish-editorial',icon:'groups',
    attributes:{heading:{type:'string',default:'Meet the Team'},limit:{type:'integer',default:12},columns:{type:'integer',default:3},showBio:{type:'boolean',default:true},showRole:{type:'boolean',default:true}},
    edit:function(props){const a=props.attributes,set=props.setAttributes;return el(Fragment,{},el(InspectorControls,{},el(PanelBody,{title:__('Team Settings','digipublish-core')},
      el(TextControl,{label:__('Heading','digipublish-core'),value:a.heading||'',onChange:function(v){set({heading:v});}}),
      el(RangeControl,{label:__('People','digipublish-core'),value:a.limit||12,min:1,max:48,onChange:function(v){set({limit:v});}}),
      el(RangeControl,{label:__('Columns','digipublish-core'),value:a.columns||3,min:1,max:5,onChange:function(v){set({columns:v});}}),
      el(ToggleControl,{label:__('Show role','digipublish-core'),checked:a.showRole!==false,onChange:function(v){set({showRole:v});}}),
      el(ToggleControl,{label:__('Show biography','digipublish-core'),checked:a.showBio!==false,onChange:function(v){set({showBio:v});}})
    )),el(Preview,{name:'digipublish/team-grid',attributes:a}));},save:function(){return null;}});

  registerBlockType('digipublish/mega-menu',{
    apiVersion:3,title:__('Mega Menu','digipublish-core'),category:'digipublish-editorial',icon:'menu-alt3',
    attributes:{label:{type:'string',default:'Explore'},url:{type:'string',default:'#'},sourceMode:{type:'string',default:'latest'},categoryId:{type:'integer',default:0},postsToShow:{type:'integer',default:4},showImages:{type:'boolean',default:true},showCategory:{type:'boolean',default:true},showDate:{type:'boolean',default:true}},
    edit:function(props){
      const a=props.attributes,set=props.setAttributes,categoryOptions=useCategoryOptions();
      return el(Fragment,{},
        el(InspectorControls,{},
          el(PanelBody,{title:__('Mega Menu Settings','digipublish-core'),initialOpen:true},
            el(TextControl,{label:__('Label','digipublish-core'),value:a.label||'',onChange:function(v){set({label:v});}}),
            el(TextControl,{label:__('View All URL','digipublish-core'),value:a.url||'',onChange:function(v){set({url:v});}}),
            el(SelectControl,{label:__('Source','digipublish-core'),value:a.sourceMode||'latest',options:[{label:__('Latest posts','digipublish-core'),value:'latest'},{label:__('Category','digipublish-core'),value:'category'}],onChange:function(v){set({sourceMode:v});}}),
            a.sourceMode==='category'?el(SelectControl,{label:__('Category','digipublish-core'),value:a.categoryId||0,options:categoryOptions,onChange:function(v){set({categoryId:parseInt(v,10)||0});}}):null,
            el(RangeControl,{label:__('Posts','digipublish-core'),value:a.postsToShow||4,min:2,max:8,onChange:function(v){set({postsToShow:v});}}),
            el(ToggleControl,{label:__('Show images','digipublish-core'),checked:a.showImages!==false,onChange:function(v){set({showImages:v});}}),
            el(ToggleControl,{label:__('Show category','digipublish-core'),checked:a.showCategory!==false,onChange:function(v){set({showCategory:v});}}),
            el(ToggleControl,{label:__('Show date','digipublish-core'),checked:a.showDate!==false,onChange:function(v){set({showDate:v});}})
          )
        ),
        el(Preview,{name:'digipublish/mega-menu',attributes:a})
      );
    },save:function(){return null;}
  });

})(window.wp);

(function (wp) {
  'use strict';
  if (!wp || !wp.plugins || !wp.editor || !wp.data || !wp.element || !wp.components) return;
  const el = wp.element.createElement;
  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editor;
  const { SelectControl, Notice, TextControl, ToggleControl } = wp.components;
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


  function CaardsLayoutPanel() {
    const state = useSelect(function(select){
      const editor=select('core/editor');
      return {postType:editor.getCurrentPostType(),meta:editor.getEditedPostAttribute('meta')||{}};
    },[]);
    if(state.postType!=='post' && state.postType!=='page') return null;
    function updateMeta(patch){wp.data.dispatch('core/editor').editPost({meta:Object.assign({},state.meta,patch)});}
    const header=state.meta.digipublish_page_header_type||'default';
    const sidebar=state.meta.digipublish_singular_sidebar||'default';
    const loadNext=state.meta.digipublish_load_nextpost||'default';
    return el(PluginDocumentSettingPanel,{name:'digipublish-caards-layout',title:__('Caards Layout Options','digipublish-core'),className:'digipublish-caards-layout'},
      el(SelectControl,{label:__('Page Header Type','digipublish-core'),value:header,options:[
        {label:__('Default','digipublish-core'),value:'default'},{label:__('Standard','digipublish-core'),value:'standard'},
        {label:__('Large','digipublish-core'),value:'large'},{label:__('Full','digipublish-core'),value:'full'},
        {label:__('Page Title Only','digipublish-core'),value:'title'},{label:__('None','digipublish-core'),value:'none'}
      ],onChange:function(v){updateMeta({digipublish_page_header_type:v});}}),
      el(SelectControl,{label:__('Sidebar','digipublish-core'),value:sidebar,options:[
        {label:__('Default','digipublish-core'),value:'default'},{label:__('Right Sidebar','digipublish-core'),value:'right'},
        {label:__('Left Sidebar','digipublish-core'),value:'left'},{label:__('No Sidebar','digipublish-core'),value:'disabled'}
      ],onChange:function(v){updateMeta({digipublish_singular_sidebar:v});}}),
      state.postType==='post'?el(SelectControl,{label:__('Load Next Post','digipublish-core'),value:loadNext,options:[
        {label:__('Default','digipublish-core'),value:'default'},{label:__('Enabled','digipublish-core'),value:'enabled'},{label:__('Disabled','digipublish-core'),value:'disabled'}
      ],onChange:function(v){updateMeta({digipublish_load_nextpost:v});}}):null,
      el(TextControl,{label:__('Hero Video URL','digipublish-core'),help:__('Direct MP4, WebM or OGG URL for Large/Full headers.','digipublish-core'),value:state.meta.digipublish_post_video_url||'',onChange:function(v){updateMeta({digipublish_post_video_url:v});}})
    );
  }
  registerPlugin('digipublish-caards-layout',{render:CaardsLayoutPanel,icon:'layout'});
})(window.wp);
