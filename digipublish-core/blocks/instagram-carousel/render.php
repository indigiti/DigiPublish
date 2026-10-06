<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$items=array();
$raw=(string)($attributes['items']??'');
foreach(preg_split('/\r\n|\r|\n/',$raw) as $line){
 $line=trim($line);if(''===$line)continue;
 $parts=array_map('trim',explode('|',$line));$image=esc_url_raw($parts[0]??'');if(!$image)continue;
 $items[]=array('image'=>$image,'url'=>esc_url_raw($parts[1]??''),'alt'=>sanitize_text_field($parts[2]??''));
}
$items=apply_filters('digipublish_instagram_carousel_items',$items,$attributes);
$image_size=sanitize_key((string)($attributes['imageSize']??'medium'));
foreach($items as &$item){
 if(isset($item['sizes'])&&is_array($item['sizes'])&&!empty($item['sizes'][$image_size])){
  $item['image']=esc_url_raw($item['sizes'][$image_size]);
 }
}
unset($item);
$number=max(1,min(30,absint($attributes['number']??6)));
$items=array_slice($items,0,$number);
$heading=(string)($attributes['heading']??'Instagram');
$profile=esc_url($attributes['profileUrl']??'');
$columns=max(1,min(8,absint($attributes['columns']??5)));
$layout=sanitize_key((string)($attributes['layout']??'default'));
if(!in_array($layout,array('default','carousel','carousel-full'),true)){$layout='default';}
$classes=array_merge(array('dp-social-carousel','dp-instagram-carousel','dp-instagram-carousel--'.$layout),digipublish_core_visibility_classes($attributes));
$styles=array('--dp-social-columns:'.$columns);
if('carousel-full'===$layout){$height=digipublish_core_css_length($attributes['diagonalCardMinHeight']??'','480px');$styles[]='--dp-social-card-min-height:'.$height;}
$wrapper=get_block_wrapper_attributes(array('class'=>implode(' ',$classes),'style'=>implode(';',$styles).';'));
echo '<section '.$wrapper.'>';
if(!empty($attributes['showHeader'])){
 echo '<div class="dp-social-carousel__head"><h2>'.esc_html($heading).'</h2>';
 if(!empty($attributes['showFollowButton'])&&$profile){echo '<a class="dp-social-carousel__follow" href="'.$profile.'" target="_blank" rel="noopener noreferrer">'.esc_html__('Follow','digipublish-core').'</a>';}
 echo '</div>';
}
if($items){
 echo '<div class="dp-social-carousel__track" data-dp-social-track>';
 foreach($items as $index=>$item){
  $target='_self'===($attributes['target']??'')?'_self':'_blank';
  $open=$item['url']?'<a class="dp-social-card" href="'.esc_url($item['url']).'" target="'.esc_attr($target).'"'.('_blank'===$target?' rel="noopener noreferrer"':'').'>':'<div class="dp-social-card">';
  $close=$item['url']?'</a>':'</div>';
  echo $open.'<img src="'.esc_url($item['image']).'" alt="'.esc_attr($item['alt']).'" loading="lazy">'. $close;
 }
 echo '</div>';
 if('default'!==$layout){echo '<div class="dp-social-carousel__nav"><button type="button" data-dp-social-prev aria-label="'.esc_attr__('Previous','digipublish-core').'">←</button><button type="button" data-dp-social-next aria-label="'.esc_attr__('Next','digipublish-core').'">→</button></div>';}
}else{echo '<div class="dp-social-carousel__empty">'.esc_html__('Add Instagram items as image URL | post URL | alt text, one item per line, or connect a feed integration.','digipublish-core').'</div>';}
echo '</section>';
?>