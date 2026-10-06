<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$mode = sanitize_key( $attributes['sourceMode'] ?? 'latest' );
$args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => max( 2, min( 8, absint( $attributes['postsToShow'] ?? 4 ) ) ),
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( 'category' === $mode && ! empty( $attributes['categoryId'] ) ) {
	$args['cat'] = absint( $attributes['categoryId'] );
}
$query = new WP_Query( $args );
$label = trim( (string) ( $attributes['label'] ?? 'Explore' ) );
$url = esc_url( $attributes['url'] ?? '#' );
$wrapper = get_block_wrapper_attributes( array( 'class' => 'dp-mega-menu' ) );
echo '<details ' . $wrapper . '><summary><span>' . esc_html( $label ) . '</span><span aria-hidden="true">⌄</span></summary><div class="dp-mega-menu__panel"><div class="dp-mega-menu__head"><h2>' . esc_html( $label ) . '</h2>';
if ( $url && '#' !== $url ) { echo '<a href="' . $url . '">' . esc_html__( 'View All', 'digipublish-core' ) . '</a>'; }
echo '</div><div class="dp-mega-menu__grid">';
foreach ( $query->posts as $post ) {
	$id = (int) $post->ID;
	echo '<article class="dp-mega-menu__card">';
	if ( $attributes['showImages'] ?? true ) {
		echo '<a class="dp-mega-menu__image" href="' . esc_url( get_permalink( $id ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Read %s', 'digipublish-core' ), get_the_title( $id ) ) ) . '">' . digipublish_core_image_markup( $id, 'medium_large', false, '25vw' ) . '</a>';
	}
	echo '<div class="dp-mega-menu__body">';
	if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $id ); }
	echo '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	if ( $attributes['showDate'] ?? true ) { echo '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $id ) ) . '">' . esc_html( get_the_date( '', $id ) ) . '</time>'; }
	echo '</div></article>';
}
echo '</div></div></details>';
