<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$author = is_author() ? get_queried_object() : null;
if ( ! ( $author instanceof WP_User ) ) {
    echo '<div class="tp-author-profile tp-author-profile--placeholder"><strong>' . esc_html__( 'Author Profile', 'digipublish-core' ) . '</strong><p>' . esc_html__( 'This block uses the current author when rendered on an author archive.', 'digipublish-core' ) . '</p></div>';
    return;
}
$author_id = (int) $author->ID;
$role = get_user_meta( $author_id, 'techpress_role', true );
if ( ! $role ) { $role = __( 'Contributor', 'digipublish-core' ); }
$bio = get_the_author_meta( 'description', $author_id );
$socials = array(
    'linkedin' => array( 'label' => 'in', 'url' => get_user_meta( $author_id, 'techpress_linkedin', true ) ),
    'x' => array( 'label' => 'X', 'url' => get_user_meta( $author_id, 'techpress_x', true ) ),
    'instagram' => array( 'label' => '◎', 'url' => get_user_meta( $author_id, 'techpress_instagram', true ) ),
    'youtube' => array( 'label' => '▶', 'url' => get_user_meta( $author_id, 'techpress_youtube', true ) ),
    'website' => array( 'label' => '◉', 'url' => get_the_author_meta( 'user_url', $author_id ) ),
);
?>
<section class="tp-author-profile alignwide">
  <div class="tp-archive-breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">⌂</a><span>›</span><span><?php esc_html_e( 'Contributors', 'digipublish-core' ); ?></span><span>›</span><span><?php echo esc_html( $author->display_name ); ?></span></div>
  <div class="tp-author-profile__main">
    <div class="tp-author-profile__avatar"><?php echo get_avatar( $author_id, 190, '', $author->display_name, array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?></div>
    <div class="tp-author-profile__copy">
      <h1><?php echo esc_html( $author->display_name ); ?></h1>
      <p class="tp-author-profile__role"><?php echo esc_html( $role ); ?></p>
      <?php if ( $bio ) : ?><div class="tp-author-profile__bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div><?php endif; ?>
      <div class="tp-author-profile__socials">
      <?php foreach ( $socials as $social ) : if ( empty( $social['url'] ) ) { continue; } ?>
        <a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>"><?php echo esc_html( $social['label'] ); ?></a>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
