/**
 * CropX editor sidebar panels.
 *
 * Registers PluginDocumentSettingPanel components for CPTs that have custom
 * meta fields, so those fields appear in the right-hand Document sidebar
 * (alongside Featured Image, Status, etc.) instead of being buried at the
 * bottom of the page in the legacy meta box accordion.
 *
 * Covered post types:
 *   cropx_team_member — Job Title
 *   cropx_dealer      — Dealer Details (website, phone, email, address, region, image type)
 *   cropx_testimonial — Field guide (informational, no data entry — all fields are native WP)
 *   post              — Table of Contents toggle (cropx_hide_toc)
 *   cropx_publication — At a Glance toggle (cropx_hide_at_a_glance)
 */

import { registerPlugin }                          from '@wordpress/plugins';
import { PluginDocumentSettingPanel }              from '@wordpress/edit-post';
import { TextControl, RadioControl, ToggleControl } from '@wordpress/components';
import { useEntityProp }                           from '@wordpress/core-data';
import { useSelect }                               from '@wordpress/data';
import { __ }                                      from '@wordpress/i18n';

// ─────────────────────────────────────────────────────────────────────────────
// Shared hook: returns the current post type so each panel can bail early
// if it's on the wrong CPT screen.
// ─────────────────────────────────────────────────────────────────────────────
function usePostType() {
	return useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
}

// ─────────────────────────────────────────────────────────────────────────────
// Team Member — Job Title
// ─────────────────────────────────────────────────────────────────────────────
function TeamMemberPanels() {
	const postType              = usePostType();
	const [ meta, setMeta ]     = useEntityProp( 'postType', 'cropx_team_member', 'meta' );

	if ( postType !== 'cropx_team_member' ) return null;

	return (
		<PluginDocumentSettingPanel
			name="cropx-job-title"
			title={ __( 'Job Title', 'cropx' ) }
		>
			<TextControl
				label={ __( 'Job title', 'cropx' ) }
				value={ meta?.job_title ?? '' }
				onChange={ ( value ) => setMeta( { ...meta, job_title: value } ) }
				placeholder={ __( 'e.g. VP of Marketing', 'cropx' ) }
				help={ __( 'Displayed below the name on the Team page. Optional.', 'cropx' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'cropx-team-member-panels', { render: TeamMemberPanels, icon: null } );

// ─────────────────────────────────────────────────────────────────────────────
// Dealer — Dealer Details
// ─────────────────────────────────────────────────────────────────────────────
function DealerPanels() {
	const postType          = usePostType();
	const [ meta, setMeta ] = useEntityProp( 'postType', 'cropx_dealer', 'meta' );

	if ( postType !== 'cropx_dealer' ) return null;

	const update = ( key, value ) => setMeta( { ...meta, [ key ]: value } );

	return (
		<PluginDocumentSettingPanel
			name="cropx-dealer-details"
			title={ __( 'Dealer Details', 'cropx' ) }
		>
			<TextControl
				label={ __( 'Region', 'cropx' ) }
				value={ meta?.dealer_region ?? '' }
				onChange={ ( v ) => update( 'dealer_region', v ) }
				placeholder={ __( 'e.g. North America, Europe', 'cropx' ) }
				help={ __( 'Required — used to organise the dealer directory.', 'cropx' ) }
			/>
			<TextControl
				label={ __( 'Website', 'cropx' ) }
				value={ meta?.dealer_website ?? '' }
				onChange={ ( v ) => update( 'dealer_website', v ) }
				placeholder="https://example.com"
				type="url"
			/>
			<TextControl
				label={ __( 'Phone', 'cropx' ) }
				value={ meta?.dealer_phone ?? '' }
				onChange={ ( v ) => update( 'dealer_phone', v ) }
				placeholder="+1 (555) 000-0000"
			/>
			<TextControl
				label={ __( 'Email', 'cropx' ) }
				value={ meta?.dealer_email ?? '' }
				onChange={ ( v ) => update( 'dealer_email', v ) }
				placeholder="dealer@example.com"
				type="email"
			/>
			<TextControl
				label={ __( 'Address', 'cropx' ) }
				value={ meta?.dealer_address ?? '' }
				onChange={ ( v ) => update( 'dealer_address', v ) }
				placeholder="123 Main St, City, State"
			/>
			<RadioControl
				label={ __( 'Featured image is a…', 'cropx' ) }
				selected={ meta?.dealer_image_type ?? 'logo' }
				options={ [
					{ label: __( 'Company logo',    'cropx' ), value: 'logo'     },
					{ label: __( 'Dealer headshot', 'cropx' ), value: 'headshot' },
				] }
				onChange={ ( v ) => update( 'dealer_image_type', v ) }
				help={ __( 'Optional — leave the Featured Image blank if not applicable.', 'cropx' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'cropx-dealer-panels', { render: DealerPanels, icon: null } );

// ─────────────────────────────────────────────────────────────────────────────
// Testimonial — field guide
// All testimonial content uses native WP fields; this panel just tells editors
// what to put where so they don't have to guess.
// ─────────────────────────────────────────────────────────────────────────────
function TestimonialPanels() {
	const postType = usePostType();
	if ( postType !== 'cropx_testimonial' ) return null;

	return (
		<PluginDocumentSettingPanel
			name="cropx-testimonial-guide"
			title={ __( 'Field Guide', 'cropx' ) }
		>
			<ul style={ { margin: 0, padding: '0 0 0 1.25rem', fontSize: '12px', lineHeight: '1.8' } }>
				<li><strong>{ __( 'Title', 'cropx' ) }</strong> — { __( "Person's name", 'cropx' ) }</li>
				<li><strong>{ __( 'Body', 'cropx' ) }</strong> — { __( 'The quote', 'cropx' ) }</li>
				<li><strong>{ __( 'Excerpt', 'cropx' ) }</strong> — { __( 'Role and company (e.g. VP of Agriculture, Reinke)', 'cropx' ) }</li>
				<li><strong>{ __( 'Featured Image', 'cropx' ) }</strong> — { __( 'Headshot or company logo. Must be a perfect square, at least 250×250 px.', 'cropx' ) }</li>
			</ul>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'cropx-testimonial-panels', { render: TestimonialPanels, icon: null } );

// ─────────────────────────────────────────────────────────────────────────────
// Blog posts — Table of Contents toggle
//
// The right-side ToC on single.php is auto-generated from the post's own
// headings (see assets/js/blog-single.js) — there's nothing to configure
// beyond whether it shows at all. Defaults to visible (meta default: false)
// so no existing post changes behavior until an editor opts out. single.php
// reads this same meta key to decide whether to render the
// <aside class="bsingle-toc-sidebar"> markup at all.
// ─────────────────────────────────────────────────────────────────────────────
function BlogPostPanels() {
	const postType          = usePostType();
	const [ meta, setMeta ] = useEntityProp( 'postType', 'post', 'meta' );

	if ( postType !== 'post' ) return null;

	return (
		<PluginDocumentSettingPanel
			name="cropx-toc-toggle"
			title={ __( 'Table of Contents', 'cropx' ) }
		>
			<ToggleControl
				label={ __( 'Hide table of contents', 'cropx' ) }
				checked={ !! meta?.cropx_hide_toc }
				onChange={ ( value ) => setMeta( { ...meta, cropx_hide_toc: value } ) }
				help={ __( 'Table of contents is shown by default. Turn this on to remove it — the article column stays the same width, so this just leaves that space blank rather than widening the content.', 'cropx' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'cropx-blog-post-panels', { render: BlogPostPanels, icon: null } );

// ─────────────────────────────────────────────────────────────────────────────
// Results & Research — "At a Glance" sidebar toggle
//
// The case-study sidebar card (Company / Region / Scale / Challenge /
// Solution — cs_company etc., registered in inc/cpts.php) renders in the
// right column on single-cropx_publication.php whenever any of those fields
// are filled in. This toggle lets an editor hide that card even when the
// data exists, collapsing the layout to a single column — see
// single-cropx_publication.php and pub-single.css's existing
// .pub-body-layout--no-sidebar rule, which already handles the "nothing to
// show in the right column" case and is reused here.
// ─────────────────────────────────────────────────────────────────────────────
function PublicationAtAGlancePanels() {
	const postType          = usePostType();
	const [ meta, setMeta ] = useEntityProp( 'postType', 'cropx_publication', 'meta' );

	if ( postType !== 'cropx_publication' ) return null;

	return (
		<PluginDocumentSettingPanel
			name="cropx-at-a-glance-toggle"
			title={ __( 'At a Glance', 'cropx' ) }
		>
			<ToggleControl
				label={ __( 'Hide At a Glance card', 'cropx' ) }
				checked={ !! meta?.cropx_hide_at_a_glance }
				onChange={ ( value ) => setMeta( { ...meta, cropx_hide_at_a_glance: value } ) }
				help={ __( 'At a Glance is shown by default when those fields are filled in below. Turn this on to remove the card — the article column stays the same width, so this just leaves that space blank rather than widening the content.', 'cropx' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'cropx-publication-at-a-glance-panels', { render: PublicationAtAGlancePanels, icon: null } );

// ─────────────────────────────────────────────────────────────────────────────
// Block separator control
//
// Adds a "Separator" panel to the block sidebar for every cropx/* block,
// letting editors choose between a drop shadow (default), a 1px line,
// both, or no separator at all.
//
// The sectionSeparator attribute is registered globally in inc/block-shadow.php
// via register_block_type_args — no individual block.json changes needed.
// The PHP render_block filter injects data-separator="…" on non-default values,
// and the CSS override rules in tokens.css target that attribute.
// ─────────────────────────────────────────────────────────────────────────────
import { addFilter }                                          from '@wordpress/hooks';
import { InspectorControls }                                  from '@wordpress/block-editor';
import { PanelBody, SelectControl, Button }                  from '@wordpress/components';
import { createHigherOrderComponent }                         from '@wordpress/compose';
import { Fragment }                                           from '@wordpress/element';

const withSeparatorControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( ! props.name.startsWith( 'cropx/' ) ) {
			return <BlockEdit { ...props } />;
		}
		const { attributes, setAttributes } = props;
		const sectionSeparator = attributes.sectionSeparator ?? 'shadow';

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Separator', 'cropx' ) } initialOpen={ false }>
						<SelectControl
							label={ __( 'Style', 'cropx' ) }
							value={ sectionSeparator }
							options={ [
								{ label: __( 'Drop shadow (default)', 'cropx' ), value: 'shadow' },
								{ label: __( 'None',                  'cropx' ), value: 'none'   },
							] }
							onChange={ ( val ) => setAttributes( { sectionSeparator: val } ) }
							help={ __( 'Separator shown above this block when it follows another CropX block.', 'cropx' ) }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withSeparatorControl' );

addFilter( 'editor.BlockEdit', 'cropx/separator-control', withSeparatorControl );

// ─────────────────────────────────────────────────────────────────────────────
// Image block — per-corner radius control
//
// Every core/image gets CropX's signature asymmetric photo radius
// (--radius-photo, 60/2/60/2) by default via styles/content.css. This panel
// lets editors override it per image, corner by corner, via four plain text
// inputs (not WordPress's BorderRadiusControl — that component's export has
// proven unreliable across WP/Gutenberg versions and crashed this panel).
//
// The four cropxRadius* attributes are registered SERVER-SIDE ONLY, in
// inc/image-corner-radius.php via register_block_type_args — WordPress
// syncs server-registered attributes to the client at boot, so no
// client-side attribute schema is declared here (same mechanism the
// separator control above relies on for sectionSeparator).
//
// Values are empty strings until customized, meaning "inherit the sitewide
// default." The moment any corner changes, all four are written explicitly
// (see applyRadius below) so there's never a mix of "some custom, some
// inherited" to reason about. Two shortcuts cover the common cases: a
// preset button that (re)applies the on-brand photo radius explicitly, and
// a reset that clears the override back to "inherit."
//
// Editor live preview: editor.BlockListBlock sets the same
// --cropx-img-radius-* custom properties on the block wrapper that
// render_block_core/image sets on the front end, so the exact same
// content.css rule renders both — no separate preview styling to maintain.
// ─────────────────────────────────────────────────────────────────────────────

// Matches --radius-photo in tokens.css (60px 2px 60px 2px).
const PHOTO_RADIUS_PRESET = {
	topLeft:     '60px',
	topRight:    '2px',
	bottomRight: '60px',
	bottomLeft:  '2px',
};

const withImageCornerRadiusControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( 'core/image' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const {
			cropxRadiusTopLeft:     tl = '',
			cropxRadiusTopRight:    tr = '',
			cropxRadiusBottomRight: br = '',
			cropxRadiusBottomLeft:  bl = '',
		} = attributes;

		const isCustomized = !! ( tl || tr || br || bl );

		// Show the sitewide default in the control until something's been
		// customized, so the inputs start where the image visually already is.
		const values = {
			topLeft:     tl || PHOTO_RADIUS_PRESET.topLeft,
			topRight:    tr || PHOTO_RADIUS_PRESET.topRight,
			bottomRight: br || PHOTO_RADIUS_PRESET.bottomRight,
			bottomLeft:  bl || PHOTO_RADIUS_PRESET.bottomLeft,
		};

		// Always writes all four corners — once touched, an image's radius is
		// fully explicit rather than part-custom / part-inherited.
		const applyRadius = ( next ) => {
			setAttributes( {
				cropxRadiusTopLeft:     next.topLeft     ?? '',
				cropxRadiusTopRight:    next.topRight    ?? '',
				cropxRadiusBottomRight: next.bottomRight ?? '',
				cropxRadiusBottomLeft:  next.bottomLeft  ?? '',
			} );
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Corner Radius', 'cropx' ) } initialOpen={ false }>
						<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } }>
							<TextControl
								label={ __( 'Top left', 'cropx' ) }
								value={ values.topLeft }
								onChange={ ( v ) => applyRadius( { ...values, topLeft: v } ) }
							/>
							<TextControl
								label={ __( 'Top right', 'cropx' ) }
								value={ values.topRight }
								onChange={ ( v ) => applyRadius( { ...values, topRight: v } ) }
							/>
							<TextControl
								label={ __( 'Bottom left', 'cropx' ) }
								value={ values.bottomLeft }
								onChange={ ( v ) => applyRadius( { ...values, bottomLeft: v } ) }
							/>
							<TextControl
								label={ __( 'Bottom right', 'cropx' ) }
								value={ values.bottomRight }
								onChange={ ( v ) => applyRadius( { ...values, bottomRight: v } ) }
							/>
						</div>
						<p style={ { fontSize: '11px', color: '#757575', margin: '6px 0 0' } }>
							{ __( 'Enter any CSS length, e.g. 40px, 2px, 1rem, or 50%.', 'cropx' ) }
						</p>
						<div style={ { display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '12px' } }>
							<Button
								variant="secondary"
								size="small"
								onClick={ () => applyRadius( PHOTO_RADIUS_PRESET ) }
							>
								{ __( 'Use CropX Photo Radius', 'cropx' ) }
							</Button>
							{ isCustomized && (
								<Button
									variant="tertiary"
									size="small"
									onClick={ () => applyRadius( {} ) }
								>
									{ __( 'Reset to default', 'cropx' ) }
								</Button>
							) }
						</div>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withImageCornerRadiusControl' );

addFilter( 'editor.BlockEdit', 'cropx/image-corner-radius-control', withImageCornerRadiusControl );

// Mirrors the render_block_core/image PHP filter's CSS custom properties
// onto the block wrapper in the editor canvas, so the live preview matches
// the front end exactly via the same content.css rule.
const withImageCornerRadiusPreview = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		if ( 'core/image' !== props.name ) {
			return <BlockListBlock { ...props } />;
		}

		const {
			cropxRadiusTopLeft:     tl = '',
			cropxRadiusTopRight:    tr = '',
			cropxRadiusBottomRight: br = '',
			cropxRadiusBottomLeft:  bl = '',
		} = props.attributes;

		if ( ! tl && ! tr && ! br && ! bl ) {
			return <BlockListBlock { ...props } />;
		}

		const wrapperProps = {
			...props.wrapperProps,
			style: {
				...props.wrapperProps?.style,
				'--cropx-img-radius-tl': tl || '0',
				'--cropx-img-radius-tr': tr || '0',
				'--cropx-img-radius-br': br || '0',
				'--cropx-img-radius-bl': bl || '0',
			},
		};

		return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
	};
}, 'withImageCornerRadiusPreview' );

addFilter( 'editor.BlockListBlock', 'cropx/image-corner-radius-preview', withImageCornerRadiusPreview );

// ─────────────────────────────────────────────────────────────────────────────
// Image block — caption alignment toggle
//
// A plain on/off toggle so non-technical editors can left-align a caption
// (instead of the sitewide centered default) without ever touching the
// Advanced panel's "Additional CSS class(es)" field. Both paths land on the
// same `caption-align-left` class defined in styles/content.css — see
// inc/image-caption-align.php for the attribute registration and the PHP
// filter that actually adds the class on the front end.
//
// Disabled (and clearly explained why) until the image has a caption typed
// in, since there's nothing to align otherwise.
// ─────────────────────────────────────────────────────────────────────────────

const withImageCaptionAlignControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( 'core/image' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const hasCaption = !! ( attributes.caption && attributes.caption.length );
		const isLeft     = 'left' === attributes.cropxCaptionAlign;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Caption', 'cropx' ) } initialOpen={ false }>
						<ToggleControl
							label={ __( 'Left-align caption text', 'cropx' ) }
							checked={ isLeft }
							disabled={ ! hasCaption }
							onChange={ ( value ) => setAttributes( { cropxCaptionAlign: value ? 'left' : '' } ) }
							help={ hasCaption
								? __( 'Off keeps the caption centered under the image (sitewide default).', 'cropx' )
								: __( 'Add a caption below the image first — nothing to align yet.', 'cropx' ) }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withImageCaptionAlignControl' );

addFilter( 'editor.BlockEdit', 'cropx/image-caption-align-control', withImageCaptionAlignControl );

// Mirrors the render_block_core/image PHP filter's class onto the block
// wrapper in the editor canvas — content.css already loads in the editor
// iframe (see inc/enqueue.php), so adding the same class here is all that's
// needed for the canvas preview to match the front end exactly.
const withImageCaptionAlignPreview = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		if ( 'core/image' !== props.name || 'left' !== props.attributes.cropxCaptionAlign ) {
			return <BlockListBlock { ...props } />;
		}

		const wrapperProps = {
			...props.wrapperProps,
			className: [ props.wrapperProps?.className, 'caption-align-left' ]
				.filter( Boolean )
				.join( ' ' ),
		};

		return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
	};
}, 'withImageCaptionAlignPreview' );

addFilter( 'editor.BlockListBlock', 'cropx/image-caption-align-preview', withImageCaptionAlignPreview );
