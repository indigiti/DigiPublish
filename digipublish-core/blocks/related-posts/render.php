<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = get_queried_object_id();
if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
	return;
}

$n = max( 3, min( 8, absint( $attributes['postsToShow'] ?? 4 ) ) );
$layout = sanitize_key( $attributes['layout'] ?? 'features' );
if ( ! in_array( $layout, array( 'features', 'read-next' ), true ) ) {
	$layout = 'features';
}
$relation_mode = sanitize_key( $attributes['relationMode'] ?? 'category-tags' );
if ( ! in_array( $relation_mode, array( 'category', 'category-tags' ), true ) ) {
	$relation_mode = 'category-tags';
}

$categories = array_values( array_filter( array_map( 'absint', wp_get_post_categories( $post_id ) ) ) );
$tag_ids = array();
if ( 'category-tags' === $relation_mode ) {
	$tag_ids = array_values( array_filter( array_map( 'absint', wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) ) ) ) );
}

$args = array(
	'post_type'              => 'post',
	'post_status'            => 'publish',
	'posts_per_page'         => max( 16, min( 32, $n * 6 ) ),
	'post__not_in'           => array( $post_id ),
	'orderby'                => 'date',
	'order'                  => 'DESC',
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_meta_cache' => true,
	'update_post_term_cache' => true,
);

$tax_query = array( 'relation' => 'OR' );
if ( $categories ) {
	$tax_query[] = array(
		'taxonomy' => 'category',
		'field'    => 'term_id',
		'terms'    => $categories,
	);
}
if ( $tag_ids ) {
	$tax_query[] = array(
		'taxonomy' => 'post_tag',
		'field'    => 'term_id',
		'terms'    => $tag_ids,
	);
}
if ( count( $tax_query ) > 1 ) {
	$args['tax_query'] = $tax_query;
}

$query = new WP_Query( $args );
$candidates = $query->posts;

if ( $candidates && ( $categories || $tag_ids ) ) {
	usort(
		$candidates,
		static function ( $a, $b ) use ( $categories, $tag_ids ) {
			$a_categories = wp_get_post_categories( $a->ID );
			$b_categories = wp_get_post_categories( $b->ID );
			$a_tags = wp_get_post_tags( $a->ID, array( 'fields' => 'ids' ) );
			$b_tags = wp_get_post_tags( $b->ID, array( 'fields' => 'ids' ) );

			$a_score = ( count( array_intersect( $categories, $a_categories ) ) * 4 ) + ( count( array_intersect( $tag_ids, $a_tags ) ) * 2 );
			$b_score = ( count( array_intersect( $categories, $b_categories ) ) * 4 ) + ( count( array_intersect( $tag_ids, $b_tags ) ) * 2 );

			if ( $a_score === $b_score ) {
				return get_post_time( 'U', true, $b ) <=> get_post_time( 'U', true, $a );
			}
			return $b_score <=> $a_score;
		}
	);
}

$posts = array_slice( $candidates, 0, $n );

if ( count( $posts ) < $n ) {
	$exclude = array_merge( array( $post_id ), wp_list_pluck( $posts, 'ID' ) );
	$fallback = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $n - count( $posts ),
			'post__not_in'           => array_values( array_unique( array_map( 'absint', $exclude ) ) ),
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		)
	);
	$posts = array_merge( $posts, $fallback->posts );
}

if ( ! $posts ) {
	return;
}

$heading = trim( (string) ( $attributes['heading'] ?? ( 'read-next' === $layout ? __( 'Read next', 'digipublish-core' ) : __( 'Related Features', 'digipublish-core' ) ) ) );

if ( 'read-next' === $layout ) {
	$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-related-posts tp-related-posts--read-next alignwide' ) );
	echo '<section ' . $wrapper . '>';
	if ( $heading ) {
		echo '<h2 class="tp-read-next__heading">' . esc_html( $heading ) . '</h2>';
	}
	echo '<div class="tp-read-next__grid">';

	foreach ( $posts as $post ) {
		$id = (int) $post->ID;
		$views = techpress_editorial_metric_value( $id, '_techpress_views' );
		$shares = techpress_editorial_metric_value( $id, '_techpress_shares' );
		$minutes = techpress_editorial_read_time( $id );

		echo '<article class="tp-read-next-card">';
		echo '<div class="tp-read-next-card__body">';
		echo '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
		echo '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $id ) ) . '">' . esc_html( get_the_date( get_option( 'date_format' ), $id ) ) . '</time>';
		if ( $attributes['showExcerpt'] ?? true ) {
			echo '<p>' . esc_html( wp_trim_words( get_the_excerpt( $id ), 22 ) ) . '</p>';
		}
		echo '</div>';

		echo '<a class="tp-read-next-card__image" href="' . esc_url( get_permalink( $id ) ) . '">';
		echo techpress_editorial_image_markup( $id, 'medium_large', false, '(max-width: 680px) 82vw, (max-width: 1120px) 50vw, 25vw' );
		echo '</a>';

		echo '<div class="tp-read-next-card__footer">';
		echo '<span>' . esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes ) ) . '</span>';
		if ( $views ) {
			echo '<span>' . esc_html( techpress_editorial_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' ) ) . '</span>';
		}
		if ( $shares ) {
			echo '<span class="tp-read-next-card__shares">' . esc_html( __( 'Shares', 'digipublish-core' ) . ' ' . techpress_editorial_format_metric( $shares ) ) . '</span>';
		}
		echo '</div>';
		echo '</article>';
	}

	echo '</div></section>';
	return;
}

echo '<section class="tp-related-posts alignwide"><h2 class="tp-section-title">' . esc_html( $heading ) . '</h2><div class="tp-related-posts__grid">';
foreach ( $posts as $i => $post ) {
	$id         = (int) $post->ID;
	$cls        = 0 === $i ? 'tp-related-card tp-related-card--lead' : 'tp-related-card';
	$image_size = 0 === $i ? 'medium_large' : 'medium';
	echo '<article class="' . esc_attr( $cls ) . '"><a class="tp-related-card__image" href="' . esc_url( get_permalink( $id ) ) . '">' . techpress_editorial_image_markup( $id, $image_size ) . '</a><div class="tp-related-card__body">' . techpress_editorial_category_markup( $id ) . '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>' . techpress_editorial_meta_markup( $id, true, true ) . '</div></article>';
}
echo '</div></section>';
