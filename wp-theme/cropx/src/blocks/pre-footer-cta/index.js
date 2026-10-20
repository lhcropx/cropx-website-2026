import { button } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

import './style.css';

registerBlockType( metadata.name, {
	icon: button,
	edit: Edit,
	save: () => null,
} );
