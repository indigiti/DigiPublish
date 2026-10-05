<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
echo digipublish_core_render_gallery_slide(
	$attributes,
	1,
	1,
	array(
		'showCounter'  => true,
		'showCaptions' => true,
		'showCredits'  => true,
	)
);
