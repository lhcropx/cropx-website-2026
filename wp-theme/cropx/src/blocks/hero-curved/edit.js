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

import CtaLinkControl from '../../shared/CtaLinkControl';

const SEGMENT_OPTIONS = [
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

const SWOOP_FILL_OPTIONS = [
	{ label: __( 'Auto (match next section)', 'cropx' ), value: '' },
	{ label: __( 'White', 'cropx' ),                    value: 'white' },
	{ label: __( 'Taupe', 'cropx' ),                    value: 'taupe' },
	{ label: __( 'Deep Blue', 'cropx' ),                value: 'deep-blue' },
];

const BADGE_CONFIG = {
	enterprise:           { line1: 'Enterprise',  line2: 'Solutions' },
	'service-provider':   { line1: 'Service',     line2: 'Providers' },
	'on-farm':            { line1: 'On-Farm',      line2: 'Solutions' },
};

function MediaPanel( {
	title, imageId, imageUrl, onSelect, onRemove, defaultLabel,
	show, onToggleShow, toggleLabel,
	// Optional position / scale controls — only rendered once an image is selected.
	scale, offsetX, offsetY,
	onScaleChange, onOffsetXChange, onOffsetYChange,
	offsetYHelp, maxScale = 200,
} ) {
	const hasImage = show !== false && !! imageUrl;
	const hasPositionControls = hasImage && onScaleChange;

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
			{ hasPositionControls && (
				<>
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<RangeControl
						label={ __( 'Size (%)', 'cropx' ) }
						value={ scale ?? 100 }
						onChange={ onScaleChange }
						min={ 40 }
						max={ maxScale }
						step={ 1 }
					/>
					<RangeControl
						label={ __( 'Horizontal offset (px)', 'cropx' ) }
						help={ __( 'Positive → right, negative → left', 'cropx' ) }
						value={ offsetX ?? 0 }
						onChange={ onOffsetXChange }
						min={ -400 }
						max={ 400 }
						step={ 1 }
					/>
					<RangeControl
						label={ __( 'Vertical offset (px)', 'cropx' ) }
						help={ offsetYHelp || __( 'Positive → down, negative → up', 'cropx' ) }
						value={ offsetY ?? 0 }
						onChange={ onOffsetYChange }
						min={ -400 }
						max={ 400 }
						step={ 1 }
					/>
				</>
			) }
		</PanelBody>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, eyebrow, heading, subheading,
		ctaLabel, ctaUrl, ctaLinkType, ctaFileId, ctaFileUrl,
		cta2Label, cta2Url, showCta2, cta2LinkType, cta2FileId, cta2FileUrl,
		bgImageId, bgImageUrl,
		bgFocalX, bgFocalY, bgZoom, bgFlipX,
		deviceImageId, deviceImageUrl,
		phoneImageId, phoneImageUrl,
		showEyebrow, showCta, showDeviceImage, showAppImage,
		deviceScale, deviceOffsetX, deviceOffsetY,
		phoneScale, phoneOffsetX, phoneOffsetY,
		swoopFill,
	} = attributes;

	const badge = BADGE_CONFIG[ segment ] || BADGE_CONFIG.enterprise;

	// See render.php's comment: --hc-pattern-url keeps the drift-pattern SVG out
	// of the compiled CSS bundle. Set here too so the editor preview matches.
	const blockProps = useBlockProps( {
		className: `hc-block hc-segment-${ segment }`,
		style: {
			'--hc-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)`,
		},
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
					<SelectControl
						label={ __( 'Swoop fill colour', 'cropx' ) }
						help={ __( 'Colour of the curved corner at the hero bottom-right. Auto reads the next section\'s background.', 'cropx' ) }
						value={ swoopFill ?? '' }
						options={ SWOOP_FILL_OPTIONS }
						onChange={ ( v ) => setAttributes( { swoopFill: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Segment', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the badge, accent color, and swoop stroke gradient.', 'cropx' ) }
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
							value={ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }
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
						<ToggleControl
							label={ __( 'Flip horizontally', 'cropx' ) }
							help={ __( 'Mirror the photo left-to-right.', 'cropx' ) }
							checked={ bgFlipX ?? false }
							onChange={ ( v ) => setAttributes( { bgFlipX: v } ) }
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
						scale={ deviceScale }
						offsetX={ deviceOffsetX }
						offsetY={ deviceOffsetY }
						onScaleChange={ ( v ) => setAttributes( { deviceScale: v } ) }
						onOffsetXChange={ ( v ) => setAttributes( { deviceOffsetX: v } ) }
						onOffsetYChange={ ( v ) => setAttributes( { deviceOffsetY: v } ) }
						offsetYHelp={ __( 'Positive → down, negative → up', 'cropx' ) }
					/>
					<MediaPanel
						title={ __( 'App/Software image (bleeding past the curve)', 'cropx' ) }
						toggleLabel={ __( 'Show app/software image', 'cropx' ) }
						show={ showAppImage }
						onToggleShow={ ( v ) => setAttributes( { showAppImage: v } ) }
						imageId={ phoneImageId }
						imageUrl={ phoneImageUrl }
						onSelect={ ( media ) => setAttributes( { phoneImageId: media.id, phoneImageUrl: media.url } ) }
						onRemove={ () => setAttributes( { phoneImageId: 0, phoneImageUrl: '' } ) }
						defaultLabel={ __( 'Default: phone-mockup-b', 'cropx' ) }
						scale={ phoneScale }
						offsetX={ phoneOffsetX }
						offsetY={ phoneOffsetY }
						onScaleChange={ ( v ) => setAttributes( { phoneScale: v } ) }
						onOffsetXChange={ ( v ) => setAttributes( { phoneOffsetX: v } ) }
						onOffsetYChange={ ( v ) => setAttributes( { phoneOffsetY: v } ) }
						offsetYHelp={ __( 'Positive → up, negative → down', 'cropx' ) }
						maxScale={ 400 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Call-to-action', 'cropx' ) }>
					<TextControl
						label={ __( 'Primary button label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<CtaLinkControl
						label={ __( 'Primary button link', 'cropx' ) }
						linkType={ ctaLinkType }
						onLinkTypeChange={ ( v ) => setAttributes( { ctaLinkType: v } ) }
						url={ ctaUrl }
						onUrlChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
						fileId={ ctaFileId }
						fileUrl={ ctaFileUrl }
						onFileSelect={ ( media ) => setAttributes( { ctaFileId: media.id, ctaFileUrl: media.url } ) }
						onFileRemove={ () => setAttributes( { ctaFileId: 0, ctaFileUrl: '', ctaLinkType: 'url' } ) }
					/>
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<ToggleControl
						label={ __( 'Show secondary CTA', 'cropx' ) }
						checked={ showCta2 === true }
						onChange={ ( v ) => setAttributes( { showCta2: v } ) }
					/>
					{ showCta2 && (
						<>
							<TextControl
								label={ __( 'Secondary button label', 'cropx' ) }
								value={ cta2Label }
								onChange={ ( v ) => setAttributes( { cta2Label: v } ) }
							/>
							<CtaLinkControl
								label={ __( 'Secondary button link', 'cropx' ) }
								linkType={ cta2LinkType }
								onLinkTypeChange={ ( v ) => setAttributes( { cta2LinkType: v } ) }
								url={ cta2Url }
								onUrlChange={ ( v ) => setAttributes( { cta2Url: v } ) }
								fileId={ cta2FileId }
								fileUrl={ cta2FileUrl }
								onFileSelect={ ( media ) => setAttributes( { cta2FileId: media.id, cta2FileUrl: media.url } ) }
								onFileRemove={ () => setAttributes( { cta2FileId: 0, cta2FileUrl: '', cta2LinkType: 'url' } ) }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ /* Simplified nav preview — badge only, no dropdown interactivity */ }
				<div className="hc-nav-preview">
					<div className="hc-nav-preview-inner">
						<span className="hc-nav-preview-wordmark">CropX</span>
						<div className="hc-badge">
							<span>
								{ badge.line1 }
								<br />
								{ badge.line2 }
							</span>
						</div>
						<span className="hc-nav-preview-links" aria-hidden="true">
							Platform &nbsp;·&nbsp; Solutions &nbsp;·&nbsp; Resources &nbsp;·&nbsp; Company
						</span>
						<span className="hc-nav-preview-login">Log in</span>
					</div>
				</div>

				{ /* Hero */ }
				<div className="hc-hero">
					{ /* PageSpeed fix (Aug 2026): real <img> instead of a CSS
					     background-image — see render.php for the front-end half. */ }
					<div className="hc-bg" style={ bgFlipX ? { transform: 'scaleX(-1)' } : undefined }>
						{ bgImageUrl && (
							<img
								src={ bgImageUrl }
								alt=""
								style={ {
									objectPosition: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }%`,
									// Flip is applied on the .hc-bg wrapper above (mirrored around its
									// own center) rather than here — see hero-curved-standard/edit.js.
									transform: `scale(${ ( ( bgZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
									transformOrigin: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }%`,
								} }
							/>
						) }
					</div>
					<div className="hc-overlay" />
					<div className="hc-pattern" />
					<div className="hc-content">
						{ showEyebrow !== false && (
							<RichText
								tagName="p"
								className="hc-eyebrow"
								placeholder={ __( 'Eyebrow text…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
							/>
						) }
						<RichText
							tagName="h1"
							className="hc-headline"
							placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/italic', 'core/bold' ] }
						/>
						<RichText
							tagName="p"
							className="hc-subheadline"
							placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
							value={ subheading }
							onChange={ ( v ) => setAttributes( { subheading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ showCta !== false && ctaLabel && (
							<div className="hc-cta-row">
								<span className="hc-cta" aria-hidden="true">
									{ ctaLabel }
								</span>
								{ showCta2 && cta2Label && (
									<span className="hc-cta--ghost" aria-hidden="true">
										{ cta2Label }
										{ cta2LinkType === 'file' ? (
											<svg className="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round"/></svg>
										) : (
											<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										) }
									</span>
								) }
							</div>
						) }
					</div>
					{ /* Swoop preview — simplified static version for editor */ }
					<div className="hc-swoop-preview" aria-hidden="true" />
				</div>
			</div>
		</>
	);
}
