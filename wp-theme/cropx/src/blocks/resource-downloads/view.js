import { initScrollReveal } from '../../shared/scrollReveal';

// Two separate selectors, not one for the whole block (Sep 2026): the intro
// header (.rsd-header) and each repeatable subsection (.rsd-subsection) are
// each their own observed root, so every subsection fades in on its own as
// the visitor scrolls to IT specifically, instead of every subsection
// animating together the instant the top of the block first appears. See
// render.php for the matching reveal-group/reveal-up/reveal-item markup —
// same pattern applied to the People Showcase block's identical
// repeatable-subsection shape.
//
// No ".wp-block-cropx-resource-downloads" ancestor prefix (Sep 2026 bugfix):
// this block's block.json sets supports.className / customClassName to
// false (deliberately, to stop editors from hand-adding a custom class to
// this data-driven block), which also stops WordPress from ever attaching
// the default "wp-block-cropx-resource-downloads" wrapper class it adds to
// every other block. The selector below silently matched nothing on every
// page since the day scroll-reveal was first wired into this block — every
// .reveal-up/.reveal-item child was stuck at opacity:0 forever, since
// nothing was ever observed to trigger is-revealed. ".rsd-header" and
// ".rsd-subsection" are already unique to this block, so no namespace
// prefix is needed at all.
initScrollReveal( '.rsd-header, .rsd-subsection' );

( function () {
	/**
	 * Resource Downloads — view.js
	 *
	 * Three responsibilities:
	 *
	 * 1. Lazy-renders a PDF first-page thumbnail using pdf.js for resource cards
	 *    that have no server-generated cover image (i.e. showing the placeholder SVG).
	 *
	 * 2. Wires up the language/format version picker (.rsd-version-select)
	 *    added Aug 2026: on change, updates the paired "Download PDF"
	 *    button's href to the newly selected PDF version. That's the *only*
	 *    thing the select does — it deliberately never touches the cover
	 *    thumbnail. Lauren's call (Aug 2026): the thumbnail is a stable
	 *    "what is this resource" representation, not a live preview of
	 *    whatever's currently selected to download, so a visitor picking a
	 *    different language/format in the dropdown should never see the
	 *    cover change. The cover's own source is resolved once, server-side,
	 *    by cropx_resource_get_thumbnail_version() in inc/helpers.php — today
	 *    that's always the English A4 version; once a language switcher
	 *    exists it'll follow the site's current language instead, again
	 *    independent of the visitor's dropdown pick.
	 *
	 *    The Online Guide (Sep 2026) is NOT part of this picker — it's a
	 *    separate, always-visible button rendered independently in render.php
	 *    (a guide is a different action from downloading a PDF, not just
	 *    another format choice), so it needs no JS at all: its href is fixed
	 *    at render time and never changes. An earlier version of this feature
	 *    folded the guide into this same dropdown and had this handler swap
	 *    the button's icon/label between "Download PDF" and "View Online
	 *    Guide" — that added a click (open dropdown → select guide → click
	 *    button) to reach content that should be one click away, so it was
	 *    pulled back out (Lauren, Sep 2026).
	 *
	 * 3. Wires up collapsible subsections (.rsd-subheading--toggle) added Aug
	 *    2026: clicking a subsection's heading toggles aria-expanded, toggles
	 *    the `is-collapsed` class that drives a CSS grid-track transition (see
	 *    style.css — this file never measures or sets pixel heights, so the
	 *    animation stays correct regardless of column count, text wrapping, or
	 *    viewport resizes), and toggles `inert` on the panel so its download
	 *    links/selects can't be focused or read by a screen reader while
	 *    visually hidden.
	 *
	 * 4. Anchor-link auto-expand (Sep 2026): each subsection can carry an
	 *    editor-set HTML anchor (rendered as the `id` on .rsd-subsection —
	 *    see render.php). If the page loads (or the hash changes) with a
	 *    fragment matching one of those ids, and that subsection happens to be
	 *    collapsed, this expands it and scrolls it into view — so a visitor
	 *    following a link straight to "Installation Guides" doesn't land on a
	 *    closed accordion and have to click twice. Shares the exact same
	 *    expand/collapse logic as the click handler in #3 via setExpanded()
	 *    below, so the two can never drift out of sync.
	 *
	 * Architecture:
	 *   - pdf.js loads from CDN only once, on first need (not on page load)
	 *   - IntersectionObserver triggers the cover render 300 px before a card
	 *     enters the viewport — this happens exactly once per card, since the
	 *     cover's source never changes afterward (see point 2 above)
	 *   - Only covers with a pdf.js-rendered placeholder (no server image) are
	 *     targeted — no interference with cards that have a real cover image
	 *   - Errors fail silently; the placeholder icon stays visible on any failure
	 *
	 * Landscape pages (Aug 2026):
	 *   The cover frame (.rsd-cover-wrap) is a fixed portrait box so every card in a
	 *   row lines up at the same height. A landscape-oriented document rendered with
	 *   object-fit:cover into that box gets its sides sliced off, which is exactly
	 *   the "System Overview" / "Sustainability Report" cropping problem reported by
	 *   Lauren. When the rendered page turns out wider than it is tall, we instead
	 *   pair two canvases: a full-bleed blurred copy behind (object-fit:cover, blur
	 *   filter — pure CSS, no extra render or fetch) and the untouched sharp page on
	 *   top sized to fit fully inside the frame (object-fit:contain). Portrait pages
	 *   — the overwhelming majority — are untouched: single canvas, cover fit, same
	 *   as before. See style.css's "Landscape cover treatment" block for the CSS half.
	 *
	 * Post-launch removal note:
	 *   Once the ImageMagick security policy is updated on the server, WordPress will
	 *   auto-generate PDF thumbnails at upload time. When that happens no placeholder
	 *   will carry a data-pdf-url attribute, the querySelector checks below find
	 *   nothing, and this script adds zero runtime overhead. (render.php has a
	 *   parallel landscape check for that server-rendered <img> path, so the fix
	 *   survives the migration. The version-picker's href-swap keeps working either
	 *   way — it doesn't depend on pdf.js.)
	 */

	const PDFJS_URL    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
	const PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

	const placeholders    = Array.from( document.querySelectorAll( '.rsd-cover-placeholder[data-pdf-url]' ) );
	const versionSelects  = Array.from( document.querySelectorAll( '.rsd-version-select' ) );
	const collapseToggles = Array.from( document.querySelectorAll( '.rsd-subheading--toggle' ) );

	// Exit immediately if this block instance has none of the three features in play.
	if ( ! placeholders.length && ! versionSelects.length && ! collapseToggles.length ) return;

	// ── pdf.js loader (singleton — loads the script only once) ─────────────────
	let _pdfJsPromise = null;

	function getPdfJs() {
		if ( _pdfJsPromise ) return _pdfJsPromise;
		_pdfJsPromise = new Promise( function ( resolve, reject ) {
			// Already loaded by another block instance on the page.
			if ( window.pdfjsLib ) {
				window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
				resolve( window.pdfjsLib );
				return;
			}
			var script = document.createElement( 'script' );
			script.src = PDFJS_URL;
			script.onload = function () {
				window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
				resolve( window.pdfjsLib );
			};
			script.onerror = function () {
				reject( new Error( 'pdf.js failed to load from CDN' ) );
			};
			document.head.appendChild( script );
		} );
		return _pdfJsPromise;
	}

	// ── Render a PDF's first page into a cover frame ────────────────────────────
	// Called once per card, by the IntersectionObserver below, to replace the
	// placeholder icon with the real first-page render. (It's no longer ever
	// called a second time for the same wrap — the cover's source is fixed
	// once server-side and the version-select handler further down never
	// triggers a re-render — so there's no need to guard against overlapping
	// calls racing each other.)
	function renderPdfCoverInto( wrap, pdfUrl ) {
		if ( ! wrap || ! pdfUrl ) return;

		wrap.classList.add( 'is-loading' );

		getPdfJs().then( function ( lib ) {
			return lib.getDocument( {
				url:              pdfUrl,
				disableAutoFetch: true,  // don't pre-fetch the whole PDF file
				disableStream:    false, // allow HTTP range requests (first page only)
			} ).promise;
		} ).then( function ( pdf ) {
			return pdf.getPage( 1 );
		} ).then( function ( page ) {
			// Render at a fixed pixel width — good quality without fetching a huge image.
			var RENDER_PX    = 400;
			var baseViewport = page.getViewport( { scale: 1 } );
			var scale        = RENDER_PX / baseViewport.width;
			var viewport     = page.getViewport( { scale: scale } );
			var isLandscape  = viewport.width > viewport.height;

			var canvas    = document.createElement( 'canvas' );
			canvas.width  = viewport.width;
			canvas.height = viewport.height;
			canvas.className = 'rsd-cover rsd-cover--canvas' + ( isLandscape ? ' rsd-cover--fg' : '' );

			return page.render( {
				canvasContext: canvas.getContext( '2d' ),
				viewport:      viewport,
			} ).promise.then( function () {
				// Clear out the placeholder icon.
				Array.from( wrap.querySelectorAll( '.rsd-cover, .rsd-cover-placeholder' ) ).forEach(
					function ( el ) { el.remove(); }
				);

				wrap.classList.toggle( 'rsd-cover-wrap--landscape', isLandscape );

				if ( ! isLandscape ) {
					wrap.appendChild( canvas );
					return;
				}

				// Landscape page: pair the sharp, fully-visible canvas with a
				// blurred full-bleed copy behind it, so the tall frame never
				// shows hard empty bars. Same rendered pixels — drawImage()
				// copies them straight across, no second PDF render or fetch.
				var bgCanvas    = document.createElement( 'canvas' );
				bgCanvas.width  = canvas.width;
				bgCanvas.height = canvas.height;
				bgCanvas.className = 'rsd-cover rsd-cover--canvas rsd-cover--bg';
				bgCanvas.setAttribute( 'aria-hidden', 'true' );
				bgCanvas.getContext( '2d' ).drawImage( canvas, 0, 0 );

				wrap.appendChild( bgCanvas );
				wrap.appendChild( canvas );
			} );
		} ).catch( function () {
			// Fail silently — the placeholder icon stays visible.
		} ).finally( function () {
			wrap.classList.remove( 'is-loading' );
		} );
	}

	// ── Initial render, via IntersectionObserver (300 px before viewport) ──────
	if ( placeholders.length ) {
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) return;
					observer.unobserve( entry.target );
					var placeholder = entry.target;
					var wrap        = placeholder.closest( '.rsd-cover-wrap' );
					renderPdfCoverInto( wrap, placeholder.dataset.pdfUrl );
				} );
			},
			{ rootMargin: '300px 0px' }
		);

		placeholders.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	// ── Version picker: sync download link only — cover never changes ──────────
	// See the file doc comment above: the cover thumbnail is fixed server-side
	// and intentionally does not follow the visitor's dropdown selection.
	versionSelects.forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			var option = select.selectedOptions[ 0 ];
			if ( ! option ) return;

			// Point the paired "Download PDF" button at the newly selected version.
			var picker      = select.closest( '.rsd-version-picker' );
			var downloadBtn = picker ? picker.querySelector( '.rsd-version-download' ) : null;
			if ( downloadBtn ) {
				downloadBtn.setAttribute( 'href', option.value );
			}
		} );
	} );

	// ── Collapsible subsections: toggle open/closed ─────────────────────────────
	// The actual open/close motion is a CSS grid-track transition (see
	// .rsd-subsection-track in style.css) — this function only ever flips a
	// class and a couple of ARIA/inert attributes, never touches layout math.
	// Shared by the click handler below and the anchor-link auto-expand further
	// down, so both paths always stay in sync.
	function setExpanded( button, willExpand ) {
		var subsection = button.closest( '.rsd-subsection--collapsible' );
		var panel      = document.getElementById( button.getAttribute( 'aria-controls' ) || '' );
		var isExpanded = button.getAttribute( 'aria-expanded' ) === 'true';

		if ( isExpanded === willExpand ) return false; // already in the requested state

		button.setAttribute( 'aria-expanded', String( willExpand ) );
		if ( subsection ) {
			subsection.classList.toggle( 'is-collapsed', ! willExpand );
		}
		if ( panel ) {
			// Remove `inert` the instant it's expanding, so keyboard/AT users
			// can reach the newly-revealed content right away; add it back
			// when collapsing so its download links/selects stop being
			// focusable or announced once it's visually hidden again.
			if ( willExpand ) {
				panel.removeAttribute( 'inert' );
			} else {
				panel.setAttribute( 'inert', '' );
			}
		}
		return true;
	}

	collapseToggles.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var isExpanded = button.getAttribute( 'aria-expanded' ) === 'true';
			setExpanded( button, ! isExpanded );
		} );
	} );

	// ── Anchor-link auto-expand + scroll (Sep 2026) ─────────────────────────────
	// See the file doc comment (#4) for the why. This only does anything when
	// the current hash matches a subsection's id, so pages with no anchored
	// subsections — the overwhelming majority — pay zero runtime cost beyond
	// one hashchange listener.
	function expandAndScrollToHash() {
		var hash = window.location.hash;
		if ( ! hash || hash.length < 2 ) return;

		var target = document.getElementById( decodeURIComponent( hash.slice( 1 ) ) );
		if ( ! target || ! target.classList.contains( 'rsd-subsection' ) ) return;

		var toggle = target.querySelector( '.rsd-subheading--toggle' );
		var wasCollapsed = target.classList.contains( 'is-collapsed' );

		// Only a collapsible + currently-collapsed subsection needs expanding;
		// a plain (non-collapsible) subsection or an already-open one just
		// needs the scroll-into-view below.
		if ( toggle && wasCollapsed ) {
			setExpanded( toggle, true );
		}

		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		function scrollToTarget() {
			target.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
		}

		if ( toggle && wasCollapsed && ! reduceMotion ) {
			// Wait for the grid-track expand transition (0.35s in style.css) to
			// finish so the page has reached its final height before scrolling —
			// otherwise the scroll target's position is still shifting under it.
			var track = target.querySelector( '.rsd-subsection-track' );
			var settled = false;
			function onTransitionEnd( e ) {
				if ( e.target !== track ) return;
				settled = true;
				track.removeEventListener( 'transitionend', onTransitionEnd );
				scrollToTarget();
			}
			if ( track ) {
				track.addEventListener( 'transitionend', onTransitionEnd );
			}
			// Fallback in case transitionend never fires (e.g. track missing).
			setTimeout( function () {
				if ( ! settled ) scrollToTarget();
			}, 400 );
		} else {
			scrollToTarget();
		}
	}

	if ( window.location.hash ) {
		expandAndScrollToHash();
	}
	window.addEventListener( 'hashchange', expandAndScrollToHash );
}() );
