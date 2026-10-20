/**
 * Shared video lightbox — a singleton full-screen overlay reused by any
 * block that opens a video (a YouTube/Vimeo URL, or a self-hosted media
 * file) in a modal instead of playing it inline.
 *
 * The first block on the page to trigger a video creates the
 * .cropx-vid-lightbox element and appends it to <body>. Every later
 * trigger — regardless of which block it came from — reuses that same
 * element (found via document.querySelector), so only one lightbox ever
 * exists in the DOM no matter how many video-enabled blocks are present.
 * This mirrors the pattern the Video and Two-Column Video blocks already
 * use for the same reason.
 *
 * Requires video-lightbox.css to be loaded. Import it from the consuming
 * block's own style.css so the block is self-sufficient even if no other
 * video block happens to be on the same page:
 *   @import '../../shared/video-lightbox.css';
 *
 * Named exports:
 *   getEmbedUrl(url)               — YouTube/Vimeo URL → autoplay embed URL, or null
 *   openLightbox(src, isMedia, title)
 *   closeLightbox()
 *   initLightboxTriggers(selector) — wires click + keyboard activation on
 *                                     every element matching `selector`
 *                                     that carries a data-video-url or
 *                                     data-media-src attribute
 */

/**
 * Extract an autoplay embed URL from a YouTube or Vimeo link.
 * Returns null for anything else (caller falls back to opening the raw
 * URL in a new tab).
 */
export function getEmbedUrl( url ) {
	const ytMatch = url.match(
		/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
	);
	if ( ytMatch ) {
		return `https://www.youtube.com/embed/${ ytMatch[ 1 ] }?autoplay=1&rel=0`;
	}

	const vimeoMatch = url.match( /(?:vimeo\.com\/(?:video\/)?)(\d+)/ );
	if ( vimeoMatch ) {
		return `https://player.vimeo.com/video/${ vimeoMatch[ 1 ] }?autoplay=1`;
	}

	return null;
}

let lightboxEl = null;

function getLightbox() {
	if ( lightboxEl ) return lightboxEl;

	// Reuse the lightbox if some other block on the page already created one.
	const existing = document.querySelector( '.cropx-vid-lightbox' );
	if ( existing ) {
		lightboxEl = existing;
		return lightboxEl;
	}

	lightboxEl = document.createElement( 'div' );
	lightboxEl.className = 'cropx-vid-lightbox';
	lightboxEl.setAttribute( 'role', 'dialog' );
	lightboxEl.setAttribute( 'aria-modal', 'true' );
	lightboxEl.setAttribute( 'aria-label', 'Video' );
	lightboxEl.innerHTML = `
		<div class="cropx-vid-lightbox__inner">
			<button class="cropx-vid-lightbox__close" aria-label="Close video">
				<svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
					<path d="M4 4l14 14M18 4L4 18"/>
				</svg>
			</button>
			<div class="cropx-vid-lightbox__stage"></div>
		</div>
	`;
	document.body.appendChild( lightboxEl );

	lightboxEl.addEventListener( 'click', ( e ) => {
		if ( e.target === lightboxEl ) closeLightbox();
	} );
	lightboxEl.querySelector( '.cropx-vid-lightbox__close' ).addEventListener( 'click', closeLightbox );

	return lightboxEl;
}

export function openLightbox( src, isMedia, title ) {
	const lb    = getLightbox();
	const stage = lb.querySelector( '.cropx-vid-lightbox__stage' );

	if ( isMedia ) {
		const video       = document.createElement( 'video' );
		video.src         = src;
		video.controls    = true;
		video.autoplay    = true;
		video.playsInline = true;
		stage.innerHTML   = '';
		stage.appendChild( video );
	} else {
		const embedUrl = getEmbedUrl( src );
		if ( ! embedUrl ) {
			// Not a recognised embed platform — open in a new tab as a fallback.
			window.open( src, '_blank', 'noopener,noreferrer' );
			return;
		}
		const iframe           = document.createElement( 'iframe' );
		iframe.src             = embedUrl;
		iframe.allow           = 'autoplay; fullscreen; picture-in-picture';
		iframe.allowFullscreen = true;
		iframe.title           = title || 'Video';
		stage.innerHTML        = '';
		stage.appendChild( iframe );
	}

	lb.classList.add( 'is-open' );
	document.body.style.overflow = 'hidden';
}

export function closeLightbox() {
	if ( ! lightboxEl ) return;
	lightboxEl.classList.remove( 'is-open' );
	lightboxEl.querySelector( '.cropx-vid-lightbox__stage' ).innerHTML = '';
	document.body.style.overflow = '';
}

// ESC key closes the lightbox. Guarded so importing this module from more
// than one block bundle on the same page doesn't add the listener twice.
if ( ! window.__cropxVidLightboxEscBound ) {
	window.__cropxVidLightboxEscBound = true;
	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' ) closeLightbox();
	} );
}

/**
 * Wire click + keyboard activation on every element matching `selector`
 * that carries a data-video-url or data-media-src attribute. Call this
 * once from a block's view.js, scoped to that block's own trigger elements
 * (e.g. '.ugp-section .ugp-cta--video').
 */
export function initLightboxTriggers( selector ) {
	document.querySelectorAll( selector ).forEach( ( el ) => {
		const src     = el.dataset.videoUrl || el.dataset.mediaSrc || '';
		const isMedia = !! el.dataset.mediaSrc;
		const title   = el.getAttribute( 'aria-label' ) || el.textContent.trim() || 'Video';

		if ( ! src ) return;

		function handleClick( e ) {
			e.preventDefault();
			openLightbox( src, isMedia, title );
		}
		function handleKey( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) {
				e.preventDefault();
				openLightbox( src, isMedia, title );
			}
		}

		el.addEventListener( 'click', handleClick );
		el.addEventListener( 'keydown', handleKey );
	} );
}
