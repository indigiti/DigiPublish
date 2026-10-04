<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$count  = isset( $attributes['postsToShow'] ) ? max( 1, min( 60, absint( $attributes['postsToShow'] ) ) ) : 16;
$letter = '';
if ( isset( $_GET['letter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $letter = strtoupper( sanitize_text_field( wp_unslash( $_GET['letter'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $letter = preg_match( '/^[A-Z]$/', $letter ) ? $letter : '';
}
$args = array( 'post_type' => 'tech_term', 'post_status' => 'publish', 'posts_per_page' => $count, 'orderby' => 'modified', 'order' => 'DESC', 'ignore_sticky_posts' => true, 'no_found_rows' => true );
$letter_filter = null;
if ( $letter ) {
    $args['orderby'] = 'title'; $args['order'] = 'ASC'; $args['techpress_initial_letter'] = $letter;
    $letter_filter = static function ( $where, $wp_query ) use ( $letter ) { global $wpdb; if ( $letter !== $wp_query->get( 'techpress_initial_letter' ) ) { return $where; } return $where . $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", $wpdb->esc_like( $letter ) . '%' ); };
    add_filter( 'posts_where', $letter_filter, 10, 2 );
}
$query = new WP_Query( $args );
if ( $letter_filter ) { remove_filter( 'posts_where', $letter_filter, 10 ); }
$terms = $query->posts;
$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-term-index' ) );
echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['heading'] ) ) {
    $words = preg_split( '/\s+/', trim( $attributes['heading'] ) );
    $last = array_pop( $words );
    echo '<h2 class="tp-dictionary-wordmark">' . esc_html( implode( ' ', $words ) ) . ( $words ? ' ' : '' ) . '<span>' . esc_html( $last ) . '</span></h2>';
}
if ( $attributes['showSearch'] ?? true ) {
    echo '<form class="tp-term-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"><label class="screen-reader-text" for="tp-term-search-input">' . esc_html__( 'Search dictionary', 'digipublish-core' ) . '</label><input id="tp-term-search-input" type="search" name="s" placeholder="' . esc_attr__( 'Search news, guides and reviews', 'digipublish-core' ) . '"><input type="hidden" name="post_type" value="tech_term"><button type="submit" aria-label="' . esc_attr__( 'Search dictionary', 'digipublish-core' ) . '"><span aria-hidden="true"></span></button></form>';
}
if ( $attributes['showAlphabet'] ?? true ) {
    echo '<nav class="tp-alphabet" aria-label="' . esc_attr__( 'Dictionary alphabet', 'digipublish-core' ) . '">';
    foreach ( range( 'A', 'Z' ) as $character ) { $url = add_query_arg( 'letter', $character, get_post_type_archive_link( 'tech_term' ) ?: home_url( '/dictionary/' ) ); echo '<a href="' . esc_url( $url ) . '">' . esc_html( $character ) . '</a>'; }
    echo '</nav>';
}
if ( $terms ) {
    echo '<div class="tp-term-trending-head"><h3>' . esc_html__( 'Recently Updated Terms', 'digipublish-core' ) . '</h3><span aria-hidden="true">↗</span></div>';
    echo '<div class="tp-term-links">';
    foreach ( array_slice( $terms, 0, min( 12, count( $terms ) ) ) as $term ) { echo '<a href="' . esc_url( get_permalink( $term->ID ) ) . '">' . esc_html( get_the_title( $term->ID ) ) . '</a>'; }
    echo '</div>';
    if ( $attributes['showPopular'] ?? true ) {
        
        $popular_heading = trim( (string) ( $attributes['popularHeading'] ?? '' ) );
        if ( '' === $popular_heading ) {
            $popular_heading = __( 'Featured Definitions', 'digipublish-core' );
        }
        echo '<div class="tp-term-popular-head"><h3>' . esc_html( $popular_heading ) . '</h3><a href="' . esc_url( get_post_type_archive_link( 'tech_term' ) ?: home_url( '/dictionary/' ) ) . '">' . esc_html__( 'More', 'digipublish-core' ) . ' <span aria-hidden="true">→</span></a></div>';
        echo '<div class="tp-term-popular">';
        foreach ( array_slice( $terms, 0, 4 ) as $term ) {
            $topics = get_the_terms( $term->ID, 'tech_topic' );
            echo '<article class="tp-term-card">';
            if ( $topics && ! is_wp_error( $topics ) ) { echo '<span class="tp-category">' . esc_html( $topics[0]->name ) . '</span>'; }
            echo '<h4><a href="' . esc_url( get_permalink( $term->ID ) ) . '">' . esc_html( get_the_title( $term->ID ) ) . '</a></h4>';
            $excerpt = get_the_excerpt( $term->ID ); if ( $excerpt ) { echo '<p>' . esc_html( wp_trim_words( $excerpt, 22 ) ) . '</p>'; }
            echo '<a class="tp-term-card__more" href="' . esc_url( get_permalink( $term->ID ) ) . '">' . esc_html__( 'Full Explanation', 'digipublish-core' ) . '</a>';
            echo '</article>';
        }
        echo '</div>';
    }
} else {
    $cats = techpress_editorial_get_top_categories( 12, false );
    if ( $cats ) {
        echo '<div class="tp-term-trending-head"><h3>' . esc_html__( 'Explore Topics', 'digipublish-core' ) . '</h3><span aria-hidden="true">↗</span></div><div class="tp-term-links">';
        foreach ( $cats as $cat ) { echo '<a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>'; }
        echo '</div>';
    }
}
echo '</section>';
