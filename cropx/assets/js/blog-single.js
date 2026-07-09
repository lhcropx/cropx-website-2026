/**
 * blog-single.js — Blog post interactivity
 *
 * Three responsibilities:
 *   1. Reading progress bar: a fixed 3px bar at the top that fills as the
 *      reader scrolls through the article content.
 *   2. ToC builder: scans the article for h2/h3/h4 headings, adds slug IDs
 *      to any heading that doesn't already have one, then builds the ToC list
 *      in the sidebar <nav> element.
 *   3. ToC active state: highlights the ToC entry for the currently visible
 *      heading as the user scrolls.
 *
 * Relies on these DOM hooks from single.php:
 *   #bsingle-reading-progress  — the progress bar element
 *   #bsingle-article-content   — the article body element
 *   #bsingle-toc-nav           — the <nav> inside the sidebar where the
 *                                 ToC list is injected
 *
 * No dependencies — pure vanilla JS.
 */

( function () {
	'use strict';

	const progressBar    = document.getElementById( 'bsingle-reading-progress' );
	const articleContent = document.getElementById( 'bsingle-article-content' );
	const tocNav         = document.getElementById( 'bsingle-toc-nav' );

	if ( ! articleContent ) return; // Safety: not on a single post page.

	// ── Reading progress bar ─────────────────────────────────────────────────

	function updateProgress() {
		if ( ! progressBar ) return;
		const rect     = articleContent.getBoundingClientRect();
		const total    = articleContent.offsetHeight - window.innerHeight;
		const scrolled = Math.max( 0, -rect.top );
		const pct      = total > 0 ? Math.min( 100, ( scrolled / total ) * 100 ) : 0;
		progressBar.style.width = pct + '%';
		progressBar.setAttribute( 'aria-valuenow', Math.round( pct ) );
	}

	// ── Slug helper ──────────────────────────────────────────────────────────

	/**
	 * Convert a heading's text content to a URL-safe slug.
	 * Keeps lowercase alphanumeric, replaces spaces/separators with hyphens,
	 * strips everything else.
	 */
	function slugify( text ) {
		return text
			.toLowerCase()
			.replace( /[^\w\s-]/g, '' )  // remove non-word chars (keep hyphens)
			.replace( /[\s_]+/g, '-' )   // spaces / underscores → hyphens
			.replace( /^-+|-+$/g, '' );  // trim leading/trailing hyphens
	}

	/**
	 * Ensure a heading has a unique ID, generating one from its text if absent.
	 * De-duplicates by appending -2, -3 etc. when needed.
	 */
	const usedIds = new Set();

	function ensureId( el ) {
		if ( el.id ) {
			usedIds.add( el.id );
			return el.id;
		}
		let base = slugify( el.textContent ) || 'section';
		let id   = base;
		let n    = 2;
		while ( usedIds.has( id ) ) {
			id = base + '-' + n++;
		}
		el.id = id;
		usedIds.add( id );
		return id;
	}

	// ── ToC builder ──────────────────────────────────────────────────────────

	/**
	 * Scans the article for h2 and h3 headings (ToC top-level items)
	 * and h4 headings (sub-items nested under their nearest h2/h3 parent).
	 * Injects the result as a <ul> into `tocNav`.
	 */
	function buildToC() {
		if ( ! tocNav ) return;

		// Collect h2, h3, h4 in document order.
		const headings = Array.from(
			articleContent.querySelectorAll( 'h2, h3, h4' )
		);

		if ( headings.length === 0 ) {
			// No headings — hide the sidebar entirely.
			const sidebar = document.querySelector( '.bsingle-toc-sidebar' );
			if ( sidebar ) sidebar.style.display = 'none';
			return;
		}

		const ul = document.createElement( 'ul' );
		ul.className = 'bsingle-toc-list';

		let currentTopItem = null; // the most recent <li> for a h2/h3
		let currentSubList = null; // the <ul> sub-list inside that item

		headings.forEach( function ( heading ) {
			const id   = ensureId( heading );
			const tag  = heading.tagName; // 'H2', 'H3', or 'H4'
			const text = heading.textContent.trim();

			if ( tag === 'H2' || tag === 'H3' ) {
				// Top-level ToC entry.
				const li = document.createElement( 'li' );
				li.className   = 'bsingle-toc-item';
				li.dataset.id  = id;

				const a = document.createElement( 'a' );
				a.href      = '#' + id;
				a.className = 'bsingle-toc-link';
				a.textContent = text;

				li.appendChild( a );
				ul.appendChild( li );

				currentTopItem = li;
				currentSubList = null; // reset sub-list for new top-level item

			} else if ( tag === 'H4' && currentTopItem ) {
				// Sub-item nested under the most recent h2/h3.
				if ( ! currentSubList ) {
					currentSubList = document.createElement( 'ul' );
					currentSubList.className = 'bsingle-toc-sub';
					currentTopItem.appendChild( currentSubList );
				}

				const li = document.createElement( 'li' );
				li.className  = 'bsingle-toc-sub-item';
				li.dataset.id = id;

				const a = document.createElement( 'a' );
				a.href      = '#' + id;
				a.className = 'bsingle-toc-sub-link';
				a.textContent = text;

				li.appendChild( a );
				currentSubList.appendChild( li );
			}
		} );

		tocNav.appendChild( ul );
	}

	// ── ToC active state ─────────────────────────────────────────────────────

	/**
	 * Reads all headings that have been given IDs (i.e. those in the ToC),
	 * determines which one is currently "in view", and updates the
	 * `is-active` class on the matching ToC list item.
	 *
	 * "In view" = the last heading whose top edge has passed the top 30% of
	 * the viewport. This feels more natural than a strict >0 threshold.
	 */
	function updateToCActive() {
		if ( ! tocNav ) return;

		const tocItems = tocNav.querySelectorAll( '[data-id]' );
		if ( tocItems.length === 0 ) return;

		const threshold = window.innerHeight * 0.25; // 25% from top
		let activeId    = null;

		// Collect all heading IDs referenced in the ToC.
		const ids = Array.from( tocItems ).map( li => li.dataset.id );

		ids.forEach( function ( id ) {
			const el = document.getElementById( id );
			if ( el ) {
				const top = el.getBoundingClientRect().top;
				if ( top <= threshold ) {
					activeId = id;
				}
			}
		} );

		// Fall back to first item if nothing has crossed the threshold yet.
		if ( ! activeId ) activeId = ids[ 0 ];

		tocItems.forEach( function ( li ) {
			li.classList.toggle( 'is-active', li.dataset.id === activeId );
		} );
	}

	// ── Smooth scroll (optional — only if browser doesn't do it natively) ───

	function handleToCClick( e ) {
		const link = e.target.closest( 'a[href^="#"]' );
		if ( ! link ) return;
		const target = document.getElementById( link.getAttribute( 'href' ).slice( 1 ) );
		if ( ! target ) return;
		e.preventDefault();
		// Offset for the sticky nav (76px) + small breathing room.
		const top = target.getBoundingClientRect().top + window.scrollY - 90;
		window.scrollTo( { top, behavior: 'smooth' } );
	}

	// ── Copy-link share button ───────────────────────────────────────────────

	function initCopyLink() {
		const copyBtn = document.getElementById( 'bsingle-copy-link' );
		if ( ! copyBtn ) return;

		copyBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			navigator.clipboard.writeText( window.location.href ).then( function () {
				const label = copyBtn.querySelector( '.bsingle-copy-label' );
				if ( label ) {
					const original = label.textContent;
					label.textContent = 'Copied!';
					setTimeout( function () { label.textContent = original; }, 2000 );
				}
			} );
		} );
	}

	// ── Wire everything up ───────────────────────────────────────────────────

	// Build ToC immediately (DOM is ready — script is defer-loaded).
	buildToC();
	updateProgress();
	updateToCActive();
	initCopyLink();

	// Update on scroll (passive for perf).
	window.addEventListener( 'scroll', function () {
		updateProgress();
		updateToCActive();
	}, { passive: true } );

	// Handle ToC link clicks for smooth scroll.
	if ( tocNav ) {
		tocNav.addEventListener( 'click', handleToCClick );
	}

} )();
