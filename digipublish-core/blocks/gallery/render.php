<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$mode = sanitize_key( $attributes['displayMode'] ?? 'story' );
if ( ! in_array( $mode, array( 'story', 'swipe', 'grid' ), true ) ) {
	$mode = 'story';
}

$settings = array(
	'showCounter'    => $attributes['showCounter'] ?? true,
	'showCaptions'   => $attributes['showCaptions'] ?? true,
	'showCredits'    => $attributes['showCredits'] ?? true,
	'showThumbnails' => $attributes['showThumbnails'] ?? false,
	'allowFullscreen'=> $attributes['allowFullscreen'] ?? true,
	'showSharing'    => $attributes['showSharing'] ?? true,
	'adInterval'     => max( 0, min( 10, absint( $attributes['adInterval'] ?? 0 ) ) ),
);

$slides = array();
foreach ( (array) ( $block->parsed_block['innerBlocks'] ?? array() ) as $inner ) {
	if ( 'digipublish/gallery-slide' === ( $inner['blockName'] ?? '' ) ) {
		$attrs = (array) ( $inner['attrs'] ?? array() );
		if ( ! empty( $attrs['imageId'] ) || ! empty( $attrs['imageUrl'] ) ) {
			$slides[] = $attrs;
		}
	}
}

$total = count( $slides );
if ( ! $total ) {
	if ( is_admin() ) {
		$empty = get_block_wrapper_attributes( array( 'class' => 'tp-gallery tp-gallery--empty' ) );
		echo '<div ' . $empty . '><p>' . esc_html__( 'Add images to start this gallery.', 'digipublish-core' ) . '</p></div>';
	}
	return;
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class'                  => 'tp-gallery tp-gallery--' . $mode,
		'data-digipublish-gallery' => '',
		'data-gallery-mode'      => $mode,
		'tabindex'               => '0',
	)
);

echo '<section ' . $wrapper . '>';

if ( ( 'swipe' === $mode && $total > 1 ) || $settings['allowFullscreen'] || $settings['showSharing'] ) {
	echo '<div class="tp-gallery__toolbar">';
	if ( 'swipe' === $mode && $total > 1 ) {
		echo '<button type="button" class="tp-gallery__nav" data-gallery-prev aria-label="' . esc_attr__( 'Previous photo', 'digipublish-core' ) . '">←</button>';
	}
	if ( 'swipe' === $mode && $settings['showCounter'] ) {
		echo '<span class="tp-gallery__counter" data-gallery-counter aria-live="polite">1 / ' . esc_html( (string) $total ) . '</span>';
	}
	if ( 'swipe' === $mode && $total > 1 ) {
		echo '<button type="button" class="tp-gallery__nav" data-gallery-next aria-label="' . esc_attr__( 'Next photo', 'digipublish-core' ) . '">→</button>';
	}
	if ( $settings['allowFullscreen'] ) {
		echo '<button type="button" class="tp-gallery__tool" data-gallery-fullscreen aria-label="' . esc_attr__( 'View current photo fullscreen', 'digipublish-core' ) . '">⛶</button>';
	}
	if ( $settings['showSharing'] ) {
		echo '<button type="button" class="tp-gallery__tool" data-gallery-share aria-label="' . esc_attr__( 'Share current gallery photo', 'digipublish-core' ) . '">↗</button>';
	}
	echo '</div>';
}

echo '<div class="tp-gallery__viewport" data-gallery-viewport>';
foreach ( $slides as $index => $slide ) {
	$number = $index + 1;
	echo digipublish_core_render_gallery_slide( $slide, $number, $total, $settings );

	if ( $settings['adInterval'] && 0 === $number % $settings['adInterval'] && $number < $total ) {
		echo '<div class="tp-gallery__ad">';
		echo do_blocks( '<!-- wp:digipublish/ad-slot {"slotName":"gallery-inline","minHeight":250,"label":"Advertisement"} /-->' );
		echo '</div>';
	}
}
echo '</div>';

if ( $settings['showThumbnails'] && $total > 1 ) {
	echo '<div class="tp-gallery__thumbs" data-gallery-thumbs aria-label="' . esc_attr__( 'Gallery thumbnails', 'digipublish-core' ) . '">';
	foreach ( $slides as $index => $slide ) {
		$image_id = absint( $slide['imageId'] ?? 0 );
		if ( ! $image_id ) {
			continue;
		}
		echo '<button type="button" class="tp-gallery__thumb' . ( 0 === $index ? ' is-active' : '' ) . '" data-gallery-thumb="' . esc_attr( (string) $index ) . '" aria-label="' . esc_attr( sprintf( __( 'Go to photo %d', 'digipublish-core' ), $index + 1 ) ) . '">';
		echo wp_get_attachment_image( $image_id, 'thumbnail', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) );
		echo '</button>';
	}
	echo '</div>';
}

echo '</section>';
