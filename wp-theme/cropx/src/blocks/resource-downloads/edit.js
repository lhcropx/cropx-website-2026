import { useState, useEffect } from '@wordpress/element';
import { useSelect }             from '@wordpress/data';
import { __ }                    from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	Button,
	Spinner,
	TextControl,
} from '@wordpress/components';

import { moveItem } from '../../shared/reorder';

import './editor.css';

/* Document icon — shown in card cover area when no featured image is set. */
const DocIcon = () => (
	<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round"/>
		<polyline points="14 2 14 8 20 8" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round"/>
		<line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
		<line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		selectedIds: legacySelectedIds = [], // legacy — pre-subsection data, migrated below
		groups = [ { subheading: '', selectedIds: [] } ],
		eyebrow,
		heading,
		showEyebrow,
		showHeading,
		bgColor,
		cardColor,
		columns,
		introAlign,
		gridAlign,
	} = attributes;

	/*
	 * One-time migration: blocks saved before subsections existed only carry
	 * the flat `selectedIds` attribute — `groups` will just be its schema
	 * default (a single empty group). If that's the situation (no group has
	 * any resources yet, but the legacy attribute does), fold the legacy IDs
	 * into that first group on mount. This makes an already-published block
	 * show its real content the moment an editor opens it, instead of an
	 * empty picker — render.php has the equivalent fallback for the front
	 * end, so nothing breaks even if this block is never re-opened at all.
	 */
	useEffect( () => {
		const hasGroupContent = groups.some( ( g ) => ( g.selectedIds ?? [] ).length > 0 );
		if ( ! hasGroupContent && legacySelectedIds.length > 0 ) {
			setAttributes( { groups: [ { subheading: '', selectedIds: legacySelectedIds } ] } );
		}
		// Intentionally run once on mount only — this is a one-time migration check.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	function updateGroup( index, patch ) {
		setAttributes( {
			groups: groups.map( ( g, i ) => ( i === index ? { ...g, ...patch } : g ) ),
		} );
	}

	function addGroup() {
		setAttributes( { groups: [ ...groups, { subheading: '', selectedIds: [], collapsible: false, collapsedByDefault: false } ] } );
	}

	function removeGroup( index ) {
		const next = groups.filter( ( _, i ) => i !== index );
		// Never drop below one group — an empty single group is what renders
		// as "just a heading + one grid" once resources are added back.
		setAttributes( { groups: next.length ? next : [ { subheading: '', selectedIds: [], collapsible: false, collapsedByDefault: false } ] } );
	}

	function moveGroup( index, dir ) {
		setAttributes( { groups: moveItem( groups, index, dir ) } );
	}

	const headerClass = `rsd-header${ ( introAlign ?? 'center' ) === 'left' ? ' rsd-header--left' : '' }`;

	const blockProps = useBlockProps( {
		className: `rsd-section rsd-section--bg-${ bgColor }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White (default)', 'cropx' ), value: 'white'     },
							{ label: __( 'Taupe 50',        'cropx' ), value: 'taupe'     },
							{ label: __( 'Deep Blue + Topo','cropx' ), value: 'deep-blue' },
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
					{ /* Eyebrow color is handled automatically by CSS:
					     cropx-blue on light backgrounds, white on deep-blue bg. */ }
				</PanelBody>

				<PanelBody title={ __( 'Layout', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Columns', 'cropx' ) }
						value={ String( columns ) }
						options={ [
							{ label: __( '6 columns',           'cropx' ), value: '6' },
							{ label: __( '5 columns',           'cropx' ), value: '5' },
							{ label: __( '4 columns (default)', 'cropx' ), value: '4' },
							{ label: __( '3 columns',           'cropx' ), value: '3' },
							{ label: __( '2 columns',           'cropx' ), value: '2' },
						] }
						onChange={ ( v ) => setAttributes( { columns: parseInt( v, 10 ) } ) }
					/>
					<ToggleControl
						label={ __( 'Show content type tag', 'cropx' ) }
						help={ __( 'The small "Brochure" / "Report" pill above each title.', 'cropx' ) }
						checked={ attributes.showTypeTag !== false }
						onChange={ ( v ) => setAttributes( { showTypeTag: v } ) }
					/>
					<SelectControl
						label={ __( 'Card color', 'cropx' ) }
						value={ cardColor ?? 'white' }
						options={ [
							{ label: __( 'White (default)', 'cropx' ), value: 'white' },
							{ label: __( 'Deep Blue',       'cropx' ), value: 'blue'  },
						] }
						onChange={ ( v ) => setAttributes( { cardColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Intro content alignment', 'cropx' ) }
						help={ __( 'Aligns the eyebrow, heading, and body text above the grid(s).', 'cropx' ) }
						value={ introAlign ?? 'center' }
						options={ [
							{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
							{ label: __( 'Left',              'cropx' ), value: 'left'   },
						] }
						onChange={ ( v ) => setAttributes( { introAlign: v } ) }
					/>
					<SelectControl
						label={ __( 'Grid cards alignment', 'cropx' ) }
						help={ __( 'Only visible when a subsection has fewer resources than columns — a full row always fills the width either way.', 'cropx' ) }
						value={ gridAlign ?? 'center' }
						options={ [
							{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
							{ label: __( 'Left',              'cropx' ), value: 'left'   },
						] }
						onChange={ ( v ) => setAttributes( { gridAlign: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="rsd-inner">

					{ /* ── Section header ── */ }
					<div className={ headerClass }>
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="section-eyebrow"
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
							/>
						) }
						{ showHeading !== false && (
							<RichText
								tagName="h2"
								className="section-heading"
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
								placeholder={ __( 'Heading…', 'cropx' ) }
							/>
						) }
					</div>

					{ /* ── Subsections — one group is the "just a grid" case, add more for subheadings ── */ }
					{ groups.map( ( group, index ) => (
						<ResourceGroupEditor
							key={ index }
							group={ group }
							groupNumber={ index + 1 }
							isOnly={ groups.length === 1 }
							canMoveUp={ index > 0 }
							canMoveDown={ index < groups.length - 1 }
							columns={ columns }
							cardColor={ cardColor }
							gridAlign={ gridAlign }
							onChangeSubheading={ ( v ) => updateGroup( index, { subheading: v } ) }
							onChangeSelectedIds={ ( ids ) => updateGroup( index, { selectedIds: ids } ) }
							onChangeCollapsible={ ( v ) => updateGroup( index, { collapsible: v } ) }
							onChangeCollapsedByDefault={ ( v ) => updateGroup( index, { collapsedByDefault: v } ) }
							onRemove={ () => removeGroup( index ) }
							onMoveUp={ () => moveGroup( index, 'up' ) }
							onMoveDown={ () => moveGroup( index, 'down' ) }
						/>
					) ) }

					<div className="rsd-add-group">
						<Button variant="secondary" onClick={ addGroup }>
							{ __( '+ Add subsection', 'cropx' ) }
						</Button>
					</div>

				</div>
			</section>
		</>
	);
}

/*
 * One subsection: an optional subheading, its own visual card grid, and its
 * own resource search picker. Kept as a separate component (rather than
 * inlined in a .map in Edit) because it needs its own useState/useSelect
 * calls per group — React's rules of hooks require each to live in its own
 * component instance, not inside a loop in the parent.
 */
function ResourceGroupEditor( {
	group,
	groupNumber,
	isOnly,
	canMoveUp,
	canMoveDown,
	columns,
	cardColor,
	gridAlign,
	onChangeSubheading,
	onChangeSelectedIds,
	onChangeCollapsible,
	onChangeCollapsedByDefault,
	onRemove,
	onMoveUp,
	onMoveDown,
} ) {
	const selectedIds = group.selectedIds ?? [];
	const [ searchQuery, setSearchQuery ] = useState( '' );
	const hasSubheading = ( group.subheading ?? '' ).trim().length > 0;
	const isCollapsible  = !! group.collapsible;

	/* Fetch each selected resource post + its featured image — see the
	   top-level Edit's original single-grid version of this same pattern. */
	const selectedResources = useSelect(
		( select ) => {
			if ( ! selectedIds.length ) return [];
			return selectedIds.map( ( id ) => {
				const post = select( 'core' ).getEntityRecord( 'postType', 'cropx_resource', id );
				if ( ! post ) return { id, title: null, coverUrl: null };

				const media = post.featured_media
					? select( 'core' ).getEntityRecord( 'root', 'media', post.featured_media )
					: null;

				return {
					id,
					title:    post.title?.rendered ?? '(Untitled)',
					coverUrl: media?.media_details?.sizes?.medium?.source_url
					       ?? media?.source_url
					       ?? null,
				};
			} );
		},
		[ selectedIds.join( ',' ) ]
	);

	/* Search the REST API as the user types (min 2 chars). */
	const searchResults = useSelect(
		( select ) => {
			if ( searchQuery.length < 2 ) return null;
			return select( 'core' ).getEntityRecords( 'postType', 'cropx_resource', {
				search:   searchQuery,
				per_page: 20,
				status:   'publish',
				_fields:  [ 'id', 'title' ],
			} );
		},
		[ searchQuery ]
	);

	function addResource( id ) {
		if ( ! selectedIds.includes( id ) ) {
			onChangeSelectedIds( [ ...selectedIds, id ] );
		}
		setSearchQuery( '' );
	}

	function removeResource( id ) {
		onChangeSelectedIds( selectedIds.filter( ( i ) => i !== id ) );
	}

	function moveResource( index, dir ) {
		onChangeSelectedIds( moveItem( selectedIds, index, dir ) );
	}

	const filteredResults = Array.isArray( searchResults )
		? searchResults.filter( ( p ) => ! selectedIds.includes( p.id ) )
		: [];

	const isCentered = selectedIds.length > 0 && selectedIds.length < columns
		&& ( gridAlign ?? 'center' ) === 'center';
	const gridClass = `rsd-grid rsd-grid--cols-${ columns }${ isCentered ? ' rsd-grid--centered' : '' }`;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ ( group.subheading && group.subheading.replace( /<[^>]+>/g, '' ).trim() ) || `${ __( 'Subsection', 'cropx' ) } ${ groupNumber }` }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Collapsible', 'cropx' ) }
						help={ hasSubheading
							? __( 'Visitors can click the subheading to expand/collapse this subsection.', 'cropx' )
							: __( 'Add a subsection heading above to enable this.', 'cropx' ) }
						checked={ isCollapsible }
						disabled={ ! hasSubheading }
						onChange={ onChangeCollapsible }
					/>
					{ isCollapsible && hasSubheading && (
						<ToggleControl
							label={ __( 'Collapsed on page load', 'cropx' ) }
							help={ __( 'Visitors have to click to reveal this subsection’s resources.', 'cropx' ) }
							checked={ !! group.collapsedByDefault }
							onChange={ onChangeCollapsedByDefault }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div className="rsd-subsection rsd-subsection--editor">

			<div className="rsd-subheading-row">
				<RichText
					tagName="h3"
					className="rsd-subheading rsd-subheading-input"
					value={ group.subheading }
					onChange={ onChangeSubheading }
					allowedFormats={ [] }
					placeholder={ __( 'Subsection heading (optional)…', 'cropx' ) }
				/>
				{ ! isOnly && (
					<div className="rsd-subsection-controls" aria-label={ __( 'Subsection controls', 'cropx' ) }>
						<button
							type="button"
							className="rsd-ctrl-btn"
							disabled={ ! canMoveUp }
							onClick={ onMoveUp }
							aria-label={ __( 'Move subsection up', 'cropx' ) }
						>↑</button>
						<button
							type="button"
							className="rsd-ctrl-btn"
							disabled={ ! canMoveDown }
							onClick={ onMoveDown }
							aria-label={ __( 'Move subsection down', 'cropx' ) }
						>↓</button>
						<button
							type="button"
							className="rsd-ctrl-btn rsd-ctrl-btn--remove"
							onClick={ onRemove }
							aria-label={ __( 'Remove subsection', 'cropx' ) }
						>✕</button>
					</div>
				) }
			</div>

			{ /* ── Visual card grid ── */ }
			{ selectedIds.length > 0 && (
				<div className={ gridClass }>
					{ selectedResources.map( ( resource, index ) => (
						<article key={ resource.id } className={ `rsd-card rsd-card--editor${ cardColor === 'blue' ? ' rsd-card--blue' : '' }` }>

							<div className="rsd-cover-wrap">
								{ resource.coverUrl ? (
									<img
										className="rsd-cover"
										src={ resource.coverUrl }
										alt=""
									/>
								) : resource.title === null ? (
									<div className="rsd-cover-placeholder">
										<Spinner />
									</div>
								) : (
									<div className="rsd-cover-placeholder">
										<DocIcon />
									</div>
								) }

								<div className="rsd-card-controls" aria-label={ __( 'Card controls', 'cropx' ) }>
									<button
										type="button"
										className="rsd-ctrl-btn"
										disabled={ index === 0 }
										onClick={ () => moveResource( index, 'up' ) }
										aria-label={ __( 'Move left', 'cropx' ) }
									>←</button>
									<button
										type="button"
										className="rsd-ctrl-btn rsd-ctrl-btn--remove"
										onClick={ () => removeResource( resource.id ) }
										aria-label={ __( 'Remove', 'cropx' ) }
									>✕</button>
									<button
										type="button"
										className="rsd-ctrl-btn"
										disabled={ index === selectedIds.length - 1 }
										onClick={ () => moveResource( index, 'down' ) }
										aria-label={ __( 'Move right', 'cropx' ) }
									>→</button>
								</div>
							</div>

							<div className="rsd-card-body">
								<p className="rsd-title">
									{ resource.title ?? <Spinner /> }
								</p>
							</div>

						</article>
					) ) }
				</div>
			) }

			{ /* ── Resource search picker ── */ }
			<div className="rsd-picker">
				<TextControl
					placeholder={ __( 'Search resources to add…', 'cropx' ) }
					value={ searchQuery }
					onChange={ ( v ) => setSearchQuery( v ) }
					hideLabelFromVision
					label={ __( 'Search resources', 'cropx' ) }
				/>

				{ searchQuery.length >= 2 && (
					<div className="rsd-search-results">
						{ searchResults === null && <Spinner /> }

						{ searchResults !== null && filteredResults.length === 0 && (
							<p className="rsd-no-results">
								{ selectedIds.some( ( id ) =>
									( searchResults ?? [] ).some( ( r ) => r.id === id )
								)
									? __( 'All matching resources are already selected.', 'cropx' )
									: __( 'No resources found.', 'cropx' ) }
							</p>
						) }

						{ filteredResults.map( ( post ) => (
							<div key={ post.id } className="rsd-result-item">
								<span className="rsd-result-title">
									{ post.title?.rendered ?? '(Untitled)' }
								</span>
								<Button
									variant="secondary"
									isSmall
									onClick={ () => addResource( post.id ) }
								>
									{ __( '+ Add', 'cropx' ) }
								</Button>
							</div>
						) ) }
					</div>
				) }

				{ selectedIds.length === 0 && searchQuery.length < 2 && (
					<p className="rsd-empty">
						{ __( 'Search for resources above to add them to this subsection.', 'cropx' ) }
					</p>
				) }
			</div>

		</div>
		</>
	);
}
