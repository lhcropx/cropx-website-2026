import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import './editor.css';

const EYEBROW_CSS = {
	'cropx-blue': 'var(--cropx-blue)',
	'deep-blue':  'var(--deep-blue)',
	'white':      'var(--white)',
};

const PLAY_ICON = (
	<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
		<path d="M8 5.14v14l11-7-11-7z"/>
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		backgroundStyle,
		displayMode,
		layout,
		layoutAlignment,
		showIntro,
		introAlignment,
		showEyebrow,
		eyebrowColor,
		eyebrow,
		heading,
		body,
		videos,
	} = attributes;

	const [ selectedVideoIndex, setSelectedVideoIndex ] = useState( 0 );

	/* ── Video array helpers ─────────────────── */
	const updateVideo = ( index, field, value ) => {
		const updated = videos.map( ( v, i ) =>
			i === index ? { ...v, [ field ]: value } : v
		);
		setAttributes( { videos: updated } );
	};

	// Update multiple fields at once (e.g. mediaId + mediaSrc from MediaUpload).
	const updateVideoFields = ( index, fields ) => {
		const updated = videos.map( ( v, i ) =>
			i === index ? { ...v, ...fields } : v
		);
		setAttributes( { videos: updated } );
	};

	const addVideo = () => {
		const newId = Math.max( ...videos.map( ( v ) => v.id ), 0 ) + 1;
		setAttributes( {
			videos: [
				...videos,
				{
					id: newId,
					videoSource: 'url',
					title: 'Video Title',
					desc: 'Video description.',
					url: '',
					mediaId: 0,
					mediaSrc: '',
					thumbnailUrl: '',
					thumbnailAlt: '',
					showCaption: true,
				},
			],
		} );
	};

	const removeVideo = ( index ) => {
		if ( videos.length <= 1 ) return;
		setAttributes( { videos: videos.filter( ( _, i ) => i !== index ) } );
	};

	/* ── Derived class names ─────────────────── */
	const blockProps = useBlockProps( {
		className: `cropx-video vid-bg--${ backgroundStyle }`,
	} );

	const headerClass =
		'section-header' +
		( introAlignment === 'left' ? ' section-header--left' : '' );

	const captionClass =
		'vid-caption' +
		( layoutAlignment === 'center' ? ' vid-caption--centered' : '' );

	const gridClass =
		`vid-wrap vid-wrap--${ layout } vid-wrap--${ layoutAlignment }`;

	/* ── Render ──────────────────────────────── */
	return (
		<>
			{ /* ── Inspector ─────────────────────────── */ }
			<InspectorControls>

				<PanelBody title={ __( 'Layout & style', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ backgroundStyle }
						options={ [
							{ label: 'Taupe 50', value: 'taupe'     },
							{ label: 'White',    value: 'white'     },
							{ label: 'Deep blue', value: 'deep-blue' },
						] }
						onChange={ ( val ) => setAttributes( { backgroundStyle: val } ) }
					/>
					<SelectControl
						label={ __( 'Video display', 'cropx' ) }
						value={ displayMode }
						options={ [
							{ label: 'Inline — plays in place',         value: 'inline'    },
							{ label: 'Lightbox — opens in overlay', value: 'lightbox' },
						] }
						onChange={ ( val ) => setAttributes( { displayMode: val } ) }
					/>
					<SelectControl
						label={ __( 'Layout', 'cropx' ) }
						value={ layout }
						options={ [
							{ label: 'Single column', value: 'single'     },
							{ label: 'Two column',    value: 'two-column' },
						] }
						onChange={ ( val ) => setAttributes( { layout: val } ) }
					/>
					<SelectControl
						label={ __( 'Alignment', 'cropx' ) }
						value={ layoutAlignment }
						options={ [
							{ label: 'Centered', value: 'center' },
							{ label: 'Left',     value: 'left'   },
						] }
						onChange={ ( val ) => setAttributes( { layoutAlignment: val } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Section intro', 'cropx' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show intro header', 'cropx' ) }
						checked={ showIntro }
						onChange={ ( val ) => setAttributes( { showIntro: val } ) }
					/>
					{ showIntro && (
						<>
							<SelectControl
								label={ __( 'Intro alignment', 'cropx' ) }
								value={ introAlignment }
								options={ [
									{ label: 'Centered', value: 'center' },
									{ label: 'Left',     value: 'left'   },
								] }
								onChange={ ( val ) => setAttributes( { introAlignment: val } ) }
							/>
							<ToggleControl
								label={ __( 'Show eyebrow', 'cropx' ) }
								checked={ showEyebrow }
								onChange={ ( val ) => setAttributes( { showEyebrow: val } ) }
							/>
							{ showEyebrow && (
								<SelectControl
									label={ __( 'Eyebrow color', 'cropx' ) }
									value={ eyebrowColor }
									options={ [
										{ label: 'CropX Blue (default)', value: 'cropx-blue' },
										...( backgroundStyle !== 'deep-blue' ? [
											{ label: 'Deep Blue', value: 'deep-blue' },
										] : [] ),
										...( backgroundStyle === 'deep-blue' ? [
											{ label: 'White', value: 'white' },
										] : [] ),
									] }
									onChange={ ( val ) => setAttributes( { eyebrowColor: val } ) }
								/>
							) }
						</>
					) }
				</PanelBody>

				{ videos.map( ( video, index ) => {
					const source = video.videoSource || 'url';
					// Strip HTML tags from title for the panel header.
					const titleText = video.title
						? video.title.replace( /<[^>]+>/g, '' ).trim()
						: '';
					const panelTitle = `Video ${ index + 1 }${ titleText ? ` — ${ titleText.substring( 0, 24 ) }` : '' }`;
					return (
						<PanelBody
							key={ video.id }
							title={ panelTitle }
							opened={ selectedVideoIndex === index }
							onToggle={ () => setSelectedVideoIndex( index ) }
						>

							{ /* ── Source selector ── */ }
							<SelectControl
								label={ __( 'Video source', 'cropx' ) }
								value={ source }
								options={ [
									{ label: 'External URL (YouTube / Vimeo)', value: 'url'   },
									{ label: 'Media library',                  value: 'media' },
								] }
								onChange={ ( val ) => updateVideo( index, 'videoSource', val ) }
							/>

							{ /* ── URL source ── */ }
							{ source === 'url' && (
								<TextControl
									label={ __( 'Video URL', 'cropx' ) }
									value={ video.url }
									onChange={ ( val ) => {
										const updates = { url: val };
										// Auto-populate YouTube thumbnail when the field is currently empty.
										if ( ! video.thumbnailUrl ) {
											const ytMatch = val.match(
												/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
											);
											if ( ytMatch ) {
												updates.thumbnailUrl = `https://img.youtube.com/vi/${ ytMatch[ 1 ] }/maxresdefault.jpg`;
											}
										}
										updateVideoFields( index, updates );
									} }
									placeholder="https://youtube.com/watch?v=..."
								/>
							) }

							{ /* ── Media library source ── */ }
							{ source === 'media' && (
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) =>
											updateVideoFields( index, {
												mediaId:  media.id,
												mediaSrc: media.url,
											} )
										}
										allowedTypes={ [ 'video' ] }
										value={ video.mediaId || 0 }
										render={ ( { open } ) => (
											<div style={ { marginBottom: '0.5rem' } }>
												{ video.mediaSrc && (
													<p style={ { fontSize: '0.8125rem', color: '#757575', marginBottom: '0.375rem', wordBreak: 'break-all' } }>
														{ video.mediaSrc.split( '/' ).pop() }
													</p>
												) }
												<Button
													variant="secondary"
													onClick={ open }
												>
													{ video.mediaSrc
														? __( 'Replace video', 'cropx' )
														: __( 'Select from library', 'cropx' ) }
												</Button>
												{ video.mediaSrc && (
													<Button
														isDestructive
														variant="tertiary"
														onClick={ () => updateVideoFields( index, { mediaId: 0, mediaSrc: '' } ) }
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

							{ /* ── Thumbnail (both source types) ── */ }
							<TextControl
								label={ __( 'Thumbnail URL (optional)', 'cropx' ) }
								value={ video.thumbnailUrl }
								onChange={ ( val ) => updateVideo( index, 'thumbnailUrl', val ) }
								placeholder="https://..."
								help={ source === 'media' ? __( 'Used as poster image before playback.', 'cropx' ) : '' }
							/>
							{ video.thumbnailUrl && (
								<TextControl
									label={ __( 'Thumbnail alt text', 'cropx' ) }
									value={ video.thumbnailAlt }
									onChange={ ( val ) => updateVideo( index, 'thumbnailAlt', val ) }
								/>
							) }

							<ToggleControl
								label={ __( 'Show caption', 'cropx' ) }
								checked={ video.showCaption !== false }
								onChange={ ( val ) => updateVideo( index, 'showCaption', val ) }
							/>
							{ videos.length > 1 && (
								<Button
									isDestructive
									variant="tertiary"
									onClick={ () => removeVideo( index ) }
									style={ { marginTop: '0.25rem' } }
								>
									{ __( 'Remove this video', 'cropx' ) }
								</Button>
							) }

						</PanelBody>
					);
				} ) }

				<div style={ { padding: '8px 16px 16px' } }>
					<Button
						variant="secondary"
						onClick={ addVideo }
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add video', 'cropx' ) }
					</Button>
				</div>

			</InspectorControls>

			{ /* ── Canvas ──────────────────────────────── */ }
			<section { ...blockProps }>
				<div className="section-inner">

					{ showIntro && (
						<div className={ headerClass }>
							{ showEyebrow && (
								<RichText
									tagName="span"
									className="section-eyebrow"
									value={ eyebrow }
									onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
									placeholder={ __( 'Eyebrow text…', 'cropx' ) }
									style={ { color: EYEBROW_CSS[ eyebrowColor ] ?? 'var(--cropx-blue)' } }
								/>
							) }
							<RichText
								tagName="h2"
								className="section-heading"
								value={ heading }
								onChange={ ( val ) => setAttributes( { heading: val } ) }
								placeholder={ __( 'Section heading…', 'cropx' ) }
							/>
							<RichText
								tagName="div"
								className="section-body"
								value={ body }
								onChange={ ( val ) => setAttributes( { body: val } ) }
								placeholder={ __( 'Optional body text…', 'cropx' ) }
							/>
						</div>
					) }

					<div className={ gridClass }>
						{ videos.map( ( video, index ) => {
							const source  = video.videoSource || 'url';
							const hasMedia = source === 'media' && video.mediaSrc;
							const hasUrl   = source === 'url' && video.url;
							return (
							<div
								key={ video.id }
								className="vid-item"
								onClick={ () => setSelectedVideoIndex( index ) }
								style={ {
									outline: selectedVideoIndex === index
										? '2px solid var(--wp-admin-theme-color, #007cba)'
										: '2px solid transparent',
									outlineOffset: '4px',
									borderRadius: '4px',
								} }
							>
								<div className="vid-frame">
									{ hasMedia ? (
										// Show a video preview in the editor for library videos.
										<video
											src={ video.mediaSrc }
											className="vid-thumb"
											preload="metadata"
										/>
									) : video.thumbnailUrl ? (
										<img
											src={ video.thumbnailUrl }
											alt={ video.thumbnailAlt || '' }
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
									{ ! hasMedia && ! hasUrl && (
										<span className="vid-frame-label">
											{ `Video ${ index + 1 }` }
										</span>
									) }
								</div>
								{ video.showCaption !== false && (
									<div className={ captionClass }>
										<RichText
											tagName="p"
											className="vid-title"
											value={ video.title }
											onChange={ ( val ) => updateVideo( index, 'title', val ) }
											placeholder={ __( 'Video title…', 'cropx' ) }
										/>
										<RichText
											tagName="p"
											className="vid-desc"
											value={ video.desc }
											onChange={ ( val ) => updateVideo( index, 'desc', val ) }
											placeholder={ __( 'Video description…', 'cropx' ) }
										/>
									</div>
								) }
							</div>
						); } ) }
					</div>

				</div>
			</section>
		</>
	);
}
