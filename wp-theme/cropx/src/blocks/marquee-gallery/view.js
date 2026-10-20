/**
 * Text + Scrolling Photo Gallery — front-end interaction.
 *
 *  • Scroll-reveal for the text column + gallery (shared utility).
 *  • Pause / play button (WCAG 2.2.2 — auto-moving content needs a pause).
 *  • Off-screen pause: the CSS animations only run while the section is
 *    actually on screen, so a long page doesn't keep compositing a marquee
 *    nobody can see.
 *  • Optional drag / swipe (data-draggable="1"): each vertical column, or the
 *    horizontal row, can be dragged to scroll it faster. The CSS keyframe
 *    animation is swapped for a small requestAnimationFrame loop that adds the
 *    visitor's drag + momentum on top of the normal auto-scroll speed, so both
 *    share one position and the loop stays seamless.
 */

import { initScrollReveal } from '../../shared/scrollReveal';

initScrollReveal( '.mg-section' );

function initMarquee( section ) {
	const btn = section.querySelector( '.mg-pause' );
	if ( btn ) {
		btn.addEventListener( 'click', () => {
			const paused = section.classList.toggle( 'is-paused' );
			btn.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
			btn.setAttribute(
				'aria-label',
				paused ? btn.dataset.labelPlay : btn.dataset.labelPause
			);
		} );
	}

	if ( 'IntersectionObserver' in window ) {
		const io = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					section.classList.toggle( 'is-offscreen', ! entry.isIntersecting );
				} );
			},
			{ rootMargin: '80px 0px' }
		);
		io.observe( section );
	}
}

/**
 * Drag / swipe + JS-driven marquee for one section.
 * Skipped for reduced-motion (the CSS already makes those windows natively
 * scrollable) and when the editor switched the option off.
 */
function initDrag( section ) {
	if ( section.dataset.draggable !== '1' ) return;
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	const gallery = section.querySelector( '.mg-gallery' );
	const trackEls = Array.from( section.querySelectorAll( '.mg-track' ) );
	if ( ! gallery || ! trackEls.length ) return;

	// Read the CSS animation's duration + direction BEFORE switching it off.
	const tracks = trackEls.map( ( el ) => {
		const cs = window.getComputedStyle( el );
		const dur = parseFloat( cs.animationDuration ) || 40;
		const reverse = cs.animationDirection === 'reverse';
		const axis = el.classList.contains( 'mg-track--x' ) ? 'x' : 'y';
		const t = {
			el,
			container: el.parentElement,
			axis,
			dur,
			reverse,
			half: 0,
			offset: 0,
			userVel: 0,
			phaseSet: false,
		};
		return t;
	} );
	section.classList.add( 'mg-js' );

	// Measure one copy of the set (= half the track) whenever sizes change.
	// A hidden layout (display:none) measures 0 and is simply skipped.
	const measure = ( t ) => {
		const set = t.el.querySelector( '.mg-set' );
		if ( ! set ) return;
		const size = t.axis === 'y' ? set.offsetHeight : set.offsetWidth;
		t.half = size;
		if ( size > 0 && ! t.phaseSet ) {
			// Mirror the CSS phase offset: opposite-direction tracks start part-way.
			t.offset = t.reverse ? -size * 0.63 : 0;
			t.phaseSet = true;
		}
	};
	const ro = 'ResizeObserver' in window ? new ResizeObserver( ( entries ) => {
		entries.forEach( ( e ) => {
			const t = tracks.find( ( x ) => x.el.contains( e.target ) );
			if ( t ) measure( t );
		} );
	} ) : null;
	tracks.forEach( ( t ) => {
		measure( t );
		const set = t.el.querySelector( '.mg-set' );
		if ( ro && set ) ro.observe( set );
	} );
	window.addEventListener( 'resize', () => tracks.forEach( measure ) );

	const wrap = ( t ) => {
		if ( t.half <= 0 ) return;
		while ( t.offset <= -t.half ) t.offset += t.half;
		while ( t.offset > 0 ) t.offset -= t.half;
	};

	// ── Pointer drag ──
	let dragging = null; // { t, id, last, startX, startY, active, samples }
	const THRESHOLD = 6;

	const posOf = ( t, e ) => ( t.axis === 'y' ? e.clientY : e.clientX );

	tracks.forEach( ( t ) => {
		const c = t.container;

		c.addEventListener( 'dragstart', ( e ) => e.preventDefault() ); // no native link/image ghost-drag

		c.addEventListener( 'pointerdown', ( e ) => {
			if ( e.pointerType === 'mouse' && e.button !== 0 ) return;
			if ( t.half <= 0 ) return;
			dragging = {
				t,
				id: e.pointerId,
				last: posOf( t, e ),
				startX: e.clientX,
				startY: e.clientY,
				active: false,
				samples: [ { time: performance.now(), pos: posOf( t, e ) } ],
			};
			t.userVel = 0;
		} );

		c.addEventListener( 'pointermove', ( e ) => {
			if ( ! dragging || dragging.t !== t || e.pointerId !== dragging.id ) return;
			if ( ! dragging.active ) {
				const dx = Math.abs( e.clientX - dragging.startX );
				const dy = Math.abs( e.clientY - dragging.startY );
				const along = t.axis === 'y' ? dy : dx;
				const across = t.axis === 'y' ? dx : dy;
				if ( along < THRESHOLD || along < across ) return;
				dragging.active = true;
				section.classList.add( 'is-dragging' );
				// Capture only once it's really a drag, so plain clicks on
				// product links still reach the link.
				try { c.setPointerCapture( e.pointerId ); } catch ( err ) { /* noop */ }
			}
			const p = posOf( t, e );
			t.offset += p - dragging.last;
			dragging.last = p;
			wrap( t );
			const now = performance.now();
			dragging.samples.push( { time: now, pos: p } );
			while ( dragging.samples.length > 2 && now - dragging.samples[ 0 ].time > 100 ) dragging.samples.shift();
		} );

		const end = ( e ) => {
			if ( ! dragging || dragging.t !== t || e.pointerId !== dragging.id ) return;
			if ( dragging.active ) {
				const s = dragging.samples;
				const a = s[ 0 ];
				const b = s[ s.length - 1 ];
				const dt = ( b.time - a.time ) / 1000;
				if ( dt > 0.01 ) {
					t.userVel = Math.max( -3000, Math.min( 3000, ( b.pos - a.pos ) / dt ) );
				}
				// Swallow the click that follows a drag so a drag-release never
				// opens a product link.
				const swallow = ( ev ) => { ev.preventDefault(); ev.stopPropagation(); };
				c.addEventListener( 'click', swallow, { capture: true, once: true } );
				setTimeout( () => c.removeEventListener( 'click', swallow, { capture: true } ), 0 );
			}
			if ( dragging.active ) hoverSuppressed = true;
			section.classList.remove( 'is-dragging' );
			dragging = null;
		};
		c.addEventListener( 'pointerup', end );
		c.addEventListener( 'pointercancel', end );
		c.addEventListener( 'lostpointercapture', end );

		// Horizontal trackpad swipe / shift-wheel on the row. Vertical wheel is
		// deliberately left alone so the page can still scroll over the gallery.
		if ( t.axis === 'x' ) {
			c.addEventListener( 'wheel', ( e ) => {
				if ( Math.abs( e.deltaX ) > Math.abs( e.deltaY ) && t.half > 0 ) {
					e.preventDefault();
					t.offset -= e.deltaX;
					wrap( t );
				}
			}, { passive: false } );
		}
	} );

	// ── Animation loop ──
	const canHover = window.matchMedia( '(hover: hover)' ).matches;

	// After a drag, don't let the (still-hovering) pointer hold the auto-scroll
	// paused — resume on release, and only re-arm hover-pause once the pointer
	// has left the gallery and come back.
	let hoverSuppressed = false;
	gallery.addEventListener( 'mouseleave', () => { hoverSuppressed = false; } );
	let prev = performance.now();
	const frame = ( now ) => {
		const dt = Math.min( 0.05, ( now - prev ) / 1000 );
		prev = now;

		const offscreen = section.classList.contains( 'is-offscreen' );
		if ( ! offscreen ) {
			const paused = section.classList.contains( 'is-paused' );
			const hoverPause =
				section.classList.contains( 'mg-section--pause-hover' ) &&
				( ( canHover && ! hoverSuppressed && gallery.matches( ':hover' ) ) ||
					// Keyboard focus only. A mouse click on a product link also gives it
					// (non-visible) focus, which must NOT freeze the gallery forever.
					!! gallery.querySelector( ':focus-visible' ) );

			tracks.forEach( ( t ) => {
				if ( t.half <= 0 ) return;
				const isDragging = dragging && dragging.t === t && dragging.active;
				if ( ! isDragging ) {
					const auto = t.half / t.dur; // px per second
					let v = 0;
					if ( ! paused && ! hoverPause ) v += t.reverse ? auto : -auto;
					v += t.userVel;
					t.offset += v * dt;
					// Momentum decays; auto-scroll then carries on.
					t.userVel *= Math.exp( -dt * 2.5 );
					if ( Math.abs( t.userVel ) < 4 ) t.userVel = 0;
					wrap( t );
				}
				t.el.style.transform =
					t.axis === 'y'
						? `translate3d(0, ${ t.offset }px, 0)`
						: `translate3d(${ t.offset }px, 0, 0)`;
			} );
		}
		window.requestAnimationFrame( frame );
	};
	window.requestAnimationFrame( frame );
}

document.querySelectorAll( '.mg-section' ).forEach( ( section ) => {
	initMarquee( section );
	initDrag( section );
} );
