<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$post_id = get_queried_object_id();
if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) { return; }

$cats = wp_get_post_categories( $post_id );
$n    = max( 3, min( 8, absint( $attributes['postsToShow'] ?? 7 ) ) );
$args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $n,
	'post__not_in'        => array( $post_id ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $cats ) { $args['category__in'] = array( $cats[0] ); }
$q = new WP_Query( $args );
if ( ! $q->posts ) { return; }

echo '<section class="tp-related-posts alignwide"><h2 class="tp-section-title">' . esc_html( $attributes['heading'] ?? __( 'Related Features', 'techpress-editorial' ) ) . '</h2><div class="tp-related-posts__grid">';
foreach ( $q->posts as $i => $p ) {
	$id         = (int) $p->ID;
	$cls        = 0 === $i ? 'tp-related-card tp-related-card--lead' : 'tp-related-card';
	$image_size = 0 === $i ? 'medium_large' : 'medium';
	echo '<article class="' . esc_attr( $cls ) . '"><a class="tp-related-card__image" href="' . esc_url( get_permalink( $id ) ) . '">' . techpress_editorial_image_markup( $id, $image_size ) . '</a><div class="tp-related-card__body">' . techpress_editorial_category_markup( $id ) . '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>' . techpress_editorial_meta_markup( $id, true, true ) . '</div></article>';
}
echo '</div></section>';
