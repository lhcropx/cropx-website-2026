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
