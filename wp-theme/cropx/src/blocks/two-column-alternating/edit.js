import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	MediaPlaceholder,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		showIntro, introHeading, introBody, introCtaLabel, introCtaUrl,
		rows,
	} = attributes;

	const blockProps = useBlockProps( { className: 'tca-section' } );

	// ── Row helpers — always spread to avoid shared references ──

	function updateRowField( idx, field, value ) {
		setAttributes( {
			rows: rows.map( ( r, i ) => i === idx ? { ...r, [ field ]: value } : r ),
		} );
	}

	function selectRowPhoto( idx, media ) {
		setAttributes( {
			rows: rows.map( ( r, i ) =>
				i === idx
					? { ...r, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' }
					: r
			),
		} );
	}

	function clearRowPhoto( idx ) {
		setAttributes( {
			rows: rows.map( ( r, i ) =>
				i === idx ? { ...r, photoId: 0, photoUrl: '', photoAlt: '' } : r
			),
		} );
	}

	function addRow() {
		setAttributes( {
			rows: [ ...rows, { heading: '', body: '', photoId: 0, photoUrl: '', photoAlt: '' } ],
		} );
	}

	function removeRow( idx ) {
		if ( rows.length <= 1 ) return;
		setAttributes( { rows: rows.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Intro', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show section intro', 'cropx' ) }
						checked={ showIntro }
						onChange={ ( v ) => setAttributes( { showIntro: v } ) }
					/>
					{ showIntro && (
						<>
							<TextControl
								label={ __( 'CTA label', 'cropx' ) }
								value={ introCtaLabel }
								onChange={ ( v ) => setAttributes( { introCtaLabel: v } ) }
							/>
							<TextControl
								label={ __( 'CTA URL', 'cropx' ) }
								value={ introCtaUrl }
								onChange={ ( v ) => setAttributes( { introCtaUrl: v } ) }
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Rows', 'cropx' ) } initialOpen={ true }>
					{ rows.map( ( row, idx ) => (
						<div
							key={ idx }
							style={ {
								marginBottom: '16px',
								paddingBottom: '16px',
								borderBottom: idx < rows.length - 1 ? '1px solid #e0e0e0' : 'none',
							} }
						>
							<p style={ { fontWeight: 600, marginBottom: '8px', fontSize: '12px', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
								{ __( 'Row', 'cropx' ) } { idx + 1 }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectRowPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ row.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ row.photoUrl
												? __( 'Replace photo', 'cropx' )
												: __( 'Select photo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ row.photoUrl && (
								<Button
									onClick={ () => clearRowPhoto( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove photo', 'cropx' ) }
								</Button>
							) }
							<Button
								onClick={ () => removeRow( idx ) }
								variant="link"
								isDestructive
								disabled={ rows.length <= 1 }
							>
								{ __( 'Remove row', 'cropx' ) }
							</Button>
						</div>
					) ) }
					<Button
						onClick={ addRow }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add row', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="tca-inner">

					{ showIntro && (
						<div className="tca-intro">
							<RichText
								tagName="h2"
								className="tca-intro-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ introHeading }
								onChange={ ( v ) => setAttributes( { introHeading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<RichText
								tagName="p"
								className="tca-intro-body"
								placeholder={ __( 'Section description…', 'cropx' ) }
								value={ introBody }
								onChange={ ( v ) => setAttributes( { introBody: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ introCtaLabel && (
								<span className="tca-intro-cta tca-intro-cta-preview" aria-hidden="true">
									{ introCtaLabel }
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
										<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
									</svg>
								</span>
							) }
						</div>
					) }

					<div className="tca-rows">
						{ rows.map( ( row, idx ) => {
							const isPhotoLeft = idx % 2 === 1;
							return (
								<div
									key={ idx }
									className={ `tca-row${ isPhotoLeft ? ' tca-row--photo-left' : '' }` }
								>
									<div className="tca-content">
										<RichText
											tagName="h2"
											className="tca-heading"
											placeholder={ __( 'Row heading…', 'cropx' ) }
											value={ row.heading }
											onChange={ ( v ) => updateRowField( idx, 'heading', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic' ] }
										/>
										<RichText
											tagName="p"
											className="tca-body"
											placeholder={ __( 'Row body…', 'cropx' ) }
											value={ row.body }
											onChange={ ( v ) => updateRowField( idx, 'body', v ) }
											allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
										/>
									</div>

									<div className="tca-photo-col">
										{ row.photoUrl ? (
											<img
												className="tca-photo"
												src={ row.photoUrl }
												alt={ row.photoAlt }
											/>
										) : (
											<MediaPlaceholder
												onSelect={ ( media ) => selectRowPhoto( idx, media ) }
												allowedTypes={ [ 'image' ] }
												accept="image/*"
												labels={ { title: __( 'Row photo', 'cropx' ) } }
											/>
										) }
									</div>
								</div>
							);
						} ) }
					</div>

				</div>
			</section>
		</>
	);
}
