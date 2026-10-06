<?php
/**
 * Title: Footer — DigiPublish 2
 * Slug: digipublish/footer-two
 * Categories: footer, digipublish
 * Block Types: core/template-part/footer
 * Description: Centered editorial footer variation.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/footer-two.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
