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
	RangeControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, items } = attributes;

	const blockProps = useBlockProps( { className: 'hwf-section' } );

	// ── Item helpers — always spread to avoid shared references ──

	function updateItem( idx, field, value ) {
		setAttributes( {
			items: items.map( ( item, i ) => i === idx ? { ...item, [ field ]: value } : item ),
		} );
	}

	function selectItemImage( idx, media ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, imageId: media.id, imageUrl: media.url } : item
			),
		} );
	}

	function clearItemImage( idx ) {
		setAttributes( {
			items: items.map( ( item, i ) =>
				i === idx ? { ...item, imageId: 0, imageUrl: '' } : item
			),
		} );
	}

	function addItem() {
		setAttributes( {
			items: [ ...items, {
				name: '', description: '', url: '#',
				imageId: 0, imageUrl: '',
				imageHeight: 90, thumbCentered: false,
			} ],
		} );
	}

	function removeItem( idx ) {
		if ( items.length <= 1 ) return;
		setAttributes( { items: items.filter( ( _, i ) => i !== idx ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Eyebrow', 'cropx' ) }
						value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Items', 'cropx' ) } initialOpen={ true }>
					{ items.map( ( item, idx ) => (
						<div
							key={ idx }
							style={ {
								marginBottom: '16px',
								paddingBottom: '16px',
								borderBottom: idx < items.length - 1 ? '1px solid #e0e0e0' : 'none',
							} }
						>
							<p style={ { fontWeight: 600, marginBottom: '8px', fontSize: '12px', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#757575' } }>
								{ __( 'Item', 'cropx' ) } { idx + 1 }
							</p>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => selectItemImage( idx, media ) }
									allowedTypes={ [ 'image' ] }
									value={ item.imageId }
									render={ ( { open } ) => (
										<Button
											onClick={ open }
											variant="secondary"
											style={ { marginBottom: '6px', display: 'block', width: '100%', justifyContent: 'center' } }
										>
											{ item.imageUrl
												? __( 'Replace image', 'cropx' )
												: __( 'Select image', 'cropx' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ item.imageUrl && (
								<Button
									onClick={ () => clearItemImage( idx ) }
									variant="link"
									isDestructive
									style={ { marginBottom: '4px', display: 'block' } }
								>
									{ __( 'Remove image', 'cropx' ) }
								</Button>
							) }
							<TextControl
								label={ __( 'Name', 'cropx' ) }
								value={ item.name }
								onChange={ ( v ) => updateItem( idx, 'name', v ) }
							/>
							<TextControl
								label={ __( 'Description', 'cropx' ) }
								value={ item.description }
								onChange={ ( v ) => updateItem( idx, 'description', v ) }
							/>
							<TextControl
								label={ __( 'URL', 'cropx' ) }
								value={ item.url }
								onChange={ ( v ) => updateItem( idx, 'url', v ) }
							/>
							<RangeControl
								label={ __( 'Image height (%)', 'cropx' ) }
								value={ item.imageHeight }
								onChange={ ( v ) => updateItem( idx, 'imageHeight', v ) }
								min={ 50 }
								max={ 100 }
							/>
							<ToggleControl
								label={ __( 'Center image vertically', 'cropx' ) }
								help={ __( 'Use for compact hardware like Rivo — centers the image in the pill instead of bottom-aligning it.', 'cropx' ) }
								checked={ item.thumbCentered }
								onChange={ ( v ) => updateItem( idx, 'thumbCentered', v ) }
							/>
							<Button
								onClick={ () => removeItem( idx ) }
								variant="link"
								isDestructive
								disabled={ items.length <= 1 }
							>
								{ __( 'Remove item', 'cropx' ) }
							</Button>
						</div>
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

			<section { ...blockProps }>
				<div className="hwf-header">
					{ eyebrow && <p className="hwf-eyebrow">{ eyebrow }</p> }
				</div>

				{ /* Static horizontal row in editor — no cloning, no auto-scroll */ }
				<div className="hwc-marquee hwc-marquee--editor">
					<div className="hwc-track">
						{ items.map( ( item, idx ) => (
							<div
								key={ idx }
								className="hwf-pill"
								style={ { '--hwf-img-h': `${ item.imageHeight }%` } }
							>
								<span className="hwf-text">
									<span className="hwf-name-row">
										<span className="hwf-name">
											{ item.name || __( 'Product name', 'cropx' ) }
										</span>
									</span>
									<span className="hwf-desc">
										{ item.description || __( 'Description', 'cropx' ) }
									</span>
								</span>
								<span className={ `hwf-thumb${ item.thumbCentered ? ' hwf-thumb--centered' : '' }` }>
									{ item.imageUrl ? (
										<img src={ item.imageUrl } alt="" />
									) : (
										<span className="hwf-thumb-placeholder" />
									) }
								</span>
							</div>
						) ) }
					</div>
				</div>
			</section>
		</>
	);
}
