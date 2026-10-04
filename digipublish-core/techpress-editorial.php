<?php
/**
 * Plugin Name: DigiPublish Core
 * Description: Dynamic Gutenberg blocks and editorial content types for the DigiPublish publishing framework.
 * Version: 0.8.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: indigiti
 * License: GPL-2.0-or-later
 * Text Domain: digipublish-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECHPRESS_EDITORIAL_VERSION', '0.8.0' );
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

/**
 * Register editorial content types.
 */
function techpress_editorial_register_content_types() {
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
add_action( 'init', 'techpress_editorial_register_content_types', 5 );


/**
 * Per-post editorial attribution used by the article byline block.
 */
function techpress_editorial_register_attribution_meta() {
	register_post_meta(
		'post',
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
		'post',
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
add_action( 'init', 'techpress_editorial_register_attribution_meta', 6 );

/**
 * Human-readable label for the selected editorial attribution.
 */
function techpress_editorial_attribution_label( $type ) {
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
function techpress_editorial_register_blocks() {
	$editor_js = TECHPRESS_EDITORIAL_DIR . 'assets/editor.js';

	wp_register_script(
		'digipublish-core-editor',
		TECHPRESS_EDITORIAL_URL . 'assets/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-plugins', 'wp-editor' ),
		file_exists( $editor_js ) ? (string) filemtime( $editor_js ) : TECHPRESS_EDITORIAL_VERSION,
		true
	);

	$blocks = array( 'featured-posts', 'post-feed', 'editorial-feed', 'ad-slot', 'term-index', 'category-nav', 'archive-hero', 'archive-feed', 'author-profile', 'popular-categories', 'category-experts', 'related-posts', 'post-author-card', 'article-toc', 'article-byline' );
	foreach ( $blocks as $block ) {
		register_block_type( TECHPRESS_EDITORIAL_DIR . 'blocks/' . $block );
	}
}
add_action( 'init', 'techpress_editorial_register_blocks', 20 );

/**
 * Add a dedicated inserter category.
 */
function techpress_editorial_block_categories( $categories ) {
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
add_filter( 'block_categories_all', 'techpress_editorial_block_categories' );

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

	return str_replace(
		array( $open_legacy, $close_legacy ),
		array( '<!-- wp:digipublish/', '<!-- /wp:digipublish/' ),
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
	$open_legacy      = '<!-- wp:' . $legacy_namespace . '/';
	$close_legacy     = '<!-- /wp:' . $legacy_namespace . '/';
	$open_like        = '%' . $wpdb->esc_like( $open_legacy ) . '%';
	$close_like       = '%' . $wpdb->esc_like( $close_legacy ) . '%';

	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_content LIKE %s",
			$open_like,
			$close_like
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
add_filter( 'render_block_data', 'digipublish_core_render_block_namespace_fallback', 5 );


/**
 * Optional publication profile fields used by the author archive hero.
 */
function techpress_editorial_author_profile_fields( $user ) {
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
add_action( 'show_user_profile', 'techpress_editorial_author_profile_fields' );
add_action( 'edit_user_profile', 'techpress_editorial_author_profile_fields' );

function techpress_editorial_save_author_profile_fields( $user_id ) {
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
add_action( 'personal_options_update', 'techpress_editorial_save_author_profile_fields' );
add_action( 'edit_user_profile_update', 'techpress_editorial_save_author_profile_fields' );


/**
 * Cached top-level categories used by navigation and editorial blocks.
 */
function techpress_editorial_get_top_categories( $limit = 8, $exclude_default = true ) {
	$limit = max( 1, min( 20, absint( $limit ) ) );
	$key   = 'v' . techpress_editorial_cache_version() . '_top_categories_' . $limit . '_' . ( $exclude_default ? '1' : '0' );
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
function techpress_editorial_get_category_expert_ids( $term_id, $limit = 5 ) {
	global $wpdb;

	$term_id = absint( $term_id );
	$limit   = max( 1, min( 12, absint( $limit ) ) );
	if ( ! $term_id ) {
		return array();
	}

	$key = 'v' . techpress_editorial_cache_version() . '_category_experts_' . $term_id . '_' . $limit;
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
 * Versioned cache namespace so content changes invalidate cached editorial
 * lookups without flushing unrelated site caches.
 */
function techpress_editorial_cache_version() {
	return max( 1, absint( get_option( 'techpress_editorial_cache_version', 1 ) ) );
}

function techpress_editorial_bump_cache_version( $object_id = 0 ) {
	if ( 'save_post' === current_filter() && $object_id && ( wp_is_post_revision( $object_id ) || wp_is_post_autosave( $object_id ) ) ) {
		return;
	}
	update_option( 'techpress_editorial_cache_version', techpress_editorial_cache_version() + 1, false );
}
add_action( 'save_post', 'techpress_editorial_bump_cache_version' );
add_action( 'created_term', 'techpress_editorial_bump_cache_version' );
add_action( 'edited_term', 'techpress_editorial_bump_cache_version' );
add_action( 'delete_term', 'techpress_editorial_bump_cache_version' );
add_action( 'wp_insert_comment', 'techpress_editorial_bump_cache_version' );
add_action( 'edit_comment', 'techpress_editorial_bump_cache_version' );
add_action( 'delete_comment', 'techpress_editorial_bump_cache_version' );

/**
 * Keep framework archive/search templates aligned with the main WordPress
 * query so the archive-feed block can render it without a duplicate query.
 */
function techpress_editorial_optimize_main_queries( $query ) {
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
add_action( 'pre_get_posts', 'techpress_editorial_optimize_main_queries' );

/**
 * Safe post-query arguments shared by editorial blocks.
 */
function techpress_editorial_post_query_args( $attributes ) {
	$allowed_orderby = array( 'date', 'modified', 'comment_count', 'title' );
	$order_by        = isset( $attributes['orderBy'] ) && in_array( $attributes['orderBy'], $allowed_orderby, true ) ? $attributes['orderBy'] : 'date';
	$count           = isset( $attributes['postsToShow'] ) ? absint( $attributes['postsToShow'] ) : 4;
	$count           = max( 1, min( 12, $count ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'orderby'             => $order_by,
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$category_id = isset( $attributes['categoryId'] ) ? absint( $attributes['categoryId'] ) : 0;
	if ( $category_id ) {
		$args['cat'] = $category_id;
	}

	return $args;
}

/**
 * Category label for a card.
 */
function techpress_editorial_category_markup( $post_id ) {
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
function techpress_editorial_category_icon_svg( $category ) {
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
 * Consistent post image with a non-breaking fallback.
 *
 * WordPress already adds responsive srcset/sizes and loading optimization.
 * Only the single above-the-fold lead image is explicitly promoted so it can
 * be discovered and fetched early for LCP.
 */
function techpress_editorial_image_markup( $post_id, $size = 'large', $priority = false ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$attrs = array(
			'alt'      => the_title_attribute( array( 'echo' => false, 'post' => $post_id ) ),
			'decoding' => 'async',
		);

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
function techpress_editorial_meta_markup( $post_id, $show_author, $show_date ) {
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
function techpress_editorial_card_markup( $post_id, $attributes = array() ) {
	$show_image   = $attributes['showImage'] ?? true;
	$show_excerpt = $attributes['showExcerpt'] ?? false;
	$show_author  = $attributes['showAuthor'] ?? true;
	$show_date    = $attributes['showDate'] ?? true;

	$html = '<article class="tp-card">';
	if ( $show_image ) {
		$html .= '<a class="tp-card__image" href="' . esc_url( get_permalink( $post_id ) ) . '">' . techpress_editorial_image_markup( $post_id ) . '</a>';
	}
	$html .= '<div class="tp-card__body">';
	$html .= techpress_editorial_category_markup( $post_id );
	$html .= '<h3 class="tp-card__title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
	if ( $show_excerpt ) {
		$html .= '<p class="tp-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $post_id ), 24 ) ) . '</p>';
	}
	$html .= techpress_editorial_meta_markup( $post_id, $show_author, $show_date );
	$html .= '</div></article>';
	return $html;
}


/**
 * Query builder for the reusable Editorial Feed Engine.
 */
function techpress_editorial_feed_query_args( $attributes ) {
	$allowed_orderby = array( 'date', 'modified', 'comment_count', 'title' );
	$order_by = isset( $attributes['orderBy'] ) && in_array( $attributes['orderBy'], $allowed_orderby, true ) ? $attributes['orderBy'] : 'date';
	$count = isset( $attributes['postsToShow'] ) ? absint( $attributes['postsToShow'] ) : 8;
	$count = max( 1, min( 16, $count ) );
	$mode = isset( $attributes['sourceMode'] ) ? sanitize_key( $attributes['sourceMode'] ) : 'latest';
	if ( ! in_array( $mode, array( 'latest', 'category', 'current', 'manual' ), true ) ) {
		$mode = 'latest';
	}

	$args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => $count,
		'orderby'                => $order_by,
		'order'                  => 'DESC',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	if ( is_singular( 'post' ) ) {
		$args['post__not_in'] = array( get_queried_object_id() );
	}

	if ( 'category' === $mode ) {
		$category_id = isset( $attributes['categoryId'] ) ? absint( $attributes['categoryId'] ) : 0;
		if ( $category_id ) {
			$args['cat'] = $category_id;
		}
	} elseif ( 'current' === $mode ) {
		if ( is_category() ) {
			$args['cat'] = get_queried_object_id();
		} elseif ( is_tag() ) {
			$args['tag_id'] = get_queried_object_id();
		} elseif ( is_author() ) {
			$args['author'] = get_queried_object_id();
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$args['tax_query'] = array( array( 'taxonomy' => $term->taxonomy, 'field' => 'term_id', 'terms' => array( $term->term_id ) ) );
			}
		} elseif ( is_search() ) {
			$args['s'] = get_search_query();
		} elseif ( is_singular( 'post' ) ) {
			$cats = wp_get_post_categories( get_queried_object_id() );
			if ( ! empty( $cats ) ) {
				$args['cat'] = (int) $cats[0];
			}
		}
	} elseif ( 'manual' === $mode ) {
		unset( $args['post__not_in'] );
		$raw = isset( $attributes['manualPostIds'] ) ? (string) $attributes['manualPostIds'] : '';
		$ids = array_values( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $raw ) ) ) );
		$ids = array_slice( array_unique( $ids ), 0, 16 );
		if ( $ids ) {
			$args['post__in'] = $ids;
			$args['orderby'] = 'post__in';
			$args['posts_per_page'] = min( $count, count( $ids ) );
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	return $args;
}


/**
 * Fetch feed posts from a shared 16-post pool. Multiple homepage sections can
 * therefore reuse one request/database result when they use the same source.
 */
function techpress_editorial_feed_get_posts( $attributes ) {
	static $request_cache = array();

	$count = isset( $attributes['postsToShow'] ) ? max( 1, min( 16, absint( $attributes['postsToShow'] ) ) ) : 8;
	$args = techpress_editorial_feed_query_args( $attributes );
	$args['posts_per_page'] = 'manual' === ( $attributes['sourceMode'] ?? 'latest' ) ? min( 16, max( $count, count( $args['post__in'] ?? array() ) ) ) : 16;

	$key_args = $args;
	unset( $key_args['posts_per_page'] );
	$cache_key = md5( wp_json_encode( $key_args ) );
	if ( isset( $request_cache[ $cache_key ] ) ) {
		return array_slice( $request_cache[ $cache_key ], 0, $count );
	}

	$object_key = 'v' . techpress_editorial_cache_version() . '_feed_' . $cache_key;
	$post_ids = wp_cache_get( $object_key, 'techpress_editorial' );
	if ( false === $post_ids ) {
		$query = new WP_Query( $args );
		$post_ids = wp_list_pluck( $query->posts, 'ID' );
		wp_cache_set( $object_key, $post_ids, 'techpress_editorial', 15 * MINUTE_IN_SECONDS );
	}
	$post_ids = array_map( 'absint', (array) $post_ids );
	_prime_post_caches( $post_ids, true, true );
	$request_cache[ $cache_key ] = array_values( array_filter( array_map( 'get_post', $post_ids ) ) );
	return array_slice( $request_cache[ $cache_key ], 0, $count );
}

/**
 * Resolve the View All destination for a feed while allowing an explicit URL.
 */
function techpress_editorial_feed_view_all_url( $attributes ) {
	if ( ! empty( $attributes['viewAllUrl'] ) ) {
		return esc_url_raw( $attributes['viewAllUrl'] );
	}
	$mode = isset( $attributes['sourceMode'] ) ? sanitize_key( $attributes['sourceMode'] ) : 'latest';
	if ( 'category' === $mode && ! empty( $attributes['categoryId'] ) ) {
		$url = get_category_link( absint( $attributes['categoryId'] ) );
		return is_wp_error( $url ) ? '' : $url;
	}
	if ( 'current' === $mode && ( is_category() || is_tag() || is_tax() ) ) {
		$url = get_term_link( get_queried_object() );
		return is_wp_error( $url ) ? '' : $url;
	}
	if ( 'current' === $mode && is_author() ) {
		return get_author_posts_url( get_queried_object_id() );
	}
	if ( 'current' === $mode && is_singular( 'post' ) ) {
		$cats = get_the_category( get_queried_object_id() );
		if ( ! empty( $cats ) ) {
			$url = get_category_link( $cats[0] );
			return is_wp_error( $url ) ? '' : $url;
		}
	}
	$posts_page = absint( get_option( 'page_for_posts' ) );
	return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}

/**
 * Reading-time estimate. It is deterministic and requires no tracking script.
 */
function techpress_editorial_read_time( $post_id ) {
	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	preg_match_all( '/[\p{L}\p{N}]+(?:[’\'][\p{L}\p{N}]+)*/u', $content, $matches );
	$words = isset( $matches[0] ) ? count( $matches[0] ) : 0;
	return max( 1, (int) ceil( $words / 225 ) );
}

/**
 * Optional metric stored by analytics/integration code. Missing values remain
 * hidden rather than displaying fabricated counters.
 */
function techpress_editorial_metric_value( $post_id, $key ) {
	$value = max( 0, (int) get_post_meta( $post_id, $key, true ) );
	$filter = '_techpress_views' === $key ? 'techpress_post_views' : ( '_techpress_shares' === $key ? 'techpress_post_shares' : 'techpress_post_metric' );
	return max( 0, (int) apply_filters( $filter, $value, $post_id, $key ) );
}

function techpress_editorial_format_metric( $value ) {
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
function techpress_editorial_feed_stats_markup( $post_id, $attributes ) {
	$stats = array();
	if ( $attributes['showReadTime'] ?? true ) {
		$minutes = techpress_editorial_read_time( $post_id );
		$stats[] = sprintf( _n( '%d min read', '%d min read', $minutes, 'digipublish-core' ), $minutes );
	}
	if ( $attributes['showViews'] ?? true ) {
		$views = techpress_editorial_metric_value( $post_id, '_techpress_views' );
		if ( $views ) {
			$stats[] = techpress_editorial_format_metric( $views ) . ' ' . __( 'views', 'digipublish-core' );
		}
	}
	if ( $attributes['showShares'] ?? true ) {
		$shares = techpress_editorial_metric_value( $post_id, '_techpress_shares' );
		if ( $shares ) {
			$stats[] = __( 'Shares', 'digipublish-core' ) . ' ' . techpress_editorial_format_metric( $shares );
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

function techpress_editorial_feed_story_markup( $post_id, $attributes, $class = 'tp-editorial-card', $overlay = false ) {
	$title = get_the_title( $post_id );
	$url = get_permalink( $post_id );
	$show_category = $attributes['showCategory'] ?? true;
	$show_excerpt = $attributes['showExcerpt'] ?? false;
	$show_author = $attributes['showAuthor'] ?? false;
	$show_date = $attributes['showDate'] ?? false;
	$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'cards-4';
	$image_size = in_array( $layout, array( 'compact-grid', 'weekly-mosaic' ), true ) && ! str_contains( $class, '--overlay' ) ? 'medium' : 'large';
	$image = '<a class="tp-feed-story__image" href="' . esc_url( $url ) . '">' . techpress_editorial_image_markup( $post_id, $image_size ) . '</a>';
	$stats = techpress_editorial_feed_stats_markup( $post_id, $attributes );

	$body = '<div class="tp-feed-story__body">';
	if ( $show_category ) {
		$category = techpress_editorial_category_markup( $post_id );
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
function techpress_editorial_ad_provider_markup( $slot_name, $attributes ) {
	return apply_filters( 'techpress_ad_slot_html', '', $slot_name, $attributes );
}

/**
 * Flush rewrite rules once when the plugin is activated/deactivated so the
 * dictionary archive and single URLs work immediately.
 */
function techpress_editorial_activate() {
	techpress_editorial_register_content_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'techpress_editorial_activate' );

function techpress_editorial_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'techpress_editorial_deactivate' );
