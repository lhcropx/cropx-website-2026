/**
 * Product Grid block — editor experience.
 *
 * Renders a close preview of the front-end card grid.
 * All item data (photo, name, description, URL, overlay) is
 * managed via InspectorControls in the sidebar.
 */

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
	TextareaControl,
	SelectControl,
	ToggleControl,
	Button,
	RangeControl,
} from '@wordpress/components';

import './editor.css';

const EYEBROW_COLOR_OPTIONS = [
	{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
	{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
	{ label: __( 'White',                'cropx' ), value: 'white'      },
];

const OVERLAY_TYPE_OPTIONS = [
	{ label: __( 'None',       'cropx' ), value: 'none'       },
	{ label: __( 'Card bleed', 'cropx' ), value: 'card-bleed' },
	{ label: __( 'Contained',  'cropx' ), value: 'contained'  },
];

const ArrowSvg = () => (
	<svg viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path
			d="M3 8h10M9 4l4 4-4 4"
			stroke="currentColor"
			strokeWidth="1.5"
			strokeLinecap="round"
			strokeLinejoin="round"
		/>
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, eyebrowColor, heading, blurb, showHeader, items } = attributes;

	const blockProps = useBlockProps( { className: 'pg-block' } );

	// ── Item helpers ─────────────────────────────────────

	function updateItem( idx, field, value ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, [ field ]: value } : item
			),
		} );
	}

	function selectPhoto( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx
					? { ...item, photoId: media.id, photoUrl: media.url, photoAlt: media.alt || '' }
					: item
			),
		} );
	}

	function clearPhoto( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, photoId: 0, photoUrl: '', photoAlt: '' } : item
			),
		} );
	}

	function selectOverlay( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx
					? { ...item, overlayId: media.id, overlayUrl: media.url }
					: item
			),
		} );
	}

	function clearOverlay( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, overlayId: 0, overlayUrl: '' } : item
			),
		} );
	}

	function addItem() {
		setAttributes( {
			items: [
				...items,
				{
					name: '', description: '', url: '#',
					photoId: 0, photoUrl: '', photoAlt: '',
					overlayId: 0, overlayUrl: '', overlayType: 'none', overlayPadding: 0,
				},
			],
		} );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
	}

	// ── Sidebar ──────────────────────────────────────────

	return (
		<>
			<InspectorControls>

				<PanelBody title={ __( 'Section settings', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show header (eyebrow / heading / blurb)', 'cropx' ) }
						checked={ showHeader }
						onChange={ ( v ) => setAttributes( { showHeader: v } ) }
					/>
				</PanelBody>

				{ showHeader && (
					<PanelBody title={ __( 'Header content', 'cropx' ) } initialOpen={ true }>
						<TextControl
							label={ __( 'Eyebrow', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						/>
						<SelectControl
							label={ __( 'Eyebrow color', 'cropx' ) }
							value={ eyebrowColor ?? 'cropx-blue' }
							options={ EYEBROW_COLOR_OPTIONS }
							onChange={ ( v ) => setAttributes( { eyebrowColor: v } ) }
						/>
						<TextControl
							label={ __( 'Heading', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
						/>
						<TextareaControl
							label={ __( 'Blurb paragraph (optional)', 'cropx' ) }
							value={ blurb }
							onChange={ ( v ) => setAttributes( { blurb: v } ) }
							help={ __( 'Short supporting paragraph. Displays at half the section width.', 'cropx' ) }
						/>
					</PanelBody>
				) }

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>
					{ items.map( ( item, idx ) => (
						<PanelBody
							key={ idx }
							title={ item.name || `${ __( 'Item', 'cropx' ) } ${ idx + 1 }` }
							initialOpen={ idx === 0 }
						>
							<TextControl
								label={ __( 'Product name', 'cropx' ) }
								value={ item.name }
								onChange={ ( v ) => updateItem( idx, 'name', v ) }
							/>
							<TextareaControl
								label={ __( 'Description (2-line excerpt)', 'cropx' ) }
								value={ item.description }
								onChange={ ( v ) => updateItem( idx, 'description', v ) }
							/>
							<TextControl
								label={ __( 'URL', 'cropx' ) }
								value={ item.url }
								onChange={ ( v ) => updateItem( idx, 'url', v ) }
							/>

							<p style={ { fontWeight: 600, margin: '8px 0 4px', fontSize: '11px', textTransform: 'uppercase', color: '#757575' } }>
								{ __( 'Photo', 'cropx' ) }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectPhoto( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ item.photoId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '4px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ item.photoUrl
												? __( 'Replace photo', 'cropx' )
												: __( 'Select photo', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ item.photoUrl && (
								<Button
									onClick={ () => clearPhoto( idx ) }
									variant="link"
									isDestructive
									style={ { display: 'block', marginBottom: '8px' } }
								>
									{ __( 'Remove photo', 'cropx' ) }
								</Button>
							) }

							<p style={ { fontWeight: 600, margin: '8px 0 4px', fontSize: '11px', textTransform: 'uppercase', color: '#757575' } }>
								{ __( 'Illustration overlay', 'cropx' ) }
							</p>
							<SelectControl
								label={ __( 'Overlay type', 'cropx' ) }
								value={ item.overlayType ?? 'none' }
								options={ OVERLAY_TYPE_OPTIONS }
								onChange={ ( v ) => updateItem( idx, 'overlayType', v ) }
								help={
									item.overlayType === 'card-bleed'
										? __( 'Illustration extends from photo column into the text area (e.g. Evato sensor arm).', 'cropx' )
										: item.overlayType === 'contained'
										? __( 'Illustration contained within the photo column (e.g. Rivo rain gauge).', 'cropx' )
										: ''
								}
							/>
							{ item.overlayType !== 'none' && (
								<>
									<MediaUploadCheck>
										<MediaUpload
											onSelect={ ( media ) => selectOverlay( idx, media ) }
											allowedTypes={ [ 'image' ] }
											value={ item.overlayId }
											render={ ( { open } ) => (
												<Button
													onClick={ open }
													variant="secondary"
													style={ { marginBottom: '4px', display: 'block', width: '100%', justifyContent: 'center' } }
												>
													{ item.overlayUrl
														? __( 'Replace illustration', 'cropx' )
														: __( 'Select illustration', 'cropx' ) }
												</Button>
											) }
										/>
									</MediaUploadCheck>
									{ item.overlayUrl && (
										<Button
											onClick={ () => clearOverlay( idx ) }
											variant="link"
											isDestructive
											style={ { display: 'block', marginBottom: '8px' } }
										>
											{ __( 'Remove illustration', 'cropx' ) }
										</Button>
									) }
									{ item.overlayType === 'contained' && (
										<RangeControl
											label={ __( 'Contained padding (%)', 'cropx' ) }
											help={ __( 'Vertical breathing room around the illustration. Use 8% for tall sensors like Rivo.', 'cropx' ) }
											value={ item.overlayPadding ?? 0 }
											onChange={ ( v ) => updateItem( idx, 'overlayPadding', v ) }
											min={ 0 }
											max={ 20 }
										/>
									) }
								</>
							) }

							<Button
								onClick={ () => removeItem( idx ) }
								variant="link"
								isDestructive
								disabled={ items.length <= 1 }
								style={ { marginTop: '4px' } }
							>
								{ __( 'Remove item', 'cropx' ) }
							</Button>
						</PanelBody>
					) ) }

					<Button
						onClick={ addItem }
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
					>
						{ __( '+ Add item', 'cropx' ) }
					</Button>
				</PanelBody>

			</InspectorControls>

			{/* ── Editor preview ──────────────────────────── */}

			<section { ...blockProps }>
				<div className="pg-inner">

					{ showHeader && (
						<header className="pg-header">
							{ eyebrow && (
								<span
									className="pg-eyebrow"
									style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
								>
									{ eyebrow }
								</span>
							) }
							{ heading && <h2 className="pg-heading">{ heading }</h2> }
							{ blurb && <p className="pg-blurb">{ blurb }</p> }
						</header>
					) }

					<div className="pg-grid">
						{ items.map( ( item, idx ) => (
							// div instead of <a> in editor — no navigation on click
							<div key={ idx } className="pg-item">

								<div className="pg-thumb-outer">
									<div
										className="pg-thumb"
										style={
											item.photoUrl
												? {
													backgroundImage: `url(${ item.photoUrl })`,
													backgroundSize: 'cover',
													backgroundPosition: 'center',
												}
												: undefined
										}
									>
										{ item.overlayType === 'contained' && item.overlayUrl && (
											<img
												className="pg-overlay--contained"
												src={ item.overlayUrl }
												alt=""
												style={
													item.overlayPadding
														? { paddingBlock: `${ item.overlayPadding }%` }
														: undefined
												}
											/>
										) }
									</div>
								</div>

								<div className="pg-text">
									<strong className="pg-name">
										{ item.name || __( 'Product name', 'cropx' ) }
										<span className="pg-arrow" aria-hidden="true">
											<ArrowSvg />
										</span>
									</strong>
									<p className="pg-desc">
										{ item.description || __( 'Description…', 'cropx' ) }
									</p>
								</div>

								{ item.overlayType === 'card-bleed' && item.overlayUrl && (
									<img
										className="pg-overlay--card-bleed"
										src={ item.overlayUrl }
										alt=""
									/>
								) }

							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
