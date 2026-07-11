/**
 * Shared IconPicker component + icon registry.
 *
 * Import this in any block editor that needs an icon-selection UI.
 * Importing this file automatically pulls in ./icon-picker.css so the
 * block doesn't need to duplicate the styles.
 *
 * Named exports:
 *   ICON_CATEGORIES  — the full 118-icon set, grouped by category
 *   iconSrc(slug)    — returns the theme-relative URL for an icon SVG
 *   IconPicker       — React component:
 *                        <IconPicker value={slug} onChange={(slug) => …} />
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import './icon-picker.css';

// ─────────────────────────────────────────────────────────────────────────────
// Icon registry — 118 icons organised by category.
// Slugs must match filenames in wp-theme/cropx/assets/icons/ (without .svg).
// ─────────────────────────────────────────────────────────────────────────────
export const ICON_CATEGORIES = [
	{
		label: __( 'Crops & Plants', 'cropx' ),
		icons: [
			'apple', 'asparagus', 'banana', 'beetroot', 'bell-pepper',
			'broccoli', 'carrot', 'celery', 'coriander', 'corn',
			'endive', 'grape', 'grapefruit-citrus', 'leek', 'lemon-citrus',
			'lettuce', 'onion', 'pear', 'peas', 'potato',
			'pumpkin', 'rapeseed', 'soybean', 'sprout', 'strawberry',
			'sugarcane', 'sunflower', 'tomato', 'tulip', 'wheat',
		],
	},
	{
		label: __( 'Field & Soil', 'cropx' ),
		icons: [
			'fields', 'fields-2', 'field-sun', 'semicircle-field', 'single-fields',
			'soil', 'soil-sensor-vertex', 'layers', '3d', 'spiral-taper',
		],
	},
	{
		label: __( 'Water & Irrigation', 'cropx' ),
		icons: [
			'droplet', 'droplets-irrigation', 'no-droplet', 'rain-bucket', 'recharge',
			'irrigation-history', 'irrigation-planning', 'spray-irrigation',
			'valve-irrigation', 'leaching', 'effluent',
		],
	},
	{
		label: __( 'Sensors & Connectivity', 'cropx' ),
		icons: [
			'sensor', 'sensor-cloud', 'sensor-network', 'antenna', 'satellite',
			'bluetooth', 'wireless-signal', 'smartphone', 'battery-charge',
			'transmitted-cloud', 'pending-cloud', 'cloud-offline',
			'partner-connection', 'partner-connection-2',
		],
	},
	{
		label: __( 'Agronomy & Field Ops', 'cropx' ),
		icons: [
			'planting', 'harvesting', 'scouting', 'machines-tractor', 'sprayer',
			'fertilization', 'fertilizer-record', 'spraying-record',
			'bug-pest', 'disease', 'nutrition',
		],
	},
	{
		label: __( 'Weather & Environment', 'cropx' ),
		icons: [
			'thermometer', 'thermometer-hot', 'thermometer-cold', 'thermometer-temperature',
			'wind-direction', 'frequency', 'mountain-snow',
			'EC-electrical-conductivity', 'ET-evapotranspiration', 'speed', 'speed-2',
		],
	},
	{
		label: __( 'Data & Analytics', 'cropx' ),
		icons: [
			'chart', 'report', 'trending-up', 'trending-down', 'history',
			'group-data', 'measurement-units', 'ruler',
		],
	},
	{
		label: __( 'Operations & UI', 'cropx' ),
		icons: [
			'alarm-clock', 'calendar', 'date-time', 'settings', 'sync',
			'user', 'people-group', 'contact', 'email', 'password',
			'location-pin', 'link', 'language', 'label-tags', 'note-thumbtack',
			'attachment', 'file', 'idea-tip', 'glasses', 'expand',
			'reorder', 'spark', 'morning-digest',
		],
	},
];

// Flat list used for search filtering.
const ALL_ICON_SLUGS = ICON_CATEGORIES.flatMap( ( cat ) => cat.icons );

const themeUri = window.cropxThemeData?.themeUri ?? '';

/** Returns the full URL for an icon SVG file. */
export function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + slug + '.svg';
}

// ─────────────────────────────────────────────────────────────────────────────
// IconSwatch — one clickable icon tile inside the picker grid.
// ─────────────────────────────────────────────────────────────────────────────
function IconSwatch( { slug, selected, onSelect } ) {
	return (
		<button
			type="button"
			title={ slug }
			className={ `cropx-icon-swatch${ selected ? ' is-selected' : '' }` }
			onClick={ () => onSelect( slug ) }
		>
			<img src={ iconSrc( slug ) } alt="" width="20" height="20" />
		</button>
	);
}

// ─────────────────────────────────────────────────────────────────────────────
// IconPicker — searchable, categorised icon grid for the Inspector sidebar.
//
// Usage:
//   <IconPicker value={ icon } onChange={ ( slug ) => setAttributes( { icon: slug } ) } />
// ─────────────────────────────────────────────────────────────────────────────
export function IconPicker( { value, onChange } ) {
	const [ search, setSearch ] = useState( '' );

	const query = search.toLowerCase().replace( /[\s\-_]+/g, '' );
	const filtered = query
		? ALL_ICON_SLUGS.filter( ( slug ) =>
			slug.replace( /[\-_]+/g, '' ).includes( query )
		  )
		: null;

	return (
		<div className="cropx-icon-picker">
			<input
				type="text"
				className="cropx-icon-search"
				placeholder={ __( 'Search icons…', 'cropx' ) }
				value={ search }
				onChange={ ( e ) => setSearch( e.target.value ) }
				aria-label={ __( 'Search icons', 'cropx' ) }
			/>

			<div className="cropx-icon-grid-wrap">
				{ filtered ? (
					/* Search results — flat grid */
					filtered.length > 0 ? (
						<div className="cropx-icon-grid">
							{ filtered.map( ( slug ) => (
								<IconSwatch
									key={ slug }
									slug={ slug }
									selected={ value === slug }
									onSelect={ onChange }
								/>
							) ) }
						</div>
					) : (
						<p className="cropx-icon-empty">
							{ __( 'No icons match', 'cropx' ) } &ldquo;{ search }&rdquo;
						</p>
					)
				) : (
					/* Browsing — grouped by category */
					ICON_CATEGORIES.map( ( cat ) => (
						<div key={ cat.label }>
							<p className="cropx-icon-cat-label">{ cat.label }</p>
							<div className="cropx-icon-grid">
								{ cat.icons.map( ( slug ) => (
									<IconSwatch
										key={ slug }
										slug={ slug }
										selected={ value === slug }
										onSelect={ onChange }
									/>
								) ) }
							</div>
						</div>
					) )
				) }
			</div>

			{ value && (
				<p className="cropx-icon-selected-label">
					{ __( 'Selected:', 'cropx' ) } <code>{ value }</code>
				</p>
			) }
		</div>
	);
}
