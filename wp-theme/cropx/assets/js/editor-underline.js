/**
 * editor-underline.js
 *
 * Registers an Underline button in the Gutenberg rich-text toolbar.
 * WordPress core omits underline by default; this adds it back as a
 * first-party format type using the <u> tag.
 *
 * Enqueued on enqueue_block_editor_assets — no webpack build needed.
 * Depends on: wp-rich-text, wp-block-editor, wp-element (declared in enqueue.php).
 *
 * Keyboard shortcut: Ctrl+U / Cmd+U (standard underline shortcut).
 */
( function () {
	'use strict';

	var el                   = wp.element.createElement;
	var registerFormatType   = wp.richText.registerFormatType;
	var toggleFormat         = wp.richText.toggleFormat;
	var RichTextToolbarButton = wp.blockEditor.RichTextToolbarButton;

	registerFormatType( 'cropx/underline', {
		title:    'Underline',
		tagName:  'u',
		className: null,

		edit: function ( props ) {
			return el(
				RichTextToolbarButton,
				{
					icon:            'editor-underline',
					title:           'Underline',
					isActive:        props.isActive,
					shortcutType:    'primary',
					shortcutCharacter: 'u',
					onClick: function () {
						props.onChange(
							toggleFormat( props.value, { type: 'cropx/underline' } )
						);
					},
				}
			);
		},
	} );
} )();
