<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$per_page = max( 5, min( 20, absint( $attributes['postsPerPage'] ?? 10 ) ) );
$columns  = max( 2, min( 5, absint( $attributes['columns'] ?? 5 ) ) );
$show_top = $attributes['showTopPicks'] ?? true;
$top_ids_for_exclusion = array();
$paged    = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );

$context_label = __( 'Latest Articles', 'digipublish-core' );
$args          = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $per_page,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
);

if ( is_category() ) {
	$term             = get_queried_object();
	$args['cat']      = (int) $term->term_id;
	$context_label    = __( 'Latest News', 'digipublish-core' );
} elseif ( is_tag() ) {
	$term             = get_queried_object();
	$args['tag_id']   = (int) $term->term_id;
	$context_label    = sprintf( __( 'Latest %s Articles', 'digipublish-core' ), $term->name );
} elseif ( is_tax() ) {
	$term              = get_queried_object();
	$args['tax_query'] = array(
		array(
			'taxonomy' => $term->taxonomy,
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	);
} elseif ( is_author() ) {
	$author           = get_queried_object();
	$args['author']   = (int) $author->ID;
	$context_label    = sprintf( __( 'Latest Articles from %s', 'digipublish-core' ), $author->display_name );
} elseif ( is_search() ) {
	$args['s']        = get_search_query();
	$context_label    = __( 'Search Results', 'digipublish-core' );
} elseif ( is_day() ) {
	$args['year']     = get_query_var( 'year' );
	$args['monthnum'] = get_query_var( 'monthnum' );
	$args['day']      = get_query_var( 'day' );
} elseif ( is_month() ) {
	$args['year']     = get_query_var( 'year' );
	$args['monthnum'] = get_query_var( 'monthnum' );
} elseif ( is_year() ) {
	$args['year']     = get_query_var( 'year' );
}

/*
 * Standard archive/search/author/date templates have already run the main
 * WordPress query. Reuse it instead of executing the same database work again.
 */
global $wp_query;
$use_main_query = ! is_admin()
	&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
	&& $wp_query instanceof WP_Query
	&& ( is_archive() || is_search() || is_author() || is_category() || is_tag() || is_date() );

$query      = $use_main_query ? $wp_query : new WP_Query( $args );
$post_items = $query->posts;

if ( empty( $post_items ) ) {
	echo '<section class="tp-archive-feed alignwide"><p>' . esc_html__( 'No matching content found.', 'digipublish-core' ) . '</p></section>';
	return;
}

$block_wrapper = get_block_wrapper_attributes(
	digipublish_core_design_wrapper_args(
		$attributes,
		array( 'tp-archive-feed-block' ),
		array( 'desktop' => $columns, 'tablet' => min( 3, $columns ), 'mobile' => 1 )
	)
);
echo '<div ' . $block_wrapper . '>';

if ( $show_top && is_category() && 1 === $paged ) {
	$top_ids = array();
	foreach ( $post_items as $item ) {
		if ( has_post_thumbnail( $item->ID ) ) {
			$top_ids[] = (int) $item->ID;
		}
		if ( 3 === count( $top_ids ) ) {
			break;
		}
	}
	if ( count( $top_ids ) < 3 ) {
		foreach ( $post_items as $item ) {
			$id = (int) $item->ID;
			if ( ! in_array( $id, $top_ids, true ) ) {
				$top_ids[] = $id;
			}
			if ( 3 === count( $top_ids ) ) {
				break;
			}
		}
	}

	if ( $top_ids ) {
		$top_ids_for_exclusion = $top_ids;
		$heading_tag = digipublish_core_heading_tag( $attributes );
		echo '<section class="tp-archive-top-picks"><' . $heading_tag . ' class="tp-section-title">' . esc_html__( 'Our Top Picks', 'digipublish-core' ) . '</' . $heading_tag . '><div class="tp-archive-top-picks__grid">';
		foreach ( $top_ids as $i => $post_id ) {
			$cls        = 0 === $i ? 'tp-top-pick tp-top-pick--lead' : 'tp-top-pick';
			$image_size = 0 === $i ? 'medium_large' : 'medium';
			$category_markup = ( $attributes['showCategory'] ?? true ) ? techpress_editorial_category_markup( $post_id ) : '';
			echo '<article class="' . esc_attr( $cls ) . '"><a class="tp-top-pick__image" href="' . esc_url( get_permalink( $post_id ) ) . '">' . techpress_editorial_image_markup( $post_id, digipublish_core_image_size( $attributes, $image_size ), false, 0 === $i ? '(max-width: 760px) 86vw, 45vw' : '(max-width: 760px) 86vw, 22vw' ) . '</a><div class="tp-top-pick__body">' . $category_markup . '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>' . techpress_editorial_meta_markup( $post_id, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true ) . '</div></article>';
		}
		echo '</div></section>';
	}
}

if ( $top_ids_for_exclusion ) {
	$post_items = array_values(
		array_filter(
			$post_items,
			static function ( $item ) use ( $top_ids_for_exclusion ) {
				return $item instanceof WP_Post && ! in_array( (int) $item->ID, $top_ids_for_exclusion, true );
			}
		)
	);
}

$heading_tag = digipublish_core_heading_tag( $attributes );
echo '<section class="tp-archive-feed alignwide"><div class="tp-archive-feed__heading"><' . $heading_tag . ' class="tp-section-title">' . esc_html( $context_label ) . '</' . $heading_tag . '></div><div class="tp-archive-story-grid" style="--tp-archive-columns:' . esc_attr( $columns ) . '">';
foreach ( $post_items as $item ) {
	$id = (int) $item->ID;
	$category_markup = ( $attributes['showCategory'] ?? true ) ? techpress_editorial_category_markup( $id ) : '';
	echo '<article class="tp-archive-story"><a class="tp-archive-story__image" href="' . esc_url( get_permalink( $id ) ) . '">' . techpress_editorial_image_markup( $id, digipublish_core_image_size( $attributes, 'medium' ), false, '(max-width: 420px) 100vw, (max-width: 760px) 50vw, 20vw' ) . '</a>' . $category_markup . '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>' . techpress_editorial_meta_markup( $id, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true ) . '</article>';
}
echo '</div>';

$links = paginate_links(
	array(
		'total'     => (int) $query->max_num_pages,
		'current'   => $paged,
		'type'      => 'list',
		'prev_text' => '←',
		'next_text' => '→',
	)
);
if ( $links ) {
	echo '<nav class="tp-pagination" aria-label="' . esc_attr__( 'Posts pagination', 'digipublish-core' ) . '">' . wp_kses_post( $links ) . '</nav>';
}
echo '</section></div>';
