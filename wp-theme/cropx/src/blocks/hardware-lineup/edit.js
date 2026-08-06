import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	RangeControl,
	ToggleControl,
	Button,
	SelectControl,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { bgColor = 'taupe', eyebrow, eyebrowColor, showEyebrow, items, autoAdvance = true } = attributes;

	const blockProps = useBlockProps( { className: `hwf-section hwf-section--bg-${bgColor}` } );

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

	// ── Item helpers — always spread to avoid shared references ──

	function updateItem( idx, field, value ) {
		setAttributes( {
			items: items.map( ( item, i ) => i === idx ? { ...item, [ field ]: value } : item ),
		} );
	}

	function selectItemImage( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, imageId: media.id, imageUrl: media.url } : item
			),
		} );
	}

	function clearItemImage( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, imageId: 0, imageUrl: '' } : item
			),
		} );
	}

	function addItem() {
		setAttributes( {
			items: [ ...items, {
				name: '', description: '', url: '#',
				imageId: 0, imageUrl: '',
				imageHeight: 90, thumbCentered: false,
			} ],
		} );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
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
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow !== false && (
						<>
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
							<TextControl
								label={ __( 'Eyebrow', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							/>
						</>
					) }
					<ToggleControl
						label={ __( 'Auto-advance', 'cropx' ) }
						help={ __( 'Automatically scrolls the carousel. Pauses on hover and while a visitor is interacting with it.', 'cropx' ) }
						checked={ !! autoAdvance }
						onChange={ ( v ) => setAttributes( { autoAdvance: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>
					{ items.map( ( item, idx ) => (
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
							<div style={ { display: 'flex', alignItems: 'center', gap: '2px', background: '#f0f0f0', padding: '3px 6px', marginBottom: '-1px' } }>
								<span
									draggable
									onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
									style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
									title={ __( 'Drag to reorder', 'cropx' ) }
								>⠿</span>
								<span style={ { flex: 1, fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#666' } }>
									{ item.name || `${ __( 'Item', 'cropx' ) } ${ idx + 1 }` }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { items: moveItem( items, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { items: moveItem( items, idx, 'down' ) } ) } disabled={ idx === items.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>
							<PanelBody title={ `${ __( 'Item', 'cropx' ) } ${ idx + 1 }` } initialOpen={ idx === 0 }>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectItemImage( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ item.imageId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ item.imageUrl
												? __( 'Replace image', 'cropx' )
												: __( 'Select image', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ item.imageUrl && (
								<Button
									onClick={ () => clearItemImage( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove image', 'cropx' ) }
								</Button>
							) }
							<TextControl
								label={ __( 'Name', 'cropx' ) }
								value={ item.name }
								onChange={ ( v ) => updateItem( idx, 'name', v ) }
							/>
							<TextControl
								label={ __( 'Description', 'cropx' ) }
								value={ item.description }
								onChange={ ( v ) => updateItem( idx, 'description', v ) }
							/>
							<TextControl
								label={ __( 'URL', 'cropx' ) }
								value={ item.url }
								onChange={ ( v ) => updateItem( idx, 'url', v ) }
							/>
							<RangeControl
								label={ __( 'Image height (%)', 'cropx' ) }
								value={ item.imageHeight }
								onChange={ ( v ) => updateItem( idx, 'imageHeight', v ) }
								min={ 50 }
								max={ 100 }
							/>
							<ToggleControl
								label={ __( 'Center image vertically', 'cropx' ) }
								help={ __( 'Use for compact hardware like Rivo — centers the image in the pill instead of bottom-aligning it.', 'cropx' ) }
								checked={ item.thumbCentered }
								onChange={ ( v ) => updateItem( idx, 'thumbCentered', v ) }
							/>
							<Button
								onClick={ () => removeItem( idx ) }
								variant="link"
								isDestructive
								disabled={ items.length <= 1 }
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

			<section { ...blockProps }>
				<div className="hwf-header">
					{ showEyebrow !== false && eyebrow && <p className="section-eyebrow" style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }>{ eyebrow }</p> }
				</div>

				{ /* Static horizontal row in editor — no cloning, no auto-scroll */ }
				<div className="hwc-marquee hwc-marquee--editor">
					<div className="hwc-track">
						{ items.map( ( item, idx ) => (
							<div
								key={ idx }
								className="hwf-pill"
								style={ { '--hwf-img-h': `${ item.imageHeight }%` } }
							>
								<span className="hwf-text">
									<span className="hwf-name-row">
										<span className="hwf-name">
											{ item.name || __( 'Product name', 'cropx' ) }
										</span>
									</span>
									<span className="hwf-desc">
										{ item.description || __( 'Description', 'cropx' ) }
									</span>
								</span>
								<span className={ `hwf-thumb${ item.thumbCentered ? ' hwf-thumb--centered' : '' }` }>
									{ item.imageUrl ? (
										<img src={ item.imageUrl } alt="" />
									) : (
										<span className="hwf-thumb-placeholder" />
									) }
								</span>
							</div>
						) ) }
					</div>
				</div>
			</section>
		</>
	);
}
