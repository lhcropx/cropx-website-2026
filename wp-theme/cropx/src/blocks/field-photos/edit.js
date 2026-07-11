import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
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
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		photos = [],
		eyebrow,
		heading,
		showEyebrow,
		showHeading,
		eyebrowColor,
		bgColor,
	} = attributes;

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

				{ ( showEyebrow !== false || showHeading !== false ) && (
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
