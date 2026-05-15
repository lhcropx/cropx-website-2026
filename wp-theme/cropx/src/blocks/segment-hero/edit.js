import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	URLInput,
} from '@wordpress/block-editor';
import './editor.css';
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
} from '@wordpress/components';

const SEGMENT_OPTIONS = [
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

const BADGE_CONFIG = {
	enterprise:           { line1: 'Enterprise',  line2: 'Solutions' },
	'service-provider':   { line1: 'Service',     line2: 'Providers' },
	'on-farm':            { line1: 'On-Farm',      line2: 'Solutions' },
};

function MediaPanel( { title, imageId, imageUrl, onSelect, onRemove, defaultLabel } ) {
	return (
		<PanelBody title={ title }>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onSelect }
					allowedTypes={ [ 'image' ] }
					value={ imageId }
					render={ ( { open } ) => (
						<div style={ { display: 'flex', flexDirection: 'column', gap: '8px' } }>
							{ imageUrl && (
								<img
									src={ imageUrl }
									alt=""
									style={ { maxWidth: '100%', height: 'auto', borderRadius: '2px', marginBottom: '4px' } }
								/>
							) }
							<Button onClick={ open } variant="primary">
								{ imageId ? __( 'Replace image', 'cropx' ) : defaultLabel }
							</Button>
							{ imageId > 0 && (
								<Button onClick={ onRemove } variant="link" isDestructive>
									{ __( 'Remove image', 'cropx' ) }
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</PanelBody>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, eyebrow, heading, subheading,
		ctaLabel, ctaUrl,
		bgImageId, bgImageUrl,
		deviceImageId, deviceImageUrl,
		phoneImageId, phoneImageUrl,
	} = attributes;

	const badge = BADGE_CONFIG[ segment ] || BADGE_CONFIG.enterprise;

	const blockProps = useBlockProps( {
		className: `sgh-block sgh-segment-${ segment }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Segment', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the badge, accent color, and colored border stripe.', 'cropx' ) }
						value={ segment }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segment: v } ) }
					/>
				</PanelBody>

				<MediaPanel
					title={ __( 'Background image', 'cropx' ) }
					imageId={ bgImageId }
					imageUrl={ bgImageUrl }
					onSelect={ ( media ) => setAttributes( { bgImageId: media.id, bgImageUrl: media.url } ) }
					onRemove={ () => setAttributes( { bgImageId: 0, bgImageUrl: '' } ) }
					defaultLabel={ __( 'Select background image', 'cropx' ) }
				/>

				<MediaPanel
					title={ __( 'Device image (sensor PNG)', 'cropx' ) }
					imageId={ deviceImageId }
					imageUrl={ deviceImageUrl }
					onSelect={ ( media ) => setAttributes( { deviceImageId: media.id, deviceImageUrl: media.url } ) }
					onRemove={ () => setAttributes( { deviceImageId: 0, deviceImageUrl: '' } ) }
					defaultLabel={ __( 'Select image (default: vertex-partial-a)', 'cropx' ) }
				/>

				<MediaPanel
					title={ __( 'Phone image (bleeding below hero)', 'cropx' ) }
					imageId={ phoneImageId }
					imageUrl={ phoneImageUrl }
					onSelect={ ( media ) => setAttributes( { phoneImageId: media.id, phoneImageUrl: media.url } ) }
					onRemove={ () => setAttributes( { phoneImageId: 0, phoneImageUrl: '' } ) }
					defaultLabel={ __( 'Select image (default: phone-mockup-b)', 'cropx' ) }
				/>

				<PanelBody title={ __( 'Call-to-action', 'cropx' ) }>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<URLInput
						label={ __( 'Button URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ /* Simplified nav preview — badge only, no dropdown interactivity */ }
				<div className="sgh-nav-preview">
					<div className="sgh-nav-preview-inner">
						<span className="sgh-nav-preview-wordmark">CropX</span>
						<div className="sgh-badge">
							<span>
								{ badge.line1 }
								<br />
								{ badge.line2 }
							</span>
						</div>
						<span className="sgh-nav-preview-links" aria-hidden="true">
							Platform &nbsp;·&nbsp; Solutions &nbsp;·&nbsp; Resources &nbsp;·&nbsp; Company
						</span>
						<span className="sgh-nav-preview-login">Log in</span>
					</div>
				</div>

				{ /* Hero */ }
				<div className="sgh-hero">
					<div
						className="sgh-bg"
						style={ bgImageUrl ? { backgroundImage: `url(${ bgImageUrl })` } : undefined }
					/>
					<div className="sgh-overlay" />
					<div className="sgh-pattern" />
					<div className="sgh-content">
						<RichText
							tagName="p"
							className="sgh-eyebrow"
							placeholder={ __( 'Eyebrow text…', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							allowedFormats={ [] }
						/>
						<RichText
							tagName="h1"
							className="sgh-headline"
							placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/italic', 'core/bold' ] }
						/>
						<RichText
							tagName="p"
							className="sgh-subheadline"
							placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
							value={ subheading }
							onChange={ ( v ) => setAttributes( { subheading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ ctaLabel && (
							<span className="sgh-cta" aria-hidden="true">
								{ ctaLabel }
							</span>
						) }
					</div>
				</div>

				{ /* Segment border stripe */ }
				<div className="sgh-border" aria-hidden="true" />
			</div>
		</>
	);
}
