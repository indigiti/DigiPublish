<?php
/**
 * Title: Header — DigiPublish 2
 * Slug: digipublish/header-two
 * Categories: header, digipublish
 * Block Types: core/template-part/header
 * Description: Centered editorial header variation.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/header-two.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
