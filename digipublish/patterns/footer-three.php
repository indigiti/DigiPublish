<?php
/**
 * Title: Footer — DigiPublish 3
 * Slug: digipublish/footer-three
 * Categories: footer, digipublish
 * Block Types: core/template-part/footer
 * Description: Editorial footer variation with alternate content hierarchy.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/footer-three.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
