import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';

registerBlockType( 'cropx/segment-hero', {
	edit: Edit,
	save: () => null,
} );
