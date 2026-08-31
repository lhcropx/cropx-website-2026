import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, RangeControl, TextareaControl } from '@wordpress/components';

import './editor.css';

// Placeholder rows shown on the canvas only — the real listings are fetched
// server-side from Workable at render time (see render.php +
// inc/workable-jobs-api.php), so there's nothing live to preview here.
// These mirror cropx_get_sample_workable_jobs() in shape, just enough to
// let the block's styling be judged before a Workable API key exists.
const PREVIEW_JOBS = [
	{ title: __( 'Backend Developer (Java / Python)', 'cropx' ), department: __( 'R&D', 'cropx' ), location: __( 'Plovdiv, Bulgaria', 'cropx' ) },
	{ title: __( 'Fullstack Software Engineer (Python/GIS)', 'cropx' ), department: __( 'R&D', 'cropx' ), location: __( 'Groningen, Netherlands', 'cropx' ) },
	{ title: __( 'Territory Sales Manager', 'cropx' ), department: __( 'Sales', 'cropx' ), location: __( 'United States', 'cropx' ) },
	{ title: __( 'Controller', 'cropx' ), department: __( 'Finance', 'cropx' ), location: __( 'Hod Hasharon, Israel', 'cropx' ) },
];

const ARROW = (
	<>
		{ ' ' }
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	</>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		heading,
		showHeading,
		headingAlign,
		bgColor = 'white',
		limit,
		emptyStateText,
	} = attributes;

	// PageSpeed fix (Aug 2026): real, cacheable drift-pattern URL instead of a
	// base64-inlined one — see render.php for the front-end half.
	const blockProps = useBlockProps( {
		className: `cjo-section cjo-section--bg-${ bgColor }`,
		style: bgColor === 'deep-blue'
			? { '--cjo-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	const rows = limit > 0 ? PREVIEW_JOBS.slice( 0, limit ) : PREVIEW_JOBS;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White (default)', 'cropx' ), value: 'white' },
							{ label: __( 'Taupe',            'cropx' ), value: 'taupe' },
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show heading', 'cropx' ) }
						checked={ showHeading !== false }
						onChange={ ( v ) => setAttributes( { showHeading: v } ) }
					/>
					{ showHeading !== false && (
						<SelectControl
							label={ __( 'Heading alignment', 'cropx' ) }
							value={ headingAlign ?? 'left' }
							options={ [
								{ label: __( 'Left (default)', 'cropx' ), value: 'left' },
								{ label: __( 'Center',          'cropx' ), value: 'center' },
							] }
							onChange={ ( v ) => setAttributes( { headingAlign: v } ) }
						/>
					) }
					<RangeControl
						label={ __( 'Limit', 'cropx' ) }
						help={ __( '0 shows every open role.', 'cropx' ) }
						value={ limit ?? 0 }
						onChange={ ( v ) => setAttributes( { limit: v } ) }
						min={ 0 }
						max={ 50 }
					/>
					<TextareaControl
						label={ __( 'Empty state message', 'cropx' ) }
						help={ __( 'Shown when Workable is connected but there are no open roles.', 'cropx' ) }
						value={ emptyStateText }
						onChange={ ( v ) => setAttributes( { emptyStateText: v } ) }
						rows={ 2 }
					/>
					<p className="cjo-sidebar-note">
						{ __( 'Live listings are fetched from Workable on the front end — connect your API key in Settings → CropX. The rows below are placeholder data so you can review the styling in the meantime.', 'cropx' ) }
					</p>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="cjo-inner">

					{ showHeading !== false && (
						<div className={ `cjo-header section-header${ ( headingAlign ?? 'left' ) === 'center' ? '' : ' section-header--left' }` }>
							<RichText
								tagName="h2"
								className="section-heading"
								placeholder={ __( 'Section heading…', 'cropx' ) }
								value={ heading }
								onChange={ ( v ) => setAttributes( { heading: v } ) }
								allowedFormats={ [ 'core/bold', 'core/italic' ] }
							/>
						</div>
					) }

					<div className="cjo-preview-badge">
						{ __( '⚠ Preview data — live jobs load on the front end once Workable is connected.', 'cropx' ) }
					</div>

					<div className="cjo-list">
						{ rows.map( ( job, i ) => (
							<div className="cjo-job cjo-job-preview" key={ i } aria-hidden="true">
								<div className="cjo-job-main">
									<span className="cjo-job-title">{ job.title }</span>
									<span className="cjo-job-meta">
										{ job.department }
										{ job.department && job.location && <span className="cjo-job-dot" aria-hidden="true">•</span> }
										{ job.location }
									</span>
								</div>
								<span className="cjo-job-arrow">{ ARROW }</span>
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
