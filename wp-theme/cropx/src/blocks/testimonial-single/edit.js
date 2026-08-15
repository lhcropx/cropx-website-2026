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
	ToggleControl,
	SelectControl,
	RadioControl,
	ComboboxControl,
	Button,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		backgroundVariant,
		contentSource = 'manual',
		quote,
		authorName, authorTitle, authorCompany,
		showPhoto, photoId, photoUrl, photoAlt,
		testimonialId = 0,
		testimonialCategory = '',
	} = attributes;

	const blockProps = useBlockProps( {
		className: `ts-section ts-section--${ backgroundVariant }`,
		style: backgroundVariant === 'blue'
			? { '--ts-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	function selectPhoto( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}

	function clearPhoto() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}

	// Attribution meta line — mirrors the PHP join logic
	const metaLine = [ authorTitle, authorCompany ].filter( Boolean ).join( ', ' );

	// ── Placeholder-aware display values ──
	// In pick/auto modes the real quote/name/title are resolved server-side
	// from the referenced Quote post — the manual attributes above are
	// irrelevant there (and may hold stale leftovers from a previous manual
	// edit), so canvas display is forced blank and a faint explanatory
	// placeholder takes over instead of showing stale/misleading text.
	const isManual         = contentSource === 'manual';
	const displayQuote     = isManual ? quote : '';
	const displayAuthorName = isManual ? authorName : '';
	const displayMetaLine   = isManual ? metaLine : '';

	const quotePlaceholder = isManual
		? __( 'This is placeholder text. Type or paste your quote here to add it to the block.', 'cropx' )
		: __( 'This is placeholder text. The live quote is resolved on the front end and will appear on the published page.', 'cropx' );

	const namePlaceholder = isManual
		? __( "Person's Name (add via the sidebar)", 'cropx' )
		: __( "Person's Name", 'cropx' );
	const titlePlaceholder = isManual
		? __( 'Title & Business Name (add via the sidebar)', 'cropx' )
		: __( 'Title & Business Name', 'cropx' );

	const nameIsPlaceholder  = ! displayAuthorName;
	const titleIsPlaceholder = ! displayMetaLine;
	const placeholderStyle   = { opacity: 0.5, fontStyle: 'italic' };

	// ── Fetch published Quotes for the "pick" post picker (returns [] until CPT is registered) ──
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

	// ── Fetch Quotes categories for the "auto" dropdown ──
	const categoryTerms = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'cropx_testimonial_category', {
			per_page: 100,
			_fields:  'id,name,slug',
		} ) ?? [];
	}, [] );

	const categoryOptions = [
		{ label: __( 'All categories', 'cropx' ), value: '' },
		...( categoryTerms ?? [] ).map( ( t ) => ( { label: t.name, value: t.slug } ) ),
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background variant', 'cropx' ) }
						value={ backgroundVariant }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'White',               'cropx' ), value: 'white' },
							{ label: __( 'Deep Blue + Topo',    'cropx' ), value: 'blue'  },
						] }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Content source', 'cropx' ) } initialOpen={ true }>
					<RadioControl
						label={ __( 'How to populate this quote', 'cropx' ) }
						selected={ contentSource }
						options={ [
							{ label: __( 'Manual (enter quotes directly)', 'cropx' ), value: 'manual' },
							{ label: __( 'Pick from existing quotes',      'cropx' ), value: 'pick'   },
							{ label: __( 'Auto (by category)',             'cropx' ), value: 'auto'   },
						] }
						onChange={ ( v ) => setAttributes( { contentSource: v } ) }
					/>
					{ contentSource !== 'manual' && (
						<p style={ { fontSize: '11px', color: '#757575', marginTop: '-8px' } }>
							{ __( 'The fields below are a placeholder preview only — the live quote is resolved on the front end.', 'cropx' ) }
						</p>
					) }
				</PanelBody>

				{ contentSource === 'pick' && (
					<PanelBody title={ __( 'Quote to display', 'cropx' ) } initialOpen={ true }>
						{ testimonialPickerOptions.length === 0 && (
							<p style={ { fontSize: '12px', color: '#757575', fontStyle: 'italic' } }>
								{ __( 'No quotes found. The Quotes post type will be available once it is set up.', 'cropx' ) }
							</p>
						) }
						<ComboboxControl
							label={ __( 'Quote', 'cropx' ) }
							value={ testimonialId ? String( testimonialId ) : '' }
							options={ testimonialPickerOptions }
							onChange={ ( val ) => setAttributes( { testimonialId: val ? Number( val ) : 0 } ) }
						/>
					</PanelBody>
				) }

				{ contentSource === 'auto' && (
					<PanelBody title={ __( 'Query settings', 'cropx' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'Category', 'cropx' ) }
							help={ __( 'Shows the newest Quote in this category. Leave on "All categories" to show the newest Quote overall.', 'cropx' ) }
							value={ testimonialCategory }
							options={ categoryOptions }
							onChange={ ( v ) => setAttributes( { testimonialCategory: v } ) }
						/>
					</PanelBody>
				) }

				{ contentSource === 'manual' && (
					<PanelBody title={ __( 'Attribution', 'cropx' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'Show photo / logo', 'cropx' ) }
							checked={ showPhoto }
							onChange={ ( v ) => setAttributes( { showPhoto: v } ) }
							style={ { marginBottom: '12px' } }
						/>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ selectPhoto }
								allowedTypes={ [ 'image' ] }
								value={ photoId }
								render={ ( { open } ) => (
									<Button
										onClick={ open }
										variant="secondary"
										style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
									>
										{ photoUrl
											? __( 'Replace photo / logo', 'cropx' )
											: __( 'Select photo / logo', 'cropx' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
						{ photoUrl && (
							<Button
								onClick={ clearPhoto }
								variant="link"
								isDestructive
								style={ { marginBottom: '12px', display: 'block' } }
							>
								{ __( 'Remove photo / logo', 'cropx' ) }
							</Button>
						) }
						<TextControl
							label={ __( 'Name', 'cropx' ) }
							value={ authorName }
							onChange={ ( v ) => setAttributes( { authorName: v } ) }
						/>
						<TextControl
							label={ __( 'Title', 'cropx' ) }
							value={ authorTitle }
							onChange={ ( v ) => setAttributes( { authorTitle: v } ) }
						/>
						<TextControl
							label={ __( 'Company', 'cropx' ) }
							value={ authorCompany }
							onChange={ ( v ) => setAttributes( { authorCompany: v } ) }
						/>
					</PanelBody>
				) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="ts-inner">

					<blockquote>
						<div className="ts-quote-row">
							<span className="ts-quote ts-quote-mark ts-quote-open" aria-hidden="true" />
							<RichText
								tagName="p"
								className="ts-quote"
								placeholder={ quotePlaceholder }
								value={ displayQuote }
								onChange={ ( v ) => setAttributes( { quote: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
								readOnly={ ! isManual }
							/>
							<span className="ts-quote ts-quote-mark ts-quote-close" aria-hidden="true" />
						</div>
					</blockquote>

					{ /* Mirrors render.php: center the attribution only when a photo will
					     actually render, not just when the toggle is on. In manual mode
					     that means "toggle on AND a photo/logo has been uploaded" — the
					     editor still shows an empty "Logo" placeholder box when the toggle
					     is on but nothing's uploaded, so that state should NOT count as
					     "has a photo" for centering purposes either. */ }
					<div className={ `ts-author${ ! ( showPhoto && photoUrl ) ? ' ts-author--no-photo' : '' }` }>
						{ showPhoto && (
							<div className="ts-icon" aria-hidden="true">
								{ photoUrl ? (
									<img src={ photoUrl } alt={ photoAlt } />
								) : (
									<span style={ { fontSize: '0.625rem', fontWeight: 700, color: 'var(--gray-500)' } }>
										{ __( 'Logo', 'cropx' ) }
									</span>
								) }
							</div>
						) }
						<div>
							<p className="ts-name" style={ nameIsPlaceholder ? placeholderStyle : undefined }>
								{ nameIsPlaceholder ? namePlaceholder : displayAuthorName }
							</p>
							<p className="ts-title" style={ titleIsPlaceholder ? placeholderStyle : undefined }>
								{ titleIsPlaceholder ? titlePlaceholder : displayMetaLine }
							</p>
						</div>
					</div>

				</div>
			</section>
		</>
	);
}
