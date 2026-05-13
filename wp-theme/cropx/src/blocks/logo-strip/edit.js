import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

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
	const { eyebrow } = attributes;

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
			</InspectorControls>

			<div { ...blockProps }>
				<div className="logo-strip-inner">
					<p className="logo-strip-eyebrow">{ eyebrow }</p>
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
