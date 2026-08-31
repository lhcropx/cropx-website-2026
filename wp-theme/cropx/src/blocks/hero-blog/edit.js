import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from '@wordpress/block-editor';
import './editor.css';
import {
	PanelBody,
	Button,
	SelectControl,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';

const SEGMENT_OPTIONS = [
	{ label: __( 'CropX Blue (General)', 'cropx' ),     value: 'cropx' },
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

function MediaPanel( {
	title, imageId, imageUrl, onSelect, onRemove, defaultLabel,
	show, onToggleShow, toggleLabel,
	scale, offsetX, offsetY,
	onScaleChange, onOffsetXChange, onOffsetYChange,
	offsetYHelp,
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
						min={ 40 } max={ 200 } step={ 1 }
					/>
					<RangeControl
						label={ __( 'Horizontal offset (px)', 'cropx' ) }
						help={ __( 'Positive → right, negative → left', 'cropx' ) }
						value={ offsetX ?? 0 }
						onChange={ onOffsetXChange }
						min={ -400 } max={ 400 } step={ 1 }
					/>
					<RangeControl
						label={ __( 'Vertical offset (px)', 'cropx' ) }
						help={ offsetYHelp || __( 'Positive → down, negative → up', 'cropx' ) }
						value={ offsetY ?? 0 }
						onChange={ onOffsetYChange }
						min={ -400 } max={ 400 } step={ 1 }
					/>
				</>
			) }
		</PanelBody>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, eyebrow, heading, subheading,
		bgImageId, bgImageUrl, bgFocalX, bgFocalY, bgZoom, bgFlipX,
		deviceImageId, deviceImageUrl,
		phoneImageId, phoneImageUrl,
		showEyebrow, showDeviceImage, showAppImage,
		deviceScale, deviceOffsetX, deviceOffsetY,
		phoneScale, phoneOffsetX, phoneOffsetY,
		textWidth,
		swoopFill,
	} = attributes;

	// PageSpeed fix (Aug 2026): real, cacheable drift-pattern URL instead of a
	// base64-inlined one — see render.php for the front-end half. Set on the
	// outermost element (ancestor of .shc-pattern) so it inherits down, since
	// the editor canvas has no .shc-bleed-wrap equivalent of its own — same
	// convention as hero-curved-standard.
	const blockProps = useBlockProps( {
		className: `shc-block hbl-block shc-segment-${ segment }`,
		style: {
			'--shc-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)`,
		},
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					<SelectControl
						label={ __( 'Swoop fill colour', 'cropx' ) }
						help={ __( 'Colour of the curved corner at the hero bottom-right. Auto reads the next section\'s background.', 'cropx' ) }
						value={ swoopFill ?? '' }
						options={ SWOOP_FILL_OPTIONS }
						onChange={ ( v ) => setAttributes( { swoopFill: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Text Width', 'cropx' ) } initialOpen={ false }>
					<p style={ { marginTop: 0, fontSize: '12px', color: '#757575' } }>
						{ __( 'Default is fine for most cases — only narrow this if a device/app overlay image is covering the headline or subheadline.', 'cropx' ) }
					</p>
					<RangeControl
						label={ __( 'Headline & subheadline width (%)', 'cropx' ) }
						value={ textWidth ?? 100 }
						onChange={ ( v ) => setAttributes( { textWidth: v } ) }
						min={ 40 }
						max={ 100 }
						step={ 5 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Segment', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the accent color and swoop stroke gradient.', 'cropx' ) }
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
					defaultLabel={ __( 'Select a background photo', 'cropx' ) }
				/>
				{ bgImageUrl && (
					<PanelBody title={ __( 'Background focal point & zoom', 'cropx' ) } initialOpen={ false }>
						<RangeControl
							label={ __( 'Focal X — left (%)', 'cropx' ) }
							value={ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }
							onChange={ ( v ) => setAttributes( { bgFocalX: v / 100 } ) }
							min={ 0 } max={ 100 }
						/>
						<RangeControl
							label={ __( 'Focal Y — top (%)', 'cropx' ) }
							value={ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }
							onChange={ ( v ) => setAttributes( { bgFocalY: v / 100 } ) }
							min={ 0 } max={ 100 }
						/>
						<RangeControl
							label={ __( 'Zoom (%)', 'cropx' ) }
							value={ bgZoom ?? 100 }
							onChange={ ( v ) => setAttributes( { bgZoom: v } ) }
							min={ 100 } max={ 200 }
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
						scale={ deviceScale } offsetX={ deviceOffsetX } offsetY={ deviceOffsetY }
						onScaleChange={ ( v ) => setAttributes( { deviceScale: v } ) }
						onOffsetXChange={ ( v ) => setAttributes( { deviceOffsetX: v } ) }
						onOffsetYChange={ ( v ) => setAttributes( { deviceOffsetY: v } ) }
					/>
					<MediaPanel
						title={ __( 'App/Software image', 'cropx' ) }
						toggleLabel={ __( 'Show app/software image', 'cropx' ) }
						show={ showAppImage }
						onToggleShow={ ( v ) => setAttributes( { showAppImage: v } ) }
						imageId={ phoneImageId }
						imageUrl={ phoneImageUrl }
						onSelect={ ( media ) => setAttributes( { phoneImageId: media.id, phoneImageUrl: media.url } ) }
						onRemove={ () => setAttributes( { phoneImageId: 0, phoneImageUrl: '' } ) }
						defaultLabel={ __( 'Default: phone-mockup-b', 'cropx' ) }
						scale={ phoneScale } offsetX={ phoneOffsetX } offsetY={ phoneOffsetY }
						onScaleChange={ ( v ) => setAttributes( { phoneScale: v } ) }
						onOffsetXChange={ ( v ) => setAttributes( { phoneOffsetX: v } ) }
						onOffsetYChange={ ( v ) => setAttributes( { phoneOffsetY: v } ) }
						offsetYHelp={ __( 'Positive → up, negative → down', 'cropx' ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="shc-bleed-wrap">
					<section className="shc-hero">
						{ /* PageSpeed fix (Aug 2026): real <img> instead of a CSS
						     background-image — see render.php for the front-end half. */ }
						<div className="shc-bg" style={ bgFlipX ? { transform: 'scaleX(-1)' } : undefined }>
							{ bgImageUrl && (
								<img
									src={ bgImageUrl }
									alt=""
									style={ {
										objectPosition: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }%`,
										// Flip is applied on the .shc-bg wrapper above (mirrored around its
									// own center) rather than here — see hero-curved-standard/edit.js.
									transform: `scale(${ ( ( bgZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
										transformOrigin: `${ Math.round( ( bgFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( bgFocalY ?? 0.3 ) * 100 ) }%`,
									} }
								/>
							) }
						</div>
						<div className="shc-overlay" />
						<div className="shc-pattern" />

						<div
							className="shc-content"
							style={ {
								'--shc-headline-w': ( textWidth ?? 100 ) / 100,
								'--shc-subhead-w':  ( textWidth ?? 100 ) / 100,
							} }
						>
							{ showEyebrow !== false && (
								<RichText
									tagName="p"
									className="shc-eyebrow"
									placeholder={ __( 'Eyebrow text…', 'cropx' ) }
									value={ eyebrow }
									onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
									allowedFormats={ [] }
								/>
							) }
							<RichText
								tagName="h1"
								className="shc-headline"
								placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/italic', 'core/bold' ] }
							/>
							<RichText
								tagName="p"
								className="shc-subheadline"
								placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
								value={ subheading }
								onChange={ ( v ) => setAttributes( { subheading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>

					</div>

						{ /* Swoop preview — simplified static version for editor */ }
						<div className="shc-swoop-preview" aria-hidden="true" />
					</section>
				</div>
			</div>
		</>
	);
}
