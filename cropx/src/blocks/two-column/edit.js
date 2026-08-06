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
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

const VISUAL_TYPE_OPTIONS = [
	{ label: __( 'Photo',       'cropx' ), value: 'photo' },
	{ label: __( 'Product PNG', 'cropx' ), value: 'png'   },
];

const POSITION_OPTIONS = [
	{ label: __( 'Visual right (default)', 'cropx' ), value: 'right' },
	{ label: __( 'Visual left',            'cropx' ), value: 'left'  },
];

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const ASPECT_RATIO_OPTIONS = [
	{ label: __( 'Natural — matches text height', 'cropx' ), value: 'natural' },
	{ label: __( '16:9 — Widescreen',             'cropx' ), value: '16/9'    },
	{ label: __( '3:2 — Standard landscape',      'cropx' ), value: '3/2'     },
	{ label: __( '4:3 — Classic landscape',       'cropx' ), value: '4/3'     },
	{ label: __( '1:1 — Square',                  'cropx' ), value: '1/1'     },
	{ label: __( '3:4 — Portrait',                'cropx' ), value: '3/4'     },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		visualType, photoPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
		photoFocalX, photoFocalY, photoZoom, photoAspectRatio,
		eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'taupe',
		floatImage,
		mobileStack = 'visual-first',
	} = attributes;

	const isPng  = visualType === 'png';
	const isLeft = photoPosition === 'left';

	const isRatio = ! isPng && ( photoAspectRatio ?? '4/3' ) !== 'natural';

	const blockProps = useBlockProps( {
		className:
			'tcv-section' +
			( isLeft    ? ' tcv-section--visual-left'  : '' ) +
			( isPng     ? ' tcv-section--png'          : '' ) +
			( floatImage ? ' tcv-section--float'       : '' ) +
			( isRatio   ? ' tcv-section--photo-ratio'  : '' ) +
			( segmentAccent !== 'general' ? ` tcv-segment-${ segmentAccent }` : '' ) +
			` tcv-section--bg-${ bgColor }`,
	} );

	function onSelectMedia( media ) {
		setAttributes( { photoId: media.id, photoUrl: media.url, photoAlt: media.alt ?? '' } );
	}

	function onRemoveMedia() {
		setAttributes( { photoId: 0, photoUrl: '', photoAlt: '' } );
	}

	const imgClass = isPng
		? 'tcv-visual-img tcv-visual-img--png'
		: 'tcv-visual-img tcv-visual-img--photo';

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
						label={ __( 'Visual type', 'cropx' ) }
						value={ visualType }
						options={ VISUAL_TYPE_OPTIONS }
						onChange={ ( v ) => setAttributes( { visualType: v } ) }
					/>
					<SelectControl
						label={ __( 'Visual position', 'cropx' ) }
						value={ photoPosition }
						options={ POSITION_OPTIONS }
						onChange={ ( v ) => setAttributes( { photoPosition: v } ) }
					/>
					<ToggleControl
						label={ __( 'Float image', 'cropx' ) }
						help={ __( 'Adds a gentle up-and-down float animation to the image.', 'cropx' ) }
						checked={ floatImage === true }
						onChange={ ( v ) => setAttributes( { floatImage: v } ) }
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

				<PanelBody
					title={ isPng ? __( 'PNG', 'cropx' ) : __( 'Photo', 'cropx' ) }
					initialOpen={ false }
				>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectMedia }
							allowedTypes={ [ 'image' ] }
							value={ photoId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '8px' } }
								>
									{ photoUrl
										? ( isPng ? __( 'Replace PNG', 'cropx' )   : __( 'Replace photo', 'cropx' ) )
										: ( isPng ? __( 'Select PNG', 'cropx' )    : __( 'Select photo', 'cropx' ) ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ photoUrl && (
						<Button onClick={ onRemoveMedia } variant="link" isDestructive>
							{ __( 'Remove', 'cropx' ) }
						</Button>
					) }
					{ photoUrl && ! isPng && (
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
							<SelectControl
								label={ __( 'Photo aspect ratio', 'cropx' ) }
								value={ photoAspectRatio ?? '4/3' }
								options={ ASPECT_RATIO_OPTIONS }
								onChange={ ( v ) => setAttributes( { photoAspectRatio: v } ) }
							/>
						</>
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

			</InspectorControls>

			<section { ...blockProps }>
				<div className="tcv-inner">
					<div className="tcv-grid">

						<div className="tcv-content">
							{ showIcon !== false && (
								<div className="tcv-icon-wrap">
									<div className="tcv-icon" aria-hidden="true">
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
									style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
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
									? <span className="tcv-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="tcv-cta tcv-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						<div className="tcv-visual-col">
							{ photoUrl ? (
								! isPng ? (
									<div
										className={ 'tcv-photo-wrap' + ( isRatio ? ' tcv-photo-wrap--ratio' : '' ) }
										style={ isRatio ? { aspectRatio: photoAspectRatio } : undefined }
									>
										<img
											className={ imgClass }
											src={ photoUrl }
											alt={ photoAlt }
											style={ {
												objectPosition: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
												transform: `scale(${ ( ( photoZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
												transformOrigin: `${ Math.round( ( photoFocalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( photoFocalY ?? 0.5 ) * 100 ) }%`,
											} }
										/>
									</div>
								) : (
									<img
										className={ imgClass }
										src={ photoUrl }
										alt={ photoAlt }
									/>
								)
							) : (
								<MediaPlaceholder
									onSelect={ onSelectMedia }
									allowedTypes={ [ 'image' ] }
									accept="image/*"
									labels={ {
										title: isPng
											? __( 'Product PNG', 'cropx' )
											: __( 'Feature photo', 'cropx' ),
									} }
								/>
							) }
						</div>

					</div>
				</div>
			</section>
		</>
	);
}
