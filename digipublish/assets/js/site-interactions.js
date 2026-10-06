(function(){
'use strict';

/**
 * Legacy classic-script compatibility controller.
 *
 * Theme shell behavior (scheme/search/menu/Escape/sticky header) is owned by
 * assets/js/site-interactivity.js through the WordPress Interactivity API.
 * This file remains temporarily for Auto Load Next Post only.
 */
var legacySingular='.dp-caards-singular';

function initLoadNextPost(){
  var runtime=window.digiPublishSite||window.digiPublishCaards||{};
  var cfg=runtime.loadNext;
  if(!cfg||!cfg.enabled||!cfg.postId||!cfg.restUrl||!('IntersectionObserver' in window)) return;

  var first=document.querySelector('.dp-singular,'+legacySingular);
  if(!first||document.querySelector('[data-dp-nextpost-sentinel]')) return;

  var sentinel=document.createElement('div');
  sentinel.className='dp-nextpost-sentinel';
  sentinel.setAttribute('data-dp-nextpost-sentinel','');
  first.parentNode.insertBefore(sentinel,first.nextSibling);

  var current=parseInt(cfg.postId,10)||0;
  var loaded=[current];
  var busy=false,ended=false;
  var status=document.createElement('div');
  status.className='dp-nextpost-status';
  status.setAttribute('aria-live','polite');
  sentinel.parentNode.insertBefore(status,sentinel.nextSibling);

  function bindHistory(section){
    if(!section||!section.dataset.url||!('IntersectionObserver' in window)) return;
    var historyObserver=new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting&&entry.intersectionRatio>.35){
          var title=section.getAttribute('data-title')||document.title;
          var url=section.getAttribute('data-url');
          if(url&&window.location.href!==url){
            history.replaceState({dpNextPost:true},title,url);
            document.title=title;
          }
        }
      });
    },{threshold:[.35,.6]});
    historyObserver.observe(section);
  }

  async function load(){
    if(busy||ended||!current) return;
    busy=true;
    status.textContent='Loading next post…';

    try{
      var response=await fetch(cfg.restUrl,{
        method:'POST',
        credentials:'same-origin',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({postId:current,exclude:loaded})
      });
      if(!response.ok) throw new Error('HTTP '+response.status);

      var data=await response.json();
      if(!data||data.end||!data.content){
        ended=true;
        observer.disconnect();
        sentinel.hidden=true;
        status.textContent='';
        return;
      }

      var wrap=document.createElement('div');
      wrap.innerHTML=data.content;
      var section=wrap.firstElementChild;
      if(section){
        sentinel.parentNode.insertBefore(section,sentinel);
        current=parseInt(data.postId,10)||0;
        if(current&&loaded.indexOf(current)===-1) loaded.push(current);
        bindHistory(section);
      }
      status.textContent='';
    }catch(error){
      status.textContent='Could not load the next post.';
    }finally{
      busy=false;
    }
  }

  var observer=new IntersectionObserver(function(entries){
    entries.forEach(function(entry){if(entry.isIntersecting) load();});
  },{rootMargin:'900px 0px'});

  observer.observe(sentinel);
}

initLoadNextPost();
})();
