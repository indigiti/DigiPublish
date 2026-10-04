<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query( techpress_editorial_post_query_args( $attributes ) );
if ( ! $query->have_posts() ) {
	return;
}

$layout  = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'grid-3';
$allowed = array( 'list', 'grid-2', 'grid-3', 'grid-4', 'grid-5' );
if ( ! in_array( $layout, $allowed, true ) ) {
	$layout = 'grid-3';
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-post-feed' ) );
echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['heading'] ) ) {
	echo '<h2 class="tp-section-title">' . esc_html( $attributes['heading'] ) . '</h2>';
}
echo '<div class="tp-feed tp-feed--' . esc_attr( $layout ) . '">';
foreach ( $query->posts as $post ) {
	echo techpress_editorial_card_markup( $post->ID, $attributes );
}
echo '</div></section>';
