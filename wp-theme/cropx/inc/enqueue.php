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
} );

/**
 * Preconnect to Fontshare's CDN so the browser opens a TCP+TLS connection
 * before it needs the font CSS. Knocks ~100–300ms off first font paint.
 */
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
