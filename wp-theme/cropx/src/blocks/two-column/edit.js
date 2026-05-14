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

const themeUri = window.cropxThemeData?.themeUri ?? '';

function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + slug + '.svg';
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		visualType, photoPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		photoId, photoUrl, photoAlt,
	} = attributes;

	const isPng  = visualType === 'png';
	const isLeft = photoPosition === 'left';

	const blockProps = useBlockProps( {
		className:
			'tcv-section' +
			( isLeft ? ' tcv-section--visual-left' : '' ) +
			( isPng  ? ' tcv-section--png'         : '' ) +
			( segmentAccent !== 'general' ? ` tcv-segment-${ segmentAccent }` : '' ),
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
				<PanelBody title={ __( 'Section', 'cropx' ) } initialOpen={ true }>
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
				</PanelBody>

				<PanelBody title={ __( 'CTA', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Button URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="tcv-inner">
					<div className="tcv-grid">

						<div className="tcv-content">
							<div className="tcv-icon-wrap">
								<div className="tcv-icon" aria-hidden="true">
									<img src={ iconSrc( icon ) } alt="" width="28" height="28" />
								</div>
							</div>
							<RichText
								tagName="span"
								className="tcv-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
							/>
							<RichText
								tagName="h2"
								className="tcv-heading"
								placeholder={ __( 'Heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
							<RichText
								tagName="p"
								className="tcv-body"
								placeholder={ __( 'Body text…', 'cropx' ) }
								value={ body }
								onChange={ ( v ) => setAttributes( { body: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ ctaLabel && (
								<span className="tcv-cta tcv-cta-preview" aria-hidden="true">
									{ ctaLabel }
								</span>
							) }
						</div>

						<div className="tcv-visual-col">
							{ photoUrl ? (
								<img
									className={ imgClass }
									src={ photoUrl }
									alt={ photoAlt }
								/>
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
