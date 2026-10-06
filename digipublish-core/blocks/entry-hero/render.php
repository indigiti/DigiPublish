<?php
/**
 * Caards-style singular entry hero.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = ! empty( $block->context['postId'] ) ? absint( $block->context['postId'] ) : ( get_the_ID() ?: get_queried_object_id() );
if ( ! $post_id ) { return; }

$layout = sanitize_key( $attributes['layout'] ?? 'auto' );
if ( 'auto' === $layout ) {
	$layout = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_page_header_type', true ) );
	if ( ! in_array( $layout, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
		$layout = function_exists( 'digipublish_legacy_layout_option' )
			? digipublish_legacy_layout_option( 'default_header', 'standard' )
			: 'standard';
	}
	if ( ! in_array( $layout, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
		$layout = 'standard';
	}
}
if ( ! in_array( $layout, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
	$layout = 'standard';
}
if ( 'none' === $layout ) { return; }

$is_post = 'post' === get_post_type( $post_id );
$classes = array( 'dp-caards-entry-hero', 'dp-caards-entry-hero--' . $layout );
$wrapper = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );
$title = get_the_title( $post_id );
$excerpt = trim( wp_strip_all_tags( get_the_excerpt( $post_id ) ) );
$permalink = get_permalink( $post_id );
$author_id = (int) get_post_field( 'post_author', $post_id );
$video_url = esc_url_raw( (string) get_post_meta( $post_id, 'digipublish_post_video_url', true ) );
$video_ext = strtolower( (string) pathinfo( wp_parse_url( $video_url, PHP_URL_PATH ) ?: '', PATHINFO_EXTENSION ) );
$can_video = in_array( $video_ext, array( 'mp4', 'webm', 'ogg' ), true );

$breadcrumbs = '';
if ( ! empty( $attributes['showBreadcrumbs'] ) ) {
	$breadcrumbs .= '<div class="dp-caards-entry-hero__breadcrumbs"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'digipublish-core' ) . '</a><span>›</span>';
	if ( $is_post ) {
		$cats = get_the_category( $post_id );
		if ( $cats ) {
			$breadcrumbs .= '<a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a><span>›</span>';
		}
	}
	$breadcrumbs .= '<span>' . esc_html( $title ) . '</span></div>';
}

$category = '';
if ( $is_post && ! empty( $attributes['showCategory'] ) && function_exists( 'digipublish_core_category_markup' ) ) {
	$category = '<div class="dp-caards-entry-hero__category">' . digipublish_core_category_markup( $post_id ) . '</div>';
}

$meta = array();
if ( $is_post && ! empty( $attributes['showAuthor'] ) && $author_id ) {
	$meta[] = '<a href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</a>';
}
if ( $is_post && ! empty( $attributes['showDate'] ) ) {
	$meta[] = '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time>';
}
if ( $is_post && ! empty( $attributes['showComments'] ) ) {
	$count = get_comments_number( $post_id );
	$meta[] = esc_html( sprintf( _n( '%s comment', '%s comments', $count, 'digipublish-core' ), number_format_i18n( $count ) ) );
}
if ( $is_post && ! empty( $attributes['showReadTime'] ) && function_exists( 'digipublish_core_read_time' ) ) {
	$minutes = digipublish_core_read_time( $post_id );
	$meta[] = esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes ) );
}
if ( $is_post && ! empty( $attributes['showViews'] ) && function_exists( 'digipublish_core_metric_value' ) ) {
	$views = digipublish_core_metric_value( $post_id, '_techpress_views' );
	if ( $views ) { $meta[] = esc_html( number_format_i18n( $views ) . ' ' . __( 'views', 'digipublish-core' ) ); }
}
if ( $is_post && ! empty( $attributes['showShares'] ) && function_exists( 'digipublish_core_metric_value' ) ) {
	$shares = digipublish_core_metric_value( $post_id, '_techpress_shares' );
	if ( $shares ) { $meta[] = esc_html( number_format_i18n( $shares ) . ' ' . __( 'shares', 'digipublish-core' ) ); }
}
$meta_html = $meta ? '<div class="dp-caards-entry-hero__meta"><span>' . implode( '</span><span>', $meta ) . '</span></div>' : '';
$subtitle = ! empty( $attributes['showSubtitle'] ) && $excerpt ? '<p class="dp-caards-entry-hero__subtitle">' . esc_html( $excerpt ) . '</p>' : '';

$info = $breadcrumbs . $category . '<h1 class="dp-caards-entry-hero__title">' . esc_html( $title ) . '</h1>' . $meta_html . $subtitle;

echo '<section ' . $wrapper . '>';
if ( in_array( $layout, array( 'large', 'full' ), true ) ) {
	echo '<div class="dp-caards-entry-hero__background">';
	if ( $can_video ) {
		echo '<video autoplay muted loop playsinline preload="metadata" src="' . esc_url( $video_url ) . '"></video>';
	} elseif ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) );
	}
	echo '</div><div class="dp-caards-entry-hero__inner">' . $info . '</div>';
} else {
	echo '<div class="dp-caards-entry-hero__inner">' . $info . '</div>';
	if ( 'standard' === $layout && has_post_thumbnail( $post_id ) ) {
		echo '<figure class="dp-caards-entry-hero__media">' . get_the_post_thumbnail( $post_id, 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) );
		$caption = get_the_post_thumbnail_caption( $post_id );
		if ( $caption ) { echo '<figcaption class="wp-caption-text">' . esc_html( $caption ) . '</figcaption>'; }
		echo '</figure>';
	}
}
echo '</section>';
