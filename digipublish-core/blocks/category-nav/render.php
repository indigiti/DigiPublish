<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$ids = array_values( array_unique( array_filter( array_map( 'absint', is_array( $attributes['filterCategoryIds'] ?? null ) ? $attributes['filterCategoryIds'] : array() ) ) ) );
$legacy_slugs = array_values( array_filter( array_map( 'sanitize_title', preg_split( '/[\s,]+/', (string) ( $attributes['filterSlugs'] ?? '' ) ) ) ) );
$maximum = isset( $attributes['maximum'] ) ? max( 0, min( 1000, absint( $attributes['maximum'] ) ) ) : 0;
$legacy_limit = isset( $attributes['limit'] ) ? max( 0, min( 1000, absint( $attributes['limit'] ) ) ) : 0;
if ( ! $maximum && $legacy_limit ) { $maximum = $legacy_limit; }
$order_by = isset( $attributes['orderBy'] ) ? sanitize_key( (string) $attributes['orderBy'] ) : 'name';
$allowed_orderby = array( 'name','count','slug__in','id','term_id' );
if ( ! in_array( $order_by, $allowed_orderby, true ) ) { $order_by = 'name'; }
$order = isset( $attributes['order'] ) && 'DESC' === strtoupper( (string) $attributes['order'] ) ? 'DESC' : 'ASC';
$args = array( 'taxonomy'=>'category','hide_empty'=>true,'order'=>$order );
if ( $maximum > 0 ) { $args['number'] = $maximum; }
if ( $ids ) {
	$args['include'] = $ids;
	$args['orderby'] = 'slug__in' === $order_by ? 'include' : ( 'id' === $order_by ? 'term_id' : $order_by );
} elseif ( $legacy_slugs ) {
	$args['slug'] = $legacy_slugs;
	$args['orderby'] = 'slug__in' === $order_by ? 'include' : ( 'id' === $order_by ? 'term_id' : $order_by );
} else {
	$args['orderby'] = 'id' === $order_by ? 'term_id' : ( 'slug__in' === $order_by ? 'name' : $order_by );
}
$categories = get_terms( $args );
if ( is_wp_error( $categories ) ) { $categories = array(); }
$alignment = isset( $attributes['alignment'] ) && in_array( $attributes['alignment'], array( 'flex-start','flex-end','center' ), true ) ? $attributes['alignment'] : 'center';
$classes = array_merge( array( 'tp-category-nav','dp-caards-category-navigation' ), digipublish_core_visibility_classes( $attributes ) );
$wrapper = get_block_wrapper_attributes( array( 'class'=>implode( ' ', $classes ), 'style'=>'--dp-category-alignment:' . $alignment . ';' ) );
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
?>