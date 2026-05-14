( function () {
	const PIXELS_PER_SEC   = 45;
	const RESUME_DELAY_MS  = 1500;
	const SMOOTH_SCROLL_MS = 600;
	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '.hwc-marquee' ).forEach( ( marquee ) => {
		const track     = marquee.querySelector( '.hwc-track' );
		const originals = Array.from( track.children );
		const N         = originals.length;

		// Prepend reversed clones + append forward clones for seamless looping.
		originals.slice().reverse().forEach( ( node ) => {
			const c = node.cloneNode( true );
			c.setAttribute( 'aria-hidden', 'true' );
			c.tabIndex = -1;
			track.insertBefore( c, track.firstChild );
		} );
		originals.forEach( ( node ) => {
			const c = node.cloneNode( true );
			c.setAttribute( 'aria-hidden', 'true' );
			c.tabIndex = -1;
			track.appendChild( c );
		} );

		function getStep() {
			const cardW = originals[ 0 ].getBoundingClientRect().width;
			const gap   = parseFloat( getComputedStyle( track ).gap ) || 0;
			return cardW + gap;
		}
		function getHalfTrack() { return N * getStep(); }

		let halfTrack = getHalfTrack();
		function center() { marquee.scrollLeft = halfTrack; }
		center();
		marquee.classList.add( 'is-ready' );

		// Suppress normalizeScroll during programmatic smooth scrolls so they
		// don't get yanked mid-flight when crossing a loop boundary.
		let normalizeSuspendedUntil = 0;
		function normalizeScroll() {
			if ( performance.now() < normalizeSuspendedUntil ) return;
			const sl = marquee.scrollLeft;
			if ( sl >= 2 * halfTrack )  marquee.scrollLeft = sl - halfTrack;
			else if ( sl < halfTrack )  marquee.scrollLeft = sl + halfTrack;
		}
		marquee.addEventListener( 'scroll', normalizeScroll, { passive: true } );

		// ── Pause / resume bookkeeping ──
		let isHoverPaused   = false;
		let lastInteraction = 0;
		function shouldAutoScroll() {
			if ( reduce ) return false;
			if ( isHoverPaused ) return false;
			return performance.now() - lastInteraction > RESUME_DELAY_MS;
		}
		marquee.addEventListener( 'mouseenter', () => { isHoverPaused = true; } );
		marquee.addEventListener( 'mouseleave', () => { isHoverPaused = false; } );
		[ 'pointerdown', 'touchstart', 'wheel', 'keydown' ].forEach( ( evt ) => {
			marquee.addEventListener( evt, () => { lastInteraction = performance.now(); }, { passive: true } );
		} );

		// ── Auto-scroll RAF loop ──
		let acc = 0, lastTs = 0;
		function tick( ts ) {
			if ( ! lastTs ) lastTs = ts;
			const dt = ( ts - lastTs ) / 1000;
			lastTs = ts;
			if ( shouldAutoScroll() ) {
				acc += PIXELS_PER_SEC * dt;
				const inc = Math.floor( acc );
				if ( inc > 0 ) {
					marquee.scrollLeft += inc;
					acc -= inc;
				}
			} else {
				acc = 0;
			}
			requestAnimationFrame( tick );
		}
		requestAnimationFrame( tick );

		window.addEventListener( 'resize', () => {
			const oldHalf = halfTrack;
			halfTrack = getHalfTrack();
			marquee.scrollLeft = ( marquee.scrollLeft / oldHalf ) * halfTrack;
		} );

		// ── Arrow buttons — visible on non-touch only via CSS ──
		const root    = marquee.parentElement; // .hwf-section (block wrapper)
		const prevBtn = root.querySelector( '.hwc-prev' );
		const nextBtn = root.querySelector( '.hwc-next' );

		function jumpByOneCard( direction ) {
			lastInteraction = performance.now();

			const step = getStep();
			// Pre-position so smooth scroll won't cross a loop boundary mid-animation.
			const sl = marquee.scrollLeft;
			if ( direction > 0 && sl + step >= 2 * halfTrack ) {
				marquee.scrollLeft = sl - halfTrack;
			} else if ( direction < 0 && sl - step < halfTrack ) {
				marquee.scrollLeft = sl + halfTrack;
			}

			normalizeSuspendedUntil = performance.now() + SMOOTH_SCROLL_MS;
			marquee.scrollBy( { left: direction * step, behavior: 'smooth' } );
		}

		if ( prevBtn ) prevBtn.addEventListener( 'click', () => jumpByOneCard( -1 ) );
		if ( nextBtn ) nextBtn.addEventListener( 'click', () => jumpByOneCard( 1 ) );
	} );
}() );
