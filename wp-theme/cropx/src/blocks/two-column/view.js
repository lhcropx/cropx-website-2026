import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Two-Column Text + Photo — front-end controller.
 *
 * This block has no other front-end interactivity of its own — the only
 * reason it needs a view.js at all is to wire up the scroll-triggered
 * reveal animation (icon/eyebrow/heading/body/CTA/visual fading up as the
 * section scrolls into view) via the shared initScrollReveal() helper.
 * No repeated items here, so the section itself is the single observed
 * root — see render.php for where the reveal-up classes and --reveal-delay
 * values are set, and src/shared/scrollReveal.js for the mechanism.
 */
initScrollReveal( '.tcv-section' );
