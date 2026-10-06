<?php
/**
 * Legacy DigiPublish theme callable aliases.
 *
 * Historical TechPress/Caards function names remain available for integrations,
 * while production theme code uses the canonical DigiPublish namespace.
 *
 * @package DigiPublish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function techpress_theme_setup( ...$args ) { return digipublish_theme_setup( ...$args ); }
function techpress_theme_enqueue_assets( ...$args ) { return digipublish_enqueue_theme_assets( ...$args ); }
function techpress_theme_editor_assets( ...$args ) { return digipublish_enqueue_editor_assets( ...$args ); }
function techpress_theme_load_block_assets_on_demand( ...$args ) { return digipublish_load_block_assets_on_demand( ...$args ); }
function techpress_theme_body_classes( ...$args ) { return digipublish_theme_body_classes( ...$args ); }

function digipublish_caards_register_singular_meta( ...$args ) { return digipublish_register_singular_meta( ...$args ); }
function digipublish_caards_body_classes( ...$args ) { return digipublish_singular_body_classes( ...$args ); }
function digipublish_caards_singular_setting( ...$args ) { return digipublish_singular_setting( ...$args ); }
function digipublish_caards_load_next_enabled( ...$args ) { return digipublish_load_next_enabled( ...$args ); }
function digipublish_caards_adjacent_post_id( ...$args ) { return digipublish_adjacent_post_id( ...$args ); }
function digipublish_caards_render_next_article( ...$args ) { return digipublish_render_next_article( ...$args ); }
function digipublish_caards_register_load_next_route( ...$args ) { return digipublish_register_load_next_route( ...$args ); }
function digipublish_caards_route_template_parts( ...$args ) { return digipublish_route_legacy_template_parts( ...$args ); }


/**
 * Read a historical visual-layout option without exposing it as an active
 * DigiPublish design setting.
 */
function digipublish_legacy_layout_option( $key, $fallback = '' ) {
	$map = array(
		'header_variant' => 'digipublish_caards_header_variant',
		'footer_variant' => 'digipublish_caards_footer_variant',
		'default_header' => 'digipublish_caards_default_header',
		'default_sidebar' => 'digipublish_caards_default_sidebar',
	);

	$key = sanitize_key( (string) $key );
	if ( empty( $map[ $key ] ) ) {
		return $fallback;
	}

	$value = get_option( $map[ $key ], $fallback );
	return is_scalar( $value ) ? sanitize_key( (string) $value ) : $fallback;
}

/**
 * Whether a canonical header/footer template part has a Site Editor override.
 */
function digipublish_site_editor_part_is_custom( $slug ) {
	if ( ! function_exists( 'get_block_template' ) ) {
		return false;
	}

	$slug = sanitize_key( (string) $slug );
	if ( ! in_array( $slug, array( 'header', 'footer' ), true ) ) {
		return false;
	}

	$template = get_block_template( get_stylesheet() . '//' . $slug, 'wp_template_part' );
	return $template instanceof WP_Block_Template && 'custom' === $template->source;
}

/**
 * Honor historical non-default header/footer selections until an editor saves
 * the canonical template part. New installs are Site-Editor-first.
 */
function digipublish_route_legacy_template_parts( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/template-part' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}

	$slug = isset( $parsed_block['attrs']['slug'] ) ? sanitize_key( (string) $parsed_block['attrs']['slug'] ) : '';
	if ( ! in_array( $slug, array( 'header', 'footer' ), true ) || digipublish_site_editor_part_is_custom( $slug ) ) {
		return $parsed_block;
	}

	$variant = digipublish_legacy_layout_option( $slug . '_variant', '' );
	if ( in_array( $variant, array( 'two', 'three', 'four' ), true ) ) {
		$parsed_block['attrs']['slug'] = $slug . '-' . $variant;
	}

	return $parsed_block;
}

$digipublish_legacy_header = digipublish_legacy_layout_option( 'header_variant', '' );
$digipublish_legacy_footer = digipublish_legacy_layout_option( 'footer_variant', '' );
if (
	in_array( $digipublish_legacy_header, array( 'two', 'three', 'four' ), true ) ||
	in_array( $digipublish_legacy_footer, array( 'two', 'three', 'four' ), true )
) {
	add_filter( 'render_block_data', 'digipublish_route_legacy_template_parts', 15 );
}
unset( $digipublish_legacy_header, $digipublish_legacy_footer );


/**
 * Preserve the historical shell body class for site-specific CSS written
 * before the canonical dp-* namespace. Active theme code emits dp-shell.
 */
function digipublish_legacy_shell_body_class( $classes ) {
	if ( in_array( 'dp-shell', $classes, true ) && ! in_array( 'dp-caards-shell', $classes, true ) ) {
		$classes[] = 'dp-caards-shell';
	}
	return $classes;
}
add_filter( 'body_class', 'digipublish_legacy_shell_body_class', 100 );
