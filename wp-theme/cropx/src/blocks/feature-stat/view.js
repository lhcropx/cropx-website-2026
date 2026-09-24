import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Feature + Stat Card — front-end controller.
 *
 * This block has no other front-end interactivity of its own — the only
 * reason it needs a view.js at all is to wire up the scroll-triggered
 * reveal animation (icon/eyebrow/heading/body/CTA + photo/stat-card fading
 * up as the section scrolls into view) via the shared initScrollReveal()
 * helper. See src/shared/scrollReveal.js, and render.php for where the
 * reveal-up classes and --reveal-delay values are set.
 */
initScrollReveal( '.fstat-section' );
