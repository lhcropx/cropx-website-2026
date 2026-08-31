import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	URLInput,
} from '@wordpress/block-editor';
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
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

const BADGE_CONFIG = {
	enterprise:           { line1: 'Enterprise',  line2: 'Solutions' },
	'service-provider':   { line1: 'Service',     line2: 'Providers' },
	'on-farm':            { line1: 'On-Farm',      line2: 'Solutions' },
};

function MediaPanel( { title, imageId, imageUrl, onSelect, onRemove, defaultLabel, show, onToggleShow, toggleLabel } ) {
	return (
		<PanelBody title={ title }>
			{ toggleLabel && (
				<ToggleControl
					label={ toggleLabel }
					checked={ show !== false }
					onChange={ onToggleShow }
				/>
			) }
			{ show !== false && (
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ onSelect }
						allowedTypes={ [ 'image' ] }
						value={ imageId }
						render={ ( { open } ) => (
							<div style={ { display: 'flex', flexDirection: 'column', gap: '8px' } }>
								{ imageUrl && (
									<img
										src={ imageUrl }
										alt=""
										style={ { maxWidth: '100%', height: 'auto', borderRadius: '2px', marginBottom: '4px' } }
									/>
								) }
								<Button onClick={ open } variant="primary">
									{ imageId ? __( 'Replace image', 'cropx' ) : __( 'Select image', 'cropx' ) }
								</Button>
								{ ! imageId && defaultLabel && (
									<p style={ { margin: 0, fontSize: '11px', color: '#757575', lineHeight: '1.4' } }>
										{ defaultLabel }
									</p>
								) }
								{ imageId > 0 && (
									<Button onClick={ onRemove } variant="link" isDestructive>
										{ __( 'Remove image', 'cropx' ) }
									</Button>
								) }
							</div>
						) }
					/>
				</MediaUploadCheck>
			) }
		</PanelBody>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, eyebrow, heading, subheading,
		ctaLabel, ctaUrl,
		bgImageId, bgImageUrl,
		bgFocalX, bgFocalY, bgZoom,
		deviceImageId, deviceImageUrl,
		phoneImageId, phoneImageUrl,
		showEyebrow, showCta, showDeviceImage, showAppImage,
		secondaryLabel, secondaryUrl, showSecondaryCta,
	} = attributes;

	const badge = BADGE_CONFIG[ segment ] || BADGE_CONFIG.enterprise;

	// PageSpeed fix (Aug 2026): real, cacheable drift-pattern URL instead of a
	// base64-inlined one — see render.php for the front-end half.
	const blockProps = useBlockProps( {
		className: `sgh-block sgh-segment-${ segment }`,
		style: { '--sgh-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` },
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
						label={ __( 'Show CTA', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Segment', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the badge, accent color, and colored border stripe.', 'cropx' ) }
						value={ segment }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segment: v } ) }
					/>
				</PanelBody>

				<MediaPanel
					title={ __( 'Background image', 'cropx' ) }
					imageId={ bgImageId }
					imageUrl={ bgImageUrl }
					onSelect={ ( media ) => setAttributes( { bgImageId: media.id, bgImageUrl: media.url } ) }
					onRemove={ () => setAttributes( { bgImageId: 0, bgImageUrl: '' } ) }
					defaultLabel={ __( 'Select background image', 'cropx' ) }
				/>
				{ bgImageUrl && (
					<PanelBody title={ __( 'Background focal point & zoom', 'cropx' ) } initialOpen={ false }>
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
					</PanelBody>
				) }

				<PanelBody title={ __( 'Product image overlay', 'cropx' ) } initialOpen={ false }>
					<MediaPanel
						title={ __( 'Device image (sensor PNG)', 'cropx' ) }
						toggleLabel={ __( 'Show device image', 'cropx' ) }
						show={ showDeviceImage }
						onToggleShow={ ( v ) => setAttributes( { showDeviceImage: v } ) }
						imageId={ deviceImageId }
						imageUrl={ deviceImageUrl }
						onSelect={ ( media ) => setAttributes( { deviceImageId: media.id, deviceImageUrl: media.url } ) }
						onRemove={ () => setAttributes( { deviceImageId: 0, deviceImageUrl: '' } ) }
						defaultLabel={ __( 'Default: vertex-partial-a', 'cropx' ) }
					/>
					<MediaPanel
						title={ __( 'App/Software image (bleeding below hero)', 'cropx' ) }
						toggleLabel={ __( 'Show app/software image', 'cropx' ) }
						show={ showAppImage }
						onToggleShow={ ( v ) => setAttributes( { showAppImage: v } ) }
						imageId={ phoneImageId }
						imageUrl={ phoneImageUrl }
						onSelect={ ( media ) => setAttributes( { phoneImageId: media.id, phoneImageUrl: media.url } ) }
						onRemove={ () => setAttributes( { phoneImageId: 0, phoneImageUrl: '' } ) }
						defaultLabel={ __( 'Default: phone-mockup-b', 'cropx' ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Call-to-action', 'cropx' ) }>
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
				{ /* Simplified nav preview — badge only, no dropdown interactivity */ }
				<div className="sgh-nav-preview">
					<div className="sgh-nav-preview-inner">
						<span className="sgh-nav-preview-wordmark">CropX</span>
						<div className="sgh-badge">
							<span>
								{ badge.line1 }
								<br />
								{ badge.line2 }
							</span>
						</div>
						<span className="sgh-nav-preview-links" aria-hidden="true">
							Platform &nbsp;·&nbsp; Solutions &nbsp;·&nbsp; Resources &nbsp;·&nbsp; Company
						</span>
						<span className="sgh-nav-preview-login">Log in</span>
					</div>
				</div>

				{ /* Hero */ }
				<div className="sgh-hero">
					{ /* PageSpeed fix (Aug 2026): real <img> instead of a CSS
						 background-image — see render.php for the front-end half. */ }
					<div className="sgh-bg">
						{ bgImageUrl && (
							<img
								src={ bgImageUrl }
								alt=""
								style={ {
									objectPosition: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.5 ) * 100 ) }%`,
									transform: `scale(${ ( ( bgZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
									transformOrigin: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.5 ) * 100 ) }%`,
								} }
							/>
						) }
					</div>
					<div className="sgh-overlay" />
					<div className="sgh-pattern" />
					<div className="sgh-content">
						{ showEyebrow !== false && (
							<RichText
								tagName="p"
								className="sgh-eyebrow"
								placeholder={ __( 'Eyebrow text…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
							/>
						) }
						<RichText
							tagName="h1"
							className="sgh-headline"
							placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/italic', 'core/bold' ] }
						/>
						<RichText
							tagName="p"
							className="sgh-subheadline"
							placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
							value={ subheading }
							onChange={ ( v ) => setAttributes( { subheading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ ( ( showCta !== false && ctaLabel ) || ( showSecondaryCta && secondaryLabel ) ) && (
							<div className="sgh-actions">
								{ showCta !== false && ctaLabel && (
									<span className="sgh-cta" aria-hidden="true">
										{ ctaLabel }
									</span>
								) }
								{ showSecondaryCta && secondaryLabel && (
									<span className="sgh-sec-cta" aria-hidden="true">
										{ secondaryLabel }
										<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
											<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
										</svg>
									</span>
								) }
							</div>
						) }
					</div>
				</div>

				{ /* Segment border stripe */ }
				<div className="sgh-border" aria-hidden="true" />
			</div>
		</>
	);
}
