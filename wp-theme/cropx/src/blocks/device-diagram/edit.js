/**
 * Interactive Device Diagram block — editor UI.
 *
 * All content is edited in the sidebar (section settings, phone screen,
 * product list). The canvas is a live-looking preview that mirrors render.php's
 * DOM and shows the DESKTOP diagram. The "active" product in the canvas is
 * simply whichever product is open in the sidebar (or clicked on the canvas) —
 * that lets you see the scaled card, the blue connector line, the phone screen
 * and the caption exactly as a hover would show them on the front end.
 *
 * Hover/scroll/swipe behaviour and the mobile carousel only run on the front
 * end (view.js) and can't be previewed here.
 */

import { __, sprintf } from '@wordpress/i18n';
import { useState, useRef, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import { drawLines } from './lines';
import './editor.css';

const OVERLAY_TYPE_OPTIONS = [
	{ label: __( 'None', 'cropx' ), value: 'none' },
	{ label: __( 'Contained (inside the photo half)', 'cropx' ), value: 'contained' },
	{ label: __( 'Card-bleed (overhangs the card)', 'cropx' ), value: 'card-bleed' },
];

const MIN_ITEMS = 4;
const MAX_ITEMS = 8;

const ArrowSvg = () => (
	<svg viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

// Allow only <em>/<br> in the canvas heading preview (matches render.php's wp_kses).
const safeHeading = ( html ) => ( html || '' ).replace( /<(?!\/?(em|br)\b)[^>]*>/gi, '' );

function blankItem() {
	return {
		name: '', softwareLabel: '', description: '', url: '#',
		photoId: 0, photoUrl: '', photoAlt: '', photoFocalX: 0.5, photoFocalY: 0.5, photoZoom: 100,
		overlayId: 0, overlayUrl: '', overlayType: 'none', overlayPadding: 0, overlayH: 100, overlayX: 0, overlayY: 0, overlayCentered: false, overlayAnchor: 'center', overlayTopBleed: 0,
		screenId: 0, screenUrl: '',
	};
}

function ImagePicker( { id, url, onSelect, onRemove, selectLabel, replaceLabel, removeLabel } ) {
	return (
		<>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onSelect }
					allowedTypes={ [ 'image' ] }
					value={ id }
					render={ ( { open } ) => (
						<Button
							onClick={ open }
							variant="secondary"
							style={ { marginBottom: '4px', display: 'block', width: '100%', justifyContent: 'center' } }
						>
							{ url ? replaceLabel : selectLabel }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ url && (
				<Button onClick={ onRemove } variant="link" isDestructive style={ { display: 'block', marginBottom: '8px' } }>
					{ removeLabel }
				</Button>
			) }
		</>
	);
}

const subHead = { fontWeight: 600, margin: '12px 0 4px', fontSize: '11px', textTransform: 'uppercase', color: '#757575' };

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, showEyebrow, heading, body, bgColor = 'taupe',
		idleScreenId, idleScreenUrl, idleScreenLabel,
		items = [],
	} = attributes;

	const blockProps = useBlockProps( {
		// dd-js: the editor has no view.js, but we want the same stacked-caption
		// layout the JS-enhanced front end uses (not the no-JS list fallback).
		className: `dd-block dd-js dd-block--bg-${ bgColor }`,
		style: bgColor === 'deep-blue'
			? { '--dd-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	/* ── Resolve images fresh from attachment IDs (stored URLs are only snapshots
	      that go stale if the site address changes — same pattern as product-grid). ── */
	const photoIds = items.map( ( it ) => it.photoId || 0 );
	const overlayIds = items.map( ( it ) => it.overlayId || 0 );
	const screenIds = items.map( ( it ) => it.screenId || 0 );
	const resolvedPhotos = useSelect(
		( select ) => photoIds.map( ( id ) => ( id ? select( 'core' ).getEntityRecord( 'root', 'media', id ) : null ) ),
		[ photoIds.join( ',' ) ]
	);
	const resolvedOverlays = useSelect(
		( select ) => overlayIds.map( ( id ) => ( id ? select( 'core' ).getEntityRecord( 'root', 'media', id ) : null ) ),
		[ overlayIds.join( ',' ) ]
	);
	const resolvedScreens = useSelect(
		( select ) => screenIds.map( ( id ) => ( id ? select( 'core' ).getEntityRecord( 'root', 'media', id ) : null ) ),
		[ screenIds.join( ',' ) ]
	);
	const resolvedIdle = useSelect(
		( select ) => ( idleScreenId ? select( 'core' ).getEntityRecord( 'root', 'media', idleScreenId ) : null ),
		[ idleScreenId ]
	);
	const photoUrl = ( it, i ) => resolvedPhotos[ i ]?.source_url ?? it.photoUrl;
	const overlayUrl = ( it, i ) => resolvedOverlays[ i ]?.source_url ?? it.overlayUrl;
	const screenUrl = ( it, i ) => resolvedScreens[ i ]?.source_url ?? it.screenUrl;
	const idleUrl = resolvedIdle?.source_url ?? idleScreenUrl;

	/* ── Selection (drives the canvas "active" product) + drag reorder ── */
	const [ selectedIdx, setSelectedIdx ] = useState( null );
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );
	const itemPanelRefs = useRef( [] );
	const stageRef = useRef( null );

	const active = selectedIdx !== null && items[ selectedIdx ] ? selectedIdx : -1;

	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { items: reorderByDrag( items, dragIdx, toIdx ) } );
			setSelectedIdx( null );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// Scroll the opened item's panel into view in the sidebar (same as product-grid).
	useEffect( () => {
		if ( selectedIdx === null ) {
			return;
		}
		const el = itemPanelRefs.current[ selectedIdx ];
		if ( ! el ) {
			return;
		}
		let container = el.parentElement;
		while ( container ) {
			const s = window.getComputedStyle( container );
			if ( ( s.overflow + s.overflowY ).includes( 'auto' ) || ( s.overflow + s.overflowY ).includes( 'scroll' ) ) {
				break;
			}
			container = container.parentElement;
		}
		if ( ! container ) {
			el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			return;
		}
		const sticky = Array.from( container.children ).find( ( c ) => {
			const p = window.getComputedStyle( c ).position;
			return p === 'sticky' || p === 'fixed';
		} );
		const headerH = sticky ? sticky.getBoundingClientRect().height : 0;
		container.scrollTo( {
			top: container.scrollTop + ( el.getBoundingClientRect().top - container.getBoundingClientRect().top ) - headerH,
			behavior: 'smooth',
		} );
	}, [ selectedIdx ] );

	/* ── Connector lines: redraw when content/size changes ── */
	useEffect( () => {
		const stage = stageRef.current;
		if ( ! stage ) {
			return undefined;
		}
		const redraw = () => drawLines( stage, active );
		redraw();
		let ro;
		if ( typeof ResizeObserver !== 'undefined' ) {
			ro = new ResizeObserver( redraw );
			ro.observe( stage );
		}
		// Images/fonts load after first paint and change card heights.
		const t = setTimeout( redraw, 400 );
		return () => {
			clearTimeout( t );
			ro?.disconnect();
		};
	} );

	/* ── Item helpers ── */
	const updateItem = ( idx, field, value ) =>
		setAttributes( { items: items.map( ( it, i ) => ( i === idx ? { ...it, [ field ]: value } : it ) ) } );
	const patchItem = ( idx, patch ) =>
		setAttributes( { items: items.map( ( it, i ) => ( i === idx ? { ...it, ...patch } : it ) ) } );

	function addItem() {
		if ( items.length >= MAX_ITEMS ) {
			return;
		}
		setAttributes( { items: [ ...items, blankItem() ] } );
		setSelectedIdx( items.length );
	}
	function removeItem( idx ) {
		if ( items.length <= MIN_ITEMS ) {
			return;
		}
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
		setSelectedIdx( null );
	}

	/* ── Layout numbers — must match render.php ── */
	const count = items.length;
	const leftCount = Math.ceil( count / 2 );
	const rightCount = count - leftCount;
	const gridRows = leftCount * 2;
	const rightOffset = leftCount - rightCount;

	const activeItem = active >= 0 ? items[ active ] : null;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Warm White', 'cropx' ), value: 'taupe' },
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow ?? true }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ ( showEyebrow ?? true ) && (
						<TextControl label={ __( 'Eyebrow', 'cropx' ) } value={ eyebrow } onChange={ ( v ) => setAttributes( { eyebrow: v } ) } />
					) }
					<TextControl
						label={ __( 'Heading', 'cropx' ) }
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						help={ __( 'Wrap a phrase in <em> tags for an accent underline, e.g. One platform. <em>Every device</em> feeding it.', 'cropx' ) }
					/>
					<TextareaControl
						label={ __( 'Body paragraph (optional)', 'cropx' ) }
						value={ body }
						onChange={ ( v ) => setAttributes( { body: v } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Phone screen', 'cropx' ) } initialOpen={ false }>
					<p style={ { fontSize: '12px', color: '#757575', marginTop: 0 } }>
						{ __( 'The phone screen image. To show just one image throughout every interaction, set it here and leave the per-product screen images blank. Products with their own screen image swap it in while highlighted. No text is shown on the phone.', 'cropx' ) }
					</p>
					<p style={ subHead }>{ __( 'Default screen image', 'cropx' ) }</p>
					<ImagePicker
						id={ idleScreenId }
						url={ idleUrl }
						onSelect={ ( m ) => setAttributes( { idleScreenId: m.id, idleScreenUrl: m.url } ) }
						onRemove={ () => setAttributes( { idleScreenId: 0, idleScreenUrl: '' } ) }
						selectLabel={ __( 'Select screen image', 'cropx' ) }
						replaceLabel={ __( 'Replace screen image', 'cropx' ) }
						removeLabel={ __( 'Remove screen image', 'cropx' ) }
					/>
				</PanelBody>

				<PanelBody title={ sprintf( __( 'Products (%1$d of %2$d)', 'cropx' ), count, MAX_ITEMS ) } initialOpen={ true }>
					<p style={ { fontSize: '12px', color: '#757575', marginTop: 0 } }>
						{ __( 'Use between 4 and 8 products. The first half sit to the left of the phone and the rest to the right (with an odd number, the extra one goes on the left). Drag or use the arrows to reorder. Each product card links to its own page.', 'cropx' ) }
					</p>
					{ items.map( ( item, idx ) => (
						<div
							key={ idx }
							ref={ ( el ) => { itemPanelRefs.current[ idx ] = el; } }
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
							<div style={ { display: 'flex', alignItems: 'center', gap: '2px', background: '#f0f0f0', padding: '3px 6px', marginBottom: '-1px' } }>
								<span
									draggable
									onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
									style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
									title={ __( 'Drag to reorder', 'cropx' ) }
								>⠿</span>
								<span style={ { flex: 1, fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#666', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } }>
									{ item.name || `${ __( 'Product', 'cropx' ) } ${ idx + 1 }` }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => { setAttributes( { items: moveItem( items, idx, 'up' ) } ); setSelectedIdx( null ); } } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => { setAttributes( { items: moveItem( items, idx, 'down' ) } ); setSelectedIdx( null ); } } disabled={ idx === items.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>

							<PanelBody
								title={ item.name || `${ __( 'Product', 'cropx' ) } ${ idx + 1 }` }
								opened={ selectedIdx === idx }
								onToggle={ () => setSelectedIdx( selectedIdx === idx ? null : idx ) }
							>
								<TextControl label={ __( 'Product name', 'cropx' ) } value={ item.name } onChange={ ( v ) => updateItem( idx, 'name', v ) } />
								<TextControl
									label={ __( 'Tagline / software label', 'cropx' ) }
									help={ __( 'Shown under the name in the card and on the phone screen, e.g. "Soil intelligence".', 'cropx' ) }
									value={ item.softwareLabel }
									onChange={ ( v ) => updateItem( idx, 'softwareLabel', v ) }
								/>
								<TextareaControl
									label={ __( 'Description (shown in the card)', 'cropx' ) }
									value={ item.description }
									onChange={ ( v ) => updateItem( idx, 'description', v ) }
									rows={ 3 }
								/>
								<TextControl
									label={ __( 'Product page URL', 'cropx' ) }
									help={ __( 'The whole card links here.', 'cropx' ) }
									value={ item.url }
									onChange={ ( v ) => updateItem( idx, 'url', v ) }
								/>

								<p style={ subHead }>{ __( 'Photo (fills the image half of the card)', 'cropx' ) }</p>
								<ImagePicker
									id={ item.photoId }
									url={ photoUrl( item, idx ) }
									onSelect={ ( m ) => patchItem( idx, { photoId: m.id, photoUrl: m.url, photoAlt: m.alt || '' } ) }
									onRemove={ () => patchItem( idx, { photoId: 0, photoUrl: '', photoAlt: '' } ) }
									selectLabel={ __( 'Select photo', 'cropx' ) }
									replaceLabel={ __( 'Replace photo', 'cropx' ) }
									removeLabel={ __( 'Remove photo', 'cropx' ) }
								/>
								{ photoUrl( item, idx ) && (
									<>
										<RangeControl
											label={ __( 'Focal X — left (%)', 'cropx' ) }
											value={ Math.round( ( item.photoFocalX ?? 0.5 ) * 100 ) }
											onChange={ ( v ) => updateItem( idx, 'photoFocalX', v / 100 ) }
											min={ 0 } max={ 100 }
										/>
										<RangeControl
											label={ __( 'Focal Y — top (%)', 'cropx' ) }
											value={ Math.round( ( item.photoFocalY ?? 0.5 ) * 100 ) }
											onChange={ ( v ) => updateItem( idx, 'photoFocalY', v / 100 ) }
											min={ 0 } max={ 100 }
										/>
										<RangeControl
											label={ __( 'Photo zoom (%)', 'cropx' ) }
											help={ __( 'Scale the photo within its frame. Zoom follows the focal point.', 'cropx' ) }
											value={ item.photoZoom ?? 100 }
											onChange={ ( v ) => updateItem( idx, 'photoZoom', v ) }
											min={ 100 } max={ 500 }
										/>
									</>
								) }

								<p style={ subHead }>{ __( 'Illustration overlay (optional PNG)', 'cropx' ) }</p>
								<SelectControl
									label={ __( 'Overlay type', 'cropx' ) }
									value={ item.overlayType ?? 'none' }
									options={ OVERLAY_TYPE_OPTIONS }
									onChange={ ( v ) => updateItem( idx, 'overlayType', v ) }
									help={
										item.overlayType === 'card-bleed'
											? __( 'Illustration extends from the photo half into the text area.', 'cropx' )
											: item.overlayType === 'contained'
												? __( 'Illustration stays inside the photo half.', 'cropx' )
												: ''
									}
								/>
								{ ( item.overlayType ?? 'none' ) !== 'none' && (
									<>
										<ImagePicker
											id={ item.overlayId }
											url={ overlayUrl( item, idx ) }
											onSelect={ ( m ) => patchItem( idx, { overlayId: m.id, overlayUrl: m.url } ) }
											onRemove={ () => patchItem( idx, { overlayId: 0, overlayUrl: '' } ) }
											selectLabel={ __( 'Select illustration', 'cropx' ) }
											replaceLabel={ __( 'Replace illustration', 'cropx' ) }
											removeLabel={ __( 'Remove illustration', 'cropx' ) }
										/>
										{ item.overlayType === 'contained' && (
											<RangeControl
												label={ __( 'Contained padding (%)', 'cropx' ) }
												help={ __( 'Vertical breathing room around the illustration.', 'cropx' ) }
												value={ item.overlayPadding ?? 0 }
												onChange={ ( v ) => updateItem( idx, 'overlayPadding', v ) }
												min={ 0 } max={ 20 }
											/>
										) }
										{ item.overlayType === 'card-bleed' && (
											<>
												<RangeControl
													label={ __( 'Overlay height (% of card)', 'cropx' ) }
													help={ __( '100% = same height as the card.', 'cropx' ) }
													value={ item.overlayH ?? 100 }
													onChange={ ( v ) => updateItem( idx, 'overlayH', v ) }
													min={ 50 } max={ 160 }
												/>
												<SelectControl
													label={ __( 'Vertical anchor', 'cropx' ) }
													value={ item.overlayAnchor ?? 'center' }
													options={ [
														{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
														{ label: __( 'Bottom edge', 'cropx' ), value: 'bottom' },
													] }
													onChange={ ( v ) => updateItem( idx, 'overlayAnchor', v ) }
												/>
												<RangeControl
													label={ __( 'Vertical offset (px)', 'cropx' ) }
													help={ __( 'Shift the overlay up (negative) or down (positive) from its anchor.', 'cropx' ) }
													value={ item.overlayY ?? 0 }
													onChange={ ( v ) => updateItem( idx, 'overlayY', v ) }
													min={ -60 } max={ 60 }
												/>
												<ToggleControl
													label={ __( 'Center on photo half', 'cropx' ) }
													help={ ( item.overlayCentered ?? false )
														? __( 'Overlay horizontally centered on the photo half. Use the nudge below to fine-tune.', 'cropx' )
														: __( 'Use manual horizontal offset below.', 'cropx' ) }
													checked={ item.overlayCentered ?? false }
													onChange={ ( v ) => updateItem( idx, 'overlayCentered', v ) }
												/>
												<RangeControl
													label={ ( item.overlayCentered ?? false )
														? __( 'Horizontal nudge from center (px)', 'cropx' )
														: __( 'Horizontal offset (px)', 'cropx' ) }
													help={ __( 'Positive moves the overlay toward the card\'s outer edge, negative toward the text. This flips automatically on the cards to the right of the phone.', 'cropx' ) }
													value={ item.overlayX ?? 0 }
													onChange={ ( v ) => updateItem( idx, 'overlayX', v ) }
													min={ ( item.overlayCentered ?? false ) ? -40 : -80 }
													max={ ( item.overlayCentered ?? false ) ? 40 : 80 }
												/>
												<RangeControl
													label={ __( 'Top bleed (px)', 'cropx' ) }
													help={ __( 'Let the illustration poke out above the top of the card.', 'cropx' ) }
													value={ item.overlayTopBleed ?? 0 }
													onChange={ ( v ) => updateItem( idx, 'overlayTopBleed', v ) }
													min={ 0 } max={ 20 }
												/>
											</>
										) }
									</>
								) }

								<p style={ subHead }>{ __( 'Phone screen image (optional)', 'cropx' ) }</p>
								<p style={ { fontSize: '11px', color: '#757575', margin: '0 0 6px' } }>
									{ __( 'Shown on the phone while this product is highlighted. Leave blank to keep showing the default screen image.', 'cropx' ) }
								</p>
								<ImagePicker
									id={ item.screenId }
									url={ screenUrl( item, idx ) }
									onSelect={ ( m ) => patchItem( idx, { screenId: m.id, screenUrl: m.url } ) }
									onRemove={ () => patchItem( idx, { screenId: 0, screenUrl: '' } ) }
									selectLabel={ __( 'Select screen image', 'cropx' ) }
									replaceLabel={ __( 'Replace screen image', 'cropx' ) }
									removeLabel={ __( 'Remove screen image', 'cropx' ) }
								/>

								<Button
									onClick={ () => removeItem( idx ) }
									variant="link"
									isDestructive
									disabled={ items.length <= MIN_ITEMS }
									style={ { marginTop: '8px' } }
								>
									{ items.length <= MIN_ITEMS ? __( 'Remove (minimum 4 products)', 'cropx' ) : __( 'Remove product', 'cropx' ) }
								</Button>
							</PanelBody>
						</div>
					) ) }

					<Button
						onClick={ addItem }
						variant="secondary"
						disabled={ items.length >= MAX_ITEMS }
						style={ { width: '100%', justifyContent: 'center', marginTop: '8px' } }
					>
						{ items.length >= MAX_ITEMS ? __( 'Maximum 8 products', 'cropx' ) : __( '+ Add product', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			{ /* ── Canvas preview — mirrors render.php ── */ }
			<section { ...blockProps }>
				<div className="dd-inner">
					{ ( ( ( showEyebrow ?? true ) && eyebrow ) || heading || body ) && (
						<header className="dd-header">
							{ ( showEyebrow ?? true ) && eyebrow && <span className="section-eyebrow">{ eyebrow }</span> }
							{ heading && <h2 className="dd-heading" dangerouslySetInnerHTML={ { __html: safeHeading( heading ) } } /> }
							{ body && <p className="dd-body">{ body }</p> }
						</header>
					) }

					<div
						ref={ stageRef }
						className={ `dd-stage${ active >= 0 ? ' has-active' : '' }` }
						style={ { '--dd-rows': gridRows } }
					>
						<svg className="dd-lines" aria-hidden="true" focusable="false" />

						<div className="dd-phone" aria-hidden="true">
							<div className="dd-phone-body">
								<span className="dd-phone-notch" />
								<div className="dd-screen">
									<div className={ `dd-screen-layer${ active < 0 ? ' is-active' : '' }` } data-i="-1">
										{ idleUrl && <img className="dd-screen-img" src={ idleUrl } alt="" /> }
									</div>
									{ items.map( ( item, i ) => ( screenUrl( item, i )
										? (
											<div key={ i } className={ `dd-screen-layer${ active === i ? ' is-active' : '' }` } data-i={ i }>
												<img className="dd-screen-img" src={ screenUrl( item, i ) } alt="" />
											</div>
										)
										: null
									) ) }
								</div>
							</div>
						</div>

						<div className="dd-carousel">
							<div className="dd-track">
								{ items.map( ( item, i ) => {
									const isLeft = i < leftCount;
									const row = isLeft ? i * 2 + 1 : ( i - leftCount ) * 2 + 1 + rightOffset;
									return (
										<div
											key={ i }
											className={ `dd-item dd-item--${ isLeft ? 'left' : 'right' }${ active === i ? ' is-active' : '' }` }
											data-i={ i }
											data-side={ isLeft ? 'left' : 'right' }
											style={ { '--dd-col': isLeft ? 1 : 3, '--dd-row': row } }
											onClick={ () => setSelectedIdx( selectedIdx === i ? null : i ) }
											title={ __( 'Click to open settings for this product', 'cropx' ) }
										>
											<div
												className={ [
													'dd-card',
													item.overlayType === 'card-bleed' && overlayUrl( item, i ) && item.overlayCentered ? 'dd-card--overlay-centered' : '',
													item.overlayType === 'card-bleed' && overlayUrl( item, i ) && ( item.overlayAnchor ?? 'center' ) === 'bottom' ? 'dd-card--overlay-bottom' : '',
												].filter( Boolean ).join( ' ' ) }
												style={ item.overlayType === 'card-bleed' && overlayUrl( item, i )
													? {
														'--dd-overlay-h': `${ item.overlayH ?? 100 }%`,
														'--dd-overlay-x': `${ item.overlayX ?? 0 }px`,
														'--dd-overlay-y': `${ item.overlayY ?? 0 }px`,
														'--dd-overlay-top-bleed': `-${ item.overlayTopBleed ?? 0 }px`,
													}
													: undefined }
											>
												<span className="dd-card-text">
													<strong className="dd-card-name">
														{ item.name || __( 'Product name', 'cropx' ) }
														{ ' ' }
														<span className="dd-card-arrow" aria-hidden="true"><ArrowSvg /></span>
													</strong>
													{ item.softwareLabel && <span className="dd-card-tag">{ item.softwareLabel }</span> }
													{ item.description && <span className="dd-card-desc">{ item.description }</span> }
												</span>
												<span className="dd-thumb-outer">
													<span
														className="dd-thumb"
														style={ {
															'--dd-focal-x': `${ Math.round( ( item.photoFocalX ?? 0.5 ) * 100 ) }%`,
															'--dd-focal-y': `${ Math.round( ( item.photoFocalY ?? 0.5 ) * 100 ) }%`,
															'--dd-zoom': item.photoZoom ?? 100,
														} }
													>
														{ photoUrl( item, i ) && <img className="dd-thumb-img" src={ photoUrl( item, i ) } alt="" /> }
														{ item.overlayType === 'contained' && overlayUrl( item, i ) && (
															<img
																className="dd-overlay--contained"
																src={ overlayUrl( item, i ) }
																alt=""
																style={ item.overlayPadding ? { paddingBlock: `${ item.overlayPadding }%` } : undefined }
															/>
														) }
													</span>
												</span>
												{ item.overlayType === 'card-bleed' && overlayUrl( item, i ) && (
													<span className="dd-overlay-clip" aria-hidden="true">
														<img className="dd-overlay--card-bleed" src={ overlayUrl( item, i ) } alt="" />
													</span>
												) }
											</div>
										</div>
									);
								} ) }
							</div>
						</div>
					</div>

					<p className="dd-editor-note">
						{ activeItem
							? sprintf( __( 'Previewing "%s" highlighted, as it looks on hover. On touch devices and narrow screens the front end shows the phone with a swipeable product carousel instead.', 'cropx' ), activeItem.name )
							: __( 'Click a product (or open it in the sidebar) to preview its hover state. On touch devices and narrow screens the front end shows the phone with a swipeable product carousel instead.', 'cropx' ) }
					</p>
				</div>
			</section>
		</>
	);
}
