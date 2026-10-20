import { mapMarker } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

import './style.css';

registerBlockType( metadata.name, {
	icon: mapMarker,
	edit: Edit,
	save: () => null, // Dynamic block — front-end rendered by render.php.
} );
