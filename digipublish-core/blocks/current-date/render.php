<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$format = ! empty( $attributes['format'] ) ? (string) $attributes['format'] : 'F d, Y';
$classes = array_merge( array( 'dp-caards-current-date' ), digipublish_core_visibility_classes( $attributes ) );
$styles = array();
$align = isset( $attributes['textAlign'] ) && in_array( $attributes['textAlign'], array( 'left','right','center' ), true ) ? $attributes['textAlign'] : 'left';
$styles[] = '--dp-current-date-align:' . $align;
$color = sanitize_hex_color( $attributes['textColor'] ?? '' );
if ( $color ) { $styles[] = '--dp-current-date-color:' . $color; }
foreach ( array( 'fontSizeDesktop'=>'--dp-current-date-size-d','fontSizeTablet'=>'--dp-current-date-size-t','fontSizeMobile'=>'--dp-current-date-size-m' ) as $key=>$var ) {
	$value = digipublish_core_css_length( $attributes[$key] ?? '' );
	if ( $value ) { $styles[] = $var . ':' . $value; }
}
$extra = array( 'class'=>implode( ' ', $classes ), 'style'=>implode( ';', $styles ) . ';' );
echo '<div ' . get_block_wrapper_attributes( $extra ) . '>' . esc_html( apply_filters( 'digipublish_current_date', wp_date( $format ) ) ) . '</div>';
?>