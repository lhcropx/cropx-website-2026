/**
 * Shared reorder helpers used across all blocks that support
 * drag-and-drop / up-down reordering of items.
 */

/**
 * Move an item in an array one step up or down.
 * Returns a new array; the original is not mutated.
 *
 * @param {Array}  arr  The source array.
 * @param {number} idx  Index of the item to move.
 * @param {'up'|'down'} dir  Direction to move.
 * @returns {Array}
 */
export function moveItem( arr, idx, dir ) {
	if ( dir === 'up' && idx === 0 ) return arr;
	if ( dir === 'down' && idx === arr.length - 1 ) return arr;

	const next = [ ...arr ];
	const swapWith = dir === 'up' ? idx - 1 : idx + 1;
	[ next[ idx ], next[ swapWith ] ] = [ next[ swapWith ], next[ idx ] ];
	return next;
}

/**
 * Reorder an array by moving the item at `from` to position `to`.
 * Returns a new array; the original is not mutated.
 *
 * @param {Array}  arr   The source array.
 * @param {number} from  Index of the dragged item.
 * @param {number} to    Index of the drop target.
 * @returns {Array}
 */
export function reorderByDrag( arr, from, to ) {
	if ( from === to ) return arr;
	const next = [ ...arr ];
	const [ item ] = next.splice( from, 1 );
	next.splice( to, 0, item );
	return next;
}
