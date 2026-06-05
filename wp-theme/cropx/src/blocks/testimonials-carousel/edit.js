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
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
	RadioControl,
	RangeControl,
	ComboboxControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading, testimonials, eyebrowColor, showEyebrow,
		contentSource = 'manual',
		testimonialIds = [],
		testimonialCategory = '',
		testimonialLimit = 5,
	} = attributes;

	const blockProps = useBlockProps( { className: 'testimonials-section' } );

	// ── Fetch published testimonials for the post picker (returns [] until CPT is registered) ──
	const allTestimonials = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'postType', 'cropx_testimonial', {
			per_page: 100,
			status:   'publish',
			_fields:  'id,title',
		} ) ?? [];
	}, [] );

	const testimonialPickerOptions = ( allTestimonials ?? [] ).map( ( p ) => ( {
		value: String( p.id ),
		label: p.title?.rendered ?? `Testimonial #${ p.id }`,
	} ) );

	// ── Manual array helpers — always spread to avoid shared references ──
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

	// ── Pick-mode helpers ──
	function addPickSlot() {
		setAttributes( { testimonialIds: [ ...testimonialIds, 0 ] } );
	}

	function updatePickSlot( idx, val ) {
		const updated = testimonialIds.map( ( id, i ) => i === idx ? Number( val ) : id );
		setAttributes( { testimonialIds: updated } );
	}

	function removePickSlot( idx ) {
		setAttributes( { testimonialIds: testimonialIds.filter( ( _, i ) => i !== idx ) } );
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

				<PanelBody title={ __( 'Content source', 'cropx' ) } initialOpen={ true }>
					<RadioControl
						label={ __( 'How to populate testimonials', 'cropx' ) }
						selected={ contentSource }
						options={ [
							{ label: __( 'Manual (enter quotes directly)', 'cropx' ), value: 'manual' },
							{ label: __( 'Pick testimonials',               'cropx' ), value: 'pick'   },
							{ label: __( 'Auto (by category)',              'cropx' ), value: 'auto'   },
						] }
						onChange={ ( v ) => setAttributes( { contentSource: v } ) }
					/>
				</PanelBody>

				{ contentSource === 'manual' && (
					<PanelBody title={ __( 'Testimonials', 'cropx' ) } initialOpen={ true }>
						{ testimonials.map( ( t, idx ) => (
							<PanelBody
								key={ idx }
								title={ `Testimonial ${ idx + 1 }` }
								initialOpen={ idx === 0 }
							>
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
									label={ __( 'Name', 'cropx' ) }
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
							</PanelBody>
						) ) }
						<Button
							onClick={ addTestimonial }
							variant="secondary"
							style={ { width: '100%', justifyContent: 'center' } }
						>
							{ __( '+ Add testimonial', 'cropx' ) }
						</Button>
					</PanelBody>
				) }

				{ contentSource === 'pick' && (
					<PanelBody title={ __( 'Testimonials', 'cropx' ) } initialOpen={ true }>
						{ testimonialPickerOptions.length === 0 && (
							<p style={ { fontSize: '12px', color: '#757575', fontStyle: 'italic' } }>
								{ __( 'No testimonials found. The Testimonials post type will be available once it is set up.', 'cropx' ) }
							</p>
						) }
						{ testimonialIds.map( ( id, idx ) => (
							<div key={ idx } style={ { marginBottom: '12px' } }>
								<ComboboxControl
									label={ `${ __( 'Testimonial', 'cropx' ) } ${ idx + 1 }` }
									value={ id ? String( id ) : '' }
									options={ testimonialPickerOptions }
									onChange={ ( val ) => updatePickSlot( idx, val ?? 0 ) }
								/>
								<Button
									onClick={ () => removePickSlot( idx ) }
									variant="link"
									isDestructive
									style={ { marginTop: '2px' } }
								>
									{ __( 'Remove', 'cropx' ) }
								</Button>
							</div>
						) ) }
						<Button
							onClick={ addPickSlot }
							variant="secondary"
							style={ { width: '100%', justifyContent: 'center' } }
						>
							{ __( '+ Add testimonial', 'cropx' ) }
						</Button>
					</PanelBody>
				) }

				{ contentSource === 'auto' && (
					<PanelBody title={ __( 'Query settings', 'cropx' ) } initialOpen={ true }>
						<TextControl
							label={ __( 'Category slug', 'cropx' ) }
							help={ __( 'Filter by a testimonial category slug. Leave blank to show all.', 'cropx' ) }
							value={ testimonialCategory }
							onChange={ ( v ) => setAttributes( { testimonialCategory: v } ) }
						/>
						<RangeControl
							label={ __( 'Number of testimonials', 'cropx' ) }
							value={ testimonialLimit }
							onChange={ ( v ) => setAttributes( { testimonialLimit: v } ) }
							min={ 1 }
							max={ 12 }
						/>
					</PanelBody>
				) }

			</InspectorControls>

			<section { ...blockProps }>

				{ ( eyebrow.trim() || heading.trim() ) && (
					<div className="testimonials-inner">
						<div className="testimonials-header">
							{ showEyebrow !== false && eyebrow.trim() && (
								<span className="testimonials-eyebrow" style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }>{ eyebrow }</span>
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
													{ t.authorName || <em style={ { opacity: 0.4 } }>{ __( 'Name', 'cropx' ) }</em> }
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
