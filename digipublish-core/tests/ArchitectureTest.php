<?php
class DigiPublishArchitectureTest extends WP_UnitTestCase {
	public function test_all_manifest_blocks_are_registered() {
		$manifest = require dirname( __DIR__ ) . '/blocks-manifest.php';
		$registry = WP_Block_Type_Registry::get_instance();

		$this->assertCount( 32, $manifest );
		foreach ( array_keys( $manifest ) as $slug ) {
			$this->assertTrue(
				$registry->is_registered( 'digipublish/' . $slug ),
				'Block is not registered: digipublish/' . $slug
			);
		}
	}

	public function test_query_id_normalization_is_stable() {
		$this->assertSame(
			array( 3, 2, 7 ),
			digipublish_core_query_normalize_ids( array( '3', 2, 3, 0, -7, 'x' ) )
		);
	}

	public function test_legacy_block_namespace_rewrite_preserves_content_shape() {
		$legacy_namespace = 'tech' . 'press';
		$legacy = '<!-- wp:' . $legacy_namespace . '/post-feed {"heading":"Latest"} /-->';
		$this->assertSame(
			'<!-- wp:digipublish/post-feed {"heading":"Latest"} /-->',
			digipublish_core_rewrite_block_namespace_in_content( $legacy )
		);
	}


	public function test_theme_navigation_fallback_can_render_valid_navigation_children() {
		$theme_functions = dirname( dirname( __DIR__ ) ) . '/digipublish/functions.php';
		if ( ! function_exists( 'digipublish_navigation_fallback_blocks' ) && file_exists( $theme_functions ) ) {
			require_once $theme_functions;
		}

		$this->assertTrue( function_exists( 'digipublish_navigation_fallback_blocks' ) );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'About',
			)
		);

		$fallback = array(
			array(
				'blockName'    => 'core/page-list',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
		);
		$result = digipublish_navigation_fallback_blocks( $fallback );
		$names  = wp_list_pluck( $result, 'blockName' );

		$this->assertContains( 'core/home-link', $names );
		$this->assertContains( 'core/navigation-link', $names );
		$this->assertNotContains( 'core/page-list', $names );

		$page_links = array_values(
			array_filter(
				$result,
				static fn( $block ) => 'core/navigation-link' === ( $block['blockName'] ?? '' )
			)
		);
		$this->assertNotEmpty( $page_links );
		$this->assertSame( $page_id, (int) $page_links[0]['attrs']['id'] );
	}
}
