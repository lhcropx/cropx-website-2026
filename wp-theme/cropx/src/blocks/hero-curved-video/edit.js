import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from '@wordpress/block-editor';
import './editor.css';
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';

import CtaLinkControl from '../../shared/CtaLinkControl';

const SEGMENT_OPTIONS = [
	{ label: __( 'CropX Blue (General)', 'cropx' ),     value: 'cropx' },
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

const SWOOP_FILL_OPTIONS = [
	{ label: __( 'Auto (match next section)', 'cropx' ), value: '' },
	{ label: __( 'White', 'cropx' ),                    value: 'white' },
	{ label: __( 'Taupe', 'cropx' ),                    value: 'taupe' },
	{ label: __( 'Deep Blue', 'cropx' ),                value: 'deep-blue' },
];

/**
 * One media-library picker (image or video). The sidebar thumbnail / filename
 * resolves fresh from the attachment ID — the stored URL is only a snapshot
 * that goes stale if the site's domain changes (same fix as the other heroes).
 */
function MediaField( { label, help, type, id, url, onSelect, onRemove } ) {
	const media = useSelect(
		( select ) => ( id ? select( 'core' ).getEntityRecord( 'root', 'media', id ) : null ),
		[ id ]
	);
	const resolvedUrl = media?.source_url ?? url;
	const fileName = resolvedUrl ? decodeURIComponent( resolvedUrl.split( '/' ).pop().split( '?' )[ 0 ] ) : '';

	return (
		<div className="hcv-videofield">
			<p className="hcv-videofield__label">{ label }</p>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onSelect }
					allowedTypes={ [ type ] }
					value={ id }
					render={ ( { open } ) => (
						<div style={ { display: 'flex', flexDirection: 'column', gap: '6px' } }>
							{ type === 'image' && resolvedUrl && (
								<img
									src={ resolvedUrl }
									alt=""
									style={ { maxWidth: '100%', height: 'auto', borderRadius: '2px' } }
								/>
							) }
							{ type === 'video' && fileName && (
								<p className="hcv-videofield__name">{ fileName }</p>
							) }
							<Button onClick={ open } variant={ id ? 'secondary' : 'primary' }>
								{ id
									? ( type === 'video' ? __( 'Replace video', 'cropx' ) : __( 'Replace image', 'cropx' ) )
									: ( type === 'video' ? __( 'Select video', 'cropx' ) : __( 'Select image', 'cropx' ) ) }
							</Button>
							{ id > 0 && (
								<Button onClick={ onRemove } variant="link" isDestructive>
									{ type === 'video' ? __( 'Remove video', 'cropx' ) : __( 'Remove image', 'cropx' ) }
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
			{ help && <p className="hcv-videofield__help">{ help }</p> }
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, eyebrow, heading, subheading,
		ctaLabel, ctaUrl, ctaLinkType, ctaFileId, ctaFileUrl,
		cta2Label, cta2Url, showCta2, cta2LinkType, cta2FileId, cta2FileUrl,
		showEyebrow, showCta, textWidth, overlayStrength, swoopFill,
		posterId, posterUrl, posterMobileId, posterMobileUrl,
		bgFocalX, bgFocalY, bgZoom, bgFlipX,
		videoMp4Id, videoMp4Url, videoWebmId, videoWebmUrl,
		videoMobileMp4Id, videoMobileMp4Url, videoMobileWebmId, videoMobileWebmUrl,
		mobileUsesDesktopVideo, videoLoop, showPause,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `hcv-block hcv-segment-${ segment }`,
	} );

	// Fresh poster URL from the attachment ID (falls back to the stored one
	// while loading or if the attachment was deleted).
	const posterMedia = useSelect(
		( select ) => ( posterId ? select( 'core' ).getEntityRecord( 'root', 'media', posterId ) : null ),
		[ posterId ]
	);
	const resolvedPosterUrl = posterMedia?.source_url ?? posterUrl;

	const posterMobileMedia = useSelect(
		( select ) => ( posterMobileId ? select( 'core' ).getEntityRecord( 'root', 'media', posterMobileId ) : null ),
		[ posterMobileId ]
	);
	const resolvedPosterMobileUrl = posterMobileMedia?.source_url ?? posterMobileUrl;

	const hasVideo = !! ( videoMp4Id || videoWebmId || videoMobileMp4Id || videoMobileWebmId );
	const canvasPoster = resolvedPosterUrl || resolvedPosterMobileUrl;

	const fx = Math.round( ( bgFocalX ?? 0.5 ) * 100 );
	const fy = Math.round( ( bgFocalY ?? 0.3 ) * 100 );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
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
						label={ __( 'Swoop fill colour', 'cropx' ) }
						help={ __( 'Colour of the curved corner at the hero bottom-right. Auto reads the next section\'s background.', 'cropx' ) }
						value={ swoopFill ?? '' }
						options={ SWOOP_FILL_OPTIONS }
						onChange={ ( v ) => setAttributes( { swoopFill: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the accent colour and swoop stroke gradient.', 'cropx' ) }
						value={ segment }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segment: v } ) }
					/>
					<RangeControl
						label={ __( 'Text legibility overlay (%)', 'cropx' ) }
						help={ __( 'Dark gradient behind the text. Lower it for dark footage, raise it for bright footage.', 'cropx' ) }
						value={ overlayStrength ?? 100 }
						onChange={ ( v ) => setAttributes( { overlayStrength: v } ) }
						min={ 0 }
						max={ 100 }
						step={ 5 }
					/>
					<RangeControl
						label={ __( 'Headline & subheadline width (%)', 'cropx' ) }
						value={ textWidth ?? 100 }
						onChange={ ( v ) => setAttributes( { textWidth: v } ) }
						min={ 40 }
						max={ 100 }
						step={ 5 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Poster image', 'cropx' ) } initialOpen={ true }>
					<p style={ { marginTop: 0, fontSize: '12px', color: '#757575', lineHeight: 1.4 } }>
						{ __( 'Required. This image paints first (it is what makes the page load fast) and stays visible whenever the video does not play. Export it from the first frame of the video so the fade-in is seamless.', 'cropx' ) }
					</p>
					<MediaField
						label={ __( 'Poster — desktop / tablet', 'cropx' ) }
						type="image"
						id={ posterId }
						url={ resolvedPosterUrl }
						onSelect={ ( media ) => setAttributes( { posterId: media.id, posterUrl: media.url } ) }
						onRemove={ () => setAttributes( { posterId: 0, posterUrl: '' } ) }
					/>
					<MediaField
						label={ __( 'Poster — mobile (optional)', 'cropx' ) }
						help={ __( 'Portrait crop for phones (≤768px). If empty, the desktop poster is used.', 'cropx' ) }
						type="image"
						id={ posterMobileId }
						url={ resolvedPosterMobileUrl }
						onSelect={ ( media ) => setAttributes( { posterMobileId: media.id, posterMobileUrl: media.url } ) }
						onRemove={ () => setAttributes( { posterMobileId: 0, posterMobileUrl: '' } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Background video', 'cropx' ) } initialOpen={ true }>
					<p style={ { marginTop: 0, fontSize: '12px', color: '#757575', lineHeight: 1.4 } }>
						{ __( 'Silent loop, no audio. Recommended: desktop 1280×720, mobile 720×1280, 6–10 s, 24 fps, audio removed, MP4 (H.264, "fast start") under ~3 MB desktop / ~1.5 MB mobile.', 'cropx' ) }
					</p>
					<MediaField
						label={ __( 'Desktop video — MP4', 'cropx' ) }
						type="video"
						id={ videoMp4Id }
						url={ videoMp4Url }
						onSelect={ ( media ) => setAttributes( { videoMp4Id: media.id, videoMp4Url: media.url } ) }
						onRemove={ () => setAttributes( { videoMp4Id: 0, videoMp4Url: '' } ) }
					/>
					<MediaField
						label={ __( 'Desktop video — WebM (optional)', 'cropx' ) }
						help={ __( 'Smaller file for browsers that support it; MP4 is the fallback.', 'cropx' ) }
						type="video"
						id={ videoWebmId }
						url={ videoWebmUrl }
						onSelect={ ( media ) => setAttributes( { videoWebmId: media.id, videoWebmUrl: media.url } ) }
						onRemove={ () => setAttributes( { videoWebmId: 0, videoWebmUrl: '' } ) }
					/>
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<MediaField
						label={ __( 'Mobile video — MP4 (≤768px)', 'cropx' ) }
						help={ __( 'Portrait 720×1280 recommended. Without a mobile video, phones show the poster only (no video download).', 'cropx' ) }
						type="video"
						id={ videoMobileMp4Id }
						url={ videoMobileMp4Url }
						onSelect={ ( media ) => setAttributes( { videoMobileMp4Id: media.id, videoMobileMp4Url: media.url } ) }
						onRemove={ () => setAttributes( { videoMobileMp4Id: 0, videoMobileMp4Url: '' } ) }
					/>
					<MediaField
						label={ __( 'Mobile video — WebM (optional)', 'cropx' ) }
						type="video"
						id={ videoMobileWebmId }
						url={ videoMobileWebmUrl }
						onSelect={ ( media ) => setAttributes( { videoMobileWebmId: media.id, videoMobileWebmUrl: media.url } ) }
						onRemove={ () => setAttributes( { videoMobileWebmId: 0, videoMobileWebmUrl: '' } ) }
					/>
					<ToggleControl
						label={ __( 'Use the desktop video on phones if there is no mobile video', 'cropx' ) }
						help={ __( 'Off by default — keeps phones light.', 'cropx' ) }
						checked={ mobileUsesDesktopVideo === true }
						onChange={ ( v ) => setAttributes( { mobileUsesDesktopVideo: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Playback', 'cropx' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Loop video', 'cropx' ) }
						checked={ videoLoop !== false }
						onChange={ ( v ) => setAttributes( { videoLoop: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show pause / play button', 'cropx' ) }
						help={ __( 'Recommended — looping motion longer than 5 seconds should be pausable (WCAG 2.2.2).', 'cropx' ) }
						checked={ showPause !== false }
						onChange={ ( v ) => setAttributes( { showPause: v } ) }
					/>
					<p style={ { fontSize: '12px', color: '#757575', lineHeight: 1.4 } }>
						{ __( 'The video loads only after the page has finished loading, and is skipped (poster only, with a Play button) for visitors with reduced-motion, Data Saver, or a slow connection. It pauses automatically when scrolled out of view.', 'cropx' ) }
					</p>
				</PanelBody>

				{ canvasPoster && (
					<PanelBody title={ __( 'Focal point, zoom & flip', 'cropx' ) } initialOpen={ false }>
						<p style={ { marginTop: 0, fontSize: '12px', color: '#757575', lineHeight: 1.4 } }>
							{ __( 'Applies to the poster and the video together.', 'cropx' ) }
						</p>
						<RangeControl
							label={ __( 'Focal X — left (%)', 'cropx' ) }
							value={ fx }
							onChange={ ( v ) => setAttributes( { bgFocalX: v / 100 } ) }
							min={ 0 }
							max={ 100 }
						/>
						<RangeControl
							label={ __( 'Focal Y — top (%)', 'cropx' ) }
							value={ fy }
							onChange={ ( v ) => setAttributes( { bgFocalY: v / 100 } ) }
							min={ 0 }
							max={ 100 }
						/>
						<RangeControl
							label={ __( 'Zoom (%)', 'cropx' ) }
							value={ bgZoom ?? 100 }
							onChange={ ( v ) => setAttributes( { bgZoom: v } ) }
							min={ 100 }
							max={ 200 }
						/>
						<ToggleControl
							label={ __( 'Flip horizontally', 'cropx' ) }
							checked={ bgFlipX ?? false }
							onChange={ ( v ) => setAttributes( { bgFlipX: v } ) }
						/>
					</PanelBody>
				) }

				<PanelBody title={ __( 'Call-to-action', 'cropx' ) }>
					<TextControl
						label={ __( 'Primary button label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<CtaLinkControl
						label={ __( 'Primary button link', 'cropx' ) }
						linkType={ ctaLinkType }
						onLinkTypeChange={ ( v ) => setAttributes( { ctaLinkType: v } ) }
						url={ ctaUrl }
						onUrlChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
						fileId={ ctaFileId }
						fileUrl={ ctaFileUrl }
						onFileSelect={ ( media ) => setAttributes( { ctaFileId: media.id, ctaFileUrl: media.url } ) }
						onFileRemove={ () => setAttributes( { ctaFileId: 0, ctaFileUrl: '', ctaLinkType: 'url' } ) }
					/>
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<ToggleControl
						label={ __( 'Show secondary CTA', 'cropx' ) }
						checked={ showCta2 === true }
						onChange={ ( v ) => setAttributes( { showCta2: v } ) }
					/>
					{ showCta2 && (
						<>
							<TextControl
								label={ __( 'Secondary button label', 'cropx' ) }
								value={ cta2Label }
								onChange={ ( v ) => setAttributes( { cta2Label: v } ) }
							/>
							<CtaLinkControl
								label={ __( 'Secondary button link', 'cropx' ) }
								linkType={ cta2LinkType }
								onLinkTypeChange={ ( v ) => setAttributes( { cta2LinkType: v } ) }
								url={ cta2Url }
								onUrlChange={ ( v ) => setAttributes( { cta2Url: v } ) }
								fileId={ cta2FileId }
								fileUrl={ cta2FileUrl }
								onFileSelect={ ( media ) => setAttributes( { cta2FileId: media.id, cta2FileUrl: media.url } ) }
								onFileRemove={ () => setAttributes( { cta2FileId: 0, cta2FileUrl: '', cta2LinkType: 'url' } ) }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="hcv-bleed-wrap">
					<div className="hcv-hero">
						{ /* Mirrors render.php's DOM: flip wrapper > zoom wrapper > poster.
						     The video is NOT loaded in the editor (keeps the editor light). */ }
						<div className="hcv-bg" style={ bgFlipX ? { transform: 'scaleX(-1)' } : undefined }>
							<div
								className="hcv-zoom"
								style={ {
									transform: `scale(${ ( ( bgZoom ?? 100 ) / 100 ).toFixed( 4 ) })`,
									transformOrigin: `${ fx }% ${ fy }%`,
								} }
							>
								{ canvasPoster && (
									<img
										className="hcv-poster"
										src={ canvasPoster }
										alt=""
										style={ { objectPosition: `${ fx }% ${ fy }%` } }
									/>
								) }
							</div>
						</div>

						<p className="hcv-editor-badge">
							{ ! canvasPoster
								? __( 'Add a poster image in the sidebar (required).', 'cropx' )
								: hasVideo
									? __( '▶ Background video — plays on the published page', 'cropx' )
									: __( 'No video added yet — showing the poster only.', 'cropx' ) }
						</p>

						<div
							className="hcv-overlay"
							style={ { opacity: ( overlayStrength ?? 100 ) / 100 } }
						/>

						<div
							className="hcv-content"
							style={ {
								'--hcv-headline-w': ( textWidth ?? 100 ) / 100,
								'--hcv-subhead-w':  ( textWidth ?? 100 ) / 100,
							} }
						>
							{ showEyebrow !== false && (
								<RichText
									tagName="p"
									className="hcv-eyebrow"
									placeholder={ __( 'Eyebrow text…', 'cropx' ) }
									value={ eyebrow }
									onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
									allowedFormats={ [] }
								/>
							) }
							<RichText
								tagName="h1"
								className="hcv-headline"
								placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/italic', 'core/bold' ] }
							/>
							<RichText
								tagName="p"
								className="hcv-subheadline"
								placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
								value={ subheading }
								onChange={ ( v ) => setAttributes( { subheading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ showCta !== false && ctaLabel && (
								<div className="hcv-cta-row">
									<span className="hcv-cta" aria-hidden="true">{ ctaLabel }</span>
									{ showCta2 && cta2Label && (
										<span className="hcv-cta--ghost" aria-hidden="true">
											{ cta2Label }
											{ cta2LinkType === 'file' ? (
												<svg className="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round"/></svg>
											) : (
												<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
											) }
										</span>
									) }
								</div>
							) }
						</div>

						{ /* Swoop preview — simplified static version for the editor */ }
						<div className="hcv-swoop-preview" aria-hidden="true" />
					</div>
				</div>
			</div>
		</>
	);
}
