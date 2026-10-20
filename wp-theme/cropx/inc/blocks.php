<?php
/**
 * Block registration.
 *
 * Every block lives at build/blocks/<name>/ — that path is what
 * @wordpress/scripts emits when you run `npm run build`. Each block
 * folder contains:
 *   • block.json     — block metadata (attributes, title, category)
 *   • index.js       — registerBlockType + edit component (compiled JSX)
 *   • render.php     — front-end template (PHP)
 *
 * register_block_type() picks all of that up from the folder path.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {

	$blocks_dir = CROPX_THEME_DIR . 'build/blocks';

	if ( ! is_dir( $blocks_dir ) ) {
		// Build hasn't been run yet — nothing to register.
		return;
	}

	foreach ( glob( $blocks_dir . '/*', GLOB_ONLYDIR ) as $block_path ) {
		// register_block_type accepts a folder containing block.json
		register_block_type( $block_path );
	}
} );
