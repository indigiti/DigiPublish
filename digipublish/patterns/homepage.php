<?php
/**
 * Title: Editorial Homepage
 * Slug: techpress/homepage
 * Categories: techpress, featured
 * Block Types: core/post-content
 * Inserter: yes
 */
?>
<!-- wp:group {"align":"full","className":"tp-home-top","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-home-top">
	<!-- wp:techpress/featured-posts {"align":"wide","heading":"Latest Features","postsToShow":7,"layout":"magazine","showExcerpt":true,"showAuthor":true,"showDate":true,"showFilters":true,"filterLimit":6} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"tp-section tp-section--blue tp-dictionary-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-section tp-section--blue tp-dictionary-section">
	<!-- wp:techpress/term-index {"align":"wide","heading":"Tech Dictionary","postsToShow":16,"showSearch":true,"showAlphabet":false,"showPopular":true,"popularHeading":""} /-->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"techpress/business-markets"} /-->
<!-- wp:pattern {"slug":"techpress/top-weekly"} /-->
<!-- wp:pattern {"slug":"techpress/science-space"} /-->
<!-- wp:pattern {"slug":"techpress/travels"} /-->
<!-- wp:pattern {"slug":"techpress/wearables"} /-->
<!-- wp:pattern {"slug":"techpress/latest-posts-cards"} /-->
<!-- wp:pattern {"slug":"techpress/technology-compact"} /-->

<!-- wp:group {"align":"full","className":"tp-home-popular-categories","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-home-popular-categories">
	<!-- wp:techpress/popular-categories {"align":"wide","heading":"Popular Categories","limit":8} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"tp-section tp-section--soft","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull tp-section tp-section--soft">
	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:pattern {"slug":"techpress/newsletter"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
