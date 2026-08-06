import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
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

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

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

export default function Edit( { attributes, setAttributes } ) {
	const {
		photoPosition, overlayPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
		photoFocalX, photoFocalY, photoZoom,
		overlayId, overlayUrl, overlayAlt,
		overlayAnchor, bleedX, eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'taupe',
		mobileStack = 'visual-first',
	} = attributes;

	const isLeft = photoPosition === 'left';

	const blockProps = useBlockProps( {
		className:
			'tco-section' +
			( isLeft ? ' tco-section--photo-left' : '' ) +
			( segmentAccent !== 'general' ? ` tco-segment-${ segmentAccent }` : '' ) +
			` tco-section--bg-${ bgColor }`,
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
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'White',               'cropx' ), value: 'white' },
							{ label: __( 'Deep Blue',           'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the icon box background.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow !== false && (
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
					) }
					<ToggleControl
						label={ __( 'Show icon', 'cropx' ) }
						checked={ showIcon !== false }
						onChange={ ( v ) => setAttributes( { showIcon: v } ) }
					/>
					{ showIcon !== false && (
						<>
							<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px', marginTop: '16px' } }>
								{ __( 'Icon', 'cropx' ) }
							</p>
							<IconPicker
								value={ icon }
								onChange={ ( v ) => setAttributes( { icon: v } ) }
							/>
						</>
					) }
					<ToggleControl
						label={ __( 'Show CTA', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Photo Positioning', 'cropx' ) } initialOpen={ false }>
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
								className="section-heading"
								placeholder={ __( 'Heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<div className="section-body">
								<InnerBlocks
									allowedBlocks={ [ 'core/paragraph', 'core/list', 'core/heading' ] }
									template={ [ [ 'core/paragraph', { placeholder: __( 'Body text…', 'cropx' ) } ] ] }
									templateLock={ false }
								/>
							</div>
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
									>
										{ /* Inner bg div gets zoom transform; .tco-photo (overflow:hidden)
										     clips it so the outer frame never grows. */ }
										<div
											className="tco-photo-bg"
											style={ {
												backgroundImage: `url('${ photoUrl }')`,
												backgroundPosition: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
												transform: `scale(${ ( ( photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
												transformOrigin: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
											} }
										/>
									</div>
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
