<?php
update_option( 'blogname', 'DigiPublish Test' );
update_option( 'blogdescription', 'Automated release test site' );
update_option( 'digipublish_load_next_enabled', false );

$category_names = array( 'Technology', 'Business', 'Science', 'Travel', 'Wearables' );
$category_ids   = array();
foreach ( $category_names as $name ) {
	$term = term_exists( $name, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category' );
	}
	if ( ! is_wp_error( $term ) ) {
		$category_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
	}
}

$existing = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 1,
		'meta_key'       => '_digipublish_release_fixture',
		'meta_value'     => '1',
	)
);

if ( ! $existing ) {
	for ( $i = 1; $i <= 12; $i++ ) {
		$post_id = wp_insert_post(
			array(
				'post_title'   => 'DigiPublish Release Story ' . $i,
				'post_excerpt' => 'Automated editorial fixture used by the DigiPublish browser and performance release tests.',
				'post_content' => '<!-- wp:paragraph --><p>This is automated test content for DigiPublish.</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_digipublish_release_fixture', '1' );
			if ( $category_ids ) {
				wp_set_post_categories( $post_id, array( $category_ids[ ( $i - 1 ) % count( $category_ids ) ] ) );
			}
		}
	}
}


$pagination_page = get_page_by_path( 'digipublish-pagination-test' );
if ( ! $pagination_page ) {
	wp_insert_post(
		array(
			'post_title'   => 'DigiPublish Pagination Test',
			'post_name'    => 'digipublish-pagination-test',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '<!-- wp:digipublish/post-feed {"heading":"Pagination Test","postsToShow":3,"layout":"standard-1","paginationType":"ajax","showImage":false,"showExcerpt":false,"showAuthor":false,"showDate":false} /-->',
		)
	);
}

$carousel_page = get_page_by_path( 'digipublish-carousel-test' );
if ( ! $carousel_page ) {
	wp_insert_post(
		array(
			'post_title'   => 'DigiPublish Carousel Test',
			'post_name'    => 'digipublish-carousel-test',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '<!-- wp:digipublish/post-feed {"heading":"Carousel Test","postsToShow":4,"layout":"carousel-1","carouselAutoplay":false,"carouselDots":true,"carouselWrap":true} /-->',
		)
	);
}

flush_rewrite_rules( false );
