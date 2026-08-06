/**
 * Two-Column Text + Video — front-end interaction.
 *
 * Mirrors the Video block's view.js logic but scoped to .tcvid-section.
 * Reuses the .cropx-vid-lightbox element if the Video block created it first,
 * so only one lightbox overlay lives in the DOM regardless of which blocks are on the page.
 *
 * Display modes:
 *   inline   — video replaces the thumbnail inside the frame
 *   lightbox — video opens in a full-screen overlay modal
 *
 * Source types:
 *   url   — external YouTube / Vimeo link → autoplay iframe
 *   media — hosted file → native <video> element
 */

/**
 * Extract an autoplay embed URL from a YouTube or Vimeo link.
 */
function getEmbedUrl( url ) {
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

/* ── Lightbox ─────────────────────────────────────────────────────────────── */

let lightboxEl = null;

function getLightbox() {
	if ( lightboxEl ) return lightboxEl;

	// Reuse the lightbox if the cropx/video block already created one.
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

function openLightbox( src, isMedia, title ) {
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

function closeLightbox() {
	if ( ! lightboxEl ) return;
	lightboxEl.classList.remove( 'is-open' );
	lightboxEl.querySelector( '.cropx-vid-lightbox__stage' ).innerHTML = '';
	document.body.style.overflow = '';
}

document.addEventListener( 'keydown', ( e ) => {
	if ( e.key === 'Escape' ) closeLightbox();
} );

/* ── Lightbox frames ──────────────────────────────────────────────────────── */

document.querySelectorAll( '.tcvid-section .vid-frame--lightbox' ).forEach( ( frame ) => {
	const src     = frame.dataset.videoUrl || frame.dataset.mediaSrc || '';
	const isMedia = !! frame.dataset.mediaSrc;
	const title   = frame.getAttribute( 'aria-label' ) || 'Video';

	if ( ! src ) return;

	function handleLightboxClick( e ) {
		e.preventDefault();
		openLightbox( src, isMedia, title );
	}

	function handleLightboxKey( e ) {
		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			openLightbox( src, isMedia, title );
		}
	}

	frame.addEventListener( 'click', handleLightboxClick );
	frame.addEventListener( 'keydown', handleLightboxKey );
} );

/* ── Inline external URL frames (YouTube / Vimeo) ────────────────────────── */

document.querySelectorAll( '.tcvid-section .vid-frame:not(.vid-frame--lightbox)[data-video-url]' ).forEach( ( frame ) => {
	frame.addEventListener( 'click', function handleClick( e ) {
		e.preventDefault();

		const rawUrl   = frame.dataset.videoUrl;
		const embedUrl = getEmbedUrl( rawUrl );

		if ( ! embedUrl ) {
			window.open( rawUrl, '_blank', 'noopener,noreferrer' );
			return;
		}

		const iframe           = document.createElement( 'iframe' );
		iframe.src             = embedUrl;
		iframe.allow           = 'autoplay; fullscreen; picture-in-picture';
		iframe.allowFullscreen = true;
		iframe.title           = frame.getAttribute( 'aria-label' ) || 'Video';
		iframe.style.cssText   = 'position:absolute;inset:0;width:100%;height:100%;border:0;z-index:1;';

		frame.innerHTML = '';
		frame.appendChild( iframe );
		frame.style.cursor = 'default';
		frame.removeEventListener( 'click', handleClick );
	} );
} );

/* ── Inline media library frames (hosted video files) ────────────────────── */

document.querySelectorAll( '.tcvid-section .vid-frame:not(.vid-frame--lightbox)[data-media-src]' ).forEach( ( frame ) => {
	function activateMedia() {
		const src = frame.dataset.mediaSrc;
		if ( ! src ) return;

		const video         = document.createElement( 'video' );
		video.src           = src;
		video.controls      = true;
		video.autoplay      = true;
		video.playsInline   = true;
		video.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:contain;background:#000;z-index:1;';

		frame.innerHTML = '';
		frame.appendChild( video );
		frame.style.cursor = 'default';
		frame.removeAttribute( 'tabindex' );
		frame.removeEventListener( 'click', handleMediaClick );
		frame.removeEventListener( 'keydown', handleMediaKey );
	}

	function handleMediaClick( e ) { e.preventDefault(); activateMedia(); }
	function handleMediaKey( e ) {
		if ( e.key === 'Enter' || e.key === ' ' ) { e.preventDefault(); activateMedia(); }
	}

	frame.addEventListener( 'click', handleMediaClick );
	frame.addEventListener( 'keydown', handleMediaKey );
} );
