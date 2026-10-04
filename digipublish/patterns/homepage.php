<?php
/**
 * Title: Editorial Homepage
 * Slug: digipublish/homepage
 * Categories: digipublish, featured
 * Block Types: core/post-content
 * Inserter: yes
 */
?>
<!-- wp:group {"align":"full","className":"tp-home-top","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-home-top">
	<!-- wp:digipublish/featured-posts {"align":"wide","heading":"Latest Features","postsToShow":7,"layout":"magazine","showExcerpt":true,"showAuthor":true,"showDate":true,"showFilters":true,"filterLimit":6} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"tp-section tp-section--blue tp-dictionary-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-section tp-section--blue tp-dictionary-section">
	<!-- wp:digipublish/term-index {"align":"wide","heading":"Tech Dictionary","postsToShow":16,"showSearch":true,"showAlphabet":false,"showPopular":true,"popularHeading":""} /-->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"digipublish/business-markets"} /-->
<!-- wp:pattern {"slug":"digipublish/top-weekly"} /-->
<!-- wp:pattern {"slug":"digipublish/science-space"} /-->
<!-- wp:pattern {"slug":"digipublish/travels"} /-->
<!-- wp:pattern {"slug":"digipublish/wearables"} /-->
<!-- wp:pattern {"slug":"digipublish/latest-posts-cards"} /-->
<!-- wp:pattern {"slug":"digipublish/technology-compact"} /-->

<!-- wp:group {"align":"full","className":"tp-home-popular-categories","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-home-popular-categories">
	<!-- wp:digipublish/popular-categories {"align":"wide","heading":"Popular Categories","limit":8} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"tp-section tp-section--soft","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-section tp-section--soft">
	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:pattern {"slug":"digipublish/newsletter"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
