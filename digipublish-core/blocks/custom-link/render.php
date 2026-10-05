<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$label=(string)($attributes['label']??'');
$url=esc_url($attributes['url']??'/');
$target='_blank'===($attributes['target']??'')?'_blank':'_self';
$styled = ( 'styled' === ( $attributes['styleVariant'] ?? '' ) ) || ! empty( $attributes['buttonStyle'] );
$classes=array('dp-caards-custom-link');
if($styled){$classes[]='dp-caards-custom-link--styled';}
$classes=array_merge($classes,digipublish_core_visibility_classes($attributes));
$styles=array();
$align=isset($attributes['textAlign'])&&in_array($attributes['textAlign'],array('left','center','right'),true)?$attributes['textAlign']:'left';
$styles[]='--dp-custom-link-align:'.$align;
$color_map=array(
 'textColor'=>'--dp-custom-link-color','textHoverColor'=>'--dp-custom-link-hover',
 'circleBackground'=>'--dp-custom-link-circle-bg','circleColor'=>'--dp-custom-link-circle-color',
 'circleHoverBackground'=>'--dp-custom-link-circle-hover-bg','circleHoverColor'=>'--dp-custom-link-circle-hover-color'
);
foreach($color_map as $key=>$var){$value=sanitize_hex_color($attributes[$key]??'');if($value){$styles[]=$var.':'.$value;}}
foreach(array('fontSizeDesktop'=>'--dp-custom-link-size-d','fontSizeTablet'=>'--dp-custom-link-size-t','fontSizeMobile'=>'--dp-custom-link-size-m') as $key=>$var){$value=digipublish_core_css_length($attributes[$key]??'');if($value){$styles[]=$var.':'.$value;}}
$extra=array('class'=>implode(' ',$classes),'style'=>implode(';',$styles).';');
echo '<div ' . get_block_wrapper_attributes($extra) . '>';
echo '<a class="dp-caards-custom-link__anchor" href="'.$url.'" target="'.esc_attr($target).'"'.('_blank'===$target?' rel="noopener noreferrer"':'').'>';
echo '<span class="dp-caards-custom-link__label'.(!empty($attributes['disableLabelMobile'])?' is-mobile-hidden':'').'">'.esc_html($label).'</span>';
if($styled){echo '<span class="dp-caards-custom-link__circle" aria-hidden="true">→</span>';}
echo '</a></div>';
?>