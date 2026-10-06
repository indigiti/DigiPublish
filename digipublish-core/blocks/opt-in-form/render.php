<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$classes = array_merge( array( 'dp-opt-in' ), digipublish_core_visibility_classes( $attributes ) );
$styles = array();
$map = array(
 'inputBackground'=>'--dp-opt-input-bg','inputColor'=>'--dp-opt-input-color','buttonBackground'=>'--dp-opt-button-bg',
 'buttonColor'=>'--dp-opt-button-color','buttonHoverBackground'=>'--dp-opt-button-hover-bg','buttonHoverColor'=>'--dp-opt-button-hover-color'
);
foreach ( $map as $key=>$var ) {
 $value = isset( $attributes[$key] ) ? sanitize_hex_color( $attributes[$key] ) : '';
 if ( $value ) { $styles[] = $var . ':' . $value; }
}
$extra=array('class'=>implode(' ',$classes));
if($styles){$extra['style']=implode(';',$styles).';';}
$heading=(string)($attributes['heading']??'');
$description=(string)($attributes['description']??'');
$label=(string)($attributes['buttonLabel']??__('Subscribe','digipublish-core'));
$action=esc_url($attributes['actionUrl']??'');
$field=preg_replace('/[^a-zA-Z0-9_-]/','',(string)($attributes['emailFieldName']??'email'));
if(!$field){$field='email';}
echo '<section ' . get_block_wrapper_attributes($extra) . '>';
if($heading){echo '<h2 class="dp-opt-in__heading">'.esc_html($heading).'</h2>';}
if($description){echo '<p class="dp-opt-in__description">'.esc_html($description).'</p>';}
echo '<form class="dp-opt-in__form" method="post" action="' . $action . '">';
echo '<label class="screen-reader-text" for="dp-opt-' . esc_attr($field) . '">' . esc_html__('Email address','digipublish-core') . '</label>';
echo '<input id="dp-opt-' . esc_attr($field) . '" type="email" name="' . esc_attr($field) . '" required autocomplete="email" placeholder="' . esc_attr__('Email address','digipublish-core') . '">';
echo '<button type="submit">' . esc_html($label) . '</button></form>';
do_action('digipublish_opt_in_form_after',$attributes);
echo '</section>';
?>