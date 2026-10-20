import { mediaAndText } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import metadata from './block.json';
import Edit from './edit';

import './style.css';

registerBlockType( metadata.name, {
	icon: mediaAndText,
	edit: Edit,
	// Save the inner blocks content so $content is populated in render.php.
	// The outer block markup is always rendered by PHP (render.php).
	save: () => <InnerBlocks.Content />,
} );
