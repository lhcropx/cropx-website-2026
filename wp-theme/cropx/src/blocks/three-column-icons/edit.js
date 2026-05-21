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
} from '@wordpress/components';

import './editor.css';

const ICON_OPTIONS = [
	{ label: 'alarm-clock',      value: 'alarm-clock' },
	{ label: 'antenna',          value: 'antenna' },
	{ label: 'corn',             value: 'corn' },
	{ label: 'field-sun',        value: 'field-sun' },
	{ label: 'fields',           value: 'fields' },
	{ label: 'language',         value: 'language' },
	{ label: 'nutrition',        value: 'nutrition' },
	{ label: 'sensor-cloud',     value: 'sensor-cloud' },
	{ label: 'speed',            value: 'speed' },
	{ label: 'valve-irrigation', value: 'valve-irrigation' },
];

const BG_OPTIONS = [
	{ label: __( 'White',     'cropx' ), value: 'white' },
	{ label: __( 'Deep Blue', 'cropx' ), value: 'blue'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (Deep Blue box)',         'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold box)',            'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra box)',     'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf box)',           'cropx' ), value: 'on-farm'          },
];

const themeUri = window.cropxThemeData?.themeUri ?? '';

function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + slug + '.svg';
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading,
		backgroundVariant, segmentAccent,
		col1Icon, col1Heading, col1Body, col1CtaLabel, col1CtaUrl,
		col2Icon, col2Heading, col2Body, col2CtaLabel, col2CtaUrl,
		col3Icon, col3Heading, col3Body, col3CtaLabel, col3CtaUrl,
		eyebrowColor,
	} = attributes;

	const isBlue = backgroundVariant === 'blue';

	const blockProps = useBlockProps( {
		className:
			`tci-section tci-section--${ backgroundVariant }` +
			( ! isBlue && segmentAccent !== 'general'
				? ` tci-segment-${ segmentAccent }`
				: '' ),
	} );

	const ARROW = (
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundVariant }
						options={ BG_OPTIONS }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
					{ ! isBlue && (
						<SelectControl
							label={ __( 'Icon box accent', 'cropx' ) }
							help={ __( 'Tints the icon box on white sections. Deep Blue sections always use a white box.', 'cropx' ) }
							value={ segmentAccent }
							options={ SEGMENT_OPTIONS }
							onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Column 1', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Icon', 'cropx' ) }
						value={ col1Icon }
						options={ ICON_OPTIONS }
						onChange={ ( v ) => setAttributes( { col1Icon: v } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ col1CtaLabel }
						onChange={ ( v ) => setAttributes( { col1CtaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ col1CtaUrl }
						onChange={ ( v ) => setAttributes( { col1CtaUrl: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Column 2', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Icon', 'cropx' ) }
						value={ col2Icon }
						options={ ICON_OPTIONS }
						onChange={ ( v ) => setAttributes( { col2Icon: v } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ col2CtaLabel }
						onChange={ ( v ) => setAttributes( { col2CtaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ col2CtaUrl }
						onChange={ ( v ) => setAttributes( { col2CtaUrl: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Column 3', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Icon', 'cropx' ) }
						value={ col3Icon }
						options={ ICON_OPTIONS }
						onChange={ ( v ) => setAttributes( { col3Icon: v } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ col3CtaLabel }
						onChange={ ( v ) => setAttributes( { col3CtaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ col3CtaUrl }
						onChange={ ( v ) => setAttributes( { col3CtaUrl: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Eyebrow', 'cropx' ) } initialOpen={ false }>
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
			</InspectorControls>

			<section { ...blockProps }>
				<div className="tci-inner">
					{ /* Header is always visible in the editor so both fields stay focusable.
					     render.php hides it when both eyebrow and heading are empty. */ }
					<div className="tci-header">
						<RichText
							tagName="span"
							className="tci-eyebrow"
							placeholder={ __( 'Eyebrow (leave both blank to hide header on front end)…', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							allowedFormats={ [] }
							style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
						/>
						<RichText
							tagName="h2"
							className="tci-heading"
							placeholder={ __( 'Section heading…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
					</div>

					<div className="tci-grid">
						{ [
							{ n: 1, icon: col1Icon, h: col1Heading, body: col1Body, cta: col1CtaLabel },
							{ n: 2, icon: col2Icon, h: col2Heading, body: col2Body, cta: col2CtaLabel },
							{ n: 3, icon: col3Icon, h: col3Heading, body: col3Body, cta: col3CtaLabel },
						].map( ( { n, icon, h, body, cta } ) => (
							<div key={ n } className="tci-item">
								<div className="tci-icon" aria-hidden="true">
									<img src={ iconSrc( icon ) } alt="" width="24" height="24" />
								</div>
								<RichText
									tagName="h3"
									className="tci-item-heading"
									placeholder={ __( 'Column heading…', 'cropx' ) }
									value={ h }
									onChange={ ( v ) => setAttributes( { [ `col${ n }Heading` ]: v } ) }
									allowedFormats={ [ 'core/bold' ] }
								/>
								<RichText
									tagName="p"
									className="tci-body"
									placeholder={ __( 'Body text…', 'cropx' ) }
									value={ body }
									onChange={ ( v ) => setAttributes( { [ `col${ n }Body` ]: v } ) }
									allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								/>
								{ cta && (
									<span className="tci-cta tci-cta-preview" aria-hidden="true">
										{ cta }
										{ ARROW }
									</span>
								) }
							</div>
						) ) }
					</div>
				</div>
			</section>
		</>
	);
}
