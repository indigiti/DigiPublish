<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$slot       = isset( $attributes['slotName'] ) ? sanitize_key( $attributes['slotName'] ) : 'content-slot';
$label      = isset( $attributes['label'] ) ? sanitize_text_field( $attributes['label'] ) : __( 'Advertisement', 'digipublish-core' );
$min_height = isset( $attributes['minHeight'] ) ? max( 0, min( 1200, absint( $attributes['minHeight'] ) ) ) : 90;
$provider   = techpress_editorial_ad_provider_markup( $slot, $attributes );
$in_editor  = is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
if ( '' === trim( (string) $provider ) && ! $in_editor ) { return; }
$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-ad-slot', 'data-slot' => $slot, 'style' => '--tp-ad-min-height:' . $min_height . 'px' ) );
echo '<aside ' . $wrapper . ' aria-label="' . esc_attr( $label ) . '">';
if ( $label ) { echo '<div class="tp-ad-slot__label">' . esc_html( $label ) . '</div>'; }
if ( '' !== trim( (string) $provider ) ) { echo $provider; } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
else { echo '<div class="tp-ad-slot__placeholder" aria-hidden="true"></div>'; }
echo '</aside>';
