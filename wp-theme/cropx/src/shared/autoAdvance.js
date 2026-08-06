/**
 * Shared "auto-advance a scroll-snap carousel" utility.
 *
 * Used by field-photos (Photo Carousel) and testimonials-carousel — both are
 * native scroll-snap tracks with their own existing arrow/dot navigation and
 * "no loop" boundary behavior (manual arrows disable at the first/last
 * slide, unlike hardware-lineup's seamless DOM-cloned marquee). This just
 * layers a timer on top that advances to the next snap position, wrapping
 * back to the first slide after the last one (so it doesn't just stop
 * forever), and pauses under the same conditions hardware-lineup's marquee
 * already established: hover, a recent manual interaction, and
 * prefers-reduced-motion — plus a visibility check here, so an off-screen
 * carousel doesn't silently rack up "skipped" slides while nobody's looking
 * at it and then jump several slides ahead the moment it scrolls into view.
 *
 * Each block keeps ownership of its own index-tracking and scrolling logic
 * (getActiveIndex / maxIndex / scrollToItem already exist in both view.js
 * files) — this helper only owns the timer + pause bookkeeping.
 *
 * @param {Object}   opts
 * @param {Element}  opts.root          Element to attach hover/interaction
 *                                      listeners to (the whole carousel
 *                                      section, so hovering the arrows or
 *                                      dots also pauses it).
 * @param {Function}  opts.getActive    () => current active slide index.
 * @param {Function}  opts.getMaxIndex  () => last valid slide index.
 * @param {Function}  opts.goTo         (index) => void — scrolls to a slide.
 * @param {number}   [opts.intervalMs=5000]    Time between advances.
 * @param {number}   [opts.resumeDelayMs=1500] How long after a manual
 *                                             interaction before
 *                                             auto-advance resumes — matches
 *                                             hardware-lineup's constant.
 */
export function initSnapAutoAdvance( {
	root,
	getActive,
	getMaxIndex,
	goTo,
	intervalMs = 5000,
	resumeDelayMs = 1500,
} ) {
	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduce || ! root ) {
		return;
	}

	let isHoverPaused   = false;
	let isVisible       = true;
	let lastInteraction = 0;

	root.addEventListener( 'mouseenter', () => { isHoverPaused = true; } );
	root.addEventListener( 'mouseleave', () => { isHoverPaused = false; } );
	[ 'pointerdown', 'touchstart', 'wheel', 'keydown' ].forEach( ( evt ) => {
		root.addEventListener( evt, () => { lastInteraction = performance.now(); }, { passive: true } );
	} );

	if ( 'IntersectionObserver' in window ) {
		const io = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => { isVisible = entry.isIntersecting; } );
			},
			{ threshold: 0.2 }
		);
		io.observe( root );
	}

	setInterval( () => {
		if ( isHoverPaused || ! isVisible ) {
			return;
		}
		if ( performance.now() - lastInteraction < resumeDelayMs ) {
			return;
		}

		const max = getMaxIndex();
		if ( max <= 0 ) {
			return; // Only one slide position — nothing to advance between.
		}

		const current = getActive();
		const next     = current >= max ? 0 : current + 1;
		goTo( next );
	}, intervalMs );
}
