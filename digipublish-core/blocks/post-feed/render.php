<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query_args = techpress_editorial_post_query_args( $attributes );
$query = new WP_Query( $query_args );
if ( ! $query->have_posts() ) {
	return;
}

$layout  = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'grid-3';
$allowed = array( 'list', 'grid-2', 'grid-3', 'grid-4', 'grid-5' );
if ( ! in_array( $layout, $allowed, true ) ) {
	$layout = 'grid-3';
}

$classes = array_merge( array( 'tp-post-feed' ), digipublish_core_visibility_classes( $attributes ) );
$styles = array();
$desktop = absint( $attributes['columnsDesktop'] ?? 0 );
$tablet  = absint( $attributes['columnsTablet'] ?? 0 );
$mobile  = absint( $attributes['columnsMobile'] ?? 0 );
if ( $desktop ) { $styles[] = '--dp-columns-desktop:' . max( 1, min( 6, $desktop ) ); }
if ( $tablet ) { $styles[] = '--dp-columns-tablet:' . max( 1, min( 6, $tablet ) ); }
if ( $mobile ) { $styles[] = '--dp-columns-mobile:' . max( 1, min( 3, $mobile ) ); }
foreach ( array( 'columnGap' => '--dp-column-gap', 'rowGap' => '--dp-row-gap', 'cardRadius' => '--dp-card-radius', 'cardMinHeight' => '--dp-card-min-height', 'headingFontSize' => '--dp-heading-size' ) as $key => $var ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) { $styles[] = $var . ':' . $value; }
}
$aspect = $attributes['imageAspect'] ?? '';
if ( in_array( $aspect, array( '16/9', '4/3', '3/2', '1/1' ), true ) ) {
	$styles[] = '--dp-image-aspect:' . $aspect;
}

$extra = array( 'class' => implode( ' ', $classes ) );
if ( $styles ) {
	$extra['style'] = implode( ';', $styles ) . ';';
}
$wrapper = get_block_wrapper_attributes( $extra );

echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['heading'] ) ) {
	$tag = digipublish_core_heading_tag( $attributes );
	echo '<' . $tag . ' class="tp-section-title">' . esc_html( $attributes['heading'] ) . '</' . $tag . '>';
}
echo '<div class="tp-feed tp-feed--' . esc_attr( $layout ) . '">';
foreach ( $query->posts as $post ) {
	echo techpress_editorial_card_markup( $post->ID, $attributes );
}
echo '</div>';

if ( ! empty( $attributes['avoidDuplicates'] ) ) {
	digipublish_core_rendered_post_ids( wp_list_pluck( $query->posts, 'ID' ) );
}

if ( 'numbers' === ( $attributes['paginationType'] ?? 'none' ) && $query->max_num_pages > 1 ) {
	$current = max( 1, absint( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
	$links = paginate_links(
		array(
			'current'   => $current,
			'total'     => (int) $query->max_num_pages,
			'type'      => 'list',
			'prev_text' => __( 'Previous', 'digipublish-core' ),
			'next_text' => __( 'Next', 'digipublish-core' ),
		)
	);
	if ( $links ) {
		echo '<nav class="tp-post-feed__pagination" aria-label="' . esc_attr__( 'Posts pagination', 'digipublish-core' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}

echo '</section>';
