<?php
/**
 * DigiPublish Posts block renderer.
 *
 * Layout semantics and pagination behavior are adapted from the GPL-3.0
 * Caards theme by Code Supply Co. See THIRD_PARTY_NOTICES.md.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

$pagination_type = isset( $attributes['paginationType'] ) ? sanitize_key( (string) $attributes['paginationType'] ) : 'none';
$pagination_type = 'standard' === $pagination_type ? 'numbers' : $pagination_type;
// Caards carousel layouts are slide collections, not paginated post archives.
if ( $is_carousel ) {
	$pagination_type = 'none';
}

$query_attributes = $attributes;
$query_attributes['paginationType'] = $pagination_type;
if ( ! empty( $attributes['relatedPosts'] ) && is_singular() ) {
	$query_attributes['_relatedPostId'] = get_queried_object_id();
}

$query = new WP_Query( techpress_editorial_post_query_args( $query_attributes ) );
if ( ! $query->have_posts() ) {
	return;
}

$classes = array_merge(
	array( 'tp-post-feed', 'tp-post-feed--layout-' . $layout ),
	digipublish_core_visibility_classes( $attributes )
);
if ( $is_carousel ) {
	$classes[] = 'tp-post-feed--carousel';
}

$styles = array();
$legacy_columns = str_starts_with( $layout, 'grid-' ) ? max( 1, min( 5, absint( substr( $layout, 5 ) ) ) ) : 1;
$default_columns = $is_carousel ? 4 : ( $is_modern ? 1 : $legacy_columns );
$desktop = absint( $attributes['columnsDesktop'] ?? 0 ) ?: $default_columns;
$tablet  = absint( $attributes['columnsTablet'] ?? 0 ) ?: ( $is_horizontal ? 1 : min( 2, $desktop ) );
$mobile  = absint( $attributes['columnsMobile'] ?? 0 ) ?: 1;

if ( 'list' !== $layout ) {
	$styles[] = '--dp-columns-desktop:' . max( 1, min( 6, $desktop ) );
	$styles[] = '--dp-columns-tablet:' . max( 1, min( 6, $tablet ) );
	$styles[] = '--dp-columns-mobile:' . max( 1, min( 3, $mobile ) );
}

foreach ( array(
	'columnGap'           => '--dp-column-gap',
	'rowGap'              => '--dp-row-gap',
	'cardRadius'          => '--dp-card-radius',
	'cardMinHeight'       => '--dp-card-min-height',
	'headingFontSize'     => '--dp-heading-size',
	'cardHeadingFontSize' => '--dp-card-heading-size',
	'excerptFontSize'     => '--dp-excerpt-size',
	'imageBorderRadius'   => '--dp-image-radius',
) as $key => $var ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $var . ':' . $value;
	}
}

$content_gap_defaults = array(
	'standard-1'=>'32px','standard-2'=>'32px','standard-3'=>'32px','standard-4'=>'32px',
	'horizontal-1'=>'16px','horizontal-2'=>'40px','horizontal-3'=>'40px',
);
$content_gap = digipublish_core_css_length( $attributes['contentGap'] ?? '', $content_gap_defaults[ $layout ] ?? '16px' );
$styles[] = '--dp-content-gap:' . $content_gap;

$image_width_map = array( 'one-fourth'=>'25%', 'one-third'=>'33.333%', 'half'=>'50%' );
$image_width_key = sanitize_key( (string) ( $attributes['imageWidth'] ?? '' ) );
if ( ! $image_width_key ) {
	$image_width_key = 'horizontal-3' === $layout ? 'half' : 'one-third';
}
$styles[] = '--dp-image-width:' . ( $image_width_map[ $image_width_key ] ?? '33.333%' );

$content_align = isset( $attributes['contentAlign'] ) && in_array( $attributes['contentAlign'], array( 'flex-start','center','flex-end','space-between' ), true ) ? $attributes['contentAlign'] : 'space-between';
$image_align = isset( $attributes['imageAlign'] ) && in_array( $attributes['imageAlign'], array( 'flex-start','center','flex-end','stretch' ), true ) ? $attributes['imageAlign'] : 'flex-start';
$styles[] = '--dp-content-align:' . $content_align;
$styles[] = '--dp-image-align:' . $image_align;

foreach ( array(
	'headingColor'        => '--dp-post-heading-color',
	'headingHoverColor'   => '--dp-post-heading-hover',
	'excerptColor'        => '--dp-post-excerpt-color',
	'metaColor'           => '--dp-post-meta-color',
	'metaLinksColor'      => '--dp-post-meta-link-color',
	'metaLinksHoverColor' => '--dp-post-meta-link-hover',
	'categoryColor'       => '--dp-post-category-color',
	'categoryHoverColor'  => '--dp-post-category-hover',
	'readMoreColor'       => '--dp-post-more-color',
	'readMoreHoverColor'  => '--dp-post-more-hover',
	'borderColor'         => '--dp-post-border-color',
) as $key => $var ) {
	$value = sanitize_hex_color( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $var . ':' . $value;
	}
}

foreach ( array(
	'marginTop'     => 'margin-top',
	'marginBottom'  => 'margin-bottom',
	'marginLeft'    => 'margin-left',
	'marginRight'   => 'margin-right',
	'paddingTop'    => 'padding-top',
	'paddingBottom' => 'padding-bottom',
	'paddingLeft'   => 'padding-left',
	'paddingRight'  => 'padding-right',
) as $key => $property ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $property . ':' . $value;
	}
}

$orientation_map = array(
	'stretch'         => 'auto',
	'landscape'       => '4/3',
	'landscape-3-2'   => '3/2',
	'landscape-16-9'  => '16/9',
	'landscape-21-10' => '21/10',
	'portrait'        => '3/4',
	'portrait-2-3'    => '2/3',
	'square'          => '1/1',
);
$orientation = isset( $attributes['imageOrientation'] ) ? sanitize_key( (string) $attributes['imageOrientation'] ) : '';
if ( ! $orientation ) {
	if ( in_array( $layout, array( 'tile-1','tile-2','carousel-1','carousel-2' ), true ) ) {
		$orientation = 'stretch';
	} elseif ( in_array( $layout, array( 'horizontal-2','horizontal-3' ), true ) ) {
		$orientation = 'square';
	} elseif ( ! in_array( $layout, array( 'tile-3','tile-4','horizontal-4','horizontal-5' ), true ) ) {
		$orientation = 'original';
	}
}
if ( isset( $orientation_map[ $orientation ] ) ) {
	$styles[] = '--dp-image-aspect:' . $orientation_map[ $orientation ];
} else {
	$legacy_aspect = $attributes['imageAspect'] ?? '';
	if ( in_array( $legacy_aspect, array( '16/9', '4/3', '3/2', '1/1' ), true ) ) {
		$styles[] = '--dp-image-aspect:' . $legacy_aspect;
	}
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

$async_pagination = in_array( $pagination_type, array( 'ajax', 'infinite' ), true );
if ( $async_pagination && $query->max_num_pages > 1 ) {
	$async_attributes = $attributes;
	$async_attributes['paginationType'] = $pagination_type;
	if ( ! empty( $attributes['avoidDuplicates'] ) ) {
		$async_attributes['_excludePostIds'] = digipublish_core_rendered_post_ids();
	}
	if ( ! empty( $attributes['relatedPosts'] ) && is_singular() ) {
		$async_attributes['_relatedPostId'] = get_queried_object_id();
	}
	$extra['data-dp-post-feed'] = '1';
	$extra['data-dp-pagination'] = $pagination_type;
	$extra['data-dp-page'] = '1';
	$extra['data-dp-max-pages'] = (string) $query->max_num_pages;
	$extra['data-dp-rest-url'] = esc_url_raw( rest_url( 'digipublish/v1/post-feed' ) );
	$extra['data-dp-attributes'] = wp_json_encode( $async_attributes );
}

if ( $is_carousel ) {
	$extra['data-dp-carousel-autoplay'] = ! empty( $attributes['carouselAutoplay'] ) ? '1' : '0';
	$extra['data-dp-carousel-dots'] = ! empty( $attributes['carouselDots'] ) ? '1' : '0';
	$extra['data-dp-carousel-wrap'] = ! empty( $attributes['carouselWrap'] ) ? '1' : '0';
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

$current_page = max( 1, absint( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
$base_index = ( $current_page - 1 ) * max( 1, absint( $attributes['postsToShow'] ?? 4 ) );
foreach ( $query->posts as $index => $post ) {
	$card_attributes = $attributes;
	$card_attributes['_cardIndex'] = $base_index + $index + 1;
	echo digipublish_core_post_feed_card_markup( $post->ID, $card_attributes );

	if ( 'masonry-1' === $layout && ! empty( $attributes['masonryWidgets'] ) ) {
		$current = $index + 1;
		$after = max( 1, absint( $attributes['masonryWidgetsAfter'] ?? 3 ) );
		if ( 0 === $current % $after ) {
			$sidebar = sanitize_key( (string) ( $attributes['masonryWidgetArea'] ?? 'sidebar-archive' ) );
			echo digipublish_core_post_feed_loop_widget( $sidebar, $current, $after, ! empty( $attributes['masonryWidgetsRepeat'] ) );
		}
	}
}
echo '</div>';

if ( $is_carousel ) {
	$total = count( $query->posts );
	echo '<div class="tp-post-feed__carousel-organizer">';
	echo '<div class="tp-post-feed__carousel-counter" aria-live="polite"><span data-dp-carousel-current>1</span><span aria-hidden="true"> / </span><span>' . esc_html( $total ) . '</span></div>';
	if ( ! empty( $attributes['carouselDots'] ) ) {
		echo '<div class="tp-post-feed__carousel-dots" role="tablist" aria-label="' . esc_attr__( 'Carousel slides', 'digipublish-core' ) . '">';
		for ( $i = 0; $i < $total; $i++ ) {
			echo '<button type="button" class="tp-post-feed__carousel-dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-dp-carousel-dot="' . esc_attr( $i ) . '" aria-label="' . esc_attr( sprintf( __( 'Go to slide %d', 'digipublish-core' ), $i + 1 ) ) . '"></button>';
		}
		echo '</div>';
	}
	echo '<div class="tp-post-feed__carousel-nav" aria-label="' . esc_attr__( 'Posts carousel navigation', 'digipublish-core' ) . '">';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-prev aria-label="' . esc_attr__( 'Previous posts', 'digipublish-core' ) . '">←</button>';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-next aria-label="' . esc_attr__( 'Next posts', 'digipublish-core' ) . '">→</button>';
	echo '</div></div></div>';
}

if ( ! empty( $attributes['avoidDuplicates'] ) ) {
	digipublish_core_rendered_post_ids( wp_list_pluck( $query->posts, 'ID' ) );
}

if ( 'numbers' === $pagination_type && $query->max_num_pages > 1 ) {
	$links = paginate_links(
		array(
			'current'   => $current_page,
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

if ( $async_pagination && $query->max_num_pages > 1 ) {
	if ( 'ajax' === $pagination_type ) {
		echo '<div class="tp-post-feed__load-more-wrap"><button type="button" class="tp-post-feed__load-more" data-dp-load-more>' . esc_html__( 'Load More', 'digipublish-core' ) . '</button></div>';
	} else {
		echo '<div class="tp-post-feed__infinite-sentinel" data-dp-infinite-sentinel aria-hidden="true"></div>';
	}
	echo '<div class="tp-post-feed__load-status" data-dp-load-status aria-live="polite"></div>';
}

echo '</section>';
