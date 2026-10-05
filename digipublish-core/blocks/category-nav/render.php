<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$limit      = isset( $attributes['limit'] ) ? max( 1, min( 8, absint( $attributes['limit'] ) ) ) : 5;
$categories = techpress_editorial_get_top_categories( $limit, true );
$wrapper    = get_block_wrapper_attributes( array( 'class' => 'tp-category-nav' ) );
$dictionary = get_post_type_archive_link( 'tech_term' ) ?: home_url( '/dictionary/' );
$gallery    = get_post_type_archive_link( 'digipublish_gallery' ) ?: home_url( '/photo-gallery/' );

echo '<nav ' . $wrapper . ' aria-label="' . esc_attr__( 'Primary publication navigation', 'digipublish-core' ) . '">';
echo '<div class="tp-category-nav__scroll">';
if ( $attributes['showDictionary'] ?? true ) {
	echo '<a class="tp-category-nav__item tp-category-nav__dictionary" href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'digipublish-core' ) . '</a>';
}
echo '<a class="tp-category-nav__item tp-category-nav__gallery" href="' . esc_url( $gallery ) . '">' . esc_html__( 'Photo Galleries', 'digipublish-core' ) . '</a>';
foreach ( $categories as $category ) {
	echo '<a class="tp-category-nav__item" href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
}
echo '</div>';

// Mobile-only disclosure menu. Native <details> keeps the navigation usable with
// zero JavaScript and avoids shipping another interaction bundle on every page.
echo '<details class="tp-category-nav__mobile">';
echo '<summary aria-label="' . esc_attr__( 'Open navigation menu', 'digipublish-core' ) . '"><span class="tp-category-nav__hamburger" aria-hidden="true"><i></i><i></i><i></i></span></summary>';
echo '<div class="tp-category-nav__mobile-panel">';
if ( $attributes['showDictionary'] ?? true ) {
	echo '<a href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'digipublish-core' ) . '</a>';
}
echo '<a href="' . esc_url( $gallery ) . '">' . esc_html__( 'Photo Galleries', 'digipublish-core' ) . '</a>';
foreach ( $categories as $category ) {
	echo '<a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
}
echo '</div></details>';

if ( $attributes['showSearch'] ?? true ) {
	echo '<a class="tp-category-nav__search" href="' . esc_url( home_url( '/?s=' ) ) . '" aria-label="' . esc_attr__( 'Search', 'digipublish-core' ) . '"><span aria-hidden="true"></span></a>';
}
echo '</nav>';
