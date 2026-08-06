import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import { reorderByDrag } from '../../shared/reorder';
import './editor.css';

const MAX_ITEMS = 12;
const MAX_CTAS = 3;

const ITEM_EYEBROW_COLOR_OPTIONS = [
	{ label: __( 'Deep Blue (default)', 'cropx' ), value: 'deep-blue' },
	{ label: __( 'CropX Blue',          'cropx' ), value: 'cropx-blue' },
	{ label: __( 'Neutral Gray',        'cropx' ), value: 'gray' },
];

const INTRO_EYEBROW_COLOR_OPTIONS = [
	{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
	{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue' },
	{ label: __( 'White',                'cropx' ), value: 'white' },
];

// Maps an itemEyebrowColor attribute value to the actual token variable name —
// 'gray' isn't a token on its own, it means --gray-700.
const eyebrowColorVar = ( color ) => ( color === 'gray' || ! color ) ? 'gray-700' : color;

// Non-breaking space glues the arrow to the last word of the label so a
// line-wrap can never strand the arrow on its own line — see the matching
// note in style.css above .usn-cta svg.
const ARROW = (
	<>
		{ ' ' }
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	</>
);

// Splits a CTA label into everything-but-the-last-word and the last word,
// mirroring render.php's split — the last word gets wrapped together with
// the arrow in a white-space:nowrap span (.usn-cta-tail) so the canvas
// preview matches the front end: an inline-block element (the arrow <svg>)
// always carries an implicit line-break opportunity on either side of it,
// so a non-breaking space next to it isn't enough on its own to stop the
// arrow landing alone on its own line.
function splitCtaLabel( label ) {
	const trimmed = ( label ?? '' ).trim();
	const lastSpace = trimmed.lastIndexOf( ' ' );
	if ( lastSpace === -1 ) {
		return { lead: '', tail: trimmed };
	}
	return { lead: trimmed.slice( 0, lastSpace ), tail: trimmed.slice( lastSpace + 1 ) };
}

const emptyItem = () => ( {
	imageId: 0, imageUrl: '', imageAlt: '',
	showEyebrow: true, eyebrow: '',
	heading: '', body: '',
	ctas: [ { label: '', url: '' } ],
} );

export default function Edit( { attributes, setAttributes } ) {
	const {
		introEyebrow, showIntroEyebrow, introEyebrowColor,
		heading, showHeading, headingAlign,
		introBody,
		bgColor = 'white',
		itemEyebrowColor,
		iconSize,
		items = [],
	} = attributes;

	const blockProps = useBlockProps( {
		className: `usn-section usn-section--bg-${ bgColor }`,
		style: { '--usn-icon-size': `${ iconSize ?? 72 }px` },
	} );

	function updateItem( index, patch ) {
		const updated = items.map( ( it, i ) => ( i === index ? { ...it, ...patch } : it ) );
		setAttributes( { items: updated } );
	}

	function addItem() {
		if ( items.length >= MAX_ITEMS ) return;
		setAttributes( { items: [ ...items, emptyItem() ] } );
	}

	function removeItem( index ) {
		setAttributes( { items: items.filter( ( _, i ) => i !== index ) } );
	}

	function moveItemAt( index, dir ) {
		const to = dir === 'up' ? index - 1 : index + 1;
		if ( to < 0 || to >= items.length ) return;
		setAttributes( { items: reorderByDrag( items, index, to ) } );
	}

	function dropItemAt( from, to ) {
		if ( from === to ) return;
		setAttributes( { items: reorderByDrag( items, from, to ) } );
	}

	function updateCta( itemIndex, ctaIndex, patch ) {
		const item = items[ itemIndex ];
		const ctas = ( item.ctas ?? [] ).map( ( c, i ) => ( i === ctaIndex ? { ...c, ...patch } : c ) );
		updateItem( itemIndex, { ctas } );
	}

	function addCta( itemIndex ) {
		const ctas = items[ itemIndex ].ctas ?? [];
		if ( ctas.length >= MAX_CTAS ) return;
		updateItem( itemIndex, { ctas: [ ...ctas, { label: '', url: '' } ] } );
	}

	function removeCta( itemIndex, ctaIndex ) {
		const ctas = ( items[ itemIndex ].ctas ?? [] ).filter( ( _, i ) => i !== ctaIndex );
		updateItem( itemIndex, { ctas } );
	}

	function onSelectImage( itemIndex, media ) {
		updateItem( itemIndex, { imageId: media.id, imageUrl: media.url, imageAlt: media.alt ?? '' } );
	}

	function onRemoveImage( itemIndex ) {
		updateItem( itemIndex, { imageId: 0, imageUrl: '', imageAlt: '' } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White (default)', 'cropx' ), value: 'white' },
							{ label: __( 'Taupe 50',         'cropx' ), value: 'taupe' },
							{ label: __( 'Deep Blue',        'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show heading', 'cropx' ) }
						checked={ showHeading !== false }
						onChange={ ( v ) => setAttributes( { showHeading: v } ) }
					/>
					{ showHeading !== false && (
						<SelectControl
							label={ __( 'Heading alignment', 'cropx' ) }
							value={ headingAlign ?? 'center' }
							options={ [
								{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
								{ label: __( 'Left',              'cropx' ), value: 'left' },
							] }
							onChange={ ( v ) => setAttributes( { headingAlign: v } ) }
						/>
					) }
					<RangeControl
						label={ __( 'Image size', 'cropx' ) }
						help={ __( 'Applies uniformly to every item’s image.', 'cropx' ) }
						value={ iconSize ?? 72 }
						onChange={ ( v ) => setAttributes( { iconSize: v } ) }
						min={ 48 }
						max={ 140 }
						step={ 2 }
					/>
					<SelectControl
						label={ __( 'Item eyebrow color', 'cropx' ) }
						help={ __( 'Applies to every item\'s eyebrow uniformly.', 'cropx' ) }
						value={ itemEyebrowColor ?? 'deep-blue' }
						options={ ITEM_EYEBROW_COLOR_OPTIONS }
						onChange={ ( v ) => setAttributes( { itemEyebrowColor: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Intro Header', 'cropx' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show intro eyebrow', 'cropx' ) }
						checked={ showIntroEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showIntroEyebrow: v } ) }
					/>
					{ showIntroEyebrow !== false && (
						<SelectControl
							label={ __( 'Intro eyebrow color', 'cropx' ) }
							value={ introEyebrowColor ?? 'cropx-blue' }
							options={ INTRO_EYEBROW_COLOR_OPTIONS }
							onChange={ ( v ) => setAttributes( { introEyebrowColor: v } ) }
						/>
					) }
				</PanelBody>

				{ items.map( ( item, index ) => {
					const {
						imageUrl,
						showEyebrow, eyebrow,
						heading: itemHeading, body,
						ctas = [],
					} = item;

					return (
						<div
							key={ index }
							className="usn-item-panel-wrap"
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
								dropItemAt( from, index );
							} }
						>
							<PanelBody
								title={ `${ __( 'Item', 'cropx' ) } ${ index + 1 }${ itemHeading ? ' — ' + itemHeading : '' }` }
								initialOpen={ false }
							>
								<div className="usn-item-reorder">
									<span className="usn-item-drag-handle" aria-hidden="true" title={ __( 'Drag to reorder', 'cropx' ) }>
										<svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<circle cx="3" cy="2.5"  r="1.5"/>
											<circle cx="7" cy="2.5"  r="1.5"/>
											<circle cx="3" cy="8"    r="1.5"/>
											<circle cx="7" cy="8"    r="1.5"/>
											<circle cx="3" cy="13.5" r="1.5"/>
											<circle cx="7" cy="13.5" r="1.5"/>
										</svg>
									</span>
									<button
										type="button"
										disabled={ index === 0 }
										onClick={ () => moveItemAt( index, 'up' ) }
										aria-label={ __( 'Move up', 'cropx' ) }
									>↑</button>
									<span>{ index + 1 } / { items.length }</span>
									<button
										type="button"
										disabled={ index === items.length - 1 }
										onClick={ () => moveItemAt( index, 'down' ) }
										aria-label={ __( 'Move down', 'cropx' ) }
									>↓</button>
								</div>

								{ imageUrl && (
									<div className="usn-image-preview">
										<img src={ imageUrl } alt="" />
									</div>
								) }
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) => onSelectImage( index, media ) }
										allowedTypes={ [ 'image' ] }
										render={ ( { open } ) => (
											<Button onClick={ open } variant="secondary" style={ { marginBottom: '8px', marginRight: '8px' } }>
												{ imageUrl ? __( 'Replace image', 'cropx' ) : __( 'Select image', 'cropx' ) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
								{ imageUrl && (
									<Button onClick={ () => onRemoveImage( index ) } variant="link" isDestructive style={ { marginBottom: '8px' } }>
										{ __( 'Remove image', 'cropx' ) }
									</Button>
								) }

								<ToggleControl
									label={ __( 'Show eyebrow', 'cropx' ) }
									checked={ showEyebrow !== false }
									onChange={ ( v ) => updateItem( index, { showEyebrow: v } ) }
								/>
								{ showEyebrow !== false && (
									<TextControl
										label={ __( 'Eyebrow text', 'cropx' ) }
										help={ __( 'Color is set once for the whole block in Section Settings above.', 'cropx' ) }
										value={ eyebrow ?? '' }
										onChange={ ( v ) => updateItem( index, { eyebrow: v } ) }
									/>
								) }

								<TextControl
									label={ __( 'Heading', 'cropx' ) }
									value={ itemHeading ?? '' }
									onChange={ ( v ) => updateItem( index, { heading: v } ) }
								/>
								<TextareaControl
									label={ __( 'Body (optional)', 'cropx' ) }
									value={ body ?? '' }
									onChange={ ( v ) => updateItem( index, { body: v } ) }
									rows={ 3 }
								/>

								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', margin: '16px 0 4px' } }>
									{ __( 'CTAs', 'cropx' ) }
								</p>
								{ ctas.map( ( cta, ctaIndex ) => (
									<div className="usn-cta-row" key={ ctaIndex }>
										<div className="usn-cta-row-header">
											<span>{ __( 'CTA', 'cropx' ) } { ctaIndex + 1 }</span>
											<button
												type="button"
												className="usn-cta-remove"
												onClick={ () => removeCta( index, ctaIndex ) }
												aria-label={ __( 'Remove CTA', 'cropx' ) }
											>✕</button>
										</div>
										<TextControl
											label={ __( 'Label', 'cropx' ) }
											value={ cta.label ?? '' }
											onChange={ ( v ) => updateCta( index, ctaIndex, { label: v } ) }
											__nextHasNoMarginBottom
										/>
										<TextControl
											label={ __( 'URL', 'cropx' ) }
											value={ cta.url ?? '' }
											onChange={ ( v ) => updateCta( index, ctaIndex, { url: v } ) }
											__nextHasNoMarginBottom
										/>
									</div>
								) ) }
								<Button
									variant="secondary"
									className="usn-add-cta-btn"
									disabled={ ctas.length >= MAX_CTAS }
									onClick={ () => addCta( index ) }
								>
									{ ctas.length >= MAX_CTAS
										? __( 'Maximum 3 CTAs reached', 'cropx' )
										: __( '+ Add CTA', 'cropx' ) }
								</Button>

								<div className="usn-item-panel-remove">
									<Button variant="link" isDestructive onClick={ () => removeItem( index ) }>
										{ __( 'Remove item', 'cropx' ) }
									</Button>
								</div>
							</PanelBody>
						</div>
					);
				} ) }

				<div style={ { padding: '8px 16px 16px' } }>
					<Button
						variant="primary"
						className="usn-add-item-btn"
						disabled={ items.length >= MAX_ITEMS }
						onClick={ addItem }
					>
						{ items.length >= MAX_ITEMS
							? __( 'Maximum 12 items reached', 'cropx' )
							: __( '+ Add item', 'cropx' ) }
					</Button>
				</div>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="usn-inner">

					{ ( showIntroEyebrow !== false || showHeading !== false || introBody ) && (
						<div className={ `usn-header section-header${ ( headingAlign ?? 'center' ) === 'left' ? ' section-header--left' : '' }` }>
							{ showIntroEyebrow !== false && (
								<RichText
									tagName="span"
									className="section-eyebrow"
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									value={ introEyebrow }
									onChange={ ( v ) => setAttributes( { introEyebrow: v } ) }
									allowedFormats={ [] }
									style={ { color: `var(--${ introEyebrowColor ?? 'cropx-blue' })` } }
								/>
							) }
							{ showHeading !== false && (
								<RichText
									tagName="h2"
									className="section-heading"
									placeholder={ __( 'Section heading…', 'cropx' ) }
									value={ heading }
									onChange={ ( v ) => setAttributes( { heading: v } ) }
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
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
					) }

					{ items.length > 0 ? (
						<div className="usn-grid">
							{ items.map( ( item, index ) => {
								const {
									imageUrl, imageAlt,
									showEyebrow, eyebrow,
									heading: itemHeading, body,
									ctas = [],
								} = item;
								const visibleCtas = ctas.filter( ( c ) => c.label );

								return (
									<div className="usn-item" key={ index }>
										{ imageUrl && (
											<div className="usn-image-wrap">
												<div className="usn-image-box" aria-hidden="true">
													<img src={ imageUrl } alt={ imageAlt } />
												</div>
											</div>
										) }
										<div className="usn-content">
											{ showEyebrow !== false && eyebrow && (
												<span
													className="usn-eyebrow"
													// Suppressed on Deep Blue sections — see the matching
													// comment in render.php for why.
													style={ bgColor !== 'deep-blue'
														? { color: `var(--${ eyebrowColorVar( itemEyebrowColor ) })` }
														: undefined }
												>
													{ eyebrow }
												</span>
											) }
											{ itemHeading && (
												<h3 className="usn-item-heading">{ itemHeading }</h3>
											) }
											{ body && (
												<p className="usn-body">{ body }</p>
											) }
											{ visibleCtas.length > 0 && (
												<div className="usn-ctas">
													{ visibleCtas.map( ( c, i ) => {
														const { lead, tail } = splitCtaLabel( c.label );
														return (
															<span className="usn-cta usn-cta-preview" key={ i } aria-hidden="true">
																{ lead && `${ lead } ` }
																<span className="usn-cta-tail">{ tail }{ ARROW }</span>
															</span>
														);
													} ) }
												</div>
											) }
										</div>
									</div>
								);
							} ) }
						</div>
					) : (
						<div className="usn-canvas-empty">
							<p>{ __( 'Use the block settings sidebar to add items.', 'cropx' ) }</p>
						</div>
					) }

				</div>
			</section>
		</>
	);
}
