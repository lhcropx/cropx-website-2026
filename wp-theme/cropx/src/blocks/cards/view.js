import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Cards — front-end controller.
 *
 * The block has no other front-end behavior (no carousel, no lightbox), so
 * this file exists solely to wire up the scroll-triggered reveal animation —
 * see src/shared/scrollReveal.js for why this needs real JS rather than the
 * hero blocks' load-only CSS animation, and src/shared/scroll-reveal.css /
 * this block's own style.css (inlined copy) for the CSS half.
 */
initScrollReveal( '.wp-block-cropx-cards' );
