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
		'styles/shared.css',
		'styles/content.css',
	) );
} );

/**
 * Register CropX block subcategories in the block inserter.
 * All 10 subcategories are prepended so they appear at the top, in order.
 */
add_filter( 'block_categories_all', function ( $categories ) {
	$cropx_categories = array(
		array( 'slug' => 'cropx-hero',        'title' => __( 'CropX / Hero Blocks',            'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-text-only',   'title' => __( 'CropX / Text-Only Layouts',      'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-text-visual', 'title' => __( 'CropX / Text + Visual Layouts',  'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-social-proof','title' => __( 'CropX / Social Proof',           'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-content',     'title' => __( 'CropX / Content Showcase',       'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-cta',         'title' => __( 'CropX / CTA Blocks',             'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-product',     'title' => __( 'CropX / Product Blocks',         'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-people',      'title' => __( 'CropX / People & Contact',       'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-global',      'title' => __( 'CropX / Global Blocks',         'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-nav',         'title' => __( 'CropX / Navigation',            'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-misc',        'title' => __( 'CropX / Miscellaneous',          'cropx' ), 'icon' => null ),
		array( 'slug' => 'cropx-footer',      'title' => __( 'CropX / Footers',                'cropx' ), 'icon' => null ),
	);
	return array_merge( $cropx_categories, $categories );
}, 10, 1 );
