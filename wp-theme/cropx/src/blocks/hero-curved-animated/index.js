import { header } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';

registerBlockType( 'cropx/hero-curved-animated', {
	icon: header,
	edit: Edit,
	save: () => null,
} );
