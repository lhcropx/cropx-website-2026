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
 */

import { registerPlugin }              from '@wordpress/plugins';
import { PluginDocumentSettingPanel }  from '@wordpress/edit-post';
import { TextControl, RadioControl }   from '@wordpress/components';
import { useEntityProp }               from '@wordpress/core-data';
import { useSelect }                   from '@wordpress/data';
import { __ }                          from '@wordpress/i18n';

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
import { addFilter }                  from '@wordpress/hooks';
import { InspectorControls }          from '@wordpress/block-editor';
import { PanelBody, SelectControl }   from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment }                   from '@wordpress/element';

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
								{ label: __( '1px line',               'cropx' ), value: 'line'   },
								{ label: __( 'Shadow + line',           'cropx' ), value: 'both'   },
								{ label: __( 'None',                    'cropx' ), value: 'none'   },
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
