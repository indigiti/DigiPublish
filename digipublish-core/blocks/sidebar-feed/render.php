<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$layout = sanitize_key( $attributes['layout'] ?? 'meta-list' );
if ( ! in_array( $layout, array( 'meta-list', 'ranked-list', 'image-grid' ), true ) ) {
	$layout = 'meta-list';
}

$count = max( 3, min( 12, absint( $attributes['postsToShow'] ?? ( 'image-grid' === $layout ? 12 : 5 ) ) ) );
$source_mode = sanitize_key( $attributes['sourceMode'] ?? 'current' );
if ( ! in_array( $source_mode, array( 'latest', 'current', 'category' ), true ) ) {
	$source_mode = 'current';
}

$order_by = sanitize_key( $attributes['orderBy'] ?? ( 'ranked-list' === $layout ? 'comment_count' : 'date' ) );
if ( ! in_array( $order_by, array( 'date', 'modified', 'comment_count', 'title' ), true ) ) {
	$order_by = 'date';
}

$period = sanitize_key( $attributes['period'] ?? 'all' );
if ( ! in_array( $period, array( 'all', 'day', 'week', 'month' ), true ) ) {
	$period = 'all';
}

$feed_attributes = array(
	'sourceMode'      => $source_mode,
	'categoryId'      => absint( $attributes['categoryId'] ?? 0 ),
	'postsToShow'     => 'image-grid' === $layout ? min( 16, max( $count * 2, 12 ) ) : $count,
	'orderBy'         => $order_by,
	'period'          => $period,
	'avoidDuplicates' => false,
	'fallbackRandom'   => true,
);

$posts = techpress_editorial_feed_get_posts( $feed_attributes );

if ( 'image-grid' === $layout ) {
	$posts = array_values(
		array_filter(
			$posts,
			static function ( $post ) {
				return $post instanceof WP_Post && has_post_thumbnail( $post->ID );
			}
		)
	);

	if ( count( $posts ) < $count ) {
		$args = techpress_editorial_feed_query_args( $feed_attributes );
		$args['posts_per_page'] = $count;
		$args['meta_query'] = array(
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		);
		$args['post__not_in'] = array_values(
			array_unique(
				array_merge(
					wp_list_pluck( $posts, 'ID' ),
					is_singular( 'post' ) ? array( get_queried_object_id() ) : array()
				)
			)
		);
		$extra = new WP_Query( $args );
		foreach ( $extra->posts as $post ) {
			if ( count( $posts ) >= $count ) {
				break;
			}
			$posts[] = $post;
		}
	}
}

$posts = array_slice( $posts, 0, $count );
if ( ! $posts ) {
	if ( is_admin() ) {
		$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-sidebar-feed tp-sidebar-feed--empty' ) );
		echo '<section ' . $wrapper . '><p>' . esc_html__( 'No sidebar stories are available yet.', 'digipublish-core' ) . '</p></section>';
	}
	return;
}

$heading = trim( (string) ( $attributes['heading'] ?? '' ) );
$show_heading = $attributes['showHeading'] ?? true;
$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'tp-sidebar-feed tp-sidebar-feed--' . $layout,
	)
);

echo '<section ' . $wrapper . '>';
if ( $show_heading && '' !== $heading ) {
	echo '<h2 class="tp-sidebar-feed__heading">' . esc_html( $heading ) . '</h2>';
}

if ( 'meta-list' === $layout ) {
	echo '<div class="tp-sidebar-meta-list">';
	foreach ( $posts as $post ) {
		$post_id = $post->ID;
		echo '<article class="tp-sidebar-meta-item">';
		echo '<div class="tp-sidebar-meta-item__meta"><span>' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . '</span><time datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time></div>';
		echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
		echo '</article>';
	}
	echo '</div>';
} elseif ( 'ranked-list' === $layout ) {
	echo '<ol class="tp-sidebar-ranked-list">';
	$rank = 1;
	foreach ( $posts as $post ) {
		$post_id = $post->ID;
		echo '<li><span class="tp-sidebar-ranked-list__rank" aria-hidden="true">' . esc_html( (string) $rank ) . '</span><h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3></li>';
		$rank++;
	}
	echo '</ol>';
} else {
	echo '<div class="tp-sidebar-image-grid">';
	foreach ( $posts as $index => $post ) {
		$post_id = $post->ID;
		$class = 'tp-sidebar-image-grid__item tp-sidebar-image-grid__item--' . ( ( $index % 7 ) + 1 );
		echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( get_the_title( $post_id ) ) . '">';
		echo techpress_editorial_image_markup( $post_id, 'medium', false, '(max-width: 1120px) 28vw, 92px' );
		echo '</a>';
	}
	echo '</div>';
}

echo '</section>';
