import { __, } from '@wordpress/i18n';
import { useState, useRef, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
	Button,
	Spinner,
} from '@wordpress/components';
import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const LINKEDIN_SVG = (
	<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
		<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
	</svg>
);

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

/**
 * Extract the best available photo URL from a fetched media entity.
 */
function getPostPhotoUrl( postData ) {
	if ( ! postData?.media ) return '';
	const sizes = postData.media.media_details?.sizes;
	return (
		sizes?.medium_large?.source_url ||
		sizes?.medium?.source_url ||
		sizes?.thumbnail?.source_url ||
		postData.media.source_url ||
		''
	);
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
		cardStyle,
		groupHeadingAlignment,
		teamMembers,
	} = attributes;

	// ── Click-to-focus: clicking a canvas card scrolls to its sidebar panel ──
	const [ focusedIndex, setFocusedIndex ] = useState( null );
	const panelRefs = useRef( [] );

	// ── Collapsed panels ──
	const [ collapsedPanels, setCollapsedPanels ] = useState( new Set() );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	// ── CPT search state ──
	const [ searchInput, setSearchInput ] = useState( '' );
	const [ searchQuery, setSearchQuery ] = useState( '' );

	useEffect( () => {
		if ( focusedIndex !== null && panelRefs.current[ focusedIndex ] ) {
			panelRefs.current[ focusedIndex ].scrollIntoView( {
				behavior: 'smooth',
				block: 'nearest',
			} );
		}
	}, [ focusedIndex ] );

	// Debounce the search input so we don't fire on every keystroke.
	useEffect( () => {
		const t = setTimeout( () => setSearchQuery( searchInput ), 300 );
		return () => clearTimeout( t );
	}, [ searchInput ] );

	// Collect all postIds currently in the list (used to detect duplicates + as dep).
	const selectedPostIds = teamMembers
		.filter( ( item ) => ( item.type || 'member' ) === 'member' && item.postId )
		.map( ( item ) => item.postId );

	// ── Fetch full CPT data (including meta) directly via apiFetch ──
	// We bypass the data store cache here because the store can return a
	// lightweight cached record from a previous search fetch that omits meta.
	const [ teamPostData, setTeamPostData ] = useState( {} );

	useEffect( () => {
		if ( selectedPostIds.length === 0 ) {
			setTeamPostData( {} );
			return;
		}

		let cancelled = false;
		const idsParam = selectedPostIds.join( ',' );

		apiFetch( {
			path: `/wp/v2/cropx_team_member?include=${ idsParam }&per_page=100&context=edit`,
		} ).then( ( posts ) => {
			if ( cancelled ) return;
			const data = {};
			posts.forEach( ( post ) => { data[ post.id ] = { post }; } );

			// Fetch featured images in a second pass.
			const mediaIds = posts
				.filter( ( p ) => p.featured_media )
				.map( ( p ) => p.featured_media );

			if ( mediaIds.length === 0 ) {
				setTeamPostData( data );
				return;
			}

			apiFetch( {
				path: `/wp/v2/media?include=${ mediaIds.join( ',' ) }&per_page=100`,
			} ).then( ( mediaItems ) => {
				if ( cancelled ) return;
				mediaItems.forEach( ( media ) => {
					posts.forEach( ( post ) => {
						if ( post.featured_media === media.id ) {
							data[ post.id ].media = media;
						}
					} );
				} );
				setTeamPostData( { ...data } );
			} ).catch( () => { if ( ! cancelled ) setTeamPostData( { ...data } ); } );
		} ).catch( () => {} );

		return () => { cancelled = true; };
	}, [ selectedPostIds.join( ',' ) ] );

	// ── Search results via the data store ──
	const searchResults = useSelect(
		( select ) => {
			if ( searchQuery.length < 2 ) return [];
			return select( 'core' ).getEntityRecords( 'postType', 'cropx_team_member', {
				search:   searchQuery,
				per_page: 10,
				status:   'publish',
				orderby:  'title',
				order:    'asc',
			} ) || [];
		},
		[ searchQuery ]
	);

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
			next.has( index ) ? next.delete( index ) : next.add( index );
			return next;
		} );
	}

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

	function addMemberById( postId ) {
		if ( selectedPostIds.includes( postId ) ) return;
		setAttributes( {
			teamMembers: [ ...teamMembers, { type: 'member', id: Date.now(), postId } ],
		} );
		setSearchInput( '' );
	}

	function addGroupHeading() {
		setAttributes( {
			teamMembers: [ ...teamMembers, { type: 'group', id: Date.now(), label: '' } ],
		} );
	}

	function removeItem( index ) {
		setAttributes( {
			teamMembers: teamMembers.filter( ( _, i ) => i !== index ),
		} );
		if ( focusedIndex === index ) setFocusedIndex( null );
	}

	function updateItem( index, patch ) {
		setAttributes( {
			teamMembers: teamMembers.map( ( item, i ) =>
				i === index ? { ...item, ...patch } : item
			),
		} );
	}

	const isDark = backgroundStyle === 'dark';

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
						label={ __( 'Card style', 'cropx' ) }
						value={ cardStyle }
						options={ [
							{ label: 'White card', value: 'white' },
							{ label: 'Deep Blue card', value: 'dark' },
						] }
						onChange={ ( val ) => setAttributes( { cardStyle: val } ) }
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

					{ /* ── Selected members list ── */ }
					{ teamMembers.map( ( item, index ) => {
						const isGroup      = ( item.type || 'member' ) === 'group';
						const isFocused    = focusedIndex === index;
						const isCollapsed  = collapsedPanels.has( index );
						const isDragging   = dragIdx === index;
						const isDragTarget = dragOverIdx === index && dragOverIdx !== dragIdx;

						// For member items, look up CPT data for the display name.
						const postInfo    = isGroup ? null : teamPostData?.[ item.postId ];
						const displayName = isGroup
							? ( item.label || __( 'Section heading', 'cropx' ) )
							: ( postInfo?.post?.title?.rendered || `${ __( 'Team Member', 'cropx' ) } ${ index + 1 }` );

						const panelClass = [
							isGroup ? 'ps-group-panel' : 'ps-member-panel',
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
									opacity:    isDragging ? 0.4 : 1,
									borderTop:  isDragTarget ? '2px solid #007cba' : '2px solid transparent',
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

										{/* Collapse toggle */}
										<button
											type="button"
											className="ps-collapse-toggle"
											onClick={ () => toggleCollapse( index ) }
											aria-expanded={ ! isCollapsed }
										>
											{ displayName }
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
													onChange={ ( val ) => updateItem( index, { label: val } ) }
												/>
											) : (
												<p className="ps-cpt-note">
													{ __( 'Edit name, photo, title, and LinkedIn in the ', 'cropx' ) }
													<strong>{ __( 'Team Members', 'cropx' ) }</strong>
													{ __( ' admin area.', 'cropx' ) }
												</p>
											) }
										</div>
									) }
								</div>
							</div>
						);
					} ) }

					{/* ── Search to add members ── */}
					<div className="ps-search-wrap">
						<TextControl
							label={ __( 'Add team member', 'cropx' ) }
							placeholder={ __( 'Type a name to search…', 'cropx' ) }
							value={ searchInput }
							onChange={ setSearchInput }
						/>
						{ searchInput.length >= 2 && (
							<div className="ps-search-results">
								{ searchResults === null ? (
									<div className="ps-search-loading"><Spinner /></div>
								) : searchResults.length === 0 ? (
									<p className="ps-search-empty">{ __( 'No team members found.', 'cropx' ) }</p>
								) : (
									searchResults.map( ( result ) => {
										const alreadyAdded = selectedPostIds.includes( result.id );
										return (
											<button
												key={ result.id }
												type="button"
												className={ `ps-search-result${ alreadyAdded ? ' ps-search-result--added' : '' }` }
												onClick={ () => ! alreadyAdded && addMemberById( result.id ) }
												disabled={ alreadyAdded }
											>
												<span className="ps-search-result-name">
													{ result.title?.rendered || __( '(Untitled)', 'cropx' ) }
												</span>
												{ result.cropx_team_data?.job_title && (
													<span className="ps-search-result-role">
														{ result.cropx_team_data.job_title }
													</span>
												) }
												{ alreadyAdded && (
													<span className="ps-search-result-badge">
														{ __( 'Added', 'cropx' ) }
													</span>
												) }
											</button>
										);
									} )
								) }
							</div>
						) }
					</div>

					<hr className="ps-section-divider" />

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
								{ section.members.map( ( { item, index } ) => {
									const postInfo   = teamPostData?.[ item.postId ];
									const photoUrl   = getPostPhotoUrl( postInfo );
									const name       = postInfo?.post?.title?.rendered || '';
									const role       = postInfo?.post?.cropx_team_data?.job_title || '';
									const linkedin   = postInfo?.post?.cropx_team_data?.linkedin_url || '';

									return (
										<article
											key={ item.id }
											className={ `team-card team-card--${ cardStyle }${ focusedIndex === index ? ' ps-card--focused' : '' }` }
											onClick={ () => focusPanel( index ) }
											style={ { cursor: 'pointer' } }
										>
											<div className={ photoClass }>
												{ photoUrl ? (
													<img src={ photoUrl } alt={ name } />
												) : (
													<div className="ps-photo-empty">
														{ postInfo
															? <span>{ __( 'No photo set', 'cropx' ) }</span>
															: <Spinner />
														}
													</div>
												) }
											</div>
											<div className="team-info">
												<div className="team-name-row">
													<p className="team-name">{ name || __( 'Loading…', 'cropx' ) }</p>
													{ linkedin && (
														<span className="team-linkedin-icon" aria-label={ `${ name } on LinkedIn` }>
															<span className="linkedin-badge">{ LINKEDIN_SVG }</span>
														</span>
													) }
												</div>
												<p className="team-role">{ role }</p>
											</div>
										</article>
									);
								} ) }
							</div>
						</div>
					) ) }

				</div>
			</section>
		</>
	);
}
