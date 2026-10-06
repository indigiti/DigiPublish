<?php
class DigiPublishDataCompatibilityTest extends WP_UnitTestCase {
	public function test_post_meta_key_maps_are_bidirectional() {
		$this->assertSame( '_techpress_views', digipublish_core_meta_legacy_key( '_digipublish_views', 'post' ) );
		$this->assertSame( '_digipublish_views', digipublish_core_meta_canonical_key( '_techpress_views', 'post' ) );
		$this->assertSame( '_techpress_shares', digipublish_core_meta_legacy_key( '_digipublish_shares', 'post' ) );
	}

	public function test_user_meta_key_maps_are_bidirectional() {
		$this->assertSame( 'techpress_linkedin', digipublish_core_meta_legacy_key( 'digipublish_linkedin', 'user' ) );
		$this->assertSame( 'digipublish_linkedin', digipublish_core_meta_canonical_key( 'techpress_linkedin', 'user' ) );
	}

	public function test_post_meta_compatibility_write_mirrors_both_namespaces() {
		$post_id = self::factory()->post->create();

		$this->assertTrue( digipublish_core_update_post_meta_compat( $post_id, '_digipublish_views', 42 ) );
		$this->assertSame( '42', (string) get_post_meta( $post_id, '_digipublish_views', true ) );
		$this->assertSame( '42', (string) get_post_meta( $post_id, '_techpress_views', true ) );
	}

	public function test_user_meta_compatibility_write_mirrors_both_namespaces() {
		$user_id = self::factory()->user->create();

		$this->assertTrue( digipublish_core_update_user_meta_compat( $user_id, 'digipublish_role', 'Editor' ) );
		$this->assertSame( 'Editor', get_user_meta( $user_id, 'digipublish_role', true ) );
		$this->assertSame( 'Editor', get_user_meta( $user_id, 'techpress_role', true ) );
	}
}
