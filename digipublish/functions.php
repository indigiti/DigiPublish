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
function techpress_theme_setup() {
	register_block_pattern_category(
		'digipublish',
		array( 'label' => __( 'DigiPublish Editorial', 'digipublish' ) )
	);

	// Marker used by the companion plugin for framework-specific optimizations.
	add_theme_support( 'digipublish-performance' );
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'techpress_theme_setup' );

/**
 * Load only the small theme shell stylesheet on the front end.
 * Custom block CSS is registered per block by the companion plugin and is
 * therefore loaded only when that block appears on the page.
 */
function techpress_theme_enqueue_assets() {
	$path = get_theme_file_path( 'assets/css/site.css' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'digipublish-site',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		$ver
	);
}
add_action( 'wp_enqueue_scripts', 'techpress_theme_enqueue_assets' );

/**
 * Keep the full theme shell available in the Site Editor.
 */
function techpress_theme_editor_assets() {
	$path = get_theme_file_path( 'assets/css/site.css' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'digipublish-editor',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		$ver
	);
}
add_action( 'enqueue_block_editor_assets', 'techpress_theme_editor_assets' );

/**
 * Ask modern WordPress to enqueue block styles only for blocks that are
 * actually rendered. This is safe for a block theme and substantially lowers
 * CSS transfer on simpler pages.
 */
function techpress_theme_load_block_assets_on_demand( $load_on_demand ) {
	if ( is_admin() ) {
		return $load_on_demand;
	}
	return true;
}
add_filter( 'should_load_block_assets_on_demand', 'techpress_theme_load_block_assets_on_demand' );

/**
 * Page-type classes used by the lean theme stylesheet.
 */
function techpress_theme_body_classes( $classes ) {
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
add_filter( 'body_class', 'techpress_theme_body_classes' );

/**
 * Optional Google Fonts integration.
 * Disabled by default for maximum performance; administrators can enable it
 * from Appearance > DigiPublish Typography.
 */
function techpress_theme_google_font_choices() {
	return array(
		'Inter'         => 'Inter',
		'Roboto'        => 'Roboto',
		'Open Sans'     => 'Open Sans',
		'Lato'          => 'Lato',
		'Poppins'       => 'Poppins',
		'Source Sans 3' => 'Source Sans 3',
		'Manrope'       => 'Manrope',
	);
}

function techpress_theme_register_typography_settings() {
	register_setting(
		'techpress_typography',
		'techpress_google_fonts_enabled',
		array(
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => static function ( $value ) { return (bool) $value; },
		)
	);
	register_setting(
		'techpress_typography',
		'techpress_google_font_family',
		array(
			'type'              => 'string',
			'default'           => 'Manrope',
			'sanitize_callback' => static function ( $value ) {
				$choices = techpress_theme_google_font_choices();
				return isset( $choices[ $value ] ) ? $value : 'Manrope';
			},
		)
	);
}
add_action( 'admin_init', 'techpress_theme_register_typography_settings' );

function techpress_theme_add_typography_page() {
	add_theme_page(
		__( 'DigiPublish Typography', 'digipublish' ),
		__( 'DigiPublish Typography', 'digipublish' ),
		'edit_theme_options',
		'techpress-typography',
		'techpress_theme_render_typography_page'
	);
}
add_action( 'admin_menu', 'techpress_theme_add_typography_page' );

function techpress_theme_render_typography_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$enabled = (bool) get_option( 'techpress_google_fonts_enabled', false );
	$family  = (string) get_option( 'techpress_google_font_family', 'Manrope' );
	$choices = techpress_theme_google_font_choices();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'DigiPublish Typography', 'digipublish' ); ?></h1>
		<p><?php esc_html_e( 'Google Fonts are optional. Keeping this disabled gives the fastest network path. When enabled, the selected family is applied site-wide, including the block editor preview.', 'digipublish' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'techpress_typography' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Google Fonts', 'digipublish' ); ?></th>
					<td><input type="hidden" name="techpress_google_fonts_enabled" value="0"><label><input type="checkbox" name="techpress_google_fonts_enabled" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Load the selected family from Google Fonts', 'digipublish' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><label for="techpress_google_font_family"><?php esc_html_e( 'Font family', 'digipublish' ); ?></label></th>
					<td><select id="techpress_google_font_family" name="techpress_google_font_family">
						<?php foreach ( $choices as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $family, $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function techpress_theme_google_font_enabled() {
	return (bool) get_option( 'techpress_google_fonts_enabled', false );
}

function techpress_theme_google_font_family() {
	$family  = (string) get_option( 'techpress_google_font_family', 'Manrope' );
	$choices = techpress_theme_google_font_choices();
	return isset( $choices[ $family ] ) ? $family : 'Manrope';
}

function techpress_theme_enqueue_google_font() {
	if ( ! techpress_theme_google_font_enabled() ) {
		return;
	}
	$family = techpress_theme_google_font_family();
	$query  = str_replace( '%20', '+', rawurlencode( $family ) );
	$url    = 'https://fonts.googleapis.com/css2?family=' . $query . ':wght@400;500;600;700;800&display=swap';
	wp_enqueue_style( 'techpress-google-font', $url, array(), null );
}
add_action( 'enqueue_block_assets', 'techpress_theme_enqueue_google_font', 5 );

function techpress_theme_google_font_css() {
	if ( ! techpress_theme_google_font_enabled() ) {
		return '';
	}
	$family = techpress_theme_google_font_family();
	$stack  = "'" . esc_attr( $family ) . "', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif";
	return ':root{--tp-google-font:' . $stack . ';--wp--preset--font-family--sans:var(--tp-google-font);--wp--preset--font-family--reading:var(--tp-google-font)}body.dp-caards-shell,body.dp-caards-shell .wp-site-blocks,body.dp-caards-shell .wp-site-blocks p,body.dp-caards-shell .wp-site-blocks li,body.dp-caards-shell .wp-site-blocks input,body.dp-caards-shell .wp-site-blocks textarea,body.dp-caards-shell .wp-site-blocks select,body.dp-caards-shell .wp-site-blocks button{font-family:var(--tp-google-font)!important}';
}

function techpress_theme_frontend_google_font_override() {
	$css = techpress_theme_google_font_css();
	if ( $css ) {
		wp_add_inline_style( 'digipublish-site', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'techpress_theme_frontend_google_font_override', 20 );

function techpress_theme_editor_google_font_override() {
	$css = techpress_theme_google_font_css();
	if ( $css ) {
		wp_add_inline_style( 'digipublish-editor', $css );
	}
}
add_action( 'enqueue_block_editor_assets', 'techpress_theme_editor_google_font_override', 20 );

function techpress_theme_google_font_resource_hints( $urls, $relation_type ) {
	if ( ! techpress_theme_google_font_enabled() || 'preconnect' !== $relation_type ) {
		return $urls;
	}
	$urls[] = 'https://fonts.googleapis.com';
	$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	return $urls;
}
add_filter( 'wp_resource_hints', 'techpress_theme_google_font_resource_hints', 10, 2 );


/**
 * Caards-derived theme shell.
 *
 * Header/footer/template concepts are adapted from Caards 1.0.4 by Code Supply
 * Co. (GPL-3.0) and implemented as native DigiPublish block-theme structures.
 */
function digipublish_caards_enqueue_shell_script() {
	$path = get_theme_file_path( 'assets/js/caards-shell.js' );
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'digipublish-caards-shell',
		get_theme_file_uri( 'assets/js/caards-shell.js' ),
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
			'enabled' => digipublish_caards_load_next_enabled( $post_id ),
			'postId'  => $post_id,
			'restUrl' => esc_url_raw( rest_url( 'digipublish/v1/load-next-post' ) ),
		);
	}
	wp_localize_script( 'digipublish-caards-shell', 'digiPublishCaards', $config );
}
add_action( 'wp_enqueue_scripts', 'digipublish_caards_enqueue_shell_script', 30 );

/**
 * Per-post/page layout settings equivalent to the Caards editor layout panel.
 */
function digipublish_caards_register_singular_meta() {
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
add_action( 'init', 'digipublish_caards_register_singular_meta', 15 );

/**
 * Expose selected singular layout choices to the theme shell.
 */
function digipublish_caards_body_classes( $classes ) {
	$classes[] = 'dp-caards-shell';
	if ( is_singular( array( 'post', 'page' ) ) ) {
		$post_id = get_queried_object_id();
		$sidebar = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_singular_sidebar', true ) );
		if ( ! in_array( $sidebar, array( 'left', 'right', 'disabled' ), true ) ) {
			$sidebar = sanitize_key( (string) get_option( 'digipublish_caards_default_sidebar', 'right' ) );
		}
		if ( ! in_array( $sidebar, array( 'left', 'right', 'disabled' ), true ) ) {
			$sidebar = 'right';
		}
		$header = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_page_header_type', true ) );
		if ( ! in_array( $header, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
			$header = sanitize_key( (string) get_option( 'digipublish_caards_default_header', 'standard' ) );
		}
		if ( ! in_array( $header, array( 'standard', 'large', 'full', 'title', 'none' ), true ) ) {
			$header = 'standard';
		}
		$classes[] = 'dp-sidebar-' . $sidebar;
		$classes[] = 'dp-entry-header-' . $header;
	}
	return $classes;
}
add_filter( 'body_class', 'digipublish_caards_body_classes', 30 );

/**
 * Safe helper for theme/plugin renderers.
 */
function digipublish_caards_singular_setting( $post_id, $key, $fallback = '' ) {
	$value = get_post_meta( absint( $post_id ), $key, true );
	if ( '' === $value || null === $value || 'default' === $value ) {
		return $fallback;
	}
	return $value;
}


/**
 * Resolve the Caards-style Auto Load Next Post state.
 */
function digipublish_caards_load_next_enabled( $post_id ) {
	$value = sanitize_key( (string) get_post_meta( absint( $post_id ), 'digipublish_load_nextpost', true ) );
	if ( 'enabled' === $value ) {
		return true;
	}
	if ( 'disabled' === $value ) {
		return false;
	}
	return (bool) get_option( 'digipublish_caards_load_nextpost_enabled', false );
}

/**
 * Find the adjacent post using the Caards direction/category semantics.
 */
function digipublish_caards_adjacent_post_id( $post_id, $exclude = array() ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 0;
	}

	$exclude = array_values( array_unique( array_filter( array_map( 'absint', (array) $exclude ) ) ) );
	$same_category = (bool) get_option( 'digipublish_caards_load_nextpost_same_category', false );
	$reverse       = (bool) get_option( 'digipublish_caards_load_nextpost_reverse', false );

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
function digipublish_caards_render_next_article( $post_id ) {
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
		$sidebar_setting = sanitize_key( (string) get_option( 'digipublish_caards_default_sidebar', 'right' ) );
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
function digipublish_caards_register_load_next_route() {
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
				$next_id = digipublish_caards_adjacent_post_id( $current, $exclude );
				if ( ! $next_id ) {
					return rest_ensure_response( array( 'end' => true, 'content' => '' ) );
				}
				$content = digipublish_caards_render_next_article( $next_id );
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
add_action( 'rest_api_init', 'digipublish_caards_register_load_next_route' );

/**
 * Appearance settings for Caards Auto Load Next Post behavior.
 */
function digipublish_caards_register_settings() {
	foreach ( array(
		'digipublish_caards_load_nextpost_enabled',
		'digipublish_caards_load_nextpost_same_category',
		'digipublish_caards_load_nextpost_reverse',
	) as $option ) {
		register_setting(
			'digipublish_caards',
			$option,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static function ( $value ) { return (bool) $value; },
			)
		);
	}

	$string_settings = array(
		'digipublish_caards_header_variant' => array( 'default' => 'one', 'allowed' => array( 'one', 'two', 'three', 'four' ) ),
		'digipublish_caards_footer_variant' => array( 'default' => 'one', 'allowed' => array( 'one', 'two', 'three', 'four' ) ),
		'digipublish_caards_default_header'  => array( 'default' => 'standard', 'allowed' => array( 'standard', 'large', 'full', 'title', 'none' ) ),
		'digipublish_caards_default_sidebar' => array( 'default' => 'right', 'allowed' => array( 'right', 'left', 'disabled' ) ),
	);
	foreach ( $string_settings as $option => $config ) {
		register_setting(
			'digipublish_caards',
			$option,
			array(
				'type'              => 'string',
				'default'           => $config['default'],
				'sanitize_callback' => static function ( $value ) use ( $config ) {
					$value = sanitize_key( (string) $value );
					return in_array( $value, $config['allowed'], true ) ? $value : $config['default'];
				},
			)
		);
	}
}
add_action( 'admin_init', 'digipublish_caards_register_settings' );

/**
 * Route the default FSE header/footer template-parts to the selected Caards
 * variant while leaving explicitly selected variant parts untouched.
 */
function digipublish_caards_route_template_parts( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/template-part' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}
	$slug = isset( $parsed_block['attrs']['slug'] ) ? sanitize_key( (string) $parsed_block['attrs']['slug'] ) : '';
	if ( 'header' === $slug ) {
		$variant = sanitize_key( (string) get_option( 'digipublish_caards_header_variant', 'one' ) );
		if ( ! in_array( $variant, array( 'one', 'two', 'three', 'four' ), true ) ) {
			$variant = 'one';
		}
		$parsed_block['attrs']['slug'] = 'header-' . $variant;
	} elseif ( 'footer' === $slug ) {
		$variant = sanitize_key( (string) get_option( 'digipublish_caards_footer_variant', 'one' ) );
		if ( ! in_array( $variant, array( 'one', 'two', 'three', 'four' ), true ) ) {
			$variant = 'one';
		}
		$parsed_block['attrs']['slug'] = 'footer-' . $variant;
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'digipublish_caards_route_template_parts', 15 );

function digipublish_caards_add_settings_page() {
	add_theme_page(
		__( 'DigiPublish Caards', 'digipublish' ),
		__( 'DigiPublish Caards', 'digipublish' ),
		'edit_theme_options',
		'digipublish-caards',
		'digipublish_caards_render_settings_page'
	);
}
add_action( 'admin_menu', 'digipublish_caards_add_settings_page' );

function digipublish_caards_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'DigiPublish Caards', 'digipublish' ); ?></h1>
		<p><?php esc_html_e( 'Header and footer variants are editable in Appearance → Editor → Design → Patterns. These settings control Caards-compatible article behavior.', 'digipublish' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'digipublish_caards' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="digipublish_caards_header_variant"><?php esc_html_e( 'Header layout', 'digipublish' ); ?></label></th>
					<td><select id="digipublish_caards_header_variant" name="digipublish_caards_header_variant">
						<?php foreach ( array( 'one' => 'Header 1', 'two' => 'Header 2', 'three' => 'Header 3', 'four' => 'Header 4' ) as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'digipublish_caards_header_variant', 'one' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select><p class="description"><?php esc_html_e( 'Maps the default header template-part to one of the four Caards layouts.', 'digipublish' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="digipublish_caards_footer_variant"><?php esc_html_e( 'Footer layout', 'digipublish' ); ?></label></th>
					<td><select id="digipublish_caards_footer_variant" name="digipublish_caards_footer_variant">
						<?php foreach ( array( 'one' => 'Footer 1', 'two' => 'Footer 2', 'three' => 'Footer 3', 'four' => 'Footer 4' ) as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'digipublish_caards_footer_variant', 'one' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
				<tr>
					<th scope="row"><label for="digipublish_caards_default_header"><?php esc_html_e( 'Default post/page header', 'digipublish' ); ?></label></th>
					<td><select id="digipublish_caards_default_header" name="digipublish_caards_default_header">
						<?php foreach ( array( 'standard' => 'Standard', 'large' => 'Large Hero', 'full' => 'Full Hero', 'title' => 'Title Only', 'none' => 'No Header' ) as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'digipublish_caards_default_header', 'standard' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
				<tr>
					<th scope="row"><label for="digipublish_caards_default_sidebar"><?php esc_html_e( 'Default sidebar', 'digipublish' ); ?></label></th>
					<td><select id="digipublish_caards_default_sidebar" name="digipublish_caards_default_sidebar">
						<?php foreach ( array( 'right' => 'Right Sidebar', 'left' => 'Left Sidebar', 'disabled' => 'No Sidebar' ) as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'digipublish_caards_default_sidebar', 'right' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto Load Next Post', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_caards_load_nextpost_enabled" value="0"><label><input type="checkbox" name="digipublish_caards_load_nextpost_enabled" value="1" <?php checked( get_option( 'digipublish_caards_load_nextpost_enabled', false ) ); ?>> <?php esc_html_e( 'Enable globally (individual posts can override this)', 'digipublish' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Same category only', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_caards_load_nextpost_same_category" value="0"><label><input type="checkbox" name="digipublish_caards_load_nextpost_same_category" value="1" <?php checked( get_option( 'digipublish_caards_load_nextpost_same_category', false ) ); ?>> <?php esc_html_e( 'Only auto-load adjacent posts from the same category', 'digipublish' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Reverse direction', 'digipublish' ); ?></th>
					<td><input type="hidden" name="digipublish_caards_load_nextpost_reverse" value="0"><label><input type="checkbox" name="digipublish_caards_load_nextpost_reverse" value="1" <?php checked( get_option( 'digipublish_caards_load_nextpost_reverse', false ) ); ?>> <?php esc_html_e( 'Load previous posts instead of next posts', 'digipublish' ); ?></label></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
