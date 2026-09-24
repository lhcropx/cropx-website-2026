import { initScrollReveal } from '../../shared/scrollReveal';

( function () {
	/*
	 * FAQ Accordion — front-end controller.
	 *
	 * Single-open accordion behaviour: clicking a closed item's toggle opens
	 * it and closes any other open item in the same list; clicking an open
	 * item's toggle closes it. is-open + aria-expanded drive both the CSS
	 * grid-template-rows height animation and the +/- icon rotation (see
	 * style.css).
	 *
	 * Also wires up the scroll-triggered reveal animation (eyebrow/heading/
	 * items fading up as the section scrolls into view) via the shared
	 * initScrollReveal() helper — see src/shared/scrollReveal.js.
	 */

	initScrollReveal( '.wp-block-cropx-faq-accordion' );

	function init() {
		document.querySelectorAll( '.faq-list' ).forEach( ( list ) => {
			const items = Array.from( list.querySelectorAll( '.faq-item' ) );

			items.forEach( ( item ) => {
				const toggle = item.querySelector( '.faq-toggle' );
				if ( ! toggle ) { return; }

				toggle.addEventListener( 'click', () => {
					const wasOpen = item.classList.contains( 'is-open' );

					// Close every item in this list
					items.forEach( ( i ) => {
						i.classList.remove( 'is-open' );
						const btn = i.querySelector( '.faq-toggle' );
						if ( btn ) { btn.setAttribute( 'aria-expanded', 'false' ); }
					} );

					// Open the clicked item only if it was previously closed
					if ( ! wasOpen ) {
						item.classList.add( 'is-open' );
						toggle.setAttribute( 'aria-expanded', 'true' );
					}
				} );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
