/**
 * Icon Columns block — view.js
 *
 * The PHP render outputs a flat, ordered list of .ici-item elements inside
 * a CSS multi-column container (a reasonable no-JS fallback — see
 * render.php / style.css). This script upgrades that on every page that has
 * JavaScript available: it regroups the flat list into real .ici-col
 * wrapper divs using a remainder-first count split, so the last column's
 * item count is never greater than any other column's — something CSS
 * multi-column can't guarantee since it only balances by rendered height,
 * not item count.
 *
 * Re-runs on resize (debounced) since the effective column count changes at
 * several breakpoints. Always regroups from the ORIGINAL flat item order
 * (stored once per container) rather than reading the current, possibly
 * already-grouped DOM — otherwise repeated regrouping would drift from the
 * source order.
 */

import { getEffectiveColumnCount, distributeIntoColumns } from '../../shared/columnDistribute';

( function () {
	const containers = Array.from( document.querySelectorAll( '.ici-columns[data-base-columns]' ) );
	if ( ! containers.length ) return;

	// One entry per container: { baseColumns, items (original flat order) }.
	const registry = containers.map( function ( el ) {
		return {
			el: el,
			baseColumns: parseInt( el.getAttribute( 'data-base-columns' ), 10 ) || 4,
			items: Array.from( el.children ), // captured once, before any regrouping
		};
	} );

	function regroup( entry ) {
		const width = entry.el.getBoundingClientRect().width || window.innerWidth;
		const effectiveCols = getEffectiveColumnCount( entry.baseColumns, width );
		const groups = distributeIntoColumns( entry.items, effectiveCols );

		entry.el.textContent = ''; // clear current children (items are detached, not destroyed)
		groups.forEach( function ( groupItems ) {
			const col = document.createElement( 'div' );
			col.className = 'ici-col';
			groupItems.forEach( function ( item ) {
				col.appendChild( item );
			} );
			entry.el.appendChild( col );
		} );

		entry.el.classList.add( 'ici-columns--js' );
	}

	function regroupAll() {
		registry.forEach( regroup );
	}

	regroupAll();

	// Debounced resize handler — the effective column count only changes at
	// a handful of breakpoints, no need to recompute on every pixel.
	let resizeTimer = null;
	window.addEventListener( 'resize', function () {
		if ( resizeTimer ) clearTimeout( resizeTimer );
		resizeTimer = setTimeout( regroupAll, 150 );
	} );
}() );
