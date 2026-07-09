import { pages } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

import './style.css';

registerBlockType( metadata.name, {
	icon: pages,
	edit: Edit,
	save: () => null, // dynamic block — front-end in render.php
} );
