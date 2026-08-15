import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const POSITION_OPTIONS = [
	{ label: __( 'Visual right (default)', 'cropx' ), value: 'right' },
	{ label: __( 'Visual left',            'cropx' ), value: 'left'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const V_ANCHOR_OPTIONS = [
	{ label: __( 'Top of photo',    'cropx' ), value: 'top'    },
	{ label: __( 'Middle of photo', 'cropx' ), value: 'middle' },
	{ label: __( 'Bottom of photo', 'cropx' ), value: 'bottom' },
];

const H_ANCHOR_OPTIONS = [
	{ label: __( 'Hangs off left edge',   'cropx' ), value: 'left'   },
	{ label: __( 'Aligned with photo',    'cropx' ), value: 'center' },
	{ label: __( 'Hangs off right edge',  'cropx' ), value: 'right'  },
];

const DEFAULT_PAIR = {
	photoId: 0, photoUrl: '', photoAlt: '', photoFocalX: 0.5, photoFocalY: 0.5, photoZoom: 100,
	overlayId: 0, overlayUrl: '', overlayAlt: '', overlayWidth: 0, overlayHeight: 0, overlayScale: 75,
	overlayVAnchor: 'bottom', overlayHAnchor: 'center', overlayHOffset: 0, caption: '',
};

function MediaPanel( { label, thumbUrl, onSelect, onRemove } ) {
	return (
		<MediaUploadCheck>
			<MediaUpload
				onSelect={ onSelect }
				allowedTypes={ [ 'image' ] }
				render={ ( { open } ) => (
					<div style={ { display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '12px' } }>
						{ thumbUrl && (
							<img className="tcap-item-thumb" src={ thumbUrl } alt="" />
						) }
						<Button onClick={ open } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
							{ thumbUrl ? __( 'Replace', 'cropx' ) : label }
						</Button>
						{ thumbUrl && (
							<Button onClick={ onRemove } variant="link" isDestructive style={ { alignSelf: 'flex-start' } }>
								{ __( 'Remove', 'cropx' ) }
							</Button>
						) }
					</div>
				) }
			/>
		</MediaUploadCheck>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		photoPosition, segmentAccent,
		icon, eyebrow, heading, ctaLabel, ctaUrl,
		eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'white',
		mobileStack = 'visual-first',
		captionAlignment = 'left',
		pairs = [],
		pairHoldSeconds = 5,
	} = attributes;

	const isLeft = photoPosition === 'left';

	const blockProps = useBlockProps( {
		className:
			'tcap-section' +
			( isLeft ? ' tcap-section--visual-left' : '' ) +
			( segmentAccent !== 'general' ? ` tcap-segment-${ segmentAccent }` : '' ) +
			` tcap-section--bg-${ bgColor }`,
		style: bgColor === 'deep-blue'
			? { '--tcap-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );
	// Tracks which pair's sidebar panel is currently expanded, so the canvas
	// can show THAT pair instead of always defaulting to the first one with a
	// photo — lets Lauren open "Pair 2" and immediately see its anchor/size
	// settings reflected, rather than having to mentally map sidebar fields
	// to a photo that isn't the one she's editing.
	const [ openPairIdx, setOpenPairIdx ] = useState( 0 );

	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { pairs: reorderByDrag( pairs, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	function updatePair( idx, field, value ) {
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, [ field ]: value } : p ),
		} );
	}

	function selectPhoto( idx, media ) {
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } : p ),
		} );
	}
	function clearPhoto( idx ) {
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, photoId: 0, photoUrl: '', photoAlt: '' } : p ),
		} );
	}
	function selectOverlay( idx, media ) {
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? {
				...p,
				overlayId: media.id, overlayUrl: media.url, overlayAlt: media.alt ?? '',
				overlayWidth: media.width ?? 0, overlayHeight: media.height ?? 0,
			} : p ),
		} );
	}
	function clearOverlay( idx ) {
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, overlayId: 0, overlayUrl: '', overlayAlt: '' } : p ),
		} );
	}

	function addPair() {
		setAttributes( { pairs: [ ...pairs, { ...DEFAULT_PAIR } ] } );
	}
	function removePair( idx ) {
		setAttributes( { pairs: pairs.filter( ( _, i ) => i !== idx ) } );
	}

	// Prefer whichever pair's panel is currently open in the sidebar — lets
	// the canvas reflect the pair actually being edited. Falls back to the
	// first pair with a photo (e.g. nothing expanded) so the canvas still
	// shows something useful, and to null only when there are no pairs at
	// all.
	const selectedPair = ( openPairIdx !== null ) ? ( pairs[ openPairIdx ] ?? null ) : null;
	const previewPair = selectedPair ?? pairs.find( ( p ) => p.photoUrl ) ?? null;
	const previewPairNumber = previewPair ? pairs.indexOf( previewPair ) + 1 : null;
	const previewOverlayRatio = previewPair && previewPair.overlayWidth && previewPair.overlayHeight
		? previewPair.overlayWidth / previewPair.overlayHeight
		: 1.4;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White (default)', 'cropx' ), value: 'white' },
							{ label: __( 'Taupe 50',          'cropx' ), value: 'taupe' },
							{ label: __( 'Deep Blue + Topo',  'cropx' ), value: 'deep-blue' },
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
						label={ __( 'Visual position', 'cropx' ) }
						value={ photoPosition }
						options={ POSITION_OPTIONS }
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
					<SelectControl
						label={ __( 'Caption alignment', 'cropx' ) }
						help={ __( 'Aligns each pair\'s caption text under its photo/overlay.', 'cropx' ) }
						value={ captionAlignment }
						options={ [
							{ label: __( 'Left (default)', 'cropx' ), value: 'left'   },
							{ label: __( 'Center',          'cropx' ), value: 'center' },
						] }
						onChange={ ( v ) => setAttributes( { captionAlignment: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Animation', 'cropx' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'Hold time (seconds)', 'cropx' ) }
						help={ __( 'How long each pair stays fully in view before the overlay sinks out and the photo crossfades into the next pair.', 'cropx' ) }
						value={ pairHoldSeconds }
						onChange={ ( v ) => setAttributes( { pairHoldSeconds: v } ) }
						min={ 4 }
						max={ 6 }
						step={ 0.5 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Rotating Pairs', 'cropx' ) } initialOpen={ true }>
					<p style={ { fontSize: '12px', color: '#757575', marginTop: 0 } }>
						{ __( 'Only one pair shows at a time — the photo cross-fades in while its overlay rises up from the bottom, hold, then the overlay sinks away first and the photo cross-fades into the next pair. Drag to reorder.', 'cropx' ) }
					</p>
					{ pairs.map( ( pair, idx ) => (
						<div
							key={ idx }
							onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
							onDragLeave={ () => setDragOverIdx( null ) }
							onDrop={ () => dropItem( idx ) }
							onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
							style={ {
								borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx ? '2px solid var(--wp-admin-theme-color, #007cba)' : '2px solid transparent',
								opacity: dragIdx === idx ? 0.4 : 1,
								transition: 'opacity 0.1s',
							} }
						>
							<div className="tcap-item-row">
								<span
									className="tcap-item-drag-handle"
									draggable
									onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
									title={ __( 'Drag to reorder', 'cropx' ) }
								>⠿</span>
								<span className="tcap-item-label">
									{ __( 'Pair', 'cropx' ) } { idx + 1 }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { pairs: moveItem( pairs, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { pairs: moveItem( pairs, idx, 'down' ) } ) } disabled={ idx === pairs.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>
							<PanelBody
								title={ `${ __( 'Pair', 'cropx' ) } ${ idx + 1 }` }
								opened={ openPairIdx === idx }
								onToggle={ ( isOpen ) => setOpenPairIdx( isOpen ? idx : null ) }
							>
								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '4px', marginTop: 0 } }>
									{ __( 'Photo', 'cropx' ) }
								</p>
								<MediaPanel
									label={ __( 'Select photo', 'cropx' ) }
									thumbUrl={ pair.photoUrl }
									onSelect={ ( media ) => selectPhoto( idx, media ) }
									onRemove={ () => clearPhoto( idx ) }
								/>
								{ pair.photoUrl && (
									<>
										<RangeControl
											label={ __( 'Focal X — left (%)', 'cropx' ) }
											value={ Math.round( ( pair.photoFocalX ?? 0.5 ) * 100 ) }
											onChange={ ( v ) => updatePair( idx, 'photoFocalX', v / 100 ) }
											min={ 0 }
											max={ 100 }
										/>
										<RangeControl
											label={ __( 'Focal Y — top (%)', 'cropx' ) }
											value={ Math.round( ( pair.photoFocalY ?? 0.5 ) * 100 ) }
											onChange={ ( v ) => updatePair( idx, 'photoFocalY', v / 100 ) }
											min={ 0 }
											max={ 100 }
										/>
										<RangeControl
											label={ __( 'Zoom (%)', 'cropx' ) }
											value={ pair.photoZoom ?? 100 }
											onChange={ ( v ) => updatePair( idx, 'photoZoom', v ) }
											min={ 100 }
											max={ 200 }
										/>
									</>
								) }

								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '4px', marginTop: '16px' } }>
									{ __( 'Overlay PNG', 'cropx' ) }
								</p>
								<MediaPanel
									label={ __( 'Select overlay PNG', 'cropx' ) }
									thumbUrl={ pair.overlayUrl }
									onSelect={ ( media ) => selectOverlay( idx, media ) }
									onRemove={ () => clearOverlay( idx ) }
								/>
								{ pair.overlayUrl && (
									<>
										<SelectControl
											label={ __( 'Vertical anchor', 'cropx' ) }
											value={ pair.overlayVAnchor ?? 'bottom' }
											options={ V_ANCHOR_OPTIONS }
											onChange={ ( v ) => updatePair( idx, 'overlayVAnchor', v ) }
										/>
										<SelectControl
											label={ __( 'Horizontal anchor', 'cropx' ) }
											value={ pair.overlayHAnchor ?? 'center' }
											options={ H_ANCHOR_OPTIONS }
											onChange={ ( v ) => updatePair( idx, 'overlayHAnchor', v ) }
										/>
										{ ( pair.overlayHAnchor === 'left' || pair.overlayHAnchor === 'right' ) && (
											<RangeControl
												label={ __( 'Horizontal hang (px)', 'cropx' ) }
												value={ pair.overlayHOffset ?? 0 }
												onChange={ ( v ) => updatePair( idx, 'overlayHOffset', v ) }
												min={ 0 }
												max={ 40 }
												step={ 1 }
											/>
										) }
										<RangeControl
											label={ __( 'Size (%)', 'cropx' ) }
											help={ __( '100% means the overlay is exactly as tall as the photo. Above 100% it bleeds past whichever edge isn’t pinned by the vertical anchor.', 'cropx' ) }
											value={ pair.overlayScale ?? 75 }
											onChange={ ( v ) => updatePair( idx, 'overlayScale', v ) }
											min={ 20 }
											max={ 160 }
											step={ 1 }
										/>
									</>
								) }
								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '4px', marginTop: '16px' } }>
									{ __( 'Caption (optional)', 'cropx' ) }
								</p>
								<RichText
									tagName="div"
									className="tcap-caption-editor-field"
									placeholder={ __( 'Add a caption for this pair…', 'cropx' ) }
									value={ pair.caption ?? '' }
									onChange={ ( v ) => updatePair( idx, 'caption', v ) }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								/>
								<Button onClick={ () => removePair( idx ) } variant="link" isDestructive style={ { marginTop: '8px' } }>
									{ __( 'Remove this pair', 'cropx' ) }
								</Button>
							</PanelBody>
						</div>
					) ) }
					<Button onClick={ addPair } variant="secondary" style={ { width: '100%', justifyContent: 'center', marginTop: '8px' } }>
						{ __( '+ Add pair', 'cropx' ) }
					</Button>
				</PanelBody>

				<PanelBody title={ __( 'Call-to-action', 'cropx' ) } initialOpen={ false }>
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
				<div className="tcap-inner">
					<div className="tcap-grid">

						<div className="tcap-content">
							{ showIcon !== false && (
								<div className="tcap-icon-wrap">
									<div className="tcap-icon" aria-hidden="true">
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
									style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
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
									? <span className="tcap-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="tcap-cta tcap-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						<div className="tcap-visual">
							{ previewPair ? (
								<div className="tcap-pair" style={ { opacity: 1 } }>
									{ /* Overlay hang compensation — mirrors render.php: when the overlay hangs
										 past the photo's left/right edge, .tcap-media itself is indented on
										 that side (see .tcap-media--h-left/--h-right in style.css) so the
										 overlay's outer point lands at the original boundary instead of past it. */ }
									<div
										className={
											'tcap-media' +
											( previewPair.overlayUrl && ( previewPair.overlayHOffset ?? 0 ) > 0 && [ 'left', 'right' ].includes( previewPair.overlayHAnchor ?? 'center' )
												? ` tcap-media--h-${ previewPair.overlayHAnchor }`
												: ''
											)
										}
										style={ { '--tcap-h-offset': `${ previewPair.overlayHOffset ?? 0 }px` } }
									>
										<div className="tcap-photo-crop">
											<div className="tcap-photo" role="img" aria-label={ previewPair.photoAlt || undefined }>
												<div
													className="tcap-photo-bg"
													style={ {
														backgroundImage: `url('${ previewPair.photoUrl }')`,
														backgroundPosition: `${ Math.round( ( previewPair.photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( previewPair.photoFocalY ?? 0.5 ) * 100 ) }%`,
														transform: `scale(${ ( ( previewPair.photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
														transformOrigin: `${ Math.round( ( previewPair.photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( previewPair.photoFocalY ?? 0.5 ) * 100 ) }%`,
													} }
												/>
											</div>
										</div>
										{ previewPair.overlayUrl && (
											<div
												className={ `tcap-overlay tcap-overlay--v-${ previewPair.overlayVAnchor ?? 'bottom' } tcap-overlay--h-${ previewPair.overlayHAnchor ?? 'center' }` }
												role="img"
												aria-label={ previewPair.overlayAlt || undefined }
												style={ {
													backgroundImage: `url('${ previewPair.overlayUrl }')`,
													opacity: 1,
													'--tcap-overlay-scale': previewPair.overlayScale ?? 75,
													'--tcap-overlay-ratio': previewOverlayRatio,
													'--tcap-h-offset': `${ previewPair.overlayHOffset ?? 0 }px`,
												} }
											/>
										) }
									</div>
									{ previewPair.caption && (
										<RichText.Content
											tagName="div"
											className={ `tcap-caption${ captionAlignment === 'center' ? ' tcap-caption--centered' : '' }` }
											value={ previewPair.caption }
										/>
									) }
								</div>
							) : (
								<p style={ { fontSize: '12px', color: '#757575', textAlign: 'center', padding: '2rem 1rem' } }>
									{ __( 'Add rotating pairs from the sidebar →', 'cropx' ) }
								</p>
							) }
							{ pairs.length > 1 && previewPair && (
								<p className="tcap-preview-note">
									{ sprintf( __( 'Showing Pair %d — rotation plays on the front end.', 'cropx' ), previewPairNumber ) }
								</p>
							) }
						</div>

					</div>
				</div>
			</section>
		</>
	);
}
