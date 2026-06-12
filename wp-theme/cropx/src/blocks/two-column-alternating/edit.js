import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	MediaPlaceholder,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		showIntro, introHeading, introBody, introCtaLabel, introCtaUrl, showIntroCta,
		rows,
	} = attributes;

	const blockProps = useBlockProps( { className: 'tca-section' } );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropRow( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { rows: reorderByDrag( rows, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// ── Row helpers — always spread to avoid shared references ──

	function updateRowField( idx, field, value ) {
		setAttributes( {
			rows: rows.map( ( r, i ) => i === idx ? { ...r, [ field ]: value } : r ),
		} );
	}

	function selectRowPhoto( idx, media ) {
		setAttributes( {
			rows: rows.map( ( r, i ) =>
				i === idx
					? { ...r, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' }
					: r
			),
		} );
	}

	function clearRowPhoto( idx ) {
		setAttributes( {
			rows: rows.map( ( r, i ) =>
				i === idx ? { ...r, photoId: 0, photoUrl: '', photoAlt: '' } : r
			),
		} );
	}

	function addRow() {
		setAttributes( {
			rows: [ ...rows, { heading: '', body: '', photoId: 0, photoUrl: '', photoAlt: '', photoFocalX: 0.5, photoFocalY: 0.5, photoZoom: 100 } ],
		} );
	}

	function removeRow( idx ) {
		if ( rows.length <= 1 ) return;
		setAttributes( { rows: rows.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show section intro', 'cropx' ) }
						checked={ showIntro }
						onChange={ ( v ) => setAttributes( { showIntro: v } ) }
					/>
					{ showIntro && (
						<PanelBody title={ __( 'CTA Link', 'cropx' ) } initialOpen={ true }>
							<ToggleControl
								label={ __( 'Show CTA', 'cropx' ) }
								checked={ showIntroCta !== false }
								onChange={ ( v ) => setAttributes( { showIntroCta: v } ) }
							/>
							{ showIntroCta !== false && (
								<>
									<TextControl
										label={ __( 'CTA link text', 'cropx' ) }
										value={ introCtaLabel }
										onChange={ ( v ) => setAttributes( { introCtaLabel: v } ) }
									/>
									<TextControl
										label={ __( 'CTA URL', 'cropx' ) }
										value={ introCtaUrl }
										onChange={ ( v ) => setAttributes( { introCtaUrl: v } ) }
									/>
								</>
							) }
						</PanelBody>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Rows', 'cropx' ) } initialOpen={ true }>
					{ rows.map( ( row, idx ) => (
						<div
							key={ idx }
							onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
							onDragLeave={ () => setDragOverIdx( null ) }
							onDrop={ () => dropRow( idx ) }
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
									{ row.heading
										? row.heading.replace( /<[^>]+>/g, '' ).substring( 0, 30 ) || `Row ${ idx + 1 }`
										: `Row ${ idx + 1 }` }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { rows: moveItem( rows, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { rows: moveItem( rows, idx, 'down' ) } ) } disabled={ idx === rows.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>
						<PanelBody
							title={ `Row ${ idx + 1 }` }
							initialOpen={ idx === 0 }
						>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectRowPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ row.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ row.photoUrl
												? __( 'Replace photo', 'cropx' )
												: __( 'Select photo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ row.photoUrl && (
								<Button
									onClick={ () => clearRowPhoto( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove photo', 'cropx' ) }
								</Button>
							) }
							{ row.photoUrl && (
								<>
									<RangeControl
										label={ __( 'Focal X — left (%)', 'cropx' ) }
										value={ Math.round( ( row.photoFocalX ?? 0.5 ) * 100 ) }
										onChange={ ( v ) => updateRowField( idx, 'photoFocalX', v / 100 ) }
										min={ 0 }
										max={ 100 }
									/>
									<RangeControl
										label={ __( 'Focal Y — top (%)', 'cropx' ) }
										value={ Math.round( ( row.photoFocalY ?? 0.5 ) * 100 ) }
										onChange={ ( v ) => updateRowField( idx, 'photoFocalY', v / 100 ) }
										min={ 0 }
										max={ 100 }
									/>
									<RangeControl
										label={ __( 'Zoom (%)', 'cropx' ) }
										value={ row.photoZoom ?? 100 }
										onChange={ ( v ) => updateRowField( idx, 'photoZoom', v ) }
										min={ 100 }
										max={ 200 }
									/>
								</>
							) }
							<Button
								onClick={ () => removeRow( idx ) }
								variant="link"
								isDestructive
								disabled={ rows.length <= 1 }
							>
								{ __( 'Remove row', 'cropx' ) }
							</Button>
						</PanelBody>
						</div>
					) ) }
					<Button
						onClick={ addRow }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add row', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="tca-inner">

					{ showIntro && (
						<div className="tca-intro">
							<RichText
								tagName="h2"
								className="tca-intro-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ introHeading }
								onChange={ ( v ) => setAttributes( { introHeading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<RichText
								tagName="p"
								className="tca-intro-body"
								placeholder={ __( 'Section description…', 'cropx' ) }
								value={ introBody }
								onChange={ ( v ) => setAttributes( { introBody: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ showIntroCta !== false && introCtaLabel && (
								<span className="tca-intro-cta tca-intro-cta-preview" aria-hidden="true">
									{ introCtaLabel }
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
										<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
									</svg>
								</span>
							) }
						</div>
					) }

					<div className="tca-rows">
						{ rows.map( ( row, idx ) => {
							const isPhotoLeft = idx % 2 === 1;
							return (
								<div
									key={ idx }
									className={ `tca-row${ isPhotoLeft ? ' tca-row--photo-left' : '' }` }
								>
									<div className="tca-content">
										<RichText
											tagName="h2"
											className="tca-heading"
											placeholder={ __( 'Row heading…', 'cropx' ) }
											value={ row.heading }
											onChange={ ( v ) => updateRowField( idx, 'heading', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic' ] }
										/>
										<RichText
											tagName="p"
											className="tca-body"
											placeholder={ __( 'Row body…', 'cropx' ) }
											value={ row.body }
											onChange={ ( v ) => updateRowField( idx, 'body', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
										/>
									</div>

									<div className="tca-photo-col">
										{ row.photoUrl ? (
											<img
												className="tca-photo"
												src={ row.photoUrl }
												alt={ row.photoAlt }
												style={ {
													objectPosition: `${ Math.round( ( row.photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( row.photoFocalY ?? 0.5 ) * 100 ) }%`,
													transform: `scale(${ ( ( row.photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
													transformOrigin: `${ Math.round( ( row.photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( row.photoFocalY ?? 0.5 ) * 100 ) }%`,
												} }
											/>
										) : (
											<MediaPlaceholder
												onSelect={ ( media ) => selectRowPhoto( idx, media ) }
												allowedTypes={ [ 'image' ] }
												accept="image/*"
												labels={ { title: __( 'Row photo', 'cropx' ) } }
											/>
										) }
									</div>
								</div>
							);
						} ) }
					</div>

				</div>
			</section>
		</>
	);
}
