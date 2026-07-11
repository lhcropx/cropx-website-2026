import { __, } from '@wordpress/i18n';
import { useState, useRef, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls, RichText, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
	Button,
} from '@wordpress/components';
import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const LINKEDIN_SVG = (
	<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
		<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
	</svg>
);

function updateItem( items, index, patch ) {
	return items.map( ( item, i ) => ( i === index ? { ...item, ...patch } : item ) );
}

/**
 * Build renderable sections from the flat mixed-type items array.
 * A 'group' item starts a new section; 'member' items belong to the current section.
 */
function buildRenderSections( items ) {
	const sections = [];
	let current = { heading: null, headingIndex: null, members: [] };

	items.forEach( ( item, index ) => {
		if ( ( item.type || 'member' ) === 'group' ) {
			if ( current.members.length > 0 || current.heading !== null ) {
				sections.push( current );
			}
			current = { heading: item, headingIndex: index, members: [] };
		} else {
			current.members.push( { item, index } );
		}
	} );

	if ( current.members.length > 0 || current.heading !== null ) {
		sections.push( current );
	}

	return sections;
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		backgroundStyle,
		showIntro,
		showEyebrow,
		showBody,
		eyebrowColor,
		introAlignment,
		eyebrow,
		heading,
		body,
		photoRatio,
		groupHeadingAlignment,
		teamMembers,
	} = attributes;

	// ── Click-to-focus: clicking a canvas card scrolls to its sidebar panel ──
	const [ focusedIndex, setFocusedIndex ] = useState( null );
	const panelRefs = useRef( [] );

	// ── Collapsed panels: Set of indices that are currently collapsed ──
	const [ collapsedPanels, setCollapsedPanels ] = useState( new Set() );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	useEffect( () => {
		if ( focusedIndex !== null && panelRefs.current[ focusedIndex ] ) {
			panelRefs.current[ focusedIndex ].scrollIntoView( {
				behavior: 'smooth',
				block: 'nearest',
			} );
		}
	}, [ focusedIndex ] );

	/**
	 * Focus a panel by its index in teamMembers.
	 * Also auto-expands the panel if it was collapsed.
	 */
	function focusPanel( index ) {
		setFocusedIndex( index );
		setCollapsedPanels( ( prev ) => {
			if ( ! prev.has( index ) ) return prev;
			const next = new Set( prev );
			next.delete( index );
			return next;
		} );
	}

	function toggleCollapse( index ) {
		setCollapsedPanels( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( index ) ) {
				next.delete( index );
			} else {
				next.add( index );
			}
			return next;
		} );
	}

	// Drop + clear collapsed state so indices don't drift
	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { teamMembers: reorderByDrag( teamMembers, dragIdx, toIdx ) } );
			setFocusedIndex( null );
			setCollapsedPanels( new Set() );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	function handleMoveItem( index, dir ) {
		setAttributes( { teamMembers: moveItem( teamMembers, index, dir ) } );
		setFocusedIndex( null );
		setCollapsedPanels( new Set() );
	}

	const isDark = backgroundStyle === 'dark';

	// Eyebrow color options vary by background
	const eyebrowColorOptions = isDark
		? [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'White', value: 'white' },
		]
		: [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'Deep Blue', value: 'deep-blue' },
		];

	const EYEBROW_CSS = {
		'cropx-blue': 'var(--cropx-blue)',
		'deep-blue':  'var(--deep-blue)',
		'white':      '#fff',
	};

	const sectionClass = [
		'cropx-people-showcase',
		`people--${ backgroundStyle }`,
	].join( ' ' );

	const headerClass = [
		'section-header',
		introAlignment === 'left' ? 'section-header--left' : '',
	].filter( Boolean ).join( ' ' );

	const photoClass = `team-photo team-photo--${ photoRatio }`;

	function addMember() {
		setAttributes( {
			teamMembers: [
				...teamMembers,
				{
					type: 'member',
					id: Date.now(),
					name: 'Team Member',
					role: 'Job Title',
					photoId: 0,
					photoUrl: '',
					photoAlt: '',
					showLinkedIn: true,
					linkedInUrl: '#',
				},
			],
		} );
	}

	function addGroupHeading() {
		setAttributes( {
			teamMembers: [
				...teamMembers,
				{ type: 'group', id: Date.now(), label: '' },
			],
		} );
	}

	function removeItem( index ) {
		setAttributes( {
			teamMembers: teamMembers.filter( ( _, i ) => i !== index ),
		} );
		if ( focusedIndex === index ) setFocusedIndex( null );
	}

	const blockProps = useBlockProps( { className: sectionClass } );

	return (
		<>
			<InspectorControls>
				{/* ── Section Settings ── */}
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundStyle }
						options={ [
							{ label: 'Taupe 50', value: 'taupe' },
							{ label: 'White',    value: 'white' },
							{ label: 'Deep Blue', value: 'dark'  },
						] }
						onChange={ ( val ) => {
							const newIsDark = val === 'dark';
							const resetColor =
								( newIsDark && eyebrowColor === 'deep-blue' ) ||
								( ! newIsDark && eyebrowColor === 'white' )
									? 'cropx-blue'
									: eyebrowColor;
							setAttributes( { backgroundStyle: val, eyebrowColor: resetColor } );
						} }
					/>
					<ToggleControl
						label={ __( 'Show intro', 'cropx' ) }
						checked={ showIntro }
						onChange={ ( val ) => setAttributes( { showIntro: val } ) }
					/>
					{ showIntro && (
						<>
							<ToggleControl
								label={ __( 'Show eyebrow', 'cropx' ) }
								checked={ showEyebrow }
								onChange={ ( val ) => setAttributes( { showEyebrow: val } ) }
							/>
							{ showEyebrow && (
								<SelectControl
									label={ __( 'Eyebrow color', 'cropx' ) }
									value={ eyebrowColor }
									options={ eyebrowColorOptions }
									onChange={ ( val ) => setAttributes( { eyebrowColor: val } ) }
								/>
							) }
							<ToggleControl
								label={ __( 'Show body paragraph', 'cropx' ) }
								checked={ showBody }
								onChange={ ( val ) => setAttributes( { showBody: val } ) }
							/>
							<SelectControl
								label={ __( 'Alignment', 'cropx' ) }
								value={ introAlignment }
								options={ [
									{ label: 'Centered', value: 'center' },
									{ label: 'Left', value: 'left' },
								] }
								onChange={ ( val ) => setAttributes( { introAlignment: val } ) }
							/>
						</>
					) }
					<SelectControl
						label={ __( 'Photo crop', 'cropx' ) }
						value={ photoRatio }
						options={ [
							{ label: '1:1 Square', value: 'square' },
							{ label: '9:10 Portrait', value: 'portrait' },
						] }
						onChange={ ( val ) => setAttributes( { photoRatio: val } ) }
					/>
					<SelectControl
						label={ __( 'Group heading alignment', 'cropx' ) }
						value={ groupHeadingAlignment }
						options={ [
							{ label: 'Left', value: 'left' },
							{ label: 'Centered', value: 'center' },
						] }
						onChange={ ( val ) => setAttributes( { groupHeadingAlignment: val } ) }
					/>
				</PanelBody>

				{/* ── Team members ── */}
				<PanelBody title={ __( 'Team members', 'cropx' ) } initialOpen={ true }>
					{ teamMembers.map( ( item, index ) => {
						const isGroup = ( item.type || 'member' ) === 'group';
						const isFocused = focusedIndex === index;
						const isCollapsed = collapsedPanels.has( index );
						const isDragging = dragIdx === index;
						const isDragTarget = dragOverIdx === index && dragOverIdx !== dragIdx;

						const panelBase = isGroup ? 'ps-group-panel' : 'ps-member-panel';
						const panelClass = [
							panelBase,
							isFocused ? 'ps-member-panel--focused' : '',
						].filter( Boolean ).join( ' ' );

						return (
							<div
								key={ item.id }
								className="ps-panel-wrapper"
								onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( index ); } }
								onDragLeave={ () => setDragOverIdx( null ) }
								onDrop={ () => dropItem( index ) }
								onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
								style={ {
									opacity: isDragging ? 0.4 : 1,
									borderTop: isDragTarget ? '2px solid #007cba' : '2px solid transparent',
									transition: 'opacity 0.1s',
								} }
							>
								<div
									className={ panelClass }
									ref={ ( el ) => ( panelRefs.current[ index ] = el ) }
								>
									{/* ── Panel header row ── */}
									<div className="ps-member-panel__header">
										{/* Drag handle */}
										<span
											draggable
											onDragStart={ ( e ) => {
												setDragIdx( index );
												e.dataTransfer.effectAllowed = 'move';
											} }
											className="ps-drag-handle"
											title={ __( 'Drag to reorder', 'cropx' ) }
										>⠿</span>

										{/* Collapse toggle — the name/label area */}
										<button
											type="button"
											className="ps-collapse-toggle"
											onClick={ () => toggleCollapse( index ) }
											aria-expanded={ ! isCollapsed }
										>
											{ isGroup
												? ( item.label || __( 'Section heading', 'cropx' ) )
												: ( item.name || `${ __( 'Member', 'cropx' ) } ${ index + 1 }` )
											}
											<span className="ps-chevron" aria-hidden="true">
												{ isCollapsed ? '▶' : '▼' }
											</span>
										</button>

										{/* Reorder buttons */}
										<Button
											variant="tertiary"
											isSmall
											onClick={ () => handleMoveItem( index, 'up' ) }
											disabled={ index === 0 }
											label={ __( 'Move up', 'cropx' ) }
										>↑</Button>
										<Button
											variant="tertiary"
											isSmall
											onClick={ () => handleMoveItem( index, 'down' ) }
											disabled={ index === teamMembers.length - 1 }
											label={ __( 'Move down', 'cropx' ) }
										>↓</Button>

										{/* Remove */}
										<Button
											isDestructive
											isSmall
											onClick={ () => removeItem( index ) }
											label={ __( 'Remove', 'cropx' ) }
										>✕</Button>
									</div>

									{/* ── Panel body (hidden when collapsed) ── */}
									{ ! isCollapsed && (
										<div className="ps-panel-body">
											{ isGroup ? (
												<TextControl
													value={ item.label || '' }
													placeholder={ __( 'e.g. Business Leads', 'cropx' ) }
													onChange={ ( val ) =>
														setAttributes( { teamMembers: updateItem( teamMembers, index, { label: val } ) } )
													}
												/>
											) : (
												<>
													{/* Photo upload */}
													<MediaUploadCheck>
														<MediaUpload
															onSelect={ ( media ) =>
																setAttributes( {
																	teamMembers: updateItem( teamMembers, index, {
																		photoId: media.id,
																		photoUrl: media.url,
																		photoAlt: media.alt || media.title || item.name,
																	} ),
																} )
															}
															allowedTypes={ [ 'image' ] }
															value={ item.photoId }
															render={ ( { open } ) => (
																<div className="ps-photo-upload">
																	{ item.photoUrl ? (
																		<>
																			<img
																				src={ item.photoUrl }
																				alt={ item.photoAlt }
																				className="ps-photo-thumb"
																			/>
																			<Button isSmall onClick={ open }>
																				{ __( 'Replace photo', 'cropx' ) }
																			</Button>
																		</>
																	) : (
																		<Button
																			variant="secondary"
																			onClick={ open }
																			className="ps-photo-placeholder"
																		>
																			{ __( '+ Add photo', 'cropx' ) }
																		</Button>
																	) }
																</div>
															) }
														/>
													</MediaUploadCheck>

													<TextControl
														label={ __( 'Name', 'cropx' ) }
														value={ item.name }
														onChange={ ( val ) =>
															setAttributes( { teamMembers: updateItem( teamMembers, index, { name: val } ) } )
														}
													/>
													<TextControl
														label={ __( 'Role / title', 'cropx' ) }
														value={ item.role }
														onChange={ ( val ) =>
															setAttributes( { teamMembers: updateItem( teamMembers, index, { role: val } ) } )
														}
													/>
													<ToggleControl
														label={ __( 'Show LinkedIn', 'cropx' ) }
														checked={ item.showLinkedIn }
														onChange={ ( val ) =>
															setAttributes( { teamMembers: updateItem( teamMembers, index, { showLinkedIn: val } ) } )
														}
													/>
													{ item.showLinkedIn && (
														<TextControl
															label={ __( 'LinkedIn URL', 'cropx' ) }
															value={ item.linkedInUrl }
															onChange={ ( val ) =>
																setAttributes( { teamMembers: updateItem( teamMembers, index, { linkedInUrl: val } ) } )
															}
														/>
													) }
												</>
											) }
										</div>
									) }
								</div>
							</div>
						);
					} ) }

					<Button
						variant="primary"
						onClick={ addMember }
						className="ps-add-member"
					>
						{ __( '+ Add team member', 'cropx' ) }
					</Button>
					<Button
						variant="secondary"
						onClick={ addGroupHeading }
						className="ps-add-group-heading"
					>
						{ __( '+ Add section heading', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			{/* ── Editor preview ── */}
			<section { ...blockProps }>
				<div className="section-inner">

					{ showIntro && (
						<div className={ headerClass }>
							{ showEyebrow && (
								<RichText
									tagName="span"
									className="section-eyebrow"
									value={ eyebrow }
									onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									style={ { color: EYEBROW_CSS[ eyebrowColor ] ?? 'var(--cropx-blue)' } }
								/>
							) }
							<RichText
								tagName="h2"
								className="section-heading"
								value={ heading }
								onChange={ ( val ) => setAttributes( { heading: val } ) }
								placeholder={ __( 'Heading…', 'cropx' ) }
							/>
							{ showBody && (
								<RichText
									tagName="div"
									className="section-body"
									value={ body }
									onChange={ ( val ) => setAttributes( { body: val } ) }
									placeholder={ __( 'Supporting copy…', 'cropx' ) }
								/>
							) }
						</div>
					) }

					{ buildRenderSections( teamMembers ).map( ( section, si ) => (
						<div key={ si } className="people-group">
							{ section.heading && (
								<h3 className={ `people-group-heading${ groupHeadingAlignment === 'center' ? ' people-group-heading--center' : '' }` }>
									{ section.heading.label || __( 'Section heading', 'cropx' ) }
								</h3>
							) }
							<div className="people-grid">
								{ section.members.map( ( { item, index } ) => (
									<article
										key={ item.id }
										className={ `team-card team-card--white${ focusedIndex === index ? ' ps-card--focused' : '' }` }
										onClick={ () => focusPanel( index ) }
										style={ { cursor: 'pointer' } }
									>
										<div className={ photoClass }>
											{ item.photoUrl ? (
												<img src={ item.photoUrl } alt={ item.photoAlt } />
											) : (
												<div className="ps-photo-empty">
													<span>{ __( 'No photo', 'cropx' ) }</span>
												</div>
											) }
										</div>
										<div className="team-info">
											<div className="team-name-row">
												<p className="team-name">{ item.name }</p>
												{ item.showLinkedIn && (
													<span className="team-linkedin-icon" aria-label={ `${ item.name } on LinkedIn` }>
														<span className="linkedin-badge">{ LINKEDIN_SVG }</span>
													</span>
												) }
											</div>
											<p className="team-role">{ item.role }</p>
										</div>
									</article>
								) ) }
							</div>
						</div>
					) ) }

				</div>
			</section>
		</>
	);
}
