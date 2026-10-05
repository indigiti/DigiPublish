<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$source = sanitize_key( $attributes['sourceMode'] ?? 'archive' );
if ( ! in_array( $source, array( 'archive', 'related', 'latest' ), true ) ) {
	$source = 'archive';
}
$count = max( 3, min( 24, absint( $attributes['postsPerPage'] ?? 12 ) ) );
$columns = max( 2, min( 5, absint( $attributes['columns'] ?? 4 ) ) );
$paged = max( 1, absint( get_query_var( 'paged' ) ) );

$args = array(
	'post_type'              => 'digipublish_gallery',
	'post_status'            => 'publish',
	'posts_per_page'         => $count,
	'orderby'                => 'date',
	'order'                  => 'DESC',
	'ignore_sticky_posts'    => true,
	'update_post_meta_cache' => true,
	'update_post_term_cache' => true,
);

if ( 'archive' === $source ) {
	$args['paged'] = $paged;
	$category_slug = sanitize_title( (string) get_query_var( 'gallery_category' ) );
	if ( $category_slug ) {
		$args['category_name'] = $category_slug;
	}
} elseif ( 'related' === $source && is_singular( 'digipublish_gallery' ) ) {
	$current_id = get_queried_object_id();
	$args['post__not_in'] = array( $current_id );
	$categories = wp_get_post_categories( $current_id );
	if ( $categories ) {
		$args['category__in'] = array( (int) $categories[0] );
	}
	$args['no_found_rows'] = true;
} else {
	$args['no_found_rows'] = true;
}

$query = new WP_Query( $args );
if ( ! $query->posts ) {
	if ( is_admin() ) {
		$empty = get_block_wrapper_attributes( array( 'class' => 'tp-gallery-archive tp-gallery-archive--empty' ) );
		echo '<section ' . $empty . '><p>' . esc_html__( 'No photo galleries are available yet.', 'digipublish-core' ) . '</p></section>';
	}
	return;
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'tp-gallery-archive tp-gallery-archive--cols-' . $columns,
	)
);
$heading = trim( (string) ( $attributes['heading'] ?? '' ) );

echo '<section ' . $wrapper . '>';
if ( $heading ) {
	echo '<div class="tp-gallery-archive__head"><h2>' . esc_html( $heading ) . '</h2>';
	if ( 'related' === $source ) {
		$archive_url = get_post_type_archive_link( 'digipublish_gallery' );
		if ( $archive_url ) {
			echo '<a href="' . esc_url( $archive_url ) . '">' . esc_html__( 'See all galleries', 'digipublish-core' ) . ' →</a>';
		}
	}
	echo '</div>';
}

if ( 'archive' === $source && ( $attributes['showFilters'] ?? true ) ) {
	$terms = digipublish_core_get_gallery_categories( 10 );
	if ( ! is_wp_error( $terms ) && $terms ) {
		$current = sanitize_title( (string) get_query_var( 'gallery_category' ) );
		echo '<nav class="tp-gallery-archive__filters" aria-label="' . esc_attr__( 'Gallery categories', 'digipublish-core' ) . '">';
		echo '<a class="' . ( ! $current ? 'is-active' : '' ) . '" href="' . esc_url( get_post_type_archive_link( 'digipublish_gallery' ) ) . '">' . esc_html__( 'All', 'digipublish-core' ) . '</a>';
		foreach ( $terms as $term ) {
			$url = home_url( user_trailingslashit( 'photo-gallery/category/' . $term->slug ) );
			echo '<a class="' . ( $current === $term->slug ? 'is-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
		}
		echo '</nav>';
	}
}

echo '<div class="tp-gallery-archive__grid">';
foreach ( $query->posts as $post ) {
	$post_id = $post->ID;
	$photo_count = digipublish_core_get_gallery_slide_count( $post_id );
	$categories = get_the_category( $post_id );
	$category = $categories ? $categories[0] : null;

	echo '<article class="tp-gallery-card">';
	echo '<a class="tp-gallery-card__image" href="' . esc_url( get_permalink( $post_id ) ) . '">';
	echo techpress_editorial_image_markup( $post_id, 'medium_large', false, '(max-width: 680px) 100vw, (max-width: 1120px) 50vw, 25vw' );
	if ( $photo_count ) {
		echo '<span class="tp-gallery-card__count">' . esc_html( sprintf( _n( '%d Photo', '%d Photos', $photo_count, 'digipublish-core' ), $photo_count ) ) . '</span>';
	}
	echo '</a><div class="tp-gallery-card__body">';
	if ( $category instanceof WP_Term ) {
		echo '<a class="tp-gallery-card__category" href="' . esc_url( home_url( user_trailingslashit( 'photo-gallery/category/' . $category->slug ) ) ) . '">' . esc_html( $category->name ) . '</a>';
	}
	echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
	if ( $attributes['showExcerpt'] ?? false ) {
		echo '<p>' . esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22 ) ) . '</p>';
	}
	echo '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( get_option( 'date_format' ), $post_id ) ) . '</time>';
	echo '</div></article>';
}
echo '</div>';

if ( 'archive' === $source && ( $attributes['showPagination'] ?? true ) && $query->max_num_pages > 1 ) {
	$links = paginate_links(
		array(
			'total'     => $query->max_num_pages,
			'current'   => $paged,
			'type'      => 'list',
			'prev_text' => '←',
			'next_text' => '→',
		)
	);
	if ( $links ) {
		echo '<nav class="tp-gallery-archive__pagination" aria-label="' . esc_attr__( 'Gallery pagination', 'digipublish-core' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}

echo '</section>';
