import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, testimonials } = attributes;

	const blockProps = useBlockProps( { className: 'testimonials-section' } );

	// ── Array helpers — always spread to avoid shared references ──

	function updateTestimonial( idx, field, value ) {
		setAttributes( {
			testimonials: testimonials.map( ( t, i ) =>
				i === idx ? { ...t, [ field ]: value } : t
			),
		} );
	}

	function selectPhoto( idx, media ) {
		setAttributes( {
			testimonials: testimonials.map( ( t, i ) =>
				i === idx
					? { ...t, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' }
					: t
			),
		} );
	}

	function clearPhoto( idx ) {
		setAttributes( {
			testimonials: testimonials.map( ( t, i ) =>
				i === idx ? { ...t, photoId: 0, photoUrl: '', photoAlt: '' } : t
			),
		} );
	}

	function addTestimonial() {
		setAttributes( {
			testimonials: [ ...testimonials, {
				quote: '', authorName: '', authorTitle: '',
				photoId: 0, photoUrl: '', photoAlt: '',
			} ],
		} );
	}

	function removeTestimonial( idx ) {
		if ( testimonials.length <= 1 ) return;
		setAttributes( { testimonials: testimonials.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section header', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Eyebrow', 'cropx' ) }
						value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
					/>
					<TextControl
						label={ __( 'Heading', 'cropx' ) }
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
					/>
					<p style={ { fontSize: '11px', color: '#757575', marginTop: '-8px' } }>
						{ __( 'Clear both fields to hide the section header entirely.', 'cropx' ) }
					</p>
				</PanelBody>

				<PanelBody title={ __( 'Testimonials', 'cropx' ) } initialOpen={ true }>
					{ testimonials.map( ( t, idx ) => (
						<div
							key={ idx }
							style={ {
								marginBottom: '16px',
								paddingBottom: '16px',
								borderBottom: idx < testimonials.length - 1 ? '1px solid #e0e0e0' : 'none',
							} }
						>
							<p style={ { fontWeight: 600, marginBottom: '8px', fontSize: '12px', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
								{ __( 'Testimonial', 'cropx' ) } { idx + 1 }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ t.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ t.photoUrl
												? __( 'Replace photo / logo', 'cropx' )
												: __( 'Select photo / logo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ t.photoUrl && (
								<Button
									onClick={ () => clearPhoto( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove photo', 'cropx' ) }
								</Button>
							) }
							<TextControl
								label={ __( 'Author name', 'cropx' ) }
								value={ t.authorName }
								onChange={ ( v ) => updateTestimonial( idx, 'authorName', v ) }
							/>
							<TextControl
								label={ __( 'Title & company', 'cropx' ) }
								value={ t.authorTitle }
								onChange={ ( v ) => updateTestimonial( idx, 'authorTitle', v ) }
							/>
							<Button
								onClick={ () => removeTestimonial( idx ) }
								variant="link"
								isDestructive
								disabled={ testimonials.length <= 1 }
							>
								{ __( 'Remove testimonial', 'cropx' ) }
							</Button>
						</div>
					) ) }
					<Button
						onClick={ addTestimonial }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add testimonial', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>

				{ ( eyebrow.trim() || heading.trim() ) && (
					<div className="testimonials-inner">
						<div className="testimonials-header">
							{ eyebrow.trim() && (
								<span className="testimonials-eyebrow">{ eyebrow }</span>
							) }
							<RichText
								tagName="h2"
								className="testimonials-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						</div>
					</div>
				) }

				<div className="tcarousel">
					<div className="tcarousel-viewport">
						<div className="tcarousel-track">
							{ testimonials.map( ( t, idx ) => {
								const name = ( t.authorName || '' ).trim();
								let initials = '?';
								if ( name ) {
									const words = name.split( ' ' ).filter( Boolean );
									initials = words.slice( 0, 2 ).map( ( w ) => w[ 0 ].toUpperCase() ).join( '' );
								}
								return (
									<article key={ idx } className="testimonial-card" role="group" aria-roledescription="slide">
										<div className="testimonial-quote">
											<RichText
												tagName="p"
												placeholder={ __( 'Quote text… (CSS adds curly quotes — do not type them)', 'cropx' ) }
												value={ t.quote }
												onChange={ ( v ) => updateTestimonial( idx, 'quote', v ) }
												allowedFormats={ [ 'core/bold', 'core/italic' ] }
											/>
										</div>
										<div className="testimonial-author">
											<div className="author-icon" aria-hidden="true">
												{ t.photoUrl ? (
													<img src={ t.photoUrl } alt={ t.photoAlt } />
												) : (
													<span className="author-initials">{ initials }</span>
												) }
											</div>
											<div>
												<p className="author-name">
													{ t.authorName || <em style={ { opacity: 0.4 } }>{ __( 'Author name', 'cropx' ) }</em> }
												</p>
												<p className="author-title">
													{ t.authorTitle || <em style={ { opacity: 0.4 } }>{ __( 'Title, Company', 'cropx' ) }</em> }
												</p>
											</div>
										</div>
									</article>
								);
							} ) }
						</div>
					</div>

					<div className="tcarousel-controls tc-controls-preview">
						<button className="tcarousel-arrow tcarousel-prev" disabled aria-label={ __( 'Previous testimonial', 'cropx' ) }>
							<svg viewBox="0 0 18 18" fill="none" aria-hidden="true">
								<path d="M11 4l-5 5 5 5" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/>
							</svg>
						</button>
						<div className="tcarousel-dots"></div>
						<button className="tcarousel-arrow tcarousel-next" aria-label={ __( 'Next testimonial', 'cropx' ) }>
							<svg viewBox="0 0 18 18" fill="none" aria-hidden="true">
								<path d="M7 4l5 5-5 5" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/>
							</svg>
						</button>
					</div>
				</div>

			</section>
		</>
	);
}
