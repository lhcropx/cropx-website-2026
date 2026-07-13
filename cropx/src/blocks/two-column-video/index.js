import { mediaAndText } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	icon: mediaAndText,
	edit: Edit,
	save: () => null,
} );
