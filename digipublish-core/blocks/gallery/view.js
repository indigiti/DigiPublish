(function(){'use strict';
function init(root){
  var viewport=root.querySelector('[data-gallery-viewport]');
  var slides=Array.prototype.slice.call(root.querySelectorAll('[data-gallery-slide]'));
  if(!viewport||!slides.length)return;
  var counter=root.querySelector('[data-gallery-counter]');
  var thumbs=Array.prototype.slice.call(root.querySelectorAll('[data-gallery-thumb]'));
  var current=0;
  var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function emit(index){
    var detail={index:index+1,total:slides.length,url:window.location.href.split('#')[0]+'#'+slides[index].id};
    root.dispatchEvent(new CustomEvent('digipublish:gallery-slide-change',{bubbles:true,detail:detail}));
    if(Array.isArray(window.dataLayer))window.dataLayer.push({event:'digipublish_gallery_slide',gallery_slide:detail.index,gallery_total:detail.total});
  }
  function setCurrent(index,shouldFocus){
    index=Math.max(0,Math.min(slides.length-1,index));
    if(index===current&&!shouldFocus){if(counter)counter.textContent=(index+1)+' / '+slides.length;return;}
    current=index;
    if(counter)counter.textContent=(index+1)+' / '+slides.length;
    thumbs.forEach(function(btn,i){btn.classList.toggle('is-active',i===index);btn.setAttribute('aria-current',i===index?'true':'false');});
    if(shouldFocus&&root.getAttribute('data-gallery-mode')==='swipe'){
      viewport.scrollTo({left:slides[index].offsetLeft-viewport.offsetLeft,behavior:reduce?'auto':'smooth'});
    }
    emit(index);
  }

  var prev=root.querySelector('[data-gallery-prev]');
  var next=root.querySelector('[data-gallery-next]');
  if(prev)prev.addEventListener('click',function(){setCurrent(current-1,true);});
  if(next)next.addEventListener('click',function(){setCurrent(current+1,true);});
  thumbs.forEach(function(btn){btn.addEventListener('click',function(){setCurrent(parseInt(btn.getAttribute('data-gallery-thumb'),10)||0,true);});});

  var full=root.querySelector('[data-gallery-fullscreen]');
  if(full)full.addEventListener('click',function(){
    var target=slides[current].querySelector('.tp-gallery-slide__figure')||slides[current];
    if(target.requestFullscreen)target.requestFullscreen().catch(function(){});
  });

  var share=root.querySelector('[data-gallery-share]');
  if(share)share.addEventListener('click',function(){
    var url=window.location.href.split('#')[0]+'#'+slides[current].id;
    var title=document.title;
    if(navigator.share){navigator.share({title:title,url:url}).catch(function(){});return;}
    if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(url).catch(function(){});return;}
    window.prompt('Copy this link:',url);
  });

  root.addEventListener('keydown',function(event){
    if(root.getAttribute('data-gallery-mode')!=='swipe')return;
    if(event.key==='ArrowLeft'){event.preventDefault();setCurrent(current-1,true);}
    if(event.key==='ArrowRight'){event.preventDefault();setCurrent(current+1,true);}
  });

  if(root.getAttribute('data-gallery-mode')==='swipe'&&'IntersectionObserver' in window){
    var observer=new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting&&entry.intersectionRatio>=.6){var i=slides.indexOf(entry.target);if(i>=0)setCurrent(i,false);}
      });
    },{root:viewport,threshold:[.6]});
    slides.forEach(function(slide){observer.observe(slide);});
  }
  setCurrent(0,false);
}
document.querySelectorAll('[data-digipublish-gallery]').forEach(init);
})();