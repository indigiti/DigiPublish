<?php
/**
 * DigiPublish gallery post type and helpers.
 *
 * @package DigiPublish_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register first-class photo galleries.
 */
function digipublish_core_register_gallery_post_type() {
	register_post_type(
		'digipublish_gallery',
		array(
			'labels' => array(
				'name'           => __( 'Photo Galleries', 'digipublish-core' ),
				'singular_name'  => __( 'Photo Gallery', 'digipublish-core' ),
				'add_new_item'   => __( 'Add Photo Gallery', 'digipublish-core' ),
				'edit_item'      => __( 'Edit Photo Gallery', 'digipublish-core' ),
				'new_item'       => __( 'New Photo Gallery', 'digipublish-core' ),
				'view_item'      => __( 'View Photo Gallery', 'digipublish-core' ),
				'search_items'   => __( 'Search Photo Galleries', 'digipublish-core' ),
				'not_found'      => __( 'No photo galleries found.', 'digipublish-core' ),
				'all_items'      => __( 'All Photo Galleries', 'digipublish-core' ),
				'menu_name'      => __( 'Photo Galleries', 'digipublish-core' ),
				'name_admin_bar' => __( 'Photo Gallery', 'digipublish-core' ),
				'archives'       => __( 'Photo Gallery Archives', 'digipublish-core' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'menu_icon'         => 'dashicons-format-gallery',
			'has_archive'       => 'photo-gallery',
			'rewrite'           => array(
				'slug'       => 'photo-gallery',
				'with_front' => false,
			),
			'taxonomies'        => array( 'category', 'post_tag' ),
			'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields', 'comments' ),
			'show_in_nav_menus' => true,
			'menu_position'     => 6,
			'template'          => array(
				array(
					'digipublish/gallery',
					array(
						'align'           => 'wide',
						'displayMode'     => 'story',
						'showCounter'     => true,
						'showCaptions'    => true,
						'showCredits'     => true,
						'showThumbnails'  => false,
						'allowFullscreen' => true,
						'showSharing'     => true,
						'adInterval'      => 0,
					),
				),
			),
			'template_lock'     => false,
		)
	);

	register_taxonomy_for_object_type( 'category', 'digipublish_gallery' );
	register_taxonomy_for_object_type( 'post_tag', 'digipublish_gallery' );

	// Put explicit gallery-category archive routes ahead of the generic single route.
	add_rewrite_rule(
		'^photo-gallery/category/([^/]+)/page/([0-9]+)/?$',
		'index.php?post_type=digipublish_gallery&gallery_category=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^photo-gallery/category/([^/]+)/?$',
		'index.php?post_type=digipublish_gallery&gallery_category=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^photo-gallery/(?!category/)([^/]+)/([^/]+)/?$',
		'index.php?post_type=digipublish_gallery&name=$matches[2]',
		'top'
	);
}
add_action( 'init', 'digipublish_core_register_gallery_post_type', 5 );

/**
 * Public query variable for gallery category archives.
 */
function digipublish_core_gallery_query_vars( $vars ) {
	$vars[] = 'gallery_category';
	return $vars;
}
add_filter( 'query_vars', 'digipublish_core_gallery_query_vars' );

/**
 * Filter the gallery post-type archive by its optional pretty category route.
 */
function digipublish_core_gallery_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || 'digipublish_gallery' !== $query->get( 'post_type' ) ) {
		return;
	}

	$slug = sanitize_title( (string) $query->get( 'gallery_category' ) );
	if ( $slug ) {
		$query->set( 'category_name', $slug );
	}
}
add_action( 'pre_get_posts', 'digipublish_core_gallery_archive_query', 8 );

/**
 * Category-aware gallery permalink:
 * /photo-gallery/category-slug/gallery-title/
 */
function digipublish_core_gallery_permalink( $post_link, $post, $leavename, $sample ) {
	if ( ! $post instanceof WP_Post || 'digipublish_gallery' !== $post->post_type ) {
		return $post_link;
	}

	$category_slug = 'gallery';
	$categories    = wp_get_post_categories( $post->ID );
	if ( $categories ) {
		$category = get_term( (int) $categories[0], 'category' );
		if ( $category instanceof WP_Term && ! is_wp_error( $category ) ) {
			$category_slug = $category->slug;
		}
	}

	$post_slug = $leavename ? '%postname%' : $post->post_name;
	if ( ! $post_slug ) {
		$post_slug = sanitize_title( $post->post_title );
	}

	return home_url( user_trailingslashit( 'photo-gallery/' . $category_slug . '/' . $post_slug ) );
}
add_filter( 'post_type_link', 'digipublish_core_gallery_permalink', 10, 4 );

/**
 * Recursively collect valid Gallery Slide block attributes.
 */
function digipublish_core_collect_gallery_slides( $blocks, &$slides ) {
	foreach ( (array) $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( 'digipublish/gallery-slide' === ( $block['blockName'] ?? '' ) ) {
			$attrs = (array) ( $block['attrs'] ?? array() );
			if ( ! empty( $attrs['imageId'] ) || ! empty( $attrs['imageUrl'] ) ) {
				$slides[] = $attrs;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			digipublish_core_collect_gallery_slides( $block['innerBlocks'], $slides );
		}
	}
}

/**
 * Read gallery slides from stored Gutenberg markup.
 */
function digipublish_core_get_gallery_slides( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || 'digipublish_gallery' !== get_post_type( $post_id ) ) {
		return array();
	}

	$slides = array();
	digipublish_core_collect_gallery_slides(
		parse_blocks( (string) get_post_field( 'post_content', $post_id, 'raw' ) ),
		$slides
	);
	return $slides;
}

/**
 * Cache the photo count used by archive/sidebar cards.
 */
function digipublish_core_update_gallery_slide_count( $post_id ) {
	if ( ! $post_id || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'digipublish_gallery' !== get_post_type( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_digipublish_gallery_slide_count', count( digipublish_core_get_gallery_slides( $post_id ) ) );
}
add_action( 'save_post_digipublish_gallery', 'digipublish_core_update_gallery_slide_count', 20 );

function digipublish_core_get_gallery_slide_count( $post_id ) {
	$post_id = absint( $post_id );
	$count   = get_post_meta( $post_id, '_digipublish_gallery_slide_count', true );

	if ( '' === $count ) {
		$count = count( digipublish_core_get_gallery_slides( $post_id ) );
		update_post_meta( $post_id, '_digipublish_gallery_slide_count', $count );
	}

	return max( 0, absint( $count ) );
}

/**
 * Explicit Featured Image wins. Otherwise use the current first slide so a
 * drag/reorder operation automatically changes the visual cover.
 */
function digipublish_core_get_gallery_cover_image_id( $post_id ) {
	$post_id      = absint( $post_id );
	$thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id ) {
		return absint( $thumbnail_id );
	}

	$slides = digipublish_core_get_gallery_slides( $post_id );
	return ! empty( $slides[0]['imageId'] ) ? absint( $slides[0]['imageId'] ) : 0;
}

function digipublish_core_gallery_cover_image_markup( $post_id, $size = 'medium_large', $sizes = '' ) {
	$image_id = digipublish_core_get_gallery_cover_image_id( $post_id );
	if ( ! $image_id ) {
		return '<span class="tp-image-placeholder" aria-hidden="true"></span>';
	}

	$attrs = array(
		'alt'      => trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ),
		'loading'  => 'lazy',
		'decoding' => 'async',
	);
	if ( $sizes ) {
		$attrs['sizes'] = $sizes;
	}

	return wp_get_attachment_image( $image_id, $size, false, $attrs );
}

/**
 * Render a single server-side gallery slide.
 */
function digipublish_core_render_gallery_slide( $attributes, $index = 1, $total = 1, $settings = array() ) {
	$image_id      = absint( $attributes['imageId'] ?? 0 );
	$image_url     = esc_url( (string) ( $attributes['imageUrl'] ?? '' ) );
	$heading       = trim( wp_strip_all_tags( (string) ( $attributes['heading'] ?? '' ) ) );
	$caption       = (string) ( $attributes['caption'] ?? '' );
	$credit        = trim( wp_strip_all_tags( (string) ( $attributes['credit'] ?? '' ) ) );
	$alt           = trim( wp_strip_all_tags( (string) ( $attributes['alt'] ?? '' ) ) );
	$show_counter  = ! empty( $settings['showCounter'] );
	$show_captions = ! isset( $settings['showCaptions'] ) || ! empty( $settings['showCaptions'] );
	$show_credits  = ! isset( $settings['showCredits'] ) || ! empty( $settings['showCredits'] );
	$priority      = 1 === (int) $index;

	if ( ! $alt && $image_id ) {
		$alt = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
	}
	if ( ! $alt ) {
		$alt = $heading ?: wp_strip_all_tags( $caption );
	}

	$image = '';
	if ( $image_id ) {
		$image = wp_get_attachment_image(
			$image_id,
			'large',
			false,
			array(
				'alt'           => $alt,
				'loading'       => $priority ? 'eager' : 'lazy',
				'fetchpriority' => $priority ? 'high' : 'auto',
				'decoding'      => 'async',
				'sizes'         => '(max-width: 680px) 100vw, (max-width: 1120px) 86vw, 860px',
			)
		);
	} elseif ( $image_url ) {
		$image = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt ) . '" loading="' . ( $priority ? 'eager' : 'lazy' ) . '" decoding="async"' . ( $priority ? ' fetchpriority="high"' : '' ) . '>';
	}

	if ( ! $image ) {
		return '';
	}

	$slide_id = 'gallery-slide-' . max( 1, absint( $index ) );
	$html     = '<article id="' . esc_attr( $slide_id ) . '" class="tp-gallery-slide" data-gallery-slide data-gallery-index="' . esc_attr( (string) $index ) . '">';

	if ( $show_counter ) {
		$html .= '<div class="tp-gallery-slide__counter" aria-label="' . esc_attr( sprintf( __( 'Photo %1$d of %2$d', 'digipublish-core' ), $index, $total ) ) . '">' . esc_html( $index . ' / ' . $total ) . '</div>';
	}

	$html .= '<figure class="tp-gallery-slide__figure"><div class="tp-gallery-slide__media">' . $image . '</div>';

	$has_caption = $show_captions && ( $heading || $caption );
	$has_credit  = $show_credits && $credit;
	if ( $has_caption || $has_credit ) {
		$html .= '<figcaption class="tp-gallery-slide__caption">';
		if ( $show_captions && $heading ) {
			$html .= '<h2>' . esc_html( $heading ) . '</h2>';
		}
		if ( $show_captions && $caption ) {
			$html .= '<div class="tp-gallery-slide__text">' . wp_kses_post( wpautop( $caption ) ) . '</div>';
		}
		if ( $has_credit ) {
			$html .= '<p class="tp-gallery-slide__credit">' . esc_html( sprintf( __( 'Photo: %s', 'digipublish-core' ), $credit ) ) . '</p>';
		}
		$html .= '</figcaption>';
	}

	$html .= '</figure></article>';
	return $html;
}

/**
 * Cached category list restricted to published galleries.
 */
function digipublish_core_get_gallery_categories( $limit = 10 ) {
	$limit = max( 1, min( 30, absint( $limit ) ) );
	$key   = 'v' . techpress_editorial_cache_version() . '_gallery_categories_' . $limit;
	$terms = wp_cache_get( $key, 'techpress_editorial' );

	if ( false !== $terms ) {
		return $terms;
	}

	$ids = get_posts(
		array(
			'post_type'              => 'digipublish_gallery',
			'post_status'            => 'publish',
			'posts_per_page'         => 200,
			'fields'                 => 'ids',
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $ids ) {
		wp_cache_set( $key, array(), 'techpress_editorial', HOUR_IN_SECONDS );
		return array();
	}

	$terms = wp_get_object_terms(
		$ids,
		'category',
		array(
			'orderby' => 'count',
			'order'   => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	$terms = array_slice( $terms, 0, $limit );
	wp_cache_set( $key, $terms, 'techpress_editorial', HOUR_IN_SECONDS );
	return $terms;
}

/**
 * Refresh rewrite rules once when gallery routing changes between releases.
 */
function digipublish_core_maybe_refresh_gallery_rewrites() {
	$version = (string) get_option( 'digipublish_gallery_rewrite_version', '' );
	if ( DIGIPUBLISH_CORE_VERSION === $version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'digipublish_gallery_rewrite_version', DIGIPUBLISH_CORE_VERSION, false );
}
add_action( 'init', 'digipublish_core_maybe_refresh_gallery_rewrites', 99 );

/**
 * Lightweight ImageGallery structured data. The canonical URL remains the one
 * gallery URL; slides use anchors rather than creating thin paginated URLs.
 */
function digipublish_core_gallery_schema() {
	if ( ! is_singular( 'digipublish_gallery' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	$slides  = digipublish_core_get_gallery_slides( $post_id );
	if ( ! $slides ) {
		return;
	}

	$media = array();
	foreach ( $slides as $slide ) {
		$image_id = absint( $slide['imageId'] ?? 0 );
		$url      = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : esc_url_raw( (string) ( $slide['imageUrl'] ?? '' ) );
		if ( ! $url ) {
			continue;
		}

		$item = array(
			'@type'      => 'ImageObject',
			'contentUrl' => $url,
		);

		$caption = trim( wp_strip_all_tags( (string) ( $slide['caption'] ?? '' ) ) );
		$credit  = trim( wp_strip_all_tags( (string) ( $slide['credit'] ?? '' ) ) );
		if ( $caption ) {
			$item['caption'] = $caption;
		}
		if ( $credit ) {
			$item['creditText'] = $credit;
		}
		$media[] = $item;
	}

	if ( ! $media ) {
		return;
	}

	$author_id = (int) get_post_field( 'post_author', $post_id );
	$schema    = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'ImageGallery',
		'@id'              => get_permalink( $post_id ) . '#image-gallery',
		'url'              => get_permalink( $post_id ),
		'name'             => get_the_title( $post_id ),
		'description'      => (string) get_post_field( 'post_excerpt', $post_id ),
		'datePublished'    => get_the_date( DATE_W3C, $post_id ),
		'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
		),
		'associatedMedia'  => $media,
		'mainEntityOfPage' => get_permalink( $post_id ),
	);

	echo "
<script type="application/ld+json">" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>
";
}
add_action( 'wp_head', 'digipublish_core_gallery_schema', 30 );
