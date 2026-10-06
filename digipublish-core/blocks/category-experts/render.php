<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! is_category() ) {
	return;
}

$term  = get_queried_object();
$limit = max( 3, min( 8, absint( $attributes['limit'] ?? 5 ) ) );
$ids   = digipublish_core_get_category_expert_ids( (int) $term->term_id, $limit );
if ( ! $ids ) {
	return;
}

echo '<section class="tp-category-experts alignwide"><h2 class="tp-section-title">' . esc_html( sprintf( __( 'Our %s Experts', 'digipublish-core' ), $term->name ) ) . '</h2><div class="tp-category-experts__grid">';
foreach ( $ids as $author_id ) {
	$u = get_userdata( $author_id );
	if ( ! $u ) {
		continue;
	}
	$role = digipublish_core_get_user_meta_compat( $author_id, 'digipublish_role' ) ?: __( 'Contributor', 'digipublish-core' );
	echo '<a class="tp-expert-card" href="' . esc_url( get_author_posts_url( $author_id ) ) . '"><span class="tp-expert-card__avatar">' . get_avatar( $author_id, 88, '', $u->display_name, array( 'loading' => 'lazy' ) ) . '</span><strong>' . esc_html( $u->display_name ) . '</strong><span>' . esc_html( $role ) . '</span></a>';
}
echo '</div></section>';
