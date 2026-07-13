<?php
/**
 * Asset enqueueing.
 *
 * The theme stylesheet (style.css) only carries metadata — the real
 * design tokens and front-end CSS live in styles/tokens.css. We enqueue
 * tokens.css on both front-end and editor so the published page and
 * the block editor's preview look the same.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Author web font via Fontshare's CDN. Loaded on enqueue_block_assets so
 * it's available BOTH on the front-end AND inside the block editor iframe.
 * Fontshare ships Author as a variable font with a free commercial license.
 * If we ever want to self-host for performance / vendor independence, swap
 * this for a local @font-face declaration in tokens.css.
 */
add_action( 'enqueue_block_assets', function () {
	wp_enqueue_style(
		'cropx-fonts-author',
		'https://api.fontshare.com/v2/css?f[]=author@1,2&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cropx-tokens',
		CROPX_THEME_URI . 'styles/tokens.css',
		array( 'cropx-fonts-author' ),
		CROPX_THEME_VERSION
	);

	// Shared block utilities (.section-inner, .section-header, .section-eyebrow,
	// .section-heading, .section-body, .cta-link, .section-padded).
	// Loaded after tokens so token variables are already declared.
	wp_enqueue_style(
		'cropx-shared',
		CROPX_THEME_URI . 'styles/shared.css',
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);

	// Core block styles — brands WordPress's built-in blocks (Paragraph, Heading,
	// Image, List, Quote, Table, Button, etc.) to match the CropX design system.
	// Loaded via enqueue_block_assets so it reaches both the front-end AND the
	// block editor iframe — WYSIWYG matches the published page.
	wp_enqueue_style(
		'cropx-content',
		CROPX_THEME_URI . 'styles/content.css',
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	// Theme metadata stylesheet (still required by WordPress even though
	// it only has the theme header comment).
	wp_enqueue_style(
		'cropx-theme',
		get_stylesheet_uri(),
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);

	// Nav styles are shared infrastructure: every page uses either the
	// cropx/nav block or the embedded nav inside cropx/segment-hero.
	// WordPress auto-enqueues block styles only for the block that declares
	// them; since segment-hero doesn't list nav/style-index.css in its
	// block.json (parent-directory file: paths aren't supported), we load it
	// globally here. The file is tiny — no meaningful perf cost.
	wp_enqueue_style(
		'cropx-nav-block-styles',
		CROPX_THEME_URI . 'build/blocks/nav/style-index.css',
		array(),
		CROPX_THEME_VERSION
	);
} );

/**
 * Preconnect to Fontshare's CDN so the browser opens a TCP+TLS connection
 * before it needs the font CSS. Knocks ~100–300ms off first font paint.
 */
/**
 * Expose theme URI to all block editor scripts so edit.js files can
 * build absolute URLs to theme assets (logos, PNGs, SVGs) for previews.
 * Usage in any edit.js: window.cropxThemeData?.themeUri + 'assets/...'
 * This is the standard pattern for all CropX blocks.
 */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script(
		'wp-blocks',
		'var cropxThemeData = ' . wp_json_encode( array(
			'themeUri' => trailingslashit( get_template_directory_uri() ),
		) ) . ';',
		'before'
	);

	// Sidebar panels for CPT meta fields (Team Member, Dealer, Testimonial).
	$asset_file = CROPX_THEME_DIR . 'build/admin/editor-panels.asset.php';
	if ( file_exists( $asset_file ) ) {
		$asset = require $asset_file;
		wp_enqueue_script(
			'cropx-editor-panels',
			CROPX_THEME_URI . 'build/admin/editor-panels.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}
} );

/**
 * Blog single post chrome — single.php.
 * Only loaded on individual blog post pages to keep the main stylesheet lean.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( 'post' ) ) {
		wp_enqueue_style(
			'cropx-single',
			CROPX_THEME_URI . 'styles/single.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Cards block CSS — needed for crd-* classes used in the related-posts section.
		wp_enqueue_style(
			'cropx-cards-block-styles',
			CROPX_THEME_URI . 'build/blocks/cards/style-index.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Reading progress bar + dynamic ToC builder + copy-link button.
		wp_enqueue_script(
			'cropx-blog-single',
			CROPX_THEME_URI . 'assets/js/blog-single.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
} );

/**
 * Blog archive chrome — home.php.
 * Only loaded when WordPress is rendering the "Posts page" set in
 * Settings → Reading. Keeps the global stylesheet lean.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_home() ) {
		wp_enqueue_style(
			'cropx-blog-archive',
			CROPX_THEME_URI . 'styles/blog-archive.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);

		// Nav interactive JS — not auto-enqueued on this page because the nav is
		// rendered via cropx_render_nav() (a PHP partial), not through a Gutenberg
		// block. On other pages a cropx/nav or cropx/segment-hero block triggers
		// WordPress's viewScript auto-enqueue. Here we do it manually.
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		// Load-more + carousel JS.
		wp_enqueue_script(
			'cropx-blog-archive-js',
			CROPX_THEME_URI . 'assets/js/blog-archive.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		// Pass the REST API base URL so the script works in subdirectory installs.
		wp_localize_script(
			'cropx-blog-archive-js',
			'cropxBlogArchive',
			array(
				'restUrl' => esc_url_raw( rest_url( 'wp/v2/posts' ) ),
			)
		);
	}
} );

/**
 * Publication single chrome — single-cropx_publication.php.
 * Only loaded on individual publication pages (case studies, white papers).
 * Reuses the same reading-progress JS as the blog single post.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( 'cropx_publication' ) ) {
		wp_enqueue_style(
			'cropx-pub-single',
			CROPX_THEME_URI . 'styles/pub-single.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Cards block CSS — needed for crd-* classes used in the related publications section.
		wp_enqueue_style(
			'cropx-cards-block-styles',
			CROPX_THEME_URI . 'build/blocks/cards/style-index.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		wp_enqueue_script(
			'cropx-pub-single-js',
			CROPX_THEME_URI . 'assets/js/blog-single.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
} );

add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href'        => 'https://api.fontshare.com',
			'crossorigin' => 'anonymous',
		);
		$hints[] = array(
			'href'        => 'https://cdn.fontshare.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}, 10, 2 );
