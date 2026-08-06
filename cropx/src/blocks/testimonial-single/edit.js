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
	Button,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		backgroundVariant,
		quote,
		authorName, authorTitle, authorCompany,
		showPhoto, photoId, photoUrl, photoAlt,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `ts-section ts-section--${ backgroundVariant }`,
	} );

	function selectPhoto( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}

	function clearPhoto() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}

	// Attribution meta line — mirrors the PHP join logic
	const metaLine = [ authorTitle, authorCompany ].filter( Boolean ).join( ', ' );

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
							{ label: __( 'Deep Blue',           'cropx' ), value: 'blue'  },
						] }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
				</PanelBody>

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
			</InspectorControls>

			<section { ...blockProps }>
				<div className="ts-inner">

					<blockquote>
						<RichText
							tagName="p"
							className="ts-quote"
							placeholder={ __( 'Type the testimonial quote here — no need to add quotation marks, the design adds them automatically…', 'cropx' ) }
							value={ quote }
							onChange={ ( v ) => setAttributes( { quote: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
					</blockquote>

					{ ( authorName || photoUrl ) && (
						<div className={ `ts-author${ ! showPhoto ? ' ts-author--no-photo' : '' }` }>
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
								{ authorName && (
									<p className="ts-name">{ authorName }</p>
								) }
								{ metaLine && (
									<p className="ts-title">{ metaLine }</p>
								) }
							</div>
						</div>
					) }

				</div>
			</section>
		</>
	);
}
