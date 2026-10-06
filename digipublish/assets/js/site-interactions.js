(function(){
'use strict';
var root=document.documentElement;
var schemeKey='digipublish-scheme';
var legacySchemeKey='digipublish-caards-scheme';
function applyScheme(value){
  var dark=value==='dark';
  root.classList.toggle('dp-theme-dark',dark);
  root.classList.toggle('dp-caards-dark',dark);
  root.setAttribute('data-dp-scheme',dark?'dark':'light');
  document.querySelectorAll('[data-dp-scheme-toggle]').forEach(function(btn){
    btn.setAttribute('aria-pressed',dark?'true':'false');
    btn.setAttribute('aria-label',dark?'Use light mode':'Use dark mode');
  });
}
try{applyScheme(localStorage.getItem(schemeKey)||localStorage.getItem(legacySchemeKey)||'light');}catch(e){applyScheme('light');}
document.addEventListener('click',function(event){
  var scheme=event.target.closest('[data-dp-scheme-toggle]');
  if(scheme){
    var next=root.classList.contains('dp-theme-dark')||root.classList.contains('dp-caards-dark')?'light':'dark';
    try{localStorage.setItem(schemeKey,next);}catch(e){}
    applyScheme(next);
    return;
  }
  var search=event.target.closest('[data-dp-search-toggle]');
  if(search){
    var header=search.closest('.dp-caards-header');
    if(header){
      var panel=header.querySelector('.dp-caards-search');
      if(panel){
        var open=!panel.classList.contains('is-open');
        panel.classList.toggle('is-open',open);
        search.setAttribute('aria-expanded',open?'true':'false');
        if(open){var input=panel.querySelector('input[type="search"]'); if(input){setTimeout(function(){input.focus();},50);}}
      }
    }
    return;
  }
  var menu=event.target.closest('[data-dp-fullscreen-toggle]');
  if(menu){
    var overlay=document.querySelector('.dp-caards-fullscreen');
    if(overlay){
      var open=!overlay.classList.contains('is-open');
      overlay.classList.toggle('is-open',open);
      document.body.classList.toggle('dp-menu-open',open);
      document.body.classList.toggle('dp-caards-menu-open',open);
      menu.setAttribute('aria-expanded',open?'true':'false');
    }
    return;
  }
  var close=event.target.closest('[data-dp-overlay-close]');
  if(close){
    document.querySelectorAll('.dp-caards-fullscreen.is-open,.dp-caards-search.is-open').forEach(function(el){el.classList.remove('is-open');});
    document.body.classList.remove('dp-menu-open');
    document.body.classList.remove('dp-caards-menu-open');
  }
});
document.addEventListener('keydown',function(event){
  if(event.key==='Escape'){
    document.querySelectorAll('.dp-caards-fullscreen.is-open,.dp-caards-search.is-open').forEach(function(el){el.classList.remove('is-open');});
    document.body.classList.remove('dp-menu-open');
    document.body.classList.remove('dp-caards-menu-open');
  }
});

function initLoadNextPost(){
  var runtime=window.digiPublishSite||window.digiPublishCaards||{};
  var cfg=runtime.loadNext;
  if(!cfg||!cfg.enabled||!cfg.postId||!cfg.restUrl||!('IntersectionObserver' in window)) return;
  var first=document.querySelector('.dp-caards-singular');
  if(!first||document.querySelector('[data-dp-nextpost-sentinel]')) return;
  var sentinel=document.createElement('div');
  sentinel.className='dp-caards-nextpost-sentinel';
  sentinel.setAttribute('data-dp-nextpost-sentinel','');
  first.parentNode.insertBefore(sentinel,first.nextSibling);
  var current=parseInt(cfg.postId,10)||0;
  var loaded=[current];
  var busy=false,ended=false;
  var status=document.createElement('div');
  status.className='dp-caards-nextpost-status';
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

var header=document.querySelector('.dp-caards-header');
initLoadNextPost();
if(header && 'IntersectionObserver' in window){
  var marker=document.createElement('div'); marker.className='dp-caards-header-marker'; header.parentNode.insertBefore(marker,header);
  new IntersectionObserver(function(entries){entries.forEach(function(entry){header.classList.toggle('is-sticky',!entry.isIntersecting);});}).observe(marker);
}
})();