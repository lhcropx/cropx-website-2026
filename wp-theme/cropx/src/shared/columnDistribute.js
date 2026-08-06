/**
 * Shared column-distribution helpers for blocks that lay items out in a
 * column-major flow (fill column 1 top-to-bottom, then column 2, etc.) where
 * the number of columns changes at responsive breakpoints.
 *
 * Used by icon-columns' view.js (front end) and edit.js (editor canvas) so
 * both environments compute the exact same grouping from the exact same
 * inputs — no duplicated breakpoint math to drift out of sync.
 */

/**
 * Mirrors the max-width breakpoint cascade declared in a block's CSS
 * (narrower rules always win, regardless of which wider rule matched first —
 * see icon-columns/style.css's Responsive section for the source of truth
 * this must stay in sync with).
 *
 * @param {number} baseCount The column count the editor explicitly chose (4, 5, or 6).
 * @param {number} width     Current viewport/container width in px.
 * @returns {number} The column count that should actually be rendered at this width.
 */
export function getEffectiveColumnCount( baseCount, width ) {
	let cols = baseCount;

	if ( baseCount === 6 && width <= 1240 ) cols = 5;
	if ( ( baseCount === 5 || baseCount === 6 ) && width <= 1020 ) cols = 4;
	if ( width <= 820 ) cols = 3;
	if ( width <= 600 ) cols = 2;
	if ( width <= 320 ) cols = 1;

	return cols;
}

/**
 * Distributes a flat, ordered list of items into `colCount` column-major
 * groups — column 1 gets the first chunk, column 2 the next, and so on —
 * using a "remainder-first" split: any items left over after dividing evenly
 * go to the *earliest* columns, never the last one. This guarantees the last
 * column's item count is never greater than any other column's count (it's
 * always the plain floor(N / colCount), while earlier columns absorb the
 * +1 remainder).
 *
 * @param {Array} items    Flat, ordered list of items.
 * @param {number} colCount Desired number of columns.
 * @returns {Array[]} Array of `colCount` (or fewer, if there aren't enough
 *   items to fill every column) arrays of items, in column-major order.
 */
export function distributeIntoColumns( items, colCount ) {
	const total = items.length;
	if ( total === 0 ) return [];

	// Never render more columns than there are items — an empty trailing
	// column would look like a bug, not a feature.
	const k = Math.max( 1, Math.min( colCount, total ) );

	const base = Math.floor( total / k );
	const remainder = total % k;

	const columns = [];
	let idx = 0;
	for ( let c = 0; c < k; c++ ) {
		const size = base + ( c < remainder ? 1 : 0 );
		columns.push( items.slice( idx, idx + size ) );
		idx += size;
	}
	return columns;
}
