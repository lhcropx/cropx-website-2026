/**
 * Category Archive Group — Show More
 *
 * Powers the "Show More" pagination for every category archive group's own
 * page (page-insights.php, page-press-room.php, ...) and category.php.
 * Mirrors pub-archive.js exactly — same IIFE pattern, same DOM-construction
 * approach for XSS safety, same button removal when all pages are loaded —
 * but sources data from the standard WordPress posts REST endpoint filtered
 * by category, and builds agr-* card markup instead of pa-*.
 *
 * Data flow:
 *   PHP (page-insights.php / page-press-room.php / category.php / tag.php,
 *        via inc/insights-archive.php)
 *     → data-max-pages, data-per-page, data-category-ids, data-filter-param
 *       on .agr-grid
 *   PHP (enqueue.php)
 *     → window.cropxAgArchive.restUrl, .categoryIds, .badgeIds, .accentIds
 *       via wp_localize_script
 *   JS
 *     → GET {restUrl}?page=N&per_page=X&{filterParam}={ids}&_embed=1
 *       (filterParam is "categories" everywhere except tag.php, which sets
 *       it to "tags" — see cropx_render_insights_grid()'s $filter_param.
 *       data-category-ids holds the REST filter's id list either way, despite
 *       the category-specific name — kept as-is rather than renaming it
 *       everywhere just for a tag archive that came along later, Aug 2026.)
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

	const grid         = document.querySelector( '.agr-grid' );
	const showMoreWrap = document.querySelector( '.agr-show-more' );
	const btn          = document.querySelector( '.agr-show-more-btn' );

	if ( grid && btn ) {
		const config = window.cropxAgArchive || {};
		const restUrl = config.restUrl || '/wp-json/wp/v2/posts';

		// Comma-string → Set of ints, for quick lookups when building each card.
		function toIdSet( str ) {
			return new Set(
				( str || '' ).split( ',' )
					.map( function ( s ) { return parseInt( s, 10 ); } )
					.filter( function ( n ) { return ! isNaN( n ); } )
			);
		}

		const categoryIds = grid.dataset.categoryIds || config.categoryIds || '';
		const badgeIds    = toIdSet( config.badgeIds );
		const accentIds   = toIdSet( config.accentIds );

		// Which REST query param categoryIds gets sent as — "categories" on
		// every category-scoped view, "tags" on tag.php's tag-scoped view.
		const filterParam = grid.dataset.filterParam || 'categories';

		// Mirrors inc/insights-archive.php's $show_badges — false for Ag
		// Insights, whose badge would say "Ag Insights" on every single card
		// now that it's a single-category group (Aug 2026). Read from the
		// grid's own data attribute (set server-side, always in sync with
		// what was rendered) rather than the config, which is shared across
		// every category-archive-group page.
		const showBadges = grid.dataset.showBadges !== '0';

		const maxPages = parseInt( grid.dataset.maxPages, 10 ) || 1;
		const perPage  = parseInt( grid.dataset.perPage,  10 ) || 10;

		let currentPage = 1;

		// ── Helpers ───────────────────────────────────────────────────────────

		function formatDate( iso ) {
			return new Date( iso ).toLocaleDateString( 'en-US', {
				year: 'numeric', month: 'long', day: 'numeric',
			} );
		}

		// Mirrors cropx_get_archive_post_badge() in inc/insights-archive.php:
		// prefer whichever of the post's own categories is within the group's
		// badge scope, falling back to the post's first category otherwise.
		function findBadgeCategory( cats ) {
			for ( let i = 0; i < cats.length; i++ ) {
				if ( badgeIds.has( cats[ i ].id ) ) return cats[ i ];
			}
			return cats[ 0 ] || null;
		}

		// ── Card builder ──────────────────────────────────────────────────────
		// Builds white agr-card markup matching inc/insights-archive.php.
		// Uses DOM construction for all user-supplied strings (XSS safety).
		// title.rendered is WP-escaped HTML so it is safe as innerHTML.

		function buildCard( post ) {
			const embedded   = post._embedded || {};
			const termGroups = embedded[ 'wp:term' ] || [];
			const cats       = ( termGroups[ 0 ] ) || []; // categories are the first embedded term group

			const badgeCat = findBadgeCategory( cats );

			const media = embedded[ 'wp:featuredmedia' ] && embedded[ 'wp:featuredmedia' ][ 0 ];
			const sizes = media && media.media_details && media.media_details.sizes;
			const thumb = ( sizes && (
				( sizes.medium_large && sizes.medium_large.source_url ) ||
				( sizes.large        && sizes.large.source_url )
			) ) || ( media && media.source_url ) || null;

			// Outer link (whole card is clickable)
			const a = document.createElement( 'a' );
			a.href      = post.link;
			a.className = 'agr-card';

			// Image / placeholder zone
			const imgDiv = document.createElement( 'div' );
			if ( thumb ) {
				imgDiv.className = 'agr-card-img';
				const img = document.createElement( 'img' );
				img.src      = thumb;
				img.alt      = post.title.rendered.replace( /<[^>]+>/g, '' );
				img.loading  = 'lazy';
				img.decoding = 'async';
				imgDiv.appendChild( img );
			} else {
				const ph = PLACEHOLDERS[ placeholderIndex % PLACEHOLDERS.length ];
				placeholderIndex++;
				imgDiv.className = 'agr-card-img agr-card-img--' + ph;
			}
			a.appendChild( imgDiv );

			// Card body
			const body = document.createElement( 'div' );
			body.className = 'agr-card-body';

			// Category badge
			if ( showBadges && badgeCat ) {
				const isAccent = accentIds.has( badgeCat.id );
				const badge = document.createElement( 'span' );
				badge.className   = 'agr-card-badge' + ( isAccent ? ' agr-card-badge--accent' : '' );
				badge.textContent = badgeCat.name;
				body.appendChild( badge );
			}

			// Title
			const h3 = document.createElement( 'h3' );
			h3.className = 'agr-card-title';
			h3.innerHTML = post.title.rendered; // WP-escaped — safe as innerHTML
			body.appendChild( h3 );

			// Date
			const dateEl = document.createElement( 'span' );
			dateEl.className   = 'agr-card-date';
			dateEl.textContent = formatDate( post.date );
			body.appendChild( dateEl );

			// Excerpt
			const rawExcerpt = ( post.excerpt && post.excerpt.rendered )
				? post.excerpt.rendered.replace( /<[^>]+>/g, '' ).replace( /&#8230;/g, '…' ).trim()
				: '';
			if ( rawExcerpt ) {
				const p = document.createElement( 'p' );
				p.className   = 'agr-card-excerpt';
				p.textContent = rawExcerpt;
				body.appendChild( p );
			}

			// Read more CTA
			const cta = document.createElement( 'span' );
			cta.className = 'agr-card-read';
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

			const label = btn.querySelector( '.agr-show-more-label' );
			if ( label ) label.textContent = 'Loading…';

			try {
				let url = restUrl + '?page=' + currentPage
					+ '&per_page=' + perPage
					+ '&_embed=1';

				if ( categoryIds ) url += '&' + filterParam + '=' + categoryIds;

				const res = await fetch( url );
				if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );

				const posts = await res.json();
				posts.forEach( function ( post ) {
					grid.appendChild( buildCard( post ) );
				} );

				if ( currentPage >= maxPages ) {
					// All pages loaded — remove the button entirely (mirrors pub-archive.js)
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
				const l = btn.querySelector( '.agr-show-more-label' );
				if ( l ) l.textContent = 'Show More';
			}
		}

		btn.addEventListener( 'click', loadMore );
	}

} )();
