<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$limit      = isset( $attributes['limit'] ) ? max( 1, min( 8, absint( $attributes['limit'] ) ) ) : 5;
$categories = techpress_editorial_get_top_categories( $limit, true );
$wrapper    = get_block_wrapper_attributes( array( 'class' => 'tp-category-nav' ) );
$dictionary = get_post_type_archive_link( 'tech_term' ) ?: home_url( '/dictionary/' );

echo '<nav ' . $wrapper . ' aria-label="' . esc_attr__( 'Primary publication navigation', 'techpress-editorial' ) . '">';
echo '<div class="tp-category-nav__scroll">';
if ( $attributes['showDictionary'] ?? true ) {
	echo '<a class="tp-category-nav__item tp-category-nav__dictionary" href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'techpress-editorial' ) . '</a>';
}
foreach ( $categories as $category ) {
	echo '<a class="tp-category-nav__item" href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
}
echo '</div>';

// Mobile-only disclosure menu. Native <details> keeps the navigation usable with
// zero JavaScript and avoids shipping another interaction bundle on every page.
echo '<details class="tp-category-nav__mobile">';
echo '<summary aria-label="' . esc_attr__( 'Open navigation menu', 'techpress-editorial' ) . '"><span class="tp-category-nav__hamburger" aria-hidden="true"><i></i><i></i><i></i></span></summary>';
echo '<div class="tp-category-nav__mobile-panel">';
if ( $attributes['showDictionary'] ?? true ) {
	echo '<a href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'techpress-editorial' ) . '</a>';
}
foreach ( $categories as $category ) {
	echo '<a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
}
echo '</div></details>';

if ( $attributes['showSearch'] ?? true ) {
	echo '<a class="tp-category-nav__search" href="' . esc_url( home_url( '/?s=' ) ) . '" aria-label="' . esc_attr__( 'Search', 'techpress-editorial' ) . '"><span aria-hidden="true"></span></a>';
}
echo '</nav>';
