/**
 * Product Sections block — front-end scroll behaviour.
 *
 * For each block instance on the page:
 *
 *   1. Shadow — adds .is-stuck to the jump wrapper once it sticks beneath the
 *      site nav, giving it a subtle drop shadow for visual separation.
 *
 *   2. Active tab — an IntersectionObserver watches each section and highlights
 *      the corresponding jump tab while that section is the one in view.
 *
 *   3. Smooth scroll — clicking a jump tab smooth-scrolls to the target
 *      section, compensating for the combined height of the sticky site nav
 *      and the sticky jump nav so the section heading isn't hidden.
 */

document.querySelectorAll( '.psec-block' ).forEach( ( block ) => {
	const jumpWrapper = block.querySelector( '.psec-jump-wrapper' );
	const jumpTabs    = Array.from( block.querySelectorAll( '.psec-jump-tab' ) );

	if ( ! jumpWrapper || ! jumpTabs.length ) return;

	// Resolve the two sections via the jump tab hrefs.
	const sections = jumpTabs
		.map( ( tab ) => document.getElementById( tab.getAttribute( 'href' ).slice( 1 ) ) )
		.filter( Boolean );

	if ( ! sections.length ) return;

	// ── 1. Sticky shadow ──────────────────────────────────────────────────
	// We watch the block's top edge. Once it scrolls above the combined height
	// of the site nav + jump nav, the jump nav has stuck — add the shadow.

	const shadowObserver = new IntersectionObserver(
		( [ entry ] ) => jumpWrapper.classList.toggle( 'is-stuck', ! entry.isIntersecting ),
		{ rootMargin: `-${ jumpWrapper.offsetHeight + jumpWrapper.getBoundingClientRect().top }px 0px 0px 0px`, threshold: 0 }
	);
	shadowObserver.observe( block );

	// ── 2. Active tab tracking ────────────────────────────────────────────
	// "Active" = the topmost section whose top edge has scrolled above the
	// combined sticky height (nav + jump nav) + a small lookahead buffer.

	function navOffset() {
		// Use the jump wrapper's current top as the reference; on desktop this
		// equals the site nav height (68px) but is correct at any size.
		return Math.round( jumpWrapper.getBoundingClientRect().top ) + jumpWrapper.offsetHeight;
	}

	function updateActive() {
		const threshold = navOffset() + 32; // 32px lookahead
		let activeIdx = 0;

		sections.forEach( ( sec, i ) => {
			if ( sec.getBoundingClientRect().top <= threshold ) activeIdx = i;
		} );

		jumpTabs.forEach( ( tab, i ) => tab.classList.toggle( 'is-active', i === activeIdx ) );
	}

	window.addEventListener( 'scroll', updateActive, { passive: true } );
	updateActive(); // run once on load

	// ── 3. Smooth scroll on jump tab click ───────────────────────────────

	jumpTabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', ( e ) => {
			e.preventDefault();

			const targetId = tab.getAttribute( 'href' ).slice( 1 );
			const target   = document.getElementById( targetId );
			if ( ! target ) return;

			// Scroll so the section top aligns just below the combined sticky height.
			const offset = navOffset();
			const top    = target.getBoundingClientRect().top + window.scrollY - offset;

			window.scrollTo( { top, behavior: 'smooth' } );
		} );
	} );
} );
