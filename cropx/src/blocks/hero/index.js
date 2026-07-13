/**
 * CropX Hero block — registration entry point.
 *
 * @wordpress/scripts compiles this file (and the JSX it pulls in via
 * import) into build/blocks/hero/index.js. WordPress loads that file
 * automatically because block.json points to "file:./index.js".
 */

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

// Front-end + editor styles. webpack picks these up via the import,
// bundles them into build/blocks/hero/style-index.css, and WordPress
// auto-enqueues them because block.json points at "style-index.css".
import './style.css';

registerBlockType( metadata.name, {
	edit: Edit,

	// Dynamic block — render is done in PHP (render.php) so we don't
	// need a save() function. Returning null tells Gutenberg "the
	// server will produce the markup."
	save: () => null,
} );
