import { useState }        from '@wordpress/element';
import { useSelect }        from '@wordpress/data';
import { __ }               from '@wordpress/i18n';
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
		selectedIds = [],
		eyebrow,
		heading,
		showEyebrow,
		showHeading,
		eyebrowColor,
		bgColor,
		cardColor,
		columns,
	} = attributes;

	const [ searchQuery, setSearchQuery ] = useState( '' );

	/*
	 * Fetch each selected resource post + its featured image in a single
	 * useSelect call. The selector re-runs automatically whenever the WP data
	 * store resolves pending entity requests, so cover images appear as soon
	 * as WordPress finishes loading them — no manual loading state needed.
	 */
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
					// Prefer the medium size (300 px) for the narrow 4-col cards;
					// fall back to full source URL if medium isn't generated yet.
					coverUrl: media?.media_details?.sizes?.medium?.source_url
					       ?? media?.source_url
					       ?? null,
				};
			} );
		},
		// Stable dependency: re-run when the list of IDs changes.
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

	/* ── Helpers ── */
	function addResource( id ) {
		if ( ! selectedIds.includes( id ) ) {
			setAttributes( { selectedIds: [ ...selectedIds, id ] } );
		}
		setSearchQuery( '' );
	}

	function removeResource( id ) {
		setAttributes( { selectedIds: selectedIds.filter( ( i ) => i !== id ) } );
	}

	function moveResource( index, dir ) {
		const next = [ ...selectedIds ];
		const to   = index + dir;
		if ( to < 0 || to >= next.length ) return;
		[ next[ index ], next[ to ] ] = [ next[ to ], next[ index ] ];
		setAttributes( { selectedIds: next } );
	}

	/* Remove already-selected items from search results. */
	const filteredResults = Array.isArray( searchResults )
		? searchResults.filter( ( p ) => ! selectedIds.includes( p.id ) )
		: [];

	const isCentered = selectedIds.length > 0 && selectedIds.length < columns;
	const gridClass  = `rsd-grid rsd-grid--cols-${ columns }${ isCentered ? ' rsd-grid--centered' : '' }`;

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
							{ label: __( '4 columns (default)', 'cropx' ), value: '4' },
							{ label: __( '3 columns',           'cropx' ), value: '3' },
							{ label: __( '2 columns',           'cropx' ), value: '2' },
						] }
						onChange={ ( v ) => setAttributes( { columns: parseInt( v, 10 ) } ) }
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
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="rsd-inner">

					{ /* ── Section header ── */ }
					<div className="rsd-header">
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

					{ /* ── Visual card grid ── */ }
					{ selectedIds.length > 0 && (
						<div className={ gridClass }>
							{ selectedResources.map( ( resource, index ) => (
								<article key={ resource.id } className={ `rsd-card rsd-card--editor${ cardColor === 'blue' ? ' rsd-card--blue' : '' }` }>

									{ /* Cover area with reorder/remove controls as an overlay */ }
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
												onClick={ () => moveResource( index, -1 ) }
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
												onClick={ () => moveResource( index, 1 ) }
												aria-label={ __( 'Move right', 'cropx' ) }
											>→</button>
										</div>
									</div>

									{ /* Card body — title only; excerpt/button are decorative in editor */ }
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
								{ __( 'Search for resources above to add them to this section.', 'cropx' ) }
							</p>
						) }
					</div>

				</div>
			</section>
		</>
	);
}
