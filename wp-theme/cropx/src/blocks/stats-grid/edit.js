import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

import './editor.css';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const ARROW = (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading, body, ctaLabel, ctaUrl, segmentAccent,
		stat1Number, stat1Descriptor,
		stat2Number, stat2Descriptor,
		stat3Number, stat3Descriptor,
		stat4Number, stat4Descriptor,
		eyebrowColor, ctaStyle, showEyebrow, showCta,
	} = attributes;

	const blockProps = useBlockProps( {
		className:
			'sg-section' +
			( segmentAccent !== 'general' ? ` sg-segment-${ segmentAccent }` : '' ),
	} );

	const stats = [
		{ n: 1, number: stat1Number, descriptor: stat1Descriptor },
		{ n: 2, number: stat2Number, descriptor: stat2Descriptor },
		{ n: 3, number: stat3Number, descriptor: stat3Descriptor },
		{ n: 4, number: stat4Number, descriptor: stat4Descriptor },
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the 6px left border on each stat card.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show CTA', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
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
				</PanelBody>
				<PanelBody title={ __( 'CTA', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'CTA style', 'cropx' ) }
						value={ ctaStyle ?? 'link' }
						options={ [
							{ label: __( 'Text link with arrow', 'cropx' ), value: 'link'   },
							{ label: __( 'Button',               'cropx' ), value: 'button' },
						] }
						onChange={ ( val ) => setAttributes( { ctaStyle: val } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
				</PanelBody>

			</InspectorControls>

			<section { ...blockProps }>
				<div className="sg-inner">

					<div className="sg-content">
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="sg-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
							/>
						) }
						<RichText
							tagName="h2"
							className="sg-heading"
							placeholder={ __( 'Section heading…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
						<RichText
							tagName="p"
							className="sg-body"
							placeholder={ __( 'Body text…', 'cropx' ) }
							value={ body }
							onChange={ ( v ) => setAttributes( { body: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ showCta !== false && ctaLabel && (
							ctaStyle === 'button'
								? <span className="sg-btn" aria-hidden="true">{ ctaLabel }</span>
								: <span className="sg-cta sg-cta-preview" aria-hidden="true">{ ctaLabel }{ ARROW }</span>
						) }
					</div>

					<div className="sg-cards">
						{ stats.map( ( { n, number, descriptor } ) => (
							<div key={ n } className="sg-stat-card">
								<RichText
									tagName="span"
									className="sg-stat-number"
									placeholder="0%"
									value={ number }
									onChange={ ( v ) => setAttributes( { [ `stat${ n }Number` ]: v } ) }
									allowedFormats={ [] }
								/>
								<RichText
									tagName="p"
									className="sg-stat-descriptor"
									placeholder={ __( 'Stat descriptor…', 'cropx' ) }
									value={ descriptor }
									onChange={ ( v ) => setAttributes( { [ `stat${ n }Descriptor` ]: v } ) }
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
								/>
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
