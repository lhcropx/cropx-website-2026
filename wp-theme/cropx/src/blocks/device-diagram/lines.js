/**
 * Device Diagram — connector lines.
 *
 * Draws one cubic-Bezier path per product from the card's phone-facing edge to
 * an evenly spaced point on the phone's edge. Shared by view.js (front end) and
 * edit.js (editor canvas) so both always draw identically.
 *
 * Geometry uses offsetLeft/offsetTop/offsetWidth/offsetHeight rather than
 * getBoundingClientRect(): those are unaffected by CSS transforms, so the lines
 * don't jump while a hovered card is scaled to 115% or while scroll-reveal has
 * the items translated.
 *
 * Both the stage (.dd-stage) and the phone (.dd-phone) are positioned
 * ancestors/children of the same coordinate space, and .dd-track is
 * display:contents on desktop, so every item's offsetParent is the stage.
 *
 * @param {HTMLElement} stage     The .dd-stage element.
 * @param {number}      activeIdx Index of the active product, or -1 for none.
 */
export function drawLines( stage, activeIdx = -1 ) {
	const svg = stage.querySelector( '.dd-lines' );
	const phone = stage.querySelector( '.dd-phone' );
	if ( ! svg || ! phone ) {
		return;
	}

	// Carousel layout hides the SVG (display:none) — nothing to draw.
	if ( window.getComputedStyle( svg ).display === 'none' ) {
		return;
	}

	const w = stage.offsetWidth;
	const h = stage.offsetHeight;
	svg.setAttribute( 'viewBox', `0 0 ${ w } ${ h }` );

	const items = Array.from( stage.querySelectorAll( '.dd-item' ) );
	const sides = [
		items.filter( ( el ) => el.dataset.side === 'left' ),
		items.filter( ( el ) => el.dataset.side === 'right' ),
	];

	const NS = 'http://www.w3.org/2000/svg';
	while ( svg.firstChild ) {
		svg.removeChild( svg.firstChild );
	}

	sides.forEach( ( group, sideIdx ) => {
		group.forEach( ( el, j ) => {
			// Anchor on the card itself (not the grid cell) so the line meets
			// the visible card edge.
			const card = el.querySelector( '.dd-card' ) || el;
			const cardLeft = el.offsetLeft + card.offsetLeft;
			const x1 = sideIdx === 0 ? cardLeft + card.offsetWidth : cardLeft;
			const y1 = el.offsetTop + card.offsetTop + card.offsetHeight / 2;

			const x2 = sideIdx === 0 ? phone.offsetLeft : phone.offsetLeft + phone.offsetWidth;
			const y2 = phone.offsetTop + ( phone.offsetHeight * ( j + 1 ) ) / ( group.length + 1 );

			const mid = ( x1 + x2 ) / 2;
			const path = document.createElementNS( NS, 'path' );
			path.setAttribute( 'd', `M${ x1 } ${ y1 } C${ mid } ${ y1 } ${ mid } ${ y2 } ${ x2 } ${ y2 }` );
			path.setAttribute( 'data-i', el.dataset.i );
			if ( Number( el.dataset.i ) === activeIdx ) {
				path.classList.add( 'is-active' );
			}
			svg.appendChild( path );
		} );
	} );
}
