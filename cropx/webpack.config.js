/**
 * Extends @wordpress/scripts default webpack config to add the admin
 * editor-panels entry point alongside the auto-discovered block entries.
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path          = require( 'path' );

// defaultConfig.entry may be an object or a function depending on the version.
const defaultEntry = typeof defaultConfig.entry === 'function'
	? defaultConfig.entry()
	: defaultConfig.entry;

module.exports = {
	...defaultConfig,
	entry: {
		...defaultEntry,
		// Gutenberg sidebar panels for CPT meta fields.
		// Compiled to build/admin/editor-panels.js + editor-panels.asset.php
		'admin/editor-panels': path.resolve( __dirname, 'src/admin/editor-panels.js' ),
	},
};
