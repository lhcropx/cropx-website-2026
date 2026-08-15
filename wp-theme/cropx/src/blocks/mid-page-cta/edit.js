import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';

import CtaLinkControl from '../../shared/CtaLinkControl';

const ARROW_SVG = (
	<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

const DOWNLOAD_ICON_SVG = (
	<svg className="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
		<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" />
		<polyline points="7 10 12 15 17 10" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" />
		<line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
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
		primaryLinkType,
		primaryFileId,
		primaryFileUrl,
		showSecondary,
		secondaryStyle,
		secondaryLabel,
		secondaryUrl,
		secondaryLinkType,
		secondaryFileId,
		secondaryFileUrl,
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

	// The deep-blue topo overlay URL is injected as a CSS custom property
	// (mirrors render.php on the front-end) so the pattern is visible in the
	// editor canvas too.
	const blockProps = useBlockProps( {
		className: sectionClass,
		style: isDark
			? { '--mcta-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

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
							{ label: 'Deep Blue + Topo', value: 'dark'  },
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
					<CtaLinkControl
						label={ __( 'Primary button link', 'cropx' ) }
						linkType={ primaryLinkType }
						onLinkTypeChange={ ( v ) => setAttributes( { primaryLinkType: v } ) }
						url={ primaryUrl }
						onUrlChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
						fileId={ primaryFileId }
						fileUrl={ primaryFileUrl }
						onFileSelect={ ( media ) => setAttributes( { primaryFileId: media.id, primaryFileUrl: media.url } ) }
						onFileRemove={ () => setAttributes( { primaryFileId: 0, primaryFileUrl: '', primaryLinkType: 'url' } ) }
					/>
					<ToggleControl
						label={ __( 'Show secondary CTA', 'cropx' ) }
						checked={ showSecondary }
						onChange={ ( val ) => setAttributes( { showSecondary: val } ) }
					/>
					{ showSecondary && (
						<>
							<TextControl
								label={ __( 'Secondary label', 'cropx' ) }
								value={ secondaryLabel }
								onChange={ ( val ) => setAttributes( { secondaryLabel: val } ) }
							/>
							<CtaLinkControl
								label={ __( 'Secondary button link', 'cropx' ) }
								linkType={ secondaryLinkType }
								onLinkTypeChange={ ( v ) => setAttributes( { secondaryLinkType: v } ) }
								url={ secondaryUrl }
								onUrlChange={ ( v ) => setAttributes( { secondaryUrl: v } ) }
								fileId={ secondaryFileId }
								fileUrl={ secondaryFileUrl }
								onFileSelect={ ( media ) => setAttributes( { secondaryFileId: media.id, secondaryFileUrl: media.url } ) }
								onFileRemove={ () => setAttributes( { secondaryFileId: 0, secondaryFileUrl: '', secondaryLinkType: 'url' } ) }
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
							<span className="mcta-btn-secondary">
								{ secondaryLabel || __( 'Secondary label', 'cropx' ) }
								{ secondaryLinkType === 'file' ? DOWNLOAD_ICON_SVG : ARROW_SVG }
							</span>
						) }
					</div>

				</div>
			</section>
		</>
	);
}
