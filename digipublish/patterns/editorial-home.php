<?php
/**
 * Title: DigiPublish Editorial Homepage
 * Slug: digipublish/editorial-home
 * Categories: digipublish
 * Inserter: true
 *
 * DigiPublish editorial homepage composition.
 * Canonical homepage pattern for new theme templates.
 */
?>
<!-- wp:group {"align":"full","className":"dp-home-hero dp-caards-home-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull dp-home-hero dp-caards-home-hero">
	<!-- wp:group {"align":"wide","className":"dp-home-hero__inner dp-caards-home-hero__inner","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide dp-home-hero__inner dp-caards-home-hero__inner">
		<!-- wp:paragraph {"align":"center","className":"dp-eyebrow dp-caards-eyebrow","fontSize":"xs"} -->
		<p class="has-text-align-center dp-eyebrow dp-caards-eyebrow has-xs-font-size">Revolutionizing Technological Frontiers</p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"4xl"} -->
		<h1 class="wp-block-heading has-text-align-center has-4-xl-font-size">Tech it to the Limit<br>Join the Tech Revolution Today</h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"align":"center","className":"dp-home-hero__lead dp-caards-home-hero__lead","fontSize":"md"} -->
		<p class="has-text-align-center dp-home-hero__lead dp-caards-home-hero__lead has-md-font-size">Join the community of curious readers and explore the stories shaping technology, business, culture and tomorrow.</p>
		<!-- /wp:paragraph -->
		<!-- wp:digipublish/category-nav {"limit":7,"showDictionary":false,"showSearch":false} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"dp-demo-mosaic dp-caards-demo-mosaic","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide dp-demo-mosaic dp-caards-demo-mosaic">
	<!-- wp:group {"className":"dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--feature dp-caards-mosaic__col--feature","layout":{"type":"default"}} -->
	<div class="wp-block-group dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--feature dp-caards-mosaic__col--feature">
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"standard-1","offset":0,"showExcerpt":true,"excerptLength":95,"showAuthor":false,"showDate":false,"showReadTime":true,"showViews":true,"showShares":true,"showCategory":true,"imageOrientation":"portrait","className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--feature dp-caards-mosaic-card--feature"} /-->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--stack dp-caards-mosaic__col--stack","layout":{"type":"default"}} -->
	<div class="wp-block-group dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--stack dp-caards-mosaic__col--stack">
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"standard-4","offset":1,"showExcerpt":true,"excerptLength":155,"showAuthor":true,"showDate":true,"showReadTime":false,"showViews":false,"showShares":false,"showCategory":false,"showImage":false,"className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--social dp-caards-mosaic-card--social"} /-->
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"standard-4","offset":2,"showExcerpt":true,"excerptLength":105,"showAuthor":false,"showDate":true,"showReadTime":true,"showViews":true,"showShares":true,"showCategory":true,"showImage":false,"className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--dark dp-caards-mosaic-card--dark"} /-->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--portrait dp-caards-mosaic__col--portrait","layout":{"type":"default"}} -->
	<div class="wp-block-group dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--portrait dp-caards-mosaic__col--portrait">
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"standard-1","offset":3,"showExcerpt":true,"excerptLength":95,"showAuthor":false,"showDate":false,"showReadTime":true,"showViews":true,"showShares":true,"showCategory":true,"imageOrientation":"portrait","className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--portrait dp-caards-mosaic-card--portrait"} /-->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--rail dp-caards-mosaic__col--rail","layout":{"type":"default"}} -->
	<div class="wp-block-group dp-mosaic__col dp-caards-mosaic__col dp-mosaic__col--rail dp-caards-mosaic__col--rail">
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"horizontal-5","offset":4,"showExcerpt":false,"showAuthor":false,"showDate":false,"showReadTime":true,"showViews":true,"showShares":false,"showCategory":false,"className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--mini dp-caards-mosaic-card--mini"} /-->
		<!-- wp:digipublish/post-feed {"heading":"","postsToShow":1,"layout":"standard-1","offset":5,"showExcerpt":false,"showAuthor":false,"showDate":false,"showReadTime":true,"showViews":true,"showShares":true,"showCategory":true,"imageOrientation":"portrait","className":"dp-mosaic-card dp-caards-mosaic-card dp-mosaic-card--rail-feature dp-caards-mosaic-card--rail-feature"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"latest","align":"wide","className":"dp-home-section dp-caards-home-section dp-home-section--latest dp-caards-home-section--latest","layout":{"type":"default"}} -->
<div id="latest" class="wp-block-group alignwide dp-home-section dp-caards-home-section dp-home-section--latest dp-caards-home-section--latest">
	<!-- wp:digipublish/post-feed {"heading":"Latest Stories","postsToShow":8,"layout":"standard-1","offset":6,"columnsDesktop":4,"columnsTablet":2,"columnsMobile":1,"showExcerpt":true,"excerptLength":90,"showAuthor":false,"showDate":true,"showReadTime":true,"showViews":true,"showCategory":true} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"newsletter","align":"wide","className":"dp-newsletter dp-caards-newsletter","layout":{"type":"constrained"}} -->
<div id="newsletter" class="wp-block-group alignwide dp-newsletter dp-caards-newsletter">
	<!-- wp:pattern {"slug":"digipublish/newsletter"} /-->
</div>
<!-- /wp:group -->
