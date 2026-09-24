import { initSnapAutoAdvance } from '../../shared/autoAdvance';
import { initScrollReveal } from '../../shared/scrollReveal';

( function () {
	/*
	 * Content Card Carousel — front-end controller.
	 *
	 * Mechanically identical to Testimonials Carousel's tcarousel controller
	 * (see src/blocks/testimonials-carousel/view.js for the fuller write-up) —
	 * manual nav via arrows + dots, native scroll-snap handles touch/trackpad
	 * swipe, dots are generated from the actual card count so markup never
	 * needs updating when cards are added or removed. The only real
	 * difference is visibleCount() naturally resolves to ~1 here since each
	 * card is wide, instead of ~3 for the smaller testimonial cards.
	 *
	 * Auto-advance (optional, editor toggle): data-cc-auto-advance="true"
	 * layers a timer on top via the shared initSnapAutoAdvance() helper.
	 */

	initScrollReveal( '.wp-block-cropx-cards-carousel' );

	document.querySelectorAll( '.cccarousel' ).forEach( ( root ) => {
		const track    = root.querySelector( '.cccarousel-track' );
		const prevBtn  = root.querySelector( '.cccarousel-prev' );
		const nextBtn  = root.querySelector( '.cccarousel-next' );
		const dotsWrap = root.querySelector( '.cccarousel-dots' );

		if ( ! track || ! track.children.length ) return;

		const items = Array.from( track.children );
		const count = items.length;
		let dots = [];

		function visibleCount() {
			const vw    = track.clientWidth;
			const cardW = items[ 0 ].getBoundingClientRect().width;
			const gap   = parseFloat( getComputedStyle( track ).gap ) || 0;
			return Math.max( 1, Math.round( ( vw + gap ) / ( cardW + gap ) ) );
		}

		function maxIndex() {
			return Math.max( 0, count - visibleCount() );
		}

		function renderDots() {
			dotsWrap.innerHTML = '';
			const total = maxIndex() + 1;
			for ( let i = 0; i < total; i++ ) {
				const d = document.createElement( 'button' );
				d.className = 'cccarousel-dot' + ( i === 0 ? ' is-active' : '' );
				d.type      = 'button';
				d.setAttribute( 'role', 'tab' );
				d.setAttribute( 'aria-label', `Go to card ${ i + 1 }` );
				d.addEventListener( 'click', () => scrollToCard( i ) );
				dotsWrap.appendChild( d );
			}
			dots = Array.from( dotsWrap.querySelectorAll( '.cccarousel-dot' ) );
		}

		function getActive() {
			const trackLeft = track.getBoundingClientRect().left;
			let closest = 0;
			let minDist  = Infinity;
			items.forEach( ( el, i ) => {
				const dist = Math.abs( el.getBoundingClientRect().left - trackLeft );
				if ( dist < minDist ) { minDist = dist; closest = i; }
			} );
			return Math.min( closest, maxIndex() );
		}

		function syncDots() {
			const a = getActive();
			dots.forEach( ( d, i ) => d.classList.toggle( 'is-active', i === a ) );
			if ( prevBtn ) prevBtn.disabled = a === 0;
			if ( nextBtn ) nextBtn.disabled = a === maxIndex();
		}

		function scrollToCard( i ) {
			items[ i ].scrollIntoView( { behavior: 'smooth', block: 'nearest', inline: 'center' } );
			setTimeout( syncDots, 400 );
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', () => {
				const a = getActive();
				if ( a > 0 ) scrollToCard( a - 1 );
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', () => {
				const a = getActive();
				if ( a < maxIndex() ) scrollToCard( a + 1 );
			} );
		}

		track.addEventListener( 'scroll', () => requestAnimationFrame( syncDots ), { passive: true } );
		window.addEventListener( 'resize', () => { renderDots(); syncDots(); } );

		renderDots();
		syncDots();

		const section = root.closest( '.cc-section' );
		if ( section && section.dataset.ccAutoAdvance === 'true' ) {
			initSnapAutoAdvance( {
				root:        root,
				getActive:   getActive,
				getMaxIndex: maxIndex,
				goTo:        scrollToCard,
			} );
		}
	} );
}() );
