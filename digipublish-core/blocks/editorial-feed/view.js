(function(){'use strict';
document.addEventListener('click',function(e){
  var button=e.target.closest('[data-tp-carousel-dir]');
  if(!button)return;
  var root=button.closest('.tp-editorial-feed');
  if(!root)return;
  var scroller=root.querySelector('[data-tp-carousel]');
  if(!scroller)return;
  var direction=button.getAttribute('data-tp-carousel-dir')==='prev'?-1:1;
  var card=scroller.querySelector('.tp-carousel-card');
  var styles=window.getComputedStyle(scroller);
  var gap=parseFloat(styles.columnGap||styles.gap)||0;
  var step=card?card.getBoundingClientRect().width+gap:Math.max(280,scroller.clientWidth*.82);
  var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  scroller.scrollBy({left:direction*step,behavior:reduce?'auto':'smooth'});
});
})();