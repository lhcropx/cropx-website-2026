import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
	Button,
} from '@wordpress/components';

import './editor.css';

const CHECK_ICON = (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" className="pt-check-icon">
		<path d="M3 8.5L6 11.5L13 4.5" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

const EYEBROW_CSS = {
	'cropx-blue': 'var(--cropx-blue)',
	'deep-blue':  'var(--deep-blue)',
	'white':      '#fff',
};

const BG_CLASS_MAP = { white: 'pt-section--white', taupe: 'pt-section--taupe', 'deep-blue': 'pt-section--deep-blue' };

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		heading,
		showEyebrow,
		showHeading,
		eyebrowColor,
		bgColor,
		tiers,
	} = attributes;

	const isDark = bgColor === 'deep-blue';

	const eyebrowColorOptions = isDark
		? [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'White', value: 'white' },
		]
		: [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'Deep Blue', value: 'deep-blue' },
		];

	const sectionClass = [ 'pt-section', BG_CLASS_MAP[ bgColor ] ?? 'pt-section--white' ].join( ' ' );

	const blockProps = useBlockProps( {
		className: sectionClass,
		style: isDark
			? { '--pt-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	// ── Tier helpers ──────────────────────────────────────────────────────────

	function updateTier( index, changes ) {
		const next = tiers.map( ( t, i ) => ( i === index ? { ...t, ...changes } : t ) );
		setAttributes( { tiers: next } );
	}

	function updateFeature( tierIndex, featureIndex, value ) {
		const tier = tiers[ tierIndex ];
		const nextFeatures = tier.features.map( ( f, i ) => ( i === featureIndex ? value : f ) );
		updateTier( tierIndex, { features: nextFeatures } );
	}

	function addFeature( tierIndex ) {
		const tier = tiers[ tierIndex ];
		updateTier( tierIndex, { features: [ ...tier.features, '' ] } );
	}

	function removeFeature( tierIndex, featureIndex ) {
		const tier = tiers[ tierIndex ];
		updateTier( tierIndex, { features: tier.features.filter( ( _, i ) => i !== featureIndex ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: 'White (default)', value: 'white' },
							{ label: 'Warm White', value: 'taupe' },
							{ label: 'Deep Blue + Topo', value: 'deep-blue' },
						] }
						onChange={ ( val ) => {
							const newIsDark = val === 'deep-blue';
							const resetColor =
								( newIsDark && eyebrowColor === 'deep-blue' ) ||
								( ! newIsDark && eyebrowColor === 'white' )
									? 'cropx-blue'
									: eyebrowColor;
							setAttributes( { bgColor: val, eyebrowColor: resetColor } );
						} }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow }
						onChange={ ( val ) => setAttributes( { showEyebrow: val } ) }
					/>
					{ showEyebrow && (
						<SelectControl
							label={ __( 'Eyebrow color', 'cropx' ) }
							value={ eyebrowColor }
							options={ eyebrowColorOptions }
							onChange={ ( val ) => setAttributes( { eyebrowColor: val } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Show heading', 'cropx' ) }
						checked={ showHeading }
						onChange={ ( val ) => setAttributes( { showHeading: val } ) }
					/>
				</PanelBody>

				{ tiers.map( ( tier, index ) => (
					<PanelBody
						key={ index }
						title={ tier.name ? `${ __( 'Tier', 'cropx' ) } ${ index + 1 }: ${ tier.name }` : `${ __( 'Tier', 'cropx' ) } ${ index + 1 }` }
						initialOpen={ false }
					>
						<ToggleControl
							label={ __( 'Featured (elevated card)', 'cropx' ) }
							help={ __( 'Raises this card above the other two with a stronger shadow — mark whichever plan you want visitors drawn to first.', 'cropx' ) }
							checked={ !! tier.featured }
							onChange={ ( val ) => updateTier( index, { featured: val } ) }
						/>
						{ tier.featured && (
							<TextControl
								label={ __( 'Badge label', 'cropx' ) }
								value={ tier.badgeLabel }
								onChange={ ( val ) => updateTier( index, { badgeLabel: val } ) }
							/>
						) }
						<TextControl
							label={ __( 'Button label', 'cropx' ) }
							value={ tier.ctaLabel }
							onChange={ ( val ) => updateTier( index, { ctaLabel: val } ) }
						/>
						<TextControl
							label={ __( 'Button link', 'cropx' ) }
							value={ tier.ctaUrl }
							onChange={ ( val ) => updateTier( index, { ctaUrl: val } ) }
						/>
					</PanelBody>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="pt-inner section-inner">

					{ ( showEyebrow || showHeading ) && (
						<div className="section-header">
							{ showEyebrow && (
								<RichText
									tagName="span"
									className="section-eyebrow"
									value={ eyebrow }
									onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									allowedFormats={ [] }
									style={ { color: EYEBROW_CSS[ eyebrowColor ] ?? 'var(--cropx-blue)' } }
								/>
							) }
							{ showHeading && (
								<RichText
									tagName="h2"
									className={ 'section-heading' + ( isDark ? ' section-heading--white' : '' ) }
									value={ heading }
									onChange={ ( val ) => setAttributes( { heading: val } ) }
									placeholder={ __( 'Heading…', 'cropx' ) }
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
								/>
							) }
						</div>
					) }

					<div className="pt-grid">
						{ tiers.map( ( tier, index ) => (
							<div className={ 'pt-card' + ( tier.featured ? ' pt-card--featured' : '' ) } key={ index }>

								{ tier.featured && tier.badgeLabel && (
									<span className="pt-badge">{ tier.badgeLabel }</span>
								) }

								<RichText
									tagName="h3"
									className="pt-name"
									value={ tier.name }
									onChange={ ( val ) => updateTier( index, { name: val } ) }
									placeholder={ __( 'Tier name…', 'cropx' ) }
									allowedFormats={ [] }
								/>

								<RichText
									tagName="p"
									className="pt-price"
									value={ tier.price }
									onChange={ ( val ) => updateTier( index, { price: val } ) }
									placeholder={ __( 'Price…', 'cropx' ) }
									allowedFormats={ [] }
								/>

								<hr className="pt-divider" />

								<ul className="pt-features">
									{ tier.features.map( ( feature, fi ) => (
										<li className="pt-feature" key={ fi }>
											{ CHECK_ICON }
											<RichText
												tagName="span"
												className="pt-feature-text"
												value={ feature }
												onChange={ ( val ) => updateFeature( index, fi, val ) }
												placeholder={ __( 'Feature…', 'cropx' ) }
												allowedFormats={ [] }
											/>
											<button
												type="button"
												className="pt-feature-remove"
												onClick={ () => removeFeature( index, fi ) }
												aria-label={ __( 'Remove feature', 'cropx' ) }
											>
												×
											</button>
										</li>
									) ) }
								</ul>

								<Button
									variant="secondary"
									className="pt-add-feature"
									onClick={ () => addFeature( index ) }
								>
									{ __( '+ Add feature', 'cropx' ) }
								</Button>

								<span className="pt-cta">
									{ tier.ctaLabel || __( 'Contact', 'cropx' ) }
								</span>

							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
