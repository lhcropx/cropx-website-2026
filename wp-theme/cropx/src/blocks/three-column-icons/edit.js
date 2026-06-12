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
import './editor.css';

const ICON_OPTIONS = [
	{ label: 'alarm-clock',      value: 'alarm-clock' },
	{ label: 'antenna',          value: 'antenna' },
	{ label: 'corn',             value: 'corn' },
	{ label: 'field-sun',        value: 'field-sun' },
	{ label: 'fields',           value: 'fields' },
	{ label: 'language',         value: 'language' },
	{ label: 'nutrition',        value: 'nutrition' },
	{ label: 'sensor-cloud',     value: 'sensor-cloud' },
	{ label: 'speed',            value: 'speed' },
	{ label: 'valve-irrigation', value: 'valve-irrigation' },
];

const BG_OPTIONS = [
	{ label: __( 'White',     'cropx' ), value: 'white' },
	{ label: __( 'Deep Blue', 'cropx' ), value: 'blue'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (Deep Blue box)',         'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold box)',            'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra box)',     'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf box)',           'cropx' ), value: 'on-farm'          },
];

const themeUri = window.cropxThemeData?.themeUri ?? '';

function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + slug + '.svg';
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading,
		backgroundVariant, segmentAccent,
		columns,
		eyebrowColor, showEyebrow, showHeading, showIcons,
	} = attributes;

	const isBlue = backgroundVariant === 'blue';

	const blockProps = useBlockProps( {
		className:
			`tci-section tci-section--${ backgroundVariant }` +
			( ! isBlue && segmentAccent !== 'general'
				? ` tci-segment-${ segmentAccent }`
				: '' ),
	} );

	// ── Drag-and-drop reorder state ──
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ dragOverIdx, setDragOverIdx ] = useState( null );

	function dropColumn( toIdx ) {
		if ( dragIdx !== null && dragIdx !== toIdx ) {
			setAttributes( { columns: reorderByDrag( columns, dragIdx, toIdx ) } );
		}
		setDragIdx( null );
		setDragOverIdx( null );
	}

	// ── Column helpers ──
	function updateColumn( idx, field, value ) {
		setAttributes( {
			columns: columns.map( ( col, i ) =>
				i === idx ? { ...col, [ field ]: value } : col
			),
		} );
	}

	const ARROW = (
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
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
					<ToggleControl
						label={ __( 'Show icons', 'cropx' ) }
						checked={ showIcons !== false }
						onChange={ ( v ) => setAttributes( { showIcons: v } ) }
					/>
					{ showEyebrow !== false && (
						<TextControl
							label={ __( 'Eyebrow text', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						/>
					) }
					{ showEyebrow !== false && (
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
					) }
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundVariant }
						options={ BG_OPTIONS }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
					{ ! isBlue && (
						<SelectControl
							label={ __( 'Icon box accent', 'cropx' ) }
							help={ __( 'Tints the icon box on white sections. Deep Blue sections always use a white box.', 'cropx' ) }
							value={ segmentAccent }
							options={ SEGMENT_OPTIONS }
							onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
						/>
					) }
				</PanelBody>

				{ columns.map( ( col, idx ) => (
					<div
						key={ idx }
						onDragOver={ ( e ) => { e.preventDefault(); setDragOverIdx( idx ); } }
						onDragLeave={ () => setDragOverIdx( null ) }
						onDrop={ () => dropColumn( idx ) }
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
								{ col.heading
									? col.heading.replace( /<[^>]+>/g, '' ).substring( 0, 28 ) || `${ __( 'Column', 'cropx' ) } ${ idx + 1 }`
									: `${ __( 'Column', 'cropx' ) } ${ idx + 1 }` }
							</span>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'up' ) } ) } disabled={ idx === 0 } label={ __( 'Move up', 'cropx' ) }>↑</Button>
							<Button variant="tertiary" isSmall onClick={ () => setAttributes( { columns: moveItem( columns, idx, 'down' ) } ) } disabled={ idx === columns.length - 1 } label={ __( 'Move down', 'cropx' ) }>↓</Button>
						</div>
						<PanelBody title={ `${ __( 'Column', 'cropx' ) } ${ idx + 1 }` } initialOpen={ false }>
							<SelectControl
								label={ __( 'Icon', 'cropx' ) }
								value={ col.icon }
								options={ ICON_OPTIONS }
								onChange={ ( v ) => updateColumn( idx, 'icon', v ) }
							/>
							<TextControl
								label={ __( 'CTA label', 'cropx' ) }
								value={ col.ctaLabel }
								onChange={ ( v ) => updateColumn( idx, 'ctaLabel', v ) }
							/>
							<TextControl
								label={ __( 'CTA URL', 'cropx' ) }
								value={ col.ctaUrl }
								onChange={ ( v ) => updateColumn( idx, 'ctaUrl', v ) }
							/>
						</PanelBody>
					</div>
				) ) }

			</InspectorControls>

			<section { ...blockProps }>
				<div className="tci-inner">
					{ /* Header is always visible in the editor so both fields stay focusable.
					     render.php hides it when both eyebrow and heading are empty. */ }
					<div className="tci-header">
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="tci-eyebrow"
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
								className="tci-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						) }
					</div>

					<div className="tci-grid">
						{ columns.map( ( col, idx ) => (
							<div key={ idx } className="tci-item">
								{ showIcons !== false && (
									<div className="tci-icon" aria-hidden="true">
										<img src={ iconSrc( col.icon ) } alt="" width="24" height="24" />
									</div>
								) }
								<RichText
									tagName="h3"
									className="tci-item-heading"
									placeholder={ __( 'Column heading…', 'cropx' ) }
									value={ col.heading }
									onChange={ ( v ) => updateColumn( idx, 'heading', v ) }
									allowedFormats={ [ 'core/bold' ] }
								/>
								<RichText
									tagName="p"
									className="tci-body"
									placeholder={ __( 'Body text…', 'cropx' ) }
									value={ col.body }
									onChange={ ( v ) => updateColumn( idx, 'body', v ) }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								/>
								{ col.ctaLabel && (
									<span className="tci-cta tci-cta-preview" aria-hidden="true">
										{ col.ctaLabel }
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
