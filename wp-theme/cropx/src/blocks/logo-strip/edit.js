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
	SelectControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const themeUri = window.cropxThemeData?.themeUri ?? '';

// Hardcoded fallback logos — shown in the canvas when no custom logos are set,
// and rendered on the front end by the PHP fallback.
const DEFAULT_LOGOS = [
	{ file: 'anheuser-busch-a.svg', alt: 'AB InBev' },
	{ file: 'dairy-holdings.svg',   alt: 'Dairy Holdings' },
	{ file: 'general-mills.svg',    alt: 'General Mills' },
	{ file: 'hzpc.svg',             alt: 'HZPC' },
	{ file: 'mccain.svg',           alt: 'McCain' },
	{ file: 'nasa.svg',             alt: 'NASA' },
	{ file: 'nec.svg',              alt: 'NEC' },
	{ file: 'nestle.svg',           alt: 'Nestlé' },
	{ file: 'pepsico.svg',          alt: 'PepsiCo' },
	{ file: 'ritter-sport.svg',     alt: 'Ritter Sport' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, eyebrowColor, showEyebrow, logos } = attributes;

	const blockProps = useBlockProps( { className: 'logo-strip' } );

	const hasCustomLogos = logos && logos.length > 0;

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropLogo( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { logos: reorderByDrag( logos, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// ── Logo helpers ──────────────────────────────────────────────────────────

	function addLogo( media ) {
		setAttributes( {
			logos: [
				...( logos ?? [] ),
				{ id: media.id, url: media.url, alt: media.alt ?? '' },
			],
		} );
	}

	function updateLogoAlt( idx, alt ) {
		setAttributes( {
			logos: logos.map( ( l, i ) => i === idx ? { ...l, alt } : l ),
		} );
	}

	function replaceLogo( idx, media ) {
		setAttributes( {
			logos: logos.map( ( l, i ) =>
				i === idx ? { id: media.id, url: media.url, alt: media.alt ?? l.alt } : l
			),
		} );
	}

	function removeLogo( idx ) {
		setAttributes( { logos: logos.filter( ( _, i ) => i !== idx ) } );
	}

	// ── Canvas logo list: custom or default fallback ──────────────────────────
	const canvasLogos = hasCustomLogos
		? logos.map( ( l ) => ( { url: l.url, alt: l.alt } ) )
		: DEFAULT_LOGOS.map( ( l ) => ( { url: themeUri + 'assets/logos/' + l.file, alt: l.alt } ) );

	return (
		<>
			<InspectorControls>

				{ /* ── Section Settings ── */ }
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow !== false && (
						<>
							<TextControl
								label={ __( 'Eyebrow text', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							/>
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
						</>
					) }
				</PanelBody>

				{ /* ── Logos panel ── */ }
				<PanelBody title={ __( 'Logos', 'cropx' ) } initialOpen={ true }>
					{ hasCustomLogos ? (
						<>
							{ logos.map( ( logo, idx ) => (
								<div
									key={ idx }
									onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
									onDragLeave={ () => setDragOverIdx( null ) }
									onDrop={ () => dropLogo( idx ) }
									onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
									style={ {
										marginBottom: '12px',
										paddingBottom: '12px',
										borderBottom: idx < logos.length - 1 ? '1px solid #e0e0e0' : 'none',
										borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx ? '2px solid var(--wp-admin-theme-color, #007cba)' : '2px solid transparent',
										opacity: dragIdx === idx ? 0.4 : 1,
										transition: 'opacity 0.1s',
									} }
								>
									<div style={ { display: 'flex', alignItems: 'flex-start', gap: '8px' } }>

										{ /* Drag handle */ }
										<span
											draggable
											onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
											style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '8px 2px 0', lineHeight: 1, flexShrink: 0 } }
											title={ __( 'Drag to reorder', 'cropx' ) }
										>⠿</span>

										{ /* Thumbnail — click to replace */ }
										<MediaUploadCheck>
											<MediaUpload
												onSelect={ ( media ) => replaceLogo( idx, media ) }
												allowedTypes={ [ 'image' ] }
												value={ logo.id }
												render={ ( { open } ) => (
													<button
														onClick={ open }
														title={ __( 'Click to replace', 'cropx' ) }
														style={ {
															flexShrink: 0,
															width: '52px',
															height: '34px',
															padding: '3px',
															border: '1px solid #ddd',
															borderRadius: '3px',
															background: '#f8f8f8',
															cursor: 'pointer',
															display: 'flex',
															alignItems: 'center',
															justifyContent: 'center',
															overflow: 'hidden',
														} }
													>
														{ logo.url ? (
															<img
																src={ logo.url }
																alt=""
																style={ { maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' } }
															/>
														) : (
															<span style={ { fontSize: '10px', color: '#aaa' } }>img</span>
														) }
													</button>
												) }
											/>
										</MediaUploadCheck>

										{ /* Alt text + reorder + remove */ }
										<div style={ { flex: 1, minWidth: 0 } }>
											<TextControl
												label={ __( 'Alt text', 'cropx' ) }
												placeholder={ __( 'Company name', 'cropx' ) }
												value={ logo.alt }
												onChange={ ( v ) => updateLogoAlt( idx, v ) }
												style={ { marginBottom: '4px' } }
											/>
											<div style={ { display: 'flex', alignItems: 'center', gap: '2px' } }>
												<Button variant="tertiary" isSmall onClick={ () => setAttributes( { logos: moveItem( logos, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
												<Button variant="tertiary" isSmall onClick={ () => setAttributes( { logos: moveItem( logos, idx, 'down' ) } ) } disabled={ idx === logos.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
												<Button
													onClick={ () => removeLogo( idx ) }
													variant="link"
													isDestructive
													style={ { fontSize: '11px' } }
												>
													{ __( 'Remove', 'cropx' ) }
												</Button>
											</div>
										</div>
									</div>
								</div>
							) ) }

							{ /* Add another logo */ }
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ addLogo }
									allowedTypes={ [ 'image' ] }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { width: '100%', justifyContent: 'center', marginTop: '4px' } }
										>
											{ __( '+ Add logo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
						</>
					) : (
						<>
							<p style={ { fontSize: '12px', color: '#757575', margin: '0 0 12px', lineHeight: 1.5 } }>
								{ __( 'No custom logos set — using built-in defaults. Upload your own logos to replace them.', 'cropx' ) }
							</p>
							<p style={ { fontSize: '11px', color: '#999', margin: '0 0 12px', lineHeight: 1.5 } }>
								{ __( 'SVG and PNG both work. SVG is preferred for logos — it stays sharp at any size.', 'cropx' ) }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ addLogo }
									allowedTypes={ [ 'image' ] }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="primary"
											style={ { width: '100%', justifyContent: 'center' } }
										>
											{ __( 'Upload first logo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
						</>
					) }
				</PanelBody>

			</InspectorControls>

			<div { ...blockProps }>
				<div className="logo-strip-inner">
					{ showEyebrow !== false && eyebrow && (
						<p className="logo-strip-eyebrow" style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }>
							{ eyebrow }
						</p>
					) }
				</div>
				<div className="ls-marquee">
					<div className="ls-track">
						{ canvasLogos.map( ( logo, i ) => (
							<img
								key={ `a-${ i }` }
								src={ logo.url }
								alt={ logo.alt }
								className="ls-logo"
							/>
						) ) }
						{ canvasLogos.map( ( logo, i ) => (
							<img
								key={ `b-${ i }` }
								src={ logo.url }
								alt=""
								aria-hidden="true"
								className="ls-logo"
							/>
						) ) }
					</div>
				</div>
			</div>
		</>
	);
}
