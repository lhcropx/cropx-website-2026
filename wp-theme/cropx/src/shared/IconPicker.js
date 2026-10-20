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
			'endive', 'grape', 'grapefruit-citrus', 'leaf', 'leek',
			'lemon-citrus', 'lettuce', 'onion', 'pear', 'peas',
			'potato', 'pumpkin', 'rapeseed', 'roots', 'soybean',
			'sprout', 'strawberry', 'sugarcane', 'sunflower', 'tomato',
			'tulip', 'wheat', 'crop-cycle', 'grain-crop',
		],
	},
	{
		label: __( 'Field & Soil', 'cropx' ),
		icons: [
			'farm', 'fields', 'field-sun', 'semicircle-field',
			'single-fields', 'soil', 'soil-sensor-vertex', 'layers', '3d',
			'spiral-taper', 'soil-record', 'tillage-record',
			'location-center', 'point-of-interest',
		],
	},
	{
		label: __( 'Water & Irrigation', 'cropx' ),
		icons: [
			'applied-irrigation', 'droplet', 'droplets-irrigation', 'no-droplet', 'rain-bucket',
			'recharge', 'irrigation-history', 'irrigation-planning', 'setpoints', 'spray-irrigation',
			'valve-irrigation', 'leaching', 'effluent', 'volumetric',
			'irrigation-cycle', 'precipitation',
		],
	},
	{
		label: __( 'Sensors & Connectivity', 'cropx' ),
		icons: [
			'sensor', 'sensor-cloud', 'sensor-network', 'antenna', 'satellite',
			'bluetooth', 'devices', 'wireless-signal', 'no-signal', 'smartphone',
			'battery-charge', 'transmitted-cloud', 'pending-cloud', 'cloud-offline',
			'partner-connection', 'partner-connection-2',
			'battery-state-full', 'battery-state-low',
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
			'EC-electrical-conductivity', 'ET-evapotranspiration', 'speed',
			'weather-cloud-sun', 'weather-fog', 'weather-heavy-rain', 'weather-light-rain',
			'weather-mostly-cloudy', 'weather-rain-and-snow', 'weather-snow', 'weather-sun',
			'weather-thunderstorm', 'weather-wind', 'mountain-elevation', 'speed-2',
		],
	},
	{
		label: __( 'Data & Analytics', 'cropx' ),
		icons: [
			'chart', 'report', 'trending-up', 'trending-down', 'history',
			'group-data', 'measurement-units', 'ranking', 'ruler',
			'measure-square', 'stopwatch', 'filtering', 'merge',
		],
	},
	{
		label: __( 'Business & Commerce', 'cropx' ),
		icons: [
			'dealer', 'inventory', 'shipment', 'wallet',
		],
	},
	{
		label: __( 'Security & Access', 'cropx' ),
		icons: [
			'attribute-thumbprint', 'eye', 'lock', 'unlock',
			'security', 'user-circle', 'verification',
		],
	},
	{
		label: __( 'Operations & UI', 'cropx' ),
		icons: [
			'add-user', 'alarm-clock', 'announcement', 'calendar', 'date-time',
			'edit', 'settings', 'sync', 'user', 'farmer',
			'people-group', 'contact', 'email', 'password', 'location-pin',
			'link', 'language', 'label-tags', 'map', 'note-thumbtack',
			'attachment', 'file', 'idea-tip', 'glasses', 'expand',
			'reorder', 'spark', 'morning-digest', 'world',
			'add-calendar-event', 'help', 'home', 'message-send', 'minimize',
			'newsletter', 'print', 'rotate-ccw', 'rotate-cw', 'search',
			'share', 'tasks', 'troubleshooting', 'zoom-in', 'zoom-out',
		],
	},
];

// Flat list used for search filtering.
const ALL_ICON_SLUGS = ICON_CATEGORIES.flatMap( ( cat ) => cat.icons );

// ─────────────────────────────────────────────────────────────────────────────
// Retired-icon aliases.
//
// When an icon SVG is deleted from assets/icons/ because it was a duplicate
// of another one, blocks that already have the old slug saved would
// otherwise show a broken image in the editor canvas (the file is gone) even
// though the PHP-side render.php helper (cropx_resolve_icon_slug() in
// inc/helpers.php) already fixes the front end. Mirroring the same alias map
// here keeps the editor canvas preview and picker "selected" state in sync
// with whatever the front end actually renders — add an entry here whenever
// an icon is deleted as a duplicate of another.
// ─────────────────────────────────────────────────────────────────────────────
const ICON_SLUG_ALIASES = {
	'fields-2': 'fields',
};

/** Resolves a possibly-retired slug to its current replacement. */
export function resolveIconSlug( slug ) {
	return ICON_SLUG_ALIASES[ slug ] ?? slug;
}

const themeUri = window.cropxThemeData?.themeUri ?? '';

/** Returns the full URL for an icon SVG file. */
export function iconSrc( slug ) {
	return themeUri + 'assets/icons/' + resolveIconSlug( slug ) + '.svg';
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

	// Resolve a retired slug (e.g. a duplicate icon that's since been
	// deleted) so the correct swatch highlights as selected even for blocks
	// saved before the icon was retired.
	const resolvedValue = resolveIconSlug( value );

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
										selected={ resolvedValue === slug }
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
					{ __( 'Selected:', 'cropx' ) } <code>{ resolvedValue }</code>
				</p>
			) }
		</div>
	);
}
