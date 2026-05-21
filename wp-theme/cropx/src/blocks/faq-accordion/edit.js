import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Button,
	SelectControl,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { showHeader, eyebrow, heading, items, eyebrowColor } = attributes;

	const blockProps = useBlockProps( { className: 'faq-section' } );

	// ── Item helpers — always spread to avoid shared references ──

	function updateItem( idx, field, value ) {
		setAttributes( {
			items: items.map( ( item, i ) => i === idx ? { ...item, [ field ]: value } : item ),
		} );
	}

	function addItem() {
		setAttributes( {
			items: [ ...items, { question: '', answer: '' } ],
		} );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show section header', 'cropx' ) }
						checked={ showHeader }
						onChange={ ( v ) => setAttributes( { showHeader: v } ) }
					/>
					{ showHeader && (
						<TextControl
							label={ __( 'Eyebrow', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>
					{ items.map( ( _, idx ) => (
						<div
							key={ idx }
							style={ {
								marginBottom: '12px',
								paddingBottom: '12px',
								borderBottom: idx < items.length - 1 ? '1px solid #e0e0e0' : 'none',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'space-between',
							} }
						>
							<span style={ { fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
								{ __( 'Item', 'cropx' ) } { idx + 1 }
							</span>
							<Button
								onClick={ () => removeItem( idx ) }
								variant="link"
								isDestructive
								disabled={ items.length <= 1 }
							>
								{ __( 'Remove', 'cropx' ) }
							</Button>
						</div>
					) ) }
					<Button
						onClick={ addItem }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add item', 'cropx' ) }
					</Button>
				</PanelBody>

				<PanelBody title={ __( 'Eyebrow', 'cropx' ) } initialOpen={ false }>
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
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="faq-section-inner">

					{ showHeader && (
						<div className="faq-content">
							{ eyebrow && (
								<span className="faq-eyebrow" style={{ color: `var(--${ eyebrowColor ?? 'cropx-blue' })` }}>{ eyebrow }</span>
							) }
							<RichText
								tagName="h2"
								className="faq-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						</div>
					) }

					<div className="faq-list">
						{ items.map( ( item, idx ) => (
							<div key={ idx } className="faq-item">
								{ /* Editor renders toggle as a div — button + accordion behavior is front-end only */ }
								<div className="faq-toggle faq-toggle--editor">
									<RichText
										tagName="span"
										className="faq-question"
										placeholder={ __( 'Question…', 'cropx' ) }
										value={ item.question }
										onChange={ ( v ) => updateItem( idx, 'question', v ) }
										allowedFormats={ [ 'core/bold', 'core/italic' ] }
									/>
									<span className="faq-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none" strokeWidth="2" strokeLinecap="round">
											<line x1="5" y1="12" x2="19" y2="12"/>
											<line x1="12" y1="5" x2="12" y2="19"/>
										</svg>
									</span>
								</div>
								{ /* Always expanded in the editor so answer is editable in context */ }
								<div className="faq-answer-wrap faq-answer-wrap--editor">
									<div className="faq-answer">
										<div className="faq-answer-inner">
											<RichText
												tagName="p"
												placeholder={ __( 'Answer…', 'cropx' ) }
												value={ item.answer }
												onChange={ ( v ) => updateItem( idx, 'answer', v ) }
												allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
											/>
										</div>
									</div>
								</div>
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
