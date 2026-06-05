<?php
/**
 * Theme setup — declare WordPress feature support.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	// Tell WordPress we'll handle block stylesheets ourselves; this prevents
	// the editor and front-end from shipping the default block CSS.
	add_theme_support( 'wp-block-styles' );

	// Editor stylesheet so the block editor's preview matches the front-end.
	// In WP 6.x the editor runs inside an iframe; add_editor_style() injects
	// these files into the iframe so blocks render with our tokens applied.
	add_editor_style( array(
		'styles/tokens.css',
	) );
} );

/**
 * Register a "CropX" category in the block inserter so all of our blocks
 * sit together at the top.
 */
add_filter( 'block_categories_all', function ( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'cropx',
				'title' => __( 'CropX', 'cropx' ),
				'icon'  => null,
			),
		),
		$categories
	);
}, 10, 1 );
