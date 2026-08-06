import { initSnapAutoAdvance } from '../../shared/autoAdvance';

( function () {
	/*
	 * Testimonials Carousel — front-end controller.
	 *
	 * Manual nav via arrows + dots. Native scroll-snap handles touch /
	 * trackpad swipe. Arrows step one card; dots jump to a specific snap index.
	 * Arrows are disabled at boundaries (no loop, though auto-advance below
	 * wraps back to the first slide). Dots are generated from the actual card
	 * count so the markup never needs updating when cards are added.
	 *
	 * scrollToCard uses scrollIntoView so the snap engine resolves the final
	 * resting position — no manual offset maths needed. syncDots is called after
	 * a 400ms delay so the scroll has time to settle before the dot updates.
	 *
	 * Auto-advance (optional, editor toggle): when the section's
	 * data-tc-auto-advance="true", layers a timer on top via the shared
	 * initSnapAutoAdvance() helper — see src/shared/autoAdvance.js.
	 */

	document.querySelectorAll( '.tcarousel' ).forEach( ( root ) => {
		const track    = root.querySelector( '.tcarousel-track' );
		const prevBtn  = root.querySelector( '.tcarousel-prev' );
		const nextBtn  = root.querySelector( '.tcarousel-next' );
		const dotsWrap = root.querySelector( '.tcarousel-dots' );

		if ( ! track ) return;

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
				d.className = 'tcarousel-dot' + ( i === 0 ? ' is-active' : '' );
				d.type      = 'button';
				d.setAttribute( 'role', 'tab' );
				d.setAttribute( 'aria-label', `Go to slide ${ i + 1 }` );
				d.addEventListener( 'click', () => scrollToCard( i ) );
				dotsWrap.appendChild( d );
			}
			dots = Array.from( dotsWrap.querySelectorAll( '.tcarousel-dot' ) );
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
			items[ i ].scrollIntoView( { behavior: 'smooth', block: 'nearest', inline: 'start' } );
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

		const section = root.closest( '.testimonials-section' );
		if ( section && section.dataset.tcAutoAdvance === 'true' ) {
			initSnapAutoAdvance( {
				root:        root,
				getActive:   getActive,
				getMaxIndex: maxIndex,
				goTo:        scrollToCard,
			} );
		}
	} );
}() );
