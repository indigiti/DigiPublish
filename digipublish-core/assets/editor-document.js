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

    const type = state.meta._digipublish_attribution_type || state.meta._techpress_attribution_type || '';
    const userId = parseInt(state.meta._digipublish_attribution_user || state.meta._techpress_attribution_user || 0, 10);
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
        onChange: function (value) { updateMeta({ _digipublish_attribution_type: value, _digipublish_attribution_user: value ? userId : 0 }); }
      }),
      type ? el(SelectControl, { label: __('Person', 'digipublish-core'), value: userId, options: userOptions, onChange: function (value) { updateMeta({ _digipublish_attribution_user: parseInt(value, 10) || 0 }); } }) : null,
      type && !userId ? el(Notice, { status: 'warning', isDismissible: false }, __('Select the person who should receive this credit.', 'digipublish-core')) : null
    );
  }

  registerPlugin('digipublish-editorial-attribution', { render: EditorialAttributionPanel, icon: 'admin-users' });


  function DigiPublishLayoutPanel() {
    const state = useSelect(function(select){
      const editor=select('core/editor');
      return {postType:editor.getCurrentPostType(),meta:editor.getEditedPostAttribute('meta')||{}};
    },[]);
    if(state.postType!=='post' && state.postType!=='page') return null;
    function updateMeta(patch){wp.data.dispatch('core/editor').editPost({meta:Object.assign({},state.meta,patch)});}
    const header=state.meta.digipublish_page_header_type||'default';
    const sidebar=state.meta.digipublish_singular_sidebar||'default';
    const loadNext=state.meta.digipublish_load_nextpost||'default';
    return el(PluginDocumentSettingPanel,{name:'digipublish-layout',title:__('DigiPublish Layout Options','digipublish-core'),className:'digipublish-layout'},
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
  registerPlugin('digipublish-layout',{render:DigiPublishLayoutPanel,icon:'layout'});
})(window.wp);
