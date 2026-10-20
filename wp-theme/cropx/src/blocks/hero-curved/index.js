import { header } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';

registerBlockType( 'cropx/hero-curved', {
	icon: header,
	edit: Edit,
	save: () => null,
} );
