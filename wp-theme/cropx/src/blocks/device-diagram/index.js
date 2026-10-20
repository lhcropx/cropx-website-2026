import { mobile } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';

registerBlockType( 'cropx/device-diagram', {
	icon: mobile,
	edit: Edit,
	save: () => null,
} );
