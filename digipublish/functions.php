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
	$classes[] = 'techpress-site';
	$classes[] = 'digipublish-site';
	if ( is_singular( 'post' ) ) {
		$classes[] = 'techpress-article';
		$classes[] = 'digipublish-article';
	}
	if ( is_singular( 'digipublish_gallery' ) ) {
		$classes[] = 'digipublish-gallery';
		$classes[] = 'digipublish-article';
	}
	if ( is_category() ) {
		$classes[] = 'techpress-category';
		$classes[] = 'digipublish-category';
	}
	if ( is_tag() ) {
		$classes[] = 'techpress-tag';
		$classes[] = 'digipublish-tag';
	}
	if ( is_author() ) {
		$classes[] = 'techpress-author';
		$classes[] = 'digipublish-author';
	}
	if ( is_archive() ) {
		$classes[] = 'techpress-archive';
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
	return ':root{--tp-google-font:' . $stack . ';--wp--preset--font-family--sans:var(--tp-google-font);--wp--preset--font-family--reading:var(--tp-google-font)}body.techpress-site,body.techpress-site .wp-site-blocks,body.techpress-site .wp-site-blocks p,body.techpress-site .wp-site-blocks li,body.techpress-site .wp-site-blocks input,body.techpress-site .wp-site-blocks textarea,body.techpress-site .wp-site-blocks select,body.techpress-site .wp-site-blocks button{font-family:var(--tp-google-font)!important}';
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
			$sidebar = 'right';
		}
		$header = sanitize_key( (string) get_post_meta( $post_id, 'digipublish_page_header_type', true ) );
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
