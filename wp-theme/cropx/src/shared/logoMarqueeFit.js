/**
 * Logo Carousel — "close in the edges" fit logic.
 *
 * The marquee's track always renders the logo set twice back-to-back (that's
 * what makes the translateX(-50%) loop seamless). That's invisible as long as
 * one full set is wider than the space available to show it — the viewer
 * only ever sees a scrolling window onto the set, never both copies at once.
 *
 * But when there are few logos, wide slots, or a wide viewport, one full set
 * can end up narrower than the available width. In that case both copies fit
 * on screen simultaneously and the visitor sees the whole logo set twice,
 * side by side, which reads as a bug rather than a marquee. Since the logo
 * boxes are a fixed size (not image-dependent), this is measurable up front:
 * find the width of exactly one set, and if it's narrower than the space
 * available, shrink the visible window (the .ls-marquee element) down to
 * that exact width and center it, so only one copy is ever on screen. If
 * there are enough logos to need scrolling, leave it full-width as before.
 *
 * Used by both view.js (front end) and edit.js (editor canvas) so behavior
 * matches in both places.
 */

/**
 * Measure and apply the fit for one marquee element. Safe to call
 * repeatedly (e.g. on resize) — always re-measures from scratch.
 *
 * @param {HTMLElement} marqueeEl The .ls-marquee element.
 */
export function fitLogoMarquee( marqueeEl ) {
	if ( ! marqueeEl ) {
		return;
	}

	const track = marqueeEl.querySelector( '.ls-track' );
	if ( ! track ) {
		return;
	}

	const items = track.children;
	const count = items.length / 2; // The track always renders the set twice.
	if ( ! count || count < 1 ) {
		return;
	}

	// Clear any previously-applied constraint before measuring, so we're
	// always measuring against the marquee's true, unconstrained width.
	marqueeEl.style.maxWidth   = '';
	marqueeEl.style.marginLeft  = '';
	marqueeEl.style.marginRight = '';

	const availableWidth = marqueeEl.getBoundingClientRect().width;
	const firstItem          = items[ 0 ];
	const firstDuplicateItem = items[ count ];
	if ( ! firstItem || ! firstDuplicateItem ) {
		return;
	}

	// Distance from the start of the first logo to the start of its
	// duplicate = the width of exactly one full set.
	const singleSetWidth =
		firstDuplicateItem.getBoundingClientRect().left - firstItem.getBoundingClientRect().left;

	if ( singleSetWidth > 0 && singleSetWidth < availableWidth ) {
		marqueeEl.style.maxWidth    = `${ singleSetWidth }px`;
		marqueeEl.style.marginLeft  = 'auto';
		marqueeEl.style.marginRight = 'auto';
	}
	// Otherwise: leave it unconstrained (full width) — there are enough
	// logos that the scroll never reveals both copies at once.
}

/**
 * Keep a marquee element fitted as its container resizes. Returns a cleanup
 * function — call it on unmount / teardown.
 *
 * @param  {HTMLElement} marqueeEl The .ls-marquee element.
 * @return {Function}              Cleanup function.
 */
export function observeLogoMarquee( marqueeEl ) {
	if ( ! marqueeEl ) {
		return () => {};
	}

	const run = () => fitLogoMarquee( marqueeEl );
	run();

	// Observe the PARENT, not the marquee itself — the marquee's own width
	// changes (which we just caused) shouldn't re-trigger this and risk a
	// feedback loop. The parent's width only changes for reasons outside
	// our control (viewport resize, sidebar toggling in the editor, etc.).
	let resizeObserver;
	if ( typeof ResizeObserver !== 'undefined' && marqueeEl.parentElement ) {
		resizeObserver = new ResizeObserver( run );
		resizeObserver.observe( marqueeEl.parentElement );
	}

	window.addEventListener( 'resize', run );

	return () => {
		if ( resizeObserver ) {
			resizeObserver.disconnect();
		}
		window.removeEventListener( 'resize', run );
	};
}
