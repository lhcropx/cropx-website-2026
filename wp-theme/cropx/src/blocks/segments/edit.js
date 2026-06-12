import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, Button } from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const SVG_ARROW = (
	<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

// Lookup map: segment key → all its attribute helpers + display label + CSS type class.
function buildSegmentDef( key, label, cssType, attributes, setAttributes ) {
	const keyMap = {
		enterprise: { eyebrow: 'enterpriseEyebrow', heading: 'enterpriseHeading', url: 'enterpriseUrl', body: 'enterpriseBody' },
		service:    { eyebrow: 'serviceEyebrow',    heading: 'serviceHeading',    url: 'serviceUrl',    body: 'serviceBody'    },
		onFarm:     { eyebrow: 'onFarmEyebrow',     heading: 'onFarmHeading',     url: 'onFarmUrl',     body: 'onFarmBody'     },
	};
	const m = keyMap[ key ];
	return {
		key,
		label,
		cssType,
		eyebrow:    attributes[ m.eyebrow ],
		heading:    attributes[ m.heading ],
		url:        attributes[ m.url ],
		body:       attributes[ m.body ],
		setEyebrow: ( v ) => setAttributes( { [ m.eyebrow ]: v } ),
		setHeading:  ( v ) => setAttributes( { [ m.heading ]:  v } ),
		setUrl:      ( v ) => setAttributes( { [ m.url ]:      v } ),
		setBody:     ( v ) => setAttributes( { [ m.body ]:     v } ),
	};
}

const SEGMENT_META = {
	enterprise: { label: __( 'Enterprise',        'cropx' ), cssType: 'enterprise' },
	service:    { label: __( 'Service Providers', 'cropx' ), cssType: 'service'    },
	onFarm:     { label: __( 'On-Farm',           'cropx' ), cssType: 'on-farm'    },
};

export default function Edit( { attributes, setAttributes } ) {
	const { showEyebrow, segmentOrder } = attributes;

	const blockProps = useBlockProps( { className: 'seg-section' } );

	// Build ordered segment definitions
	const orderedSegs = segmentOrder.map( ( key ) => {
		const meta = SEGMENT_META[ key ];
		return buildSegmentDef( key, meta.label, meta.cssType, attributes, setAttributes );
	} );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
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
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
				</PanelBody>

				{ orderedSegs.map( ( seg, idx ) => (
					<div
						key={ seg.key }
						onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
						onDragLeave={ () => setDragOverIdx( null ) }
						onDrop={ () => dropSegment( idx ) }
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
								{ seg.label }
							</span>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { segmentOrder: moveItem( segmentOrder, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { segmentOrder: moveItem( segmentOrder, idx, 'down' ) } ) } disabled={ idx === segmentOrder.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
						</div>
						<PanelBody title={ seg.label } initialOpen={ idx === 0 }>
							<TextControl
								label={ __( 'Eyebrow', 'cropx' ) }
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
							/>
						</PanelBody>
					</div>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="seg-inner">
					{ /* Tab bar omitted in editor — all 3 columns are always visible */ }
					<div className="seg-grid">
						{ orderedSegs.map( ( seg ) => (
							<div key={ seg.key } className={ `seg-col seg-col--${ seg.cssType } active` }>
								{ showEyebrow !== false && seg.eyebrow && (
									<p className="seg-eyebrow">{ seg.eyebrow }</p>
								) }
								<div className="seg-name-wrap">
									<h2 className="seg-name">
										{ /* Link replaced with span in editor so clicking doesn't navigate */ }
										<span>
											<span className="seg-underline">
												{ seg.heading || __( 'Segment heading', 'cropx' ) }
												<span className="seg-arrow" aria-hidden="true">
													{ SVG_ARROW }
												</span>
											</span>
										</span>
									</h2>
								</div>
								<RichText
									tagName="p"
									className="seg-body"
									value={ seg.body }
									onChange={ seg.setBody }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
									placeholder={ __( 'Segment description…', 'cropx' ) }
								/>
							</div>
						) ) }
					</div>
				</div>
			</section>
		</>
	);
}
