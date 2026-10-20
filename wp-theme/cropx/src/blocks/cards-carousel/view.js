import { initSnapAutoAdvance } from '../../shared/autoAdvance';
import { initScrollReveal } from '../../shared/scrollReveal';

( function () {
	/*
	 * Content Card Carousel — front-end controller.
	 *
	 * Loops endlessly, the same way the Interactive Device Diagram's carousel
	 * does: aria-hidden / inert clones of the last and first cards are added on
	 * either side of the real cards, so the first card always has "last" on its
	 * left and the last card has "first" on its right. When a scroll settles on
	 * a clone we silently jump to the real card it mirrors (pixel-identical, so
	 * nothing visibly moves). Native scroll-snap still handles touch / trackpad
	 * swipes; the arrows never disable.
	 *
	 * Dots: one per real card, generated here so markup never needs updating.
	 *
	 * Auto-advance (optional, editor toggle): data-cc-auto-advance="true"
	 * layers a timer on top via the shared initSnapAutoAdvance() helper; it
	 * always steps forward, so it loops too.
	 */

	const EASINGS = {
		sine:  ( t ) => -( Math.cos( Math.PI * t ) - 1 ) / 2,
		cubic: ( t ) => ( t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2 ),
		out:   ( t ) => 1 - Math.pow( 1 - t, 3 ),
		quint: ( t ) => ( t < 0.5 ? 16 * t * t * t * t * t : 1 - Math.pow( -2 * t + 2, 5 ) / 2 ),
	};
	const DURATIONS = { quick: 600, relaxed: 900, slow: 1300, 'very-slow': 1800 };

	const CLONES    = 2;   // clones on each side (enough for any viewport width)
	const SETTLE_MS = 120; // scroll-idle time before we treat the carousel as settled

	initScrollReveal( '.wp-block-cropx-cards-carousel' );

	document.querySelectorAll( '.cccarousel' ).forEach( ( root ) => {
		const track    = root.querySelector( '.cccarousel-track' );
		const prevBtn  = root.querySelector( '.cccarousel-prev' );
		const nextBtn  = root.querySelector( '.cccarousel-next' );
		const dotsWrap = root.querySelector( '.cccarousel-dots' );

		if ( ! track || ! track.children.length ) return;

		const items = Array.from( track.children ); // real cards only (clones are added below)
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

		// Movement speed / easing (editor options). 'default' keeps the
		// browser's own smooth scroll; anything else runs our own timed,
		// eased animation so it can be slower and softer.
		const sectionEl = root.closest( '.cc-section' );
		const speedKey  = sectionEl?.dataset.ccSpeed || 'default';
		const durMs     = DURATIONS[ speedKey ] || 0;
		const ease      = EASINGS[ sectionEl?.dataset.ccEase ] || EASINGS.sine;
		let animRaf = null;

		function cancelAnim() {
			if ( animRaf ) {
				cancelAnimationFrame( animRaf );
				animRaf = null;
				track.style.scrollSnapType = '';
				track.style.scrollBehavior = '';
			}
		}
		// Any hands-on input takes over immediately.
		[ 'pointerdown', 'touchstart', 'wheel' ].forEach( ( evt ) => {
			track.addEventListener( evt, cancelAnim, { passive: true } );
		} );

		function scrollToX( left ) {
			if ( reduceMotion.matches ) {
				track.scrollTo( { left, behavior: 'instant' } );
				return;
			}
			if ( ! durMs ) {
				track.scrollTo( { left, behavior: 'smooth' } );
				return;
			}
			cancelAnim();
			const start = track.scrollLeft;
			const dist  = left - start;
			if ( Math.abs( dist ) < 1 ) return;
			// Snap + native smoothing would fight a frame-by-frame animation.
			track.style.scrollSnapType = 'none';
			track.style.scrollBehavior = 'auto';
			const t0 = performance.now();
			const tick = ( now ) => {
				const p = Math.min( 1, ( now - t0 ) / durMs );
				track.scrollLeft = start + dist * ease( p );
				if ( p < 1 ) {
					animRaf = requestAnimationFrame( tick );
				} else {
					animRaf = null;
					track.style.scrollSnapType = '';
					track.style.scrollBehavior = '';
				}
			};
			animRaf = requestAnimationFrame( tick );
		}
		let dots = [];
		let active = 0;
		let settleTimer = null;

		/* ── Clones ── */
		function makeClone( src ) {
			const c = src.cloneNode( true );
			c.classList.add( 'cc-clone' );
			c.classList.remove( 'reveal-item' ); // already revealed; avoid the fade-in re-running
			c.setAttribute( 'aria-hidden', 'true' );
			c.setAttribute( 'inert', '' );
			c.querySelectorAll( '[id]' ).forEach( ( e ) => e.removeAttribute( 'id' ) );
			c.querySelectorAll( 'a, button' ).forEach( ( e ) => e.setAttribute( 'tabindex', '-1' ) );
			return c;
		}
		const k = Math.min( CLONES, count );
		const before = items.slice( count - k ).map( makeClone );
		const after  = items.slice( 0, k ).map( makeClone );
		track.prepend( ...before );
		track.append( ...after );

		/* ── Geometry ── */
		function scrollLeftFor( el ) {
			return el.offsetLeft - ( track.clientWidth - el.offsetWidth ) / 2;
		}

		// The card (real or clone) whose centre is nearest the track's centre.
		function centredEl() {
			const mid = track.scrollLeft + track.clientWidth / 2;
			let best = null;
			let bestDist = Infinity;
			Array.from( track.children ).forEach( ( el ) => {
				const d = Math.abs( el.offsetLeft + el.offsetWidth / 2 - mid );
				if ( d < bestDist ) { bestDist = d; best = el; }
			} );
			return best;
		}

		function jumpTo( el ) {
			track.scrollTo( { left: scrollLeftFor( el ), behavior: 'instant' } );
		}

		/* ── Dots ── */
		function renderDots() {
			dotsWrap.innerHTML = '';
			for ( let i = 0; i < count; i++ ) {
				const d = document.createElement( 'button' );
				d.className = 'cccarousel-dot' + ( i === 0 ? ' is-active' : '' );
				d.type = 'button';
				d.setAttribute( 'role', 'tab' );
				d.setAttribute( 'aria-label', `Go to card ${ i + 1 }` );
				d.addEventListener( 'click', () => goTo( i ) );
				dotsWrap.appendChild( d );
			}
			dots = Array.from( dotsWrap.querySelectorAll( '.cccarousel-dot' ) );
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
			scrollToX( scrollLeftFor( items[ i ] ) );
		}

		function step( dir ) {
			const el = centredEl();
			const next = dir < 0 ? el?.previousElementSibling : el?.nextElementSibling;
			if ( ! next ) return;
			scrollToX( scrollLeftFor( next ) );
		}

		// Called once scrolling has been idle: if we landed on a clone, swap to the
		// real card it mirrors (identical pixels, so the user sees nothing).
		function settle() {
			const el = centredEl();
			if ( el && el.classList.contains( 'cc-clone' ) ) {
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
				const el = centredEl();
				if ( el ) {
					const i = Number( el.dataset.i );
					if ( i !== active ) setActive( i );
				}
			} );
		}, { passive: true } );

		prevBtn?.addEventListener( 'click', () => step( -1 ) );
		nextBtn?.addEventListener( 'click', () => step( 1 ) );

		// Keep the current card centred when the viewport changes (rotation, resize).
		let resizeTimer = null;
		window.addEventListener( 'resize', () => {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( () => jumpTo( items[ active ] ), 100 );
		} );

		renderDots();
		// Wait a frame so layout (track padding, card widths) has applied before measuring.
		requestAnimationFrame( () => {
			jumpTo( items[ 0 ] );
			setActive( 0 );
		} );

		const section = root.closest( '.cc-section' );
		const holdSec = Math.max( 2, Math.min( 10, parseFloat( section?.dataset.ccHold ) || 5 ) );
		if ( section && section.dataset.ccAutoAdvance === 'true' ) {
			initSnapAutoAdvance( {
				root:        root,
				getActive:   () => active,
				getMaxIndex: () => count - 1,
				goTo:        () => step( 1 ), // always forward — the loop handles the wrap
				// Hold time = how long the card RESTS, so add the move time.
				intervalMs:  Math.round( holdSec * 1000 ) + ( durMs || 700 ),
			} );
		}
	} );
}() );
