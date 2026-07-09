/**
 * Product Sections block — editor UI.
 *
 * Two always-visible stacked product grids (CropX Platform / CropX Hardware)
 * connected by a sticky jump nav on the front end. The editor canvas renders
 * one section at a time (switched via the jump nav bar) so the card grid stays
 * manageable — both sections are always present on the front end.
 *
 * Sidebar structure mirrors product-tabs: labels, per-section headers, and a
 * full item manager with photo focal point, zoom, and illustration overlay.
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

const blankItem = () => ( {
	name: '', description: '', url: '#',
	photoId: 0, photoUrl: '', photoAlt: '',
	photoFocalX: 0.5, photoFocalY: 0.5, photoZoom: 100,
	overlayId: 0, overlayUrl: '', overlayType: 'none', overlayPadding: 0,
	overlayH: 100, overlayX: 0, overlayCentered: false, overlayAnchor: 'center',
} );

export default function Edit( { attributes, setAttributes } ) {
	const {
		platformLabel,   hardwareLabel,
		platformEyebrow, platformHeading, platformBlurb,
		hardwareEyebrow, hardwareHeading, hardwareBlurb,
		platformItems,   hardwareItems,
		bgColor = 'taupe',
	} = attributes;

	const [ activeSection,   setActiveSection   ] = useState( 'platform' );
	const [ selectedItemIdx, setSelectedItemIdx ] = useState( null );
	const itemPanelRefs = useRef( [] );
	const [ dragIdx,     setDragIdx     ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	const blockProps = useBlockProps( { className: `psec-block psec-block--editor psec-block--bg-${bgColor}` } );

	const isPlatform  = activeSection === 'platform';
	const items       = isPlatform ? platformItems : hardwareItems;
	const activeLabel = isPlatform ? platformLabel : hardwareLabel;

	function setItems( next ) {
		setAttributes( isPlatform ? { platformItems: next } : { hardwareItems: next } );
	}

	function switchSection( section ) {
		setActiveSection( section );
		setSelectedItemIdx( null );
		itemPanelRefs.current = [];
	}

	// ── Click-to-focus: clicking a preview card expands its sidebar panel ──

	useEffect( () => {
		if ( selectedItemIdx === null ) return;
		const el = itemPanelRefs.current[ selectedItemIdx ];
		if ( ! el ) return;

		let container = el.parentElement;
		while ( container ) {
			const style    = window.getComputedStyle( container );
			const overflow = style.overflow + style.overflowY;
			if ( overflow.includes( 'auto' ) || overflow.includes( 'scroll' ) ) break;
			container = container.parentElement;
		}

		if ( ! container ) {
			el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			return;
		}

		const stickyChild = Array.from( container.children ).find( ( child ) => {
			const s = window.getComputedStyle( child );
			return s.position === 'sticky' || s.position === 'fixed';
		} );
		const headerHeight = stickyChild ? stickyChild.getBoundingClientRect().height : 0;
		const elRect        = el.getBoundingClientRect();
		const containerRect = container.getBoundingClientRect();
		const targetScrollTop =
			container.scrollTop + ( elRect.top - containerRect.top ) - headerHeight;

		container.scrollTo( { top: targetScrollTop, behavior: 'smooth' } );
	}, [ selectedItemIdx ] );

	// ── Item helpers ────────────────────────────────────────────────────────

	function updateItem( idx, field, value ) {
		setItems( items.map( ( item, i ) =>
			i === idx ? { ...item, [ field ]: value } : item
		) );
	}

	function selectPhoto( idx, media ) {
		setItems( items.map( ( item, i ) =>
			i === idx
				? { ...item, photoId: media.id, photoUrl: media.url, photoAlt: media.alt || '' }
				: item
		) );
	}

	function clearPhoto( idx ) {
		setItems( items.map( ( item, i ) =>
			i === idx ? { ...item, photoId: 0, photoUrl: '', photoAlt: '' } : item
		) );
	}

	function selectOverlay( idx, media ) {
		setItems( items.map( ( item, i ) =>
			i === idx ? { ...item, overlayId: media.id, overlayUrl: media.url } : item
		) );
	}

	function clearOverlay( idx ) {
		setItems( items.map( ( item, i ) =>
			i === idx ? { ...item, overlayId: 0, overlayUrl: '' } : item
		) );
	}

	function addItem() {
		setItems( [ ...items, blankItem() ] );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setItems( items.filter( ( _, i ) => i !== idx ) );
	}

	// ── Drag-and-drop reorder ───────────────────────────────────────────────

	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setItems( reorderByDrag( items, dragIdx, toIdx ) );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// ── Card grid preview ───────────────────────────────────────────────────

	function renderCardGrid( gridItems ) {
		return (
			<div className="pg-grid">
				{ gridItems.map( ( item, idx ) => (
					<div
						key={ idx }
						className={ [
							'pg-item',
							item.overlayType === 'card-bleed' && ( item.overlayCentered ?? false )
								? 'pg-item--overlay-centered' : '',
							item.overlayType === 'card-bleed' && ( item.overlayAnchor ?? 'center' ) === 'bottom'
								? 'pg-item--overlay-bottom' : '',
						].filter( Boolean ).join( ' ' ) }
						style={ ( () => {
							const s = { cursor: 'pointer' };
							if ( item.overlayType === 'card-bleed' && item.overlayUrl ) {
								s[ '--pg-overlay-h' ] = `${ item.overlayH ?? 100 }%`;
								if ( ! ( item.overlayCentered ?? false ) ) {
									s[ '--pg-overlay-x' ] = `${ item.overlayX ?? 0 }px`;
								}
							}
							if ( isPlatform && selectedItemIdx === idx ) {
								s.outline = '2px solid var(--wp-admin-theme-color, #007cba)';
								s.outlineOffset = '-2px';
							}
							return s;
						} )() }
						onClick={ () => { switchSection( isPlatform ? 'platform' : 'hardware' ); setSelectedItemIdx( idx ); } }
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
									<img className="pg-thumb-img" src={ item.photoUrl } alt="" />
								) }
								{ item.overlayType === 'contained' && item.overlayUrl && (
									<img
										className="pg-overlay--contained"
										src={ item.overlayUrl }
										alt=""
										style={ item.overlayPadding ? { paddingBlock: `${ item.overlayPadding }%` } : undefined }
									/>
								) }
							</div>
						</div>

						<div className="pg-text">
							<strong className="pg-name">
								{ item.name || __( 'Product name', 'cropx' ) }{ ' ' }
								<span className="pg-arrow" aria-hidden="true"><ArrowSvg /></span>
							</strong>
							<p className="pg-desc">
								{ item.description || __( 'Description…', 'cropx' ) }
							</p>
						</div>

						{ item.overlayType === 'card-bleed' && item.overlayUrl && (
							<img className="pg-overlay--card-bleed" src={ item.overlayUrl } alt="" />
						) }
					</div>
				) ) }
			</div>
		);
	}

	// ── Render ──────────────────────────────────────────────────────────────

	return (
		<>
			{ /* ── Sidebar ── */ }
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
				</PanelBody>

				<PanelBody title={ __( 'Jump nav labels', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Platform label', 'cropx' ) }
						value={ platformLabel }
						onChange={ ( v ) => setAttributes( { platformLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Hardware label', 'cropx' ) }
						value={ hardwareLabel }
						onChange={ ( v ) => setAttributes( { hardwareLabel: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Platform section header', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Eyebrow', 'cropx' ) }
						value={ platformEyebrow }
						onChange={ ( v ) => setAttributes( { platformEyebrow: v } ) }
					/>
					<TextControl
						label={ __( 'Heading', 'cropx' ) }
						value={ platformHeading }
						onChange={ ( v ) => setAttributes( { platformHeading: v } ) }
					/>
					<TextareaControl
						label={ __( 'Blurb (optional)', 'cropx' ) }
						value={ platformBlurb }
						onChange={ ( v ) => setAttributes( { platformBlurb: v } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Hardware section header', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Eyebrow', 'cropx' ) }
						value={ hardwareEyebrow }
						onChange={ ( v ) => setAttributes( { hardwareEyebrow: v } ) }
					/>
					<TextControl
						label={ __( 'Heading', 'cropx' ) }
						value={ hardwareHeading }
						onChange={ ( v ) => setAttributes( { hardwareHeading: v } ) }
					/>
					<TextareaControl
						label={ __( 'Blurb (optional)', 'cropx' ) }
						value={ hardwareBlurb }
						onChange={ ( v ) => setAttributes( { hardwareBlurb: v } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>

					{ /* Section switcher — select which section's items to edit */ }
					<div className="psec-items-switcher">
						<button
							type="button"
							className={ `psec-items-switcher__btn${ isPlatform ? ' is-active' : '' }` }
							onClick={ () => switchSection( 'platform' ) }
						>
							{ platformLabel }
						</button>
						<button
							type="button"
							className={ `psec-items-switcher__btn${ ! isPlatform ? ' is-active' : '' }` }
							onClick={ () => switchSection( 'hardware' ) }
						>
							{ hardwareLabel }
						</button>
					</div>

					{ items.map( ( item, idx ) => (
						<div
							key={ idx }
							ref={ ( el ) => { itemPanelRefs.current[ idx ] = el; } }
							onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
							onDragLeave={ () => setDragOverIdx( null ) }
							onDrop={ () => dropItem( idx ) }
							onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
							style={ {
								borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx
									? '2px solid var(--wp-admin-theme-color, #007cba)'
									: '2px solid transparent',
								opacity: dragIdx === idx ? 0.4 : 1,
								transition: 'opacity 0.1s',
							} }
						>
							{ /* Drag handle + item label + reorder buttons */ }
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
								<Button variant="tertiary" isSmall onClick={ () => setItems( moveItem( items, idx, 'up' ) ) }   disabled={ idx === 0 }               label={ __( 'Move up',   'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setItems( moveItem( items, idx, 'down' ) ) } disabled={ idx === items.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
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

								{ /* ── Photo ── */ }
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
											min={ 100 } max={ 200 }
										/>
									</>
								) }

								{ /* ── Illustration overlay ── */ }
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
											? __( 'Illustration extends from photo column into the text area.', 'cropx' )
											: item.overlayType === 'contained'
											? __( 'Illustration contained within the photo column.', 'cropx' )
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
													help={ __( 'Size of the illustration as a percentage of card height.', 'cropx' ) }
													value={ item.overlayH ?? 100 }
													onChange={ ( v ) => updateItem( idx, 'overlayH', v ) }
													min={ 50 } max={ 160 }
												/>
												<SelectControl
													label={ __( 'Vertical anchor', 'cropx' ) }
													value={ item.overlayAnchor ?? 'center' }
													options={ [
														{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
														{ label: __( 'Bottom edge',      'cropx' ), value: 'bottom' },
													] }
													onChange={ ( v ) => updateItem( idx, 'overlayAnchor', v ) }
												/>
												<ToggleControl
													label={ __( 'Center on photo column', 'cropx' ) }
													checked={ item.overlayCentered ?? false }
													onChange={ ( v ) => updateItem( idx, 'overlayCentered', v ) }
												/>
												{ ! ( item.overlayCentered ?? false ) && (
													<RangeControl
														label={ __( 'Horizontal offset (px)', 'cropx' ) }
														help={ __( 'Negative shifts left into the text column.', 'cropx' ) }
														value={ item.overlayX ?? 0 }
														onChange={ ( v ) => updateItem( idx, 'overlayX', v ) }
														min={ -80 } max={ 40 }
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
						style={ { width: '100%', justifyContent: 'center', marginTop: '8px' } }
					>
						{ `+ ${ __( 'Add', 'cropx' ) } ${ activeLabel } ${ __( 'item', 'cropx' ) }` }
					</Button>

				</PanelBody>

			</InspectorControls>

			{ /* ── Canvas ── */ }
			<div { ...blockProps }>

				{ /* Jump nav bar — static in editor, used to switch the canvas preview */ }
				<div className="psec-editor-jump-bar">
					<button
						type="button"
						className={ `psec-editor-jump-tab${ isPlatform ? ' is-active' : '' }` }
						onClick={ () => switchSection( 'platform' ) }
					>
						{ platformLabel }
					</button>
					<button
						type="button"
						className={ `psec-editor-jump-tab${ ! isPlatform ? ' is-active' : '' }` }
						onClick={ () => switchSection( 'hardware' ) }
					>
						{ hardwareLabel }
					</button>
				</div>

				{ /* Section preview */ }
				<div className="psec-editor-section-canvas">
					{ isPlatform && (
						<>
							{ ( platformEyebrow || platformHeading || platformBlurb ) && (
								<header className="psec-editor-panel-header">
									{ platformEyebrow && <span className="psec-editor-panel-eyebrow">{ platformEyebrow }</span> }
									{ platformHeading && <h2 className="psec-editor-panel-heading">{ platformHeading }</h2> }
									{ platformBlurb   && <p  className="psec-editor-panel-blurb">{ platformBlurb }</p> }
								</header>
							) }
							{ renderCardGrid( platformItems ) }
						</>
					) }
					{ ! isPlatform && (
						<>
							{ ( hardwareEyebrow || hardwareHeading || hardwareBlurb ) && (
								<header className="psec-editor-panel-header">
									{ hardwareEyebrow && <span className="psec-editor-panel-eyebrow">{ hardwareEyebrow }</span> }
									{ hardwareHeading && <h2 className="psec-editor-panel-heading">{ hardwareHeading }</h2> }
									{ hardwareBlurb   && <p  className="psec-editor-panel-blurb">{ hardwareBlurb }</p> }
								</header>
							) }
							{ renderCardGrid( hardwareItems ) }
						</>
					) }
				</div>

			</div>
		</>
	);
}
