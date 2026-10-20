/**
 * Animated Device Hero — Curved Edge: view.js
 *
 * Runs on the front end after DOM parse. Detects the background colour of the
 * block that immediately follows the hero and sets --shc-swoop-fill so the SVG
 * taupe fill blends seamlessly into whatever section comes next.
 *
 * Identical to hero-curved-standard's view.js, just targeting this block's
 * wrapper class — the paired device/app slideshow itself is pure CSS (see
 * render.php's per-instance shca-rise-* / shca-sink-* keyframes), so no JS is
 * needed to drive that part.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-cropx-hero-curved-animated' ).forEach( ( block ) => {
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
