import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

import './editor.css';

const SVG_ARROW = (
	<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		enterpriseEyebrow, enterpriseHeading, enterpriseUrl, enterpriseBody,
		serviceEyebrow,    serviceHeading,    serviceUrl,    serviceBody,
		onFarmEyebrow,     onFarmHeading,     onFarmUrl,     onFarmBody,
		showEyebrow,
	} = attributes;

	const blockProps = useBlockProps( { className: 'seg-section' } );

	const segments = [
		{
			key:     'enterprise',
			label:   __( 'Enterprise', 'cropx' ),
			eyebrow: enterpriseEyebrow,
			heading: enterpriseHeading,
			url:     enterpriseUrl,
			body:    enterpriseBody,
			setEyebrow: ( v ) => setAttributes( { enterpriseEyebrow: v } ),
			setHeading:  ( v ) => setAttributes( { enterpriseHeading:  v } ),
			setUrl:      ( v ) => setAttributes( { enterpriseUrl:      v } ),
			setBody:     ( v ) => setAttributes( { enterpriseBody:     v } ),
		},
		{
			key:     'service',
			label:   __( 'Service Providers', 'cropx' ),
			eyebrow: serviceEyebrow,
			heading: serviceHeading,
			url:     serviceUrl,
			body:    serviceBody,
			setEyebrow: ( v ) => setAttributes( { serviceEyebrow: v } ),
			setHeading:  ( v ) => setAttributes( { serviceHeading:  v } ),
			setUrl:      ( v ) => setAttributes( { serviceUrl:      v } ),
			setBody:     ( v ) => setAttributes( { serviceBody:     v } ),
		},
		{
			key:     'onFarm',
			label:   __( 'On-Farm', 'cropx' ),
			eyebrow: onFarmEyebrow,
			heading: onFarmHeading,
			url:     onFarmUrl,
			body:    onFarmBody,
			setEyebrow: ( v ) => setAttributes( { onFarmEyebrow: v } ),
			setHeading:  ( v ) => setAttributes( { onFarmHeading:  v } ),
			setUrl:      ( v ) => setAttributes( { onFarmUrl:      v } ),
			setBody:     ( v ) => setAttributes( { onFarmBody:     v } ),
		},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
				</PanelBody>

				{ segments.map( ( seg ) => (
					<PanelBody key={ seg.key } title={ seg.label } initialOpen={ seg.key === 'enterprise' }>
						<TextControl
							label={ __( 'Eyebrow', 'cropx' ) }
							value={ seg.eyebrow }
							onChange={ seg.setEyebrow }
						/>
						<TextControl
							label={ __( 'Heading', 'cropx' ) }
							value={ seg.heading }
							onChange={ seg.setHeading }
						/>
						<TextControl
							label={ __( 'Link URL', 'cropx' ) }
							value={ seg.url }
							onChange={ seg.setUrl }
						/>
					</PanelBody>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="seg-inner">
					{ /* Tab bar omitted in editor — all 3 columns are always visible */ }
					<div className="seg-grid">
						{ segments.map( ( seg ) => (
							<div key={ seg.key } className="seg-col active">
								{ showEyebrow !== false && seg.eyebrow && (
									<p className="seg-eyebrow">{ seg.eyebrow }</p>
								) }
								<div className="seg-name-wrap">
									<h2 className="seg-name">
										{ /* Link replaced with span in editor so clicking doesn't navigate */ }
										<span>
											<span className="seg-underline">
												{ seg.heading || __( 'Segment heading', 'cropx' ) }
												<span className="seg-arrow" aria-hidden="true">
													{ SVG_ARROW }
												</span>
											</span>
										</span>
									</h2>
								</div>
								<RichText
									tagName="p"
									className="seg-body"
									value={ seg.body }
									onChange={ seg.setBody }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
									placeholder={ __( 'Segment description…', 'cropx' ) }
								/>
							</div>
						) ) }
					</div>
				</div>
			</section>
		</>
	);
}
