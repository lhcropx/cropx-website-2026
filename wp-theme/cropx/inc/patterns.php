<?php
/**
 * Register block pattern categories.
 *
 * The "CropX Pages" starter-page patterns that used to be registered here
 * (Homepage, About, Products Hub, etc.) have been intentionally removed from
 * the inserter — see PROGRESS.md for the date/reason. This is safe: these
 * were standard (unsynced) patterns registered via register_block_pattern(),
 * so their content was copied into each page's block content at insertion
 * time. Once inserted, a page's content is fully independent of the pattern
 * registration — removing the registration does not alter, unlink, or
 * disrupt any page that was already built from one of these patterns.
 *
 * The source files themselves still live in /patterns/ (untouched) in case
 * they're needed for reference or reinstatement later.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {

	// Category registration left in place — WordPress hides an empty
	// category from the inserter automatically, so this is harmless.
	register_block_pattern_category( 'cropx-pages', [
		'label' => __( 'CropX Pages', 'cropx' ),
	] );

	register_block_pattern_category( 'cropx-blocks', [
		'label' => __( 'CropX Universal Blocks', 'cropx' ),
	] );

} );
