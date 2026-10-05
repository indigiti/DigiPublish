(function(){
'use strict';
var root=document.documentElement;
var schemeKey='digipublish-caards-scheme';
function applyScheme(value){
  var dark=value==='dark';
  root.classList.toggle('dp-caards-dark',dark);
  root.setAttribute('data-dp-scheme',dark?'dark':'light');
  document.querySelectorAll('[data-dp-scheme-toggle]').forEach(function(btn){
    btn.setAttribute('aria-pressed',dark?'true':'false');
    btn.setAttribute('aria-label',dark?'Use light mode':'Use dark mode');
  });
}
try{applyScheme(localStorage.getItem(schemeKey)||'light');}catch(e){applyScheme('light');}
document.addEventListener('click',function(event){
  var scheme=event.target.closest('[data-dp-scheme-toggle]');
  if(scheme){
    var next=root.classList.contains('dp-caards-dark')?'light':'dark';
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
      document.body.classList.toggle('dp-caards-menu-open',open);
      menu.setAttribute('aria-expanded',open?'true':'false');
    }
    return;
  }
  var close=event.target.closest('[data-dp-overlay-close]');
  if(close){
    document.querySelectorAll('.dp-caards-fullscreen.is-open,.dp-caards-search.is-open').forEach(function(el){el.classList.remove('is-open');});
    document.body.classList.remove('dp-caards-menu-open');
  }
});
document.addEventListener('keydown',function(event){
  if(event.key==='Escape'){
    document.querySelectorAll('.dp-caards-fullscreen.is-open,.dp-caards-search.is-open').forEach(function(el){el.classList.remove('is-open');});
    document.body.classList.remove('dp-caards-menu-open');
  }
});
var header=document.querySelector('.dp-caards-header');
if(header && 'IntersectionObserver' in window){
  var marker=document.createElement('div'); marker.className='dp-caards-header-marker'; header.parentNode.insertBefore(marker,header);
  new IntersectionObserver(function(entries){entries.forEach(function(entry){header.classList.toggle('is-sticky',!entry.isIntersecting);});}).observe(marker);
}
})();