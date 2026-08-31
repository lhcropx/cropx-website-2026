import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
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

import { moveItem, reorderByDrag } from '../../shared/reorder';
import CtaLinkControl from '../../shared/CtaLinkControl';

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
	imageId, imageUrl, onSelect, onRemove, defaultLabel,
} ) {
	return (
		<MediaUploadCheck>
			<MediaUpload
				onSelect={ onSelect }
				allowedTypes={ [ 'image' ] }
				value={ imageId }
				render={ ( { open } ) => (
					<div style={ { display: 'flex', flexDirection: 'column', gap: '8px' } }>
						{ imageUrl && (
							<img className="shca-item-thumb" src={ imageUrl } alt="" />
						) }
						<Button onClick={ open } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
							{ imageId ? __( 'Replace image', 'cropx' ) : __( 'Select image', 'cropx' ) }
						</Button>
						{ ! imageId && defaultLabel && (
							<p style={ { margin: 0, fontSize: '11px', color: '#757575', lineHeight: '1.4' } }>
								{ defaultLabel }
							</p>
						) }
						{ imageId > 0 && (
							<Button onClick={ onRemove } variant="link" isDestructive style={ { alignSelf: 'flex-start' } }>
								{ __( 'Remove image', 'cropx' ) }
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
		segment, eyebrow, heading, subheading,
		ctaLabel, ctaUrl, ctaLinkType, ctaFileId, ctaFileUrl,
		cta2Label, cta2Url, showCta2, cta2LinkType, cta2FileId, cta2FileUrl,
		bgImageId, bgImageUrl,
		bgFocalX, bgFocalY, bgZoom, bgFlipX,
		showEyebrow, showCta,
		pairs = [],
		pairHoldSeconds = 5,
		textWidth,
		swoopFill,
	} = attributes;

	// Same pattern-URL-via-CSS-var trick as hero-curved-standard — see that
	// block's edit.js/render.php comments for why (avoids a ~90KB base64 SVG
	// bloating style-index.css, which broke WP File Manager zip deploys).
	const blockProps = useBlockProps( {
		className: `shc-block shc-segment-${ segment }`,
		style: {
			'--shc-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)`,
		},
	} );

	// ── Drag-and-drop reorder state — also determines animation order ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

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

	// slot is 'device' or 'phone' — maps to this pair's deviceId/deviceUrl or
	// phoneId/phoneUrl fields.
	function selectPairImage( idx, slot, media ) {
		const idField = slot === 'phone' ? 'phoneId' : 'deviceId';
		const urlField = slot === 'phone' ? 'phoneUrl' : 'deviceUrl';
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, [ idField ]: media.id, [ urlField ]: media.url } : p ),
		} );
	}

	function clearPairImage( idx, slot ) {
		const idField = slot === 'phone' ? 'phoneId' : 'deviceId';
		const urlField = slot === 'phone' ? 'phoneUrl' : 'deviceUrl';
		setAttributes( {
			pairs: pairs.map( ( p, i ) => i === idx ? { ...p, [ idField ]: 0, [ urlField ]: '' } : p ),
		} );
	}

	function addPair() {
		setAttributes( {
			pairs: [ ...pairs, {
				deviceId: 0, deviceUrl: '', deviceScale: 100, deviceOffsetX: 0, deviceOffsetY: 0,
				phoneId: 0, phoneUrl: '', phoneScale: 100, phoneOffsetX: 0, phoneOffsetY: 0,
			} ],
		} );
	}

	function removePair( idx ) {
		setAttributes( { pairs: pairs.filter( ( _, i ) => i !== idx ) } );
	}

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

				<PanelBody title={ __( 'Background image', 'cropx' ) } initialOpen={ false }>
					<MediaPanel
						imageId={ bgImageId }
						imageUrl={ bgImageUrl }
						onSelect={ ( media ) => setAttributes( { bgImageId: media.id, bgImageUrl: media.url } ) }
						onRemove={ () => setAttributes( { bgImageId: 0, bgImageUrl: '' } ) }
						defaultLabel={ __( 'Select background image', 'cropx' ) }
					/>
					{ bgImageUrl && (
						<>
							<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
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
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Animation', 'cropx' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'Pair hold time (seconds)', 'cropx' ) }
						help={ __( 'How long each device + app pair stays on screen before the next pair takes its turn.', 'cropx' ) }
						value={ pairHoldSeconds }
						onChange={ ( v ) => setAttributes( { pairHoldSeconds: v } ) }
						min={ 4 }
						max={ 6 }
						step={ 0.5 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Device & app pairs', 'cropx' ) } initialOpen={ true }>
					<p style={ { fontSize: '12px', color: '#757575', marginTop: 0 } }>
						{ __( 'Only one pair shows on screen at a time — they cycle through in the order listed below, then loop back to the first. Drag to reorder. Leave the app image blank to show just the device for that pair.', 'cropx' ) }
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
							<div className="shca-item-row">
								<span
									className="shca-item-drag-handle"
									draggable
									onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
									title={ __( 'Drag to reorder', 'cropx' ) }
								>⠿</span>
								<span className="shca-item-label">
									{ __( 'Pair', 'cropx' ) } { idx + 1 }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { pairs: moveItem( pairs, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { pairs: moveItem( pairs, idx, 'down' ) } ) } disabled={ idx === pairs.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>
							<PanelBody title={ `${ __( 'Pair', 'cropx' ) } ${ idx + 1 }` } initialOpen={ idx === 0 }>
								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px' } }>
									{ __( 'Device / sensor image', 'cropx' ) }
								</p>
								<MediaPanel
									imageId={ pair.deviceId }
									imageUrl={ pair.deviceUrl }
									onSelect={ ( media ) => selectPairImage( idx, 'device', media ) }
									onRemove={ () => clearPairImage( idx, 'device' ) }
									defaultLabel={ __( 'Default: vertex-partial-a', 'cropx' ) }
								/>
								<RangeControl
									label={ __( 'Size (%)', 'cropx' ) }
									value={ pair.deviceScale ?? 100 }
									onChange={ ( v ) => updatePair( idx, 'deviceScale', v ) }
									min={ 40 }
									max={ 500 }
									step={ 1 }
								/>
								<RangeControl
									label={ __( 'Horizontal offset (px)', 'cropx' ) }
									help={ __( 'Positive → right, negative → left', 'cropx' ) }
									value={ pair.deviceOffsetX ?? 0 }
									onChange={ ( v ) => updatePair( idx, 'deviceOffsetX', v ) }
									min={ -400 }
									max={ 400 }
									step={ 1 }
								/>
								<RangeControl
									label={ __( 'Vertical offset (px)', 'cropx' ) }
									help={ __( 'Positive → down, negative → up', 'cropx' ) }
									value={ pair.deviceOffsetY ?? 0 }
									onChange={ ( v ) => updatePair( idx, 'deviceOffsetY', v ) }
									min={ -400 }
									max={ 400 }
									step={ 1 }
								/>

								<hr style={ { margin: '16px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />

								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px' } }>
									{ __( 'App / software image (optional)', 'cropx' ) }
								</p>
								<MediaPanel
									imageId={ pair.phoneId }
									imageUrl={ pair.phoneUrl }
									onSelect={ ( media ) => selectPairImage( idx, 'phone', media ) }
									onRemove={ () => clearPairImage( idx, 'phone' ) }
									defaultLabel={ __( 'Left blank — this pair will show just the device', 'cropx' ) }
								/>
								{ ( pair.phoneId || pair.phoneUrl ) && (
									<>
										<RangeControl
											label={ __( 'Size (%)', 'cropx' ) }
											value={ pair.phoneScale ?? 100 }
											onChange={ ( v ) => updatePair( idx, 'phoneScale', v ) }
											min={ 40 }
											max={ 400 }
											step={ 1 }
										/>
										<RangeControl
											label={ __( 'Horizontal offset (px)', 'cropx' ) }
											help={ __( 'Positive → right, negative → left', 'cropx' ) }
											value={ pair.phoneOffsetX ?? 0 }
											onChange={ ( v ) => updatePair( idx, 'phoneOffsetX', v ) }
											min={ -400 }
											max={ 400 }
											step={ 1 }
										/>
										<RangeControl
											label={ __( 'Vertical offset (px)', 'cropx' ) }
											help={ __( 'Positive → up, negative → down', 'cropx' ) }
											value={ pair.phoneOffsetY ?? 0 }
											onChange={ ( v ) => updatePair( idx, 'phoneOffsetY', v ) }
											min={ -400 }
											max={ 400 }
											step={ 1 }
										/>
									</>
								) }

								<hr style={ { margin: '16px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />

								<Button onClick={ () => removePair( idx ) } variant="link" isDestructive>
									{ __( 'Remove this pair', 'cropx' ) }
								</Button>
							</PanelBody>
						</div>
					) ) }
					<Button onClick={ addPair } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
						{ __( '+ Add pair', 'cropx' ) }
					</Button>
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
				{ /* Hero */ }
				<div className="shc-hero">
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
						{ showCta !== false && ctaLabel && (
							<div className="shc-cta-row">
								<span className="shc-cta" aria-hidden="true">
									{ ctaLabel }
								</span>
								{ showCta2 && cta2Label && (
									<span className="shc-cta--ghost" aria-hidden="true">
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
					{ /* Swoop preview — simplified static version for editor. Device/phone
					     images are configured via the sidebar thumbnails above rather than
					     positioned live on the canvas, matching hero-curved-standard's
					     editor behavior (see that block's edit.js for the same choice). */ }
					<div className="shc-swoop-preview" aria-hidden="true" />
				</div>
			</div>
		</>
	);
}
