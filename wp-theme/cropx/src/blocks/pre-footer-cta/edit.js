import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';

import './editor.css';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)', 'cropx' ),     value: 'general' },
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		heading,
		subtext,
		primaryLabel,
		primaryUrl,
		secondaryLabel,
		secondaryUrl,
		backgroundImageId,
		backgroundImageUrl,
		bgFocalX,
		bgFocalY,
		bgZoom,
		segmentAccent,
		eyebrowColor,
		showEyebrow,
		showCta,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `pf pf-segment-${ segmentAccent }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show CTA Button', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
					<SelectControl
						label={ __( 'Eyebrow color', 'cropx' ) }
						value={ eyebrowColor ?? 'cropx-blue' }
						options={ [
							{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
							{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
							{ label: __( 'White',                'cropx' ), value: 'white'      },
						] }
						onChange={ ( val ) => setAttributes( { eyebrowColor: val } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the top stripe, heading underline, and primary button edge.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Background image', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									backgroundImageId:  media.id,
									backgroundImageUrl: media.url,
									backgroundImageAlt: media.alt || '',
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ backgroundImageId }
							render={ ( { open } ) => (
								<div style={ { display: 'flex', flexDirection: 'column', gap: '8px' } }>
									<Button onClick={ open } variant="primary">
										{ backgroundImageId
											? __( 'Replace image', 'cropx' )
											: __( 'Select image', 'cropx' ) }
									</Button>
									{ backgroundImageId > 0 && (
										<Button
											onClick={ () =>
												setAttributes( {
													backgroundImageId:  0,
													backgroundImageUrl: '',
													backgroundImageAlt: '',
												} )
											}
											variant="link"
											isDestructive
										>
											{ __( 'Remove image', 'cropx' ) }
										</Button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
					{ backgroundImageUrl && (
						<>
							<RangeControl
								label={ __( 'Focal X — left (%)', 'cropx' ) }
								value={ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { bgFocalX: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Focal Y — top (%)', 'cropx' ) }
								value={ Math.round( ( bgFocalY ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { bgFocalY: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Zoom (%)', 'cropx' ) }
								value={ bgZoom ?? 100 }
								onChange={ ( v ) => setAttributes( { bgZoom: v } ) }
								min={ 100 }
								max={ 200 }
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'CTA button', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Primary button label', 'cropx' ) }
						value={ primaryLabel }
						onChange={ ( v ) => setAttributes( { primaryLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Primary button URL', 'cropx' ) }
						value={ primaryUrl }
						onChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Secondary button label', 'cropx' ) }
						value={ secondaryLabel }
						help={ __( 'Leave blank to hide the second button.', 'cropx' ) }
						onChange={ ( v ) => setAttributes( { secondaryLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Secondary button URL', 'cropx' ) }
						value={ secondaryUrl }
						onChange={ ( v ) => setAttributes( { secondaryUrl: v } ) }
					/>
				</PanelBody>

			</InspectorControls>

			<div { ...blockProps }>
				<div className="pf-strip" />
				<div
					className="pf-bg"
					style={
						backgroundImageUrl
							? {
								backgroundImage: `url(${ backgroundImageUrl })`,
								backgroundPosition: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.5 ) * 100 ) }%`,
								transform: `scale(${ ( ( bgZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
								transformOrigin: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.5 ) * 100 ) }%`,
							}
							: undefined
					}
				/>
				<div className="pf-overlay" />

				<div className="pf-inner">
					{ showEyebrow !== false && (
						<RichText
							tagName="span"
							className="section-eyebrow"
							placeholder={ __( 'Eyebrow text…', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							allowedFormats={ [] }
							style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
						/>
					) }
					<RichText
						tagName="h2"
						className="section-heading"
						placeholder={ __( 'Heading (use Italic to underline a word)…', 'cropx' ) }
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						allowedFormats={ [ 'core/italic', 'core/bold' ] }
					/>
					<RichText
						tagName="p"
						className="pf-sub"
						placeholder={ __( 'Supporting text… (leave empty to hide on the front end)', 'cropx' ) }
						value={ subtext }
						onChange={ ( v ) => setAttributes( { subtext: v } ) }
						allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
					/>
					{ showCta !== false && (
						<div className="pf-actions">
							{ primaryLabel && (
								<span className="btn-primary pf-cta-preview" aria-hidden="true">
									{ primaryLabel }
								</span>
							) }
							{ secondaryLabel && (
								<span className="btn-ghost pf-cta-preview" aria-hidden="true">
									{ secondaryLabel }
								</span>
							) }
						</div>
					) }
				</div>
			</div>
		</>
	);
}
