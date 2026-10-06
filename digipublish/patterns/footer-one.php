<?php
/**
 * Title: Footer — DigiPublish 1
 * Slug: digipublish/footer-one
 * Categories: footer, digipublish
 * Block Types: core/template-part/footer
 * Description: Editorial footer with brand, navigation, categories and social links.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/footer-one.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
