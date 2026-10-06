<?php
/**
 * Title: Header — DigiPublish 1
 * Slug: digipublish/header-one
 * Categories: header, digipublish
 * Block Types: core/template-part/header
 * Description: Primary editorial header with desktop navigation, mobile controls, search and fullscreen navigation.
 * Inserter: true
 *
 * @package DigiPublish
 */

$digipublish_part_path = get_theme_file_path( 'parts/header-one.html' );
if ( file_exists( $digipublish_part_path ) ) {
	echo file_get_contents( $digipublish_part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
