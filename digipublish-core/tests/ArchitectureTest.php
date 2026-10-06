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

	public function test_post_feed_declares_interactivity_api_support() {
		$metadata = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/blocks/post-feed/block.json' ),
			true
		);

		$this->assertTrue( $metadata['supports']['interactivity'] );
		$this->assertSame( 'file:./view-interactivity.js', $metadata['viewScriptModule'] );
		$this->assertSame( 'file:./view.js', $metadata['viewScript'] );
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

}
