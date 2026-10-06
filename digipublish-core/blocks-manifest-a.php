<?php
/** Generated block metadata collection — part A. */
return array(
	'ad-slot' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/ad-slot',
		'version' => '0.11.1',
		'title' => 'Ad Slot',
		'category' => 'digipublish-editorial',
		'icon' => 'megaphone',
		'description' => 'Reserved ad position that can be connected to your ad manager without hardcoding provider scripts into templates.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'slotName' => array(
				'type' => 'string',
				'default' => 'content-slot',
			),
			'label' => array(
				'type' => 'string',
				'default' => 'Advertisement',
			),
			'minHeight' => array(
				'type' => 'integer',
				'default' => 90,
			),
			'collapseEmpty' => array(
				'type' => 'boolean',
				'default' => true,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'archive-feed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/archive-feed',
		'version' => '0.11.1',
		'title' => 'Archive Story Feed',
		'category' => 'digipublish-editorial',
		'icon' => 'grid-view',
		'description' => 'Context-aware archive feed with category top picks, dense article grid, and pagination.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'postsPerPage' => array(
				'type' => 'integer',
				'default' => 10,
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 5,
			),
			'showTopPicks' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'columnGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeight' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingTag' => array(
				'type' => 'string',
				'default' => 'h2',
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageAspect' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'columnsDesktop' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'columnsTablet' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'columnsMobile' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showAuthor' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'archive-hero' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/archive-hero',
		'version' => '0.11.1',
		'title' => 'Archive Hero',
		'category' => 'digipublish-editorial',
		'icon' => 'welcome-widgets-menus',
		'description' => 'Context-aware hero for category, tag, taxonomy, date, and search archives.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'showSearch' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showFeaturedTerms' => array(
				'type' => 'boolean',
				'default' => true,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'article-byline' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/article-byline',
		'version' => '0.11.1',
		'title' => 'Article Editorial Byline',
		'category' => 'digipublish-editorial',
		'icon' => 'admin-users',
		'description' => 'Primary author, optional fact-check/verification/reporting attribution, updated date, and share action for the current article.',
		'textdomain' => 'digipublish-core',
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:../../assets/share.js',
	),
	'article-toc' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/article-toc',
		'version' => '0.11.1',
		'title' => 'Article Table of Contents',
		'category' => 'digipublish-editorial',
		'icon' => 'list-view',
		'description' => 'Automatic on-page contents generated from article headings.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Table of Contents',
			),
		),
		'supports' => array(
			'html' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:../../assets/frontend.js',
	),
	'author-profile' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/author-profile',
		'version' => '0.11.1',
		'title' => 'Author Profile Hero',
		'category' => 'digipublish-editorial',
		'icon' => 'admin-users',
		'description' => 'Context-aware author profile header for author archives.',
		'textdomain' => 'digipublish-core',
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'category-experts' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/category-experts',
		'version' => '0.11.1',
		'title' => 'Category Experts',
		'category' => 'digipublish-editorial',
		'icon' => 'groups',
		'description' => 'Shows the most active authors for the current category.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'limit' => array(
				'type' => 'integer',
				'default' => 5,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'category-nav' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/category-nav',
		'version' => '0.11.1',
		'title' => 'Category Navigation',
		'category' => 'digipublish-editorial',
		'icon' => 'menu-alt3',
		'description' => 'Caards-style category navigation with filtering and ordering controls.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'limit' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'showDictionary' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showSearch' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'filterSlugs' => array(
				'type' => 'string',
				'default' => '',
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'name',
			),
			'order' => array(
				'type' => 'string',
				'default' => 'ASC',
			),
			'filterCategoryIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'maximum' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'alignment' => array(
				'type' => 'string',
				'default' => 'center',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showGallery' => array(
				'type' => 'boolean',
				'default' => false,
			),
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'className' => true,
			'customClassName' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'current-date' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/current-date',
		'version' => '0.11.1',
		'title' => 'Current Date',
		'category' => 'digipublish-editorial',
		'icon' => 'calendar-alt',
		'description' => 'Caards-style current date utility block.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'format' => array(
				'type' => 'string',
				'default' => 'F d, Y',
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left',
			),
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'fontSizeDesktop' => array(
				'type' => 'string',
				'default' => '0.75rem',
			),
			'fontSizeTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'fontSizeMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'fontSizeLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'className' => true,
			'customClassName' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'custom-link' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/custom-link',
		'version' => '0.11.1',
		'title' => 'Custom Link',
		'category' => 'digipublish-editorial',
		'icon' => 'admin-links',
		'description' => 'Caards-style custom link or header button.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'label' => array(
				'type' => 'string',
				'default' => 'View All',
			),
			'url' => array(
				'type' => 'string',
				'default' => '/',
			),
			'target' => array(
				'type' => 'string',
				'default' => '_self',
			),
			'buttonStyle' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'styleVariant' => array(
				'type' => 'string',
				'default' => 'default',
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left',
			),
			'disableLabelMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'textHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'circleBackground' => array(
				'type' => 'string',
				'default' => '',
			),
			'circleColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'circleHoverBackground' => array(
				'type' => 'string',
				'default' => '',
			),
			'circleHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'fontSizeDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'fontSizeTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'fontSizeMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'fontSizeLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'className' => true,
			'customClassName' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'editorial-feed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/editorial-feed',
		'version' => '0.11.1',
		'title' => 'Editorial Feed Engine',
		'category' => 'digipublish-editorial',
		'icon' => 'layout',
		'description' => 'Reusable editorial feed with card grid, weekly mosaic, carousel, featured trio, compact grid and latest-post layouts.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Editorial Feed',
			),
			'description' => array(
				'type' => 'string',
				'default' => '',
			),
			'sourceMode' => array(
				'type' => 'string',
				'default' => 'latest',
			),
			'categoryId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'categorySlug' => array(
				'type' => 'string',
				'default' => '',
			),
			'fillFromLatest' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'manualPostIds' => array(
				'type' => 'string',
				'default' => '',
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 8,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'cards-4',
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date',
			),
			'period' => array(
				'type' => 'string',
				'default' => 'all',
			),
			'avoidDuplicates' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'fallbackRandom' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showExcerpt' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showAuthor' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showReadTime' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showViews' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showShares' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showViewAll' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'viewAllLabel' => array(
				'type' => 'string',
				'default' => 'View All',
			),
			'viewAllUrl' => array(
				'type' => 'string',
				'default' => '',
			),
			'columnGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeight' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingTag' => array(
				'type' => 'string',
				'default' => 'h2',
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageAspect' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php',
	),
	'entry-hero' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/entry-hero',
		'version' => '0.11.1',
		'title' => 'Entry Hero',
		'category' => 'digipublish-editorial',
		'icon' => 'cover-image',
		'description' => 'Caards-style singular header with Standard, Large, Full, Title and None modes.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'layout' => array(
				'type' => 'string',
				'default' => 'auto',
			),
			'showBreadcrumbs' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showSubtitle' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showAuthor' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showComments' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showViews' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showShares' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showReadTime' => array(
				'type' => 'boolean',
				'default' => false,
			),
		),
		'usesContext' => array(
			'postId',
			'postType',
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'featured-categories' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/featured-categories',
		'version' => '0.11.1',
		'title' => 'Featured Categories',
		'category' => 'digipublish-editorial',
		'icon' => 'category',
		'description' => 'Caards/Powerkit-compatible featured categories including Vertical List Alt.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Featured Categories',
			),
			'categoryIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'limit' => array(
				'type' => 'integer',
				'default' => 6,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'vertical-list-alt',
			),
			'showCount' => array(
				'type' => 'boolean',
				'default' => true,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
			'customClassName' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'featured-posts' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/featured-posts',
		'version' => '0.11.1',
		'title' => 'Featured Stories',
		'category' => 'digipublish-editorial',
		'icon' => 'star-filled',
		'description' => 'Lead story plus supporting stories from a selected category or the latest posts.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Latest Features',
			),
			'categoryId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 7,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'magazine',
			),
			'showExcerpt' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showAuthor' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showFilters' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'filterLimit' => array(
				'type' => 'integer',
				'default' => 6,
			),
			'columnGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeight' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingTag' => array(
				'type' => 'string',
				'default' => 'h2',
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageAspect' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'order' => array(
				'type' => 'string',
				'default' => 'DESC',
			),
			'offset' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'filterCategoryIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'filterTagIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'excludeCategoryIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'excludeTagIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'filterPostIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'avoidDuplicates' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showComments' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showReadTime' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showViews' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showShares' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date',
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'gallery-archive' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/gallery-archive',
		'version' => '0.11.1',
		'title' => 'Gallery Archive',
		'category' => 'digipublish-editorial',
		'icon' => 'images-alt2',
		'description' => 'Photo-gallery archive or related-gallery grid with photo counts.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Photo Galleries',
			),
			'sourceMode' => array(
				'type' => 'string',
				'default' => 'archive',
			),
			'postsPerPage' => array(
				'type' => 'integer',
				'default' => 12,
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 4,
			),
			'showFilters' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showExcerpt' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'showPagination' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'columnGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeight' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingTag' => array(
				'type' => 'string',
				'default' => 'h2',
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageAspect' => array(
				'type' => 'string',
				'default' => '',
			),
			'hideDesktop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideLaptop' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideTablet' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'hideMobile' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'columnsDesktop' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'columnsTablet' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'columnsMobile' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full',
			),
			'html' => false,
			'anchor' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'border' => array(
				'radius' => true,
				'color' => true,
				'width' => true,
				'style' => true,
			),
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'gallery-slide' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/gallery-slide',
		'version' => '0.11.1',
		'title' => 'Gallery Slide',
		'category' => 'digipublish-editorial',
		'icon' => 'format-image',
		'description' => 'One image, caption and credit inside a DigiPublish photo gallery.',
		'textdomain' => 'digipublish-core',
		'parent' => array(
			'digipublish/gallery',
		),
		'attributes' => array(
			'imageId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'imageUrl' => array(
				'type' => 'string',
				'default' => '',
			),
			'alt' => array(
				'type' => 'string',
				'default' => '',
			),
			'heading' => array(
				'type' => 'string',
				'default' => '',
			),
			'caption' => array(
				'type' => 'string',
				'default' => '',
			),
			'credit' => array(
				'type' => 'string',
				'default' => '',
			),
		),
		'supports' => array(
			'html' => false,
			'reusable' => false,
		),
		'editorScript' => 'digipublish-core-editor',
		'render' => 'file:./render.php',
	),
);
