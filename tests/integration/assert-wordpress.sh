#!/usr/bin/env bash
set -euo pipefail

wp() {
  npx wp-env run cli wp "$@"
}

wp eval '
$required = array(
  "digipublish_core_query_post_args",
  "digipublish_core_query_feed_args",
  "digipublish_core_query_feed_posts",
  "digipublish_core_get_post_meta_compat",
  "digipublish_theme_setup"
);
foreach ( $required as $fn ) {
  if ( ! function_exists( $fn ) ) {
    fwrite( STDERR, "Missing runtime function: {$fn}\n" );
    exit( 1 );
  }
}
'

wp eval '
$registry = WP_Block_Type_Registry::get_instance();
$required = array(
  "digipublish/post-feed",
  "digipublish/editorial-feed",
  "digipublish/entry-hero",
  "digipublish/related-posts",
  "digipublish/gallery"
);
foreach ( $required as $name ) {
  if ( ! $registry->is_registered( $name ) ) {
    fwrite( STDERR, "Missing registered block: {$name}\n" );
    exit( 1 );
  }
}
'

wp eval '
$post_id = wp_insert_post(
  array(
    "post_title"   => "Compatibility Probe",
    "post_status"  => "publish",
    "post_type"    => "post",
    "post_author"  => 1,
    "post_content" => "Compatibility probe content."
  )
);
if ( is_wp_error( $post_id ) || ! $post_id ) {
  exit( 1 );
}
update_post_meta( $post_id, "_techpress_views", 42 );
$value = digipublish_core_get_post_meta_compat( $post_id, "_digipublish_views" );
if ( 42 !== (int) $value ) {
  fwrite( STDERR, "Legacy-to-canonical post-meta read failed.\n" );
  exit( 1 );
}
digipublish_core_update_post_meta_compat( $post_id, "_digipublish_views", 84 );
if ( 84 !== (int) get_post_meta( $post_id, "_digipublish_views", true ) ) {
  fwrite( STDERR, "Canonical post-meta write failed.\n" );
  exit( 1 );
}
'

wp eval '
$args = digipublish_core_query_post_args(
  array(
    "postsToShow" => 3,
    "orderBy"     => "date",
    "order"       => "DESC"
  )
);
if ( 3 !== (int) $args["posts_per_page"] || "publish" !== $args["post_status"] ) {
  fwrite( STDERR, "Canonical query normalization failed.\n" );
  exit( 1 );
}
'

wp eval '
$html = do_blocks( "<!-- wp:digipublish/post-feed {\"heading\":\"Integration Feed\",\"postsToShow\":3,\"layout\":\"standard-1\"} /-->" );
if ( false === strpos( $html, "Integration Feed" ) ) {
  fwrite( STDERR, "Server-rendered Posts block failed.\n" );
  exit( 1 );
}
if ( false !== stripos( $html, "fatal error" ) ) {
  fwrite( STDERR, "Fatal error leaked into server-rendered block output.\n" );
  exit( 1 );
}
'

wp eval '
$header = get_block_template( get_stylesheet() . "//header", "wp_template_part" );
$footer = get_block_template( get_stylesheet() . "//footer", "wp_template_part" );
if ( ! $header instanceof WP_Block_Template || ! $footer instanceof WP_Block_Template ) {
  fwrite( STDERR, "Canonical header/footer template parts unavailable.\n" );
  exit( 1 );
}
'

echo "DigiPublish WordPress integration assertions passed."
