<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$limit = isset( $attributes['limit'] ) ? max( 1, min( 20, absint( $attributes['limit'] ) ) ) : 5;
$orderby = isset( $attributes['orderBy'] ) && in_array( $attributes['orderBy'], array( 'name', 'count', 'term_id' ), true ) ? $attributes['orderBy'] : 'count';
$order = isset( $attributes['order'] ) && 'ASC' === strtoupper( (string) $attributes['order'] ) ? 'ASC' : 'DESC';
$slugs = array_values( array_filter( array_map( 'sanitize_title', preg_split( '/[\s,]+/', (string) ( $attributes['filterSlugs'] ?? '' ) ) ) ) );
$args = array(
	'taxonomy'   => 'category',
	'hide_empty' => true,
	'number'     => $limit,
	'orderby'    => $orderby,
	'order'      => $order,
);
if ( $slugs ) { $args['slug'] = $slugs; }
$default_category = absint( get_option( 'default_category' ) );
if ( ! $slugs && $default_category ) { $args['exclude'] = array( $default_category ); }
$categories = get_terms( $args );
if ( is_wp_error( $categories ) ) { $categories = array(); }
$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-category-nav dp-caards-category-navigation' ) );
$dictionary = get_post_type_archive_link( 'tech_term' ) ?: home_url( '/dictionary/' );
$gallery = get_post_type_archive_link( 'digipublish_gallery' ) ?: home_url( '/photo-gallery/' );

echo '<nav ' . $wrapper . ' aria-label="' . esc_attr__( 'Category navigation', 'digipublish-core' ) . '"><div class="tp-category-nav__scroll">';
if ( $attributes['showDictionary'] ?? true ) { echo '<a class="tp-category-nav__item tp-category-nav__dictionary" href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'digipublish-core' ) . '</a>'; }
echo '<a class="tp-category-nav__item tp-category-nav__gallery" href="' . esc_url( $gallery ) . '">' . esc_html__( 'Photo Galleries', 'digipublish-core' ) . '</a>';
foreach ( $categories as $category ) { echo '<a class="tp-category-nav__item" href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>'; }
echo '</div><details class="tp-category-nav__mobile"><summary aria-label="' . esc_attr__( 'Open navigation menu', 'digipublish-core' ) . '"><span class="tp-category-nav__hamburger" aria-hidden="true"><i></i><i></i><i></i></span></summary><div class="tp-category-nav__mobile-panel">';
if ( $attributes['showDictionary'] ?? true ) { echo '<a href="' . esc_url( $dictionary ) . '">' . esc_html__( 'Dictionary', 'digipublish-core' ) . '</a>'; }
echo '<a href="' . esc_url( $gallery ) . '">' . esc_html__( 'Photo Galleries', 'digipublish-core' ) . '</a>';
foreach ( $categories as $category ) { echo '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>'; }
echo '</div></details>';
if ( $attributes['showSearch'] ?? true ) { echo '<a class="tp-category-nav__search" href="' . esc_url( home_url( '/?s=' ) ) . '" aria-label="' . esc_attr__( 'Search', 'digipublish-core' ) . '"><span aria-hidden="true"></span></a>'; }
echo '</nav>';
