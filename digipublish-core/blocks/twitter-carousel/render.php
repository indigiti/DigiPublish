<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$items = array();
$raw = (string) ( $attributes['items'] ?? '' );
foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
	$line = trim( $line );
	if ( '' === $line ) { continue; }
	$parts = array_map( 'trim', explode( '|', $line ) );
	$text = sanitize_text_field( $parts[0] ?? '' );
	if ( ! $text ) { continue; }
	$items[] = array(
		'text'   => $text,
		'url'    => esc_url_raw( $parts[1] ?? '' ),
		'author' => sanitize_text_field( $parts[2] ?? '' ),
		'handle' => sanitize_text_field( $parts[3] ?? '' ),
	);
}
$items = apply_filters( 'digipublish_twitter_carousel_items', $items, $attributes );
$heading = (string) ( $attributes['heading'] ?? 'Twitter Feed' );
$profile = esc_url( $attributes['profileUrl'] ?? '' );
$columns = max( 1, min( 5, absint( $attributes['columns'] ?? 3 ) ) );
$wrapper = get_block_wrapper_attributes( array( 'class' => 'dp-caards-social-carousel dp-caards-twitter-carousel', 'style' => '--dp-social-columns:' . $columns ) );
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
		$open = $item['url'] ? '<a class="dp-caards-social-card dp-caards-tweet-card" href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener noreferrer">' : '<div class="dp-caards-social-card dp-caards-tweet-card">';
		$close = $item['url'] ? '</a>' : '</div>';
		echo $open . '<div class="dp-caards-social-card__body"><span class="dp-caards-tweet-card__mark">𝕏</span><p>' . esc_html( $item['text'] ) . '</p>';
		if ( $item['author'] || $item['handle'] ) { echo '<div class="dp-caards-tweet-card__author">' . esc_html( $item['author'] ) . ( $item['handle'] ? ' <span>' . esc_html( $item['handle'] ) . '</span>' : '' ) . '</div>'; }
		echo '</div>' . $close;
	}
	echo '</div>';
} else {
	echo '<div class="dp-caards-social-carousel__empty">' . esc_html__( 'Add posts as text | URL | author | @handle, one item per line, or connect a feed integration using the digipublish_twitter_carousel_items filter.', 'digipublish-core' ) . '</div>';
}
echo '</section>';
