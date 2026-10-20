/**
 * Video Hero — Curved Edge: view.js
 *
 * 1. Swoop fill: reads the computed background of the block that follows the
 *    hero and writes it to --hcv-swoop-fill so the curve blends into the next
 *    section (same as hero-curved-standard).
 * 2. Background video — loaded LATE on purpose so it never competes with the
 *    poster (the page's LCP), fonts or CSS:
 *      - nothing is downloaded until the window `load` event + an idle callback;
 *      - skipped entirely (poster stays, Play button offered) for
 *        prefers-reduced-motion, Data Saver, and 2G/3G connections;
 *      - on phones (<=768px) only a mobile video is used; if none was
 *        uploaded the poster stays (unless the editor opted the desktop video
 *        in for mobile);
 *      - paused when scrolled off-screen / in a background tab, and by the
 *        visitor via the pause button (never auto-resumed after that).
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-cropx-hero-curved-video' ).forEach( ( block ) => {
		// ── Swoop fill ──────────────────────────────────────────────────────
		const next = block.nextElementSibling;
		if ( next ) {
			const bg = getComputedStyle( next ).backgroundColor;
			if ( bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent' ) {
				const wrap = block.querySelector( '.hcv-bleed-wrap' );
				if ( wrap ) {
					wrap.style.setProperty( '--hcv-swoop-fill', bg );
				}
			}
		}

		// ── Video ───────────────────────────────────────────────────────────
		const video = block.querySelector( '.hcv-video' );
		if ( ! video ) {
			return;
		}
		const bgEl     = block.querySelector( '.hcv-bg' );
		const controls = block.querySelector( '.hcv-controls' );
		const btn      = block.querySelector( '.hcv-pause' );
		const hero     = block.querySelector( '.hcv-hero' );
		const d        = video.dataset;

		const isMobile = window.matchMedia( '(max-width: 768px)' ).matches;
		const pick = ( webm, mp4 ) => {
			const out = [];
			if ( webm ) { out.push( { url: webm, type: 'video/webm' } ); } // WebM first: smaller where supported
			if ( mp4 )  { out.push( { url: mp4,  type: 'video/mp4'  } ); } // MP4 last: universal fallback
			return out;
		};
		let entries;
		if ( isMobile ) {
			entries = pick( d.webmM, d.mp4M );
			if ( ! entries.length && d.mobileFallback === '1' ) {
				entries = pick( d.webm, d.mp4 );
			}
		} else {
			entries = pick( d.webm, d.mp4 );
			if ( ! entries.length ) {
				entries = pick( d.webmM, d.mp4M ); // only a mobile file was uploaded
			}
		}
		if ( ! entries.length ) {
			return; // poster only
		}

		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const conn         = navigator.connection || {};
		const slowNet      = !! conn.saveData || [ 'slow-2g', '2g', '3g' ].includes( conn.effectiveType );
		const autoStart    = ! reduceMotion && ! slowNet;

		let loaded     = false;
		let userPaused = ! autoStart; // no autostart => starts in the "paused" state
		let inView     = true;

		const showBtn = () => {
			if ( controls ) { controls.hidden = false; }
		};
		const syncBtn = () => {
			if ( ! btn ) { return; }
			btn.setAttribute( 'aria-pressed', userPaused ? 'true' : 'false' );
			btn.setAttribute(
				'aria-label',
				userPaused ? btn.dataset.labelPlay : btn.dataset.labelPause
			);
		};

		const load = () => {
			if ( loaded ) { return; }
			loaded = true;
			entries.forEach( ( e ) => {
				const s = document.createElement( 'source' );
				s.src  = e.url;
				s.type = e.type;
				video.appendChild( s );
			} );
			video.load();
		};

		const play = () => {
			video.muted = true; // iOS needs the property, not just the attribute
			const p = video.play();
			if ( p && typeof p.catch === 'function' ) {
				// Autoplay blocked (e.g. iOS Low Power Mode): stay on the poster
				// and let the visitor start it with the button.
				p.catch( () => {
					userPaused = true;
					syncBtn();
					showBtn();
				} );
			}
		};

		video.addEventListener( 'playing', () => {
			if ( bgEl ) { bgEl.classList.add( 'is-playing' ); }
			showBtn();
			syncBtn();
		} );
		video.addEventListener( 'ended', () => { // only reachable when looping is off
			userPaused = true;
			syncBtn();
		} );
		video.addEventListener( 'error', () => {
			// Bad/unsupported file: fall back to the poster quietly.
			if ( bgEl ) { bgEl.classList.remove( 'is-playing' ); }
			if ( controls ) { controls.hidden = true; }
		}, true );

		if ( btn ) {
			btn.addEventListener( 'click', () => {
				if ( userPaused ) {
					userPaused = false;
					load();
					play();
				} else {
					userPaused = true;
					video.pause();
				}
				syncBtn();
			} );
		}

		const evaluate = () => {
			if ( ! loaded || userPaused ) { return; }
			if ( inView && ! document.hidden ) {
				play();
			} else {
				video.pause();
			}
		};
		document.addEventListener( 'visibilitychange', evaluate );
		if ( 'IntersectionObserver' in window && hero ) {
			new IntersectionObserver( ( entriesIO ) => {
				entriesIO.forEach( ( e ) => { inView = e.isIntersecting; } );
				evaluate();
			}, { threshold: 0.1 } ).observe( hero );
		}

		if ( autoStart ) {
			const go = () => {
				const run = () => { load(); play(); };
				if ( 'requestIdleCallback' in window ) {
					window.requestIdleCallback( run, { timeout: 2000 } );
				} else {
					setTimeout( run, 200 );
				}
			};
			if ( document.readyState === 'complete' ) {
				go();
			} else {
				window.addEventListener( 'load', go, { once: true } );
			}
		} else {
			// Poster only until the visitor asks for motion.
			syncBtn();
			showBtn();
		}
	} );
} );
