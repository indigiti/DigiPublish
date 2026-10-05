<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query_args = techpress_editorial_post_query_args( $attributes );
$query = new WP_Query( $query_args );
if ( ! $query->have_posts() ) {
	return;
}

$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'grid-3';
$allowed = array(
	'list', 'grid-2', 'grid-3', 'grid-4', 'grid-5',
	'standard-1', 'standard-2', 'standard-3', 'standard-4',
	'masonry-1',
	'horizontal-1', 'horizontal-2', 'horizontal-3', 'horizontal-4', 'horizontal-5',
	'tile-1', 'tile-2', 'tile-3', 'tile-4',
	'carousel-1', 'carousel-2',
);
if ( ! in_array( $layout, $allowed, true ) ) {
	$layout = 'grid-3';
}

$is_horizontal = str_starts_with( $layout, 'horizontal-' ) || 'list' === $layout;
$is_carousel   = str_starts_with( $layout, 'carousel-' );
$is_modern     = ! in_array( $layout, array( 'list', 'grid-2', 'grid-3', 'grid-4', 'grid-5' ), true );

$classes = array_merge(
	array( 'tp-post-feed', 'tp-post-feed--layout-' . $layout ),
	digipublish_core_visibility_classes( $attributes )
);
if ( $is_carousel ) {
	$classes[] = 'tp-post-feed--carousel';
}

$styles = array();
$legacy_columns = str_starts_with( $layout, 'grid-' ) ? max( 1, min( 5, absint( substr( $layout, 5 ) ) ) ) : 1;
$default_columns = $is_horizontal ? 1 : ( $is_modern ? 4 : $legacy_columns );
$desktop = absint( $attributes['columnsDesktop'] ?? 0 ) ?: $default_columns;
$tablet  = absint( $attributes['columnsTablet'] ?? 0 ) ?: ( $is_horizontal ? 1 : min( 2, $desktop ) );
$mobile  = absint( $attributes['columnsMobile'] ?? 0 ) ?: 1;

if ( 'list' !== $layout ) {
	$styles[] = '--dp-columns-desktop:' . max( 1, min( 6, $desktop ) );
	$styles[] = '--dp-columns-tablet:' . max( 1, min( 6, $tablet ) );
	$styles[] = '--dp-columns-mobile:' . max( 1, min( 3, $mobile ) );
}

foreach ( array(
	'columnGap'       => '--dp-column-gap',
	'rowGap'          => '--dp-row-gap',
	'cardRadius'      => '--dp-card-radius',
	'cardMinHeight'   => '--dp-card-min-height',
	'headingFontSize' => '--dp-heading-size',
) as $key => $var ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $var . ':' . $value;
	}
}

foreach ( array(
	'marginTop'    => 'margin-top',
	'marginBottom' => 'margin-bottom',
	'marginLeft'   => 'margin-left',
	'marginRight'  => 'margin-right',
	'paddingTop'   => 'padding-top',
	'paddingBottom'=> 'padding-bottom',
	'paddingLeft'  => 'padding-left',
	'paddingRight' => 'padding-right',
) as $key => $property ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $property . ':' . $value;
	}
}

$aspect = $attributes['imageAspect'] ?? '';
if ( in_array( $aspect, array( '16/9', '4/3', '3/2', '1/1' ), true ) ) {
	$styles[] = '--dp-image-aspect:' . $aspect;
}

$block_radius = digipublish_core_css_length( $attributes['blockBorderRadius'] ?? '' );
if ( $block_radius ) {
	$styles[] = 'border-radius:' . $block_radius;
}
$border_style = isset( $attributes['blockBorderStyle'] ) ? sanitize_key( (string) $attributes['blockBorderStyle'] ) : 'none';
if ( in_array( $border_style, array( 'solid', 'dashed', 'dotted', 'double' ), true ) ) {
	$styles[] = 'border-style:' . $border_style;
	$border_width = digipublish_core_css_length( $attributes['blockBorderWidth'] ?? '', '1px' );
	$styles[] = 'border-width:' . $border_width;
	$styles[] = 'border-color:var(--tp-border)';
}

if ( ! empty( $attributes['customCss'] ) ) {
	$custom_css = safecss_filter_attr( (string) $attributes['customCss'] );
	if ( $custom_css ) {
		$styles[] = rtrim( $custom_css, ';' );
	}
}

$extra = array( 'class' => implode( ' ', array_filter( $classes ) ) );
if ( $styles ) {
	$extra['style'] = implode( ';', $styles ) . ';';
}
$wrapper = get_block_wrapper_attributes( $extra );

echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['heading'] ) ) {
	$tag = digipublish_core_heading_tag( $attributes );
	echo '<' . $tag . ' class="tp-section-title">' . esc_html( $attributes['heading'] ) . '</' . $tag . '>';
}

if ( $is_carousel ) {
	echo '<div class="tp-post-feed__carousel-shell">';
}

echo '<div class="tp-feed tp-feed--' . esc_attr( $layout ) . '"';
if ( $is_carousel ) {
	echo ' data-dp-post-carousel-track';
}
echo '>';

foreach ( $query->posts as $index => $post ) {
	$card_attributes = $attributes;
	$card_attributes['_cardIndex'] = $index + 1;
	echo techpress_editorial_card_markup( $post->ID, $card_attributes );
}
echo '</div>';

if ( $is_carousel ) {
	echo '<div class="tp-post-feed__carousel-nav" aria-label="' . esc_attr__( 'Posts carousel navigation', 'digipublish-core' ) . '">';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-prev aria-label="' . esc_attr__( 'Previous posts', 'digipublish-core' ) . '">←</button>';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-next aria-label="' . esc_attr__( 'Next posts', 'digipublish-core' ) . '">→</button>';
	echo '</div></div>';
}

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
