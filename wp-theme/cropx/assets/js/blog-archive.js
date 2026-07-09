/**
 * Blog Archive — Load More
 *
 * Fetches additional posts via the WP REST API and appends them to
 * .ba-grid when the user clicks "Show More". Page 1 is already
 * rendered server-side; this script loads pages 2, 3, … on demand.
 *
 * Data flow:
 *   PHP (home.php)  → data-max-pages, data-per-page on .ba-grid
 *   PHP (enqueue)   → window.cropxBlogArchive.restUrl via wp_localize_script
 *   JS              → fetches /wp/v2/posts?page=N&per_page=X&_embed=1
 */
( function () {
	'use strict';

	const grid = document.querySelector( '.ba-grid' );
	const wrap = document.querySelector( '.ba-show-more' );
	const btn  = document.querySelector( '.ba-show-more-btn' );

	if ( ! grid || ! btn ) return;

	const restUrl  = ( window.cropxBlogArchive || {} ).restUrl || '/wp-json/wp/v2/posts';
	const maxPages = parseInt( grid.dataset.maxPages, 10 ) || 1;
	const perPage  = parseInt( grid.dataset.perPage,  10 ) || 10;

	// Already rendered server-side; next fetch is page 2.
	let currentPage = 1;

	// Matches PHP $ba_tag_colors in home.php — keep in sync if adding categories.
	const TAG_COLORS = {
		'irrigation'            : 'ba-tag--teal',
		'precision-ag'          : 'ba-tag--teal',
		'precision-agriculture' : 'ba-tag--teal',
		'partner-stories'       : 'ba-tag--gold',
		'partners'              : 'ba-tag--gold',
		'sustainability'        : 'ba-tag--leaf',
		'environment'           : 'ba-tag--leaf',
		'disease-management'    : 'ba-tag--terra',
		'disease'               : 'ba-tag--terra',
	};

	const GRADIENTS = [ 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' ];
	let   gradientIdx = 0;

	const ARROW_SM = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		+ '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		+ '</svg>';

	// ── Helpers ────────────────────────────────────────────────────────────────

	function tagClass( slug ) {
		return TAG_COLORS[ slug ] || 'ba-tag--dark';
	}

	function formatDate( iso ) {
		return new Date( iso ).toLocaleDateString( 'en-US', {
			year: 'numeric', month: 'long', day: 'numeric',
		} );
	}

	// ── Card builder ───────────────────────────────────────────────────────────
	// Uses DOM construction (not innerHTML) for all user-controlled strings to
	// keep XSS surface to zero. title.rendered is WP-escaped HTML, safe as innerHTML.

	function buildCard( post ) {
		const embedded = post._embedded || {};
		const terms    = embedded[ 'wp:term' ];
		const cats     = ( terms && terms[ 0 ] ) || [];
		const cat      = cats[ 0 ] || null;

		const media    = embedded[ 'wp:featuredmedia' ] && embedded[ 'wp:featuredmedia' ][ 0 ];
		const sizes    = media && media.media_details && media.media_details.sizes;
		const thumb    = ( sizes && (
			( sizes.medium_large && sizes.medium_large.source_url ) ||
			( sizes.large        && sizes.large.source_url        )
		) ) || ( media && media.source_url ) || null;

		// Outer link
		const a = document.createElement( 'a' );
		a.href      = post.link;
		a.className = 'ba-card';

		// Image zone
		const imgDiv = document.createElement( 'div' );
		if ( thumb ) {
			imgDiv.className = 'ba-card-img';
			const img = document.createElement( 'img' );
			img.src      = thumb;
			img.alt      = post.title.rendered.replace( /<[^>]+>/g, '' );
			img.loading  = 'lazy';
			img.decoding = 'async';
			imgDiv.appendChild( img );
		} else {
			const ph = GRADIENTS[ gradientIdx % GRADIENTS.length ];
			imgDiv.className = 'ba-card-img ba-card-img--' + ph;
			gradientIdx++;
		}
		a.appendChild( imgDiv );

		// Body
		const body = document.createElement( 'div' );
		body.className = 'ba-card-body';

		// Badge (category)
		if ( cat ) {
			const badge = document.createElement( 'span' );
			badge.className   = 'ba-card-badge';
			badge.textContent = cat.name;
			body.appendChild( badge );
		}

		// Title
		const h3 = document.createElement( 'h3' );
		h3.className = 'ba-card-title';
		h3.innerHTML = post.title.rendered; // WP-escaped — safe as innerHTML
		body.appendChild( h3 );

		// Date — sits just below the title
		const date = document.createElement( 'span' );
		date.className   = 'ba-card-date';
		date.textContent = formatDate( post.date );
		body.appendChild( date );

		// Excerpt (strip HTML tags from WP excerpt)
		const rawExcerpt = ( post.excerpt && post.excerpt.rendered )
			? post.excerpt.rendered.replace( /<[^>]+>/g, '' ).replace( /&#8230;/g, '…' ).trim()
			: '';
		if ( rawExcerpt ) {
			const excerpt = document.createElement( 'p' );
			excerpt.className   = 'ba-card-excerpt';
			excerpt.textContent = rawExcerpt;
			body.appendChild( excerpt );
		}

		// Read more CTA
		const read = document.createElement( 'span' );
		read.className = 'ba-card-read';
		read.innerHTML = 'Read more ' + ARROW_SM;
		body.appendChild( read );

		a.appendChild( body );

		return a;
	}

	// ── Load more ──────────────────────────────────────────────────────────────

	async function loadMore() {
		currentPage++;
		btn.disabled = true;
		btn.setAttribute( 'aria-busy', 'true' );
		const label = btn.querySelector( '.ba-show-more-label' );
		if ( label ) label.textContent = 'Loading…';

		try {
			const url = restUrl + '?page=' + currentPage + '&per_page=' + perPage + '&_embed=1';
			const res = await fetch( url );
			if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );

			const posts = await res.json();
			posts.forEach( function ( post ) {
				grid.appendChild( buildCard( post ) );
			} );

			if ( currentPage >= maxPages ) {
				if ( wrap ) wrap.remove();
			} else {
				btn.disabled = false;
				btn.removeAttribute( 'aria-busy' );
				if ( label ) label.textContent = 'Show More';
			}
		} catch ( err ) {
			// Restore on network/parse error so the user can retry.
			btn.disabled = false;
			btn.removeAttribute( 'aria-busy' );
			if ( label ) label.textContent = 'Show More';
		}
	}

	btn.addEventListener( 'click', loadMore );
} )();


/**
 * Popular Posts Carousel
 *
 * Crossfade carousel with:
 *   - Auto-advance every 6 seconds
 *   - Pause on hover and on keyboard focus inside the carousel
 *   - Dot navigation (tablist pattern)
 *   - Prev / Next arrow buttons
 *   - Arrow-key support when carousel or its controls are focused
 *   - prefers-reduced-motion: skips auto-advance (CSS handles transition removal)
 */
( function () {
	'use strict';

	const carousel = document.querySelector( '[data-popular-carousel]' );
	if ( ! carousel ) return;

	const slides = Array.from( carousel.querySelectorAll( '.ba-popular-slide' ) );
	const dots   = Array.from( carousel.querySelectorAll( '.ba-popular-dot' ) );
	const prevBtn = carousel.querySelector( '.ba-popular-arrow--prev' );
	const nextBtn = carousel.querySelector( '.ba-popular-arrow--next' );

	if ( slides.length < 2 ) return; // single slide — nothing to animate

	const INTERVAL    = 6000; // ms between auto-advances
	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	let current = 0;
	let timer   = null;

	// ── Go-to: swap active slide + update dots ──────────────────────────────────

	function goTo( index ) {
		const prev = current;
		current = ( ( index % slides.length ) + slides.length ) % slides.length;
		if ( prev === current ) return;

		// Outgoing slide
		slides[ prev ].classList.remove( 'ba-popular-slide--active' );
		slides[ prev ].setAttribute( 'aria-hidden', 'true' );

		// Incoming slide
		slides[ current ].classList.add( 'ba-popular-slide--active' );
		slides[ current ].setAttribute( 'aria-hidden', 'false' );

		// Dots
		if ( dots.length ) {
			dots[ prev ].classList.remove( 'ba-popular-dot--active' );
			dots[ prev ].setAttribute( 'aria-selected', 'false' );
			dots[ current ].classList.add( 'ba-popular-dot--active' );
			dots[ current ].setAttribute( 'aria-selected', 'true' );
		}
	}

	function next() { goTo( current + 1 ); }
	function prev() { goTo( current - 1 ); }

	// ── Auto-advance ────────────────────────────────────────────────────────────

	function startTimer() {
		if ( reduceMotion ) return; // respect user preference
		clearInterval( timer );
		timer = setInterval( next, INTERVAL );
	}

	function stopTimer() {
		clearInterval( timer );
		timer = null;
	}

	// ── Event listeners ─────────────────────────────────────────────────────────

	// Pause while the pointer is over the carousel
	carousel.addEventListener( 'mouseenter', stopTimer );
	carousel.addEventListener( 'mouseleave', startTimer );

	// Pause while any interactive element inside has keyboard focus
	carousel.addEventListener( 'focusin',  stopTimer );
	carousel.addEventListener( 'focusout', startTimer );

	// Arrow buttons
	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', function () { stopTimer(); prev(); startTimer(); } );
	}
	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', function () { stopTimer(); next(); startTimer(); } );
	}

	// Dot buttons
	dots.forEach( function ( dot, i ) {
		dot.addEventListener( 'click', function () {
			stopTimer();
			goTo( i );
			startTimer();
		} );
	} );

	// Arrow-key navigation (carousel acts as a composite widget)
	carousel.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'ArrowLeft' ) {
			e.preventDefault();
			stopTimer(); prev(); startTimer();
		} else if ( e.key === 'ArrowRight' ) {
			e.preventDefault();
			stopTimer(); next(); startTimer();
		}
	} );

	// ── Kick off ────────────────────────────────────────────────────────────────
	startTimer();
} )();
