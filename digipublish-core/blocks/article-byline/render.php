<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = get_the_ID();
if ( ! $post_id ) {
	$post_id = get_queried_object_id();
}
if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'post', 'digipublish_gallery' ), true ) ) {
	return;
}

$author_id   = (int) get_post_field( 'post_author', $post_id );
$author      = $author_id ? get_userdata( $author_id ) : null;
$author_role = $author_id ? get_user_meta( $author_id, 'techpress_role', true ) : '';
if ( ! $author_role ) {
	$author_role = __( 'Contributor', 'digipublish-core' );
}

$attribution_type    = (string) get_post_meta( $post_id, '_techpress_attribution_type', true );
$attribution_user_id = (int) get_post_meta( $post_id, '_techpress_attribution_user', true );
$attribution_label   = techpress_editorial_attribution_label( $attribution_type );
$attribution_user    = $attribution_user_id ? get_userdata( $attribution_user_id ) : null;
$modified_iso        = get_the_modified_date( DATE_W3C, $post_id );
$modified_display    = get_the_modified_date( 'j F Y', $post_id );
$share_url           = get_permalink( $post_id );
$share_title         = get_the_title( $post_id );
$wrapper              = get_block_wrapper_attributes( array( 'class' => 'tp-article-byline-block' . ( $attribution_label && $attribution_user ? ' has-attribution' : ' no-attribution' ) ) );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( $author ) : ?>
		<div class="tp-article-byline-block__author">
			<a class="tp-article-byline-block__avatar" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" aria-label="<?php echo esc_attr( $author->display_name ); ?>">
				<?php echo get_avatar( $author_id, 52, '', $author->display_name, array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
			</a>
			<div class="tp-article-byline-block__copy">
				<span><?php echo esc_html( sprintf( __( 'by %s', 'digipublish-core' ), $author_role ) ); ?></span>
				<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author->display_name ); ?></a>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $attribution_label && $attribution_user ) : ?>
		<div class="tp-article-byline-block__attribution">
			<span><?php echo esc_html( $attribution_label ); ?></span>
			<a href="<?php echo esc_url( get_author_posts_url( $attribution_user_id ) ); ?>"><?php echo esc_html( $attribution_user->display_name ); ?></a>
		</div>
	<?php endif; ?>

	<div class="tp-article-byline-block__updated">
		<span><?php esc_html_e( 'Updated on', 'digipublish-core' ); ?></span>
		<time datetime="<?php echo esc_attr( $modified_iso ); ?>"><?php echo esc_html( $modified_display ); ?></time>
	</div>

	<button type="button" class="tp-article-byline-block__share" data-techpress-share data-url="<?php echo esc_url( $share_url ); ?>" data-title="<?php echo esc_attr( $share_title ); ?>" aria-label="<?php esc_attr_e( 'Share this story', 'digipublish-core' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7a3.16 3.16 0 0 0 0-1.4l7.05-4.11A3 3 0 1 0 15 5c0 .23.03.45.08.66L8.03 9.77A3 3 0 1 0 8 14.3l7.12 4.16c-.04.18-.06.36-.06.54A2.94 2.94 0 1 0 18 16.08Z" fill="currentColor"/></svg>
	</button>
</div>
