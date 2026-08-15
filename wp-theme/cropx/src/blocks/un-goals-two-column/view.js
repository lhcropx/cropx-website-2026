/**
 * UN Goals - 2 Column Text & Photo — front-end interaction.
 *
 * The only interactive behaviour this block needs is wiring its CTA (when
 * configured to open a video) into the shared cropx-vid-lightbox overlay —
 * the same modal the Video and Two-Column Video blocks use. See
 * src/shared/videoLightbox.js for how the singleton overlay is created and
 * reused across whichever block asks for it first.
 */
import { initLightboxTriggers } from '../../shared/videoLightbox';

initLightboxTriggers( '.ugp-section .ugp-cta--video, .ugp-section .ugp-link--video' );

// Optional secondary link, editable to point anywhere — but when it's left
// at the default "#" (no custom URL set), smooth-scroll to the top of the
// page instead of relying on the browser's default (instant) jump. Any
// other URL — a different anchor, another page, etc. — is left alone and
// navigates normally.
document.querySelectorAll( '.ugp-section .ugp-back-to-top' ).forEach( ( el ) => {
	if ( el.getAttribute( 'href' ) !== '#' ) {
		return;
	}
	el.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	} );
} );
