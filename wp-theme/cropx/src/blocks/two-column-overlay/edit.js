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
	RangeControl,
	ToggleControl,
	Button,
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

const PHOTO_POSITION_OPTIONS = [
	{ label: __( 'Photo right (default)', 'cropx' ), value: 'right' },
	{ label: __( 'Photo left',            'cropx' ), value: 'left'  },
];

const OVERLAY_POSITION_OPTIONS = [
	{ label: __( 'Top',    'cropx' ), value: 'top'    },
	{ label: __( 'Center', 'cropx' ), value: 'center' },
	{ label: __( 'Bottom', 'cropx' ), value: 'bottom' },
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

export default function Edit( { attributes, setAttributes } ) {
	const {
		photoPosition, overlayPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
		overlayId, overlayUrl, overlayAlt,
		overlayAnchor, bleedX, eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
	} = attributes;

	const isLeft = photoPosition === 'left';

	const blockProps = useBlockProps( {
		className:
			'tco-section' +
			( isLeft ? ' tco-section--photo-left' : '' ) +
			( segmentAccent !== 'general' ? ` tco-segment-${ segmentAccent }` : '' ),
	} );

	// CSS variables written as inline style on the grid — drives gap formula + overlay offsets.
	const gridStyle = {
		'--tco-anchor':  `${ overlayAnchor }%`,
		'--tco-bleed-x': `${ bleedX }rem`,
	};

	function onSelectPhoto( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}
	function onRemovePhoto() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}
	function onSelectOverlay( media ) {
		setAttributes( { overlayId: media.id, overlayUrl: media.url, overlayAlt: media.alt ?? '' } );
	}
	function onRemoveOverlay() {
		setAttributes( { overlayId: 0, overlayUrl: '', overlayAlt: '' } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Photo position', 'cropx' ) }
						value={ photoPosition }
						options={ PHOTO_POSITION_OPTIONS }
						onChange={ ( v ) => setAttributes( { photoPosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Overlay position', 'cropx' ) }
						value={ overlayPosition }
						options={ OVERLAY_POSITION_OPTIONS }
						onChange={ ( v ) => setAttributes( { overlayPosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the icon box background.', 'cropx' ) }
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
				</PanelBody>

				<PanelBody title={ __( 'Photo', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectPhoto }
							allowedTypes={ [ 'image' ] }
							value={ photoId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '8px' } }
								>
									{ photoUrl
										? __( 'Replace photo', 'cropx' )
										: __( 'Select photo', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ photoUrl && (
						<Button onClick={ onRemovePhoto } variant="link" isDestructive>
							{ __( 'Remove', 'cropx' ) }
						</Button>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Overlay PNG', 'cropx' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectOverlay }
							allowedTypes={ [ 'image' ] }
							value={ overlayId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '8px' } }
								>
									{ overlayUrl
										? __( 'Replace overlay PNG', 'cropx' )
										: __( 'Select overlay PNG', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ overlayUrl && (
						<Button onClick={ onRemoveOverlay } variant="link" isDestructive>
							{ __( 'Remove', 'cropx' ) }
						</Button>
					) }
				</PanelBody>

				<PanelBody title={ __( 'CTA', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'CTA style', 'cropx' ) }
						value={ ctaStyle ?? 'button' }
						options={ [
							{ label: __( 'Button',               'cropx' ), value: 'button' },
							{ label: __( 'Text link with arrow', 'cropx' ), value: 'link'   },
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

				<PanelBody title={ __( 'Overlay sizing', 'cropx' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Inner edge anchor (%)', 'cropx' ) }
						help={ __( "How far the overlay's inner edge sits inside the photo from the outer edge. Lower = larger overlay. Valid 0–50.", 'cropx' ) }
						value={ overlayAnchor }
						onChange={ ( v ) => setAttributes( { overlayAnchor: v } ) }
						min={ 0 }
						max={ 50 }
						step={ 1 }
					/>
					<RangeControl
						label={ __( 'Horizontal bleed (rem)', 'cropx' ) }
						help={ __( "How far the overlay's outer edge extends into the column gap. Also drives vertical bleed for top/bottom positions.", 'cropx' ) }
						value={ bleedX }
						onChange={ ( v ) => setAttributes( { bleedX: v } ) }
						min={ 0 }
						max={ 8 }
						step={ 0.5 }
					/>
				</PanelBody>

			</InspectorControls>

			<section { ...blockProps }>
				<div className="tco-inner">
					<div className="tco-grid" style={ gridStyle }>

						<div className="tco-content">
							{ showIcon !== false && (
								<div className="tco-icon-wrap">
									<div className="tco-icon" aria-hidden="true">
										<img src={ iconSrc( icon ) } alt="" width="28" height="28" />
									</div>
								</div>
							) }
							{ showEyebrow !== false && (
								<RichText
									tagName="span"
									className="tco-eyebrow"
									placeholder={ __( 'Eyebrow…', 'cropx' ) }
									value={ eyebrow }
									onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
									allowedFormats={ [] }
									style={{ color: `var(--${ eyebrowColor ?? 'cropx-blue' })` }}
								/>
							) }
							<RichText
								tagName="h2"
								className="tco-heading"
								placeholder={ __( 'Heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<RichText
								tagName="p"
								className="tco-body"
								placeholder={ __( 'Body text…', 'cropx' ) }
								value={ body }
								onChange={ ( v ) => setAttributes( { body: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ showCta !== false && ctaLabel && (
								ctaStyle === 'link'
									? <span className="tco-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="tco-cta tco-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						{ /* Visual column — progressive: no photo → placeholder; photo only → photo + add-overlay button; both → full composition */ }
						<div className="tco-visual">
							{ photoUrl ? (
								<>
									<div
										className="tco-photo"
										role="img"
										aria-label={ photoAlt || undefined }
										style={ { backgroundImage: `url('${ photoUrl }')` } }
									/>
									{ overlayUrl ? (
										<div
											className={ `tco-overlay tco-overlay--${ overlayPosition }` }
											role="img"
											aria-label={ overlayAlt || undefined }
											style={ { backgroundImage: `url('${ overlayUrl }')` } }
										/>
									) : (
										<MediaUploadCheck>
											<MediaUpload
												onSelect={ onSelectOverlay }
												allowedTypes={ [ 'image' ] }
												value={ overlayId }
												render={ ( { open } ) => (
													<Button
														onClick={ open }
														variant="primary"
														className="tco-overlay-add-btn"
													>
														{ __( 'Add overlay PNG', 'cropx' ) }
													</Button>
												) }
											/>
										</MediaUploadCheck>
									) }
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
				</div>
			</section>
		</>
	);
}
