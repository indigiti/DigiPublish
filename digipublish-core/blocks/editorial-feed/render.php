<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'cards-4';
$allowed_layouts = array( 'cards-4', 'weekly-mosaic', 'carousel-overlay', 'featured-trio', 'compact-grid', 'latest-cards' );
if ( ! in_array( $layout, $allowed_layouts, true ) ) {
	$layout = 'cards-4';
}

$posts = techpress_editorial_feed_get_posts( $attributes );
if ( empty( $posts ) ) {
	return;
}
$heading = isset( $attributes['heading'] ) ? trim( (string) $attributes['heading'] ) : '';
$description = isset( $attributes['description'] ) ? trim( (string) $attributes['description'] ) : '';
$view_all = techpress_editorial_feed_view_all_url( $attributes );
$show_view_all = ! empty( $attributes['showViewAll'] ) && $view_all;
$view_all_label = ! empty( $attributes['viewAllLabel'] ) ? (string) $attributes['viewAllLabel'] : __( 'View All', 'digipublish-core' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'tp-editorial-feed tp-editorial-feed--' . $layout ) );

echo '<section ' . $wrapper . '>';
echo '<header class="tp-editorial-feed__header">';
echo '<div class="tp-editorial-feed__heading-wrap">';
if ( $heading ) {
	echo '<h2 class="tp-editorial-feed__title">' . esc_html( $heading ) . '</h2>';
}
if ( $description ) {
	echo '<p class="tp-editorial-feed__description">' . esc_html( $description ) . '</p>';
}
echo '</div>';
if ( $show_view_all ) {
	echo '<a class="tp-editorial-feed__view-all" href="' . esc_url( $view_all ) . '"><span>' . esc_html( $view_all_label ) . '</span><span class="tp-editorial-feed__view-all-icon" aria-hidden="true">→</span></a>';
}
echo '</header>';

switch ( $layout ) {
	case 'weekly-mosaic':
		echo '<div class="tp-weekly-mosaic">';
		foreach ( $posts as $index => $post ) {
			$class = $index < 2 ? 'tp-weekly-card tp-weekly-card--overlay' : ( 2 === $index ? 'tp-weekly-card tp-weekly-card--standard' : 'tp-weekly-card tp-weekly-card--mini' );
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, $class, $index < 2 );
		}
		echo '</div>';
		break;

	case 'carousel-overlay':
		echo '<div class="tp-editorial-carousel" data-tp-carousel>';
		foreach ( $posts as $post ) {
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, 'tp-carousel-card', true );
		}
		echo '</div>';
		echo '<div class="tp-editorial-carousel__controls"><button type="button" data-tp-carousel-dir="prev" aria-label="' . esc_attr__( 'Previous stories', 'digipublish-core' ) . '">←</button><button type="button" data-tp-carousel-dir="next" aria-label="' . esc_attr__( 'Next stories', 'digipublish-core' ) . '">→</button></div>';
		break;

	case 'featured-trio':
		echo '<div class="tp-featured-trio">';
		foreach ( array_slice( $posts, 0, 3 ) as $index => $post ) {
			$class = 0 === $index ? 'tp-featured-trio__lead' : 'tp-featured-trio__side';
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, $class, true );
		}
		echo '</div>';
		break;

	case 'compact-grid':
		echo '<div class="tp-compact-grid">';
		foreach ( $posts as $post ) {
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, 'tp-compact-card', false );
		}
		echo '</div>';
		break;

	case 'latest-cards':
		echo '<div class="tp-latest-card-grid">';
		foreach ( $posts as $post ) {
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, 'tp-latest-card', false );
		}
		echo '</div>';
		break;

	case 'cards-4':
	default:
		echo '<div class="tp-editorial-card-grid">';
		foreach ( $posts as $post ) {
			echo techpress_editorial_feed_story_markup( $post->ID, $attributes, 'tp-editorial-card', false );
		}
		echo '</div>';
		break;
}

echo '</section>';
