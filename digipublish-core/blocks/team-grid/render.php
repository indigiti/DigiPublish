<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$limit = max( 1, min( 48, absint( $attributes['limit'] ?? 12 ) ) );
$columns = max( 1, min( 5, absint( $attributes['columns'] ?? 3 ) ) );
$users = get_users( array( 'number' => $limit, 'orderby' => 'display_name', 'order' => 'ASC', 'who' => 'authors' ) );
$wrapper = get_block_wrapper_attributes( array( 'class' => 'dp-team', 'style' => '--dp-team-columns:' . $columns ) );
echo '<section ' . $wrapper . '>';
if ( ! empty( $attributes['heading'] ) ) { echo '<h2 class="dp-team__heading">' . esc_html( $attributes['heading'] ) . '</h2>'; }
if ( $users ) {
	echo '<div class="dp-team-grid">';
	foreach ( $users as $user ) {
		$role = digipublish_core_get_user_meta_compat( $user->ID, 'digipublish_role' );
		$bio = get_the_author_meta( 'description', $user->ID );
		echo '<article class="dp-team-card"><a href="' . esc_url( get_author_posts_url( $user->ID ) ) . '">' . get_avatar( $user->ID, 180 ) . '</a><h3 class="dp-team-card__name"><a href="' . esc_url( get_author_posts_url( $user->ID ) ) . '">' . esc_html( $user->display_name ) . '</a></h3>';
		if ( ! empty( $attributes['showRole'] ) && $role ) { echo '<div class="dp-team-card__role">' . esc_html( $role ) . '</div>'; }
		if ( ! empty( $attributes['showBio'] ) && $bio ) { echo '<p class="dp-team-card__bio">' . esc_html( wp_trim_words( $bio, 26 ) ) . '</p>'; }
		echo '</article>';
	}
	echo '</div>';
}
echo '</section>';
