import { video } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import Edit from './edit';

registerBlockType( 'cropx/hero-curved-video', {
	icon: video,
	edit: Edit,
	save: () => null,
} );
