import { initSnapAutoAdvance } from '../../shared/autoAdvance';
import { initScrollReveal } from '../../shared/scrollReveal';

( function () {
	/*
	 * Testimonials Carousel — front-end controller.
	 *
	 * Manual nav via arrows + dots. Native scroll-snap handles touch /
	 * trackpad swipe. Arrows step one card; dots jump to a specific snap index.
	 * Loops endlessly (like the Content Card Carousel): cloned cards sit on
	 * both sides and a silent jump to the identical real card happens once a
	 * scroll settles on a clone. Arrows never disable. Dots are generated from the actual card
	 * count so the markup never needs updating when cards are added.
	 *
	 * scrollToCard uses scrollIntoView so the snap engine resolves the final
	 * resting position — no manual offset maths needed. syncDots is called after
	 * a 400ms delay so the scroll has time to settle before the dot updates.
	 *
	 * Auto-advance (optional, editor toggle): when the section's
	 * data-tc-auto-advance="true", layers a timer on top via the shared
	 * initSnapAutoAdvance() helper — see src/shared/autoAdvance.js.
	 *
	 * Also wires up the scroll-triggered reveal animation (eyebrow/heading/
	 * cards fading up as the section scrolls into view) via the shared
	 * initScrollReveal() helper — see src/shared/scrollReveal.js.
	 */

	const CLONES    = 6;   // minimum clones each side (cycled, so short sets still fill wide screens)
	const SETTLE_MS = 120; // scroll-idle time before we treat the carousel as settled

	initScrollReveal( '.wp-block-cropx-testimonials-carousel' );

	document.querySelectorAll( '.tcarousel' ).forEach( ( root ) => {
		const track    = root.querySelector( '.tcarousel-track' );
		const prevBtn  = root.querySelector( '.tcarousel-prev' );
		const nextBtn  = root.querySelector( '.tcarousel-next' );
		const dotsWrap = root.querySelector( '.tcarousel-dots' );

		if ( ! track ) return;

		const items = Array.from( track.children ); // real cards only (clones added below)
		const count = items.length;
		items.forEach( ( el, i ) => { el.dataset.i = String( i ); } );

		// Nothing to loop between with a single card.
		if ( count < 2 ) {
			prevBtn?.remove();
			nextBtn?.remove();
			dotsWrap?.remove();
			return;
		}

		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		let dots = [];
		let active = 0;
		let settleTimer = null;

		/* ── Clones (loop) ──
		   The track is full-bleed, so cards must exist on BOTH sides of the first
		   card to fill the peeking areas. Clones cycle through the real cards, so
		   even a short set (e.g. 4 cards) fills a very wide screen. */
		function makeClone( src ) {
			const c = src.cloneNode( true );
			c.classList.add( 'tc-clone' );
			c.setAttribute( 'aria-hidden', 'true' );
			c.setAttribute( 'inert', '' );
			c.querySelectorAll( '[id]' ).forEach( ( e ) => e.removeAttribute( 'id' ) );
			c.querySelectorAll( 'a, button' ).forEach( ( e ) => e.setAttribute( 'tabindex', '-1' ) );
			return c;
		}
		const K = Math.max( CLONES, count );
		const before = [];
		const after  = [];
		for ( let j = 0; j < K; j++ ) {
			before.unshift( makeClone( items[ ( ( count - 1 - j ) % count + count ) % count ] ) );
			after.push( makeClone( items[ j % count ] ) );
		}
		track.prepend( ...before );
		track.append( ...after );

		/* ── Geometry ── */
		function padLeft() {
			return parseFloat( getComputedStyle( track ).paddingLeft ) || 0;
		}
		// scrollLeft that puts `el` at the content column's left edge (the snap position).
		function scrollLeftFor( el ) {
			return el.offsetLeft - padLeft();
		}
		// The card (real or clone) sitting at the content column's left edge.
		function leadingEl() {
			const x = track.scrollLeft + padLeft();
			let best = null;
			let bestDist = Infinity;
			Array.from( track.children ).forEach( ( el ) => {
				const d = Math.abs( el.offsetLeft - x );
				if ( d < bestDist ) { bestDist = d; best = el; }
			} );
			return best;
		}
		function jumpTo( el ) {
			track.scrollTo( { left: scrollLeftFor( el ), behavior: 'instant' } );
		}

		/* ── Dots: one per real card ── */
		function renderDots() {
			dotsWrap.innerHTML = '';
			for ( let i = 0; i < count; i++ ) {
				const d = document.createElement( 'button' );
				d.className = 'tcarousel-dot' + ( i === active ? ' is-active' : '' );
				d.type      = 'button';
				d.setAttribute( 'role', 'tab' );
				d.setAttribute( 'aria-label', `Go to slide ${ i + 1 }` );
				d.addEventListener( 'click', () => goTo( i ) );
				dotsWrap.appendChild( d );
			}
			dots = Array.from( dotsWrap.querySelectorAll( '.tcarousel-dot' ) );
		}
		function setActive( i ) {
			active = i;
			dots.forEach( ( d, n ) => d.classList.toggle( 'is-active', n === i ) );
		}

		/* ── Navigation ── */
		function behavior() {
			return reduceMotion.matches ? 'instant' : 'smooth';
		}
		function goTo( i ) {
			track.scrollTo( { left: scrollLeftFor( items[ i ] ), behavior: behavior() } );
		}
		function step( dir ) {
			const el = leadingEl();
			const next = dir < 0 ? el?.previousElementSibling : el?.nextElementSibling;
			if ( ! next ) return;
			track.scrollTo( { left: scrollLeftFor( next ), behavior: behavior() } );
		}

		// Once scrolling is idle: if we landed on a clone, silently swap to the
		// identical real card so the loop never runs out of track.
		function settle() {
			const el = leadingEl();
			if ( el && el.classList.contains( 'tc-clone' ) ) {
				jumpTo( items[ Number( el.dataset.i ) ] );
			}
		}

		let ticking = false;
		track.addEventListener( 'scroll', () => {
			clearTimeout( settleTimer );
			settleTimer = setTimeout( settle, SETTLE_MS );
			if ( ticking ) return;
			ticking = true;
			requestAnimationFrame( () => {
				ticking = false;
				const el = leadingEl();
				if ( el ) {
					const i = Number( el.dataset.i );
					if ( i !== active ) setActive( i );
				}
			} );
		}, { passive: true } );

		prevBtn?.addEventListener( 'click', () => step( -1 ) );
		nextBtn?.addEventListener( 'click', () => step( 1 ) );

		let resizeTimer = null;
		window.addEventListener( 'resize', () => {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( () => jumpTo( items[ active ] ), 100 );
		} );

		renderDots();
		// Wait a frame so layout (padding, card widths) has applied before measuring.
		requestAnimationFrame( () => {
			jumpTo( items[ 0 ] );
			setActive( 0 );
		} );

		const section = root.closest( '.testimonials-section' );
		if ( section && section.dataset.tcAutoAdvance === 'true' ) {
			initSnapAutoAdvance( {
				root:        root,
				getActive:   () => active,
				getMaxIndex: () => count - 1,
				goTo:        () => step( 1 ), // always forward; the loop handles the wrap
			} );
		}
	} );
}() );
