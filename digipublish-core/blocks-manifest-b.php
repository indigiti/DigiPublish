<?php
/** Generated block metadata collection — part B. */
return array(
	'gallery' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/gallery',
		'version' => '0.11.2',
		'title' => 'Photo Gallery',
		'category' => 'digipublish-editorial',
		'icon' => 'format-gallery',
		'description' => 'Editorial photo gallery with story, swipe and grid display modes.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'displayMode' => array(
				'type' => 'string',
				'default' => 'story',
			),
			'showCounter' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showCaptions' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showCredits' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showThumbnails' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'allowFullscreen' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showSharing' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'adInterval' => array(
				'type' => 'integer',
				'default' => 0,
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
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php',
	),
	'instagram-carousel' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/instagram-carousel',
		'version' => '0.11.2',
		'title' => 'Instagram Carousel',
		'category' => 'digipublish-editorial',
		'icon' => 'instagram',
		'description' => 'Instagram presentation block for manually supplied or provider-filtered feed items.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Instagram',
			),
			'profileUrl' => array(
				'type' => 'string',
				'default' => '',
			),
			'items' => array(
				'type' => 'string',
				'default' => '',
			),
			'showHeader' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showFollowButton' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 5,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'default',
			),
			'number' => array(
				'type' => 'integer',
				'default' => 6,
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => 'medium',
			),
			'target' => array(
				'type' => 'string',
				'default' => '_blank',
			),
			'diagonalCardMinHeight' => array(
				'type' => 'string',
				'default' => '480px',
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
		'viewScript' => 'file:./view.js',
	),
	'mega-menu' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/mega-menu',
		'version' => '0.11.2',
		'title' => 'Mega Menu',
		'category' => 'digipublish-editorial',
		'icon' => 'menu-alt3',
		'description' => 'Site Editor compatible mega menu with latest or category story cards.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'label' => array(
				'type' => 'string',
				'default' => 'Explore',
			),
			'url' => array(
				'type' => 'string',
				'default' => '#',
			),
			'sourceMode' => array(
				'type' => 'string',
				'default' => 'latest',
			),
			'categoryId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 4,
			),
			'showImages' => array(
				'type' => 'boolean',
				'default' => true,
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
			'html' => false,
			'anchor' => true,
			'className' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
	),
	'opt-in-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/opt-in-form',
		'version' => '0.11.2',
		'title' => 'Opt-In Form',
		'category' => 'digipublish-editorial',
		'icon' => 'email',
		'description' => 'Editorial opt-in form with first-party color controls and integration hooks.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Stay in the loop',
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Get the latest stories in your inbox.',
			),
			'buttonLabel' => array(
				'type' => 'string',
				'default' => 'Subscribe',
			),
			'actionUrl' => array(
				'type' => 'string',
				'default' => '',
			),
			'emailFieldName' => array(
				'type' => 'string',
				'default' => 'email',
			),
			'inputBackground' => array(
				'type' => 'string',
				'default' => '',
			),
			'inputColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonBackground' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonHoverBackground' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonHoverColor' => array(
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
	'popular-categories' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/popular-categories',
		'version' => '0.11.2',
		'title' => 'Popular Categories',
		'category' => 'digipublish-editorial',
		'icon' => 'category',
		'description' => 'Icon-style publication category strip.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Popular Categories',
			),
			'limit' => array(
				'type' => 'integer',
				'default' => 8,
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
	'post-author-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/post-author-card',
		'version' => '0.11.2',
		'title' => 'Article Author Card',
		'category' => 'digipublish-editorial',
		'icon' => 'id',
		'description' => 'Detailed author card for the current article.',
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
	'post-feed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/post-feed',
		'version' => '0.11.2',
		'title' => 'Posts',
		'category' => 'digipublish-editorial',
		'icon' => 'screenoptions',
		'description' => 'Flexible editorial Posts block with standard, masonry, horizontal, tile and carousel layouts plus advanced query controls.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Latest',
			),
			'categoryId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 1,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'standard-1',
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date',
			),
			'showImage' => array(
				'type' => 'boolean',
				'default' => true,
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
			'paginationType' => array(
				'type' => 'string',
				'default' => 'none',
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
			'showReadMore' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'readMoreLabel' => array(
				'type' => 'string',
				'default' => 'Read more',
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
				'default' => 'medium_large',
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
			'postType' => array(
				'type' => 'string',
				'default' => 'post',
			),
			'postFormats' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'string',
				),
			),
			'filterTaxonomy' => array(
				'type' => 'string',
				'default' => '',
			),
			'filterTermIds' => array(
				'type' => 'array',
				'default' => array(),
				'items' => array(
					'type' => 'integer',
				),
			),
			'relatedPosts' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'topMetaType' => array(
				'type' => 'string',
				'default' => '',
			),
			'compactMeta' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '',
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '',
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '',
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '',
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => '',
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => '',
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => '',
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => '',
			),
			'blockBorderRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'blockBorderStyle' => array(
				'type' => 'string',
				'default' => 'none',
			),
			'blockBorderWidth' => array(
				'type' => 'string',
				'default' => '',
			),
			'customCss' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingTag' => array(
				'type' => 'string',
				'default' => 'h2',
			),
			'excerptLength' => array(
				'type' => 'integer',
				'default' => 100,
			),
			'excerptFontSize' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageOrientation' => array(
				'type' => 'string',
				'default' => 'original',
			),
			'imageBorderRadius' => array(
				'type' => 'string',
				'default' => '',
			),
			'carouselAutoplay' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'carouselDots' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'carouselWrap' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'contentGap' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentAlign' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageAlign' => array(
				'type' => 'string',
				'default' => '',
			),
			'imageWidth' => array(
				'type' => 'string',
				'default' => '',
			),
			'showPostFormat' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'enableVideoBackgrounds' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'enableVideoControls' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'headingColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'headingHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'excerptColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'metaColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'metaLinksColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'metaLinksHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'categoryColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'categoryHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'readMoreColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'readMoreHoverColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'masonryWidgets' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'masonryWidgetArea' => array(
				'type' => 'string',
				'default' => 'sidebar-archive',
			),
			'masonryWidgetsAfter' => array(
				'type' => 'integer',
				'default' => 3,
			),
			'masonryWidgetsRepeat' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'columnsLaptop' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'columnGapDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'columnGapLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'columnGapTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'columnGapMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGapDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGapLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGapTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'rowGapMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentGapDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentGapLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentGapTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentGapMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeightDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeightLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeightTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardMinHeightMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingFontSizeDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingFontSizeLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingFontSizeTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'cardHeadingFontSizeMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'excerptFontSizeDesktop' => array(
				'type' => 'string',
				'default' => '',
			),
			'excerptFontSizeLaptop' => array(
				'type' => 'string',
				'default' => '',
			),
			'excerptFontSizeTablet' => array(
				'type' => 'string',
				'default' => '',
			),
			'excerptFontSizeMobile' => array(
				'type' => 'string',
				'default' => '',
			),
			'masonryPatternId' => array(
				'type' => 'integer',
				'default' => 0,
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
			'customClassName' => true,
			'visibility' => true,
			'interactivity' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:./view-interactivity.js',
	),
	'related-posts' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/related-posts',
		'version' => '0.11.2',
		'title' => 'Related / Read Next',
		'category' => 'digipublish-editorial',
		'icon' => 'images-alt2',
		'description' => 'Related stories ranked from the current post\'s categories and tags, with Related Features and Read Next layouts.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Related Features',
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 4,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'features',
			),
			'relationMode' => array(
				'type' => 'string',
				'default' => 'category-tags',
			),
			'showExcerpt' => array(
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
	'section-content' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/section-content',
		'version' => '0.11.2',
		'title' => 'Section Content',
		'category' => 'digipublish-editorial',
		'icon' => 'align-wide',
		'description' => 'Legacy section content column retained for saved-content compatibility.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'backgroundColor' => array(
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
			'inserter' => false,
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'parent' => array(
			'digipublish/section',
		),
	),
	'section-heading' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/section-heading',
		'version' => '0.11.2',
		'title' => 'Section Heading',
		'category' => 'digipublish-editorial',
		'icon' => 'heading',
		'description' => 'Legacy section heading retained for saved-content compatibility.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => 'Section Heading',
			),
			'level' => array(
				'type' => 'integer',
				'default' => 2,
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'accentColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'accentContrastColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'styleVariant' => array(
				'type' => 'string',
				'default' => 'style-1',
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
			'inserter' => false,
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
	),
	'section-sidebar' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/section-sidebar',
		'version' => '0.11.2',
		'title' => 'Section Sidebar',
		'category' => 'digipublish-editorial',
		'icon' => 'align-pull-right',
		'description' => 'Legacy section sidebar retained for saved-content compatibility.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'backgroundColor' => array(
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
			'inserter' => false,
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
		'parent' => array(
			'digipublish/section',
		),
	),
	'section' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/section',
		'version' => '0.11.2',
		'title' => 'Section',
		'category' => 'digipublish-editorial',
		'icon' => 'columns',
		'description' => 'Legacy content/sidebar structural block retained for saved-content compatibility.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'layout' => array(
				'type' => 'string',
				'default' => 'right-sidebar',
			),
			'gapDesktop' => array(
				'type' => 'string',
				'default' => '40px',
			),
			'gapLaptop' => array(
				'type' => 'string',
				'default' => '40px',
			),
			'gapTablet' => array(
				'type' => 'string',
				'default' => '40px',
			),
			'gapMobile' => array(
				'type' => 'string',
				'default' => '40px',
			),
			'sidebarWidthDesktop' => array(
				'type' => 'string',
				'default' => '390px',
			),
			'sidebarWidthLaptop' => array(
				'type' => 'string',
				'default' => '390px',
			),
			'sidebarWidthTablet' => array(
				'type' => 'string',
				'default' => '300px',
			),
			'sidebarWidthMobile' => array(
				'type' => 'string',
				'default' => '300px',
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
			'inserter' => false,
			'visibility' => true,
		),
		'editorScript' => 'digipublish-core-editor',
		'style' => 'file:./style.css',
	),
	'sidebar-feed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/sidebar-feed',
		'version' => '0.11.2',
		'title' => 'Post Sidebar Feed',
		'category' => 'digipublish-editorial',
		'icon' => 'columns',
		'description' => 'Compact post-sidebar modules with recent-story, ranked-story and visual-mosaic layouts.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Recent Stories',
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'meta-list',
			),
			'sourceMode' => array(
				'type' => 'string',
				'default' => 'current',
			),
			'contentType' => array(
				'type' => 'string',
				'default' => 'post',
			),
			'categoryId' => array(
				'type' => 'integer',
				'default' => 0,
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 5,
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date',
			),
			'period' => array(
				'type' => 'string',
				'default' => 'all',
			),
			'showHeading' => array(
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
	'team-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/team-grid',
		'version' => '0.11.2',
		'title' => 'Meet Team',
		'category' => 'digipublish-editorial',
		'icon' => 'groups',
		'description' => 'Editorial team grid sourced from WordPress users.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Meet the Team',
			),
			'limit' => array(
				'type' => 'integer',
				'default' => 12,
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 3,
			),
			'showBio' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showRole' => array(
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
	'term-index' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/term-index',
		'version' => '0.11.2',
		'title' => 'Dictionary Index',
		'category' => 'digipublish-editorial',
		'icon' => 'book-alt',
		'description' => 'Searchable A–Z index of dictionary terms stored as Gutenberg-native content.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Tech Dictionary',
			),
			'postsToShow' => array(
				'type' => 'integer',
				'default' => 16,
			),
			'showSearch' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showAlphabet' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showPopular' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'popularHeading' => array(
				'type' => 'string',
				'default' => '',
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
	'twitter-carousel' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'digipublish/twitter-carousel',
		'version' => '0.11.2',
		'title' => 'X / Twitter Carousel',
		'category' => 'digipublish-editorial',
		'icon' => 'twitter',
		'description' => 'X/Twitter presentation block for manually supplied or provider-filtered feed items.',
		'textdomain' => 'digipublish-core',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => 'Twitter Feed',
			),
			'profileUrl' => array(
				'type' => 'string',
				'default' => '',
			),
			'items' => array(
				'type' => 'string',
				'default' => '',
			),
			'showHeader' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'showFollowButton' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 3,
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'default',
			),
			'number' => array(
				'type' => 'integer',
				'default' => 5,
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
		'viewScript' => 'file:./view.js',
	),
);
