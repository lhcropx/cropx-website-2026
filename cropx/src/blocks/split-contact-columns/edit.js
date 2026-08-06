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
	TextareaControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { moveItem, reorderByDrag } from '../../shared/reorder';
import './editor.css';

const BG_OPTIONS = [
	{ label: __( 'Deep Blue (default)', 'cropx' ), value: 'blue'  },
	{ label: __( 'Taupe 50',            'cropx' ), value: 'taupe' },
	{ label: __( 'White',               'cropx' ), value: 'white' },
];

// ─────────────────────────────────────────────────────────────────────────────
// Edit
// ─────────────────────────────────────────────────────────────────────────────
export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading, body,
		backgroundVariant,
		eyebrowColor, showEyebrow, showBody,
		columns,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `scc-section scc-section--${ backgroundVariant }`,
	} );

	const [ dragIdx, setDragIdx ]         = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropColumn( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { columns: reorderByDrag( columns, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	function updateColumn( idx, field, val ) {
		setAttributes( {
			columns: columns.map( ( col, i ) =>
				i === idx ? { ...col, [ field ]: val } : col
			),
		} );
	}

	function addColumn() {
		setAttributes( {
			columns: [ ...columns, { heading: '', phone: '', address: '' } ],
		} );
	}

	function removeColumn( idx ) {
		if ( columns.length <= 1 ) return;
		setAttributes( { columns: columns.filter( ( _, i ) => i !== idx ) } );
	}

	const eyebrowCss = `var(--${ eyebrowColor ?? 'cropx-blue' })`;

	return (
		<>
			<InspectorControls>

				{/* ── Section Settings ── */}
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundVariant }
						options={ BG_OPTIONS }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
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
						label={ __( 'Show body paragraph', 'cropx' ) }
						checked={ showBody !== false }
						onChange={ ( v ) => setAttributes( { showBody: v } ) }
					/>
				</PanelBody>

				{/* ── Per-column panels ── */}
				{ columns.map( ( col, idx ) => (
					<div
						key={ idx }
						onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
						onDragLeave={ () => setDragOverIdx( null ) }
						onDrop={ () => dropColumn( idx ) }
						onDragEnd={ () => { setDragIdx( null ); setDragOverIdx( null ); } }
						style={ {
							borderTop: dragOverIdx === idx && dragOverIdx !== dragIdx
								? '2px solid var(--wp-admin-theme-color, #007cba)'
								: '2px solid transparent',
							opacity: dragIdx === idx ? 0.4 : 1,
							transition: 'opacity 0.1s',
						} }
					>
						{/* Drag handle + move buttons bar */}
						<div style={ { display: 'flex', alignItems: 'center', gap: '2px', background: '#f0f0f0', padding: '3px 6px', marginBottom: '-1px' } }>
							<span
								draggable
								onDragStart={ ( e ) => { setDragIdx( idx ); e.dataTransfer.effectAllowed = 'move'; } }
								style={ { cursor: 'grab', color: '#aaa', fontSize: '14px', userSelect: 'none', padding: '0 4px 0 0', lineHeight: 1, flexShrink: 0 } }
								title={ __( 'Drag to reorder', 'cropx' ) }
							>⠿</span>
							<span style={ { flex: 1, fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#666' } }>
								{ col.heading
									? col.heading.replace( /<[^>]+>/g, '' ).substring( 0, 28 ) || `${ __( 'Column', 'cropx' ) } ${ idx + 1 }`
									: `${ __( 'Column', 'cropx' ) } ${ idx + 1 }` }
							</span>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'down' ) } ) } disabled={ idx === columns.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
						</div>

						<PanelBody title={ `${ __( 'Column', 'cropx' ) } ${ idx + 1 }` } initialOpen={ false }>
							<TextControl
								label={ __( 'Phone', 'cropx' ) }
								value={ col.phone }
								placeholder="+1 (888) 832 2767"
								onChange={ ( v ) => updateColumn( idx, 'phone', v ) }
							/>
							<TextareaControl
								label={ __( 'Address', 'cropx' ) }
								value={ col.address }
								placeholder={ '201 E Center St\nAnaheim, CA 92805\nUSA' }
								rows={ 4 }
								onChange={ ( v ) => updateColumn( idx, 'address', v ) }
								help={ __( 'Each line becomes a line break in the output.', 'cropx' ) }
							/>
							<Button
								variant="link"
								isDestructive
								disabled={ columns.length <= 1 }
								onClick={ () => removeColumn( idx ) }
								style={ { marginTop: '4px' } }
							>
								{ __( 'Remove item', 'cropx' ) }
							</Button>
						</PanelBody>
					</div>
				) ) }

				{/* ── Add item ── */}
				<div style={ { padding: '8px 16px 16px' } }>
					<Button
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
						onClick={ addColumn }
					>
						{ __( '+ Add item', 'cropx' ) }
					</Button>
				</div>

			</InspectorControls>

			{/* ── Canvas ── */}
			<section { ...blockProps }>
				<div className="scc-inner">

					{/* Left: section header */}
					<div className="scc-header">
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="section-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								style={ { color: eyebrowCss } }
							/>
						) }
						<RichText
							tagName="h2"
							className="section-heading"
							placeholder={ __( 'Section heading…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
						{ showBody !== false && (
							<RichText
								tagName="p"
								className="section-body"
								placeholder={ __( 'Supporting copy…', 'cropx' ) }
								value={ body }
								onChange={ ( v ) => setAttributes( { body: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
						) }
					</div>

					{/* Right: contact columns */}
					<div className="scc-grid">
						{ columns.map( ( col, idx ) => (
							<div key={ idx } className="scc-item">
								<RichText
									tagName="h3"
									className="scc-item-heading"
									placeholder={ __( 'Location name…', 'cropx' ) }
									value={ col.heading }
									onChange={ ( v ) => updateColumn( idx, 'heading', v ) }
									allowedFormats={ [ 'core/bold' ] }
								/>
								{ col.phone && (
									<div className="scc-contact-row">
										<h4 className="scc-label">{ __( 'Phone:', 'cropx' ) }</h4>
										<p className="scc-phone">{ col.phone }</p>
									</div>
								) }
								{ col.address && (
									<div className="scc-contact-row">
										<h4 className="scc-label">{ __( 'Address:', 'cropx' ) }</h4>
										<p className="scc-address" style={ { whiteSpace: 'pre-line' } }>{ col.address }</p>
									</div>
								) }
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
