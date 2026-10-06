(function (wp, root) {
  'use strict';
  if (!wp || !wp.element || !wp.components || !wp.data || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const { InspectorControls } = wp.blockEditor;
  const { PanelBody, RangeControl, SelectControl, ToggleControl, FormTokenField } = wp.components;
  const { __ } = wp.i18n;
  const useSelect = wp.data.useSelect;

  function hasAttribute(attributes, key) {
    return Object.prototype.hasOwnProperty.call(attributes, key);
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

  root.DigiPublishEditorQuery = Object.freeze({
    useCategoryOptions: useCategoryOptions,
    useTagOptions: useTagOptions,
    usePostTypeOptions: usePostTypeOptions,
    useTaxonomyOptions: useTaxonomyOptions,
    useTermOptions: useTermOptions,
    usePostOptions: usePostOptions,
    tokenIdsControl: tokenIdsControl,
    postFormatControl: postFormatControl,
    postQueryPanelChildren: postQueryPanelChildren,
    postQueryControls: postQueryControls
  });
})(window.wp, window);
