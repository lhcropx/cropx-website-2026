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
