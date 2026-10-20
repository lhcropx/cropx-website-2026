/**
 * Hero Carousel — Curved Edge: view.js
 *
 * 1. Swoop fill: same as hero-curved-standard — reads the computed background
 *    of the block that follows the hero and writes it to --hcr-swoop-fill so
 *    the curve blends into the next section.
 * 2. Carousel: crossfades between sets (CSS does the fade; this only toggles
 *    .is-active on the matching slide / text layer / dot). Auto-advance pauses
 *    on hover, keyboard focus, when scrolled off-screen, in a background tab,
 *    and permanently once the visitor presses the pause button. Under
 *    prefers-reduced-motion it never auto-advances (dots still work).
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-cropx-hero-carousel' ).forEach( ( block ) => {
		// ── Swoop fill ──────────────────────────────────────────────────────
		const next = block.nextElementSibling;
		if ( next ) {
			const bg = getComputedStyle( next ).backgroundColor;
			if ( bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent' ) {
				const wrap = block.querySelector( '.hcr-bleed-wrap' );
				if ( wrap ) {
					wrap.style.setProperty( '--hcr-swoop-fill', bg );
				}
			}
		}

		// ── Carousel ────────────────────────────────────────────────────────
		if ( block.dataset.hcrCarousel !== '1' ) {
			return;
		}

		const slides   = Array.from( block.querySelectorAll( '.hcr-slide' ) );
		const contents = Array.from( block.querySelectorAll( '.hcr-content[data-slide]' ) ); // none when text is shared
		const dots     = Array.from( block.querySelectorAll( '.hcr-dot' ) );
		const pauseBtn = block.querySelector( '.hcr-pause' );
		const hero     = block.querySelector( '.hcr-hero' );
		if ( slides.length < 2 || ! hero ) {
			return;
		}

		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const intervalMs   = Math.max( 3, parseInt( block.dataset.hcrInterval, 10 ) || 6 ) * 1000;
		const wantsAuto    = block.dataset.hcrAutoplay === '1' && ! reduceMotion;

		let current      = 0;
		let userPaused   = false;
		let hoverPaused  = false;
		let focusPaused  = false;
		let inView       = true;
		let timer        = null;

		const setActive = ( list, i ) => {
			list.forEach( ( el, idx ) => {
				const on = idx === i;
				el.classList.toggle( 'is-active', on );
				if ( el.classList.contains( 'hcr-content' ) ) {
					el.setAttribute( 'aria-hidden', on ? 'false' : 'true' );
					// Keep hidden layers' links out of the tab order.
					el.querySelectorAll( 'a' ).forEach( ( a ) => {
						if ( on ) { a.removeAttribute( 'tabindex' ); } else { a.setAttribute( 'tabindex', '-1' ); }
					} );
				}
			} );
		};

		const goTo = ( i ) => {
			current = ( i + slides.length ) % slides.length;
			setActive( slides, current );
			if ( contents.length ) {
				setActive( contents, current );
			}
			dots.forEach( ( d, idx ) => {
				const on = idx === current;
				d.classList.toggle( 'is-active', on );
				if ( on ) { d.setAttribute( 'aria-current', 'true' ); } else { d.removeAttribute( 'aria-current' ); }
			} );
		};

		const canRun = () =>
			wantsAuto && ! userPaused && ! hoverPaused && ! focusPaused && inView && ! document.hidden;

		const stop = () => {
			if ( timer ) { clearInterval( timer ); timer = null; }
		};
		const start = () => {
			stop();
			if ( ! canRun() ) { return; }
			timer = setInterval( () => {
				if ( canRun() ) { goTo( current + 1 ); }
			}, intervalMs );
		};

		dots.forEach( ( d ) => {
			d.addEventListener( 'click', () => {
				goTo( parseInt( d.dataset.go, 10 ) || 0 );
				start(); // restart the countdown from the chosen slide
			} );
		} );

		if ( pauseBtn ) {
			pauseBtn.addEventListener( 'click', () => {
				userPaused = ! userPaused;
				pauseBtn.setAttribute( 'aria-pressed', userPaused ? 'true' : 'false' );
				pauseBtn.setAttribute(
					'aria-label',
					userPaused ? pauseBtn.dataset.labelPlay : pauseBtn.dataset.labelPause
				);
				start();
			} );
		}

		hero.addEventListener( 'mouseenter', () => { hoverPaused = true; } );
		hero.addEventListener( 'mouseleave', () => { hoverPaused = false; start(); } );
		hero.addEventListener( 'focusin',    () => { focusPaused = true; } );
		hero.addEventListener( 'focusout',   () => { focusPaused = false; start(); } );
		document.addEventListener( 'visibilitychange', start );

		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( ( entries ) => {
				entries.forEach( ( e ) => { inView = e.isIntersecting; } );
				start();
			}, { threshold: 0.2 } ).observe( hero );
		}

		start();
	} );
} );
