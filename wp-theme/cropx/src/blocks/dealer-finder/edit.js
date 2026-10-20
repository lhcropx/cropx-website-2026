import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, SelectControl } from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { mapHeight, heading, subtext, colorScheme = 'light' } = attributes;

	// See render.php's comment: --df-pattern-url keeps the drift-pattern SVG
	// out of the compiled CSS bundle. Set here too so the editor preview matches.
	const blockProps = useBlockProps( {
		className: `df-block df-block--editor df-scheme-${ colorScheme }`,
		style: colorScheme === 'dark'
			? { '--df-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Map Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Color scheme', 'cropx' ) }
						value={ colorScheme }
						options={ [
							{ label: __( 'Light (default)', 'cropx' ), value: 'light' },
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'dark'  },
						] }
						onChange={ ( v ) => setAttributes( { colorScheme: v } ) }
					/>
					<RangeControl
						label={ __( 'Map height (px)', 'cropx' ) }
						value={ mapHeight }
						onChange={ ( v ) => setAttributes( { mapHeight: v } ) }
						min={ 400 }
						max={ 900 }
						step={ 50 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Default Map Position', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Center latitude', 'cropx' ) }
						help={ __( 'Default: 38.5 (center of contiguous US)', 'cropx' ) }
						value={ String( attributes.defaultLat ) }
						onChange={ ( v ) => setAttributes( { defaultLat: parseFloat( v ) || 38.5 } ) }
					/>
					<TextControl
						label={ __( 'Center longitude', 'cropx' ) }
						help={ __( 'Default: -96 (center of contiguous US)', 'cropx' ) }
						value={ String( attributes.defaultLng ) }
						onChange={ ( v ) => setAttributes( { defaultLng: parseFloat( v ) || -96 } ) }
					/>
					<RangeControl
						label={ __( 'Default zoom', 'cropx' ) }
						value={ attributes.defaultZoom }
						onChange={ ( v ) => setAttributes( { defaultZoom: v } ) }
						min={ 1 }
						max={ 14 }
						step={ 0.5 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps } style={ { '--df-map-height': mapHeight + 'px' } }>
				<div className="df-block-editor-preview">
					<div className="df-editor-icon">
						<svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
						</svg>
					</div>
					<div className="df-editor-text">
						<RichText
							tagName="p"
							className="df-editor-heading"
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							placeholder={ __( 'Dealer finder heading…', 'cropx' ) }
							allowedFormats={ [] }
						/>
						<p className="df-editor-sub">{ __( 'Dealer Finder Map', 'cropx' ) } · { mapHeight }px</p>
					</div>
				</div>
			</div>
		</>
	);
}
