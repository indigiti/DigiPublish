<?php
/**
 * Title: Header — DigiPublish 4
 * Slug: digipublish/header-four
 * Categories: header, digipublish
 * Block Types: core/template-part/header
 * Description: Alternative editorial header variation.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/header-four.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
