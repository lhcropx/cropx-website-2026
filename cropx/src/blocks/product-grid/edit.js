/**
 * Product Grid block — editor experience.
 *
 * Renders a close preview of the front-end card grid.
 * All item data (photo, name, description, URL, overlay) is
 * managed via InspectorControls in the sidebar.
 */

import { __ } from '@wordpress/i18n';
import { useState, useRef, useEffect } from '@wordpress/element';
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
import './editor.css';

const EYEBROW_COLOR_OPTIONS = [
	{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
	{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
	{ label: __( 'White',                'cropx' ), value: 'white'      },
];

const OVERLAY_TYPE_OPTIONS = [
	{ label: __( 'None',       'cropx' ), value: 'none'       },
	{ label: __( 'Card bleed', 'cropx' ), value: 'card-bleed' },
	{ label: __( 'Contained',  'cropx' ), value: 'contained'  },
];

const ArrowSvg = () => (
	<svg viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path
			d="M3 8h10M9 4l4 4-4 4"
			stroke="currentColor"
			strokeWidth="1.5"
			strokeLinecap="round"
			strokeLinejoin="round"
		/>
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, eyebrowColor, heading, blurb, showHeader, items, bgColor = 'taupe' } = attributes;

	const blockProps = useBlockProps( { className: `pg-block pg-block--bg-${bgColor}` } );

	// ── Click-to-focus: clicking a preview card expands its sidebar panel ──────
	const [ selectedItemIdx, setSelectedItemIdx ] = useState( 0 );
	const itemPanelRefs = useRef( [] );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { items: reorderByDrag( items, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	useEffect( () => {
		if ( selectedItemIdx === null ) return;
		const el = itemPanelRefs.current[ selectedItemIdx ];
		if ( ! el ) return;

		// Walk up the DOM to find the sidebar's scrollable container.
		let container = el.parentElement;
		while ( container ) {
			const style = window.getComputedStyle( container );
			const overflow = style.overflow + style.overflowY;
			if ( overflow.includes( 'auto' ) || overflow.includes( 'scroll' ) ) break;
			container = container.parentElement;
		}

		if ( ! container ) {
			el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			return;
		}

		// Measure the sticky "Page | Block" tabs bar so we can park the panel
		// top just below it rather than behind it.
		const stickyChild = Array.from( container.children ).find( ( child ) => {
			const s = window.getComputedStyle( child );
			return s.position === 'sticky' || s.position === 'fixed';
		} );
		const headerHeight = stickyChild ? stickyChild.getBoundingClientRect().height : 0;

		// Scroll the container so the panel's top edge sits flush with the
		// bottom of the tabs header.
		const elRect = el.getBoundingClientRect();
		const containerRect = container.getBoundingClientRect();
		const targetScrollTop =
			container.scrollTop + ( elRect.top - containerRect.top ) - headerHeight;

		container.scrollTo( { top: targetScrollTop, behavior: 'smooth' } );
	}, [ selectedItemIdx ] );

	// ── Item helpers ─────────────────────────────────────

	function updateItem( idx, field, value ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, [ field ]: value } : item
			),
		} );
	}

	function selectPhoto( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx
					? { ...item, photoId: media.id, photoUrl: media.url, photoAlt: media.alt || '' }
					: item
			),
		} );
	}

	function clearPhoto( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, photoId: 0, photoUrl: '', photoAlt: '' } : item
			),
		} );
	}

	function selectOverlay( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx
					? { ...item, overlayId: media.id, overlayUrl: media.url }
					: item
			),
		} );
	}

	function clearOverlay( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, overlayId: 0, overlayUrl: '' } : item
			),
		} );
	}

	function addItem() {
		setAttributes( {
			items: [
				...items,
				{
					name: '', description: '', url: '#',
					photoId: 0, photoUrl: '', photoAlt: '',
					photoFocalX: 0.5, photoFocalY: 0.5, photoZoom: 100,
					overlayId: 0, overlayUrl: '', overlayType: 'none', overlayPadding: 0,
					overlayH: 100, overlayX: 0, overlayCentered: false, overlayAnchor: 'center',
				},
			],
		} );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
	}

	// ── Sidebar ──────────────────────────────────────────

	return (
		<>
			<InspectorControls>

				<PanelBody title={ __( 'Section settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'White',               'cropx' ), value: 'white' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show header (eyebrow / heading / blurb)', 'cropx' ) }
						checked={ showHeader }
						onChange={ ( v ) => setAttributes( { showHeader: v } ) }
					/>
				</PanelBody>

				{ showHeader && (
					<PanelBody title={ __( 'Header content', 'cropx' ) } initialOpen={ true }>
						<TextControl
							label={ __( 'Eyebrow', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						/>
						<SelectControl
							label={ __( 'Eyebrow color', 'cropx' ) }
							value={ eyebrowColor ?? 'cropx-blue' }
							options={ EYEBROW_COLOR_OPTIONS }
							onChange={ ( v ) => setAttributes( { eyebrowColor: v } ) }
						/>
						<TextControl
							label={ __( 'Heading', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
						/>
						<TextareaControl
							label={ __( 'Blurb paragraph (optional)', 'cropx' ) }
							value={ blurb }
							onChange={ ( v ) => setAttributes( { blurb: v } ) }
							help={ __( 'Short supporting paragraph. Displays at half the section width.', 'cropx' ) }
						/>
					</PanelBody>
				) }

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>
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
									{ item.name || `${ __( 'Item', 'cropx' ) } ${ idx + 1 }` }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { items: moveItem( items, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { items: moveItem( items, idx, 'down' ) } ) } disabled={ idx === items.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>
						<PanelBody
							title={ item.name || `${ __( 'Item', 'cropx' ) } ${ idx + 1 }` }
							opened={ selectedItemIdx === idx }
							onToggle={ () => setSelectedItemIdx( selectedItemIdx === idx ? null : idx ) }
						>
							<TextControl
								label={ __( 'Product name', 'cropx' ) }
								value={ item.name }
								onChange={ ( v ) => updateItem( idx, 'name', v ) }
							/>
							<TextareaControl
								label={ __( 'Description (2-line excerpt)', 'cropx' ) }
								value={ item.description }
								onChange={ ( v ) => updateItem( idx, 'description', v ) }
							/>
							<TextControl
								label={ __( 'URL', 'cropx' ) }
								value={ item.url }
								onChange={ ( v ) => updateItem( idx, 'url', v ) }
							/>

							<p style={ { fontWeight: 600, margin: '8px 0 4px', fontSize: '11px', textTransform: 'uppercase', color: '#757575' } }>
								{ __( 'Photo', 'cropx' ) }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ item.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '4px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ item.photoUrl
												? __( 'Replace photo', 'cropx' )
												: __( 'Select photo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ item.photoUrl && (
								<>
									<Button
										onClick={ () => clearPhoto( idx ) }
										variant="link"
										isDestructive
										style={ { display: 'block', marginBottom: '8px' } }
									>
										{ __( 'Remove photo', 'cropx' ) }
									</Button>
									<RangeControl
										label={ __( 'Focal X — left (%)', 'cropx' ) }
										value={ Math.round( ( item.photoFocalX ?? 0.5 ) * 100 ) }
										onChange={ ( v ) => updateItem( idx, 'photoFocalX', v / 100 ) }
										min={ 0 }
										max={ 100 }
									/>
									<RangeControl
										label={ __( 'Focal Y — top (%)', 'cropx' ) }
										value={ Math.round( ( item.photoFocalY ?? 0.5 ) * 100 ) }
										onChange={ ( v ) => updateItem( idx, 'photoFocalY', v / 100 ) }
										min={ 0 }
										max={ 100 }
									/>
									<RangeControl
										label={ __( 'Photo zoom (%)', 'cropx' ) }
										help={ __( 'Scale the photo within its frame. Zoom follows the focal point.', 'cropx' ) }
										value={ item.photoZoom ?? 100 }
										onChange={ ( v ) => updateItem( idx, 'photoZoom', v ) }
										min={ 100 }
										max={ 200 }
									/>
								</>
							) }

							<p style={ { fontWeight: 600, margin: '8px 0 4px', fontSize: '11px', textTransform: 'uppercase', color: '#757575' } }>
								{ __( 'Illustration overlay', 'cropx' ) }
							</p>
							<SelectControl
								label={ __( 'Overlay type', 'cropx' ) }
								value={ item.overlayType ?? 'none' }
								options={ OVERLAY_TYPE_OPTIONS }
								onChange={ ( v ) => updateItem( idx, 'overlayType', v ) }
								help={
									item.overlayType === 'card-bleed'
										? __( 'Illustration extends from photo column into the text area (e.g. Evato sensor arm).', 'cropx' )
										: item.overlayType === 'contained'
										? __( 'Illustration contained within the photo column (e.g. Rivo rain gauge).', 'cropx' )
										: ''
								}
							/>
							{ item.overlayType !== 'none' && (
								<>
									<MediaUploadCheck>
										<MediaUpload
											onSelect={ ( media ) => selectOverlay( idx, media ) }
											allowedTypes={ [ 'image' ] }
											value={ item.overlayId }
											render={ ( { open } ) => (
												<Button
													onClick={ open }
													variant="secondary"
													style={ { marginBottom: '4px', display: 'block', width: '100%', justifyContent: 'center' } }
												>
													{ item.overlayUrl
														? __( 'Replace illustration', 'cropx' )
														: __( 'Select illustration', 'cropx' ) }
												</Button>
											) }
										/>
									</MediaUploadCheck>
									{ item.overlayUrl && (
										<Button
											onClick={ () => clearOverlay( idx ) }
											variant="link"
											isDestructive
											style={ { display: 'block', marginBottom: '8px' } }
										>
											{ __( 'Remove illustration', 'cropx' ) }
										</Button>
									) }
									{ item.overlayType === 'contained' && (
										<RangeControl
											label={ __( 'Contained padding (%)', 'cropx' ) }
											help={ __( 'Vertical breathing room around the illustration. Use 8% for tall sensors like Rivo.', 'cropx' ) }
											value={ item.overlayPadding ?? 0 }
											onChange={ ( v ) => updateItem( idx, 'overlayPadding', v ) }
											min={ 0 }
											max={ 20 }
										/>
									) }
									{ item.overlayType === 'card-bleed' && (
										<>
											<RangeControl
												label={ __( 'Overlay height (% of card)', 'cropx' ) }
												help={ __( 'Size of the illustration as a percentage of card height. 100% = same height as card.', 'cropx' ) }
												value={ item.overlayH ?? 100 }
												onChange={ ( v ) => updateItem( idx, 'overlayH', v ) }
												min={ 50 }
												max={ 160 }
											/>
											<SelectControl
												label={ __( 'Vertical anchor', 'cropx' ) }
												help={ item.overlayAnchor === 'bottom'
													? __( 'Illustration sits on the bottom edge of the card — good for sensors with poles or stands.', 'cropx' )
													: __( 'Illustration is vertically centred on the card.', 'cropx' )
												}
												value={ item.overlayAnchor ?? 'center' }
												options={ [
													{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
													{ label: __( 'Bottom edge',      'cropx' ), value: 'bottom' },
												] }
												onChange={ ( v ) => updateItem( idx, 'overlayAnchor', v ) }
											/>
											<ToggleControl
												label={ __( 'Center on photo column', 'cropx' ) }
												help={ item.overlayCentered
													? __( 'Overlay horizontally centered on the photo column.', 'cropx' )
													: __( 'Use manual horizontal offset below.', 'cropx' )
												}
												checked={ item.overlayCentered ?? false }
												onChange={ ( v ) => updateItem( idx, 'overlayCentered', v ) }
											/>
											{ ! ( item.overlayCentered ?? false ) && (
												<RangeControl
													label={ __( 'Horizontal offset (px)', 'cropx' ) }
													help={ __( 'Shift the overlay left (negative) or right (positive). Negative pushes the illustration further into the text column.', 'cropx' ) }
													value={ item.overlayX ?? 0 }
													onChange={ ( v ) => updateItem( idx, 'overlayX', v ) }
													min={ -80 }
													max={ 40 }
												/>
											) }
										</>
									) }
								</>
							) }

							<Button
								onClick={ () => removeItem( idx ) }
								variant="link"
								isDestructive
								disabled={ items.length <= 1 }
								style={ { marginTop: '4px' } }
							>
								{ __( 'Remove item', 'cropx' ) }
							</Button>
						</PanelBody>
						</div>
					) ) }

					<Button
						onClick={ addItem }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add item', 'cropx' ) }
					</Button>
				</PanelBody>

			</InspectorControls>

			{/* ── Editor preview ──────────────────────────── */}

			<section { ...blockProps }>
				<div className="pg-inner">

					{ showHeader && (
						<header className="pg-header">
							{ eyebrow && (
								<span
									className="section-eyebrow"
									style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
								>
									{ eyebrow }
								</span>
							) }
							{ heading && <h2 className="section-heading">{ heading }</h2> }
							{ blurb && <p className="pg-blurb">{ blurb }</p> }
						</header>
					) }

					<div className="pg-grid">
						{ items.map( ( item, idx ) => (
							// div instead of <a> in editor — click to select and expand sidebar panel
							<div
								key={ idx }
								className={ [
									'pg-item',
									item.overlayType === 'card-bleed' && ( item.overlayCentered ?? false )
										? 'pg-item--overlay-centered'
										: '',
									item.overlayType === 'card-bleed' && ( item.overlayAnchor ?? 'center' ) === 'bottom'
										? 'pg-item--overlay-bottom'
										: '',
								].filter( Boolean ).join( ' ' ) }
								style={ ( () => {
									const s = { cursor: 'pointer' };
									if ( item.overlayType === 'card-bleed' && item.overlayUrl ) {
										s[ '--pg-overlay-h' ] = `${ item.overlayH ?? 100 }%`;
										if ( ! ( item.overlayCentered ?? false ) ) {
											s[ '--pg-overlay-x' ] = `${ item.overlayX ?? 0 }px`;
										}
									}
									if ( selectedItemIdx === idx ) {
										s.outline = '2px solid var(--wp-admin-theme-color, #007cba)';
										s.outlineOffset = '-2px';
									}
									return s;
								} )() }
								onClick={ () => setSelectedItemIdx( idx ) }
								title={ __( 'Click to open settings for this card', 'cropx' ) }
							>

								<div className="pg-thumb-outer">
									<div
										className="pg-thumb"
										style={ {
											'--pg-photo-focal-x': `${ Math.round( ( item.photoFocalX ?? 0.5 ) * 100 ) }%`,
											'--pg-photo-focal-y': `${ Math.round( ( item.photoFocalY ?? 0.5 ) * 100 ) }%`,
											'--pg-photo-zoom': item.photoZoom ?? 100,
										} }
									>
										{ item.photoUrl && (
											<img
												className="pg-thumb-img"
												src={ item.photoUrl }
												alt=""
											/>
										) }
										{ item.overlayType === 'contained' && item.overlayUrl && (
											<img
												className="pg-overlay--contained"
												src={ item.overlayUrl }
												alt=""
												style={
													item.overlayPadding
														? { paddingBlock: `${ item.overlayPadding }%` }
														: undefined
												}
											/>
										) }
									</div>
								</div>

								<div className="pg-text">
									<strong className="pg-name">
										{ item.name || __( 'Product name', 'cropx' ) }{ ' ' }<span className="pg-arrow" aria-hidden="true">
											<ArrowSvg />
										</span>
									</strong>
									<p className="pg-desc">
										{ item.description || __( 'Description…', 'cropx' ) }
									</p>
								</div>

								{ item.overlayType === 'card-bleed' && item.overlayUrl && (
									<img
										className="pg-overlay--card-bleed"
										src={ item.overlayUrl }
										alt=""
									/>
								) }

							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
