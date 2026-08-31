/**
 * Segments block — editor experience.
 *
 * Canvas shows all three photo panels simultaneously, matching the
 * render.php structure as closely as possible. Editors can:
 *   • Edit the section eyebrow + heading inline (RichText).
 *   • Pick a background photo per segment (sidebar MediaUpload).
 *   • Edit eyebrow, heading, body copy, and URL per segment (sidebar).
 *   • Drag or arrow-button the panels to reorder them.
 *   • Toggle the section header and per-panel eyebrow visibility.
 */

import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	RichText,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const SVG_ARROW = (
	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

// Attribute key mapping per segment.
const ATTR_KEYS = {
	enterprise: { eyebrow: 'enterpriseEyebrow', heading: 'enterpriseHeading', body: 'enterpriseBody', url: 'enterpriseUrl', photoUrl: 'enterprisePhotoUrl', photoId: 'enterprisePhotoId' },
	service:    { eyebrow: 'serviceEyebrow',    heading: 'serviceHeading',    body: 'serviceBody',    url: 'serviceUrl',    photoUrl: 'servicePhotoUrl',    photoId: 'servicePhotoId'    },
	onFarm:     { eyebrow: 'onFarmEyebrow',     heading: 'onFarmHeading',     body: 'onFarmBody',     url: 'onFarmUrl',     photoUrl: 'onFarmPhotoUrl',     photoId: 'onFarmPhotoId'     },
};

const SEGMENT_META = {
	enterprise: { label: __( 'Enterprise',        'cropx' ), cssType: 'enterprise' },
	service:    { label: __( 'Service Providers', 'cropx' ), cssType: 'service'    },
	onFarm:     { label: __( 'On-Farm',           'cropx' ), cssType: 'on-farm'    },
};

function buildSegmentDef( key, attributes, setAttributes ) {
	const m = ATTR_KEYS[ key ];
	const meta = SEGMENT_META[ key ];
	return {
		key,
		label:      meta.label,
		cssType:    meta.cssType,
		eyebrow:    attributes[ m.eyebrow ],
		heading:    attributes[ m.heading ],
		body:       attributes[ m.body ],
		url:        attributes[ m.url ],
		photoUrl:   attributes[ m.photoUrl ],
		photoId:    attributes[ m.photoId ],
		setEyebrow: ( v ) => setAttributes( { [ m.eyebrow ]:  v } ),
		setHeading:  ( v ) => setAttributes( { [ m.heading ]:  v } ),
		setBody:     ( v ) => setAttributes( { [ m.body ]:     v } ),
		setUrl:      ( v ) => setAttributes( { [ m.url ]:      v } ),
		setPhoto:    ( media ) => setAttributes( { [ m.photoUrl ]: media.url, [ m.photoId ]: media.id } ),
		clearPhoto:  () => setAttributes( { [ m.photoUrl ]: '', [ m.photoId ]: 0 } ),
	};
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		showEyebrow,
		showSectionHeader,
		showSectionEyebrow,
		sectionEyebrow,
		sectionHeading,
		segmentOrder,
	} = attributes;

	// PageSpeed fix (Aug 2026): real, cacheable drift-pattern URL instead of a
	// base64-inlined one — see render.php for the front-end half.
	const blockProps = useBlockProps( {
		className: 'seg-section',
		style: { '--seg-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` },
	} );

	// Build ordered segment definitions.
	const orderedSegs = segmentOrder.map( ( key ) =>
		buildSegmentDef( key, attributes, setAttributes )
	);

	// Drag-and-drop reorder state.
	const [ dragIdx, setDragIdx ]         = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropSegment( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { segmentOrder: reorderByDrag( segmentOrder, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	return (
		<>
			{/* ── Sidebar ── */}
			<InspectorControls>

				{/* Section header settings */}
				<PanelBody title={ __( 'Section Header', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show section header', 'cropx' ) }
						checked={ showSectionHeader !== false }
						onChange={ ( v ) => setAttributes( { showSectionHeader: v } ) }
					/>
					{ showSectionHeader !== false && (
						<ToggleControl
							label={ __( 'Show section eyebrow', 'cropx' ) }
							checked={ showSectionEyebrow !== false }
							onChange={ ( v ) => setAttributes( { showSectionEyebrow: v } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Show segment eyebrow tags', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
				</PanelBody>

				{/* Per-segment panels — draggable to reorder */}
				{ orderedSegs.map( ( seg, idx ) => (
					<div
						key={ seg.key }
						onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
						onDragLeave={ () => setDragOverIdx( null ) }
						onDrop={ () => dropSegment( idx ) }
						onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
						style={ {
							borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx
								? '2px solid var(--wp-admin-theme-color, #007cba)'
								: '2px solid transparent',
							opacity:   dragIdx === idx ? 0.4 : 1,
							transition: 'opacity 0.1s',
						} }
					>
						{/* Drag handle + arrow buttons row */}
						<div style={ { display: 'flex', alignItems: 'center', gap: '2px', background: '#f0f0f0', padding: '3px 6px', marginBottom: '-1px' } }>
							<span
								draggable
								onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
								style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
								title={ __( 'Drag to reorder', 'cropx' ) }
							>⠿</span>
							<span style={ { flex: 1, fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#666' } }>
								{ seg.label }
							</span>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { segmentOrder: moveItem( segmentOrder, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { segmentOrder: moveItem( segmentOrder, idx, 'down' ) } ) } disabled={ idx === segmentOrder.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
						</div>

						<PanelBody title={ seg.label } initialOpen={ idx === 0 }>

							{/* Photo picker */}
							<p style={ { fontSize: '11px', color: '#757575', marginTop: 0, marginBottom: '8px' } }>
								{ __( 'Photo', 'cropx' ) }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ seg.setPhoto }
									allowedTypes={ [ 'image' ] }
									value={ seg.photoId }
									render={ ( { open } ) => (
										<div style={ { display: 'flex', flexDirection: 'column', gap: '6px', marginBottom: '12px' } }>
											{ seg.photoUrl && (
												<img
													src={ seg.photoUrl }
													alt=""
													style={ { width: '100%', height: '80px', objectFit: 'cover', borderRadius: '2px', display: 'block' } }
												/>
											) }
											<Button onClick={ open } variant="secondary" style={ { width: '100%', justifyContent: 'center' } }>
												{ seg.photoId > 0
													? __( 'Replace photo', 'cropx' )
													: __( 'Select photo', 'cropx' ) }
											</Button>
											{ seg.photoId > 0 && (
												<Button onClick={ seg.clearPhoto } variant="link" isDestructive>
													{ __( 'Remove photo', 'cropx' ) }
												</Button>
											) }
										</div>
									) }
								/>
							</MediaUploadCheck>

							{/* Text fields */}
							<TextControl
								label={ __( 'Eyebrow tag', 'cropx' ) }
								value={ seg.eyebrow }
								onChange={ seg.setEyebrow }
							/>
							<TextControl
								label={ __( 'Heading', 'cropx' ) }
								value={ seg.heading }
								onChange={ seg.setHeading }
							/>
							<TextControl
								label={ __( 'Link URL', 'cropx' ) }
								value={ seg.url }
								onChange={ seg.setUrl }
								type="url"
							/>
						</PanelBody>
					</div>
				) ) }
			</InspectorControls>

			{/* ── Canvas preview ── */}
			<section { ...blockProps }>
				<div className="seg-inner">

					{/* Section header — inline-editable */}
					{ showSectionHeader !== false && (
						<div className="seg-section-header">
							{ showSectionEyebrow !== false && (
								<RichText
									tagName="p"
									className="seg-section-eyebrow"
									value={ sectionEyebrow }
									onChange={ ( v ) => setAttributes( { sectionEyebrow: v } ) }
									allowedFormats={ [] }
									placeholder={ __( 'Section eyebrow…', 'cropx' ) }
								/>
							) }
							<RichText
								tagName="h2"
								className="seg-section-heading"
								value={ sectionHeading }
								onChange={ ( v ) => setAttributes( { sectionHeading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
								placeholder={ __( 'Section heading…', 'cropx' ) }
							/>
						</div>
					) }

					{/* Photo panels grid */}
					<div className="seg-grid">
						{ orderedSegs.map( ( seg ) => {
							const photoStyle = seg.photoUrl
								? { backgroundImage: `url(${ seg.photoUrl })` }
								: {};
							return (
								<div key={ seg.key } className={ `seg-panel seg-panel--${ seg.cssType } active` }>

									{/* Photo layer */}
									<div className="seg-panel-photo" style={ photoStyle } />

									{/* Gradient overlay */}
									<div className="seg-panel-overlay" />

									{/* Segment tag — top left */}
									{ showEyebrow !== false && seg.eyebrow && (
										<div className="seg-panel-tag-wrap">
											<span className="seg-panel-tag">{ seg.eyebrow }</span>
										</div>
									) }

									{/* Body — name + description (inline editable) + CTA */}
									<div className="seg-panel-body">
										<h3 className="seg-panel-name">{ seg.heading || __( 'Segment heading', 'cropx' ) }</h3>
										<RichText
											tagName="p"
											className="seg-panel-desc"
											value={ seg.body }
											onChange={ seg.setBody }
											allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
											placeholder={ __( 'Segment description…', 'cropx' ) }
										/>
										<span className="seg-panel-cta" aria-hidden="true">
											{ __( 'Learn more', 'cropx' ) }
											{ SVG_ARROW }
										</span>
									</div>

									{/* Accent strip */}
									<div className="seg-panel-strip" />
								</div>
							);
						} ) }
					</div>
				</div>
			</section>
		</>
	);
}
