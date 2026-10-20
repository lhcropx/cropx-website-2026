/**
 * Shared "animate in as it scrolls into view" utility.
 *
 * The hero-family blocks (hero-curved, hero-curved-standard, hero-curved-animated,
 * hero-blog, segment-hero) already fade their eyebrow/heading/subheading/CTA up
 * on page load via a pure CSS `animation` with a staggered `animation-delay` —
 * no JS at all. That works there specifically because a hero is always the
 * first thing in the viewport, so "on load" and "on scroll into view" are the
 * same moment. Any block further down the page needs an actual trigger:
 * without one, a block near the bottom of a long page would either finish
 * animating long before anyone scrolls to it, or (if the animation were
 * scroll-linked some other way) never fire at all.
 *
 * This module is that trigger. It's a single shared IntersectionObserver —
 * one instance for the whole page, however many blocks use it — that watches
 * whatever root elements you hand it and adds `is-revealed` the moment any
 * part of it first touches the viewport, then stops watching it (the
 * animation plays once, same as the hero blocks; scrolling back up doesn't
 * replay it).
 *
 * Pair this with src/shared/scroll-reveal.css, which does the actual fade-up
 * via CSS driven off that one class. Import both into a block the same way
 * three-column-icons imports the shared IconPicker: CSS via the block's own
 * style.css (`@import '../../shared/scroll-reveal.css';`), JS via the block's
 * view.js.
 *
 * A block only needs ONE observed root — not one per animated element — so a
 * whole section (header text + a grid of repeated cards/icons/stats) reveals
 * together from a single intersection. The CSS then cascades that one
 * `is-revealed` class down to every `.reveal-up` element and every
 * `.reveal-group .reveal-item` inside it, staggering each item via its own
 * `--reveal-delay` custom property (set inline per item in render.php).
 *
 * Usage in a block's view.js:
 *   import { initScrollReveal } from '../../shared/scrollReveal';
 *   initScrollReveal( '.wp-block-cropx-cards' );
 *
 * If more than one block type shares a single view.js entry (rare in this
 * theme), call it once per selector — the shared observer instance is reused
 * either way, so there's no cost to calling it multiple times.
 */

let sharedObserver = null;

function getObserver() {
	if ( sharedObserver ) {
		return sharedObserver;
	}
	sharedObserver = new IntersectionObserver(
		( entries, observer ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-revealed' );
					observer.unobserve( entry.target );
				}
			} );
		},
		// threshold 0.15 + a -10% bottom rootMargin meant a section had to be
		// scrolled quite far into view before anything fired — visible on
		// screen but sitting there inert for a beat, which read as a stuck/
		// laggy pause rather than a responsive reveal. Tightened to fire as
		// soon as any part of the section is on screen (Lauren feedback,
		// Sep 2026).
		{ threshold: 0.01, rootMargin: '0px' }
	);
	return sharedObserver;
}

/**
 * Observe every element matching `selector` and reveal it once it scrolls
 * into view. Safe to call multiple times (e.g. once per block file) and
 * safe on browsers without IntersectionObserver support — those just reveal
 * everything immediately rather than leaving it permanently hidden, since
 * scroll-reveal.css only ever hides an element once `cropx-reveal-js` is
 * present on <html> (see comment in scroll-reveal.css) — skipping that class
 * here means nothing gets hidden in the first place.
 *
 * @param {string} selector CSS selector for the root element(s) to observe —
 *                          usually a block's outer wrapper class, e.g.
 *                          '.wp-block-cropx-cards'.
 */
export function initScrollReveal( selector ) {
	if ( typeof IntersectionObserver === 'undefined' ) {
		return;
	}

	document.documentElement.classList.add( 'cropx-reveal-js' );

	const observer = getObserver();
	document.querySelectorAll( selector ).forEach( ( el ) => {
		if ( el.classList.contains( 'is-revealed' ) ) {
			return;
		}
		observer.observe( el );
	} );
}
