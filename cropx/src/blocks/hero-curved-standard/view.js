/**
 * Hero — Curved Edge: view.js
 *
 * Runs on the front end after DOM parse. Detects the background colour of the
 * block that immediately follows the hero and sets --shc-swoop-fill so the SVG
 * taupe fill blends seamlessly into whatever section comes next.
 *
 * getComputedStyle() returns the actual rendered colour even when the next
 * block's background is set via a CSS class rather than an inline style, so
 * this works regardless of how the next block is coloured.
 *
 * Falls back to #f3f1f1 (page taupe) via the CSS custom property default.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-cropx-hero-curved-standard' ).forEach( ( block ) => {
		const next = block.nextElementSibling;
		if ( ! next ) return;

		const bg = getComputedStyle( next ).backgroundColor;

		// Only apply when we get a real (non-transparent) colour back.
		if ( bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent' ) {
			const wrap = block.querySelector( '.shc-bleed-wrap' );
			if ( wrap ) {
				wrap.style.setProperty( '--shc-swoop-fill', bg );
			}
		}
	} );
} );
