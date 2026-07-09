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
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
	RangeControl,
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
	{ label: __( 'Taupe 50',  'cropx' ), value: 'taupe' },
	{ label: __( 'Deep Blue', 'cropx' ), value: 'blue'  },
];

const POSITION_OPTIONS = [
	{ label: __( 'Photo right (default)', 'cropx' ), value: 'right' },
	{ label: __( 'Photo left',            'cropx' ), value: 'left'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const themeUri = window.cropxThemeData?.themeUri ?? '';

function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + slug + '.svg';
}

const ARROW = (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		photoPosition, backgroundVariant, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
		photoFocalX, photoFocalY, photoZoom,
		cardContext, cardNumber, cardMetric, eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		mobileStack = 'visual-first',
	} = attributes;

	const isBlue   = backgroundVariant === 'blue';
	const isTaupe  = backgroundVariant === 'taupe';
	const photoLeft = photoPosition === 'left';

	const blockProps = useBlockProps( {
		className:
			'fstat-section' +
			( photoLeft ? ' fstat-section--photo-left' : '' ) +
			( isBlue    ? ' fstat-section--deep-blue'  : '' ) +
			( isTaupe   ? ' fstat-section--bg-taupe'   : '' ) +
			( segmentAccent !== 'general' ? ` fstat-segment-${ segmentAccent }` : '' ),
	} );

	function onSelectPhoto( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}

	function onRemovePhoto() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundVariant }
						options={ BG_OPTIONS }
						onChange={ ( v ) => setAttributes( { backgroundVariant: v } ) }
					/>
					<SelectControl
						label={ __( 'Photo position', 'cropx' ) }
						value={ photoPosition }
						options={ POSITION_OPTIONS }
						onChange={ ( v ) => setAttributes( { photoPosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the icon box and stat card border.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
					<SelectControl
						label={ __( 'Icon', 'cropx' ) }
						value={ icon }
						options={ ICON_OPTIONS }
						onChange={ ( v ) => setAttributes( { icon: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show icon', 'cropx' ) }
						checked={ showIcon !== false }
						onChange={ ( v ) => setAttributes( { showIcon: v } ) }
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
					<SelectControl
						label={ __( 'Mobile stack order', 'cropx' ) }
						help={ __( 'Which column appears first when the layout collapses to one column.', 'cropx' ) }
						value={ mobileStack }
						options={ [
							{ label: __( 'Visual on top (default)', 'cropx' ), value: 'visual-first' },
							{ label: __( 'Text on top',             'cropx' ), value: 'text-first'   },
						] }
						onChange={ ( v ) => setAttributes( { mobileStack: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Background Photo', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectPhoto }
							allowedTypes={ [ 'image' ] }
							value={ photoId }
							render={ ( { open } ) => (
								<Button onClick={ open } variant="secondary" style={ { marginBottom: '8px' } }>
									{ photoUrl
										? __( 'Replace photo', 'cropx' )
										: __( 'Select photo', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ photoUrl && (
						<Button onClick={ onRemovePhoto } variant="link" isDestructive>
							{ __( 'Remove photo', 'cropx' ) }
						</Button>
					) }
					{ photoUrl && (
						<>
							<RangeControl
								label={ __( 'Focal X — left (%)', 'cropx' ) }
								value={ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { photoFocalX: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Focal Y — top (%)', 'cropx' ) }
								value={ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => setAttributes( { photoFocalY: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Zoom (%)', 'cropx' ) }
								value={ photoZoom ?? 100 }
								onChange={ ( v ) => setAttributes( { photoZoom: v } ) }
								min={ 100 }
								max={ 200 }
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Stat card', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Context label top', 'cropx' ) }
						help={ __( 'Small-caps text above the number.', 'cropx' ) }
						value={ cardContext }
						onChange={ ( v ) => setAttributes( { cardContext: v } ) }
					/>
					<TextControl
						label={ __( 'Number', 'cropx' ) }
						help={ __( 'Include suffix inline, e.g. "30%" or "3M+".', 'cropx' ) }
						value={ cardNumber }
						onChange={ ( v ) => setAttributes( { cardNumber: v } ) }
					/>
					<TextControl
						label={ __( 'Context label bottom', 'cropx' ) }
						help={ __( 'Small-caps text below the number.', 'cropx' ) }
						value={ cardMetric }
						onChange={ ( v ) => setAttributes( { cardMetric: v } ) }
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
				<div className="fstat-inner">

					<div className="fstat-content">
						{ showIcon !== false && (
							<div className="fstat-icon-wrap">
								<div className="fstat-icon" aria-hidden="true">
									<img src={ iconSrc( icon ) } alt="" width="28" height="28" />
								</div>
							</div>
						) }
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="section-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								style={{ color: `var(--${ eyebrowColor ?? 'cropx-blue' })` }}
							/>
						) }
						<RichText
							tagName="h2"
							className="fstat-h2"
							placeholder={ __( 'Feature heading…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
						<RichText
							tagName="div"
							className="section-body"
							placeholder={ __( 'Body text…', 'cropx' ) }
							value={ body }
							onChange={ ( v ) => setAttributes( { body: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ showCta !== false && ctaLabel && (
							ctaStyle === 'button'
								? <span className="fstat-btn" aria-hidden="true">{ ctaLabel }</span>
								: <span className="fstat-link fstat-link-preview" aria-hidden="true">{ ctaLabel }{ ARROW }</span>
						) }
					</div>

					<div className="fstat-visual">
						{ photoUrl ? (
							<>
								<div className="fstat-photo-wrap">
									<img
										src={ photoUrl }
										alt={ photoAlt }
										style={ {
											objectPosition: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
											transform: `scale(${ ( ( photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
											transformOrigin: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
										} }
									/>
								</div>
								<div className="fstat-card" aria-hidden="true">
									{ cardContext && (
										<p className="fstat-card-context">{ cardContext }</p>
									) }
									{ cardNumber && (
										<span className="fstat-card-num">{ cardNumber }</span>
									) }
									{ cardMetric && (
										<p className="fstat-card-metric">{ cardMetric }</p>
									) }
								</div>
							</>
						) : (
							<MediaPlaceholder
								onSelect={ onSelectPhoto }
								allowedTypes={ [ 'image' ] }
								accept="image/*"
								labels={ { title: __( 'Feature photo', 'cropx' ) } }
							/>
						) }
					</div>

				</div>
			</section>
		</>
	);
}
