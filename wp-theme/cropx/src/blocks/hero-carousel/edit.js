import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';
import './editor.css';

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

// Order = widest to narrowest. Breakpoints here must match render.php's
// <source media> list and style.css's aspect-ratio rules.
const VARIANTS = [
	{ key: 'wide',    label: __( 'Extra-wide (21:9)', 'cropx' ), hint: __( 'Screens 1920px and wider', 'cropx' ),  media: '(min-width: 1920px)' },
	{ key: 'desktop', label: __( 'Desktop (16:9)', 'cropx' ),    hint: __( 'Screens 1025–1919px', 'cropx' ),       media: '(min-width: 1025px)' },
	{ key: 'tablet',  label: __( 'Tablet (4:3)', 'cropx' ),      hint: __( 'Screens 601–1024px', 'cropx' ),        media: '(min-width: 601px)' },
	{ key: 'mobile',  label: __( 'Mobile (9:16)', 'cropx' ),     hint: __( 'Screens 600px and narrower', 'cropx' ), media: null },
];

// Nearest-available fallback order per target size (mirrors render.php).
const FALLBACK = {
	wide:    [ 'wide', 'desktop', 'tablet', 'mobile' ],
	desktop: [ 'desktop', 'wide', 'tablet', 'mobile' ],
	tablet:  [ 'tablet', 'desktop', 'mobile', 'wide' ],
	mobile:  [ 'mobile', 'tablet', 'desktop', 'wide' ],
};

const BLANK_SLIDE = {
	eyebrow: '', heading: '', subheading: '',
	ctaLabel: '', ctaUrl: '#', ctaLinkType: 'url', ctaFileId: 0, ctaFileUrl: '',
	showCta2: false, cta2Label: '', cta2Url: '#', cta2LinkType: 'url', cta2FileId: 0, cta2FileUrl: '',
	wideId: 0, wideUrl: '', desktopId: 0, desktopUrl: '',
	tabletId: 0, tabletUrl: '', mobileId: 0, mobileUrl: '',
	focalX: 0.5, focalY: 0.5,
};

function MediaField( { label, hint, imageId, imageUrl, onSelect, onRemove } ) {
	return (
		<div style={ { marginBottom: '16px' } }>
			<div style={ { fontWeight: 600, fontSize: '12px', marginBottom: '2px' } }>{ label }</div>
			{ hint && (
				<div style={ { fontSize: '11px', color: '#757575', marginBottom: '6px' } }>{ hint }</div>
			) }
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onSelect }
					allowedTypes={ [ 'image' ] }
					value={ imageId }
					render={ ( { open } ) => (
						<div style={ { display: 'flex', flexDirection: 'column', gap: '6px' } }>
							{ imageUrl && (
								<img
									src={ imageUrl }
									alt=""
									style={ { maxWidth: '100%', maxHeight: '110px', objectFit: 'contain', borderRadius: '2px', background: '#f0f0f0' } }
								/>
							) }
							<Button onClick={ open } variant="secondary" size="small">
								{ imageUrl ? __( 'Replace image', 'cropx' ) : __( 'Select image', 'cropx' ) }
							</Button>
							{ imageUrl && (
								<Button onClick={ onRemove } variant="link" isDestructive size="small">
									{ __( 'Remove image', 'cropx' ) }
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		segment, swoopFill, showEyebrow, showCta, textWidth,
		enableCarousel, enableVariants, autoAdvance, intervalSec, showDots, sharedText,
	} = attributes;

	const slides = [ 0, 1, 2 ].map( ( i ) => ( { ...BLANK_SLIDE, ...( attributes.slides?.[ i ] || {} ) } ) );

	const [ activeSet, setActiveSetState ] = useState( 0 );
	const activeIdx = enableCarousel ? Math.min( activeSet, 2 ) : 0;

	const updateSlide = ( i, patch ) => {
		const next = slides.map( ( s, idx ) => ( idx === i ? { ...s, ...patch } : s ) );
		setAttributes( { slides: next } );
	};

	// Resolve every attachment fresh from its ID (stored *Url is only a
	// snapshot that goes stale if the site's domain changes — same pattern as
	// the other hero blocks). Falls back to the stored URL while the lookup is
	// in flight or if the attachment was deleted.
	const idList = [];
	slides.forEach( ( s ) => VARIANTS.forEach( ( v ) => { if ( s[ v.key + 'Id' ] ) { idList.push( s[ v.key + 'Id' ] ); } } ) );
	const idsKey = idList.join( ',' );
	const resolvedById = useSelect(
		( select ) => {
			const core = select( 'core' );
			const map = {};
			idList.forEach( ( id ) => {
				const rec = core.getEntityRecord( 'root', 'media', id );
				if ( rec?.source_url ) { map[ id ] = rec.source_url; }
			} );
			return map;
		},
		[ idsKey ]
	);
	const urlFor = ( s, key ) => {
		const id = s[ key + 'Id' ];
		return ( id && resolvedById[ id ] ) || s[ key + 'Url' ] || '';
	};

	const blockProps = useBlockProps( {
		className: `hcr-block hcr-segment-${ segment } ${ enableVariants ? 'hcr--variants' : 'hcr--single' }`,
	} );

	const set = slides[ activeIdx ];
	const textIdx = sharedText ? 0 : activeIdx;
	const textSlide = slides[ textIdx ];

	// Canvas image. <picture> is evaluated against the editor iframe's width,
	// so the editor's Desktop / Tablet / Mobile preview switcher picks the
	// matching variant exactly as the front end does.
	const pick = ( order ) => {
		for ( const k of order ) {
			const u = urlFor( set, k );
			if ( u ) { return u; }
		}
		return '';
	};
	const fallbackUrl = enableVariants ? pick( FALLBACK.mobile ) : pick( FALLBACK.desktop );
	const focalStyle = {
		objectPosition: `${ Math.round( ( set.focalX ?? 0.5 ) * 100 ) }% ${ Math.round( ( set.focalY ?? 0.5 ) * 100 ) }%`,
	};

	const sharedNote = sharedText && activeIdx !== 0;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Carousel & Variants', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Carousel', 'cropx' ) }
						help={ enableCarousel
							? __( 'Cycles through the image sets that have an image.', 'cropx' )
							: __( 'Off: only Set 1 is shown, with no controls.', 'cropx' ) }
						checked={ enableCarousel !== false }
						onChange={ ( v ) => { setAttributes( { enableCarousel: v } ); if ( ! v ) { setActiveSetState( 0 ); } } }
					/>
					{ enableCarousel !== false && (
						<>
							<ToggleControl
								label={ __( 'Auto-advance', 'cropx' ) }
								checked={ autoAdvance !== false }
								onChange={ ( v ) => setAttributes( { autoAdvance: v } ) }
							/>
							{ autoAdvance !== false && (
								<RangeControl
									label={ __( 'Seconds per set', 'cropx' ) }
									value={ intervalSec ?? 6 }
									onChange={ ( v ) => setAttributes( { intervalSec: v } ) }
									min={ 3 }
									max={ 20 }
								/>
							) }
							<ToggleControl
								label={ __( 'Show dots', 'cropx' ) }
								checked={ showDots !== false }
								onChange={ ( v ) => setAttributes( { showDots: v } ) }
							/>
							<ToggleControl
								label={ __( 'Same text on every set', 'cropx' ) }
								help={ __( 'Uses Set 1’s eyebrow, headline, subheadline and buttons over all sets, and only the images change.', 'cropx' ) }
								checked={ sharedText === true }
								onChange={ ( v ) => setAttributes( { sharedText: v } ) }
							/>
						</>
					) }
					<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />
					<ToggleControl
						label={ __( 'Responsive image variants', 'cropx' ) }
						help={ enableVariants
							? __( 'Each set has 4 images (21:9, 16:9, 4:3, 9:16). The hero grows to show the whole image at every width.', 'cropx' )
							: __( 'Off: each set uses ONE image at every size, cropped to fit using its focal point.', 'cropx' ) }
						checked={ enableVariants !== false }
						onChange={ ( v ) => setAttributes( { enableVariants: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ false }>
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
						label={ __( 'Segment variant', 'cropx' ) }
						help={ __( 'Controls the accent color and swoop stroke gradient.', 'cropx' ) }
						value={ segment }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segment: v } ) }
					/>
					<SelectControl
						label={ __( 'Swoop fill colour', 'cropx' ) }
						help={ __( 'Colour of the curved corner at the hero bottom-right. Auto reads the next section\'s background.', 'cropx' ) }
						value={ swoopFill ?? '' }
						options={ SWOOP_FILL_OPTIONS }
						onChange={ ( v ) => setAttributes( { swoopFill: v } ) }
					/>
					<RangeControl
						label={ __( 'Headline & subheadline width (%)', 'cropx' ) }
						help={ __( 'Only narrow this if text runs into a busy part of the photo.', 'cropx' ) }
						value={ textWidth ?? 100 }
						onChange={ ( v ) => setAttributes( { textWidth: v } ) }
						min={ 40 }
						max={ 100 }
						step={ 5 }
					/>
				</PanelBody>

				{ ( enableCarousel !== false ? [ 0, 1, 2 ] : [ 0 ] ).map( ( i ) => {
					const s = slides[ i ];
					const textShared = sharedText && i !== 0;
					return (
						<PanelBody
							key={ i }
							title={ enableCarousel !== false ? __( 'Set', 'cropx' ) + ' ' + ( i + 1 ) : __( 'Image & buttons', 'cropx' ) }
							opened={ activeIdx === i }
							onToggle={ () => setActiveSetState( i ) }
						>
							{ enableVariants !== false ? (
								VARIANTS.map( ( v ) => (
									<MediaField
										key={ v.key }
										label={ v.label }
										hint={ v.hint }
										imageId={ s[ v.key + 'Id' ] }
										imageUrl={ urlFor( s, v.key ) }
										onSelect={ ( media ) => updateSlide( i, { [ v.key + 'Id' ]: media.id, [ v.key + 'Url' ]: media.url } ) }
										onRemove={ () => updateSlide( i, { [ v.key + 'Id' ]: 0, [ v.key + 'Url' ]: '' } ) }
									/>
								) )
							) : (
								<MediaField
									label={ __( 'Image', 'cropx' ) }
									hint={ __( 'Used at every screen size.', 'cropx' ) }
									imageId={ s.desktopId }
									imageUrl={ urlFor( s, 'desktop' ) }
									onSelect={ ( media ) => updateSlide( i, { desktopId: media.id, desktopUrl: media.url } ) }
									onRemove={ () => updateSlide( i, { desktopId: 0, desktopUrl: '' } ) }
								/>
							) }

							<RangeControl
								label={ __( 'Focal point X (%)', 'cropx' ) }
								help={ enableVariants !== false
									? __( 'Only matters when an image has to be cropped (e.g. a missing variant, or text taller than the image).', 'cropx' )
									: __( 'Which part of the image stays in view when it is cropped.', 'cropx' ) }
								value={ Math.round( ( s.focalX ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => updateSlide( i, { focalX: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>
							<RangeControl
								label={ __( 'Focal point Y (%)', 'cropx' ) }
								value={ Math.round( ( s.focalY ?? 0.5 ) * 100 ) }
								onChange={ ( v ) => updateSlide( i, { focalY: v / 100 } ) }
								min={ 0 }
								max={ 100 }
							/>

							<hr style={ { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } } />

							{ textShared ? (
								<p style={ { fontSize: '12px', color: '#757575', margin: 0 } }>
									{ __( 'This set uses Set 1’s text and buttons (“Same text on every set” is on).', 'cropx' ) }
								</p>
							) : (
								<>
									<TextControl
										label={ __( 'Primary button label', 'cropx' ) }
										value={ s.ctaLabel }
										onChange={ ( v ) => updateSlide( i, { ctaLabel: v } ) }
									/>
									<CtaLinkControl
										label={ __( 'Primary button link', 'cropx' ) }
										linkType={ s.ctaLinkType }
										onLinkTypeChange={ ( v ) => updateSlide( i, { ctaLinkType: v } ) }
										url={ s.ctaUrl }
										onUrlChange={ ( v ) => updateSlide( i, { ctaUrl: v } ) }
										fileId={ s.ctaFileId }
										fileUrl={ s.ctaFileUrl }
										onFileSelect={ ( media ) => updateSlide( i, { ctaFileId: media.id, ctaFileUrl: media.url } ) }
										onFileRemove={ () => updateSlide( i, { ctaFileId: 0, ctaFileUrl: '', ctaLinkType: 'url' } ) }
									/>
									<ToggleControl
										label={ __( 'Show secondary CTA', 'cropx' ) }
										checked={ s.showCta2 === true }
										onChange={ ( v ) => updateSlide( i, { showCta2: v } ) }
									/>
									{ s.showCta2 && (
										<>
											<TextControl
												label={ __( 'Secondary button label', 'cropx' ) }
												value={ s.cta2Label }
												onChange={ ( v ) => updateSlide( i, { cta2Label: v } ) }
											/>
											<CtaLinkControl
												label={ __( 'Secondary button link', 'cropx' ) }
												linkType={ s.cta2LinkType }
												onLinkTypeChange={ ( v ) => updateSlide( i, { cta2LinkType: v } ) }
												url={ s.cta2Url }
												onUrlChange={ ( v ) => updateSlide( i, { cta2Url: v } ) }
												fileId={ s.cta2FileId }
												fileUrl={ s.cta2FileUrl }
												onFileSelect={ ( media ) => updateSlide( i, { cta2FileId: media.id, cta2FileUrl: media.url } ) }
												onFileRemove={ () => updateSlide( i, { cta2FileId: 0, cta2FileUrl: '', cta2LinkType: 'url' } ) }
											/>
										</>
									) }
								</>
							) }
						</PanelBody>
					);
				} ) }
			</InspectorControls>

			<div { ...blockProps }>
				<div className="hcr-bleed-wrap">
					<section className="hcr-hero">
						{ /* Editor-only set switcher. The sidebar's open Set panel and this
							 strip stay in sync. Not part of render.php. */ }
						{ enableCarousel !== false && (
							<div className="hcr-editor-tabs">
								{ [ 0, 1, 2 ].map( ( i ) => (
									<button
										key={ i }
										type="button"
										className={ 'hcr-editor-tab' + ( activeIdx === i ? ' is-active' : '' ) }
										onClick={ () => setActiveSetState( i ) }
									>
										{ __( 'Set', 'cropx' ) } { i + 1 }
									</button>
								) ) }
							</div>
						) }

						<div className="hcr-media">
							<div className="hcr-slide is-active">
								{ fallbackUrl ? (
									<picture>
										{ enableVariants !== false && VARIANTS.filter( ( v ) => v.media ).map( ( v ) => {
											const u = pick( FALLBACK[ v.key ] );
											return u ? <source key={ v.key } media={ v.media } srcSet={ u } /> : null;
										} ) }
										<img src={ fallbackUrl } alt="" style={ focalStyle } />
									</picture>
								) : (
									<div className="hcr-editor-empty">
										{ __( 'Select an image for this set in the sidebar.', 'cropx' ) }
									</div>
								) }
							</div>
						</div>

						<div className="hcr-overlay" />

						<div
							className="hcr-content is-active"
							style={ {
								'--hcr-headline-w': ( textWidth ?? 100 ) / 100,
								'--hcr-subhead-w':  ( textWidth ?? 100 ) / 100,
							} }
						>
							{ sharedNote && (
								<p className="hcr-editor-note">
									{ __( 'Text is shared — editing Set 1’s text.', 'cropx' ) }
								</p>
							) }
							{ showEyebrow !== false && (
								<RichText
									tagName="p"
									className="hcr-eyebrow"
									placeholder={ __( 'Eyebrow text…', 'cropx' ) }
									value={ textSlide.eyebrow }
									onChange={ ( v ) => updateSlide( textIdx, { eyebrow: v } ) }
									allowedFormats={ [] }
								/>
							) }
							<RichText
								tagName="h1"
								className="hcr-headline"
								placeholder={ __( 'Hero headline — use Italic for the emphasis underline…', 'cropx' ) }
								value={ textSlide.heading }
								onChange={ ( v ) => updateSlide( textIdx, { heading: v } ) }
								allowedFormats={ [ 'core/italic', 'core/bold' ] }
							/>
							<RichText
								tagName="p"
								className="hcr-subheadline"
								placeholder={ __( 'Subheading or supporting text…', 'cropx' ) }
								value={ textSlide.subheading }
								onChange={ ( v ) => updateSlide( textIdx, { subheading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
							/>
							{ showCta !== false && textSlide.ctaLabel && (
								<div className="hcr-cta-row">
									<span className="hcr-cta" aria-hidden="true">{ textSlide.ctaLabel }</span>
									{ textSlide.showCta2 && textSlide.cta2Label && (
										<span className="hcr-cta--ghost" aria-hidden="true">
											{ textSlide.cta2Label }
											{ textSlide.cta2LinkType === 'file' ? (
												<svg className="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" /><polyline points="7 10 12 15 17 10" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" /><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" /></svg>
											) : (
												<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" /></svg>
											) }
										</span>
									) }
								</div>
							) }
						</div>

						{ /* Static swoop (no gradient animation in the editor) */ }
						<div className="hcr-swoop" aria-hidden="true">
							<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
								<path className="hcr-fill-path" d="M0,80 Q720,80 1440,30 L1440,80 L0,80 Z" />
								<path d="M0,70 Q720,70 1440,20" fill="none" stroke="var(--hcr-accent, #0ca8c0)" strokeWidth="20" strokeLinecap="round" />
							</svg>
						</div>
					</section>
				</div>
			</div>
		</>
	);
}
