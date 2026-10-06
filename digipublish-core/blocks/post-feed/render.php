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

$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'standard-1';
$allowed = array(
	'list', 'grid-2', 'grid-3', 'grid-4', 'grid-5',
	'standard-1', 'standard-2', 'standard-3', 'standard-4',
	'masonry-1',
	'horizontal-1', 'horizontal-2', 'horizontal-3', 'horizontal-4', 'horizontal-5',
	'tile-1', 'tile-2', 'tile-3', 'tile-4',
	'carousel-1', 'carousel-2',
);
if ( ! in_array( $layout, $allowed, true ) ) {
	$layout = 'standard-1';
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

$query = new WP_Query( digipublish_core_query_post_args( $query_attributes ) );
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

$source_columns = array( 'desktop' => 1, 'laptop' => 1, 'tablet' => 1, 'mobile' => 1 );
if ( 'carousel-1' === $layout ) {
	$source_columns = array( 'desktop' => 4, 'laptop' => 4, 'tablet' => 2, 'mobile' => 1 );
} elseif ( 'carousel-2' === $layout ) {
	$source_columns = array( 'desktop' => 4, 'laptop' => 4, 'tablet' => 3, 'mobile' => 1 );
} elseif ( ! $is_modern && 'list' !== $layout ) {
	$source_columns = array( 'desktop' => $legacy_columns, 'laptop' => min( $legacy_columns, 4 ), 'tablet' => min( $legacy_columns, 2 ), 'mobile' => 1 );
}

$desktop = absint( $attributes['columnsDesktop'] ?? 0 ) ?: $source_columns['desktop'];
$laptop  = absint( $attributes['columnsLaptop'] ?? 0 ) ?: ( ! empty( $attributes['columnsDesktop'] ) ? $desktop : $source_columns['laptop'] );
$tablet  = absint( $attributes['columnsTablet'] ?? 0 ) ?: $source_columns['tablet'];
$mobile  = absint( $attributes['columnsMobile'] ?? 0 ) ?: $source_columns['mobile'];

if ( 'list' !== $layout ) {
	$styles[] = '--dp-columns-desktop:' . max( 1, min( 6, $desktop ) );
	$styles[] = '--dp-columns-laptop:' . max( 1, min( 6, $laptop ) );
	$styles[] = '--dp-columns-tablet:' . max( 1, min( 6, $tablet ) );
	$styles[] = '--dp-columns-mobile:' . max( 1, min( 3, $mobile ) );
}

$length_value = static function ( $responsive_key, $legacy_key, $default = '' ) use ( $attributes ) {
	$value = digipublish_core_css_length( $attributes[ $responsive_key ] ?? '' );
	if ( $value ) {
		return $value;
	}
	$value = $legacy_key ? digipublish_core_css_length( $attributes[ $legacy_key ] ?? '' ) : '';
	return $value ?: $default;
};

foreach ( array(
	'columnGap' => array( 'columnGapDesktop', 'columnGapLaptop', 'columnGapTablet', 'columnGapMobile', '40px' ),
	'rowGap'    => array( 'rowGapDesktop', 'rowGapLaptop', 'rowGapTablet', 'rowGapMobile', '40px' ),
) as $legacy_key => $config ) {
	foreach ( array( 'd', 'l', 't', 'm' ) as $index => $suffix ) {
		$value = $length_value( $config[ $index ], $legacy_key, $config[4] );
		$styles[] = '--dp-' . ( 'columnGap' === $legacy_key ? 'column-gap-' : 'row-gap-' ) . $suffix . ':' . $value;
	}
}

$content_gap_defaults = array(
	'standard-1' => array( '32px', '32px', '32px', '32px' ),
	'standard-2' => array( '32px', '32px', '32px', '32px' ),
	'standard-3' => array( '32px', '32px', '32px', '32px' ),
	'standard-4' => array( '32px', '32px', '32px', '32px' ),
	'horizontal-1' => array( '16px', '16px', '16px', '16px' ),
	'horizontal-2' => array( '40px', '40px', '40px', '40px' ),
	'horizontal-3' => array( '40px', '40px', '40px', '20px' ),
);
$content_defaults = $content_gap_defaults[ $layout ] ?? array( '16px', '16px', '16px', '16px' );
foreach ( array( 'Desktop' => 'd', 'Laptop' => 'l', 'Tablet' => 't', 'Mobile' => 'm' ) as $device => $suffix ) {
	$index = array_search( $suffix, array( 'd', 'l', 't', 'm' ), true );
	$styles[] = '--dp-content-gap-' . $suffix . ':' . $length_value( 'contentGap' . $device, 'contentGap', $content_defaults[ $index ] );
}

$title_defaults = array( '1.5rem', '1.5rem', '1.5rem', '1.5rem' );
if ( in_array( $layout, array( 'horizontal-1', 'horizontal-2', 'horizontal-4', 'horizontal-5', 'tile-3', 'tile-4' ), true ) ) {
	$title_defaults = array( '1rem', '1rem', '1rem', '1rem' );
} elseif ( 'horizontal-3' === $layout ) {
	$title_defaults = array( '2.625rem', '2.625rem', '2rem', '1.5rem' );
} elseif ( $is_carousel ) {
	$title_defaults = array( '1.25rem', '1.25rem', '1.25rem', '1.25rem' );
}
foreach ( array( 'Desktop' => 'd', 'Laptop' => 'l', 'Tablet' => 't', 'Mobile' => 'm' ) as $device => $suffix ) {
	$index = array_search( $suffix, array( 'd', 'l', 't', 'm' ), true );
	$styles[] = '--dp-card-heading-size-' . $suffix . ':' . $length_value( 'cardHeadingFontSize' . $device, 'cardHeadingFontSize', $title_defaults[ $index ] );
	$styles[] = '--dp-excerpt-size-' . $suffix . ':' . $length_value( 'excerptFontSize' . $device, 'excerptFontSize', '.875rem' );
	$styles[] = '--dp-card-min-height-' . $suffix . ':' . $length_value( 'cardMinHeight' . $device, 'cardMinHeight', 'auto' );
}

foreach ( array(
	'cardRadius'        => '--dp-card-radius',
	'headingFontSize'   => '--dp-heading-size',
	'imageBorderRadius' => '--dp-image-radius',
) as $key => $var ) {
	$value = digipublish_core_css_length( $attributes[ $key ] ?? '' );
	if ( $value ) {
		$styles[] = $var . ':' . $value;
	}
}

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

$color_layouts = array( 'standard-1', 'standard-2', 'standard-3', 'standard-4', 'masonry-1', 'horizontal-1', 'horizontal-2', 'horizontal-3', 'horizontal-4', 'horizontal-5' );
if ( in_array( $layout, $color_layouts, true ) ) {
	$color_map = array(
		'headingColor'        => '--dp-post-heading-color',
		'headingHoverColor'   => '--dp-post-heading-hover',
		'excerptColor'        => '--dp-post-excerpt-color',
		'metaColor'           => '--dp-post-meta-color',
		'metaLinksColor'      => '--dp-post-meta-link-color',
		'metaLinksHoverColor' => '--dp-post-meta-link-hover',
		'categoryColor'       => '--dp-post-category-color',
		'categoryHoverColor'  => '--dp-post-category-hover',
	);
	if ( ! in_array( $layout, array( 'horizontal-4', 'horizontal-5' ), true ) ) {
		$color_map['readMoreColor'] = '--dp-post-more-color';
		$color_map['readMoreHoverColor'] = '--dp-post-more-hover';
	}
	if ( in_array( $layout, array( 'horizontal-4', 'horizontal-5' ), true ) ) {
		$color_map['borderColor'] = '--dp-post-border-color';
	}
	foreach ( $color_map as $key => $var ) {
		$value = sanitize_hex_color( $attributes[ $key ] ?? '' );
		if ( $value ) {
			$styles[] = $var . ':' . $value;
		}
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
	$styles[] = 'border-color:var(--dp-border)';
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
$interactive = $is_carousel || ( $async_pagination && $query->max_num_pages > 1 );
$interactive_context = array(
	'current'    => 0,
	'total'      => 0,
	'wrap'       => false,
	'autoplay'   => false,
	'page'       => 1,
	'maxPages'   => max( 1, (int) $query->max_num_pages ),
	'loading'    => false,
	'ended'      => false,
	'status'     => '',
	'pagination' => $pagination_type,
);

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
	$extra['data-wp-bind--aria-busy'] = 'context.loading';
}

if ( $is_carousel ) {
	$total = count( $query->posts );
	$extra['data-dp-carousel-autoplay'] = ! empty( $attributes['carouselAutoplay'] ) ? '1' : '0';
	$extra['data-dp-carousel-dots'] = ! empty( $attributes['carouselDots'] ) ? '1' : '0';
	$extra['data-dp-carousel-wrap'] = ! empty( $attributes['carouselWrap'] ) ? '1' : '0';
	$extra['data-wp-on--pointerenter'] = 'actions.pauseAutoplay';
	$extra['data-wp-on--pointerleave'] = 'actions.resumeAutoplay';
	$extra['data-wp-on--focusin'] = 'actions.pauseAutoplay';
	$extra['data-wp-on--focusout'] = 'actions.resumeAutoplay';

	$interactive_context['total'] = $total;
	$interactive_context['wrap'] = ! empty( $attributes['carouselWrap'] );
	$interactive_context['autoplay'] = ! empty( $attributes['carouselAutoplay'] );
}

if ( $interactive ) {
	$extra['data-wp-interactive'] = 'digipublish/post-feed';
	$extra['data-wp-context'] = wp_json_encode( $interactive_context );
	$extra['data-wp-init'] = 'callbacks.init';
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
	echo ' data-dp-post-carousel-track data-wp-on--scroll="callbacks.syncFromScroll"';
}
echo '>';

$current_page = max( 1, absint( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
$base_index = ( $current_page - 1 ) * max( 1, absint( $attributes['postsToShow'] ?? 4 ) );
foreach ( $query->posts as $index => $post ) {
	$card_attributes = $attributes;
	$card_attributes['_cardIndex'] = $base_index + $index + 1;
	echo digipublish_core_post_feed_card_markup( $post->ID, $card_attributes );

	if ( 'masonry-1' === $layout && ! empty( $attributes['masonryWidgets'] ) ) {
		$current = $base_index + $index + 1;
		$after = max( 1, absint( $attributes['masonryWidgetsAfter'] ?? 3 ) );
		if ( 0 === $current % $after ) {
			echo digipublish_core_post_feed_loop_pattern(
				absint( $attributes['masonryPatternId'] ?? 0 ),
				$current,
				$after,
				! empty( $attributes['masonryWidgetsRepeat'] )
			);
		}
	}
}
echo '</div>';

if ( $is_carousel ) {
	echo '<div class="tp-post-feed__carousel-organizer">';
	echo '<div class="tp-post-feed__carousel-counter" aria-live="polite"><span data-dp-carousel-current data-wp-text="state.currentDisplay">1</span><span aria-hidden="true"> / </span><span>' . esc_html( $total ) . '</span></div>';
	if ( ! empty( $attributes['carouselDots'] ) ) {
		echo '<div class="tp-post-feed__carousel-dots" role="tablist" aria-label="' . esc_attr__( 'Carousel slides', 'digipublish-core' ) . '">';
		for ( $i = 0; $i < $total; $i++ ) {
			echo '<button type="button" role="tab" class="tp-post-feed__carousel-dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-dp-carousel-dot="' . esc_attr( $i ) . '" data-wp-on--click="actions.goTo" aria-selected="' . ( 0 === $i ? 'true' : 'false' ) . '" aria-label="' . esc_attr( sprintf( __( 'Go to slide %d', 'digipublish-core' ), $i + 1 ) ) . '"></button>';
		}
		echo '</div>';
	}
	echo '<div class="tp-post-feed__carousel-nav" aria-label="' . esc_attr__( 'Posts carousel navigation', 'digipublish-core' ) . '">';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-prev data-wp-on--click="actions.previous" aria-label="' . esc_attr__( 'Previous posts', 'digipublish-core' ) . '">←</button>';
	echo '<button type="button" class="tp-post-feed__carousel-button" data-dp-carousel-next data-wp-on--click="actions.next" aria-label="' . esc_attr__( 'Next posts', 'digipublish-core' ) . '">→</button>';
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
		echo '<div class="tp-post-feed__load-more-wrap"><button type="button" class="tp-post-feed__load-more" data-dp-load-more data-wp-on--click="actions.loadNext" data-wp-bind--disabled="context.loading" data-wp-bind--hidden="context.ended">' . esc_html__( 'Load More', 'digipublish-core' ) . '</button></div>';
	} else {
		echo '<div class="tp-post-feed__infinite-sentinel" data-dp-infinite-sentinel data-wp-init="callbacks.observeInfinite" data-wp-bind--hidden="context.ended" aria-hidden="true"></div>';
	}
	echo '<div class="tp-post-feed__load-status" data-dp-load-status data-wp-text="context.status" aria-live="polite"></div>';
}

echo '</section>';
