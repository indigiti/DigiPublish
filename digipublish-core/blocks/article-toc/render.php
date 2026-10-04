<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$heading = $attributes['heading'] ?? __( 'Table of Contents', 'techpress-editorial' );
?>
<aside class="tp-article-toc">
	<div class="tp-article-toc__desktop">
		<h2 class="tp-section-title"><?php echo esc_html( $heading ); ?></h2>
		<nav class="tp-article-toc__links" aria-label="<?php esc_attr_e( 'Table of contents', 'techpress-editorial' ); ?>"></nav>
	</div>
	<details class="tp-article-toc__mobile">
		<summary><?php echo esc_html( $heading ); ?><span aria-hidden="true">+</span></summary>
		<nav class="tp-article-toc__links" aria-label="<?php esc_attr_e( 'Table of contents', 'techpress-editorial' ); ?>"></nav>
	</details>
</aside>
