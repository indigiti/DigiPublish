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

$content_type = sanitize_key( $attributes['contentType'] ?? 'post' );
if ( ! in_array( $content_type, array( 'post', 'gallery', 'mixed' ), true ) ) {
	$content_type = 'post';
}
$post_types = 'gallery' === $content_type ? array( 'digipublish_gallery' ) : ( 'mixed' === $content_type ? array( 'post', 'digipublish_gallery' ) : array( 'post' ) );

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
	'fallbackRandom'  => true,
	'postTypes'       => $post_types,
);

$posts = digipublish_core_query_feed_posts( $feed_attributes );

if ( 'image-grid' === $layout ) {
	$posts = array_values(
		array_filter(
			$posts,
			static function ( $post ) {
				if ( ! $post instanceof WP_Post ) {
					return false;
				}
				return 'digipublish_gallery' === $post->post_type
					? (bool) digipublish_core_get_gallery_cover_image_id( $post->ID )
					: has_post_thumbnail( $post->ID );
			}
		)
	);

	if ( count( $posts ) < $count ) {
		$args = digipublish_core_query_feed_args( $feed_attributes );
		$args['post_type'] = $post_types;
		$args['posts_per_page'] = $count;
		if ( 'post' === $content_type ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			);
		}
		$args['post__not_in'] = array_values(
			array_unique(
				array_merge(
					wp_list_pluck( $posts, 'ID' ),
					is_singular( array( 'post', 'digipublish_gallery' ) ) ? array( get_queried_object_id() ) : array()
				)
			)
		);
		$extra = new WP_Query( $args );
		foreach ( $extra->posts as $post ) {
			if ( count( $posts ) >= $count ) {
				break;
			}
			$has_visual = 'digipublish_gallery' === $post->post_type
				? (bool) digipublish_core_get_gallery_cover_image_id( $post->ID )
				: has_post_thumbnail( $post->ID );
			if ( $has_visual ) {
				$posts[] = $post;
			}
		}

		if ( count( $posts ) < $count ) {
			$latest_attributes = $feed_attributes;
			$latest_attributes['sourceMode'] = 'latest';
			$latest_attributes['categoryId'] = 0;
			$latest_args = digipublish_core_query_feed_args( $latest_attributes );
			$latest_args['post_type'] = $post_types;
			$latest_args['posts_per_page'] = $count - count( $posts );
			if ( 'post' === $content_type ) {
				$latest_args['meta_query'] = array(
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				);
			}
			$latest_args['post__not_in'] = array_values(
				array_unique(
					array_merge(
						wp_list_pluck( $posts, 'ID' ),
						is_singular( array( 'post', 'digipublish_gallery' ) ) ? array( get_queried_object_id() ) : array()
					)
				)
			);
			$latest = new WP_Query( $latest_args );
			foreach ( $latest->posts as $post ) {
				if ( count( $posts ) >= $count ) {
					break;
				}
				$has_visual = 'digipublish_gallery' === $post->post_type
					? (bool) digipublish_core_get_gallery_cover_image_id( $post->ID )
					: has_post_thumbnail( $post->ID );
				if ( $has_visual ) {
					$posts[] = $post;
				}
			}
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
	digipublish_core_design_wrapper_args( $attributes, array( 'tp-sidebar-feed', 'tp-sidebar-feed--' . $layout ) )
);

echo '<section ' . $wrapper . '>';
if ( $show_heading && '' !== $heading ) {
	$heading_tag = digipublish_core_heading_tag( $attributes );
	echo '<' . $heading_tag . ' class="tp-sidebar-feed__heading">' . esc_html( $heading ) . '</' . $heading_tag . '>';
}

if ( 'meta-list' === $layout ) {
	echo '<div class="tp-sidebar-meta-list">';
	foreach ( $posts as $post ) {
		$post_id = $post->ID;
		echo '<article class="tp-sidebar-meta-item">';
		$meta_parts = array();
		if ( $attributes['showAuthor'] ?? true ) { $meta_parts[] = '<span>' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . '</span>'; }
		if ( $attributes['showDate'] ?? true ) { $meta_parts[] = '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time>'; }
		if ( $meta_parts ) { echo '<div class="tp-sidebar-meta-item__meta">' . implode( '', $meta_parts ) . '</div>'; }
		echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
		if ( 'digipublish_gallery' === get_post_type( $post_id ) ) {
			$photo_count = digipublish_core_get_gallery_slide_count( $post_id );
			if ( $photo_count ) {
				echo '<span class="tp-sidebar-gallery-count">' . esc_html( sprintf( _n( '%d Photo', '%d Photos', $photo_count, 'digipublish-core' ), $photo_count ) ) . '</span>';
			}
		}
		echo '</article>';
	}
	echo '</div>';
} elseif ( 'ranked-list' === $layout ) {
	echo '<ol class="tp-sidebar-ranked-list">';
	$rank = 1;
	foreach ( $posts as $post ) {
		$post_id = $post->ID;
		echo '<li><span class="tp-sidebar-ranked-list__rank" aria-hidden="true">' . esc_html( (string) $rank ) . '</span><div><h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
		if ( 'digipublish_gallery' === get_post_type( $post_id ) ) {
			$photo_count = digipublish_core_get_gallery_slide_count( $post_id );
			if ( $photo_count ) {
				echo '<span class="tp-sidebar-gallery-count">' . esc_html( sprintf( _n( '%d Photo', '%d Photos', $photo_count, 'digipublish-core' ), $photo_count ) ) . '</span>';
			}
		}
		echo '</div></li>';
		$rank++;
	}
	echo '</ol>';
} else {
	echo '<div class="tp-sidebar-image-grid">';
	foreach ( $posts as $index => $post ) {
		$post_id = $post->ID;
		$class = 'tp-sidebar-image-grid__item tp-sidebar-image-grid__item--' . ( ( $index % 7 ) + 1 );
		echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( get_the_title( $post_id ) ) . '">';
		echo 'digipublish_gallery' === get_post_type( $post_id )
			? digipublish_core_gallery_cover_image_markup( $post_id, 'medium', '(max-width: 1120px) 28vw, 92px' )
			: digipublish_core_image_markup( $post_id, digipublish_core_image_size( $attributes, 'medium' ), false, '(max-width: 1120px) 28vw, 92px' );
		if ( 'digipublish_gallery' === get_post_type( $post_id ) ) {
			$photo_count = digipublish_core_get_gallery_slide_count( $post_id );
			if ( $photo_count ) {
				echo '<span class="tp-sidebar-image-grid__count">' . esc_html( (string) $photo_count ) . '</span>';
			}
		}
		echo '</a>';
	}
	echo '</div>';
}

echo '</section>';
