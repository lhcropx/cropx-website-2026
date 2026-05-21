import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

import './editor.css';

const themeUri = window.cropxThemeData?.themeUri ?? '';

const LOGOS = [
	{ file: 'anheuser-busch-a.svg', alt: 'AB InBev' },
	{ file: 'dairy-holdings.svg',   alt: 'Dairy Holdings' },
	{ file: 'general-mills.svg',    alt: 'General Mills' },
	{ file: 'hzpc.svg',             alt: 'HZPC' },
	{ file: 'mccain.svg',           alt: 'McCain' },
	{ file: 'nasa.svg',             alt: 'NASA' },
	{ file: 'nec.svg',              alt: 'NEC' },
	{ file: 'nestle.svg',           alt: 'Nestlé' },
	{ file: 'pepsico.svg',          alt: 'PepsiCo' },
	{ file: 'ritter-sport.svg',     alt: 'Ritter Sport' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, eyebrowColor } = attributes;

	const blockProps = useBlockProps( { className: 'logo-strip' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Eyebrow text', 'cropx' ) }
						value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
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

			<div { ...blockProps }>
				<div className="logo-strip-inner">
					<p className="logo-strip-eyebrow" style={{ color: `var(--${ eyebrowColor ?? 'cropx-blue' })` }}>{ eyebrow }</p>
				</div>
				<div className="ls-marquee">
					<div className="ls-track">
						{ LOGOS.map( ( logo, i ) => (
							<img
								key={ `a-${ i }` }
								src={ themeUri + 'assets/logos/' + logo.file }
								alt={ logo.alt }
								className="ls-logo"
							/>
						) ) }
						{ LOGOS.map( ( logo, i ) => (
							<img
								key={ `b-${ i }` }
								src={ themeUri + 'assets/logos/' + logo.file }
								alt=""
								aria-hidden="true"
								className="ls-logo"
							/>
						) ) }
					</div>
				</div>
			</div>
		</>
	);
}
