import { __ } from '@wordpress/i18n';
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
} from '@wordpress/components';

import './editor.css';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)', 'cropx' ),     value: 'general' },
	{ label: __( 'Enterprise (Gold)', 'cropx' ),        value: 'enterprise' },
	{ label: __( 'Service Provider (Terra)', 'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)', 'cropx' ),       value: 'on-farm' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		heading,
		subtext,
		primaryLabel,
		primaryUrl,
		secondaryLabel,
		secondaryUrl,
		backgroundImageId,
		backgroundImageUrl,
		segmentAccent,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `pf pf-segment-${ segmentAccent }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Background image', 'cropx' ) } initialOpen={ true }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									backgroundImageId:  media.id,
									backgroundImageUrl: media.url,
									backgroundImageAlt: media.alt || '',
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ backgroundImageId }
							render={ ( { open } ) => (
								<div style={ { display: 'flex', flexDirection: 'column', gap: '8px' } }>
									<Button onClick={ open } variant="primary">
										{ backgroundImageId
											? __( 'Replace image', 'cropx' )
											: __( 'Select image', 'cropx' ) }
									</Button>
									{ backgroundImageId > 0 && (
										<Button
											onClick={ () =>
												setAttributes( {
													backgroundImageId:  0,
													backgroundImageUrl: '',
													backgroundImageAlt: '',
												} )
											}
											variant="link"
											isDestructive
										>
											{ __( 'Remove image', 'cropx' ) }
										</Button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelBody title={ __( 'Call to action', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Primary button label', 'cropx' ) }
						value={ primaryLabel }
						onChange={ ( v ) => setAttributes( { primaryLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Primary button URL', 'cropx' ) }
						value={ primaryUrl }
						onChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Secondary button label', 'cropx' ) }
						value={ secondaryLabel }
						help={ __( 'Leave blank to hide the second button.', 'cropx' ) }
						onChange={ ( v ) => setAttributes( { secondaryLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Secondary button URL', 'cropx' ) }
						value={ secondaryUrl }
						onChange={ ( v ) => setAttributes( { secondaryUrl: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Segment accent', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Accent colour', 'cropx' ) }
						help={ __( 'Tints the top stripe, heading underline, and primary button edge.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div
					className="pf-bg"
					style={
						backgroundImageUrl
							? { backgroundImage: `url(${ backgroundImageUrl })` }
							: undefined
					}
				/>
				<div className="pf-overlay" />

				<div className="pf-inner">
					<RichText
						tagName="span"
						className="pf-eyebrow"
						placeholder={ __( 'Eyebrow text…', 'cropx' ) }
						value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="h2"
						className="pf-heading"
						placeholder={ __( 'Heading (use Italic to underline a word)…', 'cropx' ) }
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						allowedFormats={ [ 'core/italic', 'core/bold' ] }
					/>
					<RichText
						tagName="p"
						className="pf-sub"
						placeholder={ __( 'Supporting text… (leave empty to hide on the front end)', 'cropx' ) }
						value={ subtext }
						onChange={ ( v ) => setAttributes( { subtext: v } ) }
						allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
					/>
					<div className="pf-actions">
						{ primaryLabel && (
							<span className="btn-primary pf-cta-preview" aria-hidden="true">
								{ primaryLabel }
							</span>
						) }
						{ secondaryLabel && (
							<span className="btn-ghost pf-cta-preview" aria-hidden="true">
								{ secondaryLabel }
							</span>
						) }
					</div>
				</div>
			</div>
		</>
	);
}
