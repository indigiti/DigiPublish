<?php
/**
 * Canonical editorial query service for DigiPublish.
 *
 * Block attributes remain backward-compatible, but query normalization,
 * context resolution, de-duplication and cached feed pools live here so
 * individual renderers do not grow separate data-access rules.
 *
 * @package DigiPublish_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize an arbitrary list of IDs.
 */
function digipublish_core_query_normalize_ids( $value ) {
	return array_values(
		array_unique(
			array_filter(
				array_map( 'absint', is_array( $value ) ? $value : array() )
			)
		)
	);
}

/**
 * Normalize public/queryable post types used by editorial feeds.
 */
function digipublish_core_query_post_types( $value, $fallback = 'post' ) {
	$requested = is_array( $value ) ? $value : array( $value );
	$types     = array();

	foreach ( $requested as $post_type ) {
		$post_type = sanitize_key( (string) $post_type );
		if ( ! $post_type || 'attachment' === $post_type ) {
			continue;
		}
		$object = post_type_exists( $post_type ) ? get_post_type_object( $post_type ) : null;
		if ( ! $object || ( ! $object->public && ! $object->publicly_queryable ) ) {
			continue;
		}
		$types[] = $post_type;
	}

	$types = array_values( array_unique( $types ) );
	if ( ! $types ) {
		$types = array( sanitize_key( $fallback ) ?: 'post' );
	}

	return 1 === count( $types ) ? $types[0] : $types;
}

/**
 * Resolve the singular object relevant to a feed/query profile.
 */
function digipublish_core_query_current_post_id( $post_types = 'post' ) {
	$current_post_id = get_the_ID();
	$allowed_types   = (array) $post_types;

	if ( ! $current_post_id && is_singular() ) {
		$current_post_id = get_queried_object_id();
	}
	if ( ! $current_post_id ) {
		return 0;
	}

	$current_type = get_post_type( $current_post_id );
	return in_array( $current_type, $allowed_types, true ) ? absint( $current_post_id ) : 0;
}

/**
 * Query arguments for the flexible Posts/Featured blocks.
 */
function digipublish_core_query_post_args( $attributes ) {
	$allowed_orderby = array( 'date', 'modified', 'comment_count', 'title' );
	$order_by        = isset( $attributes['orderBy'] ) && in_array( $attributes['orderBy'], $allowed_orderby, true ) ? $attributes['orderBy'] : 'date';
	$order           = isset( $attributes['order'] ) && 'ASC' === strtoupper( (string) $attributes['order'] ) ? 'ASC' : 'DESC';
	$count           = isset( $attributes['postsToShow'] ) ? absint( $attributes['postsToShow'] ) : 4;
	$count           = max( 1, min( 100, $count ) );
	$pagination      = isset( $attributes['paginationType'] ) ? sanitize_key( $attributes['paginationType'] ) : 'none';
	$pagination      = 'standard' === $pagination ? 'numbers' : $pagination;
	$has_pagination  = in_array( $pagination, array( 'numbers', 'ajax', 'infinite' ), true );
	$paged           = ! empty( $attributes['_paged'] ) ? max( 1, absint( $attributes['_paged'] ) ) : max( 1, absint( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
	$post_type       = digipublish_core_query_post_types( $attributes['postType'] ?? 'post' );

	$args = array(
		'post_type'           => $post_type,
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'orderby'             => $order_by,
		'order'               => $order,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => ! $has_pagination,
	);

	$offset = isset( $attributes['offset'] ) ? max( 0, min( 10000, absint( $attributes['offset'] ) ) ) : 0;
	if ( $offset ) {
		$args['offset'] = $offset + ( $has_pagination ? ( $paged - 1 ) * $count : 0 );
	} elseif ( $has_pagination ) {
		$args['paged'] = $paged;
	}

	$include_categories = digipublish_core_query_normalize_ids( $attributes['filterCategoryIds'] ?? array() );
	if ( ! $include_categories && ! empty( $attributes['categoryId'] ) ) {
		$include_categories = array( absint( $attributes['categoryId'] ) );
	}
	$include_tags       = digipublish_core_query_normalize_ids( $attributes['filterTagIds'] ?? array() );
	$exclude_categories = digipublish_core_query_normalize_ids( $attributes['excludeCategoryIds'] ?? array() );
	$exclude_tags       = digipublish_core_query_normalize_ids( $attributes['excludeTagIds'] ?? array() );
	$include_posts      = digipublish_core_query_normalize_ids( $attributes['filterPostIds'] ?? array() );

	if ( is_string( $post_type ) && $include_categories && is_object_in_taxonomy( $post_type, 'category' ) ) {
		$args['category__in'] = $include_categories;
	}
	if ( is_string( $post_type ) && $include_tags && is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
		$args['tag__in'] = $include_tags;
	}
	if ( is_string( $post_type ) && $exclude_categories && is_object_in_taxonomy( $post_type, 'category' ) ) {
		$args['category__not_in'] = $exclude_categories;
	}
	if ( is_string( $post_type ) && $exclude_tags && is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
		$args['tag__not_in'] = $exclude_tags;
	}
	if ( $include_posts ) {
		$args['post__in'] = $include_posts;
		if ( 'date' === $order_by ) {
			$args['orderby'] = 'post__in';
		}
	}

	$tax_query = array();
	$formats   = array_values( array_unique( array_filter( array_map( 'sanitize_key', is_array( $attributes['postFormats'] ?? null ) ? $attributes['postFormats'] : array() ) ) ) );
	if ( 'post' === $post_type && $formats && taxonomy_exists( 'post_format' ) ) {
		$include_standard = in_array( 'standard', $formats, true );
		$format_terms     = array();
		foreach ( $formats as $format ) {
			if ( 'standard' !== $format ) {
				$format_terms[] = 'post-format-' . $format;
			}
		}
		$format_query = array( 'relation' => 'OR' );
		if ( $format_terms ) {
			$format_query[] = array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => $format_terms,
				'operator' => 'IN',
			);
		}
		if ( $include_standard ) {
			$format_query[] = array(
				'taxonomy' => 'post_format',
				'operator' => 'NOT EXISTS',
			);
		}
		if ( count( $format_query ) > 1 ) {
			$tax_query[] = $format_query;
		}
	}

	$custom_taxonomy = isset( $attributes['filterTaxonomy'] ) ? sanitize_key( (string) $attributes['filterTaxonomy'] ) : '';
	$custom_terms    = digipublish_core_query_normalize_ids( $attributes['filterTermIds'] ?? array() );
	if ( $custom_taxonomy && $custom_terms && taxonomy_exists( $custom_taxonomy ) && is_string( $post_type ) && is_object_in_taxonomy( $post_type, $custom_taxonomy ) ) {
		$tax_query[] = array(
			'taxonomy' => $custom_taxonomy,
			'field'    => 'term_id',
			'terms'    => $custom_terms,
			'operator' => 'IN',
		);
	}
	if ( $tax_query ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$args['tax_query'] = $tax_query;
	}

	$related_post_id = ! empty( $attributes['_relatedPostId'] ) ? absint( $attributes['_relatedPostId'] ) : digipublish_core_query_current_post_id( $post_type );
	if ( ! empty( $attributes['relatedPosts'] ) && $related_post_id ) {
		$args['post__not_in'] = array( $related_post_id );
		if ( is_string( $post_type ) && is_object_in_taxonomy( $post_type, 'category' ) ) {
			$current_categories = wp_get_post_categories( $related_post_id );
			if ( $current_categories ) {
				if ( ! empty( $args['category__in'] ) ) {
					$related_categories = array_values( array_intersect( $args['category__in'], $current_categories ) );
					if ( $related_categories ) {
						$args['category__in'] = $related_categories;
					} else {
						$args['post__in'] = array( 0 );
					}
				} else {
					$args['category__in'] = array_values( array_map( 'absint', $current_categories ) );
				}
			}
		}
	}

	$explicit_exclusions = digipublish_core_query_normalize_ids( $attributes['_excludePostIds'] ?? array() );
	if ( $explicit_exclusions ) {
		$existing_exclusions  = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$args['post__not_in'] = array_values( array_unique( array_map( 'absint', array_merge( $existing_exclusions, $explicit_exclusions ) ) ) );
	}

	if ( ! empty( $attributes['avoidDuplicates'] ) ) {
		$seen = digipublish_core_rendered_post_ids();
		if ( $seen ) {
			$existing_exclusions  = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
			$args['post__not_in'] = array_values( array_unique( array_map( 'absint', array_merge( $existing_exclusions, $seen ) ) ) );
		}
	}

	return $args;
}

/**
 * Query arguments for editorial feed-style blocks.
 *
 * Supports one or multiple public post types so sidebar/gallery feeds can use
 * the same context, period and fallback semantics as post-only feeds.
 */
function digipublish_core_query_feed_args( $attributes ) {
	$allowed_orderby = array( 'date', 'modified', 'comment_count', 'title' );
	$order_by        = isset( $attributes['orderBy'] ) && in_array( $attributes['orderBy'], $allowed_orderby, true ) ? $attributes['orderBy'] : 'date';
	$order           = isset( $attributes['order'] ) && 'ASC' === strtoupper( (string) $attributes['order'] ) ? 'ASC' : 'DESC';
	$count           = isset( $attributes['postsToShow'] ) ? absint( $attributes['postsToShow'] ) : 8;
	$count           = max( 1, min( 100, $count ) );
	$mode            = isset( $attributes['sourceMode'] ) ? sanitize_key( $attributes['sourceMode'] ) : 'latest';
	if ( ! in_array( $mode, array( 'latest', 'category', 'current', 'manual' ), true ) ) {
		$mode = 'latest';
	}

	$post_types = digipublish_core_query_post_types(
		$attributes['postTypes'] ?? ( $attributes['postType'] ?? 'post' )
	);

	$args = array(
		'post_type'              => $post_types,
		'post_status'            => 'publish',
		'posts_per_page'         => $count,
		'orderby'                => $order_by,
		'order'                  => $order,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	$current_post_id = digipublish_core_query_current_post_id( $post_types );
	if ( $current_post_id ) {
		$args['post__not_in'] = array( $current_post_id );
	}

	if ( 'category' === $mode ) {
		$category_id = isset( $attributes['categoryId'] ) ? absint( $attributes['categoryId'] ) : 0;
		if ( $category_id ) {
			$args['category__in'] = array( $category_id );
		} else {
			$category_slug = isset( $attributes['categorySlug'] ) ? sanitize_title( (string) $attributes['categorySlug'] ) : '';
			if ( $category_slug ) {
				$category = get_category_by_slug( $category_slug );
				if ( $category instanceof WP_Term ) {
					$args['category__in'] = array( (int) $category->term_id );
				}
			}
		}
	} elseif ( 'current' === $mode ) {
		if ( is_category() ) {
			$args['category__in'] = array( get_queried_object_id() );
		} elseif ( is_tag() ) {
			$args['tag__in'] = array( get_queried_object_id() );
		} elseif ( is_author() ) {
			$args['author'] = get_queried_object_id();
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => $term->taxonomy,
						'field'    => 'term_id',
						'terms'    => array( $term->term_id ),
					),
				);
			}
		} elseif ( is_search() ) {
			$args['s'] = get_search_query();
		} elseif ( $current_post_id ) {
			$cats = wp_get_post_categories( $current_post_id );
			if ( ! empty( $cats ) ) {
				$args['category__in'] = array( (int) $cats[0] );
			}
		}
	} elseif ( 'manual' === $mode ) {
		unset( $args['post__not_in'] );
		$raw = isset( $attributes['manualPostIds'] ) ? (string) $attributes['manualPostIds'] : '';
		$ids = array_values( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $raw ) ) ) );
		$ids = array_slice( array_unique( $ids ), 0, 100 );
		if ( $ids ) {
			$args['post__in']       = $ids;
			$args['orderby']        = 'post__in';
			$args['posts_per_page'] = min( $count, count( $ids ) );
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	$period = isset( $attributes['period'] ) ? sanitize_key( $attributes['period'] ) : 'all';
	if ( 'manual' !== $mode && in_array( $period, array( 'day', 'week', 'month' ), true ) ) {
		$days = 'day' === $period ? 1 : ( 'week' === $period ? 7 : 30 );
		$args['date_query'] = array(
			array(
				'after'     => $days . ' days ago',
				'inclusive' => true,
			),
		);
	}

	$explicit_exclusions = digipublish_core_query_normalize_ids( $attributes['_excludePostIds'] ?? array() );
	if ( $explicit_exclusions ) {
		$existing             = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$args['post__not_in'] = array_values( array_unique( array_merge( $existing, $explicit_exclusions ) ) );
	}

	return $args;
}

/**
 * Request-scoped registry used by curated sections to avoid repeats.
 */
function digipublish_core_rendered_post_ids( $add = array() ) {
	static $seen = array();
	foreach ( (array) $add as $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id ) {
			$seen[ $post_id ] = true;
		}
	}
	return array_map( 'intval', array_keys( $seen ) );
}

/**
 * Execute a feed query through a shared cached pool.
 */
function digipublish_core_query_feed_posts( $attributes ) {
	static $request_cache = array();

	$count  = isset( $attributes['postsToShow'] ) ? max( 1, min( 100, absint( $attributes['postsToShow'] ) ) ) : 8;
	$mode   = isset( $attributes['sourceMode'] ) ? sanitize_key( $attributes['sourceMode'] ) : 'latest';
	$manual = 'manual' === $mode;
	$args   = digipublish_core_query_feed_args( $attributes );
	$args['posts_per_page'] = $manual ? min( 100, max( $count, count( $args['post__in'] ?? array() ) ) ) : min( 100, max( 32, $count * 2 ) );

	$get_pool = static function ( $query_args ) use ( &$request_cache ) {
		$key_args = $query_args;
		unset( $key_args['posts_per_page'] );
		$cache_key = md5( wp_json_encode( $key_args ) );
		$scope     = 'comment_count' === ( $query_args['orderby'] ?? '' ) ? 'popularity' : 'content';

		if ( ! isset( $request_cache[ $cache_key ] ) ) {
			$object_key = 'v' . digipublish_core_cache_version( $scope ) . '_feed_' . $cache_key;
			$post_ids   = wp_cache_get( $object_key, 'digipublish_core' );
			if ( false === $post_ids ) {
				$query    = new WP_Query( $query_args );
				$post_ids = wp_list_pluck( $query->posts, 'ID' );
				wp_cache_set( $object_key, $post_ids, 'digipublish_core', 15 * MINUTE_IN_SECONDS );
			}
			$post_ids = array_map( 'absint', (array) $post_ids );
			_prime_post_caches( $post_ids, true, true );
			$request_cache[ $cache_key ] = array_values( array_filter( array_map( 'get_post', $post_ids ) ) );
		}

		return $request_cache[ $cache_key ];
	};

	$seen_ids = ! empty( $attributes['avoidDuplicates'] ) ? digipublish_core_rendered_post_ids() : array();
	$seen     = array_flip( array_map( 'intval', $seen_ids ) );
	$result   = array();

	$append_unique = static function ( $posts ) use ( &$result, &$seen, $count ) {
		foreach ( (array) $posts as $post ) {
			if ( count( $result ) >= $count ) {
				break;
			}
			if ( ! $post instanceof WP_Post || isset( $seen[ $post->ID ] ) ) {
				continue;
			}
			$result[]          = $post;
			$seen[ $post->ID ] = true;
		}
	};

	$append_unique( $get_pool( $args ) );

	if ( count( $result ) < $count && 'category' === $mode && ! empty( $attributes['fillFromLatest'] ) ) {
		$fallback_attributes                 = $attributes;
		$fallback_attributes['sourceMode']   = 'latest';
		$fallback_attributes['categoryId']   = 0;
		$fallback_attributes['categorySlug'] = '';

		$fallback_args                   = digipublish_core_query_feed_args( $fallback_attributes );
		$fallback_args['posts_per_page'] = min( 100, max( 40, $count * 3 ) );
		$append_unique( $get_pool( $fallback_args ) );

		if ( count( $result ) < $count ) {
			$targeted_args                   = $fallback_args;
			$targeted_args['posts_per_page'] = $count - count( $result );
			$targeted_args['post__not_in']   = array_values( array_unique( array_map( 'intval', array_keys( $seen ) ) ) );
			$targeted                        = new WP_Query( $targeted_args );
			$append_unique( $targeted->posts );
		}
	}

	if ( count( $result ) < $count && ! $manual && ( 'category' !== $mode || empty( $attributes['fillFromLatest'] ) ) ) {
		$targeted_args                   = $args;
		$targeted_args['posts_per_page'] = $count - count( $result );
		$targeted_args['post__not_in']   = array_values( array_unique( array_map( 'intval', array_keys( $seen ) ) ) );
		$targeted                        = new WP_Query( $targeted_args );
		$append_unique( $targeted->posts );
	}

	if ( count( $result ) < $count && ! $manual && ! empty( $attributes['fallbackRandom'] ) ) {
		$random_attributes                 = $attributes;
		$random_attributes['sourceMode']   = 'latest';
		$random_attributes['categoryId']   = 0;
		$random_attributes['categorySlug'] = '';
		$random_attributes['period']       = 'all';
		$random_attributes['orderBy']      = 'date';
		$random_args                       = digipublish_core_query_feed_args( $random_attributes );
		$random_args['posts_per_page']     = max( 24, min( 100, $count * 8 ) );

		$random_pool = $get_pool( $random_args );
		if ( $random_pool ) {
			shuffle( $random_pool );
			$append_unique( $random_pool );
		}

		if ( count( $result ) < $count && $random_pool ) {
			$selected = array_flip( array_map( 'intval', wp_list_pluck( $result, 'ID' ) ) );
			shuffle( $random_pool );
			foreach ( $random_pool as $post ) {
				if ( count( $result ) >= $count ) {
					break;
				}
				if ( ! $post instanceof WP_Post || isset( $selected[ $post->ID ] ) ) {
					continue;
				}
				$result[]             = $post;
				$selected[ $post->ID ] = true;
			}
		}
	}

	$result = array_slice( $result, 0, $count );

	if ( ! empty( $attributes['avoidDuplicates'] ) ) {
		digipublish_core_rendered_post_ids( wp_list_pluck( $result, 'ID' ) );
	}

	return $result;
}

/**
 * Resolve the View All destination for a feed while allowing an explicit URL.
 */
function digipublish_core_query_view_all_url( $attributes ) {
	if ( ! empty( $attributes['viewAllUrl'] ) ) {
		return esc_url_raw( $attributes['viewAllUrl'] );
	}
	$mode = isset( $attributes['sourceMode'] ) ? sanitize_key( $attributes['sourceMode'] ) : 'latest';
	if ( 'category' === $mode ) {
		$category_id = ! empty( $attributes['categoryId'] ) ? absint( $attributes['categoryId'] ) : 0;
		if ( ! $category_id && ! empty( $attributes['categorySlug'] ) ) {
			$category    = get_category_by_slug( sanitize_title( (string) $attributes['categorySlug'] ) );
			$category_id = $category instanceof WP_Term ? (int) $category->term_id : 0;
		}
		if ( $category_id ) {
			$url = get_category_link( $category_id );
			return is_wp_error( $url ) ? '' : $url;
		}
	}
	if ( 'current' === $mode && ( is_category() || is_tag() || is_tax() ) ) {
		$url = get_term_link( get_queried_object() );
		return is_wp_error( $url ) ? '' : $url;
	}
	if ( 'current' === $mode && is_author() ) {
		return get_author_posts_url( get_queried_object_id() );
	}
	if ( 'current' === $mode && is_singular() ) {
		$cats = get_the_category( get_queried_object_id() );
		if ( ! empty( $cats ) ) {
			$url = get_category_link( $cats[0] );
			return is_wp_error( $url ) ? '' : $url;
		}
	}
	$posts_page = absint( get_option( 'page_for_posts' ) );
	return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}
