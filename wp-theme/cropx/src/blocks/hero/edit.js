/**
 * Hero block — editor experience.
 *
 * In-editor preview that closely matches the front-end render. Editors
 * can:
 *   • Type the eyebrow, heading, and subheading inline (RichText).
 *   • Pick a background image from the WordPress media library
 *     (sidebar control).
 *   • Set the CTA label + URL (sidebar control).
 *   • Switch the segment accent color (sidebar select control).
 *
 * The save() function returns null because this is a dynamic block —
 * the front-end markup is produced by render.php at view time.
 */

import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	URLInput,
} from '@wordpress/block-editor';

// Editor-only style additions. Bundled into build/blocks/hero/index.css.
import './editor.css';
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)', 'cropx' ),    value: 'general' },
	{ label: __( 'Enterprise (Gold)', 'cropx' ),       value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ),value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),      value: 'on-farm' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		heading,
		subheading,
		ctaLabel,
		ctaUrl,
		backgroundImageId,
		backgroundImageUrl,
		backgroundImageAlt,
		bgFocalX,
		bgFocalY,
		bgZoom,
		segmentAccent,
		showEyebrow,
		showCta,
		showBottomBand,
		secondaryLabel,
		secondaryUrl,
		showSecondaryCta,
	} = attributes;

	// Block wrapper — applies the className needed for our front-end CSS
	// to take effect inside the editor preview as well. The actual photo,
	// gradient overlay, and decorative pattern are rendered as three
	// stacked <div>s below, matching the front-end render.php structure.
	const blockProps = useBlockProps( {
		className: `hero-section hero-segment-${ segmentAccent }`,
	} );

	// drift-pattern.svg lives in the theme's assets folder. cropxThemeData
	// is exposed via wp_add_inline_script() if we want a JS-side URL — but
	// for now we resolve relative to the theme URL the editor knows about.
	// The editor's CSS will load it via the `.hero-pattern` background-image
	// rule, so we leave the inline style off here — only the photo needs
	// dynamic injection because it changes per-block.


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
						label={ __( 'Show CTA button', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show bottom color band', 'cropx' ) }
						help={ __( 'Adds a CropX-blue accent stripe at the bottom of the hero.', 'cropx' ) }
						checked={ showBottomBand === true }
						onChange={ ( v ) => setAttributes( { showBottomBand: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Segment accent', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Accent color', 'cropx' ) }
						help={ __(
							'Tints the heading emphasis underline and CTA accent stripe.',
							'cropx'
						) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Background image', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => {
								setAttributes( {
									backgroundImageId: media.id,
									backgroundImageUrl: media.url,
									backgroundImageAlt: media.alt || '',
								} );
							} }
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
													backgroundImageId: 0,
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

				<PanelBody title={ __( 'Call-to-action button', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<URLInput
						label={ __( 'Button URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show secondary CTA', 'cropx' ) }
						checked={ showSecondaryCta === true }
						onChange={ ( v ) => setAttributes( { showSecondaryCta: v } ) }
					/>
					{ showSecondaryCta && (
						<>
							<TextControl
								label={ __( 'Secondary label', 'cropx' ) }
								value={ secondaryLabel }
								onChange={ ( v ) => setAttributes( { secondaryLabel: v } ) }
							/>
							<TextControl
								label={ __( 'Secondary URL', 'cropx' ) }
								value={ secondaryUrl }
								onChange={ ( v ) => setAttributes( { secondaryUrl: v } ) }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div
					className="hero-bg"
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
				<div className="hero-overlay" />
				<div className="hero-pattern" />

				<div className="hero-inner">
					{ showEyebrow !== false && (
						<RichText
							tagName="p"
							className="hero-eyebrow"
							placeholder={ __( 'Eyebrow text…', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							allowedFormats={ [] }
						/>
					) }
					<RichText
						tagName="h1"
						className="hero-heading"
						placeholder={ __( 'Hero headline (use Italic to underline a word)…', 'cropx' ) }
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						allowedFormats={ [ 'core/italic', 'core/bold' ] }
					/>
					<RichText
						tagName="p"
						className="hero-subheading"
						placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
						value={ subheading }
						onChange={ ( v ) => setAttributes( { subheading: v } ) }
						allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
					/>
					{ ( ( showCta !== false && ctaLabel ) || ( showSecondaryCta && secondaryLabel ) ) && (
						<div className="hero-actions">
							{ showCta !== false && ctaLabel && (
								<span className="hero-cta-preview" aria-hidden="true">
									{ ctaLabel }
								</span>
							) }
							{ showSecondaryCta && secondaryLabel && (
								<span className="hero-sec-cta" aria-hidden="true">
									{ secondaryLabel }
									<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
										<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
									</svg>
								</span>
							) }
						</div>
					) }
				</div>
				{ showBottomBand === true && <div className="hero-band" /> }
			</div>
		</>
	);
}
