/**
 * Product Tabs block — editor UI.
 *
 * Two-panel card grid (Hardware / Software). Editors switch between the
 * panels with a functional tab bar, then add/edit/remove cards inside the
 * active panel. Each card has an image, a name, a short tagline, and the
 * anchor ID of the section it should scroll to on the front-end.
 *
 * InspectorControls hold only the tab label overrides; all card editing
 * happens inline in the canvas for a WYSIWYG feel.
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
	Button,
	TextControl,
	Notice,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import './editor.css';

/* ── helpers ── */

function uid() {
	return 'item-' + Math.random().toString( 36 ).slice( 2, 9 );
}

const blankItem = () => ( {
	id:       uid(),
	imageId:  0,
	imageUrl: '',
	imageAlt: '',
	name:     '',
	tagline:  '',
	anchor:   '',
} );

/* ── sub-component: single editable card ── */

function ItemCard( { item, onChange, onRemove } ) {
	return (
		<div className="ptabs-editor-card">
			{/* Image picker */}
			<div className="ptabs-editor-card__img">
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( media ) =>
							onChange( {
								imageId:  media.id,
								imageUrl: media.url,
								imageAlt: media.alt || '',
							} )
						}
						allowedTypes={ [ 'image' ] }
						value={ item.imageId }
						render={ ( { open } ) =>
							item.imageUrl ? (
								<img
									src={ item.imageUrl }
									alt={ item.imageAlt }
									onClick={ open }
									title={ __( 'Click to replace', 'cropx' ) }
								/>
							) : (
								<Button onClick={ open } variant="secondary">
									{ __( '+ Image', 'cropx' ) }
								</Button>
							)
						}
					/>
				</MediaUploadCheck>
			</div>

			{/* Editable fields */}
			<div className="ptabs-editor-card__fields">
				<TextControl
					label={ __( 'Name', 'cropx' ) }
					value={ item.name }
					onChange={ ( v ) => onChange( { name: v } ) }
					placeholder={ __( 'Product or feature name', 'cropx' ) }
				/>
				<TextControl
					label={ __( 'Tagline', 'cropx' ) }
					value={ item.tagline }
					onChange={ ( v ) => onChange( { tagline: v } ) }
					placeholder={ __( '1–2 sentence description', 'cropx' ) }
				/>
				<TextControl
					label={ __( 'Anchor ID (no #)', 'cropx' ) }
					value={ item.anchor }
					onChange={ ( v ) => onChange( { anchor: v } ) }
					placeholder={ __( 'e.g. soil-sensor', 'cropx' ) }
					help={ __( 'The card links to this anchor further down the page.', 'cropx' ) }
				/>
			</div>

			<Button
				className="ptabs-editor-card__remove"
				onClick={ onRemove }
				variant="link"
				isDestructive
				label={ __( 'Remove card', 'cropx' ) }
			>
				✕
			</Button>
		</div>
	);
}

/* ── main Edit component ── */

export default function Edit( { attributes, setAttributes } ) {
	const { hardwareLabel, softwareLabel, hardwareItems, softwareItems } = attributes;

	// Which tab is active in the editor canvas (local state only — not stored).
	const [ activeTab, setActiveTab ] = useState( 'hardware' );

	const blockProps = useBlockProps( { className: 'ptabs-block ptabs-block--editor' } );

	// Helpers that write to the right attribute array.
	const items    = activeTab === 'hardware' ? hardwareItems : softwareItems;
	const setItems = ( next ) =>
		setAttributes( activeTab === 'hardware'
			? { hardwareItems: next }
			: { softwareItems: next }
		);

	const updateItem = ( id, patch ) =>
		setItems( items.map( ( it ) => ( it.id === id ? { ...it, ...patch } : it ) ) );

	const removeItem = ( id ) =>
		setItems( items.filter( ( it ) => it.id !== id ) );

	const addItem = () =>
		setItems( [ ...items, blankItem() ] );

	const activeLabel = activeTab === 'hardware' ? hardwareLabel : softwareLabel;

	return (
		<>
			{ /* ── Sidebar: tab label overrides only ── */ }
			<InspectorControls>
				<PanelBody title={ __( 'Tab labels', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Hardware tab label', 'cropx' ) }
						value={ hardwareLabel }
						onChange={ ( v ) => setAttributes( { hardwareLabel: v } ) }
					/>
					<TextControl
						label={ __( 'Software tab label', 'cropx' ) }
						value={ softwareLabel }
						onChange={ ( v ) => setAttributes( { softwareLabel: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			{ /* ── Canvas ── */ }
			<div { ...blockProps }>

				{ /* Tab bar — functional in editor so you can switch panels */ }
				<div className="ptabs-tabs">
					<button
						type="button"
						className={ `ptabs-tab${ activeTab === 'hardware' ? ' is-active' : '' }` }
						onClick={ () => setActiveTab( 'hardware' ) }
					>
						{ hardwareLabel }
					</button>
					<button
						type="button"
						className={ `ptabs-tab${ activeTab === 'software' ? ' is-active' : '' }` }
						onClick={ () => setActiveTab( 'software' ) }
					>
						{ softwareLabel }
					</button>
				</div>

				{ /* Card list for the active panel */ }
				<div className="ptabs-editor-grid">
					{ items.length === 0 && (
						<Notice status="info" isDismissible={ false }>
							{ __( 'No cards yet — add one below.', 'cropx' ) }
						</Notice>
					) }

					{ items.map( ( item ) => (
						<ItemCard
							key={ item.id }
							item={ item }
							onChange={ ( patch ) => updateItem( item.id, patch ) }
							onRemove={ () => removeItem( item.id ) }
						/>
					) ) }

					<Button
						className="ptabs-editor-add"
						onClick={ addItem }
						variant="primary"
					>
						{ `+ ${ __( 'Add', 'cropx' ) } ${ activeLabel } ${ __( 'card', 'cropx' ) }` }
					</Button>
				</div>
			</div>
		</>
	);
}
