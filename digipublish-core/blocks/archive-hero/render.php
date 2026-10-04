<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$show_search = $attributes['showSearch'] ?? true;
$show_terms  = $attributes['showFeaturedTerms'] ?? true;
$title = '';
$description = '';
$crumb = '';
$term = null;
if ( is_category() || is_tag() || is_tax() ) {
    $term = get_queried_object();
    if ( $term instanceof WP_Term ) {
        $title = single_term_title( '', false );
        $description = term_description( $term );
        $crumb = $title;
    }
} elseif ( is_search() ) {
    $title = sprintf( __( 'Search results for “%s”', 'digipublish-core' ), get_search_query() );
    $description = __( 'Browse the latest matching news, guides, explainers, and analysis.', 'digipublish-core' );
    $crumb = __( 'Search', 'digipublish-core' );
} elseif ( is_day() || is_month() || is_year() ) {
    $title = get_the_archive_title();
    $description = get_the_archive_description();
    $crumb = $title;
} elseif ( is_post_type_archive() ) {
    $title = post_type_archive_title( '', false );
    $description = get_the_archive_description();
    $crumb = $title;
} else {
    $title = get_the_archive_title();
    $description = get_the_archive_description();
    $crumb = $title;
}
if ( ! $title ) { $title = __( 'Archive', 'digipublish-core' ); }
$plain_description = wp_strip_all_tags( $description );
?>
<section class="tp-archive-hero alignwide">
  <div class="tp-archive-breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'digipublish-core' ); ?>">⌂</a><span>›</span><span><?php echo esc_html( wp_strip_all_tags( $crumb ) ); ?></span></div>
  <div class="tp-archive-hero__grid">
    <div class="tp-archive-hero__copy">
      <h1><?php echo esc_html( wp_strip_all_tags( $title ) ); ?></h1>
      <?php if ( $plain_description ) : ?><p><?php echo esc_html( $plain_description ); ?></p><?php endif; ?>
      <?php if ( $show_search ) : ?>
      <form class="tp-archive-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <label class="screen-reader-text" for="tp-archive-search-input"><?php esc_html_e( 'Search', 'digipublish-core' ); ?></label>
        <input id="tp-archive-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search news, guides and reviews', 'digipublish-core' ); ?>">
        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'digipublish-core' ); ?>"><span></span></button>
      </form>
      <?php endif; ?>
      <?php
      if ( $show_terms && $term instanceof WP_Term ) {
          $featured = get_terms( array(
              'taxonomy' => $term->taxonomy,
              'hide_empty' => true,
              'parent' => $term->term_id,
              'number' => 4,
          ) );
          if ( empty( $featured ) || is_wp_error( $featured ) ) {
              $featured = get_terms( array(
                  'taxonomy' => $term->taxonomy,
                  'hide_empty' => true,
                  'number' => 4,
                  'exclude' => array( $term->term_id ),
                  'orderby' => 'count',
                  'order' => 'DESC',
              ) );
          }
          if ( ! is_wp_error( $featured ) && $featured ) {
              echo '<div class="tp-archive-featured"><span>' . esc_html__( 'Featured:', 'digipublish-core' ) . '</span>';
              foreach ( $featured as $featured_term ) {
                  $url = get_term_link( $featured_term );
                  if ( ! is_wp_error( $url ) ) {
                      echo '<a href="' . esc_url( $url ) . '">' . esc_html( $featured_term->name ) . '</a>';
                  }
              }
              echo '</div>';
          }
      }
      ?>
    </div>
    <?php if ( $term instanceof WP_Term ) : ?>
    <div class="tp-archive-hero__symbol" aria-hidden="true"><span><?php echo esc_html( strtoupper( mb_substr( $term->name, 0, 2 ) ) ); ?></span></div>
    <?php endif; ?>
  </div>
</section>
