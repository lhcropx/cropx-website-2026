import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';

const ARROW_SVG = (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		backgroundStyle,
		showEyebrow,
		eyebrowColor,
		eyebrow,
		heading,
		showBody,
		body,
		primaryLabel,
		primaryUrl,
		showSecondary,
		secondaryStyle,
		secondaryLabel,
		secondaryUrl,
	} = attributes;

	const isDark = backgroundStyle === 'dark';

	// Eyebrow color options and CSS values vary by background
	const eyebrowColorOptions = isDark
		? [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'White', value: 'white' },
		]
		: [
			{ label: 'CropX Blue', value: 'cropx-blue' },
			{ label: 'Deep Blue', value: 'deep-blue' },
		];

	const EYEBROW_CSS = {
		'cropx-blue': 'var(--cropx-blue)',
		'deep-blue':  'var(--deep-blue)',
		'white':      '#fff',
	};

	const bgClassMap = { taupe: 'mcta--taupe', white: 'mcta--white', dark: 'mcta--dark' };
	const sectionClass = [
		'cropx-mid-cta',
		bgClassMap[ backgroundStyle ] ?? 'mcta--taupe',
	].join( ' ' );

	// Resolve button classes based on background
	const primaryClass = isDark ? 'mcta-btn mcta-btn--white' : 'mcta-btn mcta-btn--primary';
	const secondaryClass = secondaryStyle === 'ghost'
		? 'mcta-btn mcta-btn--ghost'
		: 'mcta-btn-link';

	const blockProps = useBlockProps( { className: sectionClass } );

	return (
		<>
			<InspectorControls>
				{/* ── Section Settings ── */}
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundStyle }
						options={ [
							{ label: 'Taupe 50 (default)',     value: 'taupe' },
							{ label: 'White (editorial)',        value: 'white' },
							{ label: 'Deep Blue (high-impact)', value: 'dark'  },
						] }
						onChange={ ( val ) => {
							const newIsDark = val === 'dark';
							// Reset eyebrow color if it's invalid for the new background
							const resetColor =
								( newIsDark && eyebrowColor === 'deep-blue' ) ||
								( ! newIsDark && eyebrowColor === 'white' )
									? 'cropx-blue'
									: eyebrowColor;
							setAttributes( { backgroundStyle: val, eyebrowColor: resetColor } );
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
						label={ __( 'Show body paragraph', 'cropx' ) }
						checked={ showBody }
						onChange={ ( val ) => setAttributes( { showBody: val } ) }
					/>
				</PanelBody>

				{/* ── CTAs ── */}
				<PanelBody title={ __( 'Call to action', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Primary button label', 'cropx' ) }
						value={ primaryLabel }
						onChange={ ( val ) => setAttributes( { primaryLabel: val } ) }
					/>
					<TextControl
						label={ __( 'Primary button URL', 'cropx' ) }
						value={ primaryUrl }
						onChange={ ( val ) => setAttributes( { primaryUrl: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show secondary CTA', 'cropx' ) }
						checked={ showSecondary }
						onChange={ ( val ) => setAttributes( { showSecondary: val } ) }
					/>
					{ showSecondary && (
						<>
							<SelectControl
								label={ __( 'Secondary style', 'cropx' ) }
								value={ secondaryStyle }
								options={ [
									{ label: 'Text link with arrow', value: 'link' },
									{ label: 'Ghost / outline button', value: 'ghost' },
								] }
								onChange={ ( val ) => setAttributes( { secondaryStyle: val } ) }
								help={ __( 'Use "ghost" on Deep Blue backgrounds; "link" on white.', 'cropx' ) }
							/>
							<TextControl
								label={ __( 'Secondary label', 'cropx' ) }
								value={ secondaryLabel }
								onChange={ ( val ) => setAttributes( { secondaryLabel: val } ) }
							/>
							<TextControl
								label={ __( 'Secondary URL', 'cropx' ) }
								value={ secondaryUrl }
								onChange={ ( val ) => setAttributes( { secondaryUrl: val } ) }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			{/* ── Editor preview ── */}
			<section { ...blockProps }>
				<div className="mcta-inner">

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

					<RichText
						tagName="h2"
						className="section-heading"
						value={ heading }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
						placeholder={ __( 'Heading…', 'cropx' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>

					{ showBody && (
						<RichText
							tagName="div"
							className="section-body"
							value={ body }
							onChange={ ( val ) => setAttributes( { body: val } ) }
							placeholder={ __( 'Supporting copy…', 'cropx' ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
					) }

					<div className="mcta-actions">
						<span className={ primaryClass }>
							{ primaryLabel || __( 'Button label', 'cropx' ) }
						</span>

						{ showSecondary && (
							<span className={ secondaryClass }>
								{ secondaryLabel || __( 'Secondary label', 'cropx' ) }
								{ secondaryStyle === 'link' && ARROW_SVG }
							</span>
						) }
					</div>

				</div>
			</section>
		</>
	);
}
