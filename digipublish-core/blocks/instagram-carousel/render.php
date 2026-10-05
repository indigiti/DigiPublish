<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$items = array();
$raw = (string) ( $attributes['items'] ?? '' );
foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
	$line = trim( $line );
	if ( '' === $line ) { continue; }
	$parts = array_map( 'trim', explode( '|', $line ) );
	$image = esc_url_raw( $parts[0] ?? '' );
	if ( ! $image ) { continue; }
	$items[] = array(
		'image' => $image,
		'url'   => esc_url_raw( $parts[1] ?? '' ),
		'alt'   => sanitize_text_field( $parts[2] ?? '' ),
	);
}
$items = apply_filters( 'digipublish_instagram_carousel_items', $items, $attributes );
$heading = (string) ( $attributes['heading'] ?? 'Instagram' );
$profile = esc_url( $attributes['profileUrl'] ?? '' );
$columns = max( 2, min( 8, absint( $attributes['columns'] ?? 5 ) ) );
$wrapper = get_block_wrapper_attributes( array( 'class' => 'dp-caards-social-carousel dp-caards-instagram-carousel', 'style' => '--dp-social-columns:' . $columns ) );
echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['showHeader'] ) ) {
	echo '<div class="dp-caards-social-carousel__head"><h2>' . esc_html( $heading ) . '</h2>';
	if ( ! empty( $attributes['showFollowButton'] ) && $profile ) {
		echo '<a class="dp-caards-social-carousel__follow" href="' . $profile . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Follow', 'digipublish-core' ) . '</a>';
	}
	echo '</div>';
}
if ( $items ) {
	echo '<div class="dp-caards-social-carousel__track">';
	foreach ( $items as $item ) {
		$tag_open = $item['url'] ? '<a class="dp-caards-social-card" href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener noreferrer">' : '<div class="dp-caards-social-card">';
		$tag_close = $item['url'] ? '</a>' : '</div>';
		echo $tag_open . '<img src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" loading="lazy">' . $tag_close;
	}
	echo '</div>';
} else {
	echo '<div class="dp-caards-social-carousel__empty">' . esc_html__( 'Add Instagram items as image URL | post URL | alt text, one item per line, or connect a feed integration using the digipublish_instagram_carousel_items filter.', 'digipublish-core' ) . '</div>';
}
echo '</section>';
