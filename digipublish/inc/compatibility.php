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
