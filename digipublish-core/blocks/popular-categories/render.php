<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$limit   = max( 4, min( 12, absint( $attributes['limit'] ?? 8 ) ) );
$heading = $attributes['heading'] ?? __( 'Popular Categories', 'digipublish-core' );
$cats    = digipublish_core_get_top_categories( $limit, false );
if ( ! $cats ) {
	$cats = get_categories(
		array(
			'hide_empty' => false,
			'number'     => $limit,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
}
if ( ! $cats ) { return; }
$categories_page = get_page_by_path( 'categories' );
$posts_page      = (int) get_option( 'page_for_posts' );
$archive_url     = $categories_page ? get_permalink( $categories_page ) : ( $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) );
echo '<section class="tp-popular-categories alignwide"><div class="tp-popular-categories__heading"><h2 class="tp-section-title">' . esc_html( $heading ) . '</h2><a href="' . esc_url( $archive_url ) . '">' . esc_html__( 'Show All', 'digipublish-core' ) . ' <span aria-hidden="true">→</span></a></div><div class="tp-popular-categories__grid">';
foreach ( $cats as $cat ) {
	echo '<a class="tp-popular-category" href="' . esc_url( get_category_link( $cat ) ) . '"><span class="tp-popular-category__icon">' . digipublish_core_category_icon_svg( $cat ) . '</span><span class="tp-popular-category__name">' . esc_html( $cat->name ) . '</span></a>';
}
echo '</div></section>';
