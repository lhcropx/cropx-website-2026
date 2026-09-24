import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

import './style.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null, // Dynamic block — all front-end markup comes from render.php.
} );
