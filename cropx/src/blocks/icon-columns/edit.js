import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

const COLUMN_COUNT_OPTIONS = [
	{ label: __( '4 columns', 'cropx' ), value: '4' },
	{ label: __( '5 columns', 'cropx' ), value: '5' },
	{ label: __( '6 columns', 'cropx' ), value: '6' },
];

const BG_OPTIONS = [
	{ label: __( 'White',              'cropx' ), value: 'white' },
	{ label: __( 'Taupe 50',          'cropx' ), value: 'taupe' },
	{ label: __( 'Deep Blue',         'cropx' ), value: 'blue'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue box)',         'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold box)',             'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra box)',      'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf box)',            'cropx' ), value: 'on-farm'          },
];

// ─────────────────────────────────────────────────────────────────────────────
// Edit
// ─────────────────────────────────────────────────────────────────────────────
export default function Edit( { attributes, setAttributes } ) {
	const {
		columnCount,
		eyebrow, heading,
		backgroundVariant, segmentAccent,
		columns,
		eyebrowColor, showEyebrow, showHeading, showIcons,
	} = attributes;

	const isBlue = backgroundVariant === 'blue';

	const blockProps = useBlockProps( {
		className:
			`ici-section ici-section--${ backgroundVariant }` +
			( ! isBlue && segmentAccent !== 'general'
				? ` ici-segment-${ segmentAccent }`
				: '' ),
	} );

	const [ dragIdx, setDragIdx ]     = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropItem( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { columns: reorderByDrag( columns, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	function updateItem( idx, field, val ) {
		setAttributes( {
			columns: columns.map( ( col, i ) =>
				i === idx ? { ...col, [ field ]: val } : col
			),
		} );
	}

	function addItem() {
		if ( columns.length >= 36 ) return;
		setAttributes( {
			columns: [ ...columns, { icon: 'fields', heading: '', body: '', ctaLabel: '', ctaUrl: '#' } ],
		} );
	}

	function removeItem( idx ) {
		if ( columns.length <= 1 ) return;
		setAttributes( { columns: columns.filter( ( _, i ) => i !== idx ) } );
	}

	const ARROW = (
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	);

	return (
		<>
			<InspectorControls>

				{/* ── Section Settings ── */}
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Columns', 'cropx' ) }
						help={ __( 'Items flow left-to-right, wrapping to a new row once a row fills — like reading order.', 'cropx' ) }
						value={ columnCount }
						options={ COLUMN_COUNT_OPTIONS }
						onChange={ ( v ) => setAttributes( { columnCount: v } ) }
					/>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundVariant }
						options={ BG_OPTIONS }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
					{ ! isBlue && (
						<SelectControl
							label={ __( 'Icon box accent', 'cropx' ) }
							help={ __( 'Tints icon boxes on light sections. Deep Blue sections always use white boxes.', 'cropx' ) }
							value={ segmentAccent }
							options={ SEGMENT_OPTIONS }
							onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow !== false && (
						<SelectControl
							label={ __( 'Eyebrow color', 'cropx' ) }
							value={ eyebrowColor ?? 'cropx-blue' }
							options={ [
								{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
								{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
								{ label: __( 'White',                'cropx' ), value: 'white'      },
							] }
							onChange={ ( v ) => setAttributes( { eyebrowColor: v } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Show heading', 'cropx' ) }
						checked={ showHeading !== false }
						onChange={ ( v ) => setAttributes( { showHeading: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show icons', 'cropx' ) }
						checked={ showIcons !== false }
						onChange={ ( v ) => setAttributes( { showIcons: v } ) }
					/>
				</PanelBody>

				{/* ── Per-item panels — labeled by item number, in flow order ── */}
				{ columns.map( ( col, idx ) => {
					const itemNum = idx + 1;
					const panelLabel = col.heading
						? col.heading.replace( /<[^>]+>/g, '' ).substring( 0, 24 ) || `Item ${ itemNum }`
						: `Item ${ itemNum }`;

					return (
						<div
							key={ idx }
							onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
							onDragLeave={ () => setDragOverIdx( null ) }
							onDrop={ () => dropItem( idx ) }
							onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
							style={ {
								borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx
									? '2px solid var(--wp-admin-theme-color, #007cba)'
									: '2px solid transparent',
								opacity: dragIdx === idx ? 0.4 : 1,
								transition: 'opacity 0.1s',
							} }
						>
							{/* ── Drag handle + item number + move buttons ── */}
							<div style={ { display: 'flex', alignItems: 'center', gap: '2px', background: '#f0f0f0', padding: '3px 6px', marginBottom: '-1px' } }>
								<span
									draggable
									onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
									style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
									title={ __( 'Drag to reorder', 'cropx' ) }
								>⠿</span>
								{/* Item number badge */}
								<span style={ { fontSize: '10px', fontWeight: 700, color: '#fff', background: '#888', borderRadius: '2px', padding: '1px 5px', marginRight: '4px', flexShrink: 0, letterSpacing: '0.03em' } }>
									{ itemNum }
								</span>
								<span style={ { flex: 1, fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#666', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } }>
									{ panelLabel }
								</span>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
								<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'down' ) } ) } disabled={ idx === columns.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
							</div>

							<PanelBody
								title={ `${ __( 'Item', 'cropx' ) } ${ itemNum }` }
								initialOpen={ false }
							>
								{ showIcons !== false && (
									<div>
										<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px', marginTop: '0' } }>
											{ __( 'Icon', 'cropx' ) }
										</p>
										<IconPicker
											value={ col.icon }
											onChange={ ( v ) => updateItem( idx, 'icon', v ) }
										/>
									</div>
								) }
								<TextControl
									label={ __( 'CTA label', 'cropx' ) }
									value={ col.ctaLabel }
									onChange={ ( v ) => updateItem( idx, 'ctaLabel', v ) }
								/>
								<TextControl
									label={ __( 'CTA URL', 'cropx' ) }
									value={ col.ctaUrl }
									onChange={ ( v ) => updateItem( idx, 'ctaUrl', v ) }
								/>
								<Button
									variant="link"
									isDestructive
									disabled={ columns.length <= 1 }
									onClick={ () => removeItem( idx ) }
									style={ { marginTop: '4px' } }
								>
									{ __( 'Remove item', 'cropx' ) }
								</Button>
							</PanelBody>
						</div>
					);
				} ) }

				<div style={ { padding: '8px 16px 16px' } }>
					<Button
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
						disabled={ columns.length >= 36 }
						onClick={ addItem }
					>
						{ columns.length >= 36
							? __( 'Maximum 36 items reached', 'cropx' )
							: __( '+ Add item', 'cropx' ) }
					</Button>
				</div>

			</InspectorControls>

			{/* ── Canvas — real CSS Grid, matches render.php exactly ── */}
			<section { ...blockProps }>
				<div className="ici-inner">

					<div className="ici-header">
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="section-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
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
					</div>

					{/*
					  Flat grid canvas: items render in the order they're entered inside
					  a CSS Grid (.ici-cols-N sets grid-template-columns in style.css),
					  so the browser's own auto-flow wraps them into rows — no manual
					  chunking to keep in sync with render.php.
					*/}
					<div className={ `ici-columns ici-cols-${ columnCount }` }>
						{ columns.map( ( item, idx ) => (
							<div key={ idx } className="ici-item">
								{ showIcons !== false && (
									<div className="ici-icon" aria-hidden="true">
										<img src={ iconSrc( item.icon ) } alt="" width="24" height="24" />
									</div>
								) }
								<RichText
									tagName="h3"
									className="ici-item-heading"
									placeholder={ __( 'Item heading…', 'cropx' ) }
									value={ item.heading }
									onChange={ ( v ) => updateItem( idx, 'heading', v ) }
									allowedFormats={ [ 'core/bold' ] }
								/>
								<RichText
									tagName="p"
									className="ici-body"
									placeholder={ __( 'Body text…', 'cropx' ) }
									value={ item.body }
									onChange={ ( v ) => updateItem( idx, 'body', v ) }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								/>
								{ item.ctaLabel && (
									<span className="ici-cta ici-cta-preview" aria-hidden="true">
										{ item.ctaLabel }
										{ ARROW }
									</span>
								) }
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
