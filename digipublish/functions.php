<?php
/**
 * DigiPublish Editorial theme functions.
 *
 * @package DigiPublish_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup.
 */
function digipublish_theme_setup() {
	register_block_pattern_category(
		'digipublish',
		array( 'label' => __( 'DigiPublish Editorial', 'digipublish' ) )
	);

	// Marker used by the companion plugin for framework-specific optimizations.
	add_theme_support( 'digipublish-performance' );
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'digipublish_theme_setup' );


/**
 * Register DigiPublish visual treatments on WordPress Core layout blocks.
 *
 * New editorial composition uses Core blocks. The legacy DigiPublish section
 * block family remains registered only so existing content continues to edit
 * and render without migration.
 */
function digipublish_register_core_layout_styles() {
	register_block_style(
		'core/columns',
		array(
			'name'  => 'digipublish-editorial-section',
			'label' => __( 'DigiPublish Editorial Section', 'digipublish' ),
		)
	);

	register_block_style(
		'core/heading',
		array(
			'name'  => 'digipublish-accent',
			'label' => __( 'DigiPublish Accent', 'digipublish' ),
		)
	);

	register_block_style(
		'core/heading',
		array(
			'name'  => 'digipublish-accent-box',
			'label' => __( 'DigiPublish Accent Box', 'digipublish' ),
		)
	);

	$section_path = get_theme_file_path( 'assets/css/core-section.css' );
	if ( file_exists( $section_path ) ) {
		wp_enqueue_block_style(
			'core/columns',
			array(
				'handle' => 'digipublish-core-section',
				'src'    => get_theme_file_uri( 'assets/css/core-section.css' ),
				'path'   => $section_path,
				'ver'    => (string) filemtime( $section_path ),
			)
		);
	}

	$heading_path = get_theme_file_path( 'assets/css/core-heading.css' );
	if ( file_exists( $heading_path ) ) {
		wp_enqueue_block_style(
			'core/heading',
			array(
				'handle' => 'digipublish-core-heading',
				'src'    => get_theme_file_uri( 'assets/css/core-heading.css' ),
				'path'   => $heading_path,
				'ver'    => (string) filemtime( $heading_path ),
			)
		);
	}
}
add_action( 'init', 'digipublish_register_core_layout_styles', 30 );

/**
 * Load only the small theme shell stylesheet on the front end.
 * Custom block CSS is registered per block by the companion plugin and is
 * therefore loaded only when that block appears on the page.
 */
function digipublish_enqueue_theme_assets() {
	$path = get_theme_file_path( 'assets/css/site.css' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'digipublish-site',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		$ver
	);
}
add_action( 'wp_enqueue_scripts', 'digipublish_enqueue_theme_assets' );

/**
 * Keep the full theme shell available in the Site Editor.
 */
function digipublish_enqueue_editor_assets() {
	$path = get_theme_file_path( 'assets/css/site.css' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'digipublish-editor',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		$ver
	);
}
add_action( 'enqueue_block_editor_assets', 'digipublish_enqueue_editor_assets' );

/**
 * Ask modern WordPress to enqueue block styles only for blocks that are
 * actually rendered. This is safe for a block theme and substantially lowers
 * CSS transfer on simpler pages.
 */
function digipublish_load_block_assets_on_demand( $load_on_demand ) {
	if ( is_admin() ) {
		return $load_on_demand;
	}
	return true;
}
add_filter( 'should_load_block_assets_on_demand', 'digipublish_load_block_assets_on_demand' );

/**
 * Page-type classes used by the lean theme stylesheet.
 */
function digipublish_theme_body_classes( $classes ) {
	$classes[] = 'digipublish-site';
	if ( is_singular( 'post' ) ) {
		$classes[] = 'digipublish-article';
	}
	if ( is_singular( 'digipublish_gallery' ) ) {
		$classes[] = 'digipublish-gallery';
		$classes[] = 'digipublish-article';
	}
	if ( is_category() ) {
		$classes[] = 'digipublish-category';
	}
	if ( is_tag() ) {
		$classes[] = 'digipublish-tag';
	}
	if ( is_author() ) {
		$classes[] = 'digipublish-author';
	}
	if ( is_archive() ) {
		$classes[] = 'digipublish-archive';
	}
	return $classes;
}
add_filter( 'body_class', 'digipublish_theme_body_classes' );

/**
 * Typography is intentionally dependency-free.
 *
 * DigiPublish does not fetch fonts from third-party CDNs. Site owners can use
 * WordPress' native Font Library to install/manage locally hosted fonts while
 * the theme itself ships with resilient system font stacks.
 */

/**
 * DigiPublish site interaction controller.
 *
 * Some interaction semantics originate from the earlier Caards parity phase;
 * the runtime is first-party, dependency-free and owned by DigiPublish.
 */
function digipublish_enqueue_site_interactions() {
	$path = get_theme_file_path( 'assets/js/site-interactions.js' );
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'digipublish-site-interactions',
		get_theme_file_uri( 'assets/js/site-interactions.js' ),
		array(),
		(string) filemtime( $path ),
		true
	);

	$config = array(
		'loadNext' => array(
			'enabled' => false,
		),
	);
	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$config['loadNext'] = array(
			'enabled' => digipublish_load_next_enabled( $post_id ),
			'postId'  => $post_id,
			'restUrl' => esc_url_raw( rest_url( 'digipublish/v1/load-next-post' ) ),
		);
	}
	wp_localize_script( 'digipublish-site-interactions', 'digiPublishSite', $config );
}
add_action( 'wp_enqueue_scripts', 'digipublish_enqueue_site_interactions', 30 );

/**
 * Per-post/page layout settings equivalent to the Caards editor layout panel.
 */
function digipublish_register_singular_meta() {
	$schema = array(
		'digipublish_singular_sidebar' => array( 'type' => 'string', 'default' => 'default' ),
		'digipublish_page_header_type' => array( 'type' => 'string', 'default' => 'default' ),
		'digipublish_load_nextpost'    => array( 'type' => 'string', 'default' => 'default' ),
		'digipublish_post_video_url'   => array( 'type' => 'string', 'default' => '' ),
		'digipublish_post_video_location' => array( 'type' => 'array', 'default' => array() ),
	);
	foreach ( array( 'post', 'page' ) as $post_type ) {
		foreach ( $schema as $key => $config ) {
			$args = array(
				'show_in_rest'  => true,
				'type'          => $config['type'],
				'single'        => true,
				'default'       => $config['default'],
				'auth_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
			);
			if ( 'array' === $config['type'] ) {
				$args['show_in_rest'] = array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				);
			}
			register_post_meta( $post_type, $key, $args );
		}
	}
}
add_action( 'init', 'digipublish_register_singular_meta', 15 );

/**
 * Expose selected singular layout choices to the theme shell.
 */
function digipublish_singular_body_classes( $classes ) {
	$classes[] = 'digipublish-shell';
	$classes[] = 'dp-caards-shell';
	if ( is_singular( array( 'post', 'page' ) ) ) {
		$post_id = get_queried_object_id();
		$sidebar = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_singular_sidebar', true ) );
		if ( ! in_array( $sidebar, array( 'left', 'right', 'disabled' ), true ) ) {
			$sidebar = sanitize_key( (string) digipublish_legacy_layout_option( 'default_sidebar', 'right' ) );
		}
		if ( ! in_array( $sidebar, array( 'left', 'right', 'disabled' ), true ) ) {
			$sidebar = 'right';
		}
		$header = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_page_header_type', true ) );
		if ( ! in_array( $header, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
			$header = sanitize_key( (string) digipublish_legacy_layout_option( 'default_header', 'standard' ) );
		}
		if ( ! in_array( $header, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
			$header = 'standard';
		}
		$classes[] = 'dp-sidebar-' . $sidebar;
		$classes[] = 'dp-entry-header-' . $header;
	}
	return $classes;
}
add_filter( 'body_class', 'digipublish_singular_body_classes', 30 );

/**
 * Safe helper for theme/plugin renderers.
 */
function digipublish_singular_setting( $post_id, $key, $fallback = '' ) {
	$value = get_post_meta( absint( $post_id ), $key, true );
	if ( '' === $value || null === $value || 'default' === $value ) {
		return $fallback;
	}
	return $value;
}


/**
 * Resolve the Caards-style Auto Load Next Post state.
 */
function digipublish_load_next_enabled( $post_id ) {
	$value = sanitize_key( (string) get_post_meta( absint( $post_id ), 'digipublish_load_nextpost', true ) );
	if ( 'enabled' === $value ) {
		return true;
	}
	if ( 'disabled' === $value ) {
		return false;
	}
	return (bool) get_option( 'digipublish_load_next_enabled', false );
}

/**
 * Find the adjacent post using the Caards direction/category semantics.
 */
function digipublish_adjacent_post_id( $post_id, $exclude = array() ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 0;
	}

	$exclude = array_values( array_unique( array_filter( array_map( 'absint', (array) $exclude ) ) ) );
	$same_category = (bool) get_option( 'digipublish_load_next_same_category', false );
	$reverse       = (bool) get_option( 'digipublish_load_next_reverse', false );

	global $post;
	$original_post = $post;
	$cursor = get_post( $post_id );
	$found  = 0;
	$guard  = 0;

	while ( $cursor && $guard < 50 ) {
		$guard++;
		$post = $cursor;
		setup_postdata( $post );
		$adjacent = $reverse ? get_previous_post( $same_category ) : get_next_post( $same_category );
		if ( ! $adjacent || empty( $adjacent->ID ) ) {
			break;
		}
		$cursor = get_post( $adjacent->ID );
		if ( $cursor && ! in_array( (int) $cursor->ID, $exclude, true ) ) {
			$found = (int) $cursor->ID;
			break;
		}
	}

	wp_reset_postdata();
	$post = $original_post;
	if ( $original_post instanceof WP_Post ) {
		setup_postdata( $original_post );
	}
	return $found;
}

/**
 * Render the article fragment used by Auto Load Next Post.
 */
function digipublish_render_next_article( $post_id ) {
	$post_id = absint( $post_id );
	$loaded  = get_post( $post_id );
	if ( ! $loaded || 'post' !== $loaded->post_type || 'publish' !== $loaded->post_status ) {
		return '';
	}

	global $post;
	$original_post = $post;
	$post = $loaded;
	setup_postdata( $post );

	$header = do_blocks( '<!-- wp:digipublish/entry-hero {"layout":"auto","showBreadcrumbs":true,"showSubtitle":true} /-->' );
	$content = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
	$tags = get_the_tag_list( '<div class="dp-caards-tags">' . esc_html__( 'Tags:', 'digipublish' ) . ' ', ' ', '</div>', $post_id );
	$author = do_blocks( '<!-- wp:digipublish/post-author-card /-->' );
	$related = do_blocks( '<!-- wp:digipublish/related-posts {"heading":"Read next","postsToShow":4,"layout":"read-next","relationMode":"category-tags","showExcerpt":true} /-->' );

	$sidebar_setting = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_singular_sidebar', true ) );
	if ( ! in_array( $sidebar_setting, array( 'left', 'right', 'disabled' ), true ) ) {
		$sidebar_setting = sanitize_key( (string) digipublish_legacy_layout_option( 'default_sidebar', 'right' ) );
	}
	if ( ! in_array( $sidebar_setting, array( 'left', 'right', 'disabled' ), true ) ) {
		$sidebar_setting = 'right';
	}
	$sidebar = '';
	if ( 'disabled' !== $sidebar_setting && function_exists( 'block_template_part' ) ) {
		ob_start();
		block_template_part( 'sidebar' );
		$sidebar = ob_get_clean();
	}

	$classes = 'dp-caards-nextpost-section dp-sidebar-' . $sidebar_setting;
	$html = '<section class="' . esc_attr( $classes ) . '" data-dp-nextpost-section data-title="' . esc_attr( get_the_title( $post_id ) ) . '" data-url="' . esc_url( get_permalink( $post_id ) ) . '" data-post-id="' . $post_id . '">';
	$html .= $header;
	$html .= '<div class="dp-caards-article-layout alignwide"><div class="dp-caards-article-main"><div class="wp-block-post-content">' . $content . '</div>' . ( $tags ?: '' ) . $author . '</div>' . $sidebar . '</div>';
	$html .= $related;
	$html .= '</section>';

	wp_reset_postdata();
	$post = $original_post;
	if ( $original_post instanceof WP_Post ) {
		setup_postdata( $original_post );
	}

	return $html;
}

/**
 * Public REST endpoint for Auto Load Next Post.
 */
function digipublish_register_load_next_route() {
	register_rest_route(
		'digipublish/v1',
		'/load-next-post',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => static function ( WP_REST_Request $request ) {
				$params  = $request->get_json_params();
				$current = absint( $params['postId'] ?? 0 );
				$exclude = isset( $params['exclude'] ) && is_array( $params['exclude'] ) ? $params['exclude'] : array();
				$next_id = digipublish_adjacent_post_id( $current, $exclude );
				if ( ! $next_id ) {
					return rest_ensure_response( array( 'end' => true, 'content' => '' ) );
				}
				$content = digipublish_render_next_article( $next_id );
				return rest_ensure_response(
					array(
						'end'     => '' === $content,
						'postId'  => $next_id,
						'url'     => get_permalink( $next_id ),
						'title'   => get_the_title( $next_id ),
						'content' => $content,
					)
				);
			},
		)
	);
}
add_action( 'rest_api_init', 'digipublish_register_load_next_route' );

/**
 * Migrate non-visual publishing behavior settings to canonical DigiPublish keys.
 *
 * Old option names are read once and copied forward. Historical visual-layout
 * options are intentionally not migrated into new theme settings because the
 * Site Editor owns visual composition.
 */
function digipublish_migrate_publishing_settings() {
	if ( get_option( 'digipublish_publishing_settings_migrated_100', false ) ) {
		return;
	}

	$map = array(
		'digipublish_caards_load_nextpost_enabled'       => 'digipublish_load_next_enabled',
		'digipublish_caards_load_nextpost_same_category' => 'digipublish_load_next_same_category',
		'digipublish_caards_load_nextpost_reverse'       => 'digipublish_load_next_reverse',
	);

	foreach ( $map as $legacy => $canonical ) {
		if ( false !== get_option( $canonical, false ) ) {
			continue;
		}
		$value = get_option( $legacy, null );
		if ( null !== $value ) {
			update_option( $canonical, (bool) $value, false );
		}
	}

	update_option( 'digipublish_publishing_settings_migrated_100', true, false );
}
add_action( 'admin_init', 'digipublish_migrate_publishing_settings', 5 );

/**
 * Register non-visual publishing behavior settings.
 *
 * Colors, typography, templates, headers, footers and layout composition are
 * owned by theme.json and the Site Editor.
 */
function digipublish_register_publishing_settings() {
	foreach ( array(
		'digipublish_load_next_enabled',
		'digipublish_load_next_same_category',
		'digipublish_load_next_reverse',
	) as $option ) {
		register_setting(
			'digipublish_publishing',
			$option,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static function ( $value ) { return (bool) $value; },
			)
		);
	}
}
add_action( 'admin_init', 'digipublish_register_publishing_settings' );

/**
 * Non-visual publishing behavior page.
 */
function digipublish_add_publishing_settings_page() {
	add_theme_page(
		__( 'DigiPublish Publishing', 'digipublish' ),
		__( 'DigiPublish Publishing', 'digipublish' ),
		'edit_theme_options',
		'digipublish-publishing',
		'digipublish_render_publishing_settings_page'
	);
}
add_action( 'admin_menu', 'digipublish_add_publishing_settings_page' );

function digipublish_render_publishing_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'DigiPublish Publishing', 'digipublish' ); ?></h1>
		<p>
			<?php esc_html_e( 'Visual design, headers, footers, templates, colors and typography are managed in the Site Editor. This page contains publishing behavior that is not a visual design setting.', 'digipublish' ); ?>
			<a href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>"><?php esc_html_e( 'Open Site Editor', 'digipublish' ); ?></a>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'digipublish_publishing' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto Load Next Post', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_load_next_enabled" value="0"><label><input type="checkbox" name="digipublish_load_next_enabled" value="1" <?php checked( get_option( 'digipublish_load_next_enabled', false ) ); ?>> <?php esc_html_e( 'Enable globally (individual posts can override this)', 'digipublish' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Same category only', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_load_next_same_category" value="0"><label><input type="checkbox" name="digipublish_load_next_same_category" value="1" <?php checked( get_option( 'digipublish_load_next_same_category', false ) ); ?>> <?php esc_html_e( 'Only auto-load adjacent posts from the same category', 'digipublish' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Reverse direction', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_load_next_reverse" value="0"><label><input type="checkbox" name="digipublish_load_next_reverse" value="1" <?php checked( get_option( 'digipublish_load_next_reverse', false ) ); ?>> <?php esc_html_e( 'Load previous posts instead of next posts', 'digipublish' ); ?></label></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// Legacy callable aliases are isolated from the production namespace.
require_once get_theme_file_path( 'inc/compatibility.php' );
