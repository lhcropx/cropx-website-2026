/**
 * Blog Hero — Curved Edge: view.js
 *
 * Detects the background colour of the block immediately following the hero
 * and writes it as --shc-swoop-fill so the SVG taupe fill blends seamlessly
 * into whatever section comes next. Falls back to #f3f1f1 (page taupe) via
 * the CSS custom property default.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-cropx-hero-blog' ).forEach( ( block ) => {
		const next = block.nextElementSibling;
		if ( ! next ) return;

		const bg = getComputedStyle( next ).backgroundColor;

		if ( bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent' ) {
			const wrap = block.querySelector( '.shc-bleed-wrap' );
			if ( wrap ) {
				wrap.style.setProperty( '--shc-swoop-fill', bg );
			}
		}
	} );
} );
