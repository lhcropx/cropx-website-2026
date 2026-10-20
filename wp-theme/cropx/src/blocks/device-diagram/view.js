import { initScrollReveal } from '../../shared/scrollReveal';
import { drawLines } from './lines';

/*
 * Interactive Device Diagram — front-end controller.
 *
 * One "active product" state (index, or -1 for none) with two inputs:
 *   • hover / keyboard focus on a card   — desktop diagram
 *   • the card centred in the scroll-snap carousel (swipe, or the prev/next
 *     arrows that scroll it) — touch / narrow layout
 * Everything visual (card scale + dimming, the single prominent line, the phone
 * screen) is CSS driven off the .is-active / .has-active classes this file
 * sets — see style.css.
 *
 * The carousel loops: while it is the active layout, aria-hidden/inert clones
 * of the last and first cards are added on either side of the real cards, so
 * the first card always has "last" on its left and the last card has "first"
 * on its right. When a scroll settles on a clone we silently jump to the real
 * card it mirrors (they're pixel-identical, so there's no visible jump).
 *
 * MQ must match the layout media query in style.css EXACTLY.
 */
const MQ = '(max-width: 900px), (hover: none), (pointer: coarse)';
const RELEASE_MS = 250; // grace period before the diagram returns to rest on pointer-leave
const SETTLE_MS = 120; // scroll-idle time before we treat the carousel as settled
const CLONES = 2; // clones on each side (enough for tablet-width viewports)

const CHEVRON_L = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
const CHEVRON_R = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';

function initBlock( block ) {
	const stage = block.querySelector( '.dd-stage' );
	const track = stage?.querySelector( '.dd-track' );
	const carousel = stage?.querySelector( '.dd-carousel' );
	if ( ! stage || ! track || ! carousel ) {
		return;
	}

	const items = Array.from( stage.querySelectorAll( '.dd-item' ) ); // real cards only (clones are added later)
	const layers = Array.from( stage.querySelectorAll( '.dd-screen-layer' ) );
	const mq = window.matchMedia( MQ );
	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	let active = -2; // sentinel so the first setActive() always runs
	let releaseTimer = null;
	let settleTimer = null;
	let clones = [];
	let status = null;

	block.classList.add( 'dd-js' );

	/* ── Single source of truth ── */
	function setActive( i ) {
		if ( i === active ) {
			return;
		}
		active = i;

		stage.classList.toggle( 'has-active', i >= 0 );
		// Real cards AND their clones, so a centred clone looks active too.
		stage.querySelectorAll( '.dd-item' ).forEach( ( el ) => {
			const on = Number( el.dataset.i ) === i;
			el.classList.toggle( 'is-active', on );
			const card = el.querySelector( '.dd-card' );
			// aria-current only means something while the carousel is the UI.
			if ( on && mq.matches && ! el.classList.contains( 'dd-clone' ) ) {
				card?.setAttribute( 'aria-current', 'true' );
			} else {
				card?.removeAttribute( 'aria-current' );
			}
		} );
		// Products without their own screen image have no layer — show the default
		// screen (-1) instead, so a single image can stay up through every interaction.
		const screenIdx = layers.some( ( el ) => Number( el.dataset.i ) === i ) ? i : -1;
		layers.forEach( ( el ) => el.classList.toggle( 'is-active', Number( el.dataset.i ) === screenIdx ) );
		stage.querySelectorAll( '.dd-lines path' ).forEach( ( p ) => {
			p.classList.toggle( 'is-active', Number( p.dataset.i ) === i );
		} );

		announce( i );
	}

	function announce( i ) {
		if ( ! status ) {
			return;
		}
		// Only announce while the carousel is the UI (not desktop hover).
		if ( mq.matches && i >= 0 ) {
			const name = items[ i ].querySelector( '.dd-card-name' )?.textContent?.replace( / /g, ' ' ).trim() || '';
			status.textContent = `${ name }, ${ i + 1 } of ${ items.length }`;
		} else {
			status.textContent = '';
		}
	}

	/* ── Desktop: hover / focus ── */
	function hold() {
		clearTimeout( releaseTimer );
	}
	function release() {
		clearTimeout( releaseTimer );
		releaseTimer = setTimeout( () => {
			if ( ! mq.matches ) {
				setActive( -1 );
			}
		}, RELEASE_MS );
	}

	items.forEach( ( el ) => {
		const i = Number( el.dataset.i );
		const card = el.querySelector( '.dd-card' );
		card.addEventListener( 'mouseenter', () => {
			if ( mq.matches ) {
				return;
			}
			hold();
			setActive( i );
		} );
		card.addEventListener( 'focus', () => {
			hold();
			// In carousel mode the focused card may be off-centre; the browser
			// scrolls it into view, and the scroll handler then updates active.
			if ( ! mq.matches ) {
				setActive( i );
			}
		} );
	} );
	// The whole diagram is the hover zone with a short grace period, so the
	// pointer can cross the gaps between cards without everything flickering
	// back to rest.
	stage.addEventListener( 'mouseenter', () => {
		if ( ! mq.matches ) {
			hold();
		}
	} );
	stage.addEventListener( 'mouseleave', () => {
		if ( ! mq.matches ) {
			release();
		}
	} );
	stage.addEventListener( 'focusout', ( e ) => {
		if ( ! mq.matches && ! stage.contains( e.relatedTarget ) ) {
			release();
		}
	} );

	/* ── Touch / narrow: looping carousel ── */
	function addClones() {
		removeClones();
		const n = items.length;
		const k = Math.min( CLONES, n );
		const make = ( src ) => {
			const c = src.cloneNode( true );
			c.classList.add( 'dd-clone' );
			c.classList.remove( 'reveal-item' ); // already revealed; avoid the fade-in re-running
			c.setAttribute( 'aria-hidden', 'true' );
			c.setAttribute( 'inert', '' );
			c.querySelectorAll( '[id]' ).forEach( ( e ) => e.removeAttribute( 'id' ) );
			const a = c.querySelector( '.dd-card' );
			a?.removeAttribute( 'aria-labelledby' );
			a?.removeAttribute( 'aria-describedby' );
			a?.setAttribute( 'tabindex', '-1' );
			return c;
		};
		const before = items.slice( n - k ).map( make );
		const after = items.slice( 0, k ).map( make );
		track.prepend( ...before );
		track.append( ...after );
		clones = [ ...before, ...after ];
	}

	function removeClones() {
		clones.forEach( ( c ) => c.remove() );
		clones = [];
	}

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
			if ( d < bestDist ) {
				bestDist = d;
				best = el;
			}
		} );
		return best;
	}

	function jumpTo( el ) {
		track.scrollTo( { left: scrollLeftFor( el ), behavior: 'auto' } );
	}

	function step( dir ) {
		const el = centredEl();
		const next = dir < 0 ? el?.previousElementSibling : el?.nextElementSibling;
		if ( ! next ) {
			return;
		}
		track.scrollTo( {
			left: scrollLeftFor( next ),
			behavior: reduceMotion.matches ? 'auto' : 'smooth',
		} );
	}

	// Called once scrolling has been idle: if we landed on a clone, swap to the
	// real card it mirrors (identical pixels, so the user sees nothing).
	function settle() {
		if ( ! mq.matches ) {
			return;
		}
		const el = centredEl();
		if ( el && el.classList.contains( 'dd-clone' ) ) {
			jumpTo( items[ Number( el.dataset.i ) ] );
		}
	}

	let ticking = false;
	function onScroll() {
		if ( ! mq.matches ) {
			return;
		}
		clearTimeout( settleTimer );
		settleTimer = setTimeout( settle, SETTLE_MS );
		if ( ticking ) {
			return;
		}
		ticking = true;
		requestAnimationFrame( () => {
			ticking = false;
			const el = centredEl();
			if ( el ) {
				setActive( Number( el.dataset.i ) );
			}
		} );
	}
	track.addEventListener( 'scroll', onScroll, { passive: true } );

	function makeArrow( dir ) {
		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = `dd-arrow dd-arrow--${ dir }`;
		btn.setAttribute( 'aria-label', dir === 'prev' ? 'Previous product' : 'Next product' );
		btn.innerHTML = dir === 'prev' ? CHEVRON_L : CHEVRON_R;
		btn.addEventListener( 'click', () => step( dir === 'prev' ? -1 : 1 ) );
		return btn;
	}

	function buildControls() {
		status = document.createElement( 'span' );
		status.className = 'dd-status';
		status.setAttribute( 'aria-live', 'polite' );
		status.setAttribute( 'aria-atomic', 'true' );
		carousel.append( makeArrow( 'prev' ), makeArrow( 'next' ), status );
	}

	/* ── Mode switching (resize, rotate, pointer type change) ── */
	function applyMode() {
		clearTimeout( releaseTimer );
		clearTimeout( settleTimer );
		active = -2;
		if ( mq.matches ) {
			addClones();
			// Wait a frame so the carousel layout has applied before measuring.
			requestAnimationFrame( () => {
				jumpTo( items[ 0 ] );
				setActive( 0 );
			} );
		} else {
			removeClones();
			track.scrollLeft = 0;
			setActive( -1 );
			requestAnimationFrame( () => drawLines( stage, active ) );
		}
	}

	buildControls();
	applyMode();
	if ( mq.addEventListener ) {
		mq.addEventListener( 'change', applyMode );
	} else if ( mq.addListener ) {
		mq.addListener( applyMode ); // older Safari
	}

	/* ── Redraw / re-centre on any size change / late font + image load ── */
	const redraw = () => {
		if ( mq.matches ) {
			// Keep the current card centred when the viewport changes (rotation etc.).
			if ( active >= 0 ) {
				jumpTo( items[ active ] );
			}
		} else {
			drawLines( stage, active );
		}
	};
	if ( typeof ResizeObserver !== 'undefined' ) {
		new ResizeObserver( redraw ).observe( stage );
	} else {
		window.addEventListener( 'resize', redraw );
	}
	window.addEventListener( 'load', redraw );
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( redraw );
	}
}

document.querySelectorAll( '.dd-block' ).forEach( initBlock );
initScrollReveal( '.dd-block' );
