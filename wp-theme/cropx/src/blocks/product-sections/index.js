import { tabs } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import './style.css';

registerBlockType( metadata.name, {
	icon: tabs,
	edit: Edit,
	save: () => null, // dynamic block — front-end rendered by render.php
} );
