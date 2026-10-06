<?php
/**
 * Legacy DigiPublish Core callable aliases.
 *
 * Pre-1.0 TechPress function names remain available to external integrations,
 * but production code must call the canonical digipublish_core_* functions.
 *
 * @package DigiPublish_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function techpress_editorial_register_content_types( ...$args ) { return digipublish_core_register_content_types( ...$args ); }
function techpress_editorial_register_attribution_meta( ...$args ) { return digipublish_core_register_attribution_meta( ...$args ); }
function techpress_editorial_attribution_label( ...$args ) { return digipublish_core_attribution_label( ...$args ); }
function techpress_editorial_register_blocks( ...$args ) { return digipublish_core_register_blocks( ...$args ); }
function techpress_editorial_block_categories( ...$args ) { return digipublish_core_block_categories( ...$args ); }
function techpress_editorial_author_profile_fields( ...$args ) { return digipublish_core_author_profile_fields( ...$args ); }
function techpress_editorial_save_author_profile_fields( ...$args ) { return digipublish_core_save_author_profile_fields( ...$args ); }
function techpress_editorial_get_top_categories( ...$args ) { return digipublish_core_get_top_categories( ...$args ); }
function techpress_editorial_get_category_expert_ids( ...$args ) { return digipublish_core_get_category_expert_ids( ...$args ); }
function techpress_editorial_cache_version( ...$args ) { return digipublish_core_cache_version( ...$args ); }
function techpress_editorial_bump_cache_version( ...$args ) { return digipublish_core_bump_cache_version( ...$args ); }
function techpress_editorial_bump_post_cache_versions( ...$args ) { return digipublish_core_bump_post_cache_versions( ...$args ); }
function techpress_editorial_bump_term_cache_version( ...$args ) { return digipublish_core_bump_term_cache_version( ...$args ); }
function techpress_editorial_bump_comment_cache_version( ...$args ) { return digipublish_core_bump_comment_cache_version( ...$args ); }
function techpress_editorial_optimize_main_queries( ...$args ) { return digipublish_core_optimize_main_queries( ...$args ); }
function techpress_editorial_category_markup( ...$args ) { return digipublish_core_category_markup( ...$args ); }
function techpress_editorial_category_icon_svg( ...$args ) { return digipublish_core_category_icon_svg( ...$args ); }
function techpress_editorial_image_markup( ...$args ) { return digipublish_core_image_markup( ...$args ); }
function techpress_editorial_meta_markup( ...$args ) { return digipublish_core_meta_markup( ...$args ); }
function techpress_editorial_card_markup( ...$args ) { return digipublish_core_card_markup( ...$args ); }
function techpress_editorial_read_time( ...$args ) { return digipublish_core_read_time( ...$args ); }
function techpress_editorial_metric_value( ...$args ) { return digipublish_core_metric_value( ...$args ); }
function techpress_editorial_format_metric( ...$args ) { return digipublish_core_format_metric( ...$args ); }
function techpress_editorial_feed_stats_markup( ...$args ) { return digipublish_core_feed_stats_markup( ...$args ); }
function techpress_editorial_feed_story_markup( ...$args ) { return digipublish_core_feed_story_markup( ...$args ); }
function techpress_editorial_ad_provider_markup( ...$args ) { return digipublish_core_ad_provider_markup( ...$args ); }
function techpress_editorial_activate( ...$args ) { return digipublish_core_activate( ...$args ); }
function techpress_editorial_deactivate( ...$args ) { return digipublish_core_deactivate( ...$args ); }

function techpress_editorial_post_query_args( ...$args ) { return digipublish_core_query_post_args( ...$args ); }
function techpress_editorial_feed_query_args( ...$args ) { return digipublish_core_query_feed_args( ...$args ); }
function techpress_editorial_feed_get_posts( ...$args ) { return digipublish_core_query_feed_posts( ...$args ); }
function techpress_editorial_feed_view_all_url( ...$args ) { return digipublish_core_query_view_all_url( ...$args ); }
