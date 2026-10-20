import { gallery } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import './style.css';
import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	icon: gallery,
	edit: Edit,
	// Save inner blocks so $content is populated in render.php.
	save: () => <InnerBlocks.Content />,
} );
