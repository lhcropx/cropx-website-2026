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
	SelectControl,
	Button,
	RadioControl,
	RangeControl,
	CheckboxControl,
	ComboboxControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const CONTENT_TYPE_OPTIONS = [
	{ label: __( 'Case Study',  'cropx' ), value: 'case-study'  },
	{ label: __( 'White Paper', 'cropx' ), value: 'white-paper' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		bgColor = 'taupe',
		cardVariant,
		showHeader,
		eyebrow,
		heading,
		cards,
		eyebrowColor,
		queryMode        = 'manual',
		queryPostType    = 'cropx_case_study',
		queryLimit       = 3,
		queryContentTypes = [],
		excerptLines     = 4,
		manualPosts,
	} = attributes;

	const isDark   = cardVariant === 'dark';
	const tagClass = isDark ? 'crd-tag crd-tag--white' : 'crd-tag crd-tag--dark';

	const blockProps = useBlockProps( { className: `crd-section crd-section--bg-${bgColor}` } );

	// ── Drag-and-drop reorder state (shared; modes are mutually exclusive) ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropCards( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { cards: reorderByDrag( cards, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	function dropSlots( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { manualPosts: reorderByDrag( slots, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// ── Fetch published posts for the post picker (respects queryPostType) ──
	const allPosts = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'postType', queryPostType || 'cropx_publication', {
			per_page: 100,
			status:   'publish',
			_fields:  'id,title,excerpt',
		} ) ?? [];
	}, [ queryPostType ] );

	// Options for ComboboxControl
	const postPickerOptions = ( allPosts ?? [] ).map( ( p ) => ( {
		value: String( p.id ),
		label: p.title?.rendered ?? `Post #${ p.id }`,
	} ) );

	// ── Manual card helpers ──
	function updateCard( idx, field, value ) {
		setAttributes( {
			cards: cards.map( ( c, i ) => i === idx ? { ...c, [ field ]: value } : c ),
		} );
	}
	function selectCardPhoto( idx, media ) {
		setAttributes( {
			cards: cards.map( ( c, i ) =>
				i === idx ? { ...c, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } : c
			),
		} );
	}
	function clearCardPhoto( idx ) {
		setAttributes( {
			cards: cards.map( ( c, i ) =>
				i === idx ? { ...c, photoId: 0, photoUrl: '', photoAlt: '' } : c
			),
		} );
	}
	function addCard() {
		setAttributes( {
			cards: [ ...cards, {
				photoId: 0, photoUrl: '', photoAlt: '',
				tag: '', tagUrl: '#', date: '',
				title: '', excerpt: '',
				ctaLabel: 'Read more', ctaUrl: '#',
			} ],
		} );
	}
	function removeCard( idx ) {
		if ( cards.length <= 1 ) return;
		setAttributes( { cards: cards.filter( ( _, i ) => i !== idx ) } );
	}

	// ── Posts-mode helpers ──
	const slots = manualPosts ?? [];

	function updateSlot( idx, field, value ) {
		setAttributes( {
			manualPosts: slots.map( ( p, i ) => i === idx ? { ...p, [ field ]: value } : p ),
		} );
	}
	function selectSlotPhoto( idx, media ) {
		setAttributes( {
			manualPosts: slots.map( ( p, i ) =>
				i === idx ? { ...p, imageId: media.id, imageUrl: media.url, imageAlt: media.alt ?? '' } : p
			),
		} );
	}
	function clearSlotPhoto( idx ) {
		setAttributes( {
			manualPosts: slots.map( ( p, i ) =>
				i === idx ? { ...p, imageId: 0, imageUrl: '', imageAlt: '' } : p
			),
		} );
	}
	function addSlot() {
		setAttributes( {
			manualPosts: [ ...slots, { postId: 0, titleOverride: '', excerptOverride: '', imageId: 0, imageUrl: '', imageAlt: '', ctaLabel: '' } ],
		} );
	}
	function removeSlot( idx ) {
		if ( slots.length <= 1 ) return;
		setAttributes( { manualPosts: slots.filter( ( _, i ) => i !== idx ) } );
	}

	// ── Auto-mode content type toggle ──
	function toggleContentType( slug, checked ) {
		const types = queryContentTypes ?? [];
		setAttributes( {
			queryContentTypes: checked
				? [ ...types, slug ]
				: types.filter( ( t ) => t !== slug ),
		} );
	}

	// ── Preview helpers for posts + auto canvas ──
	// For posts mode: look up each slot's selected post in allPosts
	const resolvedSlots = slots.map( ( slot ) => {
		if ( ! slot.postId ) return null;
		return ( allPosts ?? [] ).find( ( p ) => p.id === slot.postId ) ?? null;
	} );

	// For auto mode: first N posts from allPosts
	const autoPreview = ( allPosts ?? [] ).slice( 0, queryLimit );

	// ── Arrow SVG ──
	const Arrow = () => (
		<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
		</svg>
	);

	// ── Read-only preview card used by posts + auto modes ──
	function PreviewCard( { title, rawExcerpt, imageUrl, ctaLabel = 'Read more' } ) {
		const noTitle = ! title;
		return (
			<article className={ `crd-card${ isDark ? ' crd-card--dark' : '' }` }>
				<div className="crd-card-img-wrap">
					{ imageUrl ? (
						<img className="crd-card-img" src={ imageUrl } alt="" />
					) : (
						<div style={ {
							width: '100%', height: '100%',
							background: isDark ? 'rgba(255,255,255,0.06)' : '#f0f0f0',
							display: 'flex', alignItems: 'center', justifyContent: 'center',
						} }>
							<span style={ { fontSize: '0.75rem', color: isDark ? 'rgba(255,255,255,0.35)' : '#aaa' } }>
								{ __( 'Featured image', 'cropx' ) }
							</span>
						</div>
					) }
				</div>
				<div className="crd-card-body">
					<h3 className="crd-title" style={ { fontStyle: noTitle ? 'italic' : 'normal', opacity: noTitle ? 0.4 : 1 } }>
						{ noTitle ? __( '— No post selected —', 'cropx' ) : title }
					</h3>
					{ rawExcerpt && (
						<p
							className={ `crd-excerpt crd-excerpt--lines-${ excerptLines }` }
							/* eslint-disable-next-line react/no-danger */
							dangerouslySetInnerHTML={ { __html: rawExcerpt } }
						/>
					) }
					<span className="crd-cta" aria-hidden="true">
						{ ctaLabel } <Arrow />
					</span>
				</div>
			</article>
		);
	}

	// ── Mode-change banner shown in canvas for dynamic modes ──
	const modeBanner = ( label ) => (
		<div style={ {
			background: '#e8f0fe', borderRadius: '4px',
			padding: '7px 12px', marginBottom: '16px',
			fontSize: '12px', color: '#1a56db', lineHeight: 1.4,
		} }>
			{ label }
		</div>
	);

	return (
		<>
			<InspectorControls>

				{ /* ── Section Settings ── */ }
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe'     },
							{ label: __( 'White',              'cropx' ), value: 'white'     },
							{ label: __( 'Deep Blue + Topo',   'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Card variant', 'cropx' ) }
						value={ cardVariant }
						options={ [
							{ label: __( 'White cards',     'cropx' ), value: 'white' },
							{ label: __( 'Deep Blue cards', 'cropx' ), value: 'dark'  },
						] }
						onChange={ ( v ) => setAttributes( { cardVariant: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show section header', 'cropx' ) }
						checked={ showHeader }
						onChange={ ( v ) => setAttributes( { showHeader: v } ) }
					/>
					{ showHeader && (
						<>
							<TextControl
								label={ __( 'Eyebrow', 'cropx' ) }
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

				{ /* ── Content source ── */ }
				<PanelBody title={ __( 'Content source', 'cropx' ) } initialOpen={ true }>
					<RadioControl
						label={ __( 'Mode', 'cropx' ) }
						selected={ queryMode }
						options={ [
							{ label: __( 'Manual (custom cards)', 'cropx' ), value: 'manual' },
							{ label: __( 'Choose posts',          'cropx' ), value: 'posts'  },
							{ label: __( 'Auto (latest posts)',   'cropx' ), value: 'auto'   },
						] }
						onChange={ ( v ) => setAttributes( { queryMode: v } ) }
					/>
				</PanelBody>

				{ /* ── Manual cards panel ── */ }
				{ queryMode === 'manual' && (
					<PanelBody title={ __( 'Cards', 'cropx' ) } initialOpen={ true }>
						{ cards.map( ( card, idx ) => (
							<div
								key={ idx }
								onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
								onDragLeave={ () => setDragOverIdx( null ) }
								onDrop={ () => dropCards( idx ) }
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
										{ card.title
											? card.title.replace( /<[^>]+>/g, '' ).substring( 0, 30 ) || `${ __( 'Card', 'cropx' ) } ${ idx + 1 }`
											: `${ __( 'Card', 'cropx' ) } ${ idx + 1 }` }
									</span>
									<Button variant="tertiary" isSmall onClick={ () => setAttributes( { cards: moveItem( cards, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
									<Button variant="tertiary" isSmall onClick={ () => setAttributes( { cards: moveItem( cards, idx, 'down' ) } ) } disabled={ idx === cards.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
								</div>
								<PanelBody title={ `${ __( 'Card', 'cropx' ) } ${ idx + 1 }` } initialOpen={ idx === 0 }>
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) => selectCardPhoto( idx, media ) }
										allowedTypes={ [ 'image' ] }
										value={ card.photoId }
										render={ ( { open } ) => (
											<Button onClick={ open } variant="secondary" style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }>
												{ card.photoUrl ? __( 'Replace photo', 'cropx' ) : __( 'Select photo', 'cropx' ) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
								{ card.photoUrl && (
									<Button onClick={ () => clearCardPhoto( idx ) } variant="link" isDestructive style={ { marginBottom: '4px', display: 'block' } }>
										{ __( 'Remove photo', 'cropx' ) }
									</Button>
								) }
								<TextControl label={ __( 'Tag label', 'cropx' ) } value={ card.tag }      onChange={ ( v ) => updateCard( idx, 'tag',      v ) } />
								<TextControl label={ __( 'Tag URL',   'cropx' ) } value={ card.tagUrl }   onChange={ ( v ) => updateCard( idx, 'tagUrl',   v ) } />
								<TextControl label={ __( 'Date',      'cropx' ) } value={ card.date }     onChange={ ( v ) => updateCard( idx, 'date',     v ) } />
								<TextControl label={ __( 'CTA label', 'cropx' ) } value={ card.ctaLabel } onChange={ ( v ) => updateCard( idx, 'ctaLabel', v ) } />
								<TextControl label={ __( 'Card URL',  'cropx' ) } value={ card.ctaUrl }   onChange={ ( v ) => updateCard( idx, 'ctaUrl',   v ) } />
								<Button onClick={ () => removeCard( idx ) } variant="link" isDestructive disabled={ cards.length <= 1 }>
									{ __( 'Remove card', 'cropx' ) }
								</Button>
								</PanelBody>
							</div>
						) ) }
						<Button onClick={ addCard } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
							{ __( '+ Add card', 'cropx' ) }
						</Button>
					</PanelBody>
				) }

				{ /* ── Post picker panel (posts mode) ── */ }
				{ queryMode === 'posts' && (
					<PanelBody title={ __( 'Posts', 'cropx' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'Post type', 'cropx' ) }
							value={ queryPostType }
							options={ [
								{ label: __( 'Publications', 'cropx' ), value: 'cropx_publication' },
								{ label: __( 'Blog Posts',   'cropx' ), value: 'post'             },
							] }
							onChange={ ( v ) => setAttributes( { queryPostType: v } ) }
						/>
						{ slots.map( ( slot, idx ) => (
							<div
								key={ idx }
								onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
								onDragLeave={ () => setDragOverIdx( null ) }
								onDrop={ () => dropSlots( idx ) }
								onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
								style={ {
									marginBottom: '16px',
									paddingBottom: '16px',
									borderBottom: idx < slots.length - 1 ? '1px solid #e0e0e0' : 'none',
									borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx ? '2px solid var(--wp-admin-theme-color, #007cba)' : '2px solid transparent',
									opacity: dragIdx === idx ? 0.4 : 1,
									transition: 'opacity 0.1s',
								} }
							>
								<div style={ { display: 'flex', alignItems: 'center', gap: '2px', marginBottom: '8px' } }>
									<span
										draggable
										onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
										style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
										title={ __( 'Drag to reorder', 'cropx' ) }
									>⠿</span>
									<p style={ { flex: 1, fontWeight: 600, margin: 0, fontSize: '12px', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
										{ __( 'Card', 'cropx' ) } { idx + 1 }
									</p>
									<Button variant="tertiary" isSmall onClick={ () => setAttributes( { manualPosts: moveItem( slots, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
									<Button variant="tertiary" isSmall onClick={ () => setAttributes( { manualPosts: moveItem( slots, idx, 'down' ) } ) } disabled={ idx === slots.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
								</div>
								<ComboboxControl
									label={ __( 'Select post', 'cropx' ) }
									value={ slot.postId ? String( slot.postId ) : '' }
									options={ postPickerOptions }
									onChange={ ( v ) => updateSlot( idx, 'postId', parseInt( v ?? '0', 10 ) ) }
									allowReset
								/>
								{ slot.postId > 0 && (
									<>
										<p style={ { fontSize: '11px', color: '#757575', margin: '10px 0 4px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' } }>
											{ __( 'Overrides (optional)', 'cropx' ) }
										</p>
										<TextControl
											label={ __( 'Title', 'cropx' ) }
											placeholder={ __( 'Defaults to post title', 'cropx' ) }
											value={ slot.titleOverride }
											onChange={ ( v ) => updateSlot( idx, 'titleOverride', v ) }
										/>
										<TextControl
											label={ __( 'Excerpt', 'cropx' ) }
											placeholder={ __( 'Defaults to post excerpt', 'cropx' ) }
											value={ slot.excerptOverride }
											onChange={ ( v ) => updateSlot( idx, 'excerptOverride', v ) }
										/>
										<TextControl
											label={ __( 'CTA label', 'cropx' ) }
											placeholder={ __( 'Read more', 'cropx' ) }
											value={ slot.ctaLabel }
											onChange={ ( v ) => updateSlot( idx, 'ctaLabel', v ) }
										/>
										<MediaUploadCheck>
											<MediaUpload
												onSelect={ ( media ) => selectSlotPhoto( idx, media ) }
												allowedTypes={ [ 'image' ] }
												value={ slot.imageId }
												render={ ( { open } ) => (
													<Button onClick={ open } variant="secondary" style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }>
														{ slot.imageUrl ? __( 'Replace image override', 'cropx' ) : __( 'Override featured image', 'cropx' ) }
													</Button>
												) }
											/>
										</MediaUploadCheck>
										{ slot.imageUrl && (
											<Button onClick={ () => clearSlotPhoto( idx ) } variant="link" isDestructive style={ { marginBottom: '4px', display: 'block' } }>
												{ __( 'Remove image override', 'cropx' ) }
											</Button>
										) }
									</>
								) }
								<Button onClick={ () => removeSlot( idx ) } variant="link" isDestructive disabled={ slots.length <= 1 }>
									{ __( 'Remove slot', 'cropx' ) }
								</Button>
							</div>
						) ) }
						<Button onClick={ addSlot } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
							{ __( '+ Add slot', 'cropx' ) }
						</Button>
					</PanelBody>
				) }

				{ /* ── Auto query panel ── */ }
				{ queryMode === 'auto' && (
					<PanelBody title={ __( 'Auto Query', 'cropx' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'Post type', 'cropx' ) }
							value={ queryPostType }
							options={ [
								{ label: __( 'Publications', 'cropx' ), value: 'cropx_publication' },
								{ label: __( 'Blog Posts',   'cropx' ), value: 'post'             },
							] }
							onChange={ ( v ) => setAttributes( { queryPostType: v } ) }
						/>
						{ queryPostType === 'cropx_publication' && (
						<>
						<p style={ { fontSize: '12px', color: '#757575', margin: '0 0 12px' } }>
							{ __( 'Filter by content type. Leave all unchecked to show all types.', 'cropx' ) }
						</p>
						{ CONTENT_TYPE_OPTIONS.map( ( { label, value } ) => (
							<CheckboxControl
								key={ value }
								label={ label }
								checked={ ( queryContentTypes ?? [] ).includes( value ) }
								onChange={ ( checked ) => toggleContentType( value, checked ) }
							/>
						) ) }
						</> ) }
					</PanelBody>
				) }

				{ /* ── Card Display Settings (posts + auto only) ── */ }
				{ ( queryMode === 'posts' || queryMode === 'auto' ) && (
					<PanelBody title={ __( 'Card Display Settings', 'cropx' ) } initialOpen={ false }>
						<SelectControl
							label={ __( 'Excerpt length', 'cropx' ) }
							value={ String( excerptLines ) }
							options={ [
								{ label: __( '3 lines', 'cropx' ), value: '3' },
								{ label: __( '4 lines', 'cropx' ), value: '4' },
								{ label: __( '5 lines', 'cropx' ), value: '5' },
							] }
							onChange={ ( v ) => setAttributes( { excerptLines: parseInt( v, 10 ) } ) }
						/>
						{ queryMode === 'auto' && (
							<RangeControl
								label={ __( 'Number of cards', 'cropx' ) }
								value={ queryLimit }
								min={ 1 }
								max={ 9 }
								onChange={ ( v ) => setAttributes( { queryLimit: v } ) }
							/>
						) }
					</PanelBody>
				) }


			</InspectorControls>

			<section { ...blockProps }>
				<div className="crd-inner">

					{ /* Section header — editable in all modes */ }
					{ showHeader && (
						<div className="crd-header">
							{ eyebrow && (
								<span
									className="section-eyebrow"
									style={ bgColor === 'deep-blue'
										? { color: 'rgba(255,255,255,0.7)' }
										: { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
								>
									{ eyebrow }
								</span>
							) }
							<RichText
								tagName="h2"
								className="section-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						</div>
					) }

					{ /* ── Manual mode canvas ── */ }
					{ queryMode === 'manual' && (
						<div className="crd-grid">
							{ cards.map( ( card, idx ) => (
								<article key={ idx } className={ `crd-card${ isDark ? ' crd-card--dark' : '' }` }>
									<div className="crd-card-img-wrap">
										{ card.photoUrl ? (
											<img className="crd-card-img" src={ card.photoUrl } alt={ card.photoAlt } />
										) : (
											<MediaPlaceholder
												onSelect={ ( media ) => selectCardPhoto( idx, media ) }
												allowedTypes={ [ 'image' ] }
												accept="image/*"
												labels={ { title: __( 'Card photo', 'cropx' ) } }
											/>
										) }
									</div>
									<div className="crd-card-body">
										{ ( card.tag || card.date ) && (
											<div className="crd-meta">
												{ card.tag  && <span className={ tagClass }>{ card.tag }</span> }
												{ card.date && <span className={ isDark ? 'crd-date crd-date--dark' : 'crd-date' }>{ card.date }</span> }
											</div>
										) }
										<RichText
											tagName="h3"
											className="crd-title"
											placeholder={ __( 'Card title…', 'cropx' ) }
											value={ card.title }
											onChange={ ( v ) => updateCard( idx, 'title', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic' ] }
										/>
										<RichText
											tagName="p"
											className="crd-excerpt"
											placeholder={ __( 'Card excerpt…', 'cropx' ) }
											value={ card.excerpt }
											onChange={ ( v ) => updateCard( idx, 'excerpt', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
										/>
										{ card.ctaLabel && (
											<span className="crd-cta" aria-hidden="true">
												{ card.ctaLabel }
												<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
													<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
												</svg>
											</span>
										) }
									</div>
								</article>
							) ) }
						</div>
					) }

					{ /* ── Posts mode canvas ── */ }
					{ queryMode === 'posts' && (
						<>
							{ modeBanner( __( 'Choose Posts mode — select posts in the sidebar. Title, excerpt, and image pull from the post automatically; override any field in the sidebar.', 'cropx' ) ) }
							<div className="crd-grid">
								{ slots.map( ( slot, idx ) => {
									const post     = resolvedSlots[ idx ];
									const title    = slot.titleOverride   || post?.title?.rendered   || '';
									const excerpt  = slot.excerptOverride || post?.excerpt?.rendered || '';
									return (
										<PreviewCard
											key={ idx }
											title={ title }
											rawExcerpt={ excerpt }
											imageUrl={ slot.imageUrl }
											ctaLabel={ slot.ctaLabel || 'Read more' }
										/>
									);
								} ) }
							</div>
						</>
					) }

					{ /* ── Auto mode canvas ── */ }
					{ queryMode === 'auto' && (
						<>
							{ modeBanner(
							queryPostType === 'post'
								? __( 'Auto mode — cards populate automatically from the latest blog posts. Configure count in the sidebar.', 'cropx' )
								: __( 'Auto mode — cards populate automatically from the latest publications. Filter by content type and configure count in the sidebar.', 'cropx' )
						) }
							<div className="crd-grid">
								{ autoPreview.length > 0
									? autoPreview.map( ( post, idx ) => (
										<PreviewCard
											key={ idx }
											title={ post.title?.rendered ?? '' }
											rawExcerpt={ post.excerpt?.rendered ?? '' }
											imageUrl=""
											ctaLabel="Read more"
										/>
									) )
									: Array.from( { length: queryLimit } ).map( ( _, idx ) => (
										<PreviewCard key={ idx } title="" rawExcerpt="" imageUrl="" />
									) )
								}
							</div>
						</>
					) }

				</div>
			</section>
		</>
	);
}
