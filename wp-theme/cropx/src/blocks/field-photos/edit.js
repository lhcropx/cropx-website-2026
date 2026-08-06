import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import './editor.css';

// Matches --radius-photo in tokens.css (60px 2px 60px 2px) — the sitewide
// default this block already renders with before any customization.
const PHOTO_RADIUS_PRESET = {
	topLeft:     '60px',
	topRight:    '2px',
	bottomRight: '60px',
	bottomLeft:  '2px',
};

export default function Edit( { attributes, setAttributes } ) {
	const {
		photos = [],
		eyebrow,
		heading,
		showEyebrow,
		showHeading,
		introBody,
		eyebrowColor,
		bgColor,
		autoAdvance,
		photoRadiusTopLeft:     radiusTL = '',
		photoRadiusTopRight:    radiusTR = '',
		photoRadiusBottomRight: radiusBR = '',
		photoRadiusBottomLeft:  radiusBL = '',
	} = attributes;

	const isRadiusCustomized = !! ( radiusTL || radiusTR || radiusBR || radiusBL );

	// Show the sitewide default in the control until something's customized,
	// so the inputs start where the carousel visually already is.
	const radiusValues = {
		topLeft:     radiusTL || PHOTO_RADIUS_PRESET.topLeft,
		topRight:    radiusTR || PHOTO_RADIUS_PRESET.topRight,
		bottomRight: radiusBR || PHOTO_RADIUS_PRESET.bottomRight,
		bottomLeft:  radiusBL || PHOTO_RADIUS_PRESET.bottomLeft,
	};

	// Always writes all four corners — once touched, the carousel's radius is
	// fully explicit rather than part-custom / part-inherited.
	function applyRadius( next ) {
		setAttributes( {
			photoRadiusTopLeft:     next.topLeft     ?? '',
			photoRadiusTopRight:    next.topRight    ?? '',
			photoRadiusBottomRight: next.bottomRight ?? '',
			photoRadiusBottomLeft:  next.bottomLeft  ?? '',
		} );
	}

	/* ── Photo management helpers ── */
	function addPhotos( mediaItems ) {
		const newPhotos = mediaItems.map( ( m ) => ( {
			id:      m.id,
			url:     m.url,
			alt:     m.alt ?? '',
			caption: '',
		} ) );
		setAttributes( { photos: [ ...photos, ...newPhotos ] } );
	}

	function removePhoto( index ) {
		const updated = photos.filter( ( _, i ) => i !== index );
		setAttributes( { photos: updated } );
	}

	function updateCaption( index, caption ) {
		const updated = photos.map( ( p, i ) => ( i === index ? { ...p, caption } : p ) );
		setAttributes( { photos: updated } );
	}

	function movePhoto( from, to ) {
		const updated = [ ...photos ];
		const [ item ] = updated.splice( from, 1 );
		updated.splice( to, 0, item );
		setAttributes( { photos: updated } );
	}

	const blockProps = useBlockProps( {
		className: `fph-section fph-section--bg-${ bgColor }`,
		style: isRadiusCustomized ? {
			'--fph-radius-tl': radiusTL || '0',
			'--fph-radius-tr': radiusTR || '0',
			'--fph-radius-br': radiusBR || '0',
			'--fph-radius-bl': radiusBL || '0',
		} : undefined,
	} );

	return (
		<>
			<InspectorControls>

				{ /* ── Section Settings ── */ }
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background color', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White (default)',  'cropx' ), value: 'white'     },
							{ label: __( 'Taupe 50',         'cropx' ), value: 'taupe'     },
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show heading', 'cropx' ) }
						checked={ showHeading !== false }
						onChange={ ( v ) => setAttributes( { showHeading: v } ) }
					/>
					<SelectControl
						label={ __( 'Eyebrow color', 'cropx' ) }
						value={ eyebrowColor ?? 'cropx-blue' }
						options={ [
							{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
							{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
						] }
						onChange={ ( v ) => setAttributes( { eyebrowColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Auto-advance', 'cropx' ) }
						help={ __( 'Automatically scrolls to the next photo every few seconds. Pauses on hover and while a visitor is interacting with the carousel.', 'cropx' ) }
						checked={ !! autoAdvance }
						onChange={ ( v ) => setAttributes( { autoAdvance: v } ) }
					/>
				</PanelBody>

				{ /* ── Corner Radius — applies to every photo in the carousel ──
				     Plain text inputs rather than WordPress's BorderRadiusControl:
				     that component's export has proven unreliable across WP/Gutenberg
				     versions (it crashed this whole block when opened), where a
				     TextControl is guaranteed to always exist. ── */ }
				<PanelBody title={ __( 'Corner Radius', 'cropx' ) } initialOpen={ false }>
					<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } }>
						<TextControl
							label={ __( 'Top left', 'cropx' ) }
							value={ radiusValues.topLeft }
							onChange={ ( v ) => applyRadius( { ...radiusValues, topLeft: v } ) }
						/>
						<TextControl
							label={ __( 'Top right', 'cropx' ) }
							value={ radiusValues.topRight }
							onChange={ ( v ) => applyRadius( { ...radiusValues, topRight: v } ) }
						/>
						<TextControl
							label={ __( 'Bottom left', 'cropx' ) }
							value={ radiusValues.bottomLeft }
							onChange={ ( v ) => applyRadius( { ...radiusValues, bottomLeft: v } ) }
						/>
						<TextControl
							label={ __( 'Bottom right', 'cropx' ) }
							value={ radiusValues.bottomRight }
							onChange={ ( v ) => applyRadius( { ...radiusValues, bottomRight: v } ) }
						/>
					</div>
					<p style={ { fontSize: '11px', color: '#757575', margin: '6px 0 0' } }>
						{ __( 'Enter any CSS length, e.g. 40px, 2px, 1rem, or 50%.', 'cropx' ) }
					</p>
					<div style={ { display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '12px' } }>
						<Button
							variant="secondary"
							size="small"
							onClick={ () => applyRadius( PHOTO_RADIUS_PRESET ) }
						>
							{ __( 'Use CropX Photo Radius', 'cropx' ) }
						</Button>
						{ isRadiusCustomized && (
							<Button
								variant="tertiary"
								size="small"
								onClick={ () => applyRadius( {} ) }
							>
								{ __( 'Reset to default', 'cropx' ) }
							</Button>
						) }
					</div>
				</PanelBody>

				{ /* ── Photos panel — all management lives here ── */ }
				<PanelBody title={ __( 'Photos', 'cropx' ) } initialOpen={ false }>

					{ photos.map( ( photo, index ) => (
						<div
							key={ photo.id ?? index }
							className="fph-panel-item"
							draggable
							onDragStart={ ( e ) => {
								e.dataTransfer.setData( 'text/plain', String( index ) );
								e.currentTarget.classList.add( 'is-dragging' );
							} }
							onDragEnd={ ( e ) => {
								e.currentTarget.classList.remove( 'is-dragging' );
							} }
							onDragOver={ ( e ) => e.preventDefault() }
							onDrop={ ( e ) => {
								e.preventDefault();
								const from = parseInt( e.dataTransfer.getData( 'text/plain' ), 10 );
								if ( from !== index ) movePhoto( from, index );
							} }
						>
							<div className="fph-panel-row">
								{ /* Grip handle — visible affordance; the whole row is already draggable */ }
								<span className="fph-panel-drag" aria-hidden="true">
									<svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<circle cx="3" cy="2.5"  r="1.5"/>
										<circle cx="7" cy="2.5"  r="1.5"/>
										<circle cx="3" cy="8"    r="1.5"/>
										<circle cx="7" cy="8"    r="1.5"/>
										<circle cx="3" cy="13.5" r="1.5"/>
										<circle cx="7" cy="13.5" r="1.5"/>
									</svg>
								</span>
								<div className="fph-panel-thumb">
									<img src={ photo.url } alt={ photo.alt } />
								</div>
								<div className="fph-panel-controls">
									<div className="fph-panel-reorder">
										<button
											type="button"
											disabled={ index === 0 }
											onClick={ () => movePhoto( index, index - 1 ) }
											aria-label={ __( 'Move up', 'cropx' ) }
										>↑</button>
										<span>{ index + 1 } / { photos.length }</span>
										<button
											type="button"
											disabled={ index === photos.length - 1 }
											onClick={ () => movePhoto( index, index + 1 ) }
											aria-label={ __( 'Move down', 'cropx' ) }
										>↓</button>
									</div>
									<button
										className="fph-panel-remove"
										type="button"
										onClick={ () => removePhoto( index ) }
										aria-label={ __( 'Remove photo', 'cropx' ) }
									>✕</button>
								</div>
							</div>
							<TextControl
								value={ photo.caption ?? '' }
								placeholder={ __( 'Caption (optional)…', 'cropx' ) }
								onChange={ ( v ) => updateCaption( index, v ) }
								__nextHasNoMarginBottom
							/>
						</div>
					) ) }

					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( items ) => {
								addPhotos( Array.isArray( items ) ? items : [ items ] );
							} }
							allowedTypes={ [ 'image' ] }
							multiple
							value={ photos.map( ( p ) => p.id ) }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									className="fph-panel-add-btn"
								>
									{ photos.length === 0
										? __( '+ Add photos', 'cropx' )
										: __( '+ Add more photos', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>

					{ photos.length === 0 && (
						<p className="fph-panel-empty">
							{ __( 'No photos yet. Click above to choose images from the Media Library.', 'cropx' ) }
						</p>
					) }

				</PanelBody>


			</InspectorControls>

			{ /* ── Canvas — header inputs + read-only thumbnail preview ── */ }
			<section { ...blockProps }>

				{ ( showEyebrow !== false || showHeading !== false || introBody ) && (
					<div className="fph-inner">
						<div className="fph-header">
							{ showEyebrow !== false && (
								<input
									className="fph-eyebrow-input"
									type="text"
									value={ eyebrow }
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									style={ {
										background: 'transparent',
										border: 'none',
										boxShadow: 'none',
										textAlign: 'center',
										color: bgColor === 'deep-blue'
											? 'rgba(255,255,255,0.7)'
											: `var(--${ eyebrowColor ?? 'cropx-blue' })`,
									} }
									onChange={ ( e ) => setAttributes( { eyebrow: e.target.value } ) }
								/>
							) }
							{ showHeading !== false && (
								<input
									className="fph-heading-input"
									type="text"
									value={ heading }
									placeholder={ __( 'Heading…', 'cropx' ) }
									style={ {
										background: 'transparent',
										border: 'none',
										boxShadow: 'none',
										textAlign: 'center',
										color: bgColor === 'deep-blue'
											? 'var(--white, #fff)'
											: 'var(--deep-blue, #243565)',
									} }
									onChange={ ( e ) => setAttributes( { heading: e.target.value } ) }
								/>
							) }
							<RichText
								tagName="div"
								className="section-body"
								placeholder={ __( 'Intro body text (optional)…', 'cropx' ) }
								value={ introBody }
								onChange={ ( v ) => setAttributes( { introBody: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
						</div>
					</div>
				) }

				{ /* Read-only thumbnail strip — visual preview only, no controls */ }
				{ photos.length > 0 ? (
					<div className="fph-canvas-preview">
						{ photos.map( ( photo, i ) => (
							<div key={ photo.id ?? i } className="fph-canvas-thumb">
								<img src={ photo.url } alt={ photo.alt } />
							</div>
						) ) }
					</div>
				) : (
					<div className="fph-canvas-empty">
						<p>{ __( '← Add photos using the Photos panel in the block settings', 'cropx' ) }</p>
					</div>
				) }

			</section>
		</>
	);
}
