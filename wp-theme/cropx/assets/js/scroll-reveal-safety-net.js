/**
 * Sitewide scroll-reveal safety net.
 *
 * src/shared/scrollReveal.js is bundled separately into every block that
 * uses it (38 blocks as of Sep 2026 — cards, testimonials, stats-grid, both
 * Zoho forms, etc.) and hides each block's .reveal-up / .reveal-item
 * elements via CSS until a per-block IntersectionObserver fires and adds
 * `is-revealed`. That's normally near-instant.
 *
 * But browsers are allowed to delay or pause IntersectionObserver callbacks
 * for a tab that isn't actually visible — backgrounded, minimized, or fully
 * covered by another window (Chrome's native window-occlusion tracking does
 * this even when the tab has focus and the user thinks they're looking at
 * it). Confirmed empirically Sep 15, 2026 while investigating Lauren's
 * report that the homepage contact form "isn't displaying": in a tab whose
 * `document.visibilityState` was "hidden", a brand-new IntersectionObserver
 * watching an element already sitting in the viewport never fired at all —
 * not even its normal first synchronous-ish callback. Since nothing else in
 * scrollReveal.js ever adds `is-revealed`, a visitor unlucky enough to hit
 * this has a permanently blank, invisible section: the elements are still
 * in the DOM with real content, just stuck at `opacity:0` forever.
 *
 * Rather than patch this into all 38 blocks' already-compiled bundles
 * (this theme's build pipeline only hand-propagates changes to build/ per
 * CLAUDE.md — see that file's "Deploying to live staging" section — so that
 * would mean manually editing 38+ minified files, a large, error-prone
 * blast radius for what's fundamentally one shared behavior), this is a
 * single independent global script. It doesn't know or care which selector
 * any given block originally observed — it only needs the shared CSS
 * contract every block already uses (reveal-up / reveal-group / reveal-item
 * / is-revealed, see src/shared/scroll-reveal.css), so it covers all of them
 * at once and needs no per-block wiring.
 *
 * It changes nothing about the normal path — the real observers still do
 * the actual reveal, instantly, the overwhelming majority of the time. This
 * only ever does anything when something already in the viewport has been
 * sitting un-revealed for a while, which should be rare.
 */
( function () {
	'use strict';

	function isInViewport( el ) {
		var rect = el.getBoundingClientRect();
		if ( rect.width === 0 && rect.height === 0 ) {
			return false;
		}
		var vh = window.innerHeight || document.documentElement.clientHeight;
		var vw = window.innerWidth || document.documentElement.clientWidth;
		return rect.bottom > 0 && rect.right > 0 && rect.top < vh && rect.left < vw;
	}

	// Reveals anything currently in the viewport that's still stuck hidden.
	// Returns true once nothing is left to watch for anywhere on the page,
	// so callers can stop polling.
	function sweep() {
		if ( ! document.documentElement.classList.contains( 'cropx-reveal-js' ) ) {
			// No block's scrollReveal.js has run yet (or ever will) — nothing
			// is hidden by the CSS side of this system either way.
			return true;
		}

		var candidates = document.querySelectorAll( '.reveal-up, .reveal-group .reveal-item' );
		if ( ! candidates.length ) {
			return true;
		}

		var allDone = true;
		candidates.forEach( function ( el ) {
			var group  = el.closest( '.reveal-group' );
			var target = group || el;

			// Already revealed via its group, itself, or some other ancestor
			// (e.g. the block's own observed root) — nothing to do.
			if ( target.classList.contains( 'is-revealed' ) || el.closest( '.is-revealed' ) ) {
				return;
			}

			if ( isInViewport( el ) ) {
				target.classList.add( 'is-revealed' );
			} else {
				allDone = false;
			}
		} );

		return allDone;
	}

	var ticking = false;
	function requestSweep() {
		if ( ticking ) {
			return;
		}
		ticking = true;
		window.requestAnimationFrame( function () {
			sweep();
			ticking = false;
		} );
	}

	// Signals that a tab just became genuinely visible/interactive again —
	// the exact moment a previously-throttled observer would have been
	// starved of callbacks.
	document.addEventListener( 'visibilitychange', function () {
		if ( ! document.hidden ) {
			requestSweep();
		}
	} );
	window.addEventListener( 'pageshow', requestSweep );
	window.addEventListener( 'focus', requestSweep );
	window.addEventListener( 'scroll', requestSweep, { passive: true } );

	// Belt-and-suspenders poll for the first several seconds after load, in
	// case none of the above ever fires — e.g. the tab is visible the whole
	// time but a real observer is simply slow to get its first check in
	// under load. Capped so it can't run forever on a long page a visitor
	// never scrolls all the way through.
	var checks = 0;
	var poll   = window.setInterval( function () {
		var done = sweep();
		if ( done || ++checks >= 12 ) {
			window.clearInterval( poll );
			window.removeEventListener( 'scroll', requestSweep );
		}
	}, 500 );
} )();
