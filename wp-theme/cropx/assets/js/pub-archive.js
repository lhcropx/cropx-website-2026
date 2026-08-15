/**
 * Publication Archive — Show More + Featured carousel
 *
 * Mirrors blog-archive.js exactly — same IIFE pattern, same DOM-construction
 * approach for XSS safety, same button removal when all pages are loaded —
 * but sources data from the cropx_publication REST endpoint and builds
 * pa-* card markup (white cards) instead of ba-* blog post cards.
 *
 * Data flow:
 *   PHP (archive-cropx_publication.php)
 *     → data-max-pages, data-per-page on .pa-grid
 *   PHP (enqueue.php)
 *     → window.cropxPubArchive.restUrl, .termId, .filterTax via wp_localize_script
 *   JS
 *     → GET {restUrl}?page=N&per_page=X&_embed=1[&{filterTax}={termId}]
 *
 * filterTax is 'cropx_content_type' on /content-type/{term}/ views,
 * 'cropx_story_tag' on /story-tag/{term}/ views, and empty on the
 * unfiltered "all customer stories" view (termId is 0 in that case, so no
 * filter param is appended regardless of filterTax).
 */
( function () {
	'use strict';

	// ── Gradient placeholder cycle — mirrors PHP template ──────────────────────
	const PLACEHOLDERS = [ 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' ];
	let placeholderIndex = 0;

	// ── Arrow SVG (matches the inline SVG in PHP template) ────────────────────
	const ARROW_SM = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		+ '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		+ '</svg>';

	// ════════════════════════════════════════════════════════════════
	// SHOW MORE  (load additional pages from the REST API)
	// ════════════════════════════════════════════════════════════════

	const grid        = document.querySelector( '.pa-grid' );
	const showMoreWrap = document.querySelector( '.pa-show-more' );
	const btn         = document.querySelector( '.pa-show-more-btn' );

	if ( grid && btn ) {
		const config    = window.cropxPubArchive || {};
		const restUrl   = config.restUrl || '/wp-json/wp/v2/cropx_publication';
		const termId    = parseInt( config.termId, 10 ) || 0;
		const filterTax = config.filterTax || 'cropx_content_type';
		const maxPages  = parseInt( grid.dataset.maxPages, 10 ) || 1;
		const perPage   = parseInt( grid.dataset.perPage,  10 ) || 10;

		let currentPage = 1;

		// ── Helpers ───────────────────────────────────────────────────────────

		function formatDate( iso ) {
			return new Date( iso ).toLocaleDateString( 'en-US', {
				year: 'numeric', month: 'long', day: 'numeric',
			} );
		}

		// ── Card builder ──────────────────────────────────────────────────────
		// Builds white pa-card markup matching archive-cropx_publication.php.
		// Uses DOM construction for all user-supplied strings (XSS safety).
		// title.rendered is WP-escaped HTML so it is safe as innerHTML.

		function buildCard( pub ) {
			const embedded   = pub._embedded || {};
			const termGroups = embedded[ 'wp:term' ] || [];

			// Find cropx_content_type among the embedded term groups
			let type = null;
			termGroups.forEach( function ( group ) {
				if ( group.length && group[ 0 ].taxonomy === 'cropx_content_type' ) {
					type = group[ 0 ];
				}
			} );

			const media = embedded[ 'wp:featuredmedia' ] && embedded[ 'wp:featuredmedia' ][ 0 ];
			const sizes = media && media.media_details && media.media_details.sizes;
			const thumb = ( sizes && (
				( sizes.medium_large && sizes.medium_large.source_url ) ||
				( sizes.large        && sizes.large.source_url )
			) ) || ( media && media.source_url ) || null;

			// Outer link (whole card is clickable)
			const a = document.createElement( 'a' );
			a.href      = pub.link;
			a.className = 'pa-card';

			// Image / placeholder zone
			const imgDiv = document.createElement( 'div' );
			if ( thumb ) {
				imgDiv.className = 'pa-card-img';
				const img = document.createElement( 'img' );
				img.src      = thumb;
				img.alt      = pub.title.rendered.replace( /<[^>]+>/g, '' );
				img.loading  = 'lazy';
				img.decoding = 'async';
				imgDiv.appendChild( img );
			} else {
				const ph = PLACEHOLDERS[ placeholderIndex % PLACEHOLDERS.length ];
				placeholderIndex++;
				imgDiv.className = 'pa-card-img pa-card-img--' + ph;
			}
			a.appendChild( imgDiv );

			// Card body
			const body = document.createElement( 'div' );
			body.className = 'pa-card-body';

			// Content-type badge
			if ( type ) {
				const badge = document.createElement( 'span' );
				badge.className   = 'pa-card-badge' + ( type.slug === 'video-testimonial' ? ' pa-card-badge--accent' : '' );
				badge.textContent = type.name;
				body.appendChild( badge );
			}

			// Title
			const h3 = document.createElement( 'h3' );
			h3.className = 'pa-card-title';
			h3.innerHTML = pub.title.rendered; // WP-escaped — safe as innerHTML
			body.appendChild( h3 );

			// Date
			const dateEl = document.createElement( 'span' );
			dateEl.className   = 'pa-card-date';
			dateEl.textContent = formatDate( pub.date );
			body.appendChild( dateEl );

			// Excerpt
			const rawExcerpt = ( pub.excerpt && pub.excerpt.rendered )
				? pub.excerpt.rendered.replace( /<[^>]+>/g, '' ).replace( /&#8230;/g, '…' ).trim()
				: '';
			if ( rawExcerpt ) {
				const p = document.createElement( 'p' );
				p.className   = 'pa-card-excerpt';
				p.textContent = rawExcerpt;
				body.appendChild( p );
			}

			// Read more CTA
			const cta = document.createElement( 'span' );
			cta.className = 'pa-card-read';
			cta.innerHTML = 'Read more ' + ARROW_SM;
			body.appendChild( cta );

			a.appendChild( body );
			return a;
		}

		// ── Load more handler ─────────────────────────────────────────────────

		async function loadMore() {
			currentPage++;
			btn.disabled = true;
			btn.setAttribute( 'aria-busy', 'true' );

			const label = btn.querySelector( '.pa-show-more-label' );
			if ( label ) label.textContent = 'Loading…';

			try {
				let url = restUrl + '?page=' + currentPage
					+ '&per_page=' + perPage
					+ '&_embed=1';

				if ( termId ) url += '&' + filterTax + '=' + termId;

				const res = await fetch( url );
				if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );

				const pubs = await res.json();
				pubs.forEach( function ( pub ) {
					grid.appendChild( buildCard( pub ) );
				} );

				if ( currentPage >= maxPages ) {
					// All pages loaded — remove the button entirely (mirrors blog-archive.js)
					if ( showMoreWrap ) showMoreWrap.remove();
				} else {
					btn.disabled = false;
					btn.removeAttribute( 'aria-busy' );
					if ( label ) label.textContent = 'Show More';
				}
			} catch ( err ) {
				// Network / parse error — restore button so the user can retry
				btn.disabled = false;
				btn.removeAttribute( 'aria-busy' );
				const l = btn.querySelector( '.pa-show-more-label' );
				if ( l ) l.textContent = 'Show More';
			}
		}

		btn.addEventListener( 'click', loadMore );
	}

} )();
