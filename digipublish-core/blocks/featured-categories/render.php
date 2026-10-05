<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$ids=array_values(array_filter(array_map('absint',is_array($attributes['categoryIds']??null)?$attributes['categoryIds']:array())));
$limit=max(1,min(50,absint($attributes['limit']??6)));
$args=array('taxonomy'=>'category','hide_empty'=>true,'number'=>$limit,'orderby'=>'count','order'=>'DESC');
if($ids){$args['include']=$ids;$args['orderby']='include';$args['number']=0;}
$terms=get_terms($args);
if(is_wp_error($terms)||!$terms){return;}
$layout=sanitize_key((string)($attributes['layout']??'vertical-list-alt'));
if(!in_array($layout,array('vertical-list-alt','default'),true)){$layout='vertical-list-alt';}
echo '<section ' . get_block_wrapper_attributes(array('class'=>'dp-caards-featured-categories dp-caards-featured-categories--'.$layout)) . '>';
if(!empty($attributes['heading'])){echo '<h2>'.esc_html($attributes['heading']).'</h2>';}
echo '<div class="dp-caards-featured-categories__list">';
foreach($terms as $term){
 echo '<a class="dp-caards-featured-category" href="'.esc_url(get_term_link($term)).'"><span>'.esc_html($term->name).'</span>';
 if($attributes['showCount']??true){echo '<strong>'.number_format_i18n($term->count).'</strong>';}
 echo '</a>';
}
echo '</div></section>';
?>