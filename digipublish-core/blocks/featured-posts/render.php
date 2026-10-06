<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = digipublish_core_query_post_args( $attributes );
$need = max( 1, min( 12, (int) ( $attributes['postsToShow'] ?? 7 ) ) );

/*
 * One query only: fetch a slightly wider candidate set, then prefer posts with
 * featured images in PHP. This avoids the previous thumbnail meta query plus
 * fallback query on every page load.
 */
$args['posts_per_page'] = min( 24, max( $need, $need * 2 ) );
$query                  = new WP_Query( $args );
$with_images            = array();
$without_images         = array();
foreach ( $query->posts as $candidate ) {
    if ( has_post_thumbnail( $candidate->ID ) ) {
        $with_images[] = $candidate;
    } else {
        $without_images[] = $candidate;
    }
}
$posts = array_slice( array_merge( $with_images, $without_images ), 0, $need );
if ( empty( $posts ) ) { return; }
digipublish_core_rendered_post_ids( wp_list_pluck( $posts, 'ID' ) );

$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'magazine';
if ( ! in_array( $layout, array( 'magazine', 'lead-list', 'grid-3', 'grid-4' ), true ) ) { $layout = 'magazine'; }
$wrapper = get_block_wrapper_attributes( digipublish_core_design_wrapper_args( $attributes, array( 'tp-featured', 'tp-featured--' . $layout ) ) );
echo '<section ' . $wrapper . '>';

if ( ! empty( $attributes['heading'] ) ) {
    $heading_tag = digipublish_core_heading_tag( $attributes );
    echo '<div class="tp-featured__bar"><div class="tp-featured__heading-row"><' . $heading_tag . ' class="tp-section-title">' . esc_html( $attributes['heading'] ) . '</' . $heading_tag . '>';
    if ( $attributes['showFilters'] ?? true ) {
        $filter_limit = max( 1, min( 8, (int) ( $attributes['filterLimit'] ?? 6 ) ) );
        $filter_cats = digipublish_core_get_top_categories( $filter_limit, true );
        echo '<nav class="tp-featured__filters" aria-label="' . esc_attr__( 'Feature categories', 'digipublish-core' ) . '">';
        echo '<a class="tp-featured__filter is-active" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'All Features', 'digipublish-core' ) . '</a>';
        foreach ( $filter_cats as $cat ) {
            echo '<a class="tp-featured__filter" href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
        }
        echo '</nav>';
    }
    $posts_page = (int) get_option( 'page_for_posts' );
    $more_url = $posts_page ? get_permalink( $posts_page ) : home_url( '/?post_type=post' );
    echo '<a class="tp-featured__more" href="' . esc_url( $more_url ) . '">' . esc_html__( 'More', 'digipublish-core' ) . ' <span aria-hidden="true">→</span></a>';
    echo '</div></div>';
}

if ( 'magazine' === $layout ) {
    $lead = array_shift( $posts );
    $side = array_shift( $posts );
    echo '<div class="tp-featured__magazine">';
    echo '<div class="tp-featured__top">';
    echo '<a class="tp-featured__hero-image" href="' . esc_url( get_permalink( $lead->ID ) ) . '">' . digipublish_core_image_markup( $lead->ID, digipublish_core_image_size( $attributes, 'large' ), is_front_page() || is_home(), '(max-width: 720px) 100vw, 52vw' ) . '</a>';
    echo '<article class="tp-featured__hero-copy">';
    echo '<div class="tp-featured__badge"><span aria-hidden="true">◆</span> ' . esc_html__( 'Top Story', 'digipublish-core' ) . '</div>';
    if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $lead->ID ); }
    echo '<h3 class="tp-featured__hero-title"><a href="' . esc_url( get_permalink( $lead->ID ) ) . '">' . esc_html( get_the_title( $lead->ID ) ) . '</a></h3>';
    echo digipublish_core_meta_markup( $lead->ID, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true );
    if ( $attributes['showExcerpt'] ?? true ) { echo '<p class="tp-featured__hero-excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $lead->ID ), 28 ) ) . '</p>'; }
    echo '</article>';
    if ( $side ) {
        echo '<article class="tp-featured__side">';
        echo '<a class="tp-featured__side-image" href="' . esc_url( get_permalink( $side->ID ) ) . '">' . digipublish_core_image_markup( $side->ID, digipublish_core_image_size( $attributes, 'medium_large' ), false, '(max-width: 720px) 86vw, 24vw' ) . '</a>';
        if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $side->ID ); }
        echo '<h3 class="tp-featured__side-title"><a href="' . esc_url( get_permalink( $side->ID ) ) . '">' . esc_html( get_the_title( $side->ID ) ) . '</a></h3>';
        echo digipublish_core_meta_markup( $side->ID, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true );
        echo '</article>';
    }
    echo '</div>';
    if ( $posts ) {
        echo '<div class="tp-featured__strip">';
        foreach ( array_slice( $posts, 0, 5 ) as $post ) {
            echo '<article class="tp-featured__strip-item">';
            echo '<a class="tp-featured__strip-image" href="' . esc_url( get_permalink( $post->ID ) ) . '">' . digipublish_core_image_markup( $post->ID, digipublish_core_image_size( $attributes, 'medium' ), false, '(max-width: 720px) 74vw, 18vw' ) . '</a>';
            if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $post->ID ); }
            echo '<h3><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h3>';
            echo digipublish_core_meta_markup( $post->ID, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true );
            echo '</article>';
        }
        echo '</div>';
    }
    echo '</div>';
} elseif ( 'lead-list' === $layout ) {
    $lead = array_shift( $posts );
    echo '<div class="tp-featured__layout"><article class="tp-featured__lead tp-card">';
    echo '<a class="tp-featured__lead-image" href="' . esc_url( get_permalink( $lead->ID ) ) . '">' . digipublish_core_image_markup( $lead->ID, digipublish_core_image_size( $attributes, 'large' ), is_front_page() || is_home(), '(max-width: 720px) 100vw, 58vw' ) . '</a><div class="tp-card__body">';
    if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $lead->ID ); }
    echo '<h3 class="tp-featured__lead-title"><a href="' . esc_url( get_permalink( $lead->ID ) ) . '">' . esc_html( get_the_title( $lead->ID ) ) . '</a></h3>';
    if ( $attributes['showExcerpt'] ?? true ) { echo '<p class="tp-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $lead->ID ), 32 ) ) . '</p>'; }
    echo digipublish_core_meta_markup( $lead->ID, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true );
    echo '</div></article><div class="tp-featured__rail">';
    foreach ( $posts as $post ) {
        echo '<article class="tp-featured__rail-item"><a class="tp-featured__rail-image" href="' . esc_url( get_permalink( $post->ID ) ) . '">' . digipublish_core_image_markup( $post->ID, digipublish_core_image_size( $attributes, 'medium' ), false, '(max-width: 720px) 115px, 115px' ) . '</a><div class="tp-featured__rail-body">';
        if ( $attributes['showCategory'] ?? true ) { echo digipublish_core_category_markup( $post->ID ); }
        echo '<h3><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h3>';
        echo digipublish_core_meta_markup( $post->ID, $attributes['showAuthor'] ?? true, $attributes['showDate'] ?? true );
        echo '</div></article>';
    }
    echo '</div></div>';
} else {
    echo '<div class="tp-feed tp-feed--' . esc_attr( $layout ) . '">';
    foreach ( $posts as $post ) {
        echo digipublish_core_card_markup( $post->ID, array( 'showImage' => true, 'showCategory' => $attributes['showCategory'] ?? true, 'showExcerpt' => $attributes['showExcerpt'] ?? true, 'showAuthor' => $attributes['showAuthor'] ?? true, 'showDate' => $attributes['showDate'] ?? true, 'imageSize' => digipublish_core_image_size( $attributes, 'medium_large' ) ) );
    }
    echo '</div>';
}

echo '</section>';
