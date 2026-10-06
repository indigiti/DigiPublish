<?php
$tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $tests_dir ) {
	fwrite( STDERR, "WP_TESTS_DIR is not set. Run through wp-env tests-cli.\n" );
	exit( 1 );
}

require_once $tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__ ) . '/techpress-editorial.php';
	}
);

require $tests_dir . '/includes/bootstrap.php';
