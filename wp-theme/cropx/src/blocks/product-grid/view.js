import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Product Grid — front-end controller.
 *
 * This block has no other front-end interactivity of its own — the only
 * reason it needs a view.js at all is to wire up the scroll-triggered
 * reveal animation (header eyebrow/heading/body + the card grid fading up
 * as the section scrolls into view) via the shared initScrollReveal()
 * helper. See src/shared/scrollReveal.js, and render.php for where the
 * reveal-group/reveal-up/reveal-item classes and --reveal-delay values are
 * set.
 */
initScrollReveal( '.pg-block' );
