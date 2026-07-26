( function () {
	/**
	 * Resource Downloads — view.js
	 *
	 * Lazy-renders a PDF first-page thumbnail using pdf.js for resource cards
	 * that have no server-generated cover image (i.e. showing the placeholder SVG).
	 *
	 * Architecture:
	 *   - pdf.js loads from CDN only once, on first need (not on page load)
	 *   - IntersectionObserver triggers rendering 300 px before a card enters the viewport
	 *   - Only placeholders with data-pdf-url are targeted — no interference with cards
	 *     that already have a server-generated cover image
	 *   - Errors fail silently; the placeholder icon stays on any failure
	 *
	 * Post-launch removal note:
	 *   Once the ImageMagick security policy is updated on the server, WordPress will
	 *   auto-generate PDF thumbnails at upload time. When that happens no placeholder
	 *   will carry a data-pdf-url attribute, this script exits at the querySelector
	 *   check below, and adds zero runtime overhead.
	 */

	const PDFJS_URL    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
	const PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

	// Exit immediately if there are no placeholder cards with a PDF URL.
	const placeholders = Array.from(
		document.querySelectorAll( '.rsd-cover-placeholder[data-pdf-url]' )
	);
	if ( ! placeholders.length ) return;

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

	// ── Render one thumbnail ────────────────────────────────────────────────────
	function renderThumbnail( placeholder ) {
		var pdfUrl = placeholder.dataset.pdfUrl;
		if ( ! pdfUrl ) return;

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

			var canvas    = document.createElement( 'canvas' );
			canvas.width  = viewport.width;
			canvas.height = viewport.height;
			canvas.className = 'rsd-cover rsd-cover--canvas';

			return page.render( {
				canvasContext: canvas.getContext( '2d' ),
				viewport:      viewport,
			} ).promise.then( function () {
				// Swap the placeholder out; the container's overflow:hidden
				// clips any aspect-ratio mismatch between the canvas and the wrap.
				placeholder.replaceWith( canvas );
			} );
		} ).catch( function () {
			// Fail silently — placeholder icon remains visible.
		} );
	}

	// ── IntersectionObserver — render 300 px before entering the viewport ──────
	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) return;
				observer.unobserve( entry.target );
				renderThumbnail( entry.target );
			} );
		},
		{ rootMargin: '300px 0px' }
	);

	placeholders.forEach( function ( el ) {
		observer.observe( el );
	} );
}() );
