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
		'techpress',
		array( 'label' => __( 'DigiPublish Editorial', 'digipublish' ) )
	);

	// Marker used by the companion plugin for framework-specific optimizations.
	add_theme_support( 'techpress-editorial-performance' );
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
			'default'           => 'Inter',
			'sanitize_callback' => static function ( $value ) {
				$choices = techpress_theme_google_font_choices();
				return isset( $choices[ $value ] ) ? $value : 'Inter';
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
	$family  = (string) get_option( 'techpress_google_font_family', 'Inter' );
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
	$family  = (string) get_option( 'techpress_google_font_family', 'Inter' );
	$choices = techpress_theme_google_font_choices();
	return isset( $choices[ $family ] ) ? $family : 'Inter';
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
