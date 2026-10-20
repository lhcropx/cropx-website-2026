import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.css';

const SOURCE_OPTIONS = [
	{ label: __( 'Hardware (Platform Mega Menu)', 'cropx' ), value: 'platform-hardware' },
	{ label: __( 'Software (Platform Mega Menu)', 'cropx' ), value: 'platform-software' },
	{ label: __( 'Solutions Dropdown', 'cropx' ),            value: 'solutions' },
	{ label: __( 'Knowledge Hub Dropdown', 'cropx' ),        value: 'knowledge-hub' },
	{ label: __( 'About Dropdown', 'cropx' ),                value: 'about' },
	{ label: __( 'Contact Dropdown', 'cropx' ),              value: 'contact' },
	{ label: __( 'Footer: Legal', 'cropx' ),                 value: 'legal' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { source = 'solutions' } = attributes;
	const currentLabel = SOURCE_OPTIONS.find( ( o ) => o.value === source )?.label ?? source;

	const blockProps = useBlockProps( {
		className: 'menu-links-editor-wrap',
		'data-source-label': currentLabel,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Menu Source', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Show links from', 'cropx' ) }
						help={ __( 'Pulled live from Appearance → Menus — the same menus that power the header dropdowns. Edit links there, not here.', 'cropx' ) }
						value={ source }
						options={ SOURCE_OPTIONS }
						onChange={ ( v ) => setAttributes( { source: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender block="cropx/menu-links" attributes={ attributes } />
			</div>
		</>
	);
}
