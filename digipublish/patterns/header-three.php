<?php
/**
 * Title: Header — DigiPublish 3
 * Slug: digipublish/header-three
 * Categories: header, digipublish
 * Block Types: core/template-part/header
 * Description: Compact editorial header variation.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/header-three.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
