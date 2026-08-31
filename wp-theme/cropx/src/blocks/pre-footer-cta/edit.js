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
import CtaLinkControl from '../../shared/CtaLinkControl';

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
		primaryLinkType,
		primaryFileId,
		primaryFileUrl,
		secondaryLabel,
		secondaryUrl,
		secondaryLinkType,
		secondaryFileId,
		secondaryFileUrl,
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

	// PageSpeed fix (Aug 2026): real, cacheable drift-pattern URL instead of a
	// base64-inlined one — see render.php for the front-end half.
	const blockProps = useBlockProps( {
		className: `pf pf-segment-${ segmentAccent }`,
		style: { '--pf-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` },
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the top stripe, heading underline, and primary button edge.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow !== false && (
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
					) }
					<ToggleControl
						label={ __( 'Show CTA Button', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
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
					<CtaLinkControl
						label={ __( 'Primary button link', 'cropx' ) }
						linkType={ primaryLinkType }
						onLinkTypeChange={ ( v ) => setAttributes( { primaryLinkType: v } ) }
						url={ primaryUrl }
						onUrlChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
						fileId={ primaryFileId }
						fileUrl={ primaryFileUrl }
						onFileSelect={ ( media ) => setAttributes( { primaryFileId: media.id, primaryFileUrl: media.url } ) }
						onFileRemove={ () => setAttributes( { primaryFileId: 0, primaryFileUrl: '', primaryLinkType: 'url' } ) }
					/>
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<TextControl
						label={ __( 'Secondary button label', 'cropx' ) }
						value={ secondaryLabel }
						help={ __( 'Leave blank to hide the second button.', 'cropx' ) }
						onChange={ ( v ) => setAttributes( { secondaryLabel: v } ) }
					/>
					<CtaLinkControl
						label={ __( 'Secondary button link', 'cropx' ) }
						linkType={ secondaryLinkType }
						onLinkTypeChange={ ( v ) => setAttributes( { secondaryLinkType: v } ) }
						url={ secondaryUrl }
						onUrlChange={ ( v ) => setAttributes( { secondaryUrl: v } ) }
						fileId={ secondaryFileId }
						fileUrl={ secondaryFileUrl }
						onFileSelect={ ( media ) => setAttributes( { secondaryFileId: media.id, secondaryFileUrl: media.url } ) }
						onFileRemove={ () => setAttributes( { secondaryFileId: 0, secondaryFileUrl: '', secondaryLinkType: 'url' } ) }
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
									{ secondaryLinkType === 'file' ? (
										<svg className="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" /><polyline points="7 10 12 15 17 10" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" /><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" /></svg>
									) : (
										<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" /></svg>
									) }
								</span>
							) }
						</div>
					) }
				</div>
			</div>
		</>
	);
}
