<?php
/**
 * Stored-data namespace compatibility for DigiPublish Core.
 *
 * Canonical 1.x keys use digipublish_* / _digipublish_* names. Historical
 * TechPress keys remain readable and are mirrored during the compatibility
 * window so existing sites and integrations keep working without a bulk
 * database rewrite.
 *
 * @package DigiPublish_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical-to-legacy post meta map.
 */
function digipublish_core_post_meta_compat_map() {
	return array(
		'_digipublish_attribution_type' => '_techpress_attribution_type',
		'_digipublish_attribution_user' => '_techpress_attribution_user',
		'_digipublish_views'            => '_techpress_views',
		'_digipublish_shares'           => '_techpress_shares',
	);
}

/**
 * Canonical-to-legacy user meta map.
 */
function digipublish_core_user_meta_compat_map() {
	return array(
		'digipublish_role'      => 'techpress_role',
		'digipublish_linkedin'  => 'techpress_linkedin',
		'digipublish_x'         => 'techpress_x',
		'digipublish_instagram' => 'techpress_instagram',
		'digipublish_youtube'   => 'techpress_youtube',
	);
}

function digipublish_core_meta_legacy_key( $canonical, $type = 'post' ) {
	$map = 'user' === $type ? digipublish_core_user_meta_compat_map() : digipublish_core_post_meta_compat_map();
	return $map[ $canonical ] ?? '';
}

function digipublish_core_meta_canonical_key( $legacy, $type = 'post' ) {
	$map = 'user' === $type ? digipublish_core_user_meta_compat_map() : digipublish_core_post_meta_compat_map();
	$reverse = array_flip( $map );
	return $reverse[ $legacy ] ?? '';
}

/**
 * Read canonical post meta, falling back to the historical key only when the
 * canonical key has never been stored.
 */
function digipublish_core_get_post_meta_compat( $post_id, $canonical ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return '';
	}

	if ( metadata_exists( 'post', $post_id, $canonical ) ) {
		return get_post_meta( $post_id, $canonical, true );
	}

	$legacy = digipublish_core_meta_legacy_key( $canonical, 'post' );
	return $legacy ? get_post_meta( $post_id, $legacy, true ) : get_post_meta( $post_id, $canonical, true );
}

function digipublish_core_update_post_meta_compat( $post_id, $canonical, $value ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return false;
	}

	update_post_meta( $post_id, $canonical, $value );
	$legacy = digipublish_core_meta_legacy_key( $canonical, 'post' );
	if ( $legacy ) {
		update_post_meta( $post_id, $legacy, $value );
	}
	return true;
}

/**
 * Read canonical user meta with historical fallback.
 */
function digipublish_core_get_user_meta_compat( $user_id, $canonical ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return '';
	}

	if ( metadata_exists( 'user', $user_id, $canonical ) ) {
		return get_user_meta( $user_id, $canonical, true );
	}

	$legacy = digipublish_core_meta_legacy_key( $canonical, 'user' );
	return $legacy ? get_user_meta( $user_id, $legacy, true ) : get_user_meta( $user_id, $canonical, true );
}

function digipublish_core_update_user_meta_compat( $user_id, $canonical, $value ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return false;
	}

	update_user_meta( $user_id, $canonical, $value );
	$legacy = digipublish_core_meta_legacy_key( $canonical, 'user' );
	if ( $legacy ) {
		update_user_meta( $user_id, $legacy, $value );
	}
	return true;
}

/**
 * Mirror direct post-meta writes from either namespace.
 */
function digipublish_core_sync_post_meta_namespace( $meta_id, $post_id, $meta_key, $meta_value ) {
	static $syncing = false;
	if ( $syncing ) {
		return;
	}

	$canonical = digipublish_core_meta_canonical_key( $meta_key, 'post' );
	$legacy    = digipublish_core_meta_legacy_key( $meta_key, 'post' );
	if ( ! $canonical && ! $legacy ) {
		return;
	}

	$syncing = true;
	if ( $canonical ) {
		update_post_meta( $post_id, $canonical, $meta_value );
	} elseif ( $legacy ) {
		update_post_meta( $post_id, $legacy, $meta_value );
	}
	$syncing = false;
}
add_action( 'added_post_meta', 'digipublish_core_sync_post_meta_namespace', 10, 4 );
add_action( 'updated_post_meta', 'digipublish_core_sync_post_meta_namespace', 10, 4 );

function digipublish_core_sync_deleted_post_meta_namespace( $meta_ids, $post_id, $meta_key ) {
	static $syncing = false;
	if ( $syncing ) {
		return;
	}

	$canonical = digipublish_core_meta_canonical_key( $meta_key, 'post' );
	$legacy    = digipublish_core_meta_legacy_key( $meta_key, 'post' );
	if ( ! $canonical && ! $legacy ) {
		return;
	}

	$syncing = true;
	delete_post_meta( $post_id, $canonical ?: $legacy );
	$syncing = false;
}
add_action( 'deleted_post_meta', 'digipublish_core_sync_deleted_post_meta_namespace', 10, 3 );

/**
 * Mirror direct user-meta writes from either namespace.
 */
function digipublish_core_sync_user_meta_namespace( $meta_id, $user_id, $meta_key, $meta_value ) {
	static $syncing = false;
	if ( $syncing ) {
		return;
	}

	$canonical = digipublish_core_meta_canonical_key( $meta_key, 'user' );
	$legacy    = digipublish_core_meta_legacy_key( $meta_key, 'user' );
	if ( ! $canonical && ! $legacy ) {
		return;
	}

	$syncing = true;
	if ( $canonical ) {
		update_user_meta( $user_id, $canonical, $meta_value );
	} elseif ( $legacy ) {
		update_user_meta( $user_id, $legacy, $meta_value );
	}
	$syncing = false;
}
add_action( 'added_user_meta', 'digipublish_core_sync_user_meta_namespace', 10, 4 );
add_action( 'updated_user_meta', 'digipublish_core_sync_user_meta_namespace', 10, 4 );

function digipublish_core_sync_deleted_user_meta_namespace( $meta_ids, $user_id, $meta_key ) {
	static $syncing = false;
	if ( $syncing ) {
		return;
	}

	$canonical = digipublish_core_meta_canonical_key( $meta_key, 'user' );
	$legacy    = digipublish_core_meta_legacy_key( $meta_key, 'user' );
	if ( ! $canonical && ! $legacy ) {
		return;
	}

	$syncing = true;
	delete_user_meta( $user_id, $canonical ?: $legacy );
	$syncing = false;
}
add_action( 'deleted_user_meta', 'digipublish_core_sync_deleted_user_meta_namespace', 10, 3 );
