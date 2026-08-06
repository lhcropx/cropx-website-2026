import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	MediaPlaceholder,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	RangeControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

const PHOTO_POSITION_OPTIONS = [
	{ label: __( 'Photo right (default)', 'cropx' ), value: 'right' },
	{ label: __( 'Photo left',            'cropx' ), value: 'left'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

// The 8 positions the overlay is allowed to hang from — 4 edges + 4 corners,
// laid out on a compass so each button sits where it points (top-left button
// at the box's top-left corner, etc.). There's deliberately no "centered/
// contained" 9th option in the middle — every preset must hang off the photo
// by design (see the comment above OverlayCompassPicker for the full rule).
const OVERLAY_POSITIONS = [
	{ key: 'top-left',     top: 0,   left: 0,   arrow: '↖' },
	{ key: 'top',          top: 0,   left: 50,  arrow: '↑' },
	{ key: 'top-right',    top: 0,   left: 100, arrow: '↗' },
	{ key: 'left',         top: 50,  left: 0,   arrow: '←' },
	{ key: 'right',        top: 50,  left: 100, arrow: '→' },
	{ key: 'bottom-left',  top: 100, left: 0,   arrow: '↙' },
	{ key: 'bottom',       top: 100, left: 50,  arrow: '↓' },
	{ key: 'bottom-right', top: 100, left: 100, arrow: '↘' },
];

// A compass picker instead of a free-form grid: the overlay's position is
// always one of 8 fixed presets (see OVERLAY_POSITIONS), each hanging a
// fixed 40px off its edge/corner — never a free X/Y coordinate — so this is
// just 8 real <button>s (keyboard-accessible for free, unlike the old
// click-anywhere grid) laid out on the photo's own corners/edge-midpoints.
function OverlayCompassPicker( { value, onChange } ) {
	// The buttons below sit at 0%/50%/100% on each axis with
	// translate(-50%, -50%), so any button on an edge (top/bottom/left/
	// right row) ends up HALF outside the box itself — an 11px overflow
	// (half of the 22px button). Left unaccounted for, that overflow was
	// eating into the spacing before whatever follows (the "hang off top &
	// bottom" toggle), causing the two to visually collide. Wrapping the box
	// in this padded container absorbs that overflow into the wrapper's own
	// box model instead, so normal spacing rules apply correctly to
	// whatever comes next.
	return (
		<div style={ { padding: '11px 11px 21px' } }>
			<div
				style={ {
					position: 'relative',
					width: '100%',
					aspectRatio: '5 / 4',
					background: '#e3e1dc',
					border: '1px solid #ccc',
					borderRadius: '2px',
				} }
			>
				{ OVERLAY_POSITIONS.map( ( pos ) => {
				const isSelected = pos.key === value;
				return (
					<button
						key={ pos.key }
						type="button"
						aria-label={ pos.key.replace( '-', ' ' ) }
						aria-pressed={ isSelected }
						onClick={ () => onChange( pos.key ) }
						style={ {
							position: 'absolute',
							top: `${ pos.top }%`,
							left: `${ pos.left }%`,
							transform: 'translate(-50%, -50%)',
							width: '22px',
							height: '22px',
							borderRadius: '50%',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							padding: 0,
							fontSize: '12px',
							lineHeight: 1,
							cursor: 'pointer',
							background: isSelected ? '#0CA8C0' : '#fff',
							border: isSelected ? '1px solid #0A8FA3' : '1px solid #999',
							color: isSelected ? '#fff' : '#555',
						} }
					>
						{ pos.arrow }
					</button>
				);
			} ) }
			</div>
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		photoPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
		photoFocalX, photoFocalY, photoZoom,
		overlayId, overlayUrl, overlayAlt,
		overlayWidth = 0, overlayHeight = 0, overlayScale = 75,
		overlayPosition = 'left', overlayFullBleed = false, overlayCornerRadius = 10, eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'taupe',
		mobileStack = 'visual-first',
	} = attributes;

	const isLeft = photoPosition === 'left';

	const overlayRatio = ( overlayWidth > 0 && overlayHeight > 0 ) ? overlayWidth / overlayHeight : 1.4;

	// Size-ceiling math. The overlay always hangs a FIXED 40px (2.5rem) off
	// its chosen edge/corner, and — on any preset that touches a left/right
	// side — must stay at least 40px from the photo's far edge/corner too.
	// Because the hang amount and the minimum far-side gap are the exact same
	// constant, they cancel out algebraically to one clean rule: the
	// overlay's width can never exceed the photo's own width. And since the
	// overlay's width and the photo's width both scale together with the
	// same fluid column (photo height = width x 0.8 via its fixed 5:4
	// aspect-ratio, overlay height = Scale% of photo height, overlay width =
	// overlay height x its own ratio), that rule reduces to a PURE ratio
	// check — no live measurement needed, unlike the old free-position model
	// this replaced. Top/bottom-only hangs have no such constraint: height
	// alone governs Size, and it's already bounded at 100%.
	//
	// 125 = 100 / 0.8, where 0.8 is .tco-photo's fixed aspect-ratio (5/4)
	// expressed as height/width — see the matching comment in style.css and
	// render.php. If that aspect-ratio ever changes, update this constant.
	// Full bleed only ever combines with 'left' or 'right' — also hanging
	// the overlay 40px off the top and bottom, which locks height to the
	// photo's height + 80px (via CSS top+bottom, see style.css) instead of
	// Scale. Once height is locked independently of Scale, Scale switches
	// to controlling WIDTH directly (0-100% of the photo's own width), and
	// needs no dynamic cap the way the 6 non-full-bleed side-touching
	// positions do: at Scale=100% the far edge lands EXACTLY 40px inside
	// the photo's opposite edge (the minimum-gap rule at its boundary, not
	// past it), and any lower Scale only adds more room.
	const isFullBleed = overlayFullBleed && ( overlayPosition === 'left' || overlayPosition === 'right' );

	const NEEDS_WIDTH_CAP_POSITIONS = [ 'left', 'right', 'top-left', 'top-right', 'bottom-left', 'bottom-right' ];
	const needsWidthCap = ! isFullBleed && NEEDS_WIDTH_CAP_POSITIONS.includes( overlayPosition );
	const scaleMaxForWidth = 125 / overlayRatio;
	const dynamicSizeMax = needsWidthCap
		? Math.max( 10, Math.min( 100, Math.floor( scaleMaxForWidth ) ) )
		: 100;

	// Text-column nudge — see the matching comment in render.php. Purely
	// for the live canvas preview here; the actual attribute-driven markup
	// is what render.php outputs on the front end.
	const overlayTouchesRight = overlayPosition === 'right' || overlayPosition === 'top-right' || overlayPosition === 'bottom-right';
	const overlayTouchesLeft = overlayPosition === 'left' || overlayPosition === 'top-left' || overlayPosition === 'bottom-left';
	let contentNudgeClass = '';
	if ( overlayUrl ) {
		if ( photoPosition === 'left' && overlayTouchesRight ) {
			contentNudgeClass = ' tco-content--nudge-left';
		} else if ( photoPosition !== 'left' && overlayTouchesLeft ) {
			contentNudgeClass = ' tco-content--nudge-right';
		}
	}

	// Block-edge spacing — see the matching comment in render.php. Whenever
	// the overlay hangs (or full-bleeds) over the photo's top or bottom
	// edge, the whole section gets an extra 40px of padding on that side —
	// on top of whatever gap the grid's own vertical centering already
	// gives it, not a minimum/cap. Full bleed always hangs both top AND
	// bottom regardless of which of left/right it's paired with.
	const TOP_BLEED_POSITIONS = [ 'top', 'top-left', 'top-right' ];
	const BOTTOM_BLEED_POSITIONS = [ 'bottom', 'bottom-left', 'bottom-right' ];
	const overlayBleedsTop = !! overlayUrl && ( isFullBleed || TOP_BLEED_POSITIONS.includes( overlayPosition ) );
	const overlayBleedsBottom = !! overlayUrl && ( isFullBleed || BOTTOM_BLEED_POSITIONS.includes( overlayPosition ) );

	// See style.css's comment on .tco-section--bg-deep-blue::before — keeps
	// the drift-pattern SVG out of the compiled CSS bundle. Set here too so
	// the editor preview matches.
	const blockProps = useBlockProps( {
		className:
			'tco-section' +
			( isLeft ? ' tco-section--photo-left' : '' ) +
			( segmentAccent !== 'general' ? ` tco-segment-${ segmentAccent }` : '' ) +
			( overlayBleedsTop ? ' tco-section--bleed-top' : '' ) +
			( overlayBleedsBottom ? ' tco-section--bleed-bottom' : '' ) +
			` tco-section--bg-${ bgColor }`,
		style: bgColor === 'deep-blue'
			? { '--tco-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	function onSelectPhoto( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}
	function onRemovePhoto() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}
	function onSelectOverlay( media ) {
		setAttributes( {
			overlayId: media.id,
			overlayUrl: media.url,
			overlayAlt: media.alt ?? '',
			// Real dimensions — used to size the overlay by its actual aspect
			// ratio instead of a hardcoded guess, and to decide which side
			// (width or height) the 500px mobile ceiling applies to.
			overlayWidth: media.width ?? 0,
			overlayHeight: media.height ?? 0,
		} );
	}
	function onRemoveOverlay() {
		setAttributes( { overlayId: 0, overlayUrl: '', overlayAlt: '' } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'White',               'cropx' ), value: 'white' },
							{ label: __( 'Deep Blue',           'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the icon box background.', 'cropx' ) }
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
						label={ __( 'Show icon', 'cropx' ) }
						checked={ showIcon !== false }
						onChange={ ( v ) => setAttributes( { showIcon: v } ) }
					/>
					{ showIcon !== false && (
						<>
							<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px', marginTop: '16px' } }>
								{ __( 'Icon', 'cropx' ) }
							</p>
							<IconPicker
								value={ icon }
								onChange={ ( v ) => setAttributes( { icon: v } ) }
							/>
						</>
					) }
					<ToggleControl
						label={ __( 'Show CTA', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Photo Positioning', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Photo position', 'cropx' ) }
						value={ photoPosition }
						options={ PHOTO_POSITION_OPTIONS }
						onChange={ ( v ) => setAttributes( { photoPosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Mobile stack order', 'cropx' ) }
						help={ __( 'Which column appears first when the layout collapses to one column.', 'cropx' ) }
						value={ mobileStack }
						options={ [
							{ label: __( 'Visual on top (default)', 'cropx' ), value: 'visual-first' },
							{ label: __( 'Text on top',             'cropx' ), value: 'text-first'   },
						] }
						onChange={ ( v ) => setAttributes( { mobileStack: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Photo', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectPhoto }
							allowedTypes={ [ 'image' ] }
							value={ photoId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '8px' } }
								>
									{ photoUrl
										? __( 'Replace photo', 'cropx' )
										: __( 'Select photo', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ photoUrl && (
						<Button onClick={ onRemovePhoto } variant="link" isDestructive>
							{ __( 'Remove', 'cropx' ) }
						</Button>
					) }
					{ photoUrl && (
						<>
							<RangeControl
								label={ __( 'Focal X — left (%)', 'cropx' ) }
								value={ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { photoFocalX: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Focal Y — top (%)', 'cropx' ) }
								value={ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { photoFocalY: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Zoom (%)', 'cropx' ) }
								value={ photoZoom ?? 100 }
								onChange={ ( v ) => setAttributes( { photoZoom: v } ) }
								min={ 100 }
								max={ 200 }
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Overlay PNG', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectOverlay }
							allowedTypes={ [ 'image' ] }
							value={ overlayId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '8px' } }
								>
									{ overlayUrl
										? __( 'Replace overlay PNG', 'cropx' )
										: __( 'Select overlay PNG', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ overlayUrl && (
						<Button onClick={ onRemoveOverlay } variant="link" isDestructive>
							{ __( 'Remove', 'cropx' ) }
						</Button>
					) }
					{ photoUrl && overlayUrl && (
						<>
							<p style={ { fontSize: '11px', fontWeight: 600, color: '#1e1e1e', marginBottom: '4px', marginTop: '8px' } }>
								{ __( 'Position', 'cropx' ) }
							</p>
							<p style={ { fontSize: '11px', color: '#757575', marginTop: 0, marginBottom: '8px', lineHeight: 1.4 } }>
								{ __( 'The overlay always hangs 40px off whichever edge or corner you pick.', 'cropx' ) }
							</p>
							<OverlayCompassPicker
								value={ overlayPosition }
								onChange={ ( v ) => setAttributes( { overlayPosition: v } ) }
							/>
							{ ( overlayPosition === 'left' || overlayPosition === 'right' ) && (
								<ToggleControl
									label={ __( 'Also hang off top & bottom', 'cropx' ) }
									help={ __( 'Extends the overlay 40px past those edges too.', 'cropx' ) }
									checked={ overlayFullBleed }
									onChange={ ( v ) => setAttributes( { overlayFullBleed: v } ) }
								/>
							) }
							<RangeControl
								label={ __( 'Size (%)', 'cropx' ) }
								help={
									isFullBleed
										? __( "Height is locked to the photo's height + 80px. This controls the frame's width — the image scales to fit inside without ever being cropped.", 'cropx' )
										: dynamicSizeMax < 100
											? `Capped at ${ dynamicSizeMax }% so it stays at least 40px from the far edge.`
											: __( '100% means the overlay is exactly as tall as the background photo.', 'cropx' )
								}
								value={ Math.min( overlayScale, dynamicSizeMax ) }
								onChange={ ( v ) => setAttributes( { overlayScale: v } ) }
								min={ 10 }
								max={ dynamicSizeMax }
								step={ 1 }
							/>
							<RangeControl
								label={ __( 'Corner rounding (px)', 'cropx' ) }
								help={ __( 'Rounds all 4 corners of the overlay PNG.', 'cropx' ) }
								value={ overlayCornerRadius }
								onChange={ ( v ) => setAttributes( { overlayCornerRadius: v } ) }
								min={ 0 }
								max={ 20 }
								step={ 1 }
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'CTA', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'CTA style', 'cropx' ) }
						value={ ctaStyle ?? 'button' }
						options={ [
							{ label: __( 'Button',               'cropx' ), value: 'button' },
							{ label: __( 'Text link with arrow', 'cropx' ), value: 'link'   },
						] }
						onChange={ ( val ) => setAttributes( { ctaStyle: val } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
				</PanelBody>

			</InspectorControls>

			<section { ...blockProps }>
				<div className="tco-inner">
					<div className="tco-grid">

						<div className={ `tco-content${ contentNudgeClass }` }>
							{ showIcon !== false && (
								<div className="tco-icon-wrap">
									<div className="tco-icon" aria-hidden="true">
										<img src={ iconSrc( icon ) } alt="" width="28" height="28" />
									</div>
								</div>
							) }
							{ showEyebrow !== false && (
								<RichText
									tagName="span"
									className="section-eyebrow"
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									value={ eyebrow }
									onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
									allowedFormats={ [] }
									style={{ color: `var(--${ eyebrowColor ?? 'cropx-blue' })` }}
								/>
							) }
							<RichText
								tagName="h2"
								className="section-heading"
								placeholder={ __( 'Heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<div className="section-body">
								<InnerBlocks
									allowedBlocks={ [ 'core/paragraph', 'core/list', 'core/heading' ] }
									template={ [ [ 'core/paragraph', { placeholder: __( 'Body text…', 'cropx' ) } ] ] }
									templateLock={ false }
								/>
							</div>
							{ showCta !== false && ctaLabel && (
								ctaStyle === 'link'
									? <span className="tco-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="tco-cta tco-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						{ /* Visual column — progressive: no photo → placeholder; photo only → photo + add-overlay button; both → full composition */ }
						<div className="tco-visual">
							{ photoUrl ? (
								<>
									<div
										className="tco-photo"
										role="img"
										aria-label={ photoAlt || undefined }
									>
										{ /* Inner bg div gets zoom transform; .tco-photo (overflow:hidden)
										     clips it so the outer frame never grows. */ }
										<div
											className="tco-photo-bg"
											style={ {
												backgroundImage: `url('${ photoUrl }')`,
												backgroundPosition: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
												transform: `scale(${ ( ( photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
												transformOrigin: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
											} }
										/>
									</div>
									{ overlayUrl ? (
										<div
											className={ `tco-overlay tco-overlay--${ overlayPosition }${ isFullBleed ? ' tco-overlay--full-bleed' : '' }` }
											role="img"
											aria-label={ overlayAlt || undefined }
											style={ {
												backgroundImage: `url('${ overlayUrl }')`,
												// Raw values only — the fixed 40px hang per position lives in
												// style.css's tco-overlay--* modifier classes, not inline here.
												'--tco-overlay-scale': overlayScale,
												'--tco-overlay-ratio': overlayRatio,
												'--tco-overlay-radius': `${ overlayCornerRadius }px`,
											} }
										/>
									) : (
										<MediaUploadCheck>
											<MediaUpload
												onSelect={ onSelectOverlay }
												allowedTypes={ [ 'image' ] }
												value={ overlayId }
												render={ ( { open } ) => (
													<Button
														onClick={ open }
														variant="primary"
														className="tco-overlay-add-btn"
													>
														{ __( 'Add overlay PNG', 'cropx' ) }
													</Button>
												) }
											/>
										</MediaUploadCheck>
									) }
								</>
							) : (
								<MediaPlaceholder
									onSelect={ onSelectPhoto }
									allowedTypes={ [ 'image' ] }
									accept="image/*"
									labels={ { title: __( 'Feature photo', 'cropx' ) } }
								/>
							) }
						</div>

					</div>
				</div>
			</section>
		</>
	);
}
