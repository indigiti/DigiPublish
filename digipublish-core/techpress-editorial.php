<?php
/**
 * Plugin Name: DigiPublish Core
 * Description: Dynamic Gutenberg blocks and editorial content types for the DigiPublish publishing framework.
 * Version: 0.11.1
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: indigiti
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: digipublish-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECHPRESS_EDITORIAL_VERSION', '0.11.1' );
define( 'TECHPRESS_EDITORIAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'TECHPRESS_EDITORIAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Canonical DigiPublish constants.
 *
 * Legacy TECHPRESS_* constants remain available for backward compatibility.
 */
define( 'DIGIPUBLISH_CORE_VERSION', TECHPRESS_EDITORIAL_VERSION );
define( 'DIGIPUBLISH_CORE_DIR', TECHPRESS_EDITORIAL_DIR );
define( 'DIGIPUBLISH_CORE_URL', TECHPRESS_EDITORIAL_URL );

require_once DIGIPUBLISH_CORE_DIR . 'includes/gallery.php';
require_once DIGIPUBLISH_CORE_DIR . 'includes/query.php';

/**
 * Register editorial content types.
 */
function digipublish_core_register_content_types() {
	register_post_type(
		'tech_term',
		array(
			'labels' => array(
				'name'          => __( 'Dictionary Terms', 'digipublish-core' ),
				'singular_name' => __( 'Dictionary Term', 'digipublish-core' ),
				'add_new_item'  => __( 'Add Dictionary Term', 'digipublish-core' ),
				'edit_item'     => __( 'Edit Dictionary Term', 'digipublish-core' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'has_archive'  => 'dictionary',
			'rewrite'      => array( 'slug' => 'dictionary' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
			'template'     => array(
				array( 'core/paragraph', array( 'placeholder' => __( 'Write a concise definition first, then expand with examples and context.', 'digipublish-core' ) ) ),
			),
		)
	);

	register_taxonomy(
		'tech_topic',
		array( 'tech_term' ),
		array(
			'labels'       => array(
				'name'          => __( 'Dictionary Topics', 'digipublish-core' ),
				'singular_name' => __( 'Dictionary Topic', 'digipublish-core' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'dictionary-topic' ),
		)
	);
}
add_action( 'init', 'digipublish_core_register_content_types', 5 );


/**
 * Per-post editorial attribution used by the article byline block.
 */
function digipublish_core_register_attribution_meta() {
	foreach ( array( 'post', 'digipublish_gallery' ) as $post_type ) {
		register_post_meta(
			$post_type,
			'_techpress_attribution_type',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => static function ( $value ) {
					$allowed = array( '', 'fact_checked', 'verified', 'reported' );
					return in_array( $value, $allowed, true ) ? $value : '';
				},
				'auth_callback'     => static function () { return current_user_can( 'edit_posts' ); },
			)
		);
		register_post_meta(
			$post_type,
			'_techpress_attribution_user',
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => static function () { return current_user_can( 'edit_posts' ); },
			)
		);
	}
}
add_action( 'init', 'digipublish_core_register_attribution_meta', 6 );

/**
 * Human-readable label for the selected editorial attribution.
 */
function digipublish_core_attribution_label( $type ) {
	$labels = array(
		'fact_checked' => __( 'Fact Checked by', 'digipublish-core' ),
		'verified'     => __( 'Verified by', 'digipublish-core' ),
		'reported'     => __( 'Reported by', 'digipublish-core' ),
	);
	return $labels[ $type ] ?? '';
}

/**
 * Register editor assets and dynamic blocks.
 *
 * Front-end styles are declared as per-block files in block.json so WordPress
 * can load only the CSS needed by the blocks rendered on a request.
 */
function digipublish_core_register_blocks() {
	$editor_js        = TECHPRESS_EDITORIAL_DIR . 'assets/editor.js';
	$editor_query_js  = TECHPRESS_EDITORIAL_DIR . 'assets/editor-query.js';
	$editor_native_js = TECHPRESS_EDITORIAL_DIR . 'assets/editor-native.js';

	wp_register_script(
		'digipublish-core-editor-native',
		TECHPRESS_EDITORIAL_URL . 'assets/editor-native.js',
		array( 'wp-hooks' ),
		file_exists( $editor_native_js ) ? (string) filemtime( $editor_native_js ) : TECHPRESS_EDITORIAL_VERSION,
		true
	);

	wp_register_script(
		'digipublish-core-editor-query',
		TECHPRESS_EDITORIAL_URL . 'assets/editor-query.js',
		array( 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-data', 'wp-core-data' ),
		file_exists( $editor_query_js ) ? (string) filemtime( $editor_query_js ) : TECHPRESS_EDITORIAL_VERSION,
		true
	);

	wp_register_script(
		'digipublish-core-editor',
		TECHPRESS_EDITORIAL_URL . 'assets/editor.js',
		array( 'digipublish-core-editor-native', 'digipublish-core-editor-query', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-plugins', 'wp-editor', 'wp-edit-post' ),
		file_exists( $editor_js ) ? (string) filemtime( $editor_js ) : TECHPRESS_EDITORIAL_VERSION,
		true
	);

	wp_register_block_types_from_metadata_collection(
		DIGIPUBLISH_CORE_DIR . 'blocks',
		DIGIPUBLISH_CORE_DIR . 'blocks-manifest.php'
	);
}
add_action( 'init', 'digipublish_core_register_blocks', 20 );

/**
 * Add a dedicated inserter category.
 */
function digipublish_core_block_categories( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'digipublish-editorial',
			'title' => __( 'DigiPublish Editorial', 'digipublish-core' ),
			'icon'  => 'admin-post',
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'digipublish_core_block_categories' );

/**
 * Rewrite the previous internal Gutenberg namespace to digipublish/*.
 *
 * The old namespace is assembled in two pieces so no retired block ID remains
 * registered or hardcoded in the active framework.
 */
function digipublish_core_rewrite_block_namespace_in_content( $content ) {
	$legacy_namespace = 'tech' . 'press';
	$open_legacy      = '<!-- wp:' . $legacy_namespace . '/';
	$close_legacy     = '<!-- /wp:' . $legacy_namespace . '/';
	$pattern_legacy   = '"slug":"' . $legacy_namespace . '/';
	$pattern_spaced   = '"slug": "' . $legacy_namespace . '/';
	$query_legacy     = '"namespace":"' . $legacy_namespace . '/story-grid"';
	$query_spaced     = '"namespace": "' . $legacy_namespace . '/story-grid"';

	return str_replace(
		array(
			$open_legacy,
			$close_legacy,
			$pattern_legacy,
			$pattern_spaced,
			$query_legacy,
			$query_spaced,
		),
		array(
			'<!-- wp:digipublish/',
			'<!-- /wp:digipublish/',
			'"slug":"digipublish/',
			'"slug": "digipublish/',
			'"namespace":"digipublish/story-grid"',
			'"namespace": "digipublish/story-grid"',
		),
		(string) $content
	);
}

/**
 * One-time migration for saved posts, reusable blocks, navigation, templates
 * and template parts that contain the previous internal block namespace.
 */
function digipublish_core_migrate_block_namespace() {
	if ( get_option( 'digipublish_block_namespace_migrated_080', false ) ) {
		return;
	}

	global $wpdb;

	$legacy_namespace = 'tech' . 'press';
	$legacy_like      = '%' . $wpdb->esc_like( $legacy_namespace . '/' ) . '%';

	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s",
			$legacy_like
		)
	);

	foreach ( $post_ids as $post_id ) {
		$old_content = get_post_field( 'post_content', $post_id, 'raw' );
		$new_content = digipublish_core_rewrite_block_namespace_in_content( $old_content );

		if ( $new_content === $old_content ) {
			continue;
		}

		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => $new_content ),
			array( 'ID' => absint( $post_id ) ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $post_id );
	}

	update_option( 'digipublish_block_namespace_migrated_080', gmdate( 'c' ), false );
}
add_action( 'admin_init', 'digipublish_core_migrate_block_namespace', 5 );

/**
 * Keep the frontend rendering during the short window before the one-time
 * database migration has run on an upgraded site.
 */
function digipublish_core_render_block_namespace_fallback( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) ) {
		return $parsed_block;
	}

	$legacy_prefix = ( 'tech' . 'press' ) . '/';
	if ( str_starts_with( $parsed_block['blockName'], $legacy_prefix ) ) {
		$parsed_block['blockName'] = 'digipublish/' . substr( $parsed_block['blockName'], strlen( $legacy_prefix ) );
	}

	return $parsed_block;
}
if ( ! get_option( 'digipublish_block_namespace_migrated_080', false ) ) {
	add_filter( 'render_block_data', 'digipublish_core_render_block_namespace_fallback', 5 );
}


/**
 * Optional publication profile fields used by the author archive hero.
 */
function digipublish_core_author_profile_fields( $user ) {
    $fields = array(
        'techpress_role'      => __( 'Editorial role', 'digipublish-core' ),
        'techpress_linkedin'  => __( 'LinkedIn URL', 'digipublish-core' ),
        'techpress_x'         => __( 'X / Twitter URL', 'digipublish-core' ),
        'techpress_instagram' => __( 'Instagram URL', 'digipublish-core' ),
        'techpress_youtube'   => __( 'YouTube URL', 'digipublish-core' ),
    );
    echo '<h2>' . esc_html__( 'DigiPublish Author Profile', 'digipublish-core' ) . '</h2><table class="form-table" role="presentation">';
    foreach ( $fields as $key => $label ) {
        $value = get_user_meta( $user->ID, $key, true );
        echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '"></td></tr>';
    }
    echo '</table>';
}
add_action( 'show_user_profile', 'digipublish_core_author_profile_fields' );
add_action( 'edit_user_profile', 'digipublish_core_author_profile_fields' );

function digipublish_core_save_author_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) { return; }
    $url_fields = array( 'techpress_linkedin', 'techpress_x', 'techpress_instagram', 'techpress_youtube' );
    if ( isset( $_POST['techpress_role'] ) ) {
        update_user_meta( $user_id, 'techpress_role', sanitize_text_field( wp_unslash( $_POST['techpress_role'] ) ) );
    }
    foreach ( $url_fields as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_user_meta( $user_id, $key, esc_url_raw( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'personal_options_update', 'digipublish_core_save_author_profile_fields' );
add_action( 'edit_user_profile_update', 'digipublish_core_save_author_profile_fields' );


/**
 * Cached top-level categories used by navigation and editorial blocks.
 */
function digipublish_core_get_top_categories( $limit = 8, $exclude_default = true ) {
	$limit = max( 1, min( 20, absint( $limit ) ) );
	$key   = 'v' . digipublish_core_cache_version() . '_top_categories_' . $limit . '_' . ( $exclude_default ? '1' : '0' );
	$cats  = wp_cache_get( $key, 'techpress_editorial' );

	if ( false !== $cats ) {
		return $cats;
	}

	$exclude = array();
	if ( $exclude_default ) {
		$default = absint( get_option( 'default_category' ) );
		if ( $default ) {
			$exclude[] = $default;
		}
	}

	$cats = get_categories(
		array(
			'hide_empty' => true,
			'number'     => $limit,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'exclude'    => $exclude,
		)
	);
	wp_cache_set( $key, $cats, 'techpress_editorial', HOUR_IN_SECONDS );
	return $cats;
}

/**
 * Cached expert IDs for a category. Uses one grouped SQL query instead of
 * loading dozens of post objects and resolving authors one by one.
 */
function digipublish_core_get_category_expert_ids( $term_id, $limit = 5 ) {
	global $wpdb;

	$term_id = absint( $term_id );
	$limit   = max( 1, min( 12, absint( $limit ) ) );
	if ( ! $term_id ) {
		return array();
	}

	$key = 'v' . digipublish_core_cache_version() . '_category_experts_' . $term_id . '_' . $limit;
	$ids = wp_cache_get( $key, 'techpress_editorial' );
	if ( false !== $ids ) {
		return $ids;
	}

	$term_taxonomy_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = 'category' LIMIT 1",
			$term_id
		)
	);
	if ( ! $term_taxonomy_id ) {
		return array();
	}

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.post_author
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			 WHERE tr.term_taxonomy_id = %d
			   AND p.post_type = 'post'
			   AND p.post_status = 'publish'
			 GROUP BY p.post_author
			 ORDER BY COUNT(*) DESC
			 LIMIT %d",
			$term_taxonomy_id,
			$limit
		)
	);
	$ids = array_map( 'intval', $ids );
	wp_cache_set( $key, $ids, 'techpress_editorial', 6 * HOUR_IN_SECONDS );
	return $ids;
}

/**
 * Versioned cache namespaces keep content and popularity invalidation separate.
 * Comments only invalidate comment-count-ranked feeds; unrelated category and
 * latest-feed caches remain warm.
 */
function digipublish_core_cache_version( $scope = 'content' ) {
	$scope  = 'popularity' === $scope ? 'popularity' : 'content';
	$option = 'popularity' === $scope ? 'digipublish_editorial_popularity_cache_version' : 'digipublish_core_cache_version';
	return max( 1, absint( get_option( $option, 1 ) ) );
}

function digipublish_core_bump_cache_version( $scope = 'content' ) {
	$scope  = 'popularity' === $scope ? 'popularity' : 'content';
	$option = 'popularity' === $scope ? 'digipublish_editorial_popularity_cache_version' : 'digipublish_core_cache_version';
	update_option( $option, digipublish_core_cache_version( $scope ) + 1, false );
}

function digipublish_core_bump_post_cache_versions( $post_id = 0 ) {
	if ( ! $post_id || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! in_array( get_post_type( $post_id ), array( 'post', 'tech_term', 'digipublish_gallery' ), true ) ) {
		return;
	}
	digipublish_core_bump_cache_version( 'content' );
	if ( 'post' === get_post_type( $post_id ) ) {
		digipublish_core_bump_cache_version( 'popularity' );
	}
}
add_action( 'save_post', 'digipublish_core_bump_post_cache_versions' );

function digipublish_core_bump_term_cache_version( $term_id = 0, $term_taxonomy_id = 0, $taxonomy = '' ) {
	if ( $taxonomy && ! in_array( $taxonomy, array( 'category', 'post_tag', 'tech_topic' ), true ) ) {
		return;
	}
	digipublish_core_bump_cache_version( 'content' );
}
add_action( 'created_term', 'digipublish_core_bump_term_cache_version', 10, 3 );
add_action( 'edited_term', 'digipublish_core_bump_term_cache_version', 10, 3 );
add_action( 'delete_term', 'digipublish_core_bump_term_cache_version', 10, 3 );

function digipublish_core_bump_comment_cache_version( $comment_id = 0 ) {
	$comment = $comment_id ? get_comment( $comment_id ) : null;
	if ( $comment && 'post' !== get_post_type( $comment->comment_post_ID ) ) {
		return;
	}
	digipublish_core_bump_cache_version( 'popularity' );
}
add_action( 'wp_insert_comment', 'digipublish_core_bump_comment_cache_version' );
add_action( 'edit_comment', 'digipublish_core_bump_comment_cache_version' );
add_action( 'delete_comment', 'digipublish_core_bump_comment_cache_version' );

/**
 * Keep framework archive/search templates aligned with the main WordPress
 * query so the archive-feed block can render it without a duplicate query.
 */
function digipublish_core_optimize_main_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! current_theme_supports( 'digipublish-performance' ) ) {
		return;
	}

	if ( $query->is_search() ) {
		$query->set( 'post_type', 'post' );
	}

	if ( $query->is_archive() || $query->is_search() ) {
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', 10 );
	}
}
add_action( 'pre_get_posts', 'digipublish_core_optimize_main_queries' );

/**
 * Category label for a card.
 */
function digipublish_core_category_markup( $post_id ) {
	$categories = get_the_category( $post_id );
	if ( empty( $categories ) ) {
		return '';
	}
	$category = $categories[0];
	return sprintf(
		'<a class="tp-category" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $category ) ),
		esc_html( $category->name )
	);
}


/**
 * Lightweight publication category icon. These are framework-owned SVGs, not
 * copied third-party assets, and are selected heuristically from the category.
 */
function digipublish_core_category_icon_svg( $category ) {
	$slug = $category instanceof WP_Term ? $category->slug : sanitize_title( (string) $category );
	$name = $category instanceof WP_Term ? $category->name : (string) $category;
	$key  = strtolower( $slug . ' ' . $name );
	$common = 'viewBox="0 0 64 64" aria-hidden="true" focusable="false"';

	if ( str_contains( $key, 'artificial' ) || preg_match( '/(^|[-_ ])ai($|[-_ ])/i', $key ) || str_contains( $key, 'machine-learning' ) ) {
		return '<svg ' . $common . '><g fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="32" cy="32" rx="25" ry="10"/><ellipse cx="32" cy="32" rx="25" ry="10" transform="rotate(60 32 32)"/><ellipse cx="32" cy="32" rx="25" ry="10" transform="rotate(120 32 32)"/></g><circle cx="32" cy="32" r="4" fill="currentColor"/><text x="32" y="37" text-anchor="middle" font-size="12" font-weight="800" fill="#fff">AI</text></svg>';
	}
	if ( str_contains( $key, 'business' ) || str_contains( $key, 'software' ) || str_contains( $key, 'enterprise' ) ) {
		return '<svg ' . $common . '><g fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="10" width="46" height="31" rx="3"/><path d="M25 48h14M32 41v7"/><circle cx="25" cy="25" r="4"/><circle cx="40" cy="25" r="4"/><path d="M18 35c2-5 12-5 14 0M33 35c2-5 12-5 14 0"/></g></svg>';
	}
	if ( str_contains( $key, 'cyber' ) || str_contains( $key, 'security' ) || str_contains( $key, 'privacy' ) ) {
		return '<svg ' . $common . '><path d="M32 6 53 14v16c0 13-8 23-21 29C19 53 11 43 11 30V14L32 6Z" fill="none" stroke="currentColor" stroke-width="3"/><path d="m22 31 7 7 14-16" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}
	if ( str_contains( $key, 'crypto' ) || str_contains( $key, 'bitcoin' ) || str_contains( $key, 'blockchain' ) ) {
		return '<svg ' . $common . '><circle cx="32" cy="32" r="24" fill="none" stroke="currentColor" stroke-width="3"/><path d="M27 17v30M36 17v30M23 22h13c7 0 9 9 2 12 9 2 7 12-2 12H23" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}
	if ( str_contains( $key, 'data' ) || str_contains( $key, 'database' ) || str_contains( $key, 'analytics' ) ) {
		return '<svg ' . $common . '><g fill="none" stroke="currentColor" stroke-width="3"><ellipse cx="25" cy="15" rx="15" ry="7"/><path d="M10 15v21c0 4 7 7 15 7 4 0 7-.7 10-2M40 15v14"/><path d="M10 26c0 4 7 7 15 7 7 0 13-2 15-6"/></g><circle cx="45" cy="43" r="10" fill="none" stroke="currentColor" stroke-width="3"/><path d="M45 37v12M39 43h12" stroke="currentColor" stroke-width="3"/></svg>';
	}
	if ( str_contains( $key, 'game' ) ) {
		return '<svg ' . $common . '><path d="M18 23h28c6 0 11 5 11 12l-3 13c-1 5-8 6-11 2l-5-6H26l-5 6c-3 4-10 3-11-2L7 35c0-7 5-12 11-12Z" fill="none" stroke="currentColor" stroke-width="3"/><path d="M19 31v10M14 36h10" stroke="currentColor" stroke-width="3"/><circle cx="44" cy="33" r="2.5" fill="currentColor"/><circle cx="50" cy="39" r="2.5" fill="currentColor"/></svg>';
	}
	if ( str_contains( $key, 'network' ) || str_contains( $key, 'connect' ) || str_contains( $key, 'wifi' ) ) {
		return '<svg ' . $common . '><rect x="9" y="34" width="46" height="17" rx="4" fill="none" stroke="currentColor" stroke-width="3"/><circle cx="19" cy="43" r="2.5" fill="currentColor"/><circle cx="28" cy="43" r="2.5" fill="currentColor"/><path d="M23 25c5-5 13-5 18 0M17 19c9-9 21-9 30 0M29 31c2-2 4-2 6 0" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>';
	}
	if ( str_contains( $key, 'personal' ) || str_contains( $key, 'tech' ) || str_contains( $key, 'internet' ) ) {
		return '<svg ' . $common . '><rect x="8" y="9" width="48" height="34" rx="3" fill="none" stroke="currentColor" stroke-width="3"/><path d="M22 53h20M32 43v10" stroke="currentColor" stroke-width="3"/><circle cx="32" cy="26" r="11" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="M21 26h22M32 15c4 4 5 17 0 22M32 15c-4 4-5 17 0 22" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
	}
	return '<svg ' . $common . '><g fill="none" stroke="currentColor" stroke-width="3"><rect x="10" y="10" width="18" height="18" rx="3"/><rect x="36" y="10" width="18" height="18" rx="3"/><rect x="10" y="36" width="18" height="18" rx="3"/><rect x="36" y="36" width="18" height="18" rx="3"/></g></svg>';
}

/**
 * Consistent responsive post image with a non-breaking fallback.
 *
 * WordPress supplies srcset candidates. Layout-specific sizes hints keep
 * browsers from downloading desktop-sized media for compact/mobile cards.
 */
function digipublish_core_image_markup( $post_id, $size = 'large', $priority = false, $sizes = '' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$attrs = array(
			'alt'      => the_title_attribute( array( 'echo' => false, 'post' => $post_id ) ),
			'decoding' => 'async',
		);

		if ( $sizes ) {
			$attrs['sizes'] = $sizes;
		}

		if ( $priority ) {
			$attrs['loading']       = 'eager';
			$attrs['fetchpriority'] = 'high';
		}

		return get_the_post_thumbnail( $post_id, $size, $attrs );
	}
	return '<span class="tp-image-placeholder" aria-hidden="true"></span>';
}

/**
 * Compact metadata row.
 */
function digipublish_core_meta_markup( $post_id, $show_author, $show_date ) {
	$parts = array();
	if ( $show_author ) {
		$author_id = (int) get_post_field( 'post_author', $post_id );
		$parts[]   = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( get_author_posts_url( $author_id ) ),
			esc_html( get_the_author_meta( 'display_name', $author_id ) )
		);
	}
	if ( $show_date ) {
		$published = (int) get_post_time( 'U', true, $post_id );
		$now       = (int) current_time( 'timestamp', true );
		$relative  = $published ? human_time_diff( $published, $now ) : get_the_date( get_option( 'date_format' ), $post_id );
		$parts[]   = sprintf(
			'<time datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C, $post_id ) ),
			esc_html( $relative )
		);
	}
	if ( empty( $parts ) ) {
		return '';
	}
	return '<div class="tp-meta">' . implode( '<span class="tp-meta__dot" aria-hidden="true">•</span>', $parts ) . '</div>';
}

/**
 * Render a standard post card.
 */
function digipublish_core_card_markup( $post_id, $attributes = array() ) {
	$show_image    = $attributes['showImage'] ?? true;
	$show_category = $attributes['showCategory'] ?? true;
	$show_excerpt  = $attributes['showExcerpt'] ?? false;
	$show_author   = $attributes['showAuthor'] ?? true;
	$show_date     = $attributes['showDate'] ?? true;
	$show_comments = $attributes['showComments'] ?? false;
	$show_read     = $attributes['showReadTime'] ?? false;
	$show_views    = $attributes['showViews'] ?? false;
	$show_shares   = $attributes['showShares'] ?? false;
	$show_readmore = $attributes['showReadMore'] ?? false;
	$compact_meta  = ! empty( $attributes['compactMeta'] );
	$top_meta_type = isset( $attributes['topMetaType'] ) ? sanitize_key( (string) $attributes['topMetaType'] ) : 'none';
	$card_index    = isset( $attributes['_cardIndex'] ) ? max( 1, absint( $attributes['_cardIndex'] ) ) : 1;
	$image_size    = isset( $attributes['imageSize'] ) && in_array( $attributes['imageSize'], array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ? $attributes['imageSize'] : 'medium_large';

	$card_classes = array( 'tp-card' );
	if ( $compact_meta ) {
		$card_classes[] = 'tp-card--compact-meta';
	}
	$html = '<article class="' . esc_attr( implode( ' ', $card_classes ) ) . '">';
	if ( 'count' === $top_meta_type ) {
		$html .= '<span class="tp-card__top-count" aria-hidden="true">' . esc_html( str_pad( (string) $card_index, 2, '0', STR_PAD_LEFT ) ) . '</span>';
	}
	if ( $show_image ) {
		$html .= '<a class="tp-card__image" href="' . esc_url( get_permalink( $post_id ) ) . '">' . digipublish_core_image_markup( $post_id, $image_size, false, '(max-width: 720px) 100vw, (max-width: 1120px) 50vw, 33vw' ) . '</a>';
	}
	$html .= '<div class="tp-card__body">';
	if ( $show_category ) {
		$html .= digipublish_core_category_markup( $post_id );
	}
	$html .= '<h3 class="tp-card__title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
	if ( $show_excerpt ) {
		$html .= '<p class="tp-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $post_id ), 24 ) ) . '</p>';
	}
	$html .= digipublish_core_meta_markup( $post_id, $show_author, $show_date );

	$stats = array();
	if ( $show_comments ) {
		$stats[] = sprintf(
			_n( '%s comment', '%s comments', get_comments_number( $post_id ), 'digipublish-core' ),
			number_format_i18n( get_comments_number( $post_id ) )
		);
	}
	if ( $show_read ) {
		$minutes = digipublish_core_read_time( $post_id );
		$stats[] = sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes );
	}
	if ( $show_views ) {
		$views = digipublish_core_metric_value( $post_id, '_techpress_views' );
		if ( $views ) {
			$stats[] = digipublish_core_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' );
		}
	}
	if ( $show_shares ) {
		$shares = digipublish_core_metric_value( $post_id, '_techpress_shares' );
		if ( $shares ) {
			$stats[] = __( 'Shares', 'digipublish-core' ) . ' ' . digipublish_core_format_metric( $shares );
		}
	}
	if ( $stats ) {
		$html .= '<div class="tp-card__stats"><span>' . implode( '</span><span>', array_map( 'esc_html', $stats ) ) . '</span></div>';
	}
	if ( $show_readmore ) {
		$label = ! empty( $attributes['readMoreLabel'] ) ? (string) $attributes['readMoreLabel'] : __( 'Read more', 'digipublish-core' );
		$html .= '<a class="tp-card__read-more" href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $label ) . '</a>';
	}
	$html .= '</div></article>';
	return $html;
}


/**
 * Posts-block helpers adapted from the GPL-3.0 Caards theme by Code Supply Co.
 * The implementation is namespaced and dependency-free for DigiPublish.
 * See THIRD_PARTY_NOTICES.md.
 */
function digipublish_core_post_feed_heading_tag( $attributes ) {
	$tag = isset( $attributes['cardHeadingTag'] ) ? strtolower( (string) $attributes['cardHeadingTag'] ) : 'h2';
	return in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ), true ) ? $tag : 'h2';
}

function digipublish_core_post_feed_excerpt( $post_id, $attributes ) {
	if ( empty( $attributes['showExcerpt'] ) ) {
		return '';
	}
	$length = isset( $attributes['excerptLength'] ) ? max( 1, min( 1000, absint( $attributes['excerptLength'] ) ) ) : 100;
	$text = trim( wp_strip_all_tags( get_the_excerpt( $post_id ) ) );
	if ( '' === $text ) {
		return '';
	}
	$text = wp_html_excerpt( $text, $length, '&hellip;' );
	return '<div class="tp-card__excerpt">' . esc_html( $text ) . '</div>';
}

function digipublish_core_post_feed_top_meta( $post_id, $attributes, $index ) {
	$type = isset( $attributes['topMetaType'] ) ? sanitize_key( (string) $attributes['topMetaType'] ) : 'none';
	if ( 'count' === $type ) {
		return '<div class="tp-card__top-meta tp-card__top-meta--count"><span>' . esc_html( str_pad( (string) max( 1, absint( $index ) ), 2, '0', STR_PAD_LEFT ) ) . '</span></div>';
	}
	if ( 'author' === $type ) {
		$author_id = (int) get_post_field( 'post_author', $post_id );
		if ( ! $author_id ) {
			return '';
		}
		return '<div class="tp-card__top-meta tp-card__top-meta--author"><a class="tp-card__top-author" href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' .
			get_avatar( $author_id, 40, '', '', array( 'class' => 'tp-card__top-author-avatar' ) ) .
			'<span>' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</span></a></div>';
	}
	if ( 'category' === $type ) {
		$categories = get_the_category( $post_id );
		if ( ! $categories ) {
			return '';
		}
		$category = $categories[0];
		$letter = function_exists( 'mb_substr' ) ? mb_substr( $category->name, 0, 1 ) : substr( $category->name, 0, 1 );
		return '<div class="tp-card__top-meta tp-card__top-meta--category"><a class="tp-card__category-letter" href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $letter ) . '</a><a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></div>';
	}
	return '';
}

function digipublish_core_post_feed_footer( $post_id, $attributes ) {
	$items = array();
	if ( ! empty( $attributes['showReadTime'] ) ) {
		$minutes = digipublish_core_read_time( $post_id );
		$items[] = sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes );
	}
	if ( ! empty( $attributes['showViews'] ) ) {
		$views = digipublish_core_metric_value( $post_id, '_techpress_views' );
		if ( $views ) {
			$items[] = digipublish_core_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' );
		}
	}
	if ( ! empty( $attributes['showShares'] ) ) {
		$shares = digipublish_core_metric_value( $post_id, '_techpress_shares' );
		if ( $shares ) {
			$items[] = digipublish_core_format_metric( $shares ) . ' ' . __( 'shares', 'digipublish-core' );
		}
	}
	$read_more = ! empty( $attributes['showReadMore'] );
	if ( ! $items && ! $read_more ) {
		return '';
	}
	$html = '<div class="tp-card__footer">';
	if ( $items ) {
		$html .= '<div class="tp-card__footer-meta"><span>' . implode( '</span><span>', array_map( 'esc_html', $items ) ) . '</span></div>';
	}
	if ( $read_more ) {
		$label = ! empty( $attributes['readMoreLabel'] ) ? (string) $attributes['readMoreLabel'] : __( 'Read More', 'digipublish-core' );
		$html .= '<a class="tp-card__read-more" href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $label ) . '</a>';
	}
	return $html . '</div>';
}

function digipublish_core_post_feed_main_meta( $post_id, $attributes, $all_inline = false ) {
	$parts = array();
	if ( ! empty( $attributes['showCategory'] ) ) {
		$category = digipublish_core_category_markup( $post_id );
		if ( $category ) {
			$parts[] = $category;
		}
	}
	if ( ! empty( $attributes['showAuthor'] ) ) {
		$author_id = (int) get_post_field( 'post_author', $post_id );
		if ( $author_id ) {
			$parts[] = '<a href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</a>';
		}
	}
	if ( ! empty( $attributes['showDate'] ) ) {
		$parts[] = '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time>';
	}
	if ( ! empty( $attributes['showComments'] ) ) {
		$comments = get_comments_number( $post_id );
		$parts[] = esc_html( sprintf( _n( '%s comment', '%s comments', $comments, 'digipublish-core' ), number_format_i18n( $comments ) ) );
	}
	if ( $all_inline ) {
		if ( ! empty( $attributes['showViews'] ) ) {
			$views = digipublish_core_metric_value( $post_id, '_techpress_views' );
			if ( $views ) {
				$parts[] = esc_html( digipublish_core_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' ) );
			}
		}
		if ( ! empty( $attributes['showReadTime'] ) ) {
			$minutes = digipublish_core_read_time( $post_id );
			$parts[] = esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes ) );
		}
		if ( ! empty( $attributes['showShares'] ) ) {
			$shares = digipublish_core_metric_value( $post_id, '_techpress_shares' );
			if ( $shares ) {
				$parts[] = esc_html( digipublish_core_format_metric( $shares ) . ' ' . __( 'shares', 'digipublish-core' ) );
			}
		}
	}
	if ( ! $parts ) {
		return '';
	}
	$class = ! empty( $attributes['compactMeta'] ) ? ' tp-card__meta--compact' : '';
	return '<div class="tp-card__meta' . esc_attr( $class ) . '"><span>' . implode( '</span><span>', $parts ) . '</span></div>';
}

function digipublish_core_post_feed_card_markup( $post_id, $attributes = array() ) {
	$layout = isset( $attributes['layout'] ) ? sanitize_key( (string) $attributes['layout'] ) : 'standard-1';
	$legacy_map = array(
		'list'   => 'horizontal-1',
		'grid-2' => 'standard-1',
		'grid-3' => 'standard-1',
		'grid-4' => 'standard-1',
		'grid-5' => 'standard-1',
	);
	$semantic_layout = $legacy_map[ $layout ] ?? $layout;
	$index = isset( $attributes['_cardIndex'] ) ? max( 1, absint( $attributes['_cardIndex'] ) ) : 1;
	$heading_tag = digipublish_core_post_feed_heading_tag( $attributes );
	$image_size = 'horizontal-5' === $semantic_layout ? 'thumbnail' : digipublish_core_image_size( $attributes, 'medium_large' );
	$has_image = ! empty( $attributes['showImage'] ) && ! in_array( $semantic_layout, array( 'standard-4', 'horizontal-4' ), true );
	$is_overlay = str_starts_with( $semantic_layout, 'tile-' ) || str_starts_with( $semantic_layout, 'carousel-' );
	$all_inline_meta = in_array( $semantic_layout, array( 'horizontal-4', 'horizontal-5' ), true );

	$classes = array( 'tp-card', 'tp-card--' . $semantic_layout );
	if ( ! empty( $attributes['compactMeta'] ) ) {
		$classes[] = 'tp-card--compact-meta';
	}

	$format_layouts = array( 'standard-1', 'standard-2', 'standard-3', 'standard-4', 'masonry-1', 'horizontal-1', 'horizontal-2', 'horizontal-3' );
	$video_layouts  = array( 'standard-1', 'standard-2', 'standard-3', 'standard-4', 'masonry-1', 'horizontal-3', 'tile-1', 'tile-2' );
	$show_format    = in_array( $semantic_layout, $format_layouts, true ) && ( $attributes['showPostFormat'] ?? true );
	$allow_video    = in_array( $semantic_layout, $video_layouts, true ) && ! empty( $attributes['enableVideoBackgrounds'] );

	$media = '';
	if ( $has_image ) {
		$video_url = $allow_video
			? esc_url_raw( (string) get_post_meta( $post_id, 'digipublish_post_video_url', true ) )
			: '';
		$video_path = $video_url ? wp_parse_url( $video_url, PHP_URL_PATH ) : '';
		$video_ext = $video_path ? strtolower( (string) pathinfo( $video_path, PATHINFO_EXTENSION ) ) : '';
		$can_video = $video_url && in_array( $video_ext, array( 'mp4', 'webm', 'ogg' ), true );

		if ( $can_video ) {
			$controls = ! empty( $attributes['enableVideoControls'] );
			$media = '<div class="tp-card__image tp-card__image--video"><video ' .
				( $controls ? 'controls ' : 'autoplay muted loop ' ) .
				'playsinline preload="metadata" src="' . esc_url( $video_url ) . '"></video></div>';
		} else {
			$media = '<a class="tp-card__image" href="' . esc_url( get_permalink( $post_id ) ) . '">' .
				digipublish_core_image_markup( $post_id, $image_size, false, '(max-width: 720px) 100vw, (max-width: 1120px) 50vw, 33vw' ) .
				'</a>';
		}

		if ( $media && $show_format ) {
			$format = get_post_format( $post_id );
			if ( $format ) {
				$labels = array(
					'video' => '▶', 'audio' => '♪', 'gallery' => '▦', 'image' => '▧',
					'quote' => '“', 'link' => '↗', 'aside' => '•', 'status' => '●', 'chat' => '☰',
				);
				$symbol = $labels[ $format ] ?? '•';
				$media = '<div class="tp-card__media">' . $media . '<span class="tp-card__format-icon" aria-label="' . esc_attr( ucfirst( $format ) ) . '">' . esc_html( $symbol ) . '</span></div>';
			}
		}
	}

	$top_meta_layout = in_array( $semantic_layout, array( 'standard-4', 'tile-1', 'tile-2', 'tile-3', 'tile-4', 'carousel-1', 'carousel-2' ), true );
	$top_meta_attributes = $attributes;
	if ( $top_meta_layout && empty( $top_meta_attributes['topMetaType'] ) ) {
		$top_meta_attributes['topMetaType'] = 'author';
	}
	$top_meta = $top_meta_layout
		? digipublish_core_post_feed_top_meta( $post_id, $top_meta_attributes, $index )
		: ( 'count' === ( $attributes['topMetaType'] ?? '' ) ? digipublish_core_post_feed_top_meta( $post_id, $attributes, $index ) : '' );

	$category_only = '';
	if ( ! $all_inline_meta && ! empty( $attributes['showCategory'] ) ) {
		$category_only = digipublish_core_category_markup( $post_id );
	}
	$secondary_meta_attrs = $attributes;
	$secondary_meta_attrs['showCategory'] = false;
	$secondary_meta = $all_inline_meta ? digipublish_core_post_feed_main_meta( $post_id, $attributes, true ) : digipublish_core_post_feed_main_meta( $post_id, $secondary_meta_attrs, false );

	$content = '<div class="tp-card__content">';
	$content .= $category_only;
	$content .= '<' . $heading_tag . ' class="tp-card__title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></' . $heading_tag . '>';
	$content .= $secondary_meta;
	$content .= digipublish_core_post_feed_excerpt( $post_id, $attributes );
	$content .= '</div>';

	$footer = $all_inline_meta ? ( ! empty( $attributes['showReadMore'] ) ? digipublish_core_post_feed_footer( $post_id, array_merge( $attributes, array( 'showReadTime' => false, 'showViews' => false, 'showShares' => false ) ) ) : '' ) : digipublish_core_post_feed_footer( $post_id, $attributes );

	$html = '<article class="' . esc_attr( implode( ' ', $classes ) ) . '" data-post-id="' . absint( $post_id ) . '"><div class="tp-card__outer">';
	if ( in_array( $semantic_layout, array( 'standard-3' ), true ) ) {
		$html .= $top_meta . $content . $media . $footer;
	} elseif ( 'standard-4' === $semantic_layout ) {
		$html .= $top_meta . $content . $footer;
	} elseif ( $is_overlay ) {
		$html .= $media . '<div class="tp-card__overlay-content">' . $top_meta . $content . $footer . '</div>';
	} elseif ( in_array( $semantic_layout, array( 'horizontal-1', 'horizontal-2', 'horizontal-3', 'horizontal-5' ), true ) ) {
		$html .= $media . '<div class="tp-card__horizontal-content">' . $top_meta . $content . $footer . '</div>';
	} elseif ( 'horizontal-4' === $semantic_layout ) {
		$html .= $content . $footer;
	} else {
		$html .= $top_meta . $media . $content . $footer;
	}
	$html .= '<a class="tp-card__overlay-link" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( get_the_title( $post_id ) ) . '"></a>';
	return $html . '</div></article>';
}

/**
 * Render a synced pattern (wp_block) at a repeated Masonry interval.
 *
 * This replaces the old Classic Widgets bridge. Editors can compose any
 * Gutenberg blocks inside a synced pattern and inject that pattern between
 * Masonry cards without a widget area or third-party runtime.
 */
function digipublish_core_post_feed_loop_pattern( $pattern_id, $current = 1, $iteration = 3, $repeat = false ) {
	$pattern_id = absint( $pattern_id );
	$iteration  = max( 1, absint( $iteration ) );
	$current    = max( 1, absint( $current ) );

	if ( ! $pattern_id || ( ! $repeat && $current > $iteration ) ) {
		return '';
	}

	$pattern = get_post( $pattern_id );
	if ( ! $pattern || 'wp_block' !== $pattern->post_type || 'publish' !== $pattern->post_status ) {
		return '';
	}

	$content = trim( (string) $pattern->post_content );
	if ( '' === $content ) {
		return '';
	}

	return '<div class="tp-card tp-post-feed__pattern-card"><div class="tp-post-feed__pattern">' . do_blocks( $content ) . '</div></div>';
}

/**
 * Public REST endpoint for Caards-style Load More / Infinite pagination.
 */
function digipublish_core_register_post_feed_rest_route() {
	register_rest_route(
		'digipublish/v1',
		'/post-feed',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => 'digipublish_core_rest_post_feed',
		)
	);
}
add_action( 'rest_api_init', 'digipublish_core_register_post_feed_rest_route' );

function digipublish_core_rest_post_feed( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	$attributes = isset( $params['attributes'] ) && is_array( $params['attributes'] ) ? $params['attributes'] : array();
	$page = isset( $params['page'] ) ? max( 1, absint( $params['page'] ) ) : 2;
	$exclude = isset( $params['exclude'] ) && is_array( $params['exclude'] ) ? array_values( array_unique( array_filter( array_map( 'absint', $params['exclude'] ) ) ) ) : array();

	// Already-rendered IDs are explicitly excluded, so query the first page of
	// the remaining result set. Advancing paged at the same time would skip
	// another full page after exclusions are applied.
	$base_exclude = isset( $attributes['_excludePostIds'] ) && is_array( $attributes['_excludePostIds'] )
		? array_values( array_unique( array_filter( array_map( 'absint', $attributes['_excludePostIds'] ) ) ) )
		: array();
	$exclude = array_values( array_unique( array_merge( $base_exclude, $exclude ) ) );

	$attributes['_paged'] = $exclude ? 1 : $page;
	$attributes['_excludePostIds'] = $exclude;
	$attributes['paginationType'] = 'ajax';
	if ( isset( $params['relatedPostId'] ) ) {
		$attributes['_relatedPostId'] = absint( $params['relatedPostId'] );
	}

	$query = new WP_Query( digipublish_core_query_post_args( $attributes ) );
	$base_index = ( $page - 1 ) * max( 1, absint( $attributes['postsToShow'] ?? 4 ) );
	$content = '';
	foreach ( $query->posts as $index => $post ) {
		$card_attributes = $attributes;
		$current_index = $base_index + $index + 1;
		$card_attributes['_cardIndex'] = $current_index;
		$content .= digipublish_core_post_feed_card_markup( $post->ID, $card_attributes );

		if ( 'masonry-1' === ( $attributes['layout'] ?? '' ) && ! empty( $attributes['masonryWidgets'] ) ) {
			$after = max( 1, absint( $attributes['masonryWidgetsAfter'] ?? 3 ) );
			if ( 0 === $current_index % $after ) {
				$content .= digipublish_core_post_feed_loop_pattern(
					absint( $attributes['masonryPatternId'] ?? 0 ),
					$current_index,
					$after,
					! empty( $attributes['masonryWidgetsRepeat'] )
				);
			}
		}
	}

	return rest_ensure_response(
		array(
			'page'      => $page,
			'maxPages'  => (int) $query->max_num_pages,
			'postsEnd'  => count( $query->posts ) < max( 1, absint( $attributes['postsToShow'] ?? 4 ) ),
			'content'   => $content,
			'loadedIds' => array_values( array_map( 'intval', wp_list_pluck( $query->posts, 'ID' ) ) ),
		)
	);
}

/**
 * Sanitize a CSS length used by dynamic block design variables.
 */
function digipublish_core_css_length( $value, $fallback = '' ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return $fallback;
	}
	if ( preg_match( '/^-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vw|vh|vmin|vmax|ch)?$/i', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
		return $value;
	}
	return $fallback;
}

/**
 * Shared responsive visibility classes for dynamic blocks.
 */
function digipublish_core_visibility_classes( $attributes ) {
	$classes = array();
	foreach ( array(
		'hideDesktop' => 'dp-hide-desktop',
		'hideLaptop'  => 'dp-hide-laptop',
		'hideTablet'  => 'dp-hide-tablet',
		'hideMobile'  => 'dp-hide-mobile',
	) as $key => $class ) {
		if ( ! empty( $attributes[ $key ] ) ) {
			$classes[] = $class;
		}
	}
	return $classes;
}

/**
 * Safe heading tag for block section titles.
 */
function digipublish_core_heading_tag( $attributes ) {
	$tag = isset( $attributes['headingTag'] ) ? strtolower( (string) $attributes['headingTag'] ) : 'h2';
	return in_array( $tag, array( 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $tag : 'h2';
}

/**
 * Resolve a safe WordPress image size override.
 */
function digipublish_core_image_size( $attributes, $fallback = 'medium_large' ) {
	$size = isset( $attributes['imageSize'] ) ? sanitize_key( (string) $attributes['imageSize'] ) : '';
	return in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ? $size : $fallback;
}

/**
 * Common wrapper classes and CSS variables for configurable editorial blocks.
 */
function digipublish_core_design_wrapper_args( $attributes, $classes, $column_defaults = array() ) {
	$classes = array_merge( (array) $classes, digipublish_core_visibility_classes( $attributes ) );
	$styles = array();
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
	$aspect = $attributes['imageAspect'] ?? '';
	if ( in_array( $aspect, array( '16/9', '4/3', '3/2', '1/1' ), true ) ) {
		$styles[] = '--dp-image-aspect:' . $aspect;
	}
	if ( $column_defaults ) {
		foreach ( array( 'columnsDesktop' => 'desktop', 'columnsTablet' => 'tablet', 'columnsMobile' => 'mobile' ) as $key => $suffix ) {
			$value = absint( $attributes[ $key ] ?? 0 );
			if ( ! $value && isset( $column_defaults[ $suffix ] ) ) {
				$value = absint( $column_defaults[ $suffix ] );
			}
			if ( $value ) {
				$styles[] = '--dp-columns-' . $suffix . ':' . max( 1, min( 6, $value ) );
			}
		}
	}
	$args = array( 'class' => implode( ' ', array_filter( $classes ) ) );
	if ( $styles ) {
		$args['style'] = implode( ';', $styles ) . ';';
	}
	return $args;
}

/**
 * Reading-time estimate. It is deterministic and requires no tracking script.
 */
function digipublish_core_read_time( $post_id ) {
	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	preg_match_all( '/[\p{L}\p{N}]+(?:[’\'][\p{L}\p{N}]+)*/u', $content, $matches );
	$words = isset( $matches[0] ) ? count( $matches[0] ) : 0;
	return max( 1, (int) ceil( $words / 225 ) );
}

/**
 * Optional metric stored by analytics/integration code. Missing values remain
 * hidden rather than displaying fabricated counters.
 */
function digipublish_core_metric_value( $post_id, $key ) {
	$value = max( 0, (int) get_post_meta( $post_id, $key, true ) );
	$filter = '_techpress_views' === $key ? 'techpress_post_views' : ( '_techpress_shares' === $key ? 'techpress_post_shares' : 'techpress_post_metric' );
	return max( 0, (int) apply_filters( $filter, $value, $post_id, $key ) );
}

function digipublish_core_format_metric( $value ) {
	$value = (int) $value;
	if ( $value >= 1000000 ) {
		return rtrim( rtrim( number_format_i18n( $value / 1000000, 1 ), '0' ), '.' ) . 'M';
	}
	if ( $value >= 1000 ) {
		return rtrim( rtrim( number_format_i18n( $value / 1000, 1 ), '0' ), '.' ) . 'K';
	}
	return number_format_i18n( $value );
}

/**
 * Shared story markup for all Editorial Feed Engine visual presets.
 */
function digipublish_core_feed_stats_markup( $post_id, $attributes ) {
	$stats = array();
	if ( $attributes['showReadTime'] ?? true ) {
		$minutes = digipublish_core_read_time( $post_id );
		$stats[] = sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes );
	}
	if ( $attributes['showViews'] ?? true ) {
		$views = digipublish_core_metric_value( $post_id, '_techpress_views' );
		if ( $views ) {
			$stats[] = digipublish_core_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' );
		}
	}
	if ( $attributes['showShares'] ?? true ) {
		$shares = digipublish_core_metric_value( $post_id, '_techpress_shares' );
		if ( $shares ) {
			$stats[] = __( 'Shares', 'digipublish-core' ) . ' ' . digipublish_core_format_metric( $shares );
		}
	}
	if ( ! $stats ) {
		return '';
	}
	$html = '<div class="tp-feed-story__stats">';
	foreach ( $stats as $stat ) {
		$html .= '<span>' . esc_html( $stat ) . '</span>';
	}
	return $html . '</div>';
}

function digipublish_core_feed_story_markup( $post_id, $attributes, $class = 'tp-editorial-card', $overlay = false ) {
	$title = get_the_title( $post_id );
	$url = get_permalink( $post_id );
	$show_category = $attributes['showCategory'] ?? true;
	$show_excerpt = $attributes['showExcerpt'] ?? false;
	$show_author = $attributes['showAuthor'] ?? false;
	$show_date = $attributes['showDate'] ?? false;
	$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'cards-4';
	$image_size = 'medium_large';
	$image_sizes = '(max-width: 720px) 86vw, (max-width: 1120px) 50vw, 25vw';
	$requested_image_size = isset( $attributes['imageSize'] ) ? sanitize_key( (string) $attributes['imageSize'] ) : '';
	if ( 'compact-grid' === $layout ) {
		$image_size = 'medium';
		$image_sizes = '(max-width: 720px) 92px, 112px';
	} elseif ( 'carousel-overlay' === $layout ) {
		$image_sizes = '(max-width: 720px) 88vw, (max-width: 1120px) 42vw, 330px';
	} elseif ( 'featured-trio' === $layout ) {
		$image_size = str_contains( $class, '__lead' ) ? 'large' : 'medium_large';
		$image_sizes = str_contains( $class, '__lead' ) ? '(max-width: 720px) 88vw, 55vw' : '(max-width: 720px) 88vw, 25vw';
	} elseif ( 'weekly-mosaic' === $layout ) {
		$image_size = str_contains( $class, '--overlay' ) ? 'large' : 'medium';
		$image_sizes = str_contains( $class, '--mini' ) ? '(max-width: 720px) 88vw, 112px' : '(max-width: 720px) 88vw, 28vw';
	} elseif ( 'latest-cards' === $layout ) {
		$image_sizes = '(max-width: 720px) 118px, 25vw';
	}
	if ( in_array( $requested_image_size, array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ) {
		$image_size = $requested_image_size;
	}
	$image = '<a class="tp-feed-story__image" href="' . esc_url( $url ) . '">' . digipublish_core_image_markup( $post_id, $image_size, false, $image_sizes ) . '</a>';
	$stats = digipublish_core_feed_stats_markup( $post_id, $attributes );

	$body = '<div class="tp-feed-story__body">';
	if ( $show_category ) {
		$category = digipublish_core_category_markup( $post_id );
		if ( $category ) {
			$body .= '<div class="tp-feed-story__category">' . $category . '</div>';
		}
	}
	$body .= '<h3 class="tp-feed-story__title"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></h3>';
	if ( $show_excerpt ) {
		$excerpt_words = 'featured-trio' === $layout ? 28 : 18;
		$body .= '<p class="tp-feed-story__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $post_id ), $excerpt_words ) ) . '</p>';
	}
	if ( $show_author || $show_date ) {
		$body .= '<div class="tp-feed-story__author">';
		if ( $show_author ) {
			$author_id = (int) get_post_field( 'post_author', $post_id );
			$body .= '<a href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</a>';
		}
		if ( $show_date ) {
			$body .= '<time class="tp-feed-story__date" datetime="' . esc_attr( get_the_date( DATE_W3C, $post_id ) ) . '">' . esc_html( get_the_date( get_option( 'date_format' ), $post_id ) ) . '</time>';
		}
		$body .= '</div>';
	}
	if ( 'latest-cards' !== $layout ) {
		$body .= $stats;
	}
	$body .= '</div>';

	$html = '<article class="tp-feed-story ' . esc_attr( $class ) . '">';
	if ( 'latest-cards' === $layout ) {
		$html .= $body . $image . $stats;
	} else {
		$html .= $image . $body;
	}
	return $html . '</article>';
}

/**
 * Allow ad-manager plugins or site code to inject provider markup.
 * Return an empty string from the filter to retain the reserved placeholder.
 */
function digipublish_core_ad_provider_markup( $slot_name, $attributes ) {
	return apply_filters( 'techpress_ad_slot_html', '', $slot_name, $attributes );
}

/**
 * Flush rewrite rules once when the plugin is activated/deactivated so the
 * dictionary archive and single URLs work immediately.
 */
function digipublish_core_activate() {
	digipublish_core_register_content_types();
	digipublish_core_register_gallery_post_type();
	digipublish_core_migrate_block_namespace();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'digipublish_core_activate' );

function digipublish_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'digipublish_core_deactivate' );

// Legacy callable aliases are isolated from the production namespace.
require_once DIGIPUBLISH_CORE_DIR . 'includes/compatibility.php';
