import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import './editor.css';

const themeUri = window.cropxThemeData?.themeUri ?? '';

export default function Edit( { attributes, setAttributes } ) {
	const {
		bgColor = 'taupe',
		alignment = 'center',
		eyebrow,
		eyebrowColor,
		showEyebrow,
		appStoreImageId,
		appStoreImageUrl,
		appStoreImageAlt,
		appStoreUrl,
		googlePlayImageId,
		googlePlayImageUrl,
		googlePlayImageAlt,
		googlePlayUrl,
	} = attributes;

	// PageSpeed fix (Aug 2026 gotcha): real, cacheable drift-pattern URL via a
	// CSS custom property instead of a relative url() in style.css — see
	// render.php for the front-end half and CLAUDE.md's WordPress block
	// development gotchas for the full history of why this matters.
	const blockProps = useBlockProps( {
		className: `app-badges app-badges--bg-${ bgColor }`,
		style: bgColor === 'deep-blue'
			? { '--ab-pattern-url': `url(${ themeUri }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	function BadgeUpload( { label, imageId, imageUrl, onSelect } ) {
		return (
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onSelect }
					allowedTypes={ [ 'image' ] }
					value={ imageId }
					render={ ( { open } ) =>
						imageUrl ? (
							<button
								onClick={ open }
								className="ab-badge"
								style={ { border: 'none', background: 'none', padding: 0, cursor: 'pointer' } }
								title={ __( 'Click to replace', 'cropx' ) }
							>
								<img src={ imageUrl } alt="" />
							</button>
						) : (
							<button className="ab-upload-placeholder" onClick={ open }>
								{ label }
							</button>
						)
					}
				/>
			</MediaUploadCheck>
		);
	}

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
							{ label: __( 'Deep Blue + Topo',    'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Badge alignment', 'cropx' ) }
						value={ alignment }
						options={ [
							{ label: __( 'Left',            'cropx' ), value: 'left'   },
							{ label: __( 'Center (default)', 'cropx' ), value: 'center' },
							{ label: __( 'Right',           'cropx' ), value: 'right'  },
						] }
						onChange={ ( v ) => setAttributes( { alignment: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						help={ __( 'Off by default — the badges usually speak for themselves.', 'cropx' ) }
						checked={ !! showEyebrow }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					{ showEyebrow && (
						<>
							<TextControl
								label={ __( 'Eyebrow text', 'cropx' ) }
								placeholder={ __( 'e.g. Get the CropX app', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							/>
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
						</>
					) }
				</PanelBody>

				{ /* ── App Store badge ── */ }
				<PanelBody title={ __( 'App Store Badge', 'cropx' ) } initialOpen={ true }>
					<p style={ { fontSize: '12px', color: '#757575', margin: '0 0 8px', lineHeight: 1.5 } }>
						{ __( 'Upload the official "Download on the App Store" badge from Apple’s own badge page — not a recreation.', 'cropx' ) }
					</p>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( {
								appStoreImageId: media.id,
								appStoreImageUrl: media.url,
							} ) }
							allowedTypes={ [ 'image' ] }
							value={ appStoreImageId }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open } style={ { width: '100%', justifyContent: 'center', marginBottom: '12px' } }>
									{ appStoreImageUrl ? __( 'Replace image', 'cropx' ) : __( 'Upload image', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Link URL', 'cropx' ) }
						placeholder="https://apps.apple.com/…"
						value={ appStoreUrl }
						onChange={ ( v ) => setAttributes( { appStoreUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Alt text', 'cropx' ) }
						value={ appStoreImageAlt }
						onChange={ ( v ) => setAttributes( { appStoreImageAlt: v } ) }
					/>
				</PanelBody>

				{ /* ── Google Play badge ── */ }
				<PanelBody title={ __( 'Google Play Badge', 'cropx' ) } initialOpen={ true }>
					<p style={ { fontSize: '12px', color: '#757575', margin: '0 0 8px', lineHeight: 1.5 } }>
						{ __( 'Upload the official "Get it on Google Play" badge from Google’s own badge page — not a recreation.', 'cropx' ) }
					</p>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( {
								googlePlayImageId: media.id,
								googlePlayImageUrl: media.url,
							} ) }
							allowedTypes={ [ 'image' ] }
							value={ googlePlayImageId }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open } style={ { width: '100%', justifyContent: 'center', marginBottom: '12px' } }>
									{ googlePlayImageUrl ? __( 'Replace image', 'cropx' ) : __( 'Upload image', 'cropx' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Link URL', 'cropx' ) }
						placeholder="https://play.google.com/store/apps/…"
						value={ googlePlayUrl }
						onChange={ ( v ) => setAttributes( { googlePlayUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Alt text', 'cropx' ) }
						value={ googlePlayImageAlt }
						onChange={ ( v ) => setAttributes( { googlePlayImageAlt: v } ) }
					/>
				</PanelBody>

			</InspectorControls>

			<div { ...blockProps }>
				<div className="app-badges-inner">
					{ showEyebrow && eyebrow && (
						<p className="section-eyebrow" style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }>
							{ eyebrow }
						</p>
					) }
					<div className={ `ab-row ab-row--${ alignment }` }>
						<BadgeUpload
							label={ __( '+ Upload App Store badge', 'cropx' ) }
							imageId={ appStoreImageId }
							imageUrl={ appStoreImageUrl }
							onSelect={ ( media ) => setAttributes( {
								appStoreImageId: media.id,
								appStoreImageUrl: media.url,
							} ) }
						/>
						<BadgeUpload
							label={ __( '+ Upload Google Play badge', 'cropx' ) }
							imageId={ googlePlayImageId }
							imageUrl={ googlePlayImageUrl }
							onSelect={ ( media ) => setAttributes( {
								googlePlayImageId: media.id,
								googlePlayImageUrl: media.url,
							} ) }
						/>
					</div>
				</div>
			</div>
		</>
	);
}
