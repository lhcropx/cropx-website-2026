import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Two-Column Alternating — scroll-reveal wiring.
 *
 * Repeatable subsections (the optional intro plus one row per rendered
 * photo), so each is independently observed rather than sharing a single
 * reveal-group — see render.php for where the reveal-up classes and
 * --reveal-delay values are set, and src/shared/scrollReveal.js for the
 * mechanism. This runs alongside the block's existing height-sync IIFE
 * below, which is unrelated front-end logic.
 */
initScrollReveal( '.tca-intro, .tca-row' );

( function () {
	const BREAKPOINT = 768;
	const PADDING    = 80;
	const MIN_HEIGHT = 230;

	function syncHeights() {
		const isDesktop = window.innerWidth > BREAKPOINT;
		document.querySelectorAll( '.tca-section' ).forEach( ( section ) => {
			const photos = section.querySelectorAll( '.tca-photo' );
			if ( ! isDesktop ) {
				photos.forEach( ( p ) => { p.style.height = ''; } );
				return;
			}
			const textCols = section.querySelectorAll( '.tca-row .tca-content' );
			let maxH = 0;
			textCols.forEach( ( col ) => { maxH = Math.max( maxH, col.offsetHeight ); } );
			const targetH = Math.max( maxH + PADDING, MIN_HEIGHT );
			photos.forEach( ( p ) => { p.style.height = targetH + 'px'; } );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', syncHeights );
	} else {
		syncHeights();
	}
	if ( document.fonts?.ready ) {
		document.fonts.ready.then( syncHeights );
	}
	let resizeTimer;
	window.addEventListener( 'resize', () => {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( syncHeights, 100 );
	} );
}() );
