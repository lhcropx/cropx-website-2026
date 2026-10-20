import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
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
	RangeControl,
	Button,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import { moveItem } from '../../shared/reorder';
import './editor.css';

const MAX_IMAGES = 16;

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const RATIOS = { portrait: '4 / 5', square: '1 / 1', landscape: '4 / 3', masonry: '1 / 1' };

const snapRatio = ( w, h ) => {
	if ( ! w || ! h ) return '1 / 1';
	const r = w / h;
	if ( r >= 1.15 ) return '3 / 2';
	if ( r <= 0.88 ) return '3 / 4';
	return '1 / 1';
};

export default function Edit( { attributes, setAttributes } ) {
	const {
		galleryPosition, segmentAccent,
		icon, eyebrow, heading, ctaLabel, ctaUrl,
		eyebrowColor, ctaStyle,
		showIcon, showEyebrow, showCta,
		bgColor = 'taupe',
		images = [],
		tileShape = 'portrait',
		speed = 'medium',
		speedMobile = 'same',
		scrollDirection = 'up',
		galleryHeight = 560,
		pauseOnHover = true,
		allowDrag = true,
		showPauseButton = true,
	} = attributes;

	const isLeft = galleryPosition === 'left';

	// Resolve each photo's URL fresh from its attachment ID (same pattern as
	// logo-strip / two-column-video) so a stored URL from a different domain
	// never leaves the canvas preview broken.
	const idKey = images.map( ( i ) => i.id || 0 ).join( ',' );
	const mediaRecords = useSelect(
		( select ) => images.map( ( i ) => ( i.id ? select( 'core' ).getMedia( i.id ) : null ) ),
		[ idKey ]
	);
	const urlFor = ( img, idx ) =>
		mediaRecords[ idx ]?.media_details?.sizes?.medium_large?.source_url ??
		mediaRecords[ idx ]?.source_url ??
		img.url;

	const blockProps = useBlockProps( {
		className:
			'mg-section' +
			( isLeft ? ' mg-section--gallery-left' : '' ) +
			( segmentAccent !== 'general' ? ` mg-segment-${ segmentAccent }` : '' ) +
			` mg-section--bg-${ bgColor }`,
		style: {
			'--mg-height': `${ galleryHeight }px`,
			'--mg-ratio': RATIOS[ tileShape ] ?? RATIOS.portrait,
			...( bgColor === 'deep-blue'
				? { '--mg-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
				: {} ),
		},
	} );

	// ── Photo list helpers ──
	const addImages = ( items ) => {
		const have = new Set( images.map( ( i ) => i.id ) );
		const added = ( Array.isArray( items ) ? items : [ items ] )
			.filter( ( m ) => m && m.id && ! have.has( m.id ) )
			.map( ( m ) => ( {
				id: m.id,
				url: m.sizes?.medium_large?.url ?? m.url,
				alt: m.alt ?? '',
			} ) );
		setAttributes( { images: [ ...images, ...added ].slice( 0, MAX_IMAGES ) } );
	};
	const removeImage = ( idx ) =>
		setAttributes( { images: images.filter( ( _, i ) => i !== idx ) } );
	const moveImage = ( idx, dir ) =>
		setAttributes( { images: moveItem( images, idx, dir ) } );
	const setAlt = ( idx, alt ) =>
		setAttributes( { images: images.map( ( img, i ) => ( i === idx ? { ...img, alt } : img ) ) } );
	const setField = ( idx, key, value ) =>
		setAttributes( { images: images.map( ( img, i ) => ( i === idx ? { ...img, [ key ]: value } : img ) ) } );

	// Canvas columns — round-robin split, same as render.php.
	const colA = [];
	const colB = [];
	images.forEach( ( img, i ) => ( i % 2 === 0 ? colA : colB ).push( { img, idx: i } ) );
	const placeholders = Array.from( { length: 4 } );

	const renderTile = ( key, entry ) => (
		<div
			className={ 'mg-tile' + ( entry?.img?.link ? ' mg-tile--link' : '' ) }
			key={ key }
			style={
				tileShape === 'masonry' && entry
					? { '--mg-ratio': snapRatio( mediaRecords[ entry.idx ]?.media_details?.width, mediaRecords[ entry.idx ]?.media_details?.height ) }
					: undefined
			}
		>
			{ entry && <img className="mg-img" src={ urlFor( entry.img, entry.idx ) } alt={ entry.img.alt || '' } /> }
			{ entry?.img?.link && (
				<span className="mg-tile-label">
					{ entry.img.name && <span className="mg-tile-name">{ entry.img.name }</span> }
					<svg className="mg-tile-arrow" width="20" height="20" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
				</span>
			) }
		</div>
	);

	return (
		<>
			<InspectorControls>

				{ /* ── Section Settings ── */ }
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Warm White (default)', 'cropx' ), value: 'taupe' },
							{ label: __( 'Deep Blue + Topo',    'cropx' ), value: 'deep-blue' },
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
						checked={ showIcon === true }
						onChange={ ( v ) => setAttributes( { showIcon: v } ) }
					/>
					{ showIcon === true && (
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

				{ /* ── Photos ── */ }
				<PanelBody title={ __( 'Gallery Photos', 'cropx' ) } initialOpen={ true }>
					<p className="mg-panel-hint">
						{ __( 'Photos are split across the two columns in order (1st left, 2nd right, 3rd left…). 6–12 photos works best; up to 16 are supported.', 'cropx' ) }
					</p>
					{ images.map( ( img, idx ) => (
						<div className="mg-panel-item" key={ `${ img.id }-${ idx }` }>
							<div className="mg-panel-row">
								<div className="mg-panel-thumb">
									<img src={ urlFor( img, idx ) } alt="" />
								</div>
								<div className="mg-panel-controls">
									<div className="mg-panel-reorder">
										<button type="button" disabled={ idx === 0 } onClick={ () => moveImage( idx, 'up' ) } aria-label={ __( 'Move up', 'cropx' ) }>↑</button>
										<span>{ idx + 1 } / { images.length }</span>
										<button type="button" disabled={ idx === images.length - 1 } onClick={ () => moveImage( idx, 'down' ) } aria-label={ __( 'Move down', 'cropx' ) }>↓</button>
									</div>
									<button type="button" className="mg-panel-remove" onClick={ () => removeImage( idx ) } aria-label={ __( 'Remove photo', 'cropx' ) }>✕</button>
								</div>
							</div>
							<TextControl
								value={ img.alt ?? '' }
								placeholder={ __( 'Alt text (describe the photo)…', 'cropx' ) }
								onChange={ ( v ) => setAlt( idx, v ) }
								__nextHasNoMarginBottom
							/>
							<TextControl
								label={ __( 'Product name (shown on hover)', 'cropx' ) }
								value={ img.name || '' }
								onChange={ ( v ) => setField( idx, 'name', v ) }
								__nextHasNoMarginBottom
							/>
							<TextControl
								label={ __( 'Link to product page', 'cropx' ) }
								help={ __( 'Optional. When set, the photo becomes a link and shows the name + arrow on hover.', 'cropx' ) }
								value={ img.link || '' }
								placeholder="/products/…"
								onChange={ ( v ) => setField( idx, 'link', v ) }
								__nextHasNoMarginBottom
							/>
						</div>
					) ) }
					{ images.length < MAX_IMAGES && (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ addImages }
								allowedTypes={ [ 'image' ] }
								multiple
								gallery
								value={ images.map( ( i ) => i.id ) }
								render={ ( { open } ) => (
									<Button variant="secondary" className="mg-panel-add-btn" onClick={ open }>
										{ images.length === 0 ? __( '+ Add photos', 'cropx' ) : __( '+ Add more photos', 'cropx' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					) }
					{ images.length >= MAX_IMAGES && (
						<p className="mg-panel-hint">{ __( 'Maximum of 16 photos reached.', 'cropx' ) }</p>
					) }
				</PanelBody>

				{ /* ── Gallery Layout & Motion ── */ }
				<PanelBody title={ __( 'Gallery Layout & Motion', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Gallery position (desktop)', 'cropx' ) }
						help={ __( 'On smaller tablets and mobile the gallery always sits below the text.', 'cropx' ) }
						value={ galleryPosition }
						options={ [
							{ label: __( 'Right of text (default)', 'cropx' ), value: 'right' },
							{ label: __( 'Left of text',            'cropx' ), value: 'left'  },
						] }
						onChange={ ( v ) => setAttributes( { galleryPosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Photo shape', 'cropx' ) }
						help={ __( 'Masonry mixes landscape, square and portrait tiles based on each photo. Otherwise all photos are cropped to one shape.', 'cropx' ) }
						value={ tileShape }
						options={ [
							{ label: __( 'Portrait (4:5)',  'cropx' ), value: 'portrait'  },
							{ label: __( 'Square (1:1)',    'cropx' ), value: 'square'    },
							{ label: __( 'Landscape (4:3)', 'cropx' ), value: 'landscape' },
							{ label: __( 'Masonry (mixed, follows each photo)', 'cropx' ), value: 'masonry' },
						] }
						onChange={ ( v ) => setAttributes( { tileShape: v } ) }
					/>
					<SelectControl
						label={ __( 'Scroll speed — desktop (vertical)', 'cropx' ) }
						value={ speed }
						options={ [
							{ label: __( 'Very slow',   'cropx' ), value: 'very-slow'   },
							{ label: __( 'Slow',        'cropx' ), value: 'slow'        },
							{ label: __( 'Medium-slow', 'cropx' ), value: 'medium-slow' },
							{ label: __( 'Medium',      'cropx' ), value: 'medium'      },
							{ label: __( 'Medium-fast', 'cropx' ), value: 'medium-fast' },
							{ label: __( 'Fast',        'cropx' ), value: 'fast'        },
							{ label: __( 'Very fast',   'cropx' ), value: 'very-fast'   },
						] }
						onChange={ ( v ) => setAttributes( { speed: v } ) }
					/>
					<SelectControl
						label={ __( 'Scroll speed — mobile / tablet (horizontal)', 'cropx' ) }
						value={ speedMobile }
						options={ [
							{ label: __( 'Same as desktop', 'cropx' ), value: 'same' },
							...[
							{ label: __( 'Very slow',   'cropx' ), value: 'very-slow'   },
							{ label: __( 'Slow',        'cropx' ), value: 'slow'        },
							{ label: __( 'Medium-slow', 'cropx' ), value: 'medium-slow' },
							{ label: __( 'Medium',      'cropx' ), value: 'medium'      },
							{ label: __( 'Medium-fast', 'cropx' ), value: 'medium-fast' },
							{ label: __( 'Fast',        'cropx' ), value: 'fast'        },
							{ label: __( 'Very fast',   'cropx' ), value: 'very-fast'   },

							],
						] }
						onChange={ ( v ) => setAttributes( { speedMobile: v } ) }
					/>
					<SelectControl
						label={ __( 'Left column direction (desktop)', 'cropx' ) }
						help={ __( 'The right column always scrolls the opposite way.', 'cropx' ) }
						value={ scrollDirection }
						options={ [
							{ label: __( 'Up',   'cropx' ), value: 'up'   },
							{ label: __( 'Down', 'cropx' ), value: 'down' },
						] }
						onChange={ ( v ) => setAttributes( { scrollDirection: v } ) }
					/>
					<RangeControl
						label={ __( 'Minimum block height on desktop (px)', 'cropx' ) }
						help={ __( 'On desktop the gallery fills the full height of the block, edge to edge. The text sets the height; this only matters if the text is short.', 'cropx' ) }
						value={ galleryHeight }
						min={ 360 }
						max={ 800 }
						step={ 20 }
						onChange={ ( v ) => setAttributes( { galleryHeight: v ?? 560 } ) }
					/>
					<ToggleControl
						label={ __( 'Let visitors drag / swipe to scroll', 'cropx' ) }
						help={ __( 'Each column can be dragged up or down on its own, and the mobile row dragged sideways. The auto-scroll keeps going afterward.', 'cropx' ) }
						checked={ allowDrag !== false }
						onChange={ ( v ) => setAttributes( { allowDrag: v } ) }
					/>
					<ToggleControl
						label={ __( 'Pause on hover / keyboard focus', 'cropx' ) }
						checked={ pauseOnHover !== false }
						onChange={ ( v ) => setAttributes( { pauseOnHover: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show pause / play button', 'cropx' ) }
						help={ __( 'Recommended — lets visitors stop the motion (accessibility).', 'cropx' ) }
						checked={ showPauseButton !== false }
						onChange={ ( v ) => setAttributes( { showPauseButton: v } ) }
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
				<div className="mg-inner">
					<div className="mg-grid">

						{ /* Text column */ }
						<div className="mg-content">
							{ showIcon === true && (
								<div className="mg-icon-wrap">
									<div className="mg-icon" aria-hidden="true">
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
									? <span className="mg-link" aria-hidden="true">
											{ ctaLabel }
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
										</span>
									: <span className="mg-cta mg-cta-preview" aria-hidden="true">{ ctaLabel }</span>
							) }
						</div>

						{ /* Gallery column — static preview (the front end scrolls) */ }
						<div className="mg-gallery">

							<div className="mg-viewport mg-viewport--cols">
								<div className="mg-cols">
									<div className="mg-col mg-col--a">
										<div className="mg-track mg-track--y"><div className="mg-set">
											{ images.length
												? colA.map( ( e ) => renderTile( `a-${ e.idx }`, e ) )
												: placeholders.map( ( _, i ) => renderTile( `pa-${ i }`, null ) ) }
										</div></div>
									</div>
									<div className="mg-col mg-col--b">
										<div className="mg-track mg-track--y"><div className="mg-set">
											{ images.length
												? ( colB.length ? colB : colA ).map( ( e ) => renderTile( `b-${ e.idx }`, e ) )
												: placeholders.map( ( _, i ) => renderTile( `pb-${ i }`, null ) ) }
										</div></div>
									</div>
								</div>
							</div>

							<div className="mg-viewport mg-viewport--row">
								<div className="mg-rowline">
									<div className="mg-track mg-track--x"><div className="mg-set">
										{ images.length
											? images.map( ( img, idx ) => renderTile( `r-${ idx }`, { img, idx } ) )
											: placeholders.map( ( _, i ) => renderTile( `pr-${ i }`, null ) ) }
									</div></div>
								</div>
							</div>

							{ images.length === 0 && (
								<p className="mg-empty-note">
									{ __( 'Add photos in the sidebar → Gallery Photos. The gallery scrolls on the published page.', 'cropx' ) }
								</p>
							) }
						</div>

					</div>
				</div>
			</section>
		</>
	);
}
