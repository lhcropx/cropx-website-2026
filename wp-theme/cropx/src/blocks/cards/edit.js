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
	SelectControl,
	Button,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { cardVariant, showHeader, eyebrow, heading, cards } = attributes;

	const isDark   = cardVariant === 'dark';
	const tagClass = isDark ? 'crd-tag crd-tag--white' : 'crd-tag crd-tag--dark';

	const blockProps = useBlockProps( { className: 'crd-section' } );

	// ── Card helpers — always spread to avoid shared references ──

	function updateCard( idx, field, value ) {
		setAttributes( {
			cards: cards.map( ( c, i ) => i === idx ? { ...c, [ field ]: value } : c ),
		} );
	}

	function selectCardPhoto( idx, media ) {
		setAttributes( {
			cards: cards.map( ( c, i ) =>
				i === idx
					? { ...c, photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' }
					: c
			),
		} );
	}

	function clearCardPhoto( idx ) {
		setAttributes( {
			cards: cards.map( ( c, i ) =>
				i === idx ? { ...c, photoId: 0, photoUrl: '', photoAlt: '' } : c
			),
		} );
	}

	function addCard() {
		setAttributes( {
			cards: [ ...cards, {
				photoId: 0, photoUrl: '', photoAlt: '',
				tag: '', tagUrl: '#', date: '',
				title: '', excerpt: '',
				ctaLabel: 'Read more', ctaUrl: '#',
			} ],
		} );
	}

	function removeCard( idx ) {
		if ( cards.length <= 1 ) return;
		setAttributes( { cards: cards.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Card variant', 'cropx' ) }
						value={ cardVariant }
						options={ [
							{ label: __( 'White cards', 'cropx' ),     value: 'white' },
							{ label: __( 'Deep Blue cards', 'cropx' ), value: 'dark'  },
						] }
						onChange={ ( v ) => setAttributes( { cardVariant: v } ) }
					/>
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

				<PanelBody title={ __( 'Cards', 'cropx' ) } initialOpen={ true }>
					{ cards.map( ( card, idx ) => (
						<div
							key={ idx }
							style={ {
								marginBottom: '16px',
								paddingBottom: '16px',
								borderBottom: idx < cards.length - 1 ? '1px solid #e0e0e0' : 'none',
							} }
						>
							<p style={ { fontWeight: 600, marginBottom: '8px', fontSize: '12px', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
								{ __( 'Card', 'cropx' ) } { idx + 1 }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectCardPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ card.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ card.photoUrl
												? __( 'Replace photo', 'cropx' )
												: __( 'Select photo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ card.photoUrl && (
								<Button
									onClick={ () => clearCardPhoto( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove photo', 'cropx' ) }
								</Button>
							) }
							<TextControl
								label={ __( 'Tag label', 'cropx' ) }
								value={ card.tag }
								onChange={ ( v ) => updateCard( idx, 'tag', v ) }
							/>
							<TextControl
								label={ __( 'Tag URL', 'cropx' ) }
								value={ card.tagUrl }
								onChange={ ( v ) => updateCard( idx, 'tagUrl', v ) }
							/>
							<TextControl
								label={ __( 'Date', 'cropx' ) }
								value={ card.date }
								onChange={ ( v ) => updateCard( idx, 'date', v ) }
							/>
							<TextControl
								label={ __( 'CTA label', 'cropx' ) }
								value={ card.ctaLabel }
								onChange={ ( v ) => updateCard( idx, 'ctaLabel', v ) }
							/>
							<TextControl
								label={ __( 'Card URL', 'cropx' ) }
								value={ card.ctaUrl }
								onChange={ ( v ) => updateCard( idx, 'ctaUrl', v ) }
							/>
							<Button
								onClick={ () => removeCard( idx ) }
								variant="link"
								isDestructive
								disabled={ cards.length <= 1 }
							>
								{ __( 'Remove card', 'cropx' ) }
							</Button>
						</div>
					) ) }
					<Button
						onClick={ addCard }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add card', 'cropx' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="crd-inner">

					{ showHeader && (
						<div className="crd-header">
							{ eyebrow && (
								<span className="crd-eyebrow">{ eyebrow }</span>
							) }
							<RichText
								tagName="h2"
								className="crd-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						</div>
					) }

					<div className="crd-grid">
						{ cards.map( ( card, idx ) => (
							<article
								key={ idx }
								className={ `crd-card${ isDark ? ' crd-card--dark' : '' }` }
							>
								<div className="crd-card-img-wrap">
									{ card.photoUrl ? (
										<img
											className="crd-card-img"
											src={ card.photoUrl }
											alt={ card.photoAlt }
										/>
									) : (
										<MediaPlaceholder
											onSelect={ ( media ) => selectCardPhoto( idx, media ) }
											allowedTypes={ [ 'image' ] }
											accept="image/*"
											labels={ { title: __( 'Card photo', 'cropx' ) } }
										/>
									) }
								</div>

								<div className="crd-card-body">
									{ ( card.tag || card.date ) && (
										<div className="crd-meta">
											{ card.tag && (
												<span className={ tagClass }>{ card.tag }</span>
											) }
											{ card.date && (
												<span className={ isDark ? 'crd-date crd-date--dark' : 'crd-date' }>
													{ card.date }
												</span>
											) }
										</div>
									) }
									<RichText
										tagName="h3"
										className="crd-title"
										placeholder={ __( 'Card title…', 'cropx' ) }
										value={ card.title }
										onChange={ ( v ) => updateCard( idx, 'title', v ) }
										allowedFormats={ [ 'core/bold', 'core/italic' ] }
									/>
									<RichText
										tagName="p"
										className="crd-excerpt"
										placeholder={ __( 'Card excerpt…', 'cropx' ) }
										value={ card.excerpt }
										onChange={ ( v ) => updateCard( idx, 'excerpt', v ) }
										allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
									/>
									{ card.ctaLabel && (
										<span className="crd-cta" aria-hidden="true">
											{ card.ctaLabel }
											<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
												<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
											</svg>
										</span>
									) }
								</div>
							</article>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
