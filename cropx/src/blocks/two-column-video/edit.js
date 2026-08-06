import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const PLAY_ICON = (
	<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
		<path d="M8 5.14v14l11-7-11-7z"/>
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		videoPosition, segmentAccent,
		icon, eyebrow, heading, body, ctaLabel, ctaUrl,
		eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'taupe',
		mobileStack = 'visual-first',
		displayMode,
		videoSource,
		videoUrl,
		videoMediaId,
		videoMediaSrc,
		thumbnailUrl,
		thumbnailAlt,
		showCaption,
		videoTitle,
		videoDesc,
	} = attributes;

	const isLeft   = videoPosition === 'left';
	const isMedia  = videoSource === 'media' && videoMediaSrc;
	const hasUrl   = videoSource === 'url' && videoUrl;

	const blockProps = useBlockProps( {
		className:
			'tcvid-section' +
			( isLeft ? ' tcvid-section--visual-left' : '' ) +
			( segmentAccent !== 'general' ? ` tcvid-segment-${ segmentAccent }` : '' ) +
			` tcvid-section--bg-${ bgColor }`,
	} );

	return (
		<>
			<InspectorControls>

				{ /* ── Section Settings ── */ }
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'White',               'cropx' ), value: 'white' },
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

				{ /* ── Video Positioning ── */ }
				<PanelBody title={ __( 'Video Positioning', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Video position', 'cropx' ) }
						value={ videoPosition }
						options={ [
							{ label: __( 'Video right (default)', 'cropx' ), value: 'right' },
							{ label: __( 'Video left',            'cropx' ), value: 'left'  },
						] }
						onChange={ ( v ) => setAttributes( { videoPosition: v } ) }
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

				{ /* ── Video ── */ }
				<PanelBody title={ __( 'Video', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Video display', 'cropx' ) }
						value={ displayMode }
						options={ [
							{ label: __( 'Inline — plays in place',     'cropx' ), value: 'inline'   },
							{ label: __( 'Lightbox — opens in overlay', 'cropx' ), value: 'lightbox' },
						] }
						onChange={ ( val ) => setAttributes( { displayMode: val } ) }
					/>
					<SelectControl
						label={ __( 'Video source', 'cropx' ) }
						value={ videoSource }
						options={ [
							{ label: __( 'External URL (YouTube / Vimeo)', 'cropx' ), value: 'url'   },
							{ label: __( 'Media library',                  'cropx' ), value: 'media' },
						] }
						onChange={ ( val ) => setAttributes( { videoSource: val } ) }
					/>

					{ videoSource === 'url' && (
						<TextControl
							label={ __( 'Video URL', 'cropx' ) }
							value={ videoUrl }
							onChange={ ( val ) => {
								const updates = { videoUrl: val };
								// Auto-populate YouTube thumbnail when the field is currently empty.
								if ( ! thumbnailUrl ) {
									const ytMatch = val.match(
										/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
									);
									if ( ytMatch ) {
										updates.thumbnailUrl = `https://img.youtube.com/vi/${ ytMatch[ 1 ] }/maxresdefault.jpg`;
									}
								}
								setAttributes( updates );
							} }
							placeholder="https://youtube.com/watch?v=..."
						/>
					) }

					{ videoSource === 'media' && (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									setAttributes( { videoMediaId: media.id, videoMediaSrc: media.url } )
								}
								allowedTypes={ [ 'video' ] }
								value={ videoMediaId || 0 }
								render={ ( { open } ) => (
									<div style={ { marginBottom: '0.5rem' } }>
										{ videoMediaSrc && (
											<p style={ { fontSize: '0.8125rem', color: '#757575', marginBottom: '0.375rem', wordBreak: 'break-all' } }>
												{ videoMediaSrc.split( '/' ).pop() }
											</p>
										) }
										<Button variant="secondary" onClick={ open }>
											{ videoMediaSrc
												? __( 'Replace video', 'cropx' )
												: __( 'Select from library', 'cropx' ) }
										</Button>
										{ videoMediaSrc && (
											<Button
												isDestructive
												variant="tertiary"
												onClick={ () => setAttributes( { videoMediaId: 0, videoMediaSrc: '' } ) }
												style={ { marginLeft: '0.5rem' } }
											>
												{ __( 'Remove', 'cropx' ) }
											</Button>
										) }
									</div>
								) }
							/>
						</MediaUploadCheck>
					) }

					<TextControl
						label={ __( 'Thumbnail URL (optional)', 'cropx' ) }
						value={ thumbnailUrl }
						onChange={ ( val ) => setAttributes( { thumbnailUrl: val } ) }
						placeholder="https://..."
						help={ videoSource === 'media' ? __( 'Used as poster image before playback.', 'cropx' ) : '' }
					/>
					{ thumbnailUrl && (
						<TextControl
							label={ __( 'Thumbnail alt text', 'cropx' ) }
							value={ thumbnailAlt }
							onChange={ ( val ) => setAttributes( { thumbnailAlt: val } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Show caption', 'cropx' ) }
						checked={ showCaption === true }
						onChange={ ( v ) => setAttributes( { showCaption: v } ) }
					/>
				</PanelBody>

				{ /* ── CTA ── */ }
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

			{ /* ── Canvas ── */ }
			<section { ...blockProps }>
				<div className="tcvid-inner">
					<div className="tcvid-grid">

						{ /* Text column */ }
						<div className="tcvid-content">
							{ showIcon !== false && (
								<div className="tcvid-icon-wrap">
									<div className="tcvid-icon" aria-hidden="true">
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
									? <span className="tcvid-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="tcvid-cta tcvid-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						{ /* Video column */ }
						<div className="tcvid-visual">
							<div className="vid-frame">
								{ isMedia ? (
									<video
										src={ videoMediaSrc }
										className="vid-thumb"
										preload="metadata"
									/>
								) : thumbnailUrl ? (
									<img
										src={ thumbnailUrl }
										alt={ thumbnailAlt || '' }
										className="vid-thumb"
									/>
								) : null }
								<div className="vid-play">
									<div className="vid-play-btn">
										{ PLAY_ICON }
									</div>
								</div>
								{ displayMode === 'lightbox' && (
									<span className="vid-lightbox-badge">
										{ __( 'Lightbox', 'cropx' ) }
									</span>
								) }
								{ ! isMedia && ! hasUrl && (
									<span className="vid-frame-label">
										{ __( 'Video', 'cropx' ) }
									</span>
								) }
							</div>
							{ showCaption && (
								<div className="vid-caption">
									<RichText
										tagName="p"
										className="vid-title"
										placeholder={ __( 'Video title…', 'cropx' ) }
										value={ videoTitle }
										onChange={ ( v ) => setAttributes( { videoTitle: v } ) }
										allowedFormats={ [] }
									/>
									<RichText
										tagName="p"
										className="vid-desc"
										placeholder={ __( 'Caption text…', 'cropx' ) }
										value={ videoDesc }
										onChange={ ( v ) => setAttributes( { videoDesc: v } ) }
										allowedFormats={ [ 'core/bold', 'core/italic' ] }
									/>
								</div>
							) }
						</div>

					</div>
				</div>
			</section>
		</>
	);
}
